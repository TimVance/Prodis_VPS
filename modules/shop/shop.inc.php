<?php
/**
 * Подключение модуля «Магазин» для работы с ценами
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
if ( ! defined('DIAFAN'))
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
 * Shop_inc
 */
class Shop_inc extends Diafan
{
	/**
	 * @var array идентификаторы персональных скидок текущего пользователя
	 */
	private $person_discount_ids = false;

	/**
	 * Получает цену товара с указанными параметрами для пользователя
	 *
	 * @param integer $good_id номер товара
	 * @param array $params параметры, влияющие на цену
	 * @param boolean $current_user текущий пользователь
	 * @return array
	 */
	public function price_get($good_id, $params, $current_user = true)
	{
		if($current_user)
		{
			$person_discount_ids = $this->price_get_person_discounts();
			$role_id = $this->diafan->_users->role_id;
		}
		else
		{
			$person_discount_ids = false;
			$role_id = 0;
		}
		$where = array();
		foreach ($params as $id => $value)
		{
			$where[] = "s.param_id=".intval($id)." AND (s.param_value=".intval($value)." OR s.param_value=0)";
		}
		$price = DB::query_fetch_array("SELECT id, price_id, count_goods, price, old_price, discount_id FROM {shop_price} AS p WHERE good_id=%d"
			.($where ? " AND (SELECT COUNT(*) FROM {shop_price_param} AS s WHERE p.price_id=s.price_id AND (".implode(" OR ", $where).")) = ".count($params) : "")
			." AND currency_id=0"
			." AND role_id".($role_id ? " IN (0,".$role_id.")" : "=0")
			." AND date_start<=%d AND (date_finish=0 OR date_finish>=%d)"
			." AND (person='0'".($person_discount_ids ? " OR discount_id IN(".implode(",", $person_discount_ids).")" : "").")"
			." AND trash='0' ORDER BY price LIMIT 1",
			$good_id, time(), time());
		
		$this->cache["id"][$price["price_id"]] = $price;

		return $price;
	}

	/**
	 * Получает цену товара по идентификатору
	 *
	 * @param integer $price_id идентификатор базовой цены из таблицы {shop_price}
	 * @param boolean $current_user текущий пользователь
	 * @return array
	 */
	public function price_get_id($price_id, $current_user = true)
	{
		if($current_user)
		{
			$person_discount_ids = $this->price_get_person_discounts();
			$role_id = $this->diafan->_users->role_id;
		}
		else
		{
			$person_discount_ids = false;
			$role_id = 0;
		}
		if(! isset($this->cache["id"][$price_id]))
		{
			$this->price_prepare_id($price_id);
			$rows = DB::query_fetch_key("SELECT * FROM {shop_price} AS p WHERE price_id IN (%s)"
			." AND currency_id=0"
			." AND role_id".($role_id ? " IN (0,".$role_id.")" : "=0")
			." AND date_start<=%d AND (date_finish=0 OR date_finish>=%d)"
			." AND (person='0'".($person_discount_ids ? " OR discount_id IN(".implode(",", $person_discount_ids).")" : "").")"
			." AND trash='0' ORDER BY price DESC",
			implode(',', array_keys($this->cache["prepare_id"])), time(), time(), "price_id");
			foreach($this->cache["prepare_id"] as $p_id => $dummy)
			{
				if(! empty($rows[$p_id]))
				{
					$this->cache["id"][$p_id] = $rows[$p_id];
				}
				else
				{
					$this->cache["id"][$p_id] = false;
				}
			}
		}
		return $this->cache["id"][$price_id];
	}

	/**
	 * Подготавливает цены по идентификатору для пользователя
	 *
	 * @param integer $price_id идентификтор базовой цены из таблицы {shop_price}
	 * @return void
	 */
	public function price_prepare_id($price_id)
	{
		if(! isset($this->cache["prepare_id"][$price_id]) && ! isset($this->cache["id"][$price_id]))
		{
			$this->cache["prepare_id"][$price_id] = true;
		}
	}

