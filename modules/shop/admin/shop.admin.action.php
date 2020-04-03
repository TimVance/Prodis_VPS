<?php
/**
 * Обработка POST-запросов в административной части модуля
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
 * Shop_admin_action
 */
class Shop_admin_action extends Action_admin
{
	/**
	 * Вызывает обработку POST-запросов
	 *
	 * @return void
	 */
	public function init()
	{
		if ( ! empty($_POST["action"]))
		{
			switch ($_POST["action"])
			{
				case 'show_discount_goods':
					$this->show_discount_goods();
					break;

				case 'discount_good':
					$this->discount_good();
					break;

				case 'delete_discount_good':
					$this->delete_discount_good();
					break;

				case 'list_site_id':
					$this->list_site_id();
					break;

				case 'optimize_price':
					$this->optimize_price();
					break;

				case 'table_params_refresh':
					$this->table_params_refresh();
					break;
			}
		}
	}

	/**
	 * Подгружает список товаров
	 *
	 * @return void
	 */
	private function show_discount_goods()
	{
		if (empty($_POST["discount_id"]))
		{
			$_POST["discount_id"] = 0;
		}
		$nastr = 16;
		$list = '';
		if (empty($_POST["page"]))
		{
			$start = 0;
			$page = 1;
			if ( ! isset($_POST["search"]))
			{
				$list .= '<div class="fa fa-close ipopup__close"></div>
				<div class="ipopup__heading">'.$this->diafan->_('Товары').'</div>
				<div class="infofield">'.$this->diafan->_('Поиск').'</div> <input type="text" size="30" class="rel_module_search">
				<div class="rel_all_elements_container">';
			}
		}
		else
		{
			$page = intval($_POST["page"]);
			$start = ($page - 1) * $nastr;
		}
		$list .= '<div class="rel_all_elements">';
		$discount_goods = array();
		if ($_POST["discount_id"])
		{
			$discount_goods = DB::query_fetch_value("SELECT good_id FROM {shop_discount_object} WHERE discount_id=%d", $_POST["discount_id"], "good_id");
		}
		if (! empty($_POST["search"]))
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0' AND (LOWER([name]) LIKE LOWER('%%%h%%') OR LOWER(article) LIKE LOWER('%%%h%%'))", $_POST["search"], $_POST["search"]);
			$rows = DB::query_range_fetch_all("SELECT id, [name] FROM {shop} WHERE trash='0' AND (LOWER([name]) LIKE LOWER('%%%h%%') OR LOWER(article) LIKE LOWER('%%%h%%'))", $_POST["search"], $_POST["search"], $start, $nastr);
		}
		else
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0'");
			$rows = DB::query_range_fetch_all("SELECT id, [name] FROM {shop} WHERE trash='0'", $start, $nastr);
		}
		foreach ($rows as $row)
		{
			$img = DB::query_fetch_array("SELECT name, folder_num FROM {images} WHERE element_id=%d AND module_name='shop' AND element_type='element' AND trash='0' ORDER BY sort ASC LIMIT 1", $row["id"]);
			$list .= '<div class="rel_module" element_id="'.$row["id"].'">
			<div'.(in_array($row["id"], $discount_goods) ? ' class="rel_module_selected"' : '').'>
			'.($img ? '<a href="javascript:void(0)"><img src="'.BASE_PATH.USERFILES.'/small/'.($img["folder_num"] ? $img["folder_num"].'/' : '').$img["name"].'"></a><br>' : '').'
			<a href="javascript:void(0)">'.$row["name"].'</a>
			</div>
			</div>';
		}
		$list .= '</div><div class="rel_module_navig paginator">';
		for ($i = 1; $i <= ceil($count / $nastr); $i ++ )
		{
			if ($i != $page)
			{
				$list .= '<a href="javascript:void(0)" page="'.$i.'">'.$i.'</a> ';
			}
			else
			{
				$list .= '<span class="active">'.$i.'</span>';
			}
		}
		$list .= '</div>';
		if (empty($_POST["page"]) && ! isset($_POST["search"]))
		{
			$list .= '</div>';
		}

		$this->result["data"] = $list;
	}

	/**
	 * Прикрепляет скидку к товару
	 *
	 * @return void
	 */
	private function discount_good()
	{
		if ( ! $_POST["discount_id"])
		{
			$_POST["discount_id"] = DB::query("INSERT INTO {shop_discount} () VALUES ()");
			$this->result["id"] = $_POST["discount_id"];
		}
		Custom::inc('modules/shop/admin/shop.admin.view.php');
		if ( ! DB::query_result("SELECT id FROM {shop_discount_object} WHERE good_id=%d AND discount_id=%d LIMIT 1", $_POST["good_id"], $_POST["discount_id"]))
		{
			DB::query("INSERT INTO {shop_discount_object} (good_id, discount_id) VALUES (%d, %d)", $_POST["good_id"], $_POST["discount_id"]);
		}

		$shop_admin_view = new Shop_admin_view($this->diafan);
		$this->result["data"] = $shop_admin_view->discount_goods($_POST["discount_id"]);
	}

	/**
	 * Удаляет скидку на товар
	 *
	 * @return void
	 */
	private function delete_discount_good()
	{
		DB::query("DELETE FROM {shop_discount_object} WHERE good_id=%d AND discount_id=%d", $_POST['good_id'], $_POST['discount_id']);

		$this->diafan->_cache->delete("", $this->diafan->_admin->module);
	}

	/**
	 * Подгружает список модулей
	 *
	 * @return void
	 */
	private function list_site_id()
	{
		if (! $_POST["parent_id"])
		{
			$list = '<div class="fa fa-close ipopup__close"></div>
			<div class="menu_list menu_list_first"><div class="ipopup__heading">'.$this->diafan->_('Страницы сайта').'</div>';
		}
		else
		{
			$list = '<div class="menu_list">';
		}

		$rows = DB::query_fetch_all("SELECT id, [name], module_name, count_children FROM {site} WHERE [act]='1' AND trash='0' AND parent_id=%d ORDER BY sort ASC", $_POST["parent_id"]);
		foreach ($rows as $row)
		{
			$list .= '<p site_id="'.$row["id"].'" module_name="site" element_id="" cat_id="">';
			if ($row["count_children"])
			{
				$list .= '<a href="javascript:void(0)" class="plus menu_plus">+</a>';
			}
			else
			{
				$list .= '&nbsp;&nbsp;';
			}
			$list .= '&nbsp;<a href="'.BASE_PATH_HREF.'site/edit'.$row["id"].'/" class="menu_select">'.$row["name"].'</a></p>';
		}
		$list .= '</div>';

		$this->result["data"] = $list;
	}

	/**
	 * Оптимизировать таблицу БД цены товаров
	 *
	 * @return void
	 */
	private function optimize_price()
	{
		$service = $this->diafan->filter($_POST, "string", "service");
		$service = preg_replace('/[^a-zA-Z0-9_:;]/', '', $service);
		$service = $this->diafan->str_to_array($service, ';', ':');

		$mode_optimize = $this->diafan->filter($_POST, "integer", "mode_optimize");
		$mode_optimize = ! empty($mode_optimize);

		if($mode_optimize && (! defined('MOD_DEVELOPER_TECH') || ! MOD_DEVELOPER_TECH))
		{
			$messages = $this->diafan->_('Ошибка: оптимизация прервана. Необходимо перевести сайт в режим обслуживания.');
			$this->result["error"] = false;
			$this->result["messages"] = '<div class="error">'.$messages.'</div>';
			return false;
		}

		$max = 500; $sleep = 1;
		$this->diafan->set_time_limit();
		$part = $this->diafan->filter($_POST, "integer", "part");
		$iteration = $this->diafan->filter($_POST, "integer", "iteration");

		// пропускаем итерации, если не режим оптимизации
		$part = ! $mode_optimize && $part >= 13 ? $part + 4 : $part;

		switch($part)
		{
			case 0:
				$messages = $this->diafan->_('Начинаем процесс проверки ...');
				$count = 0;
				$rows = array();
				break;

			case 1:
				sleep($sleep);
				$messages = $this->diafan->_('Проверка характеристик ...');
				$count = 0;
				$rows = array();
				break;

			case 2:
				$messages = $this->diafan->_('Проверка характеристик ...').' %s%%';
				$count = DB::query_result("SELECT COUNT(*) FROM {shop_param} WHERE trash='0'");
				$rows = DB::query_fetch_all("SELECT id, type, required FROM {shop_param} WHERE trash='0' LIMIT %d, %d", $max * $iteration, $max);
				foreach ($rows as $row)
				{
					if (in_array($row["type"], array('select', 'multiple'))) continue;

					if(! $mode_optimize)
					{
						$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_select} WHERE param_id=%d AND trash='0'", $row["id"]);
						$service['shop_param_select'] = ! empty($service['shop_param_select']) ? $service['shop_param_select'] : 0;
						$service['shop_param_select'] += $query_result;
					}
					else
					{
						// удаляем списки характеристик кроме 'select', 'multiple'
						DB::query("DELETE FROM {shop_param_select} WHERE param_id=%d AND trash='0'", $row["id"]);
					}
				}
				break;

			case 3:
				sleep($sleep);
				$messages = $this->diafan->_('Проверка значений характеристик ...');
				$count = 0;
				$rows = array();
				break;

			case 4:
				$messages = $this->diafan->_('Проверка значений характеристик ...').' %s%%';
				$count = $max * 6;
				switch($iteration)
				{
					case 0:
						$rows = array(true);

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_select} LEFT JOIN {shop_param} ON {shop_param_select}.param_id={shop_param}.id AND {shop_param_select}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
							$service['shop_param_select'] = ! empty($service['shop_param_select']) ? $service['shop_param_select'] : 0;
							$service['shop_param_select'] += $query_result;
						}
						else
						{
							// удаляем списки характеристик, указывающие на не существующие виды характеристики
							DB::query("DELETE {shop_param_select} FROM {shop_param_select} LEFT JOIN {shop_param} ON {shop_param_select}.param_id={shop_param}.id AND {shop_param_select}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
						}
						break;

					case 1:
						$rows = array(true);

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_element} LEFT JOIN {shop_param} ON {shop_param_element}.param_id={shop_param}.id AND {shop_param_element}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
							$service['shop_param_element'] = ! empty($service['shop_param_element']) ? $service['shop_param_element'] : 0;
							$service['shop_param_element'] += $query_result;
						}
						else
						{
							// удаляем значения характеристик, указывающие на не существующие виды характеристики
							DB::query("DELETE {shop_param_element} FROM {shop_param_element} LEFT JOIN {shop_param} ON {shop_param_element}.param_id={shop_param}.id AND {shop_param_element}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
						}
						break;

					case 2:
						$rows = array(true);

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_element} LEFT JOIN {shop} ON {shop_param_element}.element_id={shop}.id AND {shop_param_element}.trash='0' AND {shop}.trash='0' WHERE {shop}.id IS NULL");
							$service['shop_param_element'] = ! empty($service['shop_param_element']) ? $service['shop_param_element'] : 0;
							$service['shop_param_element'] += $query_result;
						}
						else
						{
							// удаляем значения характеристик, указывающие на не существующие товары
							DB::query("DELETE {shop_param_element} FROM {shop_param_element} LEFT JOIN {shop} ON {shop_param_element}.element_id={shop}.id AND {shop_param_element}.trash='0' AND {shop}.trash='0' WHERE {shop}.id IS NULL");
						}
						break;

					case 3:
						$rows = array(true);

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_element} LEFT JOIN {shop_param} ON {shop_param_element}.param_id={shop_param}.id AND {shop_param_element}.trash='0' AND {shop_param}.trash='0' LEFT JOIN {shop} ON {shop_param_element}.element_id={shop}.id AND {shop}.trash='0' WHERE {shop_param}.site_id<>0 AND {shop_param}.site_id<>{shop}.site_id");
							$service['shop_param_element'] = ! empty($service['shop_param_element']) ? $service['shop_param_element'] : 0;
							$service['shop_param_element'] += $query_result;
						}
						else
						{
							// удаляем значения характеристик, не принадлежащие странице, к которой прикреплен модуль
							DB::query("DELETE {shop_param_element} FROM {shop_param_element} LEFT JOIN {shop_param} ON {shop_param_element}.param_id={shop_param}.id AND {shop_param_element}.trash='0' AND {shop_param}.trash='0' LEFT JOIN {shop} ON {shop_param_element}.element_id={shop}.id AND {shop}.trash='0' WHERE {shop_param}.site_id<>0 AND {shop_param}.site_id<>{shop}.site_id");
						}
						break;

					case 4:
						$rows = array(true);

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_category_rel} LEFT JOIN {shop_param} ON {shop_param_category_rel}.element_id={shop_param}.id AND {shop_param_category_rel}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
							$service['shop_param_category_rel'] = ! empty($service['shop_param_category_rel']) ? $service['shop_param_category_rel'] : 0;
							$service['shop_param_category_rel'] += $query_result;
						}
						else
						{
							// удаляем связь харктеристик с категориями, указывающие на не существующие харктеристики
							DB::query("DELETE {shop_param_category_rel} FROM {shop_param_category_rel} LEFT JOIN {shop_param} ON {shop_param_category_rel}.element_id={shop_param}.id AND {shop_param_category_rel}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
						}
						break;

					case 5:
						$rows = array();

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_category_rel} LEFT JOIN {shop_category} ON {shop_param_category_rel}.cat_id={shop_category}.id AND {shop_param_category_rel}.trash='0' AND {shop_category}.trash='0' WHERE {shop_param_category_rel}.cat_id<>0 AND {shop_category}.id IS NULL");
							$service['shop_param_category_rel'] = ! empty($service['shop_param_category_rel']) ? $service['shop_param_category_rel'] : 0;
							$service['shop_param_category_rel'] += $query_result;
						}
						else
						{
							// удаляем связь харктеристик с категориями, указывающие на не существующие категории
							DB::query("DELETE {shop_param_category_rel} FROM {shop_param_category_rel} LEFT JOIN {shop_category} ON {shop_param_category_rel}.cat_id={shop_category}.id AND {shop_param_category_rel}.trash='0' AND {shop_category}.trash='0' WHERE {shop_param_category_rel}.cat_id<>0 AND {shop_category}.id IS NULL");
						}
						break;

					default:
						$rows = array();
						break;
				}
				break;

			case 5:
				sleep($sleep);
				$messages = $this->diafan->_('Проверка параметров цен ...');
				$count = 0;
				$rows = array();
				break;

			case 6:
				$messages = $this->diafan->_('Проверка параметров цен ...').' %s%%';
				$count = $max * 9;
				switch($iteration)
				{
					case 0:
						$rows = array(true);

						// удаляем цены, указывающие на не существующие товары
						$ids = DB::query_fetch_value("SELECT p.price_id FROM {shop_price} AS p LEFT JOIN {shop} AS s ON p.good_id=s.id AND p.trash='0' AND s.trash='0' WHERE p.id=p.price_id AND s.id IS NULL", "price_id");
						if(! empty($ids))
						{
							if(! $mode_optimize)
							{
								$service['shop_price'] = ! empty($service['shop_price']) ? $service['shop_price'] : 0;
								$service['shop_price'] += count($ids);
								foreach($ids as $id)
								{
									$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} WHERE price_id=%d", $id);
									$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
									$service['shop_price_param'] += $query_result;
									$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_image_rel} WHERE price_id=%d", $id);
									$service['shop_price_image_rel'] = ! empty($service['shop_price_image_rel']) ? $service['shop_price_image_rel'] : 0;
									$service['shop_price_image_rel'] += $query_result;
								}
							}
							else
							{
								foreach($ids as $id)
								{
									DB::query("DELETE FROM {shop_price} WHERE price_id=%d", $id);
									DB::query("DELETE FROM {shop_price_param} WHERE price_id=%d", $id);
									DB::query("DELETE FROM {shop_price_image_rel} WHERE price_id=%d", $id);
								}
							}
						}
						break;

					case 1:
						$rows = array(true);

						// удаляем цены, идентификаторы исходных цен которых указывают на не существующие цены
						$ids = DB::query_fetch_value("SELECT a.price_id FROM {shop_price} AS a LEFT JOIN {shop_price} AS b ON a.price_id=b.id WHERE a.id<>a.price_id AND b.id IS NULL", "price_id");
						if(! empty($ids))
						{
							if(! $mode_optimize)
							{
								$service['shop_price'] = ! empty($service['shop_price']) ? $service['shop_price'] : 0;
								$service['shop_price'] += count($ids);
								foreach($ids as $id)
								{
									$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} WHERE price_id=%d", $id);
									$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
									$service['shop_price_param'] += $query_result;
									$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_image_rel} WHERE price_id=%d", $id);
									$service['shop_price_image_rel'] = ! empty($service['shop_price_image_rel']) ? $service['shop_price_image_rel'] : 0;
									$service['shop_price_image_rel'] += $query_result;
								}
							}
							else
							{
								foreach($ids as $id)
								{
									DB::query("DELETE FROM {shop_price} WHERE price_id=%d", $id);
									DB::query("DELETE FROM {shop_price_param} WHERE price_id=%d", $id);
									DB::query("DELETE FROM {shop_price_image_rel} WHERE price_id=%d", $id);
								}
							}
						}
						break;

					case 2:
						$rows = array(true);

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_image_rel} LEFT JOIN {images} ON {shop_price_image_rel}.image_id={images}.id AND {shop_price_image_rel}.trash='0' AND {images}.trash='0' WHERE {images}.id IS NULL");
							$service['shop_price_image_rel'] = ! empty($service['shop_price_image_rel']) ? $service['shop_price_image_rel'] : 0;
							$service['shop_price_image_rel'] += $query_result;
						}
						else
						{
							// удаляем связь цен с изображениями, указывающую на не существующие картинки
							DB::query("DELETE {shop_price_image_rel} FROM {shop_price_image_rel} LEFT JOIN {images} ON {shop_price_image_rel}.image_id={images}.id AND {shop_price_image_rel}.trash='0' AND {images}.trash='0' WHERE {images}.id IS NULL");
						}
						break;

					case 3:
						$rows = array(true);

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_image_rel} LEFT JOIN {shop_price} ON {shop_price_image_rel}.price_id={shop_price}.id AND {shop_price_image_rel}.trash='0' AND {shop_price}.trash='0' WHERE {shop_price}.id IS NULL");
							$service['shop_price_image_rel'] = ! empty($service['shop_price_image_rel']) ? $service['shop_price_image_rel'] : 0;
							$service['shop_price_image_rel'] += $query_result;
						}
						else
						{
							// удаляем связь цен с изображениями, указывающую на не существующие цены
							DB::query("DELETE {shop_price_image_rel} FROM {shop_price_image_rel} LEFT JOIN {shop_price} ON {shop_price_image_rel}.price_id={shop_price}.id AND {shop_price_image_rel}.trash='0' AND {shop_price}.trash='0' WHERE {shop_price}.id IS NULL");
						}
						break;

					case 4:
						$rows = array(true);

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} LEFT JOIN {shop_param_select} ON {shop_price_param}.param_value={shop_param_select}.id AND {shop_price_param}.trash='0' AND {shop_param_select}.trash='0' WHERE {shop_price_param}.param_value<>0 AND ({shop_param_select}.id IS NULL OR {shop_price_param}.param_id<>{shop_param_select}.param_id)");
							$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
							$service['shop_price_param'] += $query_result;
						}
						else
						{
							// удаляем параметры цен, указывающие на не существующие списки характеристик
							DB::query("DELETE {shop_price_param} FROM {shop_price_param} LEFT JOIN {shop_param_select} ON {shop_price_param}.param_value={shop_param_select}.id AND {shop_price_param}.trash='0' AND {shop_param_select}.trash='0' WHERE {shop_price_param}.param_value<>0 AND ({shop_param_select}.id IS NULL OR {shop_price_param}.param_id<>{shop_param_select}.param_id)");
						}
						break;

					case 5:
						$rows = array(true);

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} LEFT JOIN {shop_param} ON {shop_price_param}.param_id={shop_param}.id AND {shop_price_param}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
							$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
							$service['shop_price_param'] += $query_result;
						}
						else
						{
							// удаляем параметры цен, указывающие на не существующие характеристики
							DB::query("DELETE {shop_price_param} FROM {shop_price_param} LEFT JOIN {shop_param} ON {shop_price_param}.param_id={shop_param}.id AND {shop_price_param}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
						}
						break;

					case 6:
						$rows = array(true);

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} LEFT JOIN {shop_param} ON {shop_price_param}.param_id={shop_param}.id AND {shop_price_param}.trash='0' AND {shop_param}.trash='0' LEFT JOIN {shop_price} ON {shop_price_param}.price_id={shop_price}.id AND {shop_price}.trash='0' LEFT JOIN {shop} ON {shop_price}.good_id={shop}.id AND {shop}.trash='0' WHERE {shop_param}.id IS NULL OR {shop_price}.id IS NULL OR {shop}.id IS NULL OR ({shop_param}.site_id<>0 AND {shop_param}.site_id<>{shop}.site_id)");
							$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
							$service['shop_price_param'] += $query_result;
						}
						else
						{
							// удаляем параметры цен, указывающие на списки характеристик, не принадлежащие странице, к которой прикреплен модуль
							DB::query("DELETE {shop_price_param} FROM {shop_price_param} LEFT JOIN {shop_param} ON {shop_price_param}.param_id={shop_param}.id AND {shop_price_param}.trash='0' AND {shop_param}.trash='0' LEFT JOIN {shop_price} ON {shop_price_param}.price_id={shop_price}.id AND {shop_price}.trash='0' LEFT JOIN {shop} ON {shop_price}.good_id={shop}.id AND {shop}.trash='0' WHERE {shop_param}.id IS NULL OR {shop_price}.id IS NULL OR {shop}.id IS NULL OR ({shop_param}.site_id<>0 AND {shop_param}.site_id<>{shop}.site_id)");
						}
						break;

					case 7:
						$rows = array(true);

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} LEFT JOIN {shop_param} ON {shop_price_param}.param_id={shop_param}.id AND {shop_param}.required='1' AND {shop_price_param}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
							$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
							$service['shop_price_param'] += $query_result;
						}
						else
						{
							// удаляем параметры цен, указывающие на характеристики, не влияющие на цену
							DB::query("DELETE {shop_price_param} FROM {shop_price_param} LEFT JOIN {shop_param} ON {shop_price_param}.param_id={shop_param}.id AND {shop_param}.required='1' AND {shop_price_param}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
						}
						break;

					case 8:
						$rows = array();

						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} LEFT JOIN {shop_price} ON {shop_price_param}.price_id={shop_price}.id AND {shop_price_param}.trash='0' AND {shop_price}.trash='0' WHERE {shop_price}.id IS NULL");
							$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
							$service['shop_price_param'] += $query_result;
						}
						else
						{
							// удаляем параметры цен, указывающие на не существующие цены
							DB::query("DELETE {shop_price_param} FROM {shop_price_param} LEFT JOIN {shop_price} ON {shop_price_param}.price_id={shop_price}.id AND {shop_price_param}.trash='0' AND {shop_price}.trash='0' WHERE {shop_price}.id IS NULL");
						}
						break;

					default:
						$rows = array();
						break;
				}
				break;

			case 7:
				sleep($sleep);
				$messages = $this->diafan->_('Проверка цен в валюте ...');
				$count = 0;
				$rows = array();
				break;

			case 8:
				$messages = $this->diafan->_('Проверка цен в валюте ...').' %s%%';
				$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0'");
				$rows = DB::query_fetch_all("SELECT id, site_id FROM {shop} WHERE trash='0' LIMIT %d, %d", $max * $iteration, $max);
				foreach ($rows as $row)
				{
					if(! $mode_optimize)
					{
						// выявляем дубликаты цен в валюте
						$count = DB::query_result("SELECT COUNT(*) AS `count` FROM {shop_price} WHERE good_id=%d AND currency_id<>0 AND trash='0' GROUP BY price_id HAVING `count` > 1", $row["id"]);

						if($count)
						{
							$service['shop_price'] = ! empty($service['shop_price']) ? $service['shop_price'] : 0;
							$service['shop_price'] += --$count;
						}
					}
					else
					{
						// удаляем дубликаты цен в валюте, кроме первой цены
						$ids = DB::query_fetch_value("SELECT o.id FROM {shop_price} AS o INNER JOIN (SELECT *, COUNT(*) AS `count` FROM {shop_price} WHERE good_id=%d AND currency_id<>0 GROUP BY price_id HAVING `count`>1) AS oc ON o.price_id=oc.price_id AND o.id<>oc.id", $row["id"], "id");
						if(! empty($ids))
						{
							foreach($ids as $id)
							{
								DB::query("DELETE FROM {shop_price} WHERE id=%d", $id);
							}
						}
					}
				}
				break;

			case 9:
				sleep($sleep);
				$messages = $this->diafan->_('Проверка цен ...');
				$count = 0;
				$rows = array();
				break;

			case 10:
				$messages = $this->diafan->_('Проверка цен ...').' %s%%';
				$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0'");
				$rows = DB::query_fetch_all("SELECT id, site_id FROM {shop} WHERE trash='0' LIMIT %d, %d", $max * $iteration, $max);
				foreach ($rows as $row)
				{
					if(! $pr = DB::query_fetch_all("SELECT * FROM {shop_price} WHERE good_id=%d AND id=price_id AND trash='0' ORDER BY id ASC", $row["id"]))
					continue;
					$p_ids = array();
					foreach($pr as $p)
					{
						$p_ids[] = $p["price_id"];
					}
					$pr_param = DB::query_fetch_key_array("SELECT * FROM {shop_price_param} WHERE price_id IN (%s) AND trash='0' ORDER BY param_id ASC", implode(',', $p_ids), "price_id");

					$cats = DB::query_fetch_value("SELECT cat_id FROM {shop_category_rel} WHERE element_id=%d", $row["id"], "cat_id");
					array_push($cats, 0);
					$params = DB::query_fetch_key_value("SELECT p.id FROM {shop_param} AS p LEFT JOIN {shop_param_category_rel} AS r ON p.id=r.element_id AND p.trash='0' AND r.trash='0' WHERE p.required='1' AND (p.site_id=0 OR p.site_id=%d) AND r.cat_id IN (%s) ORDER BY p.id ASC", $row["site_id"], implode(',', $cats), "id", "id");

					$pr_array = array(); $is_selected = false;
					foreach($pr as $p)
					{
						// проверяем корректность параметров цен
						if(isset($pr_param[$p["id"]]))
						{
							foreach($pr_param[$p["id"]] as $k => $pa)
							{
								if(! in_array($pa["param_id"], $params))
								{
									if(! $mode_optimize)
									{
										$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
										$service['shop_price_param'] += 1;
									}
									else
									{
										DB::query("DELETE FROM {shop_price_param} WHERE id=%d", $pa["id"]);
									}
									unset($pr_param[$p["id"]][$k]);
								}
								else
								{
									$pr_array[$p["id"]][(! empty($pa["param_value"]) ? "selected" : "unselected")][] = $k;
									if(! $is_selected && ! empty($pa["param_value"]))
									{
										// присутствует цена с выбранными параметрами
										$is_selected = true;
									}
								}
							}
						}

						// восстанавливаем параметры цен
						foreach($params as $param_id)
						{
							$isset = false;
							if(isset($pr_param[$p["id"]]))
							{
								foreach($pr_param[$p["id"]] as $k => $pa)
								{
									if($pa["param_id"] != $param_id) continue;
									$isset = true;
									break;
								}
							}
							if($isset) continue;

							if(! $mode_optimize)
							{
								$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
								$service['shop_price_param'] += 1;
							}
							else
							{
								$shop_price_param_id = DB::query("INSERT INTO {shop_price_param} (price_id, param_id, param_value) VALUES (%d, %d, %d)", $p["id"], $param_id, 0);

								$pr_param[$p["id"]][] = array(
									"id" => $shop_price_param_id,
									"price_id" => $p["id"],
									"param_id" => $param_id,
									"param_value" => 0,
								);
								end($pr_param[$p["id"]]); $k = key($pr_param[$p["id"]]);
								$pr_array[$p["id"]]["unselected"][] = $k;
							}
						}
						// проверка на наличие дубликатов пройдет на следующем этапе: Оптимизация параметров цен
					}

					// допускаем цену с невыбранными параметрами, если нет иных цен и такая цена одна
					// если текущая цена является производной от валютной цены, то оставляем ее, удаляя первичную
					// поэтому сначала сортируем массив
					$pr_array_unshift = $pr_array_push = array();
					foreach($pr_array as $p_id => $value)
					{
						$value["id"] = $p_id;
						if(DB::query_result("SELECT id FROM {shop_price} WHERE price_id=%d AND currency_id<>0 AND trash='0' LIMIT 1", $p_id))
						{
							$pr_array_unshift[] = $value;
						}
						else $pr_array_push[] = $value;
					}
					$pr_array = array_merge($pr_array_unshift, $pr_array_push);
					$pr_array_first = true;
					foreach($pr_array as $value)
					{
						$p_id = $value["id"];
						if(isset($value["selected"]) || ! isset($value["unselected"]))
							continue;
						if($is_selected || ! $pr_array_first)
						{
							// $is_selected - так как есть цены с выбранными параметрами, то удаляем цены с не выбранными параметрами
							// ! $pr_array_first - так как нет цен с выбранными параметрами, то оставляем только одну такую цену
							foreach($value["unselected"] as $k)
							{
								if(! isset($pr_param[$p_id]) || ! isset($pr_param[$p_id][$k]) || ! isset($pr_param[$p_id][$k]["id"]))
									continue;

								if(! $mode_optimize)
								{
									$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
									$service['shop_price_param'] += 1;
								}
								else
								{
									DB::query("DELETE FROM {shop_price_param} WHERE id=%d", $pr_param[$p_id][$k]["id"]);
								}
								unset($pr_param[$p_id][$k]);
							}
							if(! $mode_optimize)
							{
								$service['shop_price'] = ! empty($service['shop_price']) ? $service['shop_price'] : 0;
								$service['shop_price'] += 1;
							}
							else
							{
								DB::query("DELETE FROM {shop_price} WHERE id=%d", $p_id);
							}
						}
						$pr_array_first = false;
					}
				}
				break;

			case 11:
				sleep($sleep);
				$messages = $this->diafan->_('Проверка оптимизации параметров цен ...');
				$count = 0;
				$rows = array();
				break;

			case 12:
				$messages = $this->diafan->_('Проверка оптимизации параметров цен ...').' %s%%';
				$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0'");
				$rows = DB::query_fetch_all("SELECT id FROM {shop} WHERE trash='0' LIMIT %d, %d", $max * $iteration, $max);
				// проверка на наличие дубликатов
				foreach ($rows as $row)
				{
					if(! $pr = DB::query_fetch_all("SELECT * FROM {shop_price} WHERE good_id=%d AND id=price_id AND trash='0' ORDER BY id ASC", $row["id"]))
					continue;
					$uniq = array();
					$p_ids = array();
					foreach($pr as $p)
					{
						$p_ids[] = $p["price_id"];
					}
					$pr_param = DB::query_fetch_key_array("SELECT * FROM {shop_price_param} WHERE price_id IN (%s) AND trash='0' ORDER BY param_id ASC", implode(',', $p_ids), "price_id");
					foreach($pr as $p)
					{
						$u = '';
						if(isset($pr_param[$p["id"]]))
						{
							foreach($pr_param[$p["id"]] as $pa)
							{
								$u .= "_".$pa["param_id"]."_".$pa["param_value"]."_";
							}
						}
						// если есть цена с такими же параметрами (запись более ранняя), удаляем ее
						if(isset($uniq[$u]))
						{
							if(! $mode_optimize)
							{
								$service['shop_price'] = ! empty($service['shop_price']) ? $service['shop_price'] : 0;
								$service['shop_price'] += 1;
								$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} WHERE price_id=%d", $uniq[$u]);
								$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
								$service['shop_price_param'] += $query_result;
								$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_image_rel} WHERE price_id=%d", $uniq[$u]);
								$service['shop_price_image_rel'] = ! empty($service['shop_price_image_rel']) ? $service['shop_price_image_rel'] : 0;
								$service['shop_price_image_rel'] += $query_result;
							}
							else
							{
								// если более ранняя цена является производной от цены в валюте, то оставляем ее и удаляем найденную
								if(DB::query_result("SELECT id FROM {shop_price} WHERE price_id=%d AND currency_id<>0 AND trash='0' LIMIT 1", $p["id"]))
								{
									$p_id = $p["id"];
								}
								else $p_id = $uniq[$u];
								DB::query("DELETE FROM {shop_price} WHERE price_id=%d", $p_id);
								DB::query("DELETE FROM {shop_price_param} WHERE price_id=%d", $p_id);
								DB::query("DELETE FROM {shop_price_image_rel} WHERE price_id=%d", $p_id);
							}
						}
						$uniq[$u] = $p["id"];
					}
				}
				break;

			case 13:
				sleep($sleep);
				$messages = $this->diafan->_('Пересчет цен ...');
				$count = 0;
				$rows = array();
				break;

			case 14:
				$messages = $this->diafan->_('Пересчет цен ...').' %s%%';
				$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0'");
				$rows = DB::query_fetch_all("SELECT id FROM {shop} WHERE trash='0' LIMIT %d, %d", $max * $iteration, $max);
				foreach ($rows as $row)
				{
					$this->diafan->_shop->price_calc($row["id"]);
				}
				break;

			case 15:
				sleep($sleep);
				$messages = $this->diafan->_('Переиндексация таблицы цен ...');
				$count = 0;
				$rows = array();
				break;

			case 16:
				$messages = $this->diafan->_('Переиндексация таблицы цен ...').' %s%%';
				$count = $max * 8;

				$url = parse_url(DB_URL);
				$dbname = substr($url['path'], 1);
				$table = 'shop_price';
				$field = 'id_id';
				$is_field = DB::query_fetch_value("SHOW COLUMNS FROM {".$table."} FROM `".$dbname."` WHERE Field='%s'", $field, 'Field');

				switch($iteration)
				{
					case 0:
						$rows = array(true);
						if(! $is_field)
						{
							DB::query("ALTER TABLE {shop_price} DROP PRIMARY KEY, MODIFY `id` INT(11) UNSIGNED NOT NULL DEFAULT '0', AUTO_INCREMENT=1, ADD `".$field."` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT AFTER `id`, ADD PRIMARY KEY(`".$field."`);");
						}
						break;

					case 1:
						$rows = array(true);
						if($is_field)
						{
							// TO_DO: если присутствуют не совпадения, то они будут переписаны значением NULL.
							// Если требуется избежать этого и оставить не совпадения как есть, необходимо заменить LEFT JOIN на просто JOIN.
							DB::query("UPDATE {shop_price} AS b LEFT JOIN {shop_price} AS a ON b.`price_id`=a.`id` SET b.`price_id`=a.`".$field."`;");
						}
						break;

					case 2:
						$rows = array(true);
						if($is_field)
						{
							// TO_DO: если присутствуют не совпадения, то они будут переписаны значением NULL.
							// Если требуется избежать этого и оставить не совпадения как есть, необходимо заменить LEFT JOIN на просто JOIN.
							DB::query("UPDATE {shop_price_param} LEFT JOIN {shop_price} ON {shop_price_param}.`price_id`={shop_price}.`id` SET {shop_price_param}.`price_id`={shop_price}.`".$field."`;");
						}
						break;

					case 3:
						$rows = array(true);
						if($is_field)
						{
							// TO_DO: если присутствуют не совпадения, то они будут переписаны значением NULL.
							// Если требуется избежать этого и оставить не совпадения как есть, необходимо заменить LEFT JOIN на просто JOIN.
							DB::query("UPDATE {shop_price_image_rel} LEFT JOIN {shop_price} ON {shop_price_image_rel}.`price_id`={shop_price}.`id` SET {shop_price_image_rel}.`price_id`={shop_price}.`".$field."`;");
						}
						break;

					case 4:
						$rows = array(true);
						if($is_field)
						{
							DB::query("ALTER TABLE {shop_price} DROP `id`;");
						}
						break;

					case 5:
						$rows = array(true);
						if($is_field)
						{
							DB::query("ALTER TABLE {shop_price} CHANGE `".$field."` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;");
						}
						break;

					case 6:
						$rows = array();
						if($is_field)
						{
							DB::query("ALTER TABLE {shop_price} DROP PRIMARY KEY, ADD PRIMARY KEY(`id`);");
						}
						break;

					case 7:
						$rows = array();
						if($is_field)
						{
							// TO_DO: InnoDB
							// Table does not support optimize, doing recreate + analyze instead:
							// CREATE TABLE <NEW.NAME.TABLE> LIKE <TABLE.CRASHED>;
							// INSERT INTO <NEW.NAME.TABLE> SELECT * FROM <TABLE.CRASHED>;
							// RENAME TABLE <TABLE.CRASHED> TO <TABLE.CRASHED.BACKUP>;
							// RENAME TABLE <NEW.NAME.TABLE> TO <TABLE.CRASHED>;
							// DROP TABLE <TABLE.CRASHED.BACKUP>;
							DB::query("OPTIMIZE TABLE {shop_price};");
							DB::query("OPTIMIZE TABLE {shop_price_param};");
							DB::query("OPTIMIZE TABLE {shop_price_image_rel};");
							DB::query("OPTIMIZE TABLE {shop_param};");
							DB::query("OPTIMIZE TABLE {shop_param_select};");
							DB::query("OPTIMIZE TABLE {shop_param_element};");
							DB::query("OPTIMIZE TABLE {shop_param_category_rel};");
						}
						break;

					default:
						$rows = array();
						break;
				}
				break;

			case 17:
				sleep($sleep);
				$messages = $this->diafan->_('Процесс проверки завершен ...');
				$count = 0;
				$rows = array();
				break;

			case 18:
				sleep($sleep);
				$messages = $this->diafan->_('Процесс проверки завершен ...').' %s%%';
				$count = 0;
				$rows = array();
				// удаляем кэш модуля Интернет-магазин
				$this->diafan->_cache->delete("", "shop");
				break;

			default:
				sleep($sleep);
				$messages = '';
				$count = 0;
				$rows = array();
				break;
		}

		$c = (($max * $iteration) + $max);
		$c = $c < $count ? $c : $count;
		$c = $count > 0 ? ceil($c * 100 / $count) : 100;
		$messages = sprintf($messages, $c);

		$this->result["messages"] = '<div class="commentary">'.$messages.'</div>';
		if(count($rows))
		{
			$this->result["error"] = 'next';
		}
		elseif($part <= 18)
		{
			$this->result["error"] = 'next_part';
		}
		else
		{
			if(! $mode_optimize)
			{
				$messages = $this->diafan->_('Цены товаров проверены.');
				$this->result["error"] = $messages;
				$messages = array();
				if(! empty($service))
				{
					foreach($service as $key => $value)
					{
						if(empty($value)) continue;
						$error = true;
						$messages[] = $this->diafan->_("в таблице %s - %s", '{'.$key.'}', $value);
					}
				}

				if(! empty($messages))
				{
					$messages = "<b>".$this->diafan->_('Выявлены ошибки:')."</b>"."<br />".implode("<br />", $messages);
					$this->result["messages"] = '<div class="error">'.$messages.'</div>';
				}
				else
				{
					$messages = $this->diafan->_('Ошибок не выявлено.');
					$this->result["messages"] = '<div class="ok">'.$messages.'</div>';
				}
			}
			else
			{
				$messages = $this->diafan->_('Цены товаров успешно оптимизированы.');
				$this->result["error"] = $messages;
				$this->result["messages"] = '<div class="ok">'.$messages.'</div>';
			}
		}

		$this->result["service"] = $this->diafan->array_to_str($service, ';', ':');
	}

	/**
	 * Обновление значений характеристики в таблице
	 *
	 * @return void
	 */
	private function table_params_refresh()
	{
		$id = $this->diafan->filter($_POST, 'int', 'id', 0);

		Custom::inc("modules/service/admin/service.admin.express.fields.element.php");
		inc_file_express_modules( $this->diafan, 'shop' );
		$object = new service_admin_express_fields_element($this->diafan);
		$object->prepare_config();
		$table = 'service_express_fields';

		$trash = ! empty($object->variables_list["actions"]["trash"]);
		$element = DB::query_fetch_array(
			"SELECT * FROM {".$table."} WHERE id=%d"
			.($trash ? " AND trash='0'" : '' )." LIMIT 1",
			$id
		);
		$cat_id = ! empty($element["cat_id"]) ? $element["cat_id"] : 0;
		$site_id = DB::query_result("SELECT site_id FROM {%s} WHERE id=%d AND trash='0' LIMIT 1", 'service_express_fields_category', $cat_id);

		$key = 'params';
		$is_new = empty($element);
		$value = isset($element[$key])
			? $element[$key]
			: '';
		$k = 'type';
		$type_value = isset($element[$k])
			? $element[$k]
			: '';

		$type = '';
		if(! $is_new)
		{
			$params = unserialize($value);
			$type = $type_value;
		}

		unset($object);
		$this->result["result"] = '';

		// дополнительная характеристика
		$param_select_type = $type == 'param' && ! empty($params["select_type"]) ? $params["select_type"] : '';
		$this->result["result"] .= '
		<div unit_id="param_id" class="unit params param_param box_refresh" field_id="'.$id.'">
			<div class="infofield">'.$this->diafan->_('Укажите характеристику')

			.' ('.$this->diafan->_('или').' <a href="'.BASE_PATH_HREF.'shop/param/'.(! empty($site_id) ? 'site'.$site_id.'/' : '').'" title="'.$this->diafan->_('Добавить характеристику. После необходимо обновить характеристики в текущей таблице. Для этого следует нажать кнопку «Обновить».').'" target="_blank">'.$this->diafan->_('добавьте новую').'</a>)'
			.'</div>';
		$rows = DB::query_fetch_all("SELECT id, [name], type FROM {shop_param} WHERE trash='0' ORDER BY sort ASC, id ASC");
		$this->result["result"] .= '<select name="param_id[]">';
		$this->result["result"] .= '<option value="0">-</option>';
		foreach ($rows as $row)
		{
			$this->result["result"] .= '<option value="'.$row["id"].'"'
			.($type == 'param' && ! empty($params["id"]) && $params["id"] == $row["id"] ? ' selected="selected" ' : '' )
			.' type="'.$row["type"].'">'.$row["name"].'</option>';
		}
		$this->result["result"] .= '</select>';
		$this->result["result"] .= ' <i class="tooltip fa fa-refresh" title="'.$this->diafan->_("Обновить список, если характеристики были добавлены позже открытия этой страницы.").'"></i>
		</div>';
	}
}
