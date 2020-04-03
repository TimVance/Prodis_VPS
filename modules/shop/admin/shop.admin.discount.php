<?php
/**
 * Редактирование скидок
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
 * Shop_admin_discount
 */
class Shop_admin_discount extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_discount';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'title1' => array(
				'type' => 'title',
				'name' => 'Размер скидки',
			),
			'info' => array(
				'type' => 'function',
				'name' => 'Информационное поле',
				'no_save' => true,
				'hide' => true,
			),
			'discount' => array(
				'type' => 'floattext',
				'name' => 'Скидка в процентах (%)',
				'help' => 'Если заполнено это поле, скидка будет считаться в процентах от исходной цены.',
			),
			'deduction' => array(
				'type' => 'floattext',
				'name' => 'Скидка в виде фиксированной суммы',
				'help' => 'Если заполнено это поле, скидка будет вычитаться от исходной цены в виде фиксированной суммы.',
			),
			'text' => array(
				'type' => 'textarea',
				'name' => 'Комментарий для администратора',
				'help' => 'Поле видно только администратору.',
			),
			'title2' => array(
				'type' => 'title',
				'name' => 'На отдельные категории и товары',
			),
			'amount' => array(
				'type' => 'floattext',
				'name' => 'Cкидка действует на товары дороже',
				'help' => 'Cкидка будет применяться только к тем товарам, которые дороже указанной суммы.',
			),
			'object' => array(
				'type' => 'function',
				'name' => 'Объект',
				'help' => 'Товары и категории, на которые распространяется скидка. Если не указаны, то скидка считается общей.',
			),
			'title3' => array(
				'type' => 'title',
				'name' => 'На весь заказ',
			),
			'threshold' => array(
				'type' => 'numtext',
				'name' => 'Скидка действует от общей суммы заказа',
				'help' => 'Скидка начнет действовать когда пользователь наберет в корзину товаров на указанную сумму. Если заполнено, то скидка применяется только в корзине товаров на общую сумму.',
			),
			'threshold_cumulative' => array(
				'type' => 'numtext',
				'name' => 'Накопительная скидка от суммы ранее оплаченных заказов',
				'help' => 'Скидка начнет действовать когда пользователь оплатит товаров на указанную сумму. Если заполнено, то скидка применяется только в корзине товаров на общую сумму.',
			),
			'title4' => array(
				'type' => 'title',
				'name' => 'Купоны',
			),
			'coupon' => array(
				'type' => 'function',
				'name' => 'Код купона',
				'help' => 'Пользователь должен активировать на сайте этот код, чтобы получить скидку.',
			),
			'title5' => array(
				'type' => 'title',
				'name' => 'Для отдельных пользователей',
			),
			'role_id' => array(
				'type' => 'select',
				'name' => 'Группы покупателей',
				'help' => 'Скидка будет применяться ко всей группе пользователей.',
				'select_db' => array(
					'table' => 'users_role',
					'name' => '[name]',
					'empty' => 'Все',
					'where' => "trash='0'",
					'order' => 'sort ASC',
				),
			),
			'person' => array(
				'type' => 'function',
				'name' => 'ID',
				'help' => 'Если есть пользователи, использующие скидку, то скидка считается персонализированной и другим пользователям не применяется.',
			),
			'title1' => array(
				'type' => 'title',
				'name' => 'Активировать скидку',
			),
			'date_period' => array(
				'type' => 'datetime',
				'name' => 'Период действия скидки',
				'help' => 'Если выбрать период действия скидки, она будет применяться только в указанное время.',
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Активировать скидку',
				'help' => 'Если отметить, скидка будет опубликована на сайте и примениться ко всем товарам, отвечающим условиям выше.',
				'default' => true,
			),
		),
	);

	/**
	 * @var array названия табов
	 */
	public $tabs_name = array(
		'main' => 'Размер скидки',
		'goods' => 'На отдельные категории и товары',
		'order' => 'На весь заказ',
		'coupon' => 'Купоны',
		'person' => 'Для отдельных пользователей',
		'actdis' => 'Активировать скидку',
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'name' => array(
			'variable' => 'discount',
		),
		'deduction' => array(
			'sql' => true,
		),
		'text' => array(
			'type' => 'text',
			'sql' => true,
		),
		'actions' => array(
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'date_start' => array(
			'name' => 'Искать по дате',
			'type' => 'datetime',
		),
		'date_finish' => array(
			'type' => 'datetime',
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить скидку');
	}

	/**
	 * Выводит список заказов
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Формирует основную ссылку для элемента в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_name($row, $var)
	{
		if(! empty($row["deduction"]))
		{
			$name = $row["deduction"].' '.$this->diafan->configmodules("currency", "shop");
		}
		else
		{
			$name = $row["discount"].' %';
		}

		$link = '<div class="name"><a href="';
		if ($this->diafan->_users->roles('init', $this->diafan->_admin->rewrite))
		{

			$link .= $this->diafan->_route->current_admin_link().'edit'.$row["id"].'/'.$this->diafan->get_nav.'" title="'.$this->diafan->_('Редактировать').' ('.$row["id"].')';
		}
		else
		{
			$link .= '#';
		}
		$link .= '" name="'.$row["id"].'">'.$name.'</a>';
		$link .= $this->diafan->list_variable_date_period($row, array());
		$link .= '</div>';
		return $link;
	}

	/**
	 * Редактирование поля "Информационное поле"
	 * @return void
	 */
	public function edit_variable_info()
	{
		echo '<div class="discount_info" style="color: green;text-align:center"></div>
		<script type="text/javascript">
		var lang_shop_discout = {
			"discount" : "'.$this->diafan->_('Скидка').'",
			"currency" : "'.$this->diafan->configmodules('currency', 'shop').'",
			"category" : "'.$this->diafan->_('действует на категории').'",
			"goods" : "'.$this->diafan->_('действует на товары').'",
			"all" : "'.$this->diafan->_('действует на все категории и товары').'",
			"group" : "'.$this->diafan->_('для группы покупателей').'",
			"user" : "'.$this->diafan->_('для пользователей с ID').'",
		}
		</script>';
	}

	/**
	 * Редактирование поля "Объект"
	 * @return void
	 */
	public function edit_variable_object()
	{
		$marker = "&nbsp;&nbsp;";
		$cs = DB::query_fetch_all("SELECT id, [name], parent_id FROM {shop_category} WHERE trash='0' ORDER BY sort ASC, id ASC LIMIT 1000");
		if(count($cs) < 1000)
		{
			foreach($cs as $c)
			{
				$cats[$c["parent_id"]][] = $c;
			}
		}

		$values = array();
		if (! $this->diafan->is_new)
		{
			$values = DB::query_fetch_value("SELECT cat_id FROM {shop_discount_object} WHERE discount_id='%d' AND cat_id>0", $this->diafan->id, "cat_id");
		}
		Custom::inc('modules/shop/admin/shop.admin.view.php');

		if(! empty($cats))
		{
			echo '
			<div class="unit">
				<div class="infofield">
					'.$this->diafan->_('Категории').'
				</div>
				<select name="cat_ids[]" multiple="multiple" size="11">
				<option value="all"'.(empty($values) ? ' selected' : '').'>'.$this->diafan->_('Все').'</option>'
				.$this->diafan->get_options($cats, $cats[0], $values).'
				</select>
			</div>';
		}
		echo '<div class="unit">
			<div class="infofield">
				'.$this->diafan->_('Отдельные товары').'
				'.$this->diafan->help('Вы можете назначить скидку только на некоторые конкретные товары.').'
			</div>';
			echo '<div class="rel_elements">';
			if (! $this->diafan->is_new)
			{
				$view = new Shop_admin_view($this->diafan);
				echo $view->discount_goods($this->diafan->id);
			}
			echo '</div>
			<a href="javascript:void(0)" class="rel_module_plus" title="'.$this->diafan->_('Добавить').'"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить').'</a>';
			echo '<p>'.$this->diafan->_('Чтобы назначить скидку отдельному товару, Вы также можете просто заполнить поле старая цена при его редактировании').'</p>';
		echo '</div>';
	}

	/**
	 * Редактирование поля "Пользователь"
	 * @return void
	 */
	public function edit_variable_person()
	{
		$persons = array();
		if(! $this->diafan->is_new)
		{
			$persons = DB::query_fetch_all("SELECT p.*, CONCAT(u.fio,' (', u.name, ')') AS user FROM {shop_discount_person} AS p"
			." LEFT JOIN {users} AS u ON u.id=p.user_id"
			." WHERE p.discount_id=%d", $this->diafan->id);
		}
		$coupon = $this->diafan->values("coupon");
		echo '
		<div class="unit param_container">';
		foreach ($persons as $row)
		{
			echo '<div class="param">
				<input type="hidden" name="person_id[]" value="'.$row["id"].'">
				<div class="infofield">ID'.$this->diafan->help().'</div>';
			if($row["user"])
			{
				echo '<a href="'.BASE_PATH_HREF.'users/edit'.$row["user_id"].'/">'.$row["user"].'</a> ';
			}
				echo '<input type="text" name="person_user_id[]" size="4" value="'.($row["user_id"] ? $row["user_id"] : '').'">
				'.($row["session_id"] ? 'session_id: '.$row["session_id"] : '').'
				'.($row["coupon_id"] && ! empty($coupon[$row["coupon_id"]]) ? ' '.$this->diafan->_('Добавлен по купону').' '.$coupon[$row["coupon_id"]] : '').'
				<input type="hidden" name="person_session_id[]" value="'.$row["session_id"].'">
				<span class="param_actions">
					<a href="javascript:void(0)" action="delete_param" class="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
				</span>
			</div>';

		}
		echo '<div class="param">
				<div class="infofield">ID'.$this->diafan->help().'</div>
				<input type="text" name="person_user_id[]" size="4" value="">
				<span class="param_actions">
					<a href="javascript:void(0)" action="delete_param" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'" style="display:none" class="delete"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
				</span>
			</div>
			<p><a href="javascript:void(0)" class="param_plus" title="'.$this->diafan->_('Добавить').'"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить').'</a></p>
		</div>';
	}

	/**
	 * Редактирование поля "Код купона"
	 * @return void
	 */
	public function edit_variable_coupon()
	{
		$coupons = array();
		$rs = array();
		if(! $this->diafan->is_new)
		{
			$coupons = DB::query_fetch_all("SELECT * FROM {shop_discount_coupon} WHERE discount_id=%d", $this->diafan->id);
			foreach ($coupons as $row)
			{
				$rs[$row["id"]] = $row["coupon"];
			}
			$this->diafan->values("coupon", $rs, true);
		}
		echo '
		<div class="unit param_container" id="coupon">';
		foreach ($coupons as $row)
		{
			echo '<div class="unit param">
				<div class="infofield">'.$this->diafan->_('Код купона').$this->diafan->help().'</div>
				<input type="hidden" name="coupon_id[]" value="'.$row["id"].'">
				<input type="text" name="coupon[]" value="'.$row["coupon"].'">
				<a href="javascript:void(0)" class="coupon_generate">'.$this->diafan->_('сгенерировать').'</a><br>
				<div class="infofield">'.$this->diafan->_('Сколько раз можно использовать купон').'
				'.$this->diafan->help('Скидка становится неактивной, если она исчерпала этот лимит. Если поле не заполнено, ограничение по количеству раз не действует.').'</div>
				<input type="number" name="coupon_count_use[]" size="4" value="'.($row["count_use"] ? $row["count_use"] : '').'">
				'.($row["used"] ? ' '.$this->diafan->_('Использован').': '.$row["used"] : '').'
				<span class="param_actions">
					<a href="javascript:void(0)" action="delete_param" class="delete"  confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
				</span>
				<h2></h2>
				</div>';

		}
		echo '<div class="unit param">
				<div class="infofield">'.$this->diafan->_('Код купона').$this->diafan->help().'</div>
				<input type="text" name="coupon[]" value="">
				<a href="javascript:void(0)" class="coupon_generate">'.$this->diafan->_('сгенерировать').'</a>
				<div class="infofield">'.$this->diafan->_('Сколько раз можно использовать купон').'
				'.$this->diafan->help('Скидка становится неактивной, если она исчерпала этот лимит. Если поле не заполнено, ограничение по количеству раз не действует.').'</div>
				<input type="text" name="coupon_count_use[]" size="4" value="">
				<span class="param_actions">
					<a href="javascript:void(0)" action="delete_param" class="delete"  confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'" style="display:none"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
				</span>
				<h2></h2>
			</div>
			<a href="javascript:void(0)" class="param_plus" title="'.$this->diafan->_('Добавить').'"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить').'</a>
		</div>';
	}

	/**
	 * Проверка поля "Купон"
	 * @return void
	 */
	public function validate_variable_coupon()
	{
		if(! empty($_POST["coupon"]) && ! $this->diafan->configmodules("discount_code", "shop", $this->diafan->_route->site))
		{
			$coupon_codes = array();
			foreach($_POST["coupon"] as $key => $coupon)
			{
				if(empty($coupon)) continue;
				$coupon_id = ! empty($_POST["coupon_id"][$key]) ? $_POST["coupon_id"][$key] : 0;
				if(DB::query_result("SELECT COUNT(*) FROM {shop_discount_coupon} WHERE coupon='%s' AND id NOT IN(%d, 0)", $coupon, $coupon_id))
				{
					$coupon_codes[] = $coupon;
				}

			}
			if(! empty($coupon_codes))
			{
				$error = $this->diafan->_('Для купонов уже используются следующие коды: %s. Если необходимо использовать несколько купонов с одинаковым кодом активации, то измените настройки модуля.', implode(", ", $coupon_codes));
				$this->diafan->set_error("coupon", $error);
			}
		}
	}

	/**
	 * Сохранение поля "Объект"
	 * @return void
	 */
	public function save_variable_object()
	{
		DB::query("DELETE FROM {shop_discount_object} WHERE discount_id=%d AND good_id=0", $this->diafan->id);
		if(! empty($_POST["cat_ids"]) && in_array("all", $_POST["cat_ids"]))
		{
			$_POST["cat_ids"] = array();
		}

		if (! empty($_POST["cat_ids"]))
		{
			foreach ($_POST["cat_ids"] as $id)
			{
				if($id)
				{
					DB::query("INSERT INTO {shop_discount_object} (discount_id, cat_id) VALUES (%d, %d)", $this->diafan->id, $id);
				}
			}
		}
		elseif (! DB::query_result("SELECT id FROM {shop_discount_object} WHERE discount_id=%d LIMIT 1", $this->diafan->id))
		{
			DB::query("INSERT INTO {shop_discount_object} (discount_id) VALUES (%d)", $this->diafan->id);
		}
		DB::query("UPDATE {shop_discount} SET act='%d' WHERE id=%d", ! empty($_POST["act"]) ? '1' : '0', $this->diafan->id);
	}

	/**
	 * Сохранение поля "Пользователь"
	 * @return void
	 */
	public function save_variable_person()
	{
		$person = 0;
		if(! empty($_POST["person_user_id"]))
		{
			foreach ($_POST["person_user_id"] as $i => $user_id)
			{
				$user_id = intval($user_id);
				if(! empty($_POST["person_id"][$i]))
				{
					if(! empty($user_id) || ! empty($_POST["person_session_id"][$i]))
					{
						DB::query("UPDATE {shop_discount_person} SET user_id=%d, session_id='%s' WHERE id=%d AND discount_id=%d", $user_id,$_POST["person_session_id"][$i], $_POST["person_id"][$i], $this->diafan->id);
						$id = intval($_POST["person_id"][$i]);
						if($id)
						{
							$ids[] = $id;
						}
					}
				}
				else
				{
					if($user_id)
					{
						$ids[] = DB::query("INSERT INTO {shop_discount_person} (user_id, discount_id) VALUES (%d, %d)", $user_id, $this->diafan->id);
					}
				}
			}
			if(! empty($ids))
			{
				$person = 1;
			}
		}
		DB::query("DELETE FROM {shop_discount_person} WHERE ".(! empty($ids) ? "id NOT IN(".implode(",", $ids).") AND" : "")." discount_id=%d", $this->diafan->id);
		if(! $person && ! empty($_POST["coupon"]))
		{
			foreach ($_POST["coupon"] as $c)
			{
				if(! empty($c))
				{
					$person = 1;
				}
			}
		}
		$this->diafan->set_query("person='%d'");
		$this->diafan->set_value($person);
	}

	/**
	 * Сохранение поля "Группы покупателей"
	 * @return void
	 */
	public function save_variable_role_id()
	{
		$this->diafan->set_query("role_id=%d");
		$this->diafan->set_value($_POST["role_id"]);
	}

	/**
	 * Сохранение поля "Купон"
	 * @return void
	 */
	public function save_variable_coupon()
	{
		if(! empty($_POST["coupon"]))
		{
			foreach ($_POST["coupon"] as $i => $coupon)
			{
				if(! empty($_POST["coupon_id"][$i]))
				{
					if(! empty($coupon))
					{
						DB::query("UPDATE {shop_discount_coupon} SET coupon='%h', count_use=%d WHERE id=%d AND discount_id=%d", $coupon, $_POST["coupon_count_use"][$i], $_POST["coupon_id"][$i], $this->diafan->id);
						$id = intval($_POST["coupon_id"][$i]);
						if($id)
						{
							$ids[] = $id;
						}
					}
				}
				else
				{
					if($coupon)
					{
						$ids[] = DB::query("INSERT INTO {shop_discount_coupon} (coupon, count_use, discount_id) VALUES ('%h', %d, %d)", $coupon, $_POST["coupon_count_use"][$i], $this->diafan->id);
					}
				}
			}
		}
		DB::query("DELETE FROM {shop_discount_coupon} WHERE ".(! empty($ids) ? "id NOT IN(".implode(",", $ids).") AND" : "")." discount_id=%d", $this->diafan->id);
	}

	/**
	 * Пользовательская функция, выполняемая перед редиректом при сохранении скидки
	 *
	 * @return void
	 */
	public function save_redirect()
	{
		$this->diafan->_shop->price_calc(0, $this->diafan->id);
		parent::__call('save_redirect', array());
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		foreach($del_ids as $del_id)
		{
			$this->diafan->_shop->price_calc(0, $del_id);
		}
		$this->diafan->del_or_trash_where("shop_discount_object", "discount_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_discount_person", "discount_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_discount_coupon", "discount_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_price", "discount_id IN (".implode(",", $del_ids).")");
	}
}