	/**
	 * Возвращает идентификаторы персональных скидок, применимые для текущего пользователя
	 *
	 * @return array
	 */
	public function price_get_person_discounts()
	{
		if($this->person_discount_ids !== false)
		{
			return $this->person_discount_ids;
		}
		$this->person_discount_ids = DB::query_fetch_value("SELECT discount_id FROM {shop_discount_person} WHERE trash='0' AND used='0' AND (session_id='%s'".($this->diafan->_users->id ? " OR user_id=%d" : "").")", $this->diafan->_session->id, $this->diafan->_users->id, "discount_id");
		if($this->diafan->_users->role_id)
		{
			$role_discount_ids = DB::query_fetch_value("SELECT id FROM {shop_discount} WHERE trash='0' AND role_id=%d", $this->diafan->_users->role_id, "id");
			foreach($role_discount_ids as $id)
			{
				if(! in_array($id, $this->person_discount_ids))
				{
					$this->person_discount_ids[] = $id;
				}
			}
		}
		return $this->person_discount_ids;
	}

	/**
	 * Получает все цены товара для пользователя
	 *
	 * @param integer $good_id номер товара
	 * @param integer $current_user пользователь, для которого определяется цена
	 * @return array
	 */
	public function price_get_all($good_id, $current_user = true)
	{
		$this->price_prepare_all($good_id);
		if(! empty($this->cache["prepare_all"]))
		{
			foreach($this->cache["prepare_all"] as $g_id => $dummy)
			{
				$this->cache["all"][$g_id] = array();
			}
			$role_id = 0;
			$price_ids = array();
			// показывает только цены, заданные условием, если это контент модуля, а не шаблонная функция
			$pr1 = ! empty($_REQUEST["pr1"]) && ! $this->diafan->_parser_theme->is_tag ? intval($_REQUEST["pr1"]) : 0;
			$pr2 = ! empty($_REQUEST["pr2"]) && ! $this->diafan->_parser_theme->is_tag ? intval($_REQUEST["pr2"]) : 0;
			if($current_user == $this->diafan->_users->id && $this->diafan->_users->id)
			{
				$person_discount_ids = $this->price_get_person_discounts();
				$role_id = $this->diafan->_users->role_id;
			}
			else
			{
				$person_discount_ids = false;
				$role_id = 0;
			}
			$result = array();
			// выбирает все цены товара, доступные текущиму типу пользователю, действующие в текущий период времени
			// если действует несколько скидок , выбирает самую выгодную цену
			$all_rows = DB::query_fetch_key_array(
				"SELECT * FROM {shop_price}"
				." WHERE good_id IN (%s) AND trash='0'"
				." AND currency_id=0"
				." AND role_id".($role_id ? " IN (0,".$role_id.")" : "=0")
				." AND date_start<=%d AND (date_finish=0 OR date_finish>=%d)"
				." AND (person='0'".($person_discount_ids ? " OR discount_id IN(".implode(",", $person_discount_ids).")" : "").")"
				." ORDER BY price ASC, id ASC",
				implode(",", array_keys($this->cache["prepare_all"])), time(), time(),
				"good_id");
			foreach ($all_rows as $g_id => $rows)
			{
				$r_null = array();
				$r_not_null = array();
				foreach ($rows as $row)
				{
					if(in_array($row["price_id"], $price_ids))
					{
						continue;
					}
					$price_ids[] = $row["price_id"];
					if($pr1 && $row["price"] < $pr1)
					{
						continue;
					}
					if($pr2 && $row["price"] > $pr2)
					{
						continue;
					}
					if($row["price"])
					{
						$r_not_null[] = $row;
					}
					else
					{
						$r_null[] = $row;
					}
				}
				$c_not_null = array();
				$c_null = array();
				foreach ($r_not_null as $val)
				{
					if ($val["count_goods"])
					{
						$c_not_null[] = $val;
					}
					else
					{
						$c_null[] = $val;
					}
				}
				$this->cache["all"][$g_id] = array_merge($c_not_null, $c_null, $r_null);

			}
			unset($this->cache["prepare_all"]);
		}
		if(! isset($this->cache["all"][$good_id]))
		{
			$this->cache["all"][$good_id] = array();
		}
		return $this->cache["all"][$good_id];
	}

	/**
	 * Подготавливает все цены товара для пользователя
	 *
	 * @param integer $good_id номер товара
	 * @return void
	 */
	public function price_prepare_all($good_id)
	{
		if(! isset($this->cache["prepare_all"][$good_id]) && ! isset($this->cache["all"][$good_id]))
		{
			$this->cache["prepare_all"][$good_id] = true;
		}
	}

