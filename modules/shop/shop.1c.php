<?php
/**
 * Интеграция с системой 1C:Предприятие
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	$path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}

/**
 * Shop_1c
 */
class Shop_1c extends Diafan
{
	const MODULE_NAME = 'shop';

	/**
	 * @var integer порядковый номер текущей обабатываемой записи (категории, характеристики или товара)
	 */
	private $i = 0;

	/**
	 * @var integer минимальный порядковый номер записи для текущей итерации
	 */
	private $min_i = 0;

	/**
	 * @var integer максимальный порядковый номер записи для текущей итерации
	 */
	private $max_i = 50;

	/**
	 * @var array метка кэша
	 */
	private $cache_meta = array();

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);

		$this->cache_meta = array(
			'name'       => __METHOD__,
			'session_id' => session_id()
		);

		if(! $this->cache["session"] = $this->diafan->_cache->get($this->cache_meta, self::MODULE_NAME))
		{
			if(isset($_SESSION[$this->cache_meta["name"]]))
			{
				$this->cache["session"] = $_SESSION[$this->cache_meta["name"]];
			}
		}
		else unset($_SESSION[$this->cache_meta["name"]]);

		$this->diafan->set_time_limit();
	}

	/**
	 * Деструктор класса
	 *
	 * @return void
	 */
	public function __destruct()
	{
		$this->diafan->_cache->save( TRUE , $this->cache_meta, self::MODULE_NAME);
		if(! $this->diafan->_cache->get($this->cache_meta, self::MODULE_NAME))
		{
			$this->diafan->_session->init();
			$_SESSION[$this->cache_meta["name"]] = $this->cache["session"];
		}
		else $this->diafan->_cache->save($this->cache["session"], $this->cache_meta, self::MODULE_NAME);
	}

	/**
	 * Стартует интеграцию
	 *
	 * @return void
	 */
	public function start()
	{
		File::create_dir('tmp/1c', true);

		if(empty($_GET["type"]) || ! in_array($_GET["type"], array('sale', 'catalog')))
		{
			Custom::inc('includes/404.php');
		}

		if($this->diafan->_users->id)
		{
			$success = true;
		}
		else
		{
			if(! empty($_GET["auth"]))
			{
				preg_match('/^Basic\s+(.*)$/i', $_GET["auth"], $user_pass);
				list($user, $pass) = explode(':', base64_decode($user_pass[1]));
			}
			else
			{
				$user = $_SERVER["PHP_AUTH_USER"];
				$pass = $_SERVER["PHP_AUTH_PW"];
			}

			$name = ($this->diafan->configmodules("mail_as_login", "users", 0, 0) ? "mail" : "name");
			$result = DB::query("SELECT * FROM {users} u WHERE trash='0' AND act='1' AND LOWER(".$name.")=LOWER('%s') AND password='%s'", trim($user), encrypt(trim($pass)));
			if (DB::num_rows($result))
			{
				$user = DB::fetch_object($result);
				$this->diafan->_users->set($user);
				if($this->diafan->_users->roles('init', 'shop', false, 'admin'))
				{
					$success = true;
				}
			}
		}
		if(empty($success))
		{
			echo 'ошибка авторизации';
			exit;
		}

		if($_GET["type"] == 'sale')
		{
			switch($_GET["mode"])
			{
				case 'checkauth':
					$this->checkauth();
					break;

				case 'init':
					$this->init();
					break;

				case 'file':
					$this->sale_file();
					break;

				case 'query':
					$this->sale_query();
					break;

				case 'success':
					$this->sale_success();
					break;

				default:
					Custom::inc('includes/404.php');
			}
		}
		if($_GET["type"] == 'catalog')
		{
			switch($_GET["mode"])
			{
				case 'checkauth':
					$this->checkauth();
					break;

				case 'init':
					$this->init();
					break;

				case 'file':
					$this->catalog_file();
					break;

				case 'import':
					$this->catalog_import();
					break;

				default:
					Custom::inc('includes/404.php');
			}
		}
    }

	/**
	 * Начало сеанса
	 *
	 * @return void
	 */
	private function checkauth()
	{
		echo "success\n";
		echo $this->diafan->_session->name."\n";
		echo $this->diafan->_session->id;
	}

	/**
	 * Запрос параметров от сайта
	 *
	 * @return void
	 */
	private function init()
	{
		echo "zip=no\n";
		echo "file_limit=1000000\n";
	}

	/**
	 * Обмен информацией о заказах: получение файла обмена с сайта
	 *
	 * @return void
	 */
	private function sale_query()
	{
		$no_spaces = '<?xml version="1.0" encoding="utf-8"?>
		<КоммерческаяИнформация ВерсияСхемы="2.04" ДатаФормирования="'.date('Y-m-d').'"></КоммерческаяИнформация>';
		$xml = new SimpleXMLElement($no_spaces);

		if($this->diafan->configmodules("1c_sale_all", "shop"))
		{
			$rows = DB::query_fetch_all("SELECT * FROM {shop_order} WHERE trash='0'");
		}
		else
		{
			$rows = DB::query_fetch_all("SELECT * FROM {shop_order} WHERE created>%d AND trash='0'", $this->diafan->unixdate(LAST_1C_EXPORT));
		}

		foreach ($rows as $row)
		{
			$doc = $xml->addChild("Документ");
			$doc->addChild("Ид", $row["id"]);
			$doc->addChild("Номер", $row["id"]);
			$doc->addChild("Дата", date('Y-m-d', $row["created"]));
			$doc->addChild("ХозОперация", "Заказ товара" );
			$doc->addChild("Роль", "Продавец" );
			$doc->addChild("Курс", "1" );
			$doc->addChild("Время",  date('H:i:s', $row["created"]));
			$doc->addChild("Валюта", "руб");

			$name = '';
			$comment = '';
			$address = array();
			$contacts = array();
			$rows_p = DB::query_fetch_all(
					"SELECT p.id, p.[name], v.value, p.type, p.info FROM {shop_order_param} AS p"
					." INNER JOIN {shop_order_param_element} AS v ON v.param_id=p.id AND v.element_id=%d WHERE p.trash='0' AND v.trash='0' ORDER BY p.sort ASC",
					$row["id"]
				);
			foreach ($rows_p as $row_p)
			{
				switch($row_p["type"])
				{

					case 'select':
					case 'multiple':
						$this->order_select_values($select_values, $row_p["id"]);
						$row_p["value"] = $select_values[$row_p["id"]][$row_p["value"]];
						break;

					case 'checkbox':
						$row_p["value"] = $row_p["value"] ? "да" : "нет";
						break;
				}
				switch($row_p["info"])
				{
					case 'email':
					case 'phone':
					case 'phone-extra':
						$contacts[$row_p["name"]] = $row_p["value"];
						break;

					case 'address':
					case 'street':
					case 'building':
					case 'suite':
					case 'flat':
					case 'entrance':
					case 'floor':
					case 'intercom':
					case 'city':
					case 'country':
					case 'zip':
					case 'metro':
					case 'cargolift':
						$address[$row_p["name"]] = $row_p["value"];
						break;

					case 'name':
					case 'firstname':
					case 'lastname':
					case 'fathersname':
						$name .= ($name ? ' ' : '').$row_p["value"];
						break;

					default:
						$comment .= $row_p["name"].': '.$row_p["value"]."\n";
				}

			}

			if($comment)
			{
				$doc->addChild("Комментарий", $comment);
			}

			// Контрагенты
			$k1 = $doc->addChild('Контрагенты');
			$k1_1 = $k1->addChild('Контрагент');
			$k1_2 = $k1_1->addChild("Ид", $name);
			$k1_2 = $k1_1->addChild("Наименование", $name);
			$k1_2 = $k1_1->addChild("Роль", "Покупатель");
			$k1_2 = $k1_1->addChild("ПолноеНаименование", $name);

			// Доп параметры
			if($address)
			{
				$addr = $k1_1->addChild('АдресРегистрации');
				$addr->addChild('Представление', implode(", ", $address));
				foreach ($address as $k => $v)
				{
					$addrField = $addr->addChild('АдресноеПоле');
					$addrField->addChild('Тип', $k);
					$addrField->addChild('Значение', $v);
				}
			}

			if($contacts)
			{
				$cs = $k1_1->addChild('Контакты');
				foreach ($contacts as $k => $v)
				{
					$cont = $cs->addChild('Контакт');
					$cont->addChild('Тип', $k);
					$cont->addChild('Значение', $v);
				}
			}

			$order_summ = 0;

			$t1 = $doc->addChild('Товары');
			$rows_good = DB::query_fetch_all(
					"SELECT g.id, g.good_id, g.price, g.count_goods, s.[name], s.article, s.import_id FROM {shop_order_goods} AS g"
					." INNER JOIN {shop} AS s ON s.id=g.good_id"
					." WHERE g.order_id=%d AND g.trash='0' AND s.trash='0'",
					$row["id"]
				);
			foreach ($rows_good as $row_good)
			{
				$params = DB::query_fetch_key_value("SELECT * FROM {shop_order_goods_param} WHERE order_goods_id=%d AND trash='0'", $row_good["id"], "param_id", "value");
				$row_price = $this->diafan->_shop->price_get($row_good["good_id"], $params, false);
				if(empty($row_price["price_id"]))
				{
					$row_price["price_id"] = 0;
					$row_price["price"] = 0;
					$row_price["import_id"] = '';
				}
				else
				{
					$row_price["import_id"] = DB::query_result("SELECT import_id FROM {shop_price} WHERE id=%d AND trash='0' LIMIT 1", $row_price["price_id"]);
					if(! $row_price["import_id"])
					{
						$row_price["import_id"] = $row_price["price_id"];
					}
				}
				$row_good["discount"] = '';

				if(! empty($row_price["old_price"]) && $row_price["old_price"] != $row_good["price"])
				{
					$row_good["discount"] = ($row_price["old_price"] - $row_good["price"]) * $row_good["count_goods"];
					$row_good["price"] = $row_price["old_price"];
				}

				$t1_1 = $t1->addChild('Товар' );

				if (empty($row_good['import_id']))
				{
					$t1_2 = $t1_1->addChild("Ид", $row_good["good_id"].($params && $row_price["import_id"] ? '#'.$row_price["import_id"] : ''));
				}
				else
				{
					if($params)
					{
						$row_good["import_id"] .= '#'.$row_price["import_id"];
					}
					$t1_2 = $t1_1->addChild("Ид", $row_good["import_id"]);
				}

				$t1_2 = $t1_1->addChild("Артикул", $row_good["article"]);

				$t1_2 = $t1_1->addChild("Наименование", $row_good["name"]);
				$t1_2 = $t1_1->addChild("ЦенаЗаЕдиницу", number_format($row_good["price"], 2, ".", ""));
				$measure = $t1_1->addChild("БазоваяЕдиница", "шт");
				$measure->addAttribute('Код', '796');
				$measure->addAttribute('НаименованиеПолное', 'Штука');
				$measure->addAttribute('МеждународноеСокращение', 'PCE');
				$t1_2 = $t1_1->addChild("Количество", $row_good["count_goods"]);
				$t1_2 = $t1_1->addChild("Сумма", number_format($row_good["price"] * $row_good["count_goods"], 2 , ".", ""));

				$order_summ += $row_good["price"] * $row_good["count_goods"] - $row_good["discount"];

				if($row_good["discount"])
				{
					$t1_2 = $t1_1->addChild("Скидки");
					$t1_3 = $t1_2->addChild("Скидка");
					$t1_4 = $t1_3->addChild("Сумма", $row_good["discount"]);
					$t1_4 = $t1_3->addChild("УчтеноВСумме", "false");
				}

				$t1_2 = $t1_1->addChild("ЗначенияРеквизитов");
				$t1_3 = $t1_2->addChild("ЗначениеРеквизита");
				$t1_4 = $t1_3->addChild("Наименование", "ВидНоменклатуры");
				$t1_4 = $t1_3->addChild("Значение", "Товар");

				$t1_3 = $t1_2->addChild("ЗначениеРеквизита");
				$t1_4 = $t1_3->addChild("Наименование", "ТипНоменклатуры");
				$t1_4 = $t1_3->addChild("Значение", "Товар");

				$t1_2 = $t1_1->addChild("ХарактеристикиТовара");
				foreach ($params as $param_id => $param_value)
				{
					if(! $param_value)
					{
						continue;
					}
					if(! isset($this->cache["params_name"][$param_id]))
					{
						$this->cache["params_name"][$param_id] = DB::query_result("SELECT [name] FROM {shop_param} WHERE id=%d AND trash='0' LIMIT 1", $param_id);
					}
					if(! isset($this->cache["params_value"][$param_value]))
					{
						$this->cache["params_value"][$param_value] = DB::query_result("SELECT s.[name] FROM {shop_param_select} AS s WHERE s.id=%d AND s.trash='0' LIMIT 1", $param_value);
					}
					$param_name  = $this->cache["params_name"][$param_id];
					$param_value = $this->cache["params_value"][$param_value];

					$t1_3 = $t1_2->addChild("ХарактеристикаТовара");
					$t1_4 = $t1_3->addChild("Наименование", $param_name);
					$t1_4 = $t1_3->addChild("Значение", $param_value);
				}
			}

			// Дополнительные затраты
			$rows_a = DB::query_fetch_all(
					"SELECT a.id, a.[name], s.summ FROM {shop_additional_cost} AS a"
					." INNER JOIN {shop_order_additional_cost} AS s ON s.additional_cost_id=a.id AND s.order_id=%d"
					." WHERE a.trash='0'", $row["id"]
				);
			foreach ($rows_a as $row_a)
			{
				$t1_1 = $t1->addChild('Товар');
				$t1_1->addChild("Ид", 'ORDER_ADDITIONAL_'.$row_a["id"]);
				$t1_1->addChild("Наименование", $row_a["name"]);
				$t1_1->addChild("ЦенаЗаЕдиницу", $row_a['summ']);
				$measure = $t1_1->addChild("БазоваяЕдиница", "шт");
				$measure->addAttribute('Код', '796');
				$measure->addAttribute('НаименованиеПолное', 'Штука');
				$measure->addAttribute('МеждународноеСокращение', 'PCE');
				$t1_1->addChild("Количество", 1 );
				$t1_1->addChild("Сумма", $row_a['summ']);
				$order_summ += $row_a["summ"];
				$t1_2 = $t1_1->addChild("ЗначенияРеквизитов" );
				$t1_3 = $t1_2->addChild("ЗначениеРеквизита" );
				$t1_4 = $t1_3->addChild("Наименование", "ВидНоменклатуры" );
				$t1_4 = $t1_3->addChild("Значение", "Услуга" );

				$t1_3 = $t1_2->addChild("ЗначениеРеквизита" );
				$t1_4 = $t1_3->addChild("Наименование", "ТипНоменклатуры" );
				$t1_4 = $t1_3->addChild("Значение", "Услуга" );
			}

			// Доставка
			if ($row["delivery_id"])
			{
				$delivery_name = DB::query_result("SELECT [name] FROM {shop_delivery} WHERE id=%d AND trash='0' LIMIT 1", $row["delivery_id"]);
				$t1_1 = $t1->addChild('Товар');
				$t1_1->addChild("Ид", 'ORDER_DELIVERY_'.$row["delivery_id"]);
				$t1_1->addChild("Наименование", 'Доставка: '.$delivery_name);
				$t1_1->addChild("ЦенаЗаЕдиницу", $row["delivery_summ"]);
				$order_summ += $row["delivery_summ"];
				$measure = $t1_1->addChild("БазоваяЕдиница", "шт");
				$measure->addAttribute('Код', '796');
				$measure->addAttribute('НаименованиеПолное', 'Штука');
				$measure->addAttribute('МеждународноеСокращение', 'PCE');
				$t1_1->addChild("Количество", 1 );
				$t1_1->addChild("Сумма", $row["delivery_summ"]);
				$t1_2 = $t1_1->addChild("ЗначенияРеквизитов" );
				$t1_3 = $t1_2->addChild("ЗначениеРеквизита" );
				$t1_4 = $t1_3->addChild("Наименование", "ВидНоменклатуры" );
				$t1_4 = $t1_3->addChild("Значение", "Услуга" );

				$t1_3 = $t1_2->addChild("ЗначениеРеквизита" );
				$t1_4 = $t1_3->addChild("Наименование", "ТипНоменклатуры" );
				$t1_4 = $t1_3->addChild("Значение", "Услуга" );
			}

			if($order_summ != $row["summ"])
			{
				$t1_2 = $doc->addChild("Скидки");
				$t1_3 = $t1_2->addChild("Скидка");
				$t1_4 = $t1_3->addChild("Сумма", number_format($order_summ - $row["summ"], 2 , ".", ""));
				$t1_4 = $t1_3->addChild("УчтеноВСумме", "false");
			}
			$doc->addChild("Сумма", number_format($order_summ, 2 , ".", ""));

			// Статус
			if($row["status"] == 3)
			{
				$s1_2 = $doc->addChild("ЗначенияРеквизитов" );
				$s1_3 = $s1_2->addChild("ЗначениеРеквизита" );
				$s1_3->addChild("Наименование", "Статус заказа" );
				$s1_3->addChild("Значение", "[F] Доставлен" );
			}
			elseif($row["status"] == 2)
			{
				$s1_2 = $doc->addChild("ЗначенияРеквизитов" );
				$s1_3 = $s1_2->addChild("ЗначениеРеквизита" );
					$s1_3->addChild("Наименование", "Отменен" );
					$s1_3->addChild("Значение", "true" );
			}
			else
			{
				$s1_2 = $doc->addChild("ЗначенияРеквизитов" );
				$s1_3 = $s1_2->addChild("ЗначениеРеквизита" );
				$s1_3->addChild("Наименование", "Статус заказа" );
				$s1_3->addChild("Значение", "[N] Принят" );
			}

			// Доставка в реквизиты документа
			if ($row["delivery_id"])
			{
				$s1_3 = $s1_2->addChild("ЗначениеРеквизита" );
				$s1_3->addChild("Наименование", "Способ доставки");
				$s1_3->addChild("Значение", $delivery_name);
			}

			// Метод оплаты в реквизиты документа
			if (in_array('payment', $this->diafan->installed_modules))
			{
				$payment = DB::query_fetch_array("SELECT p.[name], p.id as payment_id, h.id, h.payment_data, h.summ, h.created FROM {payment} AS p INNER JOIN {payment_history} AS h ON h.payment_id=p.id WHERE h.element_id=%d AND h.module_name='cart' AND p.trash='0'", $row["id"]);
				$s1_3 = $s1_2->addChild("ЗначениеРеквизита" );
				$s1_3->addChild("Наименование", "Метод оплаты");
				$s1_3->addChild("Значение", $payment["name"]);

				$p1 = $doc->addChild('Оплаты');
				$p1_1 = $p1->addAttribute('НомерДокумента', ($payment["payment_data"] ? $payment["payment_data"] : $payment["id"]));
				$p1_2 = $p1->addAttribute('НомерТранзакции', $payment["id"]);
				$p1_3 = $p1->addAttribute('ДатаОплаты', date('Y-m-d H:i:s', $payment["created"]));
				$p1_4 = $p1->addAttribute('СуммаОплаты', $payment["summ"]);
				$p1_5 = $p1->addAttribute('СпособОплаты', $payment["name"]);
				$p1_6 = $p1->addAttribute('ИдСпособаОплаты', $payment["payment_id"]);
			}
		}

		header ( "Content-type: text/xml; charset=utf-8" );
		echo "\xEF\xBB\xBF";
		echo $xml->asXML ();
	}

	private function order_select_values(&$select_values, $id)
	{
		if(! isset($select_values[$id]))
		{
			$select_values[$id] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_order_param_select} WHERE param_id=%d AND trash='0'", $id, "id", "name");
		}
	}

	/**
	 * Обмен информацией о заказах: отправка файла обмена на сайт
	 *
	 * @return void
	 */
	private function sale_file()
	{
		$filename = basename($_GET['filename']);

		if(preg_match('/\.php$/', $filename))
		{
			return;
		}

		File::save_file(file_get_contents('php://input'), 'tmp/1c/'.$filename);

		$xml = simplexml_load_file(ABSOLUTE_PATH.'tmp/1c/'.$filename);

		File::delete_file('tmp/1c/'.$filename);

		foreach ($xml->Документ as $xml_order)
		{
			$id = $xml_order->Номер;
			list($y, $m, $d) = explode('-', $xml_order->Дата);
			list($h, $i, $s) = explode(':', $xml_order->Время);
			$created = mktime($h, $i, $s, $m, $d, $y);

			if(isset($xml_order->ЗначенияРеквизитов->ЗначениеРеквизита))
			{
				foreach ($xml_order->ЗначенияРеквизитов->ЗначениеРеквизита as $r)
				{
					switch ($r->Наименование)
					{
						case 'Проведен':
							$proveden = ($r->Значение == 'true');
							break;

						case 'ПометкаУдаления':
							$udalen = ($r->Значение == 'true');
							break;
					}
				}
			}

			if(! empty($udalen))
			{
				$status = 2;
			}
			elseif(! empty($proveden))
			{
				$status = 3;
			}
			else
			{
				$status = 0;
			}
			$status_id = DB::query_result("SELECT id FROM {shop_order_status} WHERE status='%d' AND trash='0' LIMIT 1", $status);

			if(DB::query_result("SELECT id FROM {shop_order} WHERE id=%d", $id))
			{
				DB::query("UPDATE {shop_order} SET status='%d', status_id=%d, created=%d, trash='0' WHERE id=%d", $status, $status_id, $created, $id);
			}
			else
			{
				DB::query("INSERT INTO {shop_order} (status, status_id, created".($id ? ", id" : '').") VALUES ('%d', %d, %d".($id ? ", %d" : '').")", $status, $status_id, $created, $id);
				if(! $id)
				{
					$id = DB::insert_id();
				}
			}

			$order_goods = array();
			// Товары
			foreach ($xml_order->Товары->Товар as $xml_product)
			{
				$import_good_id = 0;
				$import_price_id = '';
				$order_goods_id = 0;
				$good_id = 0;
				if(strstr($xml_product->Ид, '#'))
				{
					list($import_good_id, $import_price_id) = explode('#', $xml_product->Ид, 2);
				}
				else
				{
					$import_good_id = $xml_product->Ид;
				}

				$article = $xml_product->Артикул;
				$name = $xml_product->Наименование;
				$count_goods = $xml_product->Количество;
				$price = $xml_product->ЦенаЗаЕдиницу;
				$discount_id = 0;

				if(isset($xml_product->Скидки->Скидка))
				{
					$discount_id = DB::query_result("SELECT id FROM {shop_discount} WHERE discount=%d AND trash='0'", $xml_product->Скидки->Скидка->Процент);
				}
				$join_params = '';
				if($import_good_id)
				{
					$good_id = DB::query_result("SELECT id FROM {shop} WHERE import_id='%h' AND trash='0'".(! preg_match('/[^0-9]+/', $import_good_id) ? " OR id='%s'" : ''), $import_good_id, $import_good_id);
					if($good_id && $import_price_id)
					{
						$price_id = DB::query_result("SELECT price_id FROM {shop_price} WHERE good_id=%d AND import_id='%h' AND trash='0'".(! preg_match('/[^0-9]+/', $import_price_id) ? " OR price_id='%s'" : ''), $good_id,$import_price_id, $import_price_id);
						if($price_id)
						{
							$rs =  DB::query_fetch_all("SELECT * FROM {shop_price_param} WHERE price_id=%d AND trash='0'", $price_id);
							foreach ($rs as $r)
							{
								if($r["param_value"])
								{
									$i = (empty($i) ? 1 : $i + 1);
									$join_params .= " INNER JOIN {shop_order_goods_param} AS p".$i." ON p".$i.".order_goods_id=g.id AND p".$i.".param_id=".$r["param_id"]." AND p".$i.".value=".$r["param_value"];
								}
							}
						}
					}
				}
				if(! $good_id && $article)
				{
					if(! $good_id = DB::query_result("SELECT id FROM {shop} WHERE article='%h' AND trash='0'", $article))
						continue;
				}
				if(! $good_id && $name)
				{
					if(! $good_id = DB::query_result("SELECT id FROM {shop} WHERE [name]='%h' AND trash='0'", $name))
						continue;
				}

				$order_goods_id = DB::query_result("SELECT g.id FROM {shop_order_goods} AS g".$join_params." WHERE g.order_id=%d AND g.good_id=%d AND g.trash='0'", $id, $good_id);

				if($order_goods_id)
				{
					DB::query("UPDATE {shop_order_goods} SET count_goods=%f, price=%f, discount_id=%d WHERE id=%d", $count_goods, $price, $discount_id, $order_goods_id);
				}
				else
				{
					$order_goods_id = DB::query("INSERT INTO {shop_order_goods} (order_id, good_id, count_goods, price, discount_id) VALUES (%d, %d, %f, %f, %d)", $id, $good_id, $count_goods, $price, $discount_id);
				}
				$order_goods[] = $order_goods_id;
			}
			// удаляет покупки, которых нет в файле
			if($order_goods)
			{
				$del_goods = DB::query_fetch_value("SELECT id FROM {shop_order_goods} WHERE id NOT IN (%s) AND order_id=%d AND trash='0'", implode(",", $order_goods), $id, "id");
				if($del_goods)
				{
					DB::query("DELETE FROM {shop_order_goods_param} WHERE order_goods_id IN (%s)", implode(",", $del_goods));
					DB::query("DELETE FROM {shop_order_goods} WHERE id IN (%s)", implode(",", $del_goods));
				}
			}
			DB::query("UPDATE {shop_order} SET summ=%f WHERE id=%d", $xml_order->Сумма, $id);
		}

		echo "success";
	}

	/**
	 * Обмен информацией о заказах: успешное получение и запись заказов системой "1С:Предприятие"
	 *
	 * @return void
	 */
	private function sale_success()
	{
		Custom::inc('includes/config.php');
		$config = new Config();
		$config->save(array('LAST_1C_EXPORT' => date('d.m.Y H:i')), $this->diafan->_languages->all);
		File::delete_dir('tmp/1c');
	}

	/**
	 * Выгрузка каталогов продукции: выгрузка на сайт файлов обмена
	 *
	 * @return void
	 */
	private function catalog_file()
	{
		$filename = basename($_GET['filename']);

		if(preg_match('/\.php$/', $filename))
		{
			return;
		}
		if(preg_match('/\.jpeg$/', $filename))
		{
			File::delete_file('tmp/1c/'.$filename);
		}
		$f = fopen(ABSOLUTE_PATH.'tmp/1c/'.$filename, 'ab');
		fwrite($f, file_get_contents('php://input'));
		fclose($f);
		echo "success\n";
	}

	/**
	 * Выгрузка каталогов продукции: пошаговая загрузка каталога
	 *
	 * @return void
	 */
	private function catalog_import()
	{
		$filename = basename($_GET['filename']);
		$xml = simplexml_load_file(ABSOLUTE_PATH.'tmp/1c/'.$filename);
		if($this->diafan->configmodules("1c_progress_i", "shop"))
		{
			$this->min_i = $this->diafan->configmodules("1c_progress_i", "shop");
			$this->max_i += $this->diafan->configmodules("1c_progress_i", "shop") - 1;
		}

		$site_id = DB::query_result("SELECT id FROM {site} WHERE module_name='shop' AND trash='0' AND [act]='1' LIMIT 1");
		if(isset($xml->Классификатор))
		{
			// Категории
			$this->import_categories($xml->Классификатор, $site_id);
			$this->import_params($xml->Классификатор);
			DB::query("UPDATE {shop_category} SET sort=id WHERE sort=0");
			DB::query("UPDATE {shop_param} SET sort=id WHERE sort=0");
		}

		if(isset($xml->Каталог))
		{
			$this->import_goods($xml->Каталог, $site_id);
			DB::query("UPDATE {shop} SET sort=id WHERE sort=0");
		}

		if(isset($xml->ПакетПредложений))
		{
			$this->import_prices($xml->ПакетПредложений);
		}

		echo "success";
		$this->diafan->_cache->delete("", "shop");
		if(empty($_GET["no_delete"]))
		{
			File::delete_file('tmp/1c/'.$filename);
		}
		$this->cache["session"] = FALSE;
		$this->diafan->configmodules("1c_progress_i", "shop", 0, false, 0);
	}

	/**
	 * Импорт категорий
	 *
	 * @return void
	 */
	private function import_categories($xml, $site_id, $parent_id = 0, $parents = array())
	{
		if(! isset($xml->Группы->Группа))
			return;

		if($parent_id)
		{
			$parents[] = $parent_id;
		}

		foreach ($xml->Группы->Группа as $xml_group)
		{
			$row = DB::query_fetch_array("SELECT id, parent_id FROM {shop_category} WHERE import_id='%h' AND trash='0' LIMIT 1", $xml_group->Ид);
			$id = ! empty($row["id"]) ? $row["id"] : 0;
			if($this->check_max())
			{
				if(! $id)
				{
					$id = DB::query("INSERT INTO {shop_category} (import_id, [name], parent_id, site_id, [act]) VALUES ('%h', '%h', %d, %d, '%d')", $xml_group->Ид, $xml_group->Наименование, $parent_id, $site_id, ($this->diafan->configmodules("1c_act", "shop") ? 1 : 0));

					if($parents)
					{
						DB::query("INSERT INTO {shop_category_parents} (parent_id, element_id) VALUES (".implode(",".$id."), (", $parents).",".$id.")");
					}

					//ЧПУ
					if(ROUTE_AUTO_MODULE)
					{
						$this->diafan->_route->save('', $xml_group->Наименование, $id, 'shop', 'cat', $site_id, 0,$parent_id);
					}

					// если категории будут активироваться на сайте сразу
					// ссылка на карте сайта
					if($this->diafan->configmodules("1c_act", "shop") && in_array("map", $this->diafan->installed_modules))
					{
						$shop_row = array(
							"module_name" => 'shop',
							"id"          => $id,
							"site_id"     => $site_id,
							"element_type"        => 'cat',
						);
						$this->diafan->_map->index_element($shop_row);
					}
				}
				else
				{
					DB::query("UPDATE {shop_category} SET parent_id=%d, [name]='%h' WHERE id=%d", $parent_id, $xml_group->Наименование, $id);
					if($parent_id != $row["parent_id"])
					{
						DB::query("DELETE FROM {shop_category_parents} WHERE element_id=%d", $id);
						if($parents)
						{
							DB::query("INSERT INTO {shop_category_parents} (parent_id, element_id) VALUES (".implode(",".$id."), (", $parents).",".$id.")");
						}
					}
				}
				$this->cache["session"]["cats"][strval($xml_group->Ид)] = $id;
			}
			$this->import_categories($xml_group, $site_id, $id, $parents);
		}
		if(! $parent_id)
		{
			// пересчитываем количество детей у всех категорий
			$rows = DB::query_fetch_all("SELECT id FROM {shop_category}");
			foreach ($rows as $row)
			{
				$count = DB::query_result("SELECT COUNT(*) FROM  {shop_category_parents} WHERE parent_id=%d", $row["id"]);
				DB::query("UPDATE {shop_category} SET count_children=%d WHERE id=%d", $count, $row["id"]);
			}
		}
	}

	/**
	 * Импорт дополнительных характеристик
	 *
	 * @return void
	 */
	private function import_params($xml)
	{
		$property = array();
		if(isset($xml->Свойства->СвойствоНоменклатуры))
		{
			$property = $xml->Свойства->СвойствоНоменклатуры;
		}

		if(isset($xml->Свойства->Свойство))
		{
			$property = $xml->Свойства->Свойство;
		}

		foreach ($property as $xml_feature)
		{
			if(! $this->check_max())
			{
				continue;
			}
			switch($xml_feature->ТипЗначений)
			{
				case 'Число':
					$type = 'numtext';
					break;
				case 'Справочник':
					$type = 'select';
					break;
				default:
					$type = 'text';
					break;
			}
			$where = " WHERE [name]='%h'";
			$value = array($xml_feature->Наименование);
			if($type == 'select')
			{
				$where  .= " AND (type='%s' OR type='%s')";
				$value[] = $type; $value[] = "multiple";
			}
			else
			{
				$where  .= " AND type='%s'";
				$value[] = $type;
			}
			$row = DB::query_fetch_array("SELECT id, type FROM {shop_param}".$where." AND trash='0' LIMIT 1", $value);
			$values = array();
			if(! $row)
			{
				$row["id"] = DB::query("INSERT INTO {shop_param} ([name], type, id_page) VALUES ('%h', '%s', '1')", $xml_feature->Наименование, $type);
				$row["type"] = $type;
				if($type == 'select' && ! empty($xml_feature->ВариантыЗначений->Справочник))
				{
					$i = 1;
					foreach ($xml_feature->ВариантыЗначений->Справочник as $xml_s)
					{
						$values[strval($xml_s->ИдЗначения)] = DB::query("INSERT INTO {shop_param_select} ([name], param_id, sort) VALUES ('%h', %d, %d)", $xml_s->Значение, $row["id"], $i++);
					}
				}
			}
			else
			{
				if($row["type"] == "multiple" && $type == 'select')
				{
					$type = 'multiple';
				}
				if($row["type"] != $type)
				{
					DB::query("UPDATE {shop_param} SET type='%s' WHERE id=%d", $type, $row["id"]);
				}
				if(($type == 'select' || $type == 'multiple') && ! empty($xml_feature->ВариантыЗначений->Справочник))
				{
					$i = 1;
					foreach ($xml_feature->ВариантыЗначений->Справочник as $xml_s)
					{
						if(! $sel_id = DB::query_result("SELECT id FROM {shop_param_select} WHERE [name]='%h' AND param_id=%d AND trash='0'", $xml_s->Значение, $row["id"]))
						{
							$sel_id = DB::query("INSERT INTO {shop_param_select} ([name], param_id, sort) VALUES ('%h', %d, %d)", $xml_s->Значение, $row["id"], $i++);
						}
						$values[strval($xml_s->ИдЗначения)] = $sel_id;
					}
				}
			}
			$row["values"] = $values;
			$this->cache["session"]["params"][strval($xml_feature->Ид)] = $row;
		}
	}

	/**
	 * Импорт товаров
	 *
	 * @return void
	 */
	private function import_goods($xml, $site_id)
	{
		if(! isset($xml->Товары->Товар))
			return;

		$this->cache["params_name"] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param} WHERE trash='0'", "name", "id");

		$rows = DB::query_fetch_all("SELECT cat_id, element_id FROM {shop_param_category_rel} WHERE trash='0'");
		foreach ($rows as $row)
		{
			$this->cache["session"]["params_cats"][$row["element_id"]][$row["cat_id"]] = true;
		}
		DB::query("UPDATE {shop} SET import_id=id WHERE import_id=''");

		foreach ($xml->Товары->Товар as $xml_product)
		{
			if(! $this->check_max())
			{
				continue;
			}
			if(strpos(strval($xml_product->Ид), '#') !== false)
			{
				list($good_id_1c, $variant_id_1c) = explode('#', strval($xml_product->Ид), 2);
			}
			else
			{
				$good_id_1c = strval($xml_product->Ид);
				$variant_id_1c = '';
			}

			// если товар уже выгружали, то это вариация
			if(empty($this->cache["session"]["goods"][$good_id_1c]))
			{
				$row = DB::query_fetch_array("SELECT id, cat_id, article, [measure_unit], [name], [text], [anons], brand_id, weight, width, height, length FROM {shop} WHERE import_id='%h' AND trash='0' LIMIT 1", $good_id_1c);

				$id = ! empty($row["id"]) ? $row["id"] : 0;

				// удаление товара
				if($xml_product->attributes()->Статус == 'Удален')
				{
					if($id)
					{
						$this->delete_good($id);
					}
					continue;
				}

				// категория
				if(isset($xml_product->Группы->Ид))
				{
					if(! isset($this->cache["session"]["cats"][strval($xml_product->Группы->Ид)]))
					{
						$this->cache["session"]["cats"][strval($xml_product->Группы->Ид)] = DB::query_result("SELECT id FROM {shop_category} WHERE trash='0' AND import_id='%h'", $xml_product->Группы->Ид);
					}
					$cat_id = $this->cache["session"]["cats"][strval($xml_product->Группы->Ид)];
				}
				else
				{
					$cat_id = 0;
				}

				// производитель
				$brand_id = 0;
				if(isset($xml_product->Изготовитель->Ид))
				{
					if(empty($this->cache["session"]["brands"][strval($xml_product->Изготовитель->Ид)]))
					{
						if(! $brand_id = DB::query_result("SELECT id FROM {shop_brand} WHERE import_id='%s' AND trash='0'", $xml_product->Изготовитель->Ид))
						{
							$brand_id = DB::query("INSERT INTO {shop_brand} ([name], site_id, timeedit, import_id, [act]) VALUES ('%s', %d, %d, '%s', '%d')", $xml_product->Изготовитель->Наименование, $site_id, time(), $xml_product->Изготовитель->Ид, ($this->diafan->configmodules("1c_act", "shop") ? 1 : 0));
							DB::query("INSERT INTO {shop_brand_category_rel} (element_id) VALUES (%d)", $brand_id);
							//ЧПУ
							if(ROUTE_AUTO_MODULE)
							{
								$this->diafan->_route->save('', strval($xml_product->Изготовитель->Наименование), $brand_id, 'shop', 'brand', $site_id);
							}
							if($this->diafan->configmodules("1c_act", "shop") && in_array("map", $this->diafan->installed_modules))
							{
								$shop_row = array(
									"module_name"  => 'shop',
									"id"           => $brand_id,
									"site_id"      => $site_id,
									"element_type" => 'brand',
								);
								$this->diafan->_map->index_element($shop_row);
							}
						}
						$this->cache["session"]["brands"][strval($xml_product->Изготовитель->Ид)] = $brand_id;
					}
					$brand_id = $this->cache["session"]["brands"][strval($xml_product->Изготовитель->Ид)];
				}

				// описание
				$description = '';
				if(isset($xml_product->Описание))
				{
					$description = strval($xml_product->Описание);
				}
				else
				{
					// в системе МойСклад описание записывается в реквизит "Полное наименование"
					if(isset($xml_product->ЗначенияРеквизитов->ЗначениеРеквизита))
					foreach ($xml_product->ЗначенияРеквизитов->ЗначениеРеквизита as $xml_option)
					{
						if(strval($xml_option->Наименование) == 'Полное наименование')
						{
							$description = strval($xml_option->Значение);
						}
					}
				}
				$measure_unit = strval($xml_product->БазоваяЕдиница);

				$weight = false;
				$width = false;
				$height = false;
				$length = false;
				if(isset($xml_product->ЗначенияРеквизитов->ЗначениеРеквизита))
				foreach ($xml_product->ЗначенияРеквизитов->ЗначениеРеквизита as $xml_option)
				{
					$name = strval($xml_option->Наименование);
					switch ($name)
					{
						case 'Вес':
							$weight = strval($xml_option->Значение);
							break;

						case 'Длина':
							$length = strval($xml_option->Значение);
							break;

						case 'Ширина':
							$width = strval($xml_option->Значение);
							break;

						case 'Высота':
							$height = strval($xml_option->Значение);
							break;
					}
				}

				if(! $id)
				{
					$id = DB::query("INSERT INTO {shop} ([name], [text], article, [measure_unit], import_id, cat_id, site_id, timeedit, brand_id, [act], weight, width, height, length) VALUES ('%h', '%s', '%h', '%h', '%h', %d, %d, %d, %d, '%d', %f, %f, %f, %f)", $xml_product->Наименование, $description, $xml_product->Артикул, $measure_unit, $good_id_1c, $cat_id, $site_id, time(), $brand_id, ($this->diafan->configmodules("1c_act", "shop") ? 1 : 0), $weight, $width, $height, $length);
					if($cat_id)
					{
						DB::query("INSERT INTO {shop_category_rel} (cat_id, element_id) VALUES (%d, %d)", $cat_id, $id);
					}

					//ЧПУ
					if(ROUTE_AUTO_MODULE)
					{
						$this->diafan->_route->save('', $xml_product->Наименование, $id, 'shop', 'element', $site_id, $cat_id);
					}

					// если товары будут активироваться на сайте сразу
					// ссылка на карте сайта
					if($this->diafan->configmodules("1c_act", "shop") && in_array("map", $this->diafan->installed_modules))
					{
						$shop_row = array(
							"module_name" => 'shop',
							"id"          => $id,
							"site_id"     => $site_id,
							"cat_id"      => $cat_id,
						);
						$this->diafan->_map->index_element($shop_row);
					}

					$this->import_img($xml_product, $id, $site_id);
				}
				else
				{
					$edit = false;
					if($cat_id != $row["cat_id"] && $cat_id)
					{
						if($cat_id)
						{
							DB::query("DELETE FROM {shop_category_rel} WHERE element_id=%d", $id);
							DB::query("INSERT INTO {shop_category_rel} (cat_id, element_id) VALUES (%d, %d)", $cat_id, $id);
						}
						$edit = true;
					}
					else
					{
						$cat_id = $row["cat_id"];
					}
					if($brand_id && $brand_id != $row["brand_id"])
					{
						$edit = true;
					}
					if($this->import_img($xml_product, $id, $site_id))
					{
						$edit = true;
					}
					if($weight !== false && $weight != $row["weight"])
					{
						$edit = true;
					}
					if($width !== false && $width != $row["width"])
					{
						$edit = true;
					}
					if($height !== false && $height != $row["height"])
					{
						$edit = true;
					}
					if($length !== false && $length != $row["length"])
					{
						$edit = true;
					}

					if($edit
					   || $row["name"] != strval($xml_product->Наименование)
					   || $description && $row["text"] != $description
					   || $row["article"] != strval($xml_product->Артикул)
					   || $row["measure_unit"] != $measure_unit)
					{
						$set = "[name]='%h', article='%h', cat_id=%d, timeedit=%d";
						$vs = array($xml_product->Наименование, $xml_product->Артикул, $cat_id, time());
						// если в системе 1C описание не задано, то оно не должно затирать заданное на сайте
						if($description)
						{
							$set .= ", [text]='%s'";
							$vs[] = $description;
						}
						if($brand_id)
						{
							$set .= ", brand_id=%d";
							$vs[] = $brand_id;
						}
						if($measure_unit)
						{
							$set .= ", [measure_unit]='%h'";
							$vs[] = $measure_unit;
						}
						if($weight !== false)
						{
							$set .= ", weight=%f";
							$vs[] = $weight;
						}
						if($width !== false)
						{
							$set .= ", width=%f";
							$vs[] = $width;
						}
						if($height !== false)
						{
							$set .= ", height=%f";
							$vs[] = $height;
						}
						if($length !== false)
						{
							$set .= ", length=%f";
							$vs[] = $length;
						}
						$vs[] = $id;
						DB::query("UPDATE {shop} SET ".$set." WHERE id=%d", $vs);
					}
				}
				$this->cache["session"]["goods"][$good_id_1c] = $id;

				// дополнительные характеристики
				if(isset($xml_product->ЗначенияСвойств->ЗначенияСвойства))
				foreach ($xml_product->ЗначенияСвойств->ЗначенияСвойства as $xml_option)
				{
					if(! empty($this->cache["session"]["params"][strval($xml_option->Ид)]))
					{
						$param_id = $this->cache["session"]["params"][strval($xml_option->Ид)]["id"];
						if($cat_id)
						{
							if(empty($this->cache["session"]["params_cats"][$param_id][$cat_id]) && empty($this->cache["session"]["params_cats"][$param_id][0]))
							{
								DB::query("INSERT INTO {shop_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $param_id, $cat_id);
								$this->cache["session"]["params_cats"][$param_id][$cat_id] = true;
							}
						}
						DB::query("DELETE FROM {shop_param_element} WHERE param_id=%d AND element_id=%d", $param_id, $id);
						$i = 0;
						foreach ($xml_option->Значение as $xml_value)
						{
							$xml_value = strval($xml_value);
							if(! empty($xml_value))
							{
								if($this->cache["session"]["params"][strval($xml_option->Ид)]["type"] == 'select' || $this->cache["session"]["params"][strval($xml_option->Ид)]["type"] == 'multiple')
								{
									$val = $this->cache["session"]["params"][strval($xml_option->Ид)]["values"][$xml_value];
								}
								else
								{
									$val = $xml_value;
								}
								DB::query("INSERT INTO {shop_param_element} (param_id, element_id, [value]) VALUES (%d, %d, '%s')", $param_id, $id, $val);
								$i++;
							}
						}
						if($i > 1 && $this->cache["session"]["params"][strval($xml_option->Ид)]["type"] == 'select')
						{
							$this->cache["session"]["params"][strval($xml_option->Ид)]["type"] = 'multiple';
							DB::query("UPDATE {shop_param} SET type='multiple' WHERE id=%d", $param_id);
						}
					}
				}

				if(isset($xml_product->ХарактеристикиТовара->ХарактеристикаТовара))
				{
					foreach ($xml_product->ХарактеристикиТовара->ХарактеристикаТовара as $xml_property)
					{
						$name = strval($xml_property->Наименование);
						if(! isset($this->cache["session"]["1_params"][$name]) && ! isset($this->cache["session"]["1_params_text"][$name]))
						{
							$r = DB::query_fetch_array("SELECT id, required, type FROM {shop_param} WHERE [name]='%h' AND trash='0' LIMIT 1", $name);
							if(! $r)
							{
								$r["id"] = DB::query("INSERT INTO {shop_param} ([name], type, id_page) VALUES ('%h', 'text', '1')", $xml_property->Наименование);
								$r["type"] = 'text';
								if($cat_id)
								{
									DB::query("INSERT INTO {shop_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $r["id"], $cat_id);
									$this->cache["session"]["params_cats"][$r["id"]][$cat_id] = true;
								}
							}
							if($r["type"] == 'select' || $r["type"] == 'multiple')
							{
								$this->cache["session"]["1_params"][$name] = $r["id"];
							}
							else
							{
								$this->cache["session"]["1_params_text"][$name] = $r["id"];
							}
						}
						if(isset($this->cache["session"]["1_params"][$name]))
						{
							$param_id = $this->cache["session"]["1_params"][$name];
							$param_value = strval($xml_property->Значение);
							if(! isset($this->cache["session"]["params_select"][$param_id][$param_value]))
							{
								$r_v = DB::query_result("SELECT id FROM {shop_param_select} WHERE param_id=%d AND [name]='%h' AND trash='0' LIMIT 1", $param_id, $param_value);
								if(! $r_v)
								{
									$r_v = DB::query("INSERT INTO {shop_param_select} ([name], param_id) VALUES ('%h', %d)", $param_value, $param_id);
								}
								$this->cache["session"]["params_select"][$param_id][$param_value] = $r_v;
							}
							DB::query("DELETE FROM {shop_param_element} WHERE param_id=%d AND element_id=%d", $param_id, $id);
							$value = $this->cache["session"]["params_select"][$param_id][$param_value];
							if($value)
							{
								DB::query("INSERT INTO {shop_param_element} (param_id, element_id, [value]) VALUES (%d, %d, '%s')", $param_id, $id, $value);
							}
						}
						if(isset($this->cache["session"]["1_params_text"][$name]))
						{
							$param_id = $this->cache["session"]["1_params_text"][$name];
							$param_value = strval($xml_property->Значение);
							DB::query("DELETE FROM {shop_param_element} WHERE param_id=%d AND element_id=%d", $param_id, $id);
							DB::query("INSERT INTO {shop_param_element} (param_id, element_id, [value]) VALUES (%d, %d, '%s')", $param_id, $id, $param_value);
						}
						if($cat_id)
						{
							if(empty($this->cache["session"]["params_cats"][$param_id][$cat_id]) && empty($this->cache["session"]["params_cats"][$param_id][0]))
							{
								DB::query("INSERT INTO {shop_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $param_id, $cat_id);
								$this->cache["session"]["params_cats"][$param_id][$cat_id] = true;
							}
						}
					}
				}

				if(isset($xml_product->ЗначенияРеквизитов->ЗначениеРеквизита))
				foreach ($xml_product->ЗначенияРеквизитов->ЗначениеРеквизита as $xml_option)
				{
					$name = strval($xml_option->Наименование);
					if($name == 'Файл' && file_exists(ABSOLUTE_PATH.'tmp/1c/'.strval($xml_option->Значение)))
					{
						if(empty($this->cache["params_name"][$name]))
						{
							$this->cache["params_name"][$name] = DB::query_result("SELECT id FROM {shop_param} WHERE [name]='%h' AND type='attachments' AND trash='0'", $name);
							if(! $this->cache["params_name"][$name])
							{
								$this->cache["params_name"][$name] = DB::query("INSERT INTO {shop_param} ([name], type, id_page) VALUES ('%h', 'attachments', '1')", $name);
								DB::query("INSERT INTO {shop_param_category_rel} (element_id) VALUES (%d)", $this->cache["params_name"][$name]);
							}
						}
						$config = array(
							"attachment_extensions" => '',
							"recognize_image" => false,
							"attachments_access_admin" => false,
							"attach_big_width" => 0,
							"attach_big_height" => 0,
							"attach_big_quality" => 0,
							"attach_medium_width" => 0,
							"attach_medium_height" => 0,
							"attach_medium_quality" => 0,
							"param_id" => $this->cache["params_name"][$name],
						);
						$file = array(
							'name' => substr(strrchr(strval($xml_option->Значение), '/'), 1),
							'tmp_name' => ABSOLUTE_PATH.'tmp/1c/'.strval($xml_option->Значение),
							'type' => '',
						);
						$this->diafan->_attachments->upload($file, 'shop', $id, false, $config);
						if(empty($_GET["no_delete"]))
						{
							unlink($file['tmp_name']);
						}
					}
				}

				/* дополнительные характеристики
				if(isset($xml_product->ЗначенияРеквизитов->ЗначениеРеквизита))
				foreach ($xml_product->ЗначенияРеквизитов->ЗначениеРеквизита as $xml_option)
				{
					$name = strval($xml_option->Наименование);

					if(in_array($name,  array('ТипНоменклатуры', 'ВидНоменклатуры')))
						continue;

					if(empty($this->cache["params_name"][$name]))
					{
						$this->cache["params_name"][$name] = DB::query_result("SELECT id FROM {shop_param} WHERE [name]='%h' AND trash='0'", $name);
						if(! $this->cache["params_name"][$name])
						{
							$this->cache["params_name"][$name] = DB::query("INSERT INTO {shop_param} ([name], type, id_page) VALUES ('%h', 'text', '1')", $name);
						}
					}
					$param_id = $this->cache["params_name"][$name];
					if($cat_id)
					{
						if(empty($this->cache["session"]["params_cats"][$param_id][$cat_id]) && empty($this->cache["session"]["params_cats"][$param_id][0]))
						{
							DB::query("INSERT INTO {shop_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $param_id, $cat_id);
							$this->cache["session"]["params_cats"][$param_id][$cat_id] = true;
						}
					}
					DB::query("DELETE FROM {shop_param_element} WHERE param_id=%d AND element_id=%d", $param_id, $id);
					foreach ($xml_option->Значение as $xml_value)
					{
						DB::query("INSERT INTO {shop_param_element} (param_id, element_id, [value]) VALUES (%d, %d, '%s')", $param_id, $id, strval($xml_value));
					}
				}*/
			}
		}
	}

	/**
	 * Импорт изображений
	 *
	 * @param object $xml_product данные о товаре из 1C
	 * @param integer $id идентификатор товара
	 * @param integer $site_id раздел сайта, к которому прикреплен товар
	 * @return boolean
	 */
	private function import_img($xml_product, $id, $site_id)
	{
		$edit = false;
		if(isset($xml_product->Картинка))
		{
			$this->diafan->_images->delete($id, 'shop');
			if(is_object($xml_product->Картинка))
			{
				foreach ($xml_product->Картинка as $img)
				{
					$img1 = basename($img);
					$image_address = ABSOLUTE_PATH.'tmp/1c/'.$img1;
					if($img1 && ! file_exists($image_address) && strpos($img1, './') === false)
					{
						$image_address = ABSOLUTE_PATH.'tmp/1c/'.$img;
					}
					if($img1 && file_exists($image_address))
					{
						$image_name = $xml_product->Наименование ? preg_replace('/[^A-Za-z0-9-_]+/', '', strtolower($this->diafan->translit(substr($xml_product->Наименование, 0, 50)))) : $id;
						try
						{
							$this->diafan->_images->upload($id, 'shop', 'element', $site_id, $image_address, $image_name);
						}
						catch (Exception $e)
						{
							if($this->diafan->configmodules("1c_write_log", "shop"))
							{
								$this->diafan->configmodules("1c_log", "shop", 0, false, $this->diafan->configmodules("1c_log", "shop").$img.': '.$e->getMessage()."\n");
							}
						}
						$edit = true;
						if(empty($_GET["no_delete"]))
						{
							unlink($image_address);
						}
					}
				}
			}
			else
			{
				$img = basename($xml_product->Картинка);
				$image_address = ABSOLUTE_PATH.'tmp/1c/'.$img;
				if(!empty($xml_product->Картинка) && is_file($image_address))
				{
					$image_name = $xml_product->Наименование ? preg_replace('/[^A-Za-z0-9-_]+/', '', strtolower($this->diafan->translit(substr($xml_product->Наименование, 0, 50)))) : $id;
					$this->diafan->_images->upload($id, 'shop', 'element', $site_id, $image_address, $image_name);
					$edit = true;
					if(empty($_GET["no_delete"]))
					{
						unlink($image_address);
					}
				}
			}
		}
		return $edit;
	}

	/**
	 * Импорт цен и количества
	 *
	 * @return void
	 */
	private function import_prices($xml)
	{
		if(! isset($xml->Предложения->Предложение))
			return;

		// определяем идентификатор розничной цены
		if(! isset($this->cache["session"]["price_type_id"]))
		{
			if(! isset($xml->ТипыЦен) || ! isset($xml->ТипыЦен->ТипЦены))
			 return;

			 $price_type = $price_type_another = false;
			foreach ($xml->ТипыЦен->ТипЦены as $xml_price_type)
			{
				switch($xml_price_type->Наименование)
				{
					case 'Розничная':
						$price_type = (string) $xml_price_type->Ид;
						break;

					default:
						$price_type_another = (string) $xml_price_type->Ид;
						break;
				}
			}
			if($price_type) $this->cache["session"]["price_type_id"] = $price_type;
			elseif($price_type_another) $this->cache["session"]["price_type_id"] = $price_type_another;

			if(! isset($this->cache["session"]["price_type_id"]))
				return;
		}

		if(! isset($this->cache["session"]["good_ids"])) $this->cache["session"]["good_ids"] = array();
		if(! isset($this->cache["session"]["empty_param_prices"])) $this->cache["session"]["empty_param_prices"] = array();
		if(! isset($this->cache["session"]["price_goods"])) $this->cache["session"]["price_goods"] = array();
		foreach ($xml->Предложения->Предложение as $xml_variant)
		{
			if(! $this->check_max())
			{
				continue;
			}

			if(strpos($xml_variant->Ид, '#') !== false)
			{
				list($good_id_1c, $variant_id_1c) = explode('#', $xml_variant->Ид, 2);
			}
			else
			{
				$good_id_1c = $xml_variant->Ид;
				$variant_id_1c = '';
			}

			$row = DB::query_fetch_array("SELECT * FROM {shop} WHERE import_id='%s' AND trash='0' LIMIT 1", $good_id_1c);
			if(! $row)
				continue;

			if(empty($xml_variant->Цены))
			{
				// если не переданы цены, то удаляем их или, если отмечено "Учитывать остатки товаров на складе", выставляем количество = 0
				$prices = DB::query_fetch_value("SELECT id FROM {shop_price} WHERE good_id='%d' AND trash='0'", $row["id"], "id");
				if(! empty($prices))
				{
					$prices = implode(",", $prices);
					if($this->diafan->configmodules("use_count_goods", "shop", $row["site_id"]))
					{
						DB::query("UPDATE {shop_price} SET count_goods=0 WHERE count_goods=1 AND id IN (%s)", $prices);
					}
					else
					{
						DB::query("DELETE FROM {shop_price} WHERE id IN (%s)", $prices);
						DB::query("DELETE FROM {shop_price_param} WHERE price_id IN (%s)", $prices);
						DB::query("DELETE FROM {shop_price_image_rel} WHERE price_id IN (%s)", $prices);
					}
				}
				continue;
			}

			// находим розничную цену согласно идентификатора
			$xml_price = false;
			foreach ($xml_variant->Цены->Цена as $xml_price_variant)
			{
				if((string) $xml_price_variant->ИдТипаЦены != $this->cache["session"]["price_type_id"])
					continue;

				$xml_price = $xml_price_variant;
				break;
			}
			if(! $xml_price)
				return;

			$currency_id = 0;
			if(! empty($xml_price->Валюта))
			{
				$currency_id = DB::query_result("SELECT id FROM {shop_currency} WHERE  name='%h' AND trash='0' LIMIT 1", $xml_price->Валюта);
			}
			$params = $this->price_param($xml_variant, $row["cat_id"]);
			// новый формат выгрузки характеристик, влияющих на цену у 1С:
			// характеристики нигде не выгружаются, только для предложения в скобках рядом с названием цены указано название характеристики
			// нужно предварительно создать характеристику, влияющую на цену на сайте
			if($variant_id_1c && ! $params && preg_match('/^'.preg_quote($row["name"._LANG], '/').' \((.*)\)$/', $xml_variant->Наименование, $m))
			{
				$p = DB::query_fetch_array("SELECT s.id, s.param_id FROM {shop_param_select} AS s INNER JOIN {shop_param} AS p ON p.id=s.param_id INNER JOIN {shop_param_category_rel} AS c ON c.element_id=p.id WHERE p.required='1' AND p.trash='0' AND s.[name]='%s' AND (c.cat_id=0 OR c.cat_id=%d)", $m[1], $row["cat_id"]);
				if($p)
				{
					$params[$p["param_id"]] = $p["id"];
				}
				else
				{
					if(! isset($this->cache["session"]["1_params"]['Тип']))
					{
						$r = DB::query_fetch_array("SELECT id, required FROM {shop_param} WHERE [name]='Тип' AND type='multiple' AND trash='0' LIMIT 1");
						if(! $r)
						{
							$r["id"] = DB::query("INSERT INTO {shop_param} ([name], type, required, id_page) VALUES ('Тип', 'multiple', '1', '1')");
							$this->cache["session"]["multiple_params"][$r["id"]] = array("id" => $r["id"], "cats" => array($row["cat_id"]));
							DB::query("INSERT INTO {shop_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $r["id"], $row["cat_id"]);
						}
						elseif(! $r["required"])
						{
							DB::query("UPDATE {shop_param} SET required='1' WHERE id=%d", $r["id"]);
						}
						$this->cache["session"]["1_params"]['Тип'] = $r["id"];
					}
					if($param_id = $this->cache["session"]["1_params"]['Тип'])
					{
						$param_value = $m[1];
						if(! isset($this->cache["session"]["params_select"][$param_id][$param_value]))
						{
							$r_v = DB::query_result("SELECT id FROM {shop_param_select} WHERE param_id=%d AND [name]='%h' AND trash='0' LIMIT 1", $param_id, $param_value);
							if(! $r_v)
							{
								$r_v = DB::query("INSERT INTO {shop_param_select} ([name], param_id) VALUES ('%h', %d)", $param_value, $param_id);
							}
							$this->cache["session"]["params_select"][$param_id][$param_value] = $r_v;
						}
						$value = $this->cache["session"]["params_select"][$param_id][$param_value];
						if($value)
						{
							$params[$param_id] = $value;
						}
						if(empty($this->cache["session"]["multiple_params"][$param_id]))
						{
							$this->cache["session"]["multiple_params"][$param_id] = array("id" => $param_id, "cats" => array($row["cat_id"]));
						}
						if(! in_array($row["cat_id"], $this->cache["session"]["multiple_params"][$param_id]["cats"]) && ! in_array(0, $this->cache["session"]["multiple_params"][$param_id]["cats"]))
						{
							$this->cache["session"]["multiple_params"][$param_id]["cats"][] = $row["cat_id"];
							DB::query("INSERT INTO {shop_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $param_id, $row["cat_id"]);
						}
					}
				}
			}
			$val_params = $params;
			$this->get_empty_param($params, $row["cat_id"]);
			if(! $variant_id_1c && ! empty($xml_price->ИдТипаЦены))
			{
				$variant_id_1c = $xml_price->ИдТипаЦены;
			}

			$price_id = $this->diafan->_shop->price_insert($row["id"], $xml_price->ЦенаЗаЕдиницу, 0, (! empty($xml_variant->Количество) ? $xml_variant->Количество :  0), $params, $currency_id, $variant_id_1c);

			if((float)$xml_variant->Количество > 0)
			{
				$this->diafan->_shop->price_send_mail_waitlist($row["id"], $params, $row);
			}

			$this->cache["session"]["price_goods"][$row["id"]][] = $price_id;
			if(! in_array($row["id"], $this->cache["session"]["good_ids"]))
			{
				$this->cache["session"]["good_ids"][] = $row["id"];
			}
			// если параметры пустые, запоминаем цену
			if(empty($val_params))
			{
				$this->cache["session"]["empty_param_prices"][$row["id"]] = $price_id;
			}
		}
		foreach ($this->cache["session"]["good_ids"] as $good_id)
		{
			$del_prices = DB::query_result("SELECT GROUP_CONCAT(id SEPARATOR ',') FROM {shop_price} WHERE good_id=%d AND id NOT IN (".implode(",", $this->cache['session']['price_goods'][$good_id]).")", $good_id);
			//если цен у товара несколько и есть цена без параметров, то удаляем ее
			if(count($this->cache["session"]["price_goods"][$good_id]) > 1 &&  ! empty($this->cache["session"]["empty_param_prices"][$good_id]))
			{
				$del_prices .= ($del_prices ? ',' : '').$this->cache["session"]["empty_param_prices"][$good_id];
			}
			if($del_prices)
			{
				if(count($del_prices) > 100)
				{
					for($i = 0; $i++; $i < count($del_prices))
					{
						if(isset($dp) && count($dp) == 100)
						{
							DB::query("DELETE FROM {shop_price} WHERE price_id IN (".$dp.")");
							DB::query("DELETE FROM {shop_price_param} WHERE price_id IN (".$dp.")");
							DB::query("DELETE FROM {shop_price_image_rel} WHERE price_id IN (".$dp.")");
							$dp = array();
						}
						$dp[] = $del_prices[$i];
					}
				}
				else
				{
					DB::query("DELETE FROM {shop_price} WHERE id IN (".$del_prices.")");
					DB::query("DELETE FROM {shop_price_param} WHERE price_id IN (".$del_prices.")");
					DB::query("DELETE FROM {shop_price_image_rel} WHERE price_id IN (".$del_prices.")");
				}
			}
			$this->diafan->_shop->price_calc($good_id);
		}
	}

	private function price_param($xml_product, $cat_id)
	{
		if(! isset($this->cache["session"]["multiple_params"]))
		{
			$this->cache["session"]["multiple_params"] = array();
			$rows = DB::query_fetch_all("SELECT id FROM {shop_param} WHERE type='multiple' AND required='1' AND trash='0'");
			foreach ($rows as $row)
			{
				$row["cats"] = DB::query_fetch_value("SELECT cat_id FROM {shop_param_category_rel} WHERE element_id=%d AND trash='0'", $row["id"], "cat_id");
				if(! $row["cats"])
				{
					$row["cats"][] = 0;
				}
				$this->cache["session"]["multiple_params"][$row["id"]] = $row;
			}
		}

		$params = array();
		if(isset($xml_product->ХарактеристикиТовара->ХарактеристикаТовара))
		{
			foreach ($xml_product->ХарактеристикиТовара->ХарактеристикаТовара as $xml_property)
			{
				$name = strval($xml_property->Наименование);
				if(! isset($this->cache["session"]["1_params"][$name]))
				{
					$r = DB::query_fetch_array("SELECT id, required, type FROM {shop_param} WHERE [name]='%h' AND (type='multiple' OR type='select') AND trash='0' LIMIT 1", $name);
					if(! $r)
					{
						$r["id"] = DB::query("INSERT INTO {shop_param} ([name], type, required, id_page) VALUES ('%h', 'multiple', '1', '1')", $xml_property->Наименование);
						$this->cache["session"]["multiple_params"][$r["id"]] = array("id" => $r["id"], "cats" => array($cat_id));
						DB::query("INSERT INTO {shop_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $r["id"], $cat_id);
					}
					elseif($r["type"] == 'select' || ! $r["required"])
					{
						DB::query("UPDATE {shop_param} SET type='multiple', required='1' WHERE id=%d", $r["id"]);
					}
					$this->cache["session"]["1_params"][$name] = $r["id"];
				}
				if($param_id = $this->cache["session"]["1_params"][$name])
				{
					$param_value = strval($xml_property->Значение);
					if(! isset($this->cache["session"]["params_select"][$param_id][$param_value]))
					{
						$r_v = DB::query_result("SELECT id FROM {shop_param_select} WHERE param_id=%d AND [name]='%h' AND trash='0' LIMIT 1", $param_id, $param_value);
						if(! $r_v)
						{
							$r_v = DB::query("INSERT INTO {shop_param_select} ([name], param_id) VALUES ('%h', %d)", $param_value, $param_id);
						}
						$this->cache["session"]["params_select"][$param_id][$param_value] = $r_v;
					}
					$value = $this->cache["session"]["params_select"][$param_id][$param_value];
					if($value)
					{
						$params[$param_id] = $value;
					}
					if(empty($this->cache["session"]["multiple_params"][$param_id]))
					{
						$this->cache["session"]["multiple_params"][$param_id] = array("id" => $param_id, "cats" => array($cat_id));
					}
					if(! in_array($cat_id, $this->cache["session"]["multiple_params"][$param_id]["cats"]) && ! in_array(0, $this->cache["session"]["multiple_params"][$param_id]["cats"]))
					{
						$this->cache["session"]["multiple_params"][$param_id]["cats"][] = $cat_id;
						DB::query("INSERT INTO {shop_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $param_id, $cat_id);
					}
				}
			}
		}
		return $params;
	}

	private function get_empty_param(&$params, $cat_id)
	{
		$current_params = array();
		foreach ($this->cache["session"]["multiple_params"] as $p)
		{
			if(in_array(0, $p["cats"]) || in_array($cat_id, $p["cats"]))
			{
				if(!in_array($p["id"], array_keys($params)))
				{
					$params[$p["id"]] = 0;
				}
				$current_params[] = $p["id"];
			}
		}
		foreach ($params as $p => $v)
		{
			if(! in_array($p, $current_params))
			{
				if(in_array($p, array_keys($this->cache["session"]["multiple_params"])))
				{
					DB::query("INSERT INTO {shop_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $p, $cat_id);
					$this->cache["session"]["multiple_params"][$p]["cats"][] = $cat_id;
				}
				else
				{
					DB::query("INSERT INTO {shop_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $p, $cat_id);
					DB::query("UPDATE {shop_param} SET type='multiple', required='1' WHERE id=%d", $p);
					$this->cache["session"]["multiple_params"][$p] = array("id" => $p, "cats" => array($cat_id));
				}
			}
		}
	}

	/**
	 * Удаление товара
	 *
	 * @param integer идентификатор товара
	 * @return void
	 */
	private function delete_good($id)
	{
		DB::query("DELETE FROM {shop_category_rel} WHERE element_id=%d", $id);
		DB::query("DELETE FROM {shop_rel} WHERE element_id=%d OR rel_element_id=%d", $id, $id);
		DB::query("DELETE FROM {shop_cart} WHERE good_id=%d", $id);
		DB::query("DELETE FROM {shop_wishlist} WHERE good_id=%d", $id);
		DB::query("DELETE FROM {shop_waitlist} WHERE good_id=%d", $id);
		DB::query("DELETE FROM {shop_price_param} WHERE price_id IN (SELECT price_id FROM {shop_price} WHERE good_id=%d)", $id);
		DB::query("DELETE FROM {shop_price} WHERE good_id=%d", $id);
		DB::query("DELETE FROM {shop_param_element} WHERE element_id=%d", $id);
		DB::query("DELETE FROM {shop_discount_object} WHERE good_id=%d", $id);
		DB::query("DELETE FROM {access} WHERE element_id=%d AND module_name='shop' AND element_type='element'", $id);

		$this->diafan->_comments->delete($id, 'shop');
		$this->diafan->_tags->delete($id, 'shop');
		$this->diafan->_rating->delete($id, 'shop');
		$this->diafan->_map->delete($id, 'shop');
		$this->diafan->_images->delete($id, 'shop');
		$this->diafan->_menu->delete($id, 'shop');
		$this->diafan->_route->delete($id, 'shop');

		$this->diafan->_attachments->delete($id, 'shop');

		DB::query("DELETE FROM {shop} WHERE id=%d", $id);
	}

	/**
	 * Проверяет достижение максимума обрабытываемых записей за один проход скрипта
	 *
	 * @return void
	 */
	private function check_max()
	{
		$this->i++;
		if($this->i < $this->min_i)
		{
			return false;
		}
		if($this->i == $this->max_i + 1)
		{
			$this->diafan->configmodules("1c_progress_i", "shop", 0, false, $this->i);
			echo 'progress
			Выгружено '.($this->i - 1);
			exit;
		}
		return true;
	}
}

$shop_1c = new Shop_1c($this->diafan);
$shop_1c->start();

exit;
