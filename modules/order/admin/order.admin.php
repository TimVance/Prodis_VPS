<?php
/**
 * Редактирование заказов
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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
 * Order_admin
 */
class Order_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_order';
	
	/**
	 * @var string тип элемента
	 */
	public $element_type = 'element';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'number' => array(
				'type' => 'function',
				'name' => 'Заказ №',
				'help' => 'Номер заказа.',
				'no_save' => true,
			),
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата',
				'help' => 'Дата создания заказа. Вводится в формате дд.мм.гггг чч:мм.',
			),
			'lang_id' => array(
				'type' => 'select',
				'name' => 'Язык интерфейса',
				'help' => 'Фиксируется язык интерфейса, который был при оформлении заказа.',
			),
			'backend' => array(
				'type' => 'function',
				'name' => 'Расширения',
				'help' => 'Расширения дополняют интерфейс редактирования заказов.',
			),
			'goods' => array(
				'type' => 'function',
				'name' => 'Товары',
				'help' => 'Таблица заказанных товаров, сопутствующих услуг. Доступна для редактирования.',
			),
			'payment_id' => array(
				'type' => 'select',
				'name' => 'Способ оплаты',
				'help' => 'Список подключенных методов оплаты.',
				'select_db' => array(
					"table" => "payment",
					"name" => "[name]",
					"empty" => "-",
					"where" => "trash='0'",
					"order" => 'sort ASC',
				),
			),
			'discount_summ' => array(
				'type' => 'floattext',
				'name' => 'Общая скидка',
			),
			'delivery_id' => array(
				'type' => 'select',
				'name' => 'Способ доставки',
				'help' => 'Список подключенных способов доставки.',
				'select_db' => array(
					"table" => "shop_delivery",
					"name" => "[name]",
					"where" => "trash='0'",
					"order" => 'sort ASC',
				),
			),
			'delivery_info' => array(
				'type' => 'text',
				'name' => 'Информация о доставке',
				'help' => 'Заполняется бэкендом доставки.',
			),
			'hr2' => 'hr',
			'user_id' => array(
				'type' => 'function',
				'name' => 'Покупатель',
			),
			'user_buy' => array(
				'type' => 'function',
				'name' => 'Покупатель первый или повторный',
			),
			'param' => array(
				'type' => 'function',
				'name' => 'Дополнительные поля',
				'help' => 'Группа полей, определенных в части «Форма оформления заказа».',
			),
			'hr3' => 'hr',
			'status_id' => array(
				'type' => 'function',
				'name' => 'Статус',
				'help' => 'Список подключенных статусов. При смене статуса, у которого действие определено как «оплата, уменьшение количества на складе», делается запись в историю платежей и количество товара уменьшается.',
			),
			'send_mail' => array(
				'type' => 'none',
				'name' => 'Отправка письма пользователю',
				'help' => 'При создании заказа пользователю будет отправлено сообщение на указанный e-mail адрес. Шаблон письма в настройках модуля «Сообщение пользователю о новом заказе».',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'created' => array(
			'name' => 'Дата и время',
			'type' => 'datetime',
			'sql' => true,
			'no_important' => true,
		),
		'name' => array(
			'name' => 'Заказ',
			'variable' => 'id',
			'text' => '№ %d'
		),
		'status_id' => array(
			'name' => 'Статус',
			'sql' => true,
		),
		'summ' => array(
			'name' => 'Сумма',
			'sql' => true,
		),
		'user_id' => array(
			'name' => 'Покупатель',
			'sql' => true,
		),
		'backend' => array(
		),
		'actions' => array(
			'trash' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'created' => array(
			'name' => 'Дата',
			'type' => 'datetime_interval',
		),
		'id' => array(
			'type' => 'numtext',
			'name' => 'Искать по номеру',
		),
		'status' => array(
			'type' => 'function',
			'name' => 'Искать по статусу',
		),
		'summ' => array(
			'name' => 'Сумма',
			'type' => 'numtext_interval',
		),
		'text' => array(
			'type' => 'text',
			'name' => 'Искать по покупателю',
		),
		'user_id' => array(
			'type' => 'none',
		),
		'param' => array(
			'type' => 'function',
		),
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if(count($this->diafan->_languages->all) > 1)
		{
			foreach ($this->diafan->_languages->all as $language)
			{
				$rows[$language["id"]] = $language["name"];
			}
			$this->diafan->variable('lang_id', 'select', $rows);
		}
		else
		{
			$this->diafan->variable_unset("lang_id");
		}
		$select = array();
		if(! $this->diafan->is_action("edit"))
		{
			$select[''] = $this->diafan->_('Все');
		}
		$rows = DB::query_fetch_all("SELECT id, [name], status, color FROM {shop_order_status} WHERE trash='0' ORDER BY sort ASC");
		foreach ($rows as $row)
		{
			$this->cache["status"][$row["id"]] = $row["status"];
			$this->cache["status_color"][$row["id"]] = $row["color"];
			$select[$row["id"]] = $row["name"];
		}
		$this->diafan->variable("status_id", 'select', $select);
		if($this->diafan->is_action('edit') || $this->diafan->is_action('save'))
		{
			if(! in_array('payment', $this->diafan->installed_modules))
			{
				$this->diafan->variable_unset('payment_id');
			}
		}
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить');
	}

	/**
	 * Выводит список заказов
	 * @return void
	 */
	public function show()
	{
		echo '<div id="orders_list">';
		echo '<script type="text/javascript">
			var last_order_id = '.DB::query_result("SELECT MAX(id) FROM {shop_order} WHERE trash='0'").';
			var title = \''.$this->diafan->_('Новый заказ').'\';
		</script>';
		foreach ($this->cache["status"] as $id => $value)
		{
			$first_status = $id;
			break;
		}

		echo '<div class="refresh_order"><a href="'.BASE_PATH_HREF.'order/?filter_status_id='.$first_status.'" class="new_order"><i class="fa fa-refresh"></i> '.$this->diafan->_('Проверить новые заказы').'</a></div>';

		$this->diafan->list_row();

		if (! $this->diafan->count)
		{
			if(empty($this->diafan->get_nav_params))
			{
				echo '<center><b>'.$this->diafan->_('Заказов нет').'</b><br>('
				.$this->diafan->_('заказы создаются посетителями из пользовательской части сайта')
				.')</center>';
			}
			else
			{
				echo '<p><center><b>'.$this->diafan->_('Заказов не найдено').'</b></p>';
			}
		}
		else
		{
			$stat = DB::query_fetch_array("SELECT SUM(e.summ) AS summ, COUNT(*) AS count FROM {shop_order} as e".$this->diafan->join." WHERE e.trash='0'".$this->diafan->where);
			echo '<div class="orders_bottom"><p>'.$this->diafan->_('Всего заказов').': <span>'.$stat["count"].'</span></p>
			<p>'.$this->diafan->_('На сумму').': <span>'.$this->format_summ($stat["summ"]).' '.$this->diafan->configmodules("currency", "shop").'</span></p>';
			echo '<p>'.$this->diafan->_('Средний чек').': <span>'.$this->format_summ($stat["summ"] / $stat["count"]).' '.$this->diafan->configmodules("currency", "shop").'</span></p></div>';
		}
		echo '</div>';
	}

	/**
	 * Фильтр по статусу
	 *
	 * @return string
	 */
	public function get_filter_variable_status()
	{
		$value = (! empty($this->diafan->get_nav_params['filter_status']) ? $this->diafan->get_nav_params['filter_status'] : array());
		
		$count = DB::query_fetch_key_value("SELECT COUNT(e.id) AS count, e.status_id FROM {shop_order} as e".$this->diafan->join." WHERE e.trash='0'".preg_replace('/ AND e\.status_id IN \(.?\)/', '', $this->diafan->where)." GROUP BY e.status_id", "status_id", "count");
		
		$status = $this->diafan->variable("status_id", 'select');
		
		$text = '';

		foreach($status as $id => $name)
		{
			if(! $id)
				continue;
			$text .= '<p><input type="checkbox" name="filter_status[]" value="'.$id.'"'.(in_array($id, $value) ? ' checked' : '').' id="filter_status'.$id.'"><label for="filter_status'.$id.'"'.(! empty($this->cache["status_color"][$id]) ? ' style="color:'.$this->cache["status_color"][$id] : '').';font-weight: bold;">'.$name.' ('.(! empty($count[$id]) ? $count[$id] : 0).')</label></p>';
		}
		
		return $text;
	}

	/**
	 * Поиск по полю "Покупатель"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_text($row)
	{
		$res = $this->diafan->filter($_GET, 'string', "filter_text");
		if($res)
		{
			$this->diafan->join .= " LEFT JOIN {shop_order_param_element} AS p ON p.element_id=e.id";
			$this->diafan->join .= " LEFT JOIN {users} AS u ON u.id=e.user_id";
			$this->diafan->where .= " AND (p.value LIKE '%%".$this->diafan->filter($_GET, 'sql', "filter_text")."%%' OR u.name LIKE '%%".$this->diafan->filter($_GET, 'sql', "filter_text")."%%' OR u.fio LIKE '%%".$this->diafan->filter($_GET, 'sql', "filter_text")."%%')";
			$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?') . 'filter_text='.$this->diafan->filter($_GET, 'url', "filter_text");
		}
		return $res;
	}

	/**
	 * Поиск по полю "Статус"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_status($row)
	{
		if(! empty($_GET["filter_status"]) && is_array($_GET["filter_status"]))
		{
			$res = $this->diafan->filter($_GET["filter_status"], 'integer');
		}
		else
		{
			$res = array();
		}
		if($res)
		{
			$this->diafan->where .= " AND e.status_id IN (".implode(",", $res).")";
			$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?') . 'filter_status[]='.implode("&amp;filter_status[]=", $res);
		}
		return $res;
	}

	/**
	 * Выводит номера заказов в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_name($row, $var)
	{
		$text = '<div class="name'.(! empty($var["class"]) ? ' '.$var["class"] : '').'" id="'.$row['id'].'">';
		$name  = '';
		if(! empty($var["variable"]))
		{
			$name = strip_tags($row[$var["variable"]]);

			if(! empty($var["type"]) && $var["type"] == 'select')
			{
				if(! isset($var["select"]))
				{
					if(! empty($var["select_db"]))
					{
						$var["select"] = $this->diafan->get_select_from_db($var["select_db"]);
					}
					else
					{
						$var["select"] = $this->diafan->variable('name', 'select');
						if(! $var["select"] && $this->diafan->variable('name', 'select_db'))
						{
							$var["select"] = $this->diafan->get_select_from_db($this->diafan->variable('name', 'select_db'));
						}
					}
					$this->diafan->variable_list('name', 'select', $var["select"]);
				}
				if(! empty($var["select"][$name]))
				{
					$name = $this->diafan->_($var["select"][$name]);
				}
			}
		}
		if(! empty($var["text"]))
		{
			$name = sprintf($this->diafan->_($var["text"]), $name);
		}
		if (! $name)
		{
			if(! empty($row["name"]))
			{
				$name = $row["name"];
			}
			else
			{
				$name = $row['id'];
			}
		}

		$text .= '<a href="';
		$text .= $this->diafan->get_base_link($row);
		$text .= '" title="'.$this->diafan->_('Редактировать').' ('.$row["id"].')">'.$name.'</a>';
		$text .= $this->diafan->list_variable_menu($row, array());
		$text .= $this->diafan->list_variable_parent($row, array());
		$text .= $this->diafan->list_variable_date_period($row, array());
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит имя заказчика в списке заказов
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_user_id($row, $var)
	{
		if(! isset($this->cache["prepare"]["users"]))
		{
			$user_ids = array();
			foreach($this->diafan->rows as $r)
			{
				if($r["user_id"] && ! in_array($r["user_id"], $user_ids))
				{
					$user_ids[] = $r["user_id"];
				}
			}
			if($user_ids)
			{
				$this->cache["prepare"]["users"] = DB::query_fetch_key_value(
					"SELECT id, CONCAT(fio, ' (', name, ')') as fio FROM {users} WHERE id IN (%s) AND trash='0'",
					implode(",", $user_ids),
					"id", "fio"
				);
			}
		}
		if(! isset($this->cache["prepare"]["param"]))
		{
			$select = array();
			$checkbox = array();
			$rows = DB::query_fetch_all("SELECT e.element_id, e.value, e.param_id, p.type, p.[name] FROM"
				." {shop_order_param_element} AS e"
				." INNER JOIN {shop_order_param} AS p ON e.param_id=p.id"
				. " WHERE e.trash='0' AND e.element_id IN (%s)", implode(",", $this->diafan->rows_id));
			foreach ($rows as $r)
			{
				switch ($r["type"])
				{
					case 'select':
					case 'multiple':
						if(! in_array($r["value"], $select))
						{
							$select[] = $r["value"];
						}
						break;

					case 'checkbox':
						if(! in_array($r["param_id"], $checkbox))
						{
							$checkbox[] = $r["param_id"];
						}
						break;
				}
			}
			if($select)
			{
				$select_value = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_order_param_select} WHERE id IN (%s)", implode(",", $select), "id", "name");
			}
			if($checkbox)
			{
				$checkbox_value = DB::query_fetch_key_value("SELECT param_id, [name] FROM {shop_order_param_select} WHERE param_id IN (%s)", implode(",", $checkbox), "param_id", "name");
			}
			foreach ($rows as $r)
			{
				if ($r["value"])
				{
					switch ($r["type"])
					{
						case 'select':
						case 'multiple':
							if(! empty($select_value[$r["value"]]))
							{
								$r["value"] = $select_value[$r["value"]];
							}
							break;

						case 'checkbox':
							$v = (! empty($checkbox_value[$r["param_id"]]) ? $checkbox_value[$r["param_id"]] : '');
							if ($v)
							{
								$r["value"] = $r["name"].': '.$v;
							}
							else
							{
								$r["value"] = $r["name"];
							}
							break;
					}
					$this->cache["prepare"]["param"][$r["element_id"]][] = $r["value"];
				}
			}
		}
		$text = '<div class="user no_important ipad">';
		if($row["user_id"] && ! empty($this->cache["prepare"]["users"][$row["user_id"]]))
		{
			$text .= '<a href="'.BASE_PATH_HREF.'users/edit'.$row["user_id"].'/">'.$this->cache["prepare"]["users"][$row["user_id"]].'</a>';
		}
		elseif(! empty($this->cache["prepare"]["param"][$row["id"]]))
		{
			$text .= implode(', ', $this->cache["prepare"]["param"][$row["id"]]);
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит сумму заказа в списке заказов
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_summ($row, $var)
	{
		return '<div class="sum">'
		.($row["summ"]
		 ? $this->format_summ($row["summ"]).' '.$this->diafan->configmodules("currency", "shop")
		 : '').'</div>';
	}

	/**
	 * Выводит статус заказа в списке заказов
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_status_id($row, $var)
	{
		if(! isset($this->cache["status"][$row["status_id"]]))
		{
			$this->cache["status"][$row["status_id"]] = '';
		}
		$select = $this->diafan->variable("status_id", 'select');
		if(empty($select[$row["status_id"]]))
		{
			return '';
		}
		return '<div class="num">'
		.'<span style="color:'.$this->cache["status_color"][$row["status_id"]].';font-weight: bold;">'
		.$select[$row["status_id"]].'</div>';
	}

	/**
	 * Поля "Расширения" в списке заказов
	 * 
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return void
	 */
	public function list_variable_backend($row, $var)
	{
		if(! isset($this->cache["backend"]))
		{
			$this->cache["backend"] = array();
			$rows_value = DB::query_fetch_all("SELECT * FROM {shop_order_backend_element} WHERE trash='0' AND order_id IN (%s)", implode(",", $this->diafan->rows_id));
			foreach($rows_value as $r)
			{
				$this->cache["backend_values"][$r["backend_id"]][$r["order_id"]][$r["type"]][] = $r["value"];
			}
			$rows = DB::query_fetch_all("SELECT * FROM {shop_order_backend} WHERE trash='0' AND act='1' AND list='1' ORDER BY sort ASC");
			foreach($rows as $r)
			{
				if(Custom::exists('modules/order/backend/'.$r["backend"].'/order.'.$r["backend"].'.admin.order.php'))
				{
					Custom::inc('modules/order/backend/'.$r["backend"].'/order.'.$r["backend"].'.admin.order.php');
					$name_class = 'Order_'.$r["backend"].'_admin_order';
					$class = new $name_class($this->diafan);
					if (is_callable(array(&$class, "list")))
					{
						$this->cache["backend"][] = array(
							"class" => $class,
							"id" => $r["id"]
						);
					}
				}
			}
		}
		$text = '';
		foreach($this->cache["backend"] as &$b)
		{
			$text .= $b["class"]->list($row, isset($this->cache["backend_values"][$b["id"]][$row["id"]]) ? $this->cache["backend_values"][$b["id"]][$row["id"]] : false);
		}
		return $text;
	}

	/**
	 * Редактирование поля "Номер"
	 * @return void
	 */
	public function edit_variable_number()
	{
		echo '<div class="unit" id="order_number">';
		echo '<b>'.$this->diafan->variable_name().'</b> ';
		if(! $this->diafan->is_new)
		{
			echo $this->diafan->id;
		}
		else
		{
			echo DB::query_result("SELECT MAX(id) FROM {shop_order}") + 1;
		}
		
		if($log = DB::query_fetch_array("SELECT * FROM {shop_cart_log_mail} WHERE order_id=%d ORDER BY created DESC LIMIT 1", $this->diafan->id))
		{
			echo '<div class="unit" id="order_number">'.date("d.m.Y H:i", $log["created"]).' <b>'.$this->diafan->_('Отправлено письмо из интерфейса «Брошенные корзины».').'</b> </div>';
		}
	}

	/**
	 * Редактирование поля "Дата"
	 * @return void
	 */
	public function edit_variable_created()
	{
		if(! $this->diafan->value)
		{
			$this->diafan->value = time();
		}
		echo '<b>
				'.$this->diafan->_('от ').'
			</b>
				<input type="text" showtime="true" class="timecalendar hasDatepicker" value="'. date("d.m.Y H:i", $this->diafan->value).'" name="created" id="filed_created">
		</div>';
	}


	/**
	 * Редактирование поля "Статус"
	 * @return void
	 */
	public function edit_variable_status_id()
	{
		echo '
		<div class="unit">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>';
		echo '<select name="status_id" id="order_select_status">';
		foreach ($this->diafan->variable("status_id", 'select') as $key => $value)
		{
			echo '<option value="'.$key.'"'.($key == $this->diafan->value ? ' selected' : '').'>'.$value.'</option>';
		}
		echo '</select>';
		echo '</div>';
	}

	/**
	 * Редактирование поля "Расширения"
	 * @return void
	 */
	public function edit_variable_backend()
	{
		$values = array();
		if(! $this->diafan->is_new)
		{
			$rows_value = DB::query_fetch_all("SELECT * FROM {shop_order_backend_element} WHERE trash='0' AND order_id=%d", $this->diafan->id);
			foreach($rows_value as $row)
			{
				$values[$row["backend"]][$row["type"]][] = $value;
			}
		}
		$this->diafan->values("backend", $values, true);
		$rows = DB::query_fetch_all("SELECT * FROM {shop_order_backend} WHERE trash='0' AND act='1' ORDER BY sort ASC");
		foreach($rows as $row)
		{
			if(Custom::exists('modules/order/backend/'.$row["backend"].'/order.'.$row["backend"].'.admin.order.php'))
			{
				Custom::inc('modules/order/backend/'.$row["backend"].'/order.'.$row["backend"].'.admin.order.php');
				$name_class = 'Order_'.$row["backend"].'_admin_order';
				$class = new $name_class($this->diafan);
				if (is_callable(array(&$class, "edit")))
				{
					$this->diafan->value = (! empty($values[$row["backend"]]) ? $values[$row["backend"]] : array());
					call_user_func_array(array(&$class, "edit"), array());
				}
			}
		}
	}

	/**
	 * Редактирование поля "Накладная"
	 * @return void
	 */
	public function edit_variable_param()
	{
		parent::__call('edit_variable_param', array());

		if($this->diafan->is_new)
			return;

		$rows = DB::query_fetch_all("SELECT e.value, p.info FROM {shop_order_param_element} AS e INNER JOIN {shop_order_param} AS p ON p.id=e.param_id WHERE e.element_id=%d", $this->diafan->id);
		$address = array();
		foreach ($rows as $row)
		{
			switch($row["info"])
			{
				case 'city':
				case 'street':
				case 'building':
				case 'suite':
				case 'address':
					$address[] = $row["value"];
					break;
			}
		}

		echo '
		<div class="unit">
			<a href="https://www.google.com/maps/search/'.urlencode(implode(' ', $address)).'/" target="_blank"><i class="fa fa-map-marker"></i> '.$this->diafan->_('Показать адрес на карте').'</a>
		</div>';
	}

	/**
	 * Редактирование поля "Товары"
	 * @return void
	 */
	public function edit_variable_goods()
	{
		$summ = 0;
		$count = 0;
		echo '
		<ul class="list list_stat do_auto_width" id="order_goods_list">
		<li class="item item_heading">
			<div class="item__th" no_important ipad></div>
			<div class="item__th">'.$this->diafan->_('Товар').'</div>
			<div class="item__th item__th_adapt"></div>
			<div class="item__th item__th_seporator"></div>
			<div class="item__th">'.$this->diafan->_('Количество').'</div>
			<div class="item__th">'.$this->diafan->_('Цена').'</div>
			<div class="item__th no_important ipad">'.$this->diafan->_('Скидка').'</div>
			<div class="item__th no_important ipad">'.$this->diafan->_('Итоговая цена').'</div>
			<div class="item__th">'.$this->diafan->_('Сумма').'</div>
			<div class="item__th">'.$this->diafan->_('Удалить').'</div>
		</li>';

		if(! $this->diafan->is_new)
		{
			$rows = DB::query_fetch_all(
				"SELECT g.*, s.name".$this->diafan->_languages->site." AS name_good, s.article, s.[measure_unit], s.cat_id, c.name".$this->diafan->_languages->site." AS name_cat FROM {shop_order_goods} AS g"
				." INNER JOIN {shop} AS s ON g.good_id=s.id"
				." LEFT JOIN {shop_category} AS c ON s.cat_id=c.id"
				." WHERE g.order_id=%d ORDER by c.sort ASC",
				$this->diafan->id
			);
			if($rows)
			{
				$good_ids = array();
				foreach($rows as $row)
				{
					$good_ids[] = $row["good_id"];
				}
				$additional_costs = DB::query_fetch_key_array("SELECT a.id, a.[name], a.price, a.percent, r.summ, r.element_id FROM {shop_additional_cost} AS a
				INNER JOIN {shop_additional_cost_rel} AS r ON r.element_id IN (%s) AND r.additional_cost_id=a.id
				WHERE a.trash='0' AND a.shop_rel='1'
				ORDER BY a.sort ASC", implode(',', $good_ids), "element_id");

				$order_additional_costs = DB::query_fetch_key_array("SELECT id, summ, order_goods_id, additional_cost_id FROM {shop_order_additional_cost} WHERE order_id=%d", $this->diafan->id, "order_goods_id");
			}
			foreach ($rows as $row)
			{
				$depend = '';
				$params = array();
				$rows_p = DB::query_fetch_all("SELECT * FROM {shop_order_goods_param} WHERE order_goods_id=%d", $row["id"]);
				foreach ($rows_p as $row_p)
				{
					$params[$row_p["param_id"]] = $row_p["value"];
					if(! $row_p["value"])
						continue;
					$param_name  = DB::query_result("SELECT [name] FROM {shop_param} WHERE id=%d LIMIT 1", $row_p["param_id"]);
					$param_value = DB::query_result("SELECT [name] FROM {shop_param_select} WHERE id=%d AND param_id=%d LIMIT 1", $row_p["value"], $row_p["param_id"]);
					$depend .= ($depend ? ', ' : '').$param_name.': '.$param_value;
				}
				$row["price"] = $this->format_summ($row["price"]);
				if($row_price = $this->diafan->_shop->price_get($row["good_id"], $params, false))
				{
					$row_price["price"] = $this->format_summ($row_price["price"]);
					$row_price["old_price"] = $this->format_summ($row_price["old_price"]);
				}
				if(empty($row_price["price_id"]))
				{
					$row_price["price_id"] = 0;
					$row_price["price"] = 0;
				}

				$price = ! empty($row_price["old_price"]) ? $row_price["old_price"] : $row_price["price"];

				$row["discount"] = '';
				if($row["discount_id"])
				{
					if(empty($discounts[$row["discount_id"]]))
					{
						$discounts[$row["discount_id"]] = DB::query_fetch_array("SELECT discount, deduction FROM {shop_discount} WHERE id=%d LIMIT 1", $row["discount_id"]);
					}
					if($discounts[$row["discount_id"]]["discount"])
					{
						$row["discount"] = $discounts[$row["discount_id"]]["discount"].'%';
					}
					else
					{
						$row["discount"] = ($price - $row["price"]).' '.$this->diafan->configmodules("currency", "shop");
					}
				}
				elseif(! empty($row_price["old_price"]) && $row_price["old_price"] != $row["price"])
				{
					$row["discount"] = ceil(100 - $row["price"]/$row_price["old_price"] * 100).' %';
				}
				$img = DB::query_fetch_array("SELECT i.name, i.folder_num FROM {images} AS i
				LEFT JOIN {shop_price_image_rel} AS r ON r.image_id=i.id AND r.price_id=%d
				WHERE i.element_id=%d AND i.module_name='shop' AND i.element_type='element' AND i.trash='0'
				ORDER BY r.image_id DESC, i.sort ASC LIMIT 1",
				$row_price["price_id"], $row["good_id"]);
				echo '
				<li class="item">
				<div class="item__in">
					<div class="sum no_important ipad">'.($img ? '<img src="'.BASE_PATH.USERFILES.'/small/'.($img["folder_num"] ? $img["folder_num"].'/' : '').$img["name"].'">' : '').'</div>

					<div class="name"><a href="'.BASE_PATH_HREF.'shop/edit'.$row["good_id"].'/">'.$row["name_good"].' '.$depend.($row["article"] ? ' ('.$row["article"].')' : '').'</a><div class="categories">'.$row["name_cat"].'</div></div>

					<div class="item__adapt mobile">
						<i class="fa fa-bars"></i>
						<i class="fa fa-caret-up"></i>
					</div>
					<div class="item__seporator mobile"></div>

					<div class="num no_important ipad"><nobr><input type="text" name="count_goods'.$row["id"].'" value="'.$row["count_goods"].'" size="2">';
					if($row["measure_unit"])
					{
						echo ' '.$row["measure_unit"];
					}
					echo '</nobr></div>

					<div class="num no_important ipad">'.$this->format_summ($price).'</div>

					<div class="num no_important ipad">'.($row["discount_id"] ? '<a href="'.BASE_PATH_HREF.'shop/discount/edit'.$row["discount_id"].'/">'.$row["discount"].'</a>' : $row["discount"]).'</div>

					<div class="num no_important ipad"><input type="text" name="price_goods'.$row["id"].'" value="'.$this->format_summ($row["price"]).'" size="4"></div>

					<div class="num">'.$this->format_summ($row["price"] * $row["count_goods"]).'</div>

					<div class="num"><a href="javascript:void(0)" confirm="'.$this->diafan->_('Вы действительно хотите удалить товар из заказа?').'" class="delete delete_order_good"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a></div>

				</div>';
				$summ += $row["price"] * $row["count_goods"];
				$count += $row["count_goods"];
				if(! empty($additional_costs[$row["good_id"]]))
				{
					foreach($additional_costs[$row["good_id"]] as $a)
					{
						if($a["percent"])
						{
							$a["summ"] = ($row["price"] * $a["percent"]) / 100;
						}
						elseif(! $a["summ"])
						{
							$a["summ"] = $a["price"];
						}
						$a["summ"] = $this->format_summ($a["summ"]);
						$checked = false;
						$order_summ = $a["summ"];
						if(! empty($order_additional_costs[$row["id"]]))
						{
							foreach($order_additional_costs[$row["id"]] as $o_a)
							{
								if($o_a["additional_cost_id"] == $a["id"])
								{
									$checked = true;
									$o_a["summ"] = $this->format_summ($o_a["summ"]);
									$order_summ = $o_a["summ"] / $row["count_goods"];
								}
							}
						}
						echo '
						</li><li class="item">
						<div class="item__in">
							<div class="sum no_important ipad"></div>

							<div class="name">'.$a["name"].'</div>

							<div class="item__adapt mobile">
								<i class="fa fa-bars"></i>
								<i class="fa fa-caret-up"></i>
							</div>
							<div class="item__seporator mobile"></div>

							<div></div>
							<div></div>

							<div class="num">
							<input name="additional_cost_id_good_'.$row["id"].'_'.$a["id"].'" id="additional_cost_id_good_'.$row["id"].'_'.$a["id"].'" value="1" type="checkbox" '.($checked ? ' checked' : '').' title="'.$this->diafan->_('Добавлено к заказу').'">
							<label for="additional_cost_id_good_'.$row["id"].'_'.$a["id"].'"></label>
							</div>
							<div class="num">
							<input type="text" name="summ_additional_cost_good_'.$row["id"].'_'.$a["id"].'" value="'.$this->format_summ($order_summ).'" size="4"></div>

							<div class="num no_important ipad">'.$this->format_summ($a["summ"]).'
							</div>
							<div class="num no_important ipad"></div>
						</div>';
					}
				}
				echo '</li>';
			}
		}
		if($this->diafan->_users->roles('edit', 'order'))
		{
			echo '<li class="item">
				<div class="item__in">
					<div class="sum no_important ipad"></div>

					<div class="name"><i class="fa fa-plus-square"></i>  <a href="javascript:void(0)" class="order_good_plus" title="'.$this->diafan->_('Добавить').'">'.$this->diafan->_('Добавить товар к заказу').'</a>
					</div>

					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>

				</div>
			</li>';
		}

		if(! $this->diafan->is_new)
		{
			$rows = DB::query_fetch_all("SELECT a.id, a.[name], a.price, a.amount, s.id AS sid, s.summ, a.percent FROM {shop_additional_cost} AS a LEFT JOIN {shop_order_additional_cost} AS s ON s.additional_cost_id=a.id AND s.order_id=%d WHERE a.trash='0' AND a.shop_rel='0'", $this->diafan->id);
		}
		else
		{
			$rows = DB::query_fetch_all("SELECT a.id, a.[name], a.price, a.amount, a.percent FROM {shop_additional_cost} AS a WHERE a.trash='0' AND a.shop_rel='0'");
		}
		if($rows)
		{
			echo '<li class="item">
				<div class="item__in">
					<div></div>

					<div class="name"><strong>'.$this->diafan->_('Сопутствующие услуги').'</strong></div>

					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>

				</div>
			</li>';
		}
		foreach ($rows as $row)
		{
			if(! empty($row["sid"]))
			{
				$row['price'] = $row['summ'];
			}
			else
			{
				if($row['percent'])
				{
					$row["price"] = $summ * $row['percent'] / 100;
				}
				if (! empty($row['amount']))
				{
					if ($row['amount'] < $summ)
					{
						$row['price'] = 0;
					}
				}
			}
			$row['price'] = $this->format_summ($row["price"]);
			echo '<li class="item">
			<div class="item__in">
				<div class="sum no_important ipad"></div>

				<div class="name">'.$row["name"].'</div>

				<div class="item__adapt mobile">
					<i class="fa fa-bars"></i>
					<i class="fa fa-caret-up"></i>
				</div>
				<div class="item__seporator mobile"></div>

				<div></div>
				<div></div>

				<div class="num">
				<input name="additional_cost_id'.$row["id"].'" id="additional_cost_id'.$row["id"].'" value="1" type="checkbox" '.(! empty($row["sid"]) ? ' checked' : '').' title="'.$this->diafan->_('Добавлено к заказу').'"> <label for="additional_cost_id'.$row["id"].'"></label></div>

				<div class="num">
				<input type="text" name="summ_additional_cost'.$row["id"].'" value="'.$row["price"].'" size="4"></div>

				<div class="num no_important ipad">'.(! empty($row["sid"]) ? $row["price"] : '0').'
				</div>
				<div class="num no_important ipad"></div>
			</div>
			</li>';
		}

		if ($this->diafan->values("delivery_id"))
		{
			echo '<li class="item">
				<div class="item__in">
					<div></div>

					<div class="name"><b>'.$this->diafan->_('Доставка').'</b></div>

					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>

				</div>
			</li>';

			$delivery_name = DB::query_result("SELECT [name] FROM {shop_delivery} WHERE id=%d LIMIT 1", $this->diafan->values("delivery_id"));
		    echo '<li class="item">
				<div class="item__in">
					<div class="sum no_important ipad"></div>

					<div class="name">'.$delivery_name;
					if($this->diafan->values("delivery_info"))
					{
						echo '<br>'.$this->diafan->values("delivery_info");
					}
					echo '</div>

					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div class="num no_important ipad"><input name="delivery_summ" value="'.$this->diafan->values("delivery_summ").'" size="4" type="text"></div>
					<div class="num">'.$this->format_summ($this->diafan->values("delivery_summ")).'</div>
					<div class="num no_important ipad"></div>

				</div>
			</li>';
		}
		echo '<li class="item">
				<div class="item__in">
					<div class="sum no_important ipad"></div>

					<div class="name">'
			.($this->diafan->values("discount_id") ? '<a href="'.BASE_PATH_HREF.'shop/discount/edit'.$this->diafan->values("discount_id").'/">' : '')
			.$this->diafan->variable_name('discount_summ')
			.($this->diafan->values("discount_id") ? '</a>' : '');
			if($this->diafan->values("coupon"))
			{
				$coupons = explode(',', $this->diafan->values("coupon"));
				$cs = DB::query_fetch_key_value("SELECT coupon, discount_id FROM {shop_discount_coupon} WHERE coupon IN ('".implode("','", $this->diafan->filter($coupons, "sql"))."') AND trash='0'", "coupon", "discount_id");
				foreach($coupons as $i => $c)
				{
					if($i)
					{
						echo ', ';
					}
					else
					{
						echo '<br>'.$this->diafan->_('Купон').': ';
					}
					echo '<a href="'.BASE_PATH_HREF.'shop/discount/edit'.(! empty($cs[$c]) ? $cs[$c] : $this->diafan->values("discount_id")).'/">'.$c.'</a>';
				}
			}
			echo '</div>

					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div class="num no_important ipad"></div>
<div class="num"><input name="discount_summ" value="'
			.($this->diafan->values("discount_summ") ? $this->format_summ($this->diafan->values("discount_summ")) : '')
			.'" size="4" type="text"></div>
					<div class="num no_important ipad">'.($this->diafan->values("discount_summ") ? '-'.$this->format_summ($this->diafan->values("discount_summ")) : '').'</div>
					<div class="num no_important ipad"></div>

				</div>
			</li>';
		echo '<li class="item">
				<div class="item__in">
					<div></div>

					<div class="name"><b>'.$this->diafan->_('ИТОГО').'</b></div>

					<div></div>
					<div></div>
					<div class="num no_important ipad"><b>'.$count.'</b>&nbsp;'.$this->diafan->_('товар(ов)').'</div>
					<div class="num no_important ipad"></div>
					<div class="num no_important ipad"></div>
					<div class="num no_important ipad">'.$this->diafan->_('на&nbsp;сумму').'</div>
					<div class="num">';
		if(! $this->diafan->is_new)
		{
			echo '<b>'.$this->format_summ($this->diafan->values("summ")).'</b>';
			if($this->diafan->configmodules('tax', 'shop'))
			{
				echo '<br>'.$this->diafan->_('в т. ч. %s', $this->diafan->configmodules('tax_name', 'shop')).'<br>'.$this->format_summ($this->diafan->values("summ") * $this->diafan->configmodules('tax', 'shop') / (100 + $this->diafan->configmodules('tax', 'shop')));
			}
		}

				echo '</div>
					<div class="num no_important ipad"></div>

				</div>
			</li>
		</ul>
		';
	}

	/**
	 * Редактирование поля "Сумма скидки"
	 * @return void
	 */
	public function edit_variable_discount_summ(){}

	/**
	 * Редактирование поля "Способ оплаты"
	 * @return void
	 */
	public function edit_variable_payment_id()
	{
		if(in_array('payment', $this->diafan->installed_modules))
		{
			$pay = DB::query_fetch_array("SELECT payment_id, id, code FROM {payment_history} WHERE module_name='cart' AND element_id=%d", $this->diafan->id);
		}
		$key = 'payment_id';
		echo '
		<div class="unit">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>
			<select name="'.$key.'">';
			$select = $this->get_select_from_db($this->diafan->variable("payment_id", "select_db"));
			foreach($select as $k => $v)
			{
				echo '<option value="'.$k.'"'.($k == $pay["payment_id"] ? ' selected' : '').'>'.$v.'</option>';
			}
		echo '</select>';
		if (! $this->diafan->is_new && ! $this->diafan->values("status") && $pay["payment_id"] <> 0)
		{
			$cart_rewrite = DB::query_result("SELECT r.rewrite FROM {rewrite} AS r INNER JOIN {site} AS s ON s.id=r.element_id AND s.module_name='cart' WHERE r.element_type='element' AND r.module_name='site'");
			echo '<div style="display: inline-block; margin: 5px 10px;"><a href="'.BASE_PATH.$cart_rewrite.'/step2/show'.$this->diafan->id.ROUTE_END.'?code='.$pay["code"].'" target="_blank">'.$this->diafan->_('Ссылка на оплату заказа').'</a></div>';
		}
		echo '</div>';
	}

	/**
	 * Редактирование поля "Покупатель"
	 * @return void
	 */
	public function edit_variable_user_buy()
	{
		if ($this->diafan->values("user_id")>0)
		{
			echo '<div class="unit">';
			$orders = DB::query_result("SELECT COUNT(*) FROM {shop_order} WHERE user_id=%d AND trash='0'",$this->diafan->values("user_id"));
				if ($orders>1)
				{
					$user = $this->diafan->_('Покупатель совершил').' <a href="'.BASE_PATH_HREF.'order/?filter_user_id='.$this->diafan->values("user_id").'">'.$orders .' '. $this->diafan->_('заказ(ов)').'</a>';
				}
				else
				{
					$user = $this->diafan->_('Первый заказ этого покупателя');
				}
			echo $user;
			echo '</div>';
		}
	}

	/**
	 * Форматирует сумму
	 *
	 * @param float $summ сумма
	 * @return string
	 */
	private function format_summ($summ)
	{
		//if(($summ * 100) % 100)
		//{
		//	$num_decimal_places = 2;
		//}
		//else
		//{
		//	$num_decimal_places = 0;
		//}
		//return number_format($summ, $num_decimal_places, ".", "");

		return $this->diafan->_shop->price_format($summ, true);
	}

	/**
	 * Сохранение поля "Товары"
	 * @return void
	 */
	public function save_variable_goods()
	{
		$summ = 0;

		$order_additional_costs = DB::query_fetch_key_array("SELECT * FROM {shop_order_additional_cost} WHERE order_id=%d", $this->diafan->id, "order_goods_id");

		$rows = DB::query_fetch_all("SELECT * FROM {shop_order_goods} WHERE order_id=%d", $this->diafan->id);
		if($rows)
		{
			$good_ids = array();
			foreach($rows as $row)
			{
				$good_ids[] = $row["good_id"];
			}
			$additional_costs = DB::query_fetch_key_array("SELECT a.id, a.[name], a.price, a.percent, r.summ, r.element_id FROM {shop_additional_cost} AS a
			INNER JOIN {shop_additional_cost_rel} AS r ON r.element_id IN (%s) AND r.additional_cost_id=a.id
			WHERE a.trash='0' AND a.shop_rel='1'
			ORDER BY a.sort ASC", implode(',', $good_ids), "element_id");
		}
		foreach ($rows as $row)
		{
			if(empty($_POST["count_goods".$row["id"]]))
			{
				$del_order_goods[] = $row["id"];
			}
			else
			{
				$_POST["count_goods".$row["id"]] = $this->diafan->filter($_POST, 'float', "count_goods".$row["id"]);
				if ($_POST["count_goods".$row["id"]] != $row["count_goods"])
				{
					DB::query("UPDATE {shop_order_goods} SET count_goods=%f WHERE id=%d", $_POST["count_goods".$row["id"]], $row["id"]);
				}
				$_POST["price_goods".$row["id"]] = $this->format_summ($_POST["price_goods".$row["id"]]);
				if ($_POST["price_goods".$row["id"]] != $row["price"])
				{
					DB::query("UPDATE {shop_order_goods} SET price=%f, discount_id=0 WHERE id=%d", $_POST["price_goods".$row["id"]], $row["id"]);
				}
				if(! empty($additional_costs[$row["good_id"]]))
				{
					foreach($additional_costs[$row["good_id"]] as $a)
					{
						if(! empty($_POST["additional_cost_id_good_".$row["id"].'_'.$a["id"]]))
						{
							$_POST["summ_additional_cost_good_".$row["id"].'_'.$a["id"]] = $this->format_summ($_POST["summ_additional_cost_good_".$row["id"].'_'.$a["id"]]);
							$_POST["summ_additional_cost_good_".$row["id"].'_'.$a["id"]] = $_POST["summ_additional_cost_good_".$row["id"].'_'.$a["id"]] * $_POST["count_goods".$row["id"]];
							$find_c = false;
							if(! empty($order_additional_costs[$row["id"]]))
							{
								foreach($order_additional_costs[$row["id"]] as $c)
								{
									if($c["additional_cost_id"] == $a["id"])
									{
										$find_c = $c;
									}
								}
							}
							if(! $find_c)
							{
								$order_additional_cost_ids[] = DB::query("INSERT INTO {shop_order_additional_cost} (order_id, order_goods_id, additional_cost_id, summ) VALUES (%d, %d, %d, %f)", $this->diafan->id, $row["id"], $a["id"], $_POST["summ_additional_cost_good_".$row["id"].'_'.$a["id"]]);
							}
							else
							{
								$order_additional_cost_ids[] = $find_c["id"];
								if($_POST["summ_additional_cost_good_".$row["id"].'_'.$a["id"]] != $find_c["summ"])
								{
									DB::query("UPDATE {shop_order_additional_cost} SET summ=%f WHERE id=%d", $_POST["summ_additional_cost_good_".$row["id"].'_'.$a["id"]], $find_c["id"]);
								}
							}
							$summ += $_POST["summ_additional_cost_good_".$row["id"].'_'.$a["id"]];
						}
					}
				}

				$summ += $_POST["price_goods".$row["id"]] * $_POST["count_goods".$row["id"]];
			}
		}
		if(! empty($del_order_goods))
		{
			DB::query("DELETE FROM {shop_order_goods} WHERE id IN (%s)", implode(',', $del_order_goods));
			DB::query("DELETE FROM {shop_order_goods_param} WHERE order_goods_id IN (%s)", implode(',', $del_order_goods));
			DB::query("DELETE FROM {shop_order_additional_cost} WHERE order_goods_id IN (%s)", implode(',', $del_order_goods));
		}
		if(! empty($_POST["new_prices"]))
		{
			foreach($_POST["new_prices"] as $i => $price_id)
			{
				$price = DB::query_fetch_array("SELECT price_id, price, old_price, good_id, discount_id FROM {shop_price} WHERE id=%d LIMIT 1", $price_id);
				$where = array();
				$params = array();
				$rows = DB::query_fetch_all("SELECT param_id, param_value FROM {shop_price_param} WHERE price_id=%d AND trash='0'", $price["price_id"]);
				foreach ($rows as $row)
				{
					$params[$row["param_id"]] = $row["param_value"];
					$where[] = "s.param_id=".intval($row["param_id"])." AND s.value=".intval($row["param_value"]);
				}
				$_POST["new_price_goods"][$i] = $this->format_summ($_POST["new_price_goods"][$i]);
				$order_goods_id = DB::query("INSERT INTO {shop_order_goods} (order_id, good_id, count_goods, price, discount_id) VALUES (%d, %d, %f, %f, %d)", $this->diafan->id, $price["good_id"], $_POST["new_count_prices"][$i], $_POST["new_price_goods"][$i], $price["discount_id"]);
				$summ += $_POST["new_count_prices"][$i] * $_POST["new_price_goods"][$i];
				if($params)
				{
					foreach ($params as $id => $value)
					{
						DB::query("INSERT INTO {shop_order_goods_param} (value, param_id, order_goods_id) VALUES (%d, %d, %d)", $value, $id, $order_goods_id);
					}
				}
				if(! empty($additional_costs[$price["good_id"]]))
				{
					foreach($additional_costs[$price["good_id"]] as $a)
					{
						if(! empty($_POST["additional_cost_id_good_".$row["id"].'_'.$a["id"]]))
						{
							$_POST["summ_additional_cost_price_".$price_id.'_'.$a["id"]] = $this->format_summ($_POST["summ_additional_cost_price_".$price_id.'_'.$a["id"]]);
							$_POST["summ_additional_cost_price_".$price_id.'_'.$a["id"]] = $_POST["summ_additional_cost_price_".$price_id.'_'.$a["id"]] * $_POST["new_count_prices"][$i];
							$order_additional_cost_ids[] = DB::query("INSERT INTO {shop_order_additional_cost} (order_id, order_goods_id, additional_cost_id, summ) VALUES (%d, %d, %d, %f)", $this->diafan->id, $order_goods_id, $a["id"], $_POST["summ_additional_cost_price_".$price_id.'_'.$a["id"]]);
							$summ += $_POST["summ_additional_cost_price_".$price_id.'_'.$a["id"]];
						}
					}
				}
			}
		}
		if(! empty($_POST["new_goods"]))
		{
			foreach($_POST["new_goods"] as $i => $good_id)
			{
				DB::query("INSERT INTO {shop_order_goods} (order_id, good_id, count_goods) VALUES (%d, %d, %f)", $this->diafan->id, $good_id, $_POST["new_count_goods"][$i]);
			}
		}

		$rows = DB::query_fetch_all("SELECT * FROM {shop_additional_cost} WHERE trash='0' AND shop_rel='0'");
		foreach ($rows as $a)
		{
			if(! empty($_POST["additional_cost_id".$a["id"]]))
			{
				$_POST["summ_additional_cost".$a["id"]] = $this->format_summ($_POST["summ_additional_cost".$a["id"]]);
				$find_c = false;
				if(! empty($order_additional_costs[0]))
				{
					foreach($order_additional_costs[0] as $c)
					{
						if($c["additional_cost_id"] == $a["id"])
						{
							$find_c = $c;
						}
					}
				}
				if(! $find_c)
				{
					$order_additional_cost_ids[] = DB::query("INSERT INTO {shop_order_additional_cost} (order_id, additional_cost_id, summ) VALUES (%d, %d, %f)", $this->diafan->id, $a["id"], $_POST["summ_additional_cost".$a["id"]]);
				}
				else
				{
					$order_additional_cost_ids[] = $find_c["id"];
					if($_POST["summ_additional_cost".$a["id"]] != $find_c["summ"])
					{
						DB::query("UPDATE {shop_order_additional_cost} SET summ=%f WHERE id=%d", $_POST["summ_additional_cost".$a["id"]], $find_c["id"]);
					}
				}
				$summ += $_POST["summ_additional_cost".$a["id"]];
			}
		}
		DB::query("DELETE FROM {shop_order_additional_cost} WHERE order_id=%d".(! empty($order_additional_cost_ids) ? " AND id NOT IN (%s)" : ''), $this->diafan->id, (! empty($order_additional_cost_ids) ? implode(',', $order_additional_cost_ids) : ''));

		$_POST["discount_summ"] = $this->format_summ($_POST["discount_summ"]);
		$summ -= $_POST["discount_summ"];
		DB::query("UPDATE {shop_order} SET summ=%f+delivery_summ, discount_summ=%f WHERE id=%d", $summ, $_POST["discount_summ"], $this->diafan->id);
	}

	/**
	 * Сохранение поля "Способ доставки"
	 * @return void
	 */
	public function save_variable_delivery_id()
	{
		$summ = DB::query_result("SELECT summ-delivery_summ FROM {shop_order} WHERE id=%d LIMIT 1", $this->diafan->id);
		$summ = $this->format_summ($summ);
		if($_POST["delivery_id"] != $this->diafan->values('delivery_id'))
		{
			$delivery_summ = 0;
			$delivery_id = $_POST["delivery_id"];
			if ($row = DB::query_fetch_array("SELECT price, delivery_id FROM {shop_delivery_thresholds}  WHERE delivery_id=%d AND amount<=%f ORDER BY amount DESC LIMIT 1", $_POST["delivery_id"], $summ))
			{
				$row["price"] = $this->format_summ($row["price"]);
				$delivery_summ = $row["price"];
				$delivery_id = $row["delivery_id"];
			}
			DB::query("UPDATE {shop_order} SET summ=%f, delivery_summ=%f, delivery_id=%d WHERE id=%d", $summ + $delivery_summ, $delivery_summ, $delivery_id, $this->diafan->id);

			$this->diafan->_payment->update_pay($this->diafan->id, 'cart', (! empty($_POST["payment_id"]) ? $_POST["payment_id"] : ''), $summ + $delivery_summ);
		}
		elseif($_POST["delivery_summ"] != $this->diafan->values('delivery_summ'))
		{
			$delivery_summ = $this->diafan->filter($_POST, "float", "delivery_summ");
			$delivery_summ = $this->format_summ($delivery_summ);
			DB::query("UPDATE {shop_order} SET summ=%f, delivery_summ=%f WHERE id=%d", $summ + $delivery_summ, $delivery_summ, $this->diafan->id);
		}
		else
		{
			$delivery_summ = $this->diafan->values('delivery_summ');
			$delivery_summ = $this->format_summ($delivery_summ);
		}
		if($summ + $delivery_summ != $this->diafan->values('summ'))
		{
			$this->diafan->_payment->update_pay($this->diafan->id, 'cart', (! empty($_POST["payment_id"]) ? $_POST["payment_id"] : ''), $summ + $delivery_summ);
		}
	}

	/**
	 * Сохранение поля "Способ оплаты"
	 * @return void
	 */
	public function save_variable_payment_id()
	{
		$pay_id = DB::query_result("SELECT id FROM {payment_history} WHERE module_name='cart' AND element_id=%d", $this->diafan->id);
		if($pay_id)
		{
			DB::query("UPDATE {payment_history} SET payment_id=%d WHERE id=%d", (! empty($_POST["payment_id"]) ? $_POST["payment_id"] : ''), $pay_id);
		}
		elseif(! empty($_POST["payment_id"]))
		{
			DB::query("INSERT INTO {payment_history} (payment_id, module_name, element_id, created, `summ`, code) VALUES (%d, 'cart', %d, %d, %f, '%s')", $_POST["payment_id"], $this->diafan->id, time(), $this->diafan->values("summ"), md5(mt_rand(0, 999999999)));
		}
	}

	/**
	 * Сохранение поля "Статус",
	 * отправка ссылок на купленные файлы при необходимости
	 *
	 * @return void
	 */
	public function save_variable_status_id()
	{
		if($this->diafan->values("status_id") == $_POST["status_id"])
			return;

		$status = DB::query_fetch_array("SELECT * FROM {shop_order_status} WHERE id=%d LIMIT 1", $_POST["status_id"]);
		$order = DB::query_fetch_array("SELECT * FROM {shop_order} WHERE id=%d LIMIT 1", $this->diafan->id);
		$this->diafan->_order->set_status($order, $status);
	}

	/**
	 * Заглушка информационного поля user_buy
	 *
	 * @return void
	 */
	public function save_variable_user_buy()
	{
	}

	/**
	 * Отправляет письмо пользователю, сделавшему заказ, если заказ создается из панели администрирования
	 *
	 * @return void
	 */
	public function save_variable_send_mail()
	{
		if(empty($_POST["is_new"]))
		{
			return;
		}

		$user_email = '';
		$user_phone = '';
		$user_fio = '';

		if(! empty($_POST["user_id"]) && $user = DB::query_fetch_array("SELECT * FROM {users} WHERE trash='0' AND id=%d", $_POST["user_id"]))
		{
			$user_email = $user["mail"];
			$user_phone = $user["phone"];
			$user_fio = $user["fio"];
		}

		$params = $this->diafan->_order->get_param($this->diafan->id);

		foreach ($params as $param)
		{
			if ($param["type"] == "email")
			{
				$user_email = $param["value"];
			}
			if ($param["info"] == "phone")
			{
				$user_phone = $param["value"];
			}
			if ($param["info"] == "name")
			{
				$user_fio = $param["value"];
			}
			$mess = array();
			// добавляем файлы
			switch($param["type"])
			{
				case "attachments":
					$m = $param["name"].':';
					foreach ($param["value"] as $a)
					{
						if ($a["is_image"])
						{
							$m .= ' <a href="'.$a["link"].'">'.$a["name"].'</a> <a href="'.$a["link"].'"><img src="'.$a["link_preview"].'"></a>';
						}
						else
						{
							$m .= ' <a href="'.$a["link"].'">'.$a["name"].'</a>';
						}
					}
					$mess[] = $m;
					break;

				default:
					if(is_array($param["value"]))
					{
						$mess[] = $param["name"].': '.implode(", ", $param["value"]);
					}
					else
					{
						$mess[] = $param["name"].($param["value"] ? ': '.$param["value"] : '');
					}
					break;
			}
		}

		if(in_array("subscription", $this->diafan->installed_modules))
		{
			if(! empty($user_phone))
			{
				$phone = preg_replace('/[^0-9]+/', '', $user_phone);
				if(! DB::query_result("SELECT id FROM {subscription_phones} WHERE phone='%s' AND trash='0'", $user_phone))
				{
					DB::query("INSERT INTO {subscription_phones} (phone, name, created, act) VALUES ('%s', '%h', %d, '1')", $user_phone, $user_fio, time());
				}
			}

			if (! empty($user_email))
			{
				$row_subscription = DB::query_fetch_array("SELECT * FROM {subscription_emails} WHERE mail='%s' AND trash='0' LIMIT 1", $user_email);

				if(empty($row_subscription))
				{
					$code = md5(rand(111, 99999));
					DB::query("INSERT INTO {subscription_emails} (created, mail, name, code, act) VALUES (%d, '%s', '%s', '%s', '1')", time(), $user_email, $user_fio, $code);
				}
				elseif(! $row_subscription["act"])
				{
					DB::query("UPDATE {subscription_emails} SET act='1', created=%d WHERE id=%d", $row_subscription['id'], time());
				}
			}
		}

		//send mail user
		if (empty($user_email))
		{
			return;
		}

		$cart = $this->diafan->_tpl->get('table_mail', 'cart', $this->diafan->_order->get($this->diafan->id));

		$payment_name = '';
		if(! empty($_POST["payment_id"]))
		{
			$payment = $this->diafan->_payment->get($_POST["payment_id"]);
			$payment_name = $payment["name"];
			if($payment["payment"] == 'non_cash')
			{
				$p = DB::query_fetch_array("SELECT code, id FROM {payment_history} WHERE module_name='cart' AND element_id=%d", $this->diafan->id);
				$payment_name .= ', <a href="'.BASE_PATH.'payment/get/non_cash/ul/'.$p["id"].'/'.$p["code"].'/">'.$this->diafan->_('Счет для юридических лиц', false).'</a>,
				<a href="'.BASE_PATH.'payment/get/non_cash/fl/'.$p["id"].'/'.$p["code"].'/">'.$this->diafan->_('Квитанция для физических лиц', false).'</a>';
			}
		}

		$subject = str_replace(
				array('%title', '%url', '%id'),
				array(TITLE, BASE_URL, $this->diafan->id),
				$this->diafan->configmodules('subject', 'shop')
			);

		$message = str_replace(
				array('%title', '%url', '%id', '%message', '%order', '%payment', '%fio'),
				array(
					TITLE,
					BASE_URL,
					$this->diafan->id,
					implode('<br>', $mess),
					$cart,
					$payment_name,
					$user_fio
				),
				$this->diafan->configmodules('message', 'shop')
			);
		$this->diafan->_postman->message_add_mail(
			$user_email,
			$subject,
			$message,
			$this->diafan->configmodules("emailconf", 'shop') ? $this->diafan->configmodules("email", 'shop') : EMAIL_CONFIG
		);
	}

	/**
	 * Сохранение поля "Расширения"
	 * @return void
	 */
	public function save_variable_backend()
	{
		$values = array();
		$rows_value = DB::query_fetch_all("SELECT * FROM {shop_order_backend_element} WHERE trash='0' AND order_id=%d", $this->diafan->id);
		foreach($rows_value as $row)
		{
			$values[$row["backend_id"]][$row["type"]][] = $value;
		}
		$this->diafan->values("backend", $values, true);
		
		$new_values = array();

		$rows = DB::query_fetch_all("SELECT * FROM {shop_order_backend} WHERE trash='0' AND act='1' ORDER BY sort ASC");
		foreach($rows as $row)
		{
			if(Custom::exists('modules/order/backend/'.$row["backend"].'/order.'.$row["backend"].'.admin.order.php'))
			{
				Custom::inc('modules/order/backend/'.$row["backend"].'/order.'.$row["backend"].'.admin.order.php');
				$name_class = 'Order_'.$row["backend"].'_admin_order';
				$class = new $name_class($this->diafan);
				if (is_callable(array(&$class, "save")))
				{
					$params = ($row["params"] ? unserialize($row["params"]) : array());
					$value = (! empty($values[$row["id"]]) ? $values[$row["id"]] : array());
					// возвращает массив, где ключ - type, а значение - массив значений
					$new_values[$row["id"]] = call_user_func_array(array(&$class, "save"), array($value, $params));
				}
			}
		}
		foreach($new_values as $backend_id => $array)
		{
			if(! is_array($array))
				continue;

			foreach($array as $type => $values)
			{
				if(! is_array($values))
					continue;
				foreach($values as $value)
				{
					if(! isset($values[$backend_id][$type]) || ! in_array($value, $values[$backend_id][$type]))
					{
						DB::query("INSERT INTO {shop_order_backend_element} (order_id, backend_id, type, value) VALUES (%d, %d, '%s' '%s')", $this->diafan->id, $backend_id, $type, $value);
					}
				}
			}
		}
		foreach($values as $backend_id => $array)
		{
			foreach($array as $type => $values)
			{
				foreach($values as $value)
				{
					if(! isset($new_values[$backend_id][$type]) || !is_array($new_values[$backend_id][$type]) || ! in_array($value, $new_values[$backend_id][$type]))
					{
						DB::query("DELETE FROM {shop_order_backend_element} WHERE order_id=%d AND backend_id=%d AND type='%s' AND value='%s'", $this->diafan->id, $backend_id, $type, $value);
					}
				}
			}
		}
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$order_good_ids = DB::query_fetch_value("SELECT id FROM {shop_order_goods} WHERE order_id IN (".implode(",", $del_ids).") AND trash='0'", "id");
		$this->diafan->del_or_trash_where("shop_order_goods", "id IN (".implode(",", $order_good_ids).")");
		$this->diafan->del_or_trash_where("shop_order_goods_param", "order_goods_id IN (".implode(',', $order_good_ids).")");
		$this->diafan->del_or_trash_where("shop_order_param_element", "element_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_order_backend_element", "order_id IN (".implode(",", $del_ids).")");
	}
}