	/**
	 * Получает основы для цен на товар (используется для администрирования)
	 *
	 * @param integer $good_id номер товара
	 * @param boolean $base_currency показывать результаты в основной валюте
	 * @return array
	 */
	public function price_get_base($good_id, $base_currency = false)
	{
		$this->price_prepare_base($good_id);
		if(! empty($this->cache["prepare_base"]))
		{
			$all_rows = DB::query_fetch_key_array("SELECT id, price_id, price, old_price, currency_id, count_goods, good_id, import_id FROM {shop_price} WHERE good_id IN  (%s) AND trash='0' AND (".(! $base_currency ? "currency_id>0 OR " : '')."price_id=id) ORDER BY currency_id DESC, price ASC, id ASC", implode(",", array_keys($this->cache["prepare_base"])), "good_id");

			$prices = array();
			foreach ($all_rows as $g_id => &$rows)
			{
				foreach ($rows as &$row)
				{
					if(! in_array($row["price_id"], $prices))
					{
						$prices[] = $row["price_id"];
					}
				}
			}

			if($prices)
			{
				$param_rows = DB::query_fetch_all("SELECT param_id, param_value, price_id FROM {shop_price_param} WHERE price_id IN (%s)", implode(",", $prices));
				foreach($param_rows as $param)
				{
					$params[$param["price_id"]][$param["param_id"]] = $param["param_value"];
				}
			}

			$prices = array();
			foreach ($all_rows as $g_id => &$rows)
			{
				foreach ($rows as &$row)
				{
					if(! in_array($row["price_id"], $prices))
					{
						$prices[] = $row["price_id"];
						$row['currency_name'] = $this->price_get_currency_name($row['currency_id']);
						$row["param"] = (! empty($params[$row["price_id"]]) ? $params[$row["price_id"]] : array());
						$this->cache["base"][$g_id][] = $row;
					}
				}
			}
			foreach($this->cache["prepare_base"] as $g_id => $dummy)
			{
				if(! isset($this->cache["base"][$g_id]))
				{
					$this->cache["base"][$g_id] = array();
				}
			}
			unset($this->cache["prepare_base"]);
		}
		return $this->cache["base"][$good_id];
	}

	/**
	 * Подготавливает основы для цен на товар (используется для администрирования)
	 *
	 * @param integer $good_id номер товара
	 * @return array
	 */
	public function price_prepare_base($good_id)
	{
		if(! isset($this->cache["prepare_base"][$good_id]) && ! isset($this->cache["base"][$good_id]))
		{
			$this->cache["prepare_base"][$good_id] = true;
		}
	}

	/**
	 * Получает название валюты по ID
	 *
	 * @param integer $id номер валюты
	 * @return string
	 */
	private function price_get_currency_name($id)
	{
		if(! isset($this->cache["currency_name"]))
		{
			$this->cache["currency_name"] = DB::query_fetch_key_value("SELECT id, name FROM {shop_currency} WHERE trash='0'", "id", "name");
		}

		if(! isset($this->cache["currency_name"][$id]))
		{
			if($id > 0)
			{
				$this->cache["currency_name"][$id] = '';
			}
			else
			{
				$this->cache["currency_name"][$id] = $this->diafan->configmodules("currency");
			}
		}
		return $this->cache["currency_name"][$id];
	}

	/**
	 * Рассчитывает все возможные вариации цен и записывает их в базу данных
	 *
	 * @param integer $good_id номер товара, если не задан, цены рассчитываются для всех товаров
	 * @param integer $discount_id номер скидки
	 * @param integer $currency_id номер валюты, если нужно изменить цены, указанные в валюте
	 * @return void
	 */
	public function price_calc($good_id = 0, $discount_id = 0, $currency_id = 0)
	{
		// сбрасываем локальный кэш файла
		$this->cache = array();

		// пересчитывает цены в основную валюту, если редактируем товар или валюту
		if($currency_id || $good_id)
		{
			// валюты
			$currency = DB::query_fetch_all("SELECT * FROM {shop_currency} WHERE trash='0'".($currency_id ? " AND id=%d" : ""), $currency_id);

			foreach ($currency as $c)
			{
				$rows = DB::query_fetch_all("SELECT * FROM {shop_price} WHERE trash='0'".($good_id ? " AND good_id=".$good_id : '')." AND currency_id=%d", $c["id"]);
				foreach ($rows as $row)
				{
					// удаляет все цены, для которых есть цена в валюте
					DB::query("DELETE FROM {shop_price} WHERE currency_id=0".($good_id ? " AND good_id=".$good_id : '')." AND price_id=%d", $row["price_id"]);
					$new_price = $c["exchange_rate"] * $row["price"];
					$new_old_price = $c["exchange_rate"] * $row["old_price"];
					$price_id = DB::query("INSERT INTO {shop_price} (good_id, price, old_price, count_goods) VALUES (%d, %f, %f, %f)", $row["good_id"], $new_price, $new_old_price, $row["count_goods"]);
					DB::query("UPDATE {shop_price_param} SET price_id=%d WHERE price_id=%d OR price_id=%d", $price_id, $row["price_id"], $row["id"]);
					DB::query("UPDATE {shop_price} SET price_id=%d WHERE id=%d OR price_id=%d", $price_id, $price_id, $row["price_id"]);
					DB::query("UPDATE {shop_price_image_rel} SET price_id=%d WHERE price_id=%d", $price_id, $row["price_id"]);
				}
			}
		}
		// удаляем дубликаты цен в валюте, кроме первой цены
		if($ids = DB::query_fetch_value("SELECT o.id FROM {shop_price} AS o INNER JOIN (SELECT *, COUNT(*) AS `count` FROM {shop_price} WHERE ".($good_id ? "good_id=%d AND " : "")."currency_id<>0 GROUP BY price_id HAVING `count`>1) AS oc ON o.price_id=oc.price_id AND o.id<>oc.id", $good_id, "id"))
		{
			DB::query("DELETE FROM {shop_price} WHERE id IN(%s)", implode(",", $ids));
		}

		// удаляет все цены, сформированные с учетом скидки
		DB::query("DELETE FROM {shop_price} WHERE price_id<>id AND currency_id=0".($good_id ? " AND good_id=".$good_id : '').($discount_id ? " AND discount_id=".$discount_id : ''));

		// скидки
		$time = mktime(0, 0, 0, date("m"), date("d"), date("Y")); // начало текущих суток
		$discounts = DB::query_fetch_all("SELECT d.* FROM {shop_discount} AS d"
		." WHERE act='1' AND (date_finish=0 OR date_finish > %d) AND trash='0' AND threshold_cumulative=0 AND threshold=0".($discount_id ? " AND id=".$discount_id : ''), $time);
		foreach ($discounts as &$d)
		{
			if($d["person"] && ! DB::query_result("SELECT id FROM {shop_discount_person} WHERE discount_id=%d AND used='0' LIMIT 1", $d["id"]))
			{
				continue;
			}
			if($d["date_finish"] && $d["date_finish"] < time())
			{
				continue;
			}
			$d["objects"] = DB::query_fetch_all("SELECT * FROM {shop_discount_object} WHERE discount_id=%d", $d["id"]);
		}
		unset($d);

		// пересчитывает цены с учетом скидки
		if($discounts)
		{
			$rows = DB::query_fetch_all("SELECT p.*, s.cat_id FROM {shop_price} AS p INNER JOIN {shop} AS s ON p.good_id=s.id WHERE p.trash='0'".($good_id ? " AND p.good_id=".$good_id : '')." AND p.price_id=p.id");
			foreach ($rows as $row)
			{
				// категории текущего товара
				$cats = DB::query_fetch_value("SELECT cat_id FROM {shop_category_rel} WHERE element_id=%d", $row["good_id"], "cat_id");
				foreach ($discounts as $d)
				{
					$in_discount = false;
					if(empty($d["objects"][0]) || empty($d["objects"][0]["cat_id"]) && empty($d["objects"][0]["good_id"]))
					{
						$in_discount = true;
					}
					else
					{
						foreach ($d["objects"] as $d_o)
						{
							if($d_o["cat_id"] && in_array($d_o["cat_id"], $cats) || $d_o["good_id"] == $row["good_id"])
							{
								$in_discount = true;
								break;
							}
						}
					}
					if($in_discount)
					{
						$price = $row['price'];
						// скидка действует от суммы
						if (empty($d['amount']) || $price > $d['amount'])
						{
							// фиксированная сумма к вычету
							if ( ! empty($d['deduction']))
							{
								$price -= $d['deduction'];
							}
							else
							{
								$price = $price * (100 - $d['discount']) / 100;
							}
						}
						if($price != $row["price"])
						{
							DB::query("INSERT INTO {shop_price} (good_id, price, old_price, count_goods, price_id, date_start, date_finish, discount, discount_id, person, role_id) VALUES (%d, %f, %f, %f, %d, %d, %d, %f, %d, '%d', %d)", $row["good_id"], $price, $row["price"], $row["count_goods"], $row["id"], $d["date_start"], $d["date_finish"], $d["discount"], $d["id"], $d["person"], $d["role_id"]);
						}
					}
				}
			}
		}
	}

	/**
	 * Добавляет базовую цену для товара
	 *
	 * @param integer $good_id номер товара
	 * @param float $price цена
	 * @param float $old_price старая цена
	 * @param integer $count количество товара
	 * @param integer $params дополнительные характеристики, учитываемые в цене
	 * @param integer $currency_id номер валюты
	 * @param integer $import_id ID цены для импорта
	 * @param integer $image_id ID изображения, прикрепляемого к цене
	 * @return integer
	 */
	public function price_insert($good_id, $price, $old_price, $count, $params = array(), $currency_id = 0, $import_id = '', $image_id = 0)
	{
		// сбрасываем локальный кэш файла
		$this->cache = array();

		if($import_id)
		{
			$row_i = DB::query_fetch_array("SELECT price_id, count_goods FROM {shop_price} WHERE import_id='%h' AND good_id=%d LIMIT 1", $import_id, $good_id);
			if($row_i)
			{
				$q = "price=%f, currency_id=%d, count_goods=%f";
				$v = array($price, $currency_id, $count);
				if($old_price)
				{
					$q .= ", old_price=%f";
					$v[] = $old_price;
				}
				$v[] = $row_i["price_id"];
				DB::query("UPDATE {shop_price} SET ".$q." WHERE id=%d", $v);
				DB::query("DELETE FROM {shop_price_param} WHERE price_id=%d", $row_i["price_id"]);
				foreach ($params as $param_id => $param_value)
				{
					DB::query("INSERT INTO {shop_price_param} (price_id, param_id, param_value) VALUES (%d, %d, %d)", $row_i["price_id"], $param_id, $param_value);
				}
				return $row_i["price_id"];
			}
		}
		$price_id = DB::query("INSERT INTO {shop_price} (price, old_price, currency_id, count_goods, good_id, import_id) VALUES (%f, %f, %d, %f, %d, '%h')", $price, $old_price, $currency_id, $count, $good_id, $import_id);
		DB::query("UPDATE {shop_price} SET price_id=id WHERE id=%d", $price_id);
		if($image_id)
		{
			DB::query("INSERT INTO {shop_price_image_rel} (price_id, image_id) VALUES (%d, %d)", $price_id, $image_id);
		}

		foreach ($params as $id => $value)
		{
			if($value)
			{
				if(! $count = DB::query_result("SELECT COUNT(*) FROM {shop_param_element} WHERE value".$this->diafan->_languages->site."=%d AND param_id=%d AND element_id=%d", $value, $id, $good_id))
				{
					DB::query("INSERT INTO {shop_param_element} (value".$this->diafan->_languages->site.", param_id, element_id)
						VALUES ('%s', %d, %d)", $value, $id, $good_id);
				}
				elseif($count > 1)
				{
					DB::query("DELETE FROM {shop_param_element} WHERE value".$this->diafan->_languages->site."=%d AND param_id=%d AND element_id=%d LIMIT ".($count - 1), $value, $id, $good_id);
				}
			}

			DB::query("INSERT INTO {shop_price_param} (price_id, param_id, param_value)
				VALUES (%d, %d, %d)", $price_id, $id, $value);
		}
		return $price_id;
	}

	/**
	 * Отправляет уведомления о поступлении товара
	 *
	 * @param integer $good_id идентификатор товара
	 * @param array $params дополнительные характеристики, влияющие на цену
	 * @param array $row данные о товаре
	 * @return void
	 */
	public function price_send_mail_waitlist($good_id, $params, $row = array())
	{
		if(! isset($this->cache["waitlist"]))
		{
			$this->cache["waitlist"] = DB::query_fetch_key_array("SELECT * FROM {shop_waitlist} WHERE trash='0'", "good_id");
		}
		if(empty($this->cache["waitlist"][$good_id]))
		{
			return;
		}
		if($params)
		{
			$params2 = array();
			foreach($params as $i => $k)
			{
				$k = intval($k);
				if($k)
				{
					$params2[$i] = $k;
				}
			}
			if($params2)
			{
				asort($params2);
				$param = serialize($params2);
			}
			else
			{
				$param = '';
			}
		}
		else
		{
			$param = '';
		}
		$rs = array();
		foreach($this->cache["waitlist"][$good_id] as $r)
		{
			if(! empty($this->cache["send_mail_waitlist"][$good_id][$r["mail"]]))
				continue;

			if(! $param || $r["param"] == $param || $r["param"] == 'a:0:{}')
			{
				$rs[] = $r;
			}
		}
		if(! $rs)
		{
			return;
		}
		$row["id"] = $good_id;
		$fields = array("site_id", "no_buy");
		foreach($this->diafan->_languages->all as $l)
		{
			$fields[] = "name".$l["id"];
		}
		foreach($fields as $field)
		{
			if(! isset($row[$field]))
			{
				if(! isset($old_row))
				{
					$old_row = DB::query_fetch_array("SELECT * FROM {shop} WHERE id=%d", $good_id);
				}
				$row[$field] = $old_row[$field];
			}
		}
		if($row["no_buy"])
		{
			return;
		}
		$email = ($this->diafan->configmodules("emailconf", 'shop', $row["site_id"])
				   && $this->diafan->configmodules("email", 'shop', $row["site_id"])
				   ? $this->diafan->configmodules("email", 'shop', $row["site_id"]) : EMAIL_CONFIG );

		foreach ($rs as $r)
		{
			if(! empty($this->cache["send_mail_waitlist"][$row["id"]][$r["mail"]]))
				continue;

			$this->cache["send_mail_waitlist"][$row["id"]][$r["mail"]] = true;

			if(! isset($subject[$r["lang_id"]]))
			{
				$subject[$r["lang_id"]] =
				str_replace(
					array (
						'%title',
						'%url'
					), array (
						TITLE,
						BASE_URL
					),
					$this->diafan->configmodules('subject_waitlist', 'shop', $row["site_id"], $r["lang_id"]));

				$link = BASE_PATH;
				foreach($this->diafan->_languages->all as $l)
				{
					if($r["lang_id"] == $l["id"] && ! $l["base_site"])
					{
						$link .= $l["shortname"].'/';
					}
				}
				$link .= $this->diafan->_route->link($row["site_id"], $row["id"], "shop");
				if($params)
				{
					$i = 0;
					foreach($params as $k => $v)
					{
						if($v)
						{
							$link .= ($i ? '&' : '?').'p'.$k.'='.$v;
							$i++;
						}
					}
				}

				$message[$r["lang_id"]] = str_replace(
					array (
						'%title',
						'%url',
						'%good',
						'%link',
					), array (
						TITLE,
						BASE_URL,
						$row["name".$r["lang_id"]],
						$link,
					), $this->diafan->configmodules('message_waitlist', 'shop', $row["site_id"], $r["lang_id"]));
			}
			$this->diafan->_postman->message_add_mail($r["mail"], $subject[$r["lang_id"]], $message[$r["lang_id"]], $email);
		}
		DB::query("DELETE FROM {shop_waitlist} WHERE trash='0' AND good_id=%d".($param ? " AND (param='%s' OR param='%s')" : ''), $row["id"], $param, 'a:0:{}');
	}

	/**
	 * Форматирует цену согласно настройкам модуля
	 *
	 * @param float $price цена
	 * @param boolean $float возвращаемый результат: **true** - дискретное число, по умолчанию - строка
	 * @return mixed (string|float)
	 */
	public function price_format($price, $float = false)
	{
		$format_price_1 = $this->price_num_decimal_places();
		$format_price_2 = ($this->diafan->configmodules("format_price_2", "shop") ? $this->diafan->configmodules("format_price_2", "shop") : ',');
		$format_price_3 = ($this->diafan->configmodules("format_price_3", "shop") ? $this->diafan->configmodules("format_price_3", "shop") : "");
		if(($price * 100) % 100 == 0)
		{
			$format_price_1 = 0;
		}
		if($float)
		{
			return round($price, $format_price_1);
		}
		$text = number_format(
			$price,
			$format_price_1,
			$format_price_2,
			$format_price_3
		);
		$text = str_replace(' ', '&nbsp;', $text);
		return $text;
	}

	/**
	 * Возвращает количество знаков в цене после запятой, установленное в настройках модуля
	 *
	 * @param float $price цена
	 * @return integer
	 */
	public function price_num_decimal_places($price = false)
	{
		if(false === $this->diafan->configmodules("format_price_1", "shop"))
		{
			$format_price_1 = 2;
		}
		elseif(0 === $this->diafan->configmodules("format_price_1", "shop") || '0' === $this->diafan->configmodules("format_price_1", "shop"))
		{
			$format_price_1 = 0;
		}
		else
		{
			$format_price_1 = $this->diafan->configmodules("format_price_1", "shop");
		}
		if($price)
		{
			if(($price * 100) % 100 == 0)
			{
				$format_price_1 = 0;
			}
		}
		return $format_price_1;
	}
}
