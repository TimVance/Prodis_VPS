<?php
/**
 * Модель модуля «Магазин»
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
 * Shop_model
 */
class Shop_model extends Model
{
	/**
	 * @var array значения параметров, влияющих на цену
	 */
	private $depends;

	/**
	 * @var array идентификаторы персональных скидок текущего пользователя
	 */
	private $person_discount_ids = false;

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		$this->diafan = &$diafan;

		$this->person_discount_ids = $this->diafan->_shop->price_get_person_discounts();

		$this->sort_config = $this->expand_sort_with_params();

		if ($this->diafan->_route->sort > count($this->sort_config['sort_directions']))
		{
			Custom::inc('includes/404.php');
		}
	}

	/**
	 * Генерирует данные для списка товаров, найденных при помощи поиска
	 *
	 * @return void
	 */
	public function list_search()
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		$where_param = '';
		$where = '';
		$values = array();
		$getnav = '';
		$group = '';

		$this->where($where, $where_param, $values, $getnav, $group);

		if($this->diafan->configmodules('where_period_element'))
		{
			$where .= " AND s.date_start<=".$time." AND (s.date_finish=0 OR s.date_finish>=".$time.")";
		}

		////navigation//
		$this->diafan->_paginator->get_nav = $getnav;
		$this->diafan->_paginator->nen = $this->list_search_query_count($where_param, $where, $group, $values);
		$this->result["paginator"] = $this->diafan->_paginator->get();
		////navigation///

		if($this->diafan->_route->cat)
		{
			$cat = DB::query_fetch_array("SELECT [name], view_rows, view, theme FROM {shop_category} WHERE id=%d AND [act]='1' AND trash='0'", $this->diafan->_route->cat);
			if($cat)
			{
				$this->result["name"] = $cat["name"];
				$this->result["theme"] = $cat["theme"];
				$this->result["view"] = $cat["view"];
				$this->result["view_rows"] = $cat["view_rows"];
			}
		}
		if($this->diafan->configmodules("theme_list_search"))
		{
			$this->result["theme"] = $this->diafan->configmodules("theme_list_search");
		}
		if($this->diafan->configmodules("view_list_search"))
		{
			$this->result["view"] = $this->diafan->configmodules("view_list_search");
		}
		elseif(empty($this->result["view"]))
		{
			$this->result["view"] = 'list';
		}
		if($this->diafan->configmodules("view_list_search_rows"))
		{
			$this->result["view_rows"] = $this->diafan->configmodules("view_list_search_rows");
		}
		elseif(empty($this->result["view_rows"]))
		{
			$this->result["view_rows"] = 'rows';
		}
		$this->result["breadcrumb"] = $this->get_breadcrumb();
		if(isset($this->result["name"]))
		{
			$this->result["titlemodule"] = $this->result["name"];
		}
		else
		{
			$this->result["titlemodule"] = $this->diafan->_('Поиск по товарам', false);
		}
		if ( ! $this->diafan->_paginator->nen)
		{
			$this->result["error"] = $this->diafan->_('Извините, ничего не найдено.', false);
			return $this->result;
		}

		$this->result["rows"] = $this->list_search_query($where_param, $where, $group, $values);
		$this->elements($this->result["rows"]);

		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);

		foreach ($this->result["rows"] as &$row)
		{
			$this->prepare_data_element($row);
		}
		foreach ($this->result["rows"] as &$row)
		{
			$this->format_data_element($row);
		}

		$this->result["link_sort"] = $this->get_sort_links($this->sort_config['sort_directions']);

		$this->result['sort_config'] = $this->sort_config;

		$this->result['shop_link'] = $this->diafan->_route->module($this->diafan->_site->module);
	}

	/**
	 * Получает из базы данных общее количество найденных при помощи поиска элементов
	 *
	 * @param string $where_param
	 * @param string $where
	 * @param string $group
	 * @param array $values
	 * @return integer
	 */
	private function list_search_query_count($where_param, $where, $group, $values)
	{
		$result = DB::query("SELECT ".($group ? "DISTINCT s.id" : "COUNT(DISTINCT s.id)")." FROM {shop} AS s"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='shop' AND a.element_type='element'" : "")
		.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=s.id AND prh.count_goods>0" : "")
		.$where_param
		.($this->sort_config['use_params_for_sort'] ? " LEFT JOIN {shop_param_element} AS sp  ON sp.element_id=s.id AND sp.trash='0' AND sp.param_id=".$this->sort_config['param_ids'][$this->diafan->_route->sort] : '')
		." WHERE s.[act]='1' AND s.trash='0'"
		.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		.($this->diafan->configmodules('hide_missing_goods') ? " AND s.no_buy='0'" : "")
		.$where
		.($group ? " GROUP BY s.id".$group : ''),
		$values);
		if($group)
		{
			$count = DB::num_rows($result);
			DB::free_result($result);
		}
		else
		{
			$count = DB::result($result);
		}
		return $count;
	}

	/**
	 * Получает из базы данных найденных при помощи поиска элементы на одной странице
	 *
	 * @param string $where_param
	 * @param string $where
	 * @param string $group
	 * @param array $values
	 * @return array
	 */
	private function list_search_query($where_param, $where, $group, $values)
	{
		switch($this->diafan->configmodules("sort"))
		{
			case 1:
				$order = 's.id DESC';
				break;
			case 2:
				$order = 's.id ASC';
				break;
			case 3:
				$order = 's.name'._LANG.' ASC';
				break;
			default:
				$order = 's.sort DESC, s.id DESC';
		}
		if(! $this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods'))
		{
			$order = "isset_count_goods DESC,".$order;
		}
		$rows = DB::query_range_fetch_all("SELECT DISTINCT s.id, s.[name], s.timeedit, s.[anons], s.site_id, s.brand_id, s.no_buy, s.article,"
		."s.hit, s.new, s.action, s.is_file, s.[measure_unit]"
		.(! $this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? ", (prhc.good_id<>0) AS isset_count_goods" : "")
		." FROM {shop} AS s"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='shop' AND a.element_type='element'" : "")
		.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=s.id AND prh.count_goods>0" : "")
		.(! $this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " LEFT JOIN {shop_price} AS prhc ON prhc.good_id=s.id AND prhc.count_goods>0" : "")
		.$where_param
		.($this->sort_config['use_params_for_sort'] ? " LEFT JOIN {shop_param_element} AS sp  ON sp.element_id=s.id AND sp.trash='0' AND sp.param_id=".$this->sort_config['param_ids'][$this->diafan->_route->sort] : '')
		." WHERE s.[act]='1' AND s.trash='0'"
		.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		.($this->diafan->configmodules('hide_missing_goods') ? " AND s.no_buy='0'" : "")
		.$where
		." GROUP BY s.id".$group
		." ORDER BY ".($this->diafan->_route->sort ? $this->sort_config['sort_directions'][$this->diafan->_route->sort].',' : '')
		." s.no_buy ASC, ".$order, $values, $this->diafan->_paginator->polog, $this->diafan->_paginator->nastr);
		return $rows;
	}

	/**
	 * Генерирует данные для списка товаров, соответствующих значению доп. характеристики
	 *
	 * @return void
	 */
	public function list_param()
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		$cache_meta = array(
			"name" => "list_param",
			"lang_id" => _LANG,
			"page" => $this->diafan->_route->page > 1 ? $this->diafan->_route->page : 1,
			"site_id" => $this->diafan->_site->id,
			"sort" => $this->diafan->_route->sort,
			"param" => $this->diafan->_route->param,
			"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
			"discounts" => $this->person_discount_ids,
			"time" => $time
		);
		//кеширование
		if ( ! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{
			if ( ! $param_value = DB::query_fetch_array("SELECT param_id, [name] FROM {shop_param_select} WHERE id=%d AND trash='0' LIMIT 1", $this->diafan->_route->param))
			{
				Custom::inc('includes/404.php');
			}
			if ( ! $param = DB::query_fetch_array("SELECT [name] FROM {shop_param} WHERE id=%d AND trash='0' AND page='1' LIMIT 1", $param_value["param_id"]))
			{
				Custom::inc('includes/404.php');
			}
			////navigation//
			$this->diafan->_paginator->nen = $this->list_param_query_count($time, $param_value["param_id"]);
			$this->result["paginator"] = $this->diafan->_paginator->get();
			////navigation///

			$this->result["breadcrumb"] = $this->get_breadcrumb();
			$this->result["titlemodule"] = $param["name"].': '.$param_value["name"];

			$this->result["rows"] = $this->list_param_query($time, $param_value["param_id"]);

			$this->elements($this->result["rows"]);

			if(! empty($this->result["depends_param"]))
			{
				foreach ($this->result["depends_param"] as &$param)
				{
					foreach ($param["values"] as &$value)
					{
						if($this->diafan->_route->param == $value["id"])
						{
							$value["selected"] =  true;
							break 2;
						}
					}
				}
			}

			if($this->diafan->_route->page > 1)
			{
				$this->result["canonical"] = $this->diafan->_route->current_link("page");
			}

			//сохранение кеша
			$this->diafan->_cache->save($this->result, $cache_meta, $this->diafan->_site->module);
		}
		foreach ($this->result["rows"] as &$row)
		{
			$this->prepare_data_element($row);
		}
		foreach ($this->result["rows"] as &$row)
		{
			$this->format_data_element($row);
		}

		if($this->diafan->configmodules("theme_list_param"))
		{
			$this->result["theme"] = $this->diafan->configmodules("theme_list_param");
		}
		if($this->diafan->configmodules("view_list_param"))
		{
			$this->result["view"] = $this->diafan->configmodules("view_list_param");
		}
		else
		{
			$this->result["view"] = 'list';
		}
		if($this->diafan->configmodules("view_list_param_rows"))
		{
			$this->result["view_rows"] = $this->diafan->configmodules("view_list_param_rows");
		}
		else
		{
			$this->result["view_rows"] = 'rows';
		}

		$this->result["link_sort"] = $this->get_sort_links($this->sort_config['sort_directions']);

		$this->result['sort_config'] = $this->sort_config;

		$this->result['shop_link'] = $this->diafan->_route->module($this->diafan->_site->module);

		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);
	}

	/**
	 * Получает из базы данных общее количество элементов, соответствующих значению доп. характеристики
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @param integer $param_id дополнительная характеристика
	 * @return integer
	 */
	private function list_param_query_count($time, $param_id)
	{
		$count = DB::query_result(
			"SELECT COUNT(DISTINCT s.id) FROM {shop} AS s "
			." INNER JOIN {shop_param_element} AS e ON e.element_id=s.id"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='shop' AND a.element_type='element'" : "")
			.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=s.id AND prh.count_goods>0" : "")
			." WHERE s.[act]='1' AND s.trash='0' AND e.param_id=%d AND e.value".$this->diafan->_languages->site."=%d"
			.($this->diafan->configmodules('where_period_element') ? " AND s.date_start<=".$time." AND (s.date_finish=0 OR s.date_finish>=".$time.")" : '')
			.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			.($this->diafan->configmodules('hide_missing_goods') ? " AND s.no_buy='0'" : ""),
			$param_id, $this->diafan->_route->param
			);
		return $count;
	}

	/**
	 * Получает из базы данных элементы на одной странице, соответствующие значению доп. характеристики
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @param integer $param_id дополнительная характеристика
	 * @return array
	 */
	private function list_param_query($time, $param_id)
	{
		switch($this->diafan->configmodules("sort"))
		{
			case 1:
				$order = 's.id DESC';
				break;
			case 2:
				$order = 's.id ASC';
				break;
			case 3:
				$order = 's.name'._LANG.' ASC';
				break;
			default:
				$order = 's.sort DESC, s.id DESC';
		}
		if(! $this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods'))
		{
			$order = "isset_count_goods DESC,".$order;
		}
		$rows = DB::query_range_fetch_all(
			"SELECT s.id, s.[name], s.timeedit, s.[anons], s.site_id, s.brand_id, s.no_buy,  s.article, s.[measure_unit], s.is_file"
			.(! $this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? ", (prhc.good_id<>0) AS isset_count_goods" : "")
			." FROM {shop} AS s"
			.($this->diafan->_route->sort == 1 || $this->diafan->_route->sort == 2 ?
				" LEFT JOIN {shop_price} AS pr ON pr.good_id=s.id AND pr.trash='0'"
				." AND pr.date_start<=".time()." AND (pr.date_finish=0 OR pr.date_finish>=".time().")"
				." AND pr.currency_id=0"
				." AND pr.role_id".($this->diafan->_users->role_id ? " IN (0,".$this->diafan->_users->role_id.")" : "=0")
				." AND (pr.person='0'".($this->person_discount_ids ? " OR pr.discount_id IN(".implode(",", $this->person_discount_ids).")" : "").")"
				: '')
			." INNER JOIN {shop_param_element} as e ON e.element_id=s.id"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='shop' AND a.element_type='element'" : "")
			.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=s.id AND prh.count_goods>0" : "")
			.(! $this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " LEFT JOIN {shop_price} AS prhc ON prhc.good_id=s.id AND prhc.count_goods>0" : "")
			. ($this->sort_config['use_params_for_sort'] ? " LEFT JOIN {shop_param_element} AS sp  ON sp.element_id=s.id AND sp.trash='0'"
			." AND sp.param_id=".$this->sort_config['param_ids'][$this->diafan->_route->sort] : '')
			." WHERE s.[act]='1' AND s.trash='0' AND e.param_id=%d AND e.value".$this->diafan->_languages->site."=%d"
			.($this->diafan->configmodules('where_period_element') ? " AND s.date_start<=".$time." AND (s.date_finish=0 OR s.date_finish>=".$time.")" : '')
			.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			.($this->diafan->configmodules('hide_missing_goods') ? " AND s.no_buy='0'" : "")
			." GROUP BY s.id"
			." ORDER BY ".($this->diafan->_route->sort ? $this->sort_config['sort_directions'][$this->diafan->_route->sort].',' : '')
			." s.no_buy ASC, ".$order,
			$param_id, $this->diafan->_route->param,
			$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr);
		return $rows;
	}

	/**
	 * Генерирует данные для первой страницы магазина
	 *
	 * @return void
	 */
	public function first_page()
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name" => "first_page",
			"lang_id" => _LANG,
			"page" => $this->diafan->_route->page > 1 ? $this->diafan->_route->page : 1,
			"site_id" => $this->diafan->_site->id,
			"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
			"discounts" => $this->person_discount_ids,
			"time" => $time
		);
		if ( ! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{
			////navigation//
			$this->diafan->_paginator->nen = $this->first_page_cats_query_count();
			$this->diafan->_paginator->nastr = $this->diafan->configmodules("nastr_cat");
			$this->result["paginator"] = $this->diafan->_paginator->get();
			////navigation///

			$this->result["categories"] = $this->first_page_cats_query();
			foreach ($this->result["categories"] as &$row)
			{
				$this->diafan->_route->prepare($row["site_id"], $row["id"], "shop", "cat");
				if ($this->diafan->configmodules("images_cat") && $this->diafan->configmodules("list_img_cat"))
				{
					$this->diafan->_images->prepare($row["id"], 'shop', 'cat');
				}
			}
			foreach ($this->result["categories"] as &$row)
			{
				if (empty($this->result["timeedit"]) || $row["timeedit"] > $this->result["timeedit"])
				{
					$this->result["timeedit"] = $row["timeedit"];
				}

				$row["children"] = $this->get_children_category($row["id"], $time);

				$children = $this->diafan->get_children($row["id"], "shop_category");
				$children[] = $row["id"];

				if ($this->diafan->configmodules("children_elements"))
				{
					$cat_ids = $children;
				}
				else
				{
					$cat_ids = array($row["id"]);
				}

				$row["rows"] = array();
				if($this->diafan->configmodules("count_list"))
				{
					$row["rows"] = $this->first_page_elements_query($time, $cat_ids);
					$this->elements($row["rows"]);
				}
				$row["count"] = $this->get_count_in_cat($children, time());

				$row["link_all"] = $this->diafan->_route->link($row["site_id"], $row["id"], 'shop', 'cat');

				if ($this->diafan->configmodules("images_cat") && $this->diafan->configmodules("list_img_cat"))
				{
					$row["img"] =
					$this->diafan->_images->get(
						'medium', $row["id"], 'shop', 'cat',
						$row["site_id"], $row["name"], 0,
						$this->diafan->configmodules("list_img_cat") == 1 ? 1 : 0,
						$row["link_all"]
					);
				}
			}

			//сохранение кеша
			$this->diafan->_cache->save($this->result, $cache_meta, $this->diafan->_site->module);
		}
		foreach ($this->result["categories"] as &$row)
		{
			$this->prepare_data_category($row);
		}
		foreach ($this->result["categories"] as &$row)
		{
			$this->format_data_category($row);
		}
		$this->theme_view_first_page();

		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);
	}

	/**
	 * Получает из базы данных общее количество категории верхнего уровня для первой странице модуля, если категории используются
	 *
	 * @return integer
	 */
	private function first_page_cats_query_count()
	{
		$count = DB::query_result(
		"SELECT COUNT(DISTINCT c.id) FROM {shop_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='shop' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.parent_id=0 AND c.trash='0' AND c.site_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''), $this->diafan->_site->id
		);
		return $count;
	}

	/**
	 * Получает из базы данных категории верхнего уровня для первой странице модуля, если категории используются
	 *
	 * @return array
	 */
	private function first_page_cats_query()
	{
		$rows = DB::query_range_fetch_all(
		"SELECT c.id, c.[name], c.[anons], c.timeedit, c.site_id FROM {shop_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='shop' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.parent_id=0 AND c.trash='0' AND c.site_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY c.id ORDER by c.sort ASC, c.id ASC", $this->diafan->_site->id,
		$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
		);
		return $rows;
	}

	/**
	 * Получает из базы данных элементы для первой страницы модуля, если категории используются
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @param array $cat_ids номера категорий, элементы из которых выбираются
	 * @return array
	 */
	private function first_page_elements_query($time, $cat_ids)
	{
		switch($this->diafan->configmodules("sort"))
		{
			case 1:
				$order = 'e.id DESC';
				break;
			case 2:
				$order = 'e.id ASC';
				break;
			case 3:
				$order = 'e.name'._LANG.' ASC';
				break;
			default:
				$order = 'e.sort DESC, e.id DESC';
		}
		if(! $this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods'))
		{
			$order = "isset_count_goods DESC,".$order;
		}
		$rows = DB::query_range_fetch_all(
		"SELECT e.id, e.[name], e.timeedit, e.[anons], e.site_id, e.brand_id, e.no_buy, e.article, e.hit,"
		." e.[measure_unit], e.new, e.action, e.is_file"
		.(! $this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? ", (prhc.good_id<>0) AS isset_count_goods" : "")
		." FROM {shop} AS e"
		." INNER JOIN {shop_category_rel} AS r ON e.id=r.element_id"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='shop' AND a.element_type='element'" : "")
		.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=e.id AND prh.count_goods>0" : "")
		.(! $this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " LEFT JOIN {shop_price} AS prhc ON prhc.good_id=e.id AND prhc.count_goods>0" : "")
		." WHERE r.cat_id IN (%s) AND e.[act]='1' AND e.trash='0'"
		.($this->diafan->configmodules('where_period_element') ? " AND e.date_start<=".$time." AND (e.date_finish=0 OR e.date_finish>=".$time.")" : '')
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		.($this->diafan->configmodules('hide_missing_goods') ? " AND e.no_buy='0'" : "")
		." GROUP BY e.id ORDER BY e.no_buy ASC, ".$order,
		implode(',', $cat_ids),
		0, $this->diafan->configmodules("count_list")
		);
		return $rows;
	}

	/**
	 * Генерирует данные для списка товаров
	 *
	 * @return void
	 */
	public function list_()
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name" => "list",
			"cat_id" => $this->diafan->_route->cat,
			"brand_id" => $this->diafan->_route->brand,
			"lang_id" => _LANG,
			"page" => $this->diafan->_route->page > 1 ? $this->diafan->_route->page : 1,
			"site_id" => $this->diafan->_site->id,
			"sort" => $this->diafan->_route->sort,
			"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
			"discounts" => $this->person_discount_ids,
			"time" => $time
		);
		if ( ! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{

			$this->result["breadcrumb"] = $this->get_breadcrumb();

			$this->result["view"] = 'list';
			$this->result["view_rows"] = 'rows';

			$cat_ids = array();

			if($this->diafan->_route->cat)
			{
				if(! $this->diafan->configmodules("cat"))
				{
					Custom::inc('includes/404.php');
				}
				$row = $this->list_category_query();

				if ( ! $row)
				{
					Custom::inc('includes/404.php');
				}
				if (empty($row) || (! empty($row['access']) && ! $this->access($row['id'], 'shop', 'cat')))
				{
					Custom::inc('includes/403.php');
				}

				if ($this->diafan->configmodules("images_cat"))
				{
					$this->diafan->_images->prepare($row["id"], 'shop', 'cat');
				}

				$this->result["children"] = $this->get_children_category($row["id"], $time);

				if ($this->diafan->configmodules("images_cat"))
				{
					$this->result["img"] = $this->diafan->_images->get(
							'medium', $row["id"], 'shop', 'cat',
							$this->diafan->_site->id, $row["name"], 0, 0, 'large'
						);
				}

				if ($this->diafan->configmodules("children_elements"))
				{
					$cat_ids = $this->diafan->get_children($this->diafan->_route->cat, "shop_category");
					$cat_ids[] = $this->diafan->_route->cat;
				}
				else
				{
					$cat_ids = array($this->diafan->_route->cat);
				}
				if(! $this->diafan->_route->brand)
				{
					$this->meta_cat($row);
				}
				$this->theme_view_cat($row);

				if($row["anons_plus"])
				{
					$row["text"] = $row["anons"].$row["text"];
				}
				$this->result["id"] = $row["id"];
				$this->result["text"] = $row["text"];
				$this->result["anons"] = $row["anons"];
				$this->result["name"] = $row["name"];
				foreach ($this->diafan->_languages->all as &$l)
				{
					if(! empty($row["act".$l["id"]]))
					{
						$this->result["act".$l["id"]] = $row["act".$l["id"]];
					}
				}
				$this->result["act"] = $row["act"];

				if(! $this->diafan->_route->brand)
				{
					$this->list_category_previous_next($row["sort"], $row["parent_id"]);
				}
			}
			if($this->diafan->_route->brand)
			{
				$brand = $this->list_brand_query();

				if (! $brand)
				{
					Custom::inc('includes/404.php');
				}
				$this->meta_brand($brand);
				$this->result["id"] = $brand["id"];
				$this->result["name"] = $brand["name"];
				$this->result["text"] = $brand["text"];

				if($brand["theme"])
				{
					$this->result["theme"] = $brand["theme"];
				}
				elseif($this->diafan->configmodules("theme_list_brand"))
				{
					$this->result["theme"] = $this->diafan->configmodules("theme_list_brand");
				}

				if($brand["view"])
				{
					$this->result["view"] = $brand["view"];
				}
				elseif($this->diafan->configmodules("view_list_brand"))
				{
					$this->result["view"] = $this->diafan->configmodules("view_list_brand");
				}

				if ($this->diafan->configmodules("images_brand"))
				{
					$this->diafan->_images->prepare($brand["id"], 'shop', 'brand');
				}

				if ($this->diafan->configmodules("images_brand"))
				{
					$img = $this->diafan->_images->get(
							'medium', $brand["id"], 'shop', 'brand',
							$this->diafan->_site->id, $brand["name"], 0, 0, 'large'
						);
					if($img)
					{
						$this->result["img"] = $img;
					}
				}

				$this->list_brand_previous_next($brand["sort"]);
			}
			if(! $this->diafan->_route->brand && ! $this->diafan->_route->cat)
			{
				$this->theme_view_first_page();
			}

			////navigation//
			$this->diafan->_paginator->nen = $this->list_elements_query_count($time, $cat_ids);
			$this->result["paginator"] = $this->diafan->_paginator->get();
			////navigation///

			$this->result["rows"] = $this->list_elements_query($time, $cat_ids);
			$this->elements($this->result["rows"]);

			if(! $this->is_admin())
			{
				//сохранение кеша
				$this->diafan->_cache->save($this->result, $cache_meta, $this->diafan->_site->module);
			}
		}
		if($this->diafan->_route->cat && ! $this->diafan->_route->brand)
		{
			$this->result["comments"] = $this->diafan->_comments->get(0, '', 'cat');
			$this->prepare_data_category($this->result);
			$this->format_data_category($this->result);

			if (! empty($this->result["previous"]["text"]))
			{
				$this->result["previous"]["text"] =
						$this->diafan->_useradmin->get($this->result["previous"]["text"], 'name', $this->result["previous"]["id"], 'shop_category', _LANG);
			}
			if (! empty($this->result["next"]["text"]))
			{
				$this->result["next"]["text"] =
						$this->diafan->_useradmin->get($this->result["next"]["text"], 'name', $this->result["next"]["id"], 'shop_category', _LANG);
			}
		}
		if($this->diafan->_route->brand)
		{
			$this->prepare_data_brand($this->result);
			$this->format_data_brand($this->result);

			if (! empty($this->result["previous"]["text"]))
			{
				$this->result["previous"]["text"] =
						$this->diafan->_useradmin->get($this->result["previous"]["text"], 'name', $this->result["previous"]["id"], 'shop_brand', _LANG);
			}
			if (! empty($this->result["next"]["text"]))
			{
				$this->result["next"]["text"] =
						$this->diafan->_useradmin->get($this->result["next"]["text"], 'name', $this->result["next"]["id"], 'shop_brand', _LANG);
			}
		}
		if(! $this->diafan->_route->brand && ! $this->diafan->_route->cat)
		{
			$this->theme_view();
		}
		if(! $this->diafan->_route->cat && ! $this->diafan->_route->brand)
		{
			foreach ($this->result["rows"] as &$row)
			{
				$this->prepare_data_element($row);
			}
			foreach ($this->result["rows"] as &$row)
			{
				$this->format_data_element($row);
			}
		}

		if($this->result["breadcrumb"])
		{
			foreach ($this->result["breadcrumb"] as $k => &$b)
			{
				if ($k == 0)
					continue;

				$b["name"] = $this->diafan->_useradmin->get($b["name"], 'name', $b["id"], 'shop_category', _LANG);
			}
		}

		$this->result["link_sort"] = $this->get_sort_links($this->sort_config['sort_directions']);
		$this->result["sort_config"] = $this->sort_config;

		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);

		$this->result['shop_link'] = $this->diafan->_route->module($this->diafan->_site->module);
		if(! empty($this->result["text"]))
		{
			$this->diafan->_keywords->get($this->result["text"]);
		}
	}

	/**
	 * Получает из базы данных количество элементов
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @param array $cat_ids номера категорий, элементы из которых выбираются
	 * @return integer
	 */
	private function list_elements_query_count($time, $cat_ids)
	{
		$count = DB::query_result(
		"SELECT COUNT(DISTINCT e.id) FROM {shop} AS e"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='shop' AND a.element_type='element'" : "")
		.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=e.id AND prh.count_goods>0" : "")
		.($cat_ids ? " INNER JOIN {shop_category_rel} AS r ON e.id=r.element_id" : '')
		." WHERE e.[act]='1' AND e.trash='0' "
		.($cat_ids ? "AND r.cat_id IN (".implode(',', $cat_ids).")" : 'AND e.site_id='.$this->diafan->_site->id)
		.($this->diafan->configmodules('where_period_element') ? " AND e.date_start<=".$time." AND (e.date_finish=0 OR e.date_finish>=".$time.")" : '')
		.($this->diafan->_route->brand ? " AND e.brand_id=".$this->diafan->_route->brand : '')
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		.($this->diafan->configmodules('hide_missing_goods') ? " AND e.no_buy='0'" : "")
		);
		return $count;
	}

	/**
	 * Получает из базы данных элементы для списка элементов
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @param array $cat_ids номера категорий, элементы из которых выбираются
	 * @return array
	 */
	private function list_elements_query($time, $cat_ids)
	{
		switch($this->diafan->configmodules("sort"))
		{
			case 1:
				$order = 's.id DESC';
				break;
			case 2:
				$order = 's.id ASC';
				break;
			case 3:
				$order = 's.name'._LANG.' ASC';
				break;
			default:
				$order = 's.sort DESC, s.id DESC';
		}
		if(! $this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods'))
		{
			$order = "isset_count_goods DESC,".$order;
		}
		$rows = DB::query_range_fetch_all(
		"SELECT s.id, s.[name], s.timeedit, s.[anons], s.site_id, s.brand_id, s.no_buy, s.article, s.[measure_unit], "
		." s.hit, s.new, s.action, s.is_file"
		.(! $this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? ", (prhc.good_id<>0) AS isset_count_goods" : "")
		." FROM {shop} AS s"
		.($this->diafan->_route->sort == 1 || $this->diafan->_route->sort == 2 ?
			" LEFT JOIN {shop_price} AS pr ON pr.good_id=s.id AND pr.trash='0'"
			." AND pr.date_start<=".time()." AND (pr.date_start=0 OR pr.date_finish>=".time().")"
			." AND pr.currency_id=0"
			." AND pr.role_id".($this->diafan->_users->role_id ? " IN (0,".$this->diafan->_users->role_id.")" : "=0")
			." AND (pr.person='0'".($this->person_discount_ids ? " OR pr.discount_id IN(".implode(",", $this->person_discount_ids).")" : "").")"
			: '')
		.($this->sort_config['use_params_for_sort'] ? " LEFT JOIN {shop_param_element} AS sp  ON sp.element_id=s.id AND sp.trash='0' AND sp.param_id=".$this->sort_config['param_ids'][$this->diafan->_route->sort] : '')
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='shop' AND a.element_type='element'" : "")
		.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=s.id AND prh.count_goods>0" : "")
		.(! $this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " LEFT JOIN {shop_price} AS prhc ON prhc.good_id=s.id AND prhc.count_goods>0" : "")
		.($cat_ids ? " INNER JOIN {shop_category_rel} AS r ON s.id=r.element_id" : '')
		." WHERE s.[act]='1' AND s.trash='0' "
		.($cat_ids ? "AND r.cat_id IN (".implode(',', $cat_ids).")" : 'AND s.site_id='.$this->diafan->_site->id)
		.($this->diafan->configmodules('where_period_element') ? " AND s.date_start<=".$time." AND (s.date_finish=0 OR s.date_finish>=".$time.")" : '')
		.($this->diafan->_route->brand ? " AND s.brand_id=".$this->diafan->_route->brand : '')
		.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		.($this->diafan->configmodules('hide_missing_goods') ? " AND s.no_buy='0'" : "")
		." GROUP BY s.id ORDER BY "
		.($this->diafan->_route->sort ? $this->sort_config['sort_directions'][$this->diafan->_route->sort].',' : '')
		."s.no_buy ASC, ".$order,
		$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
		);
		return $rows;
	}

	/**
	 * Получает из базы данных данные о текущей категории для списка элементов в категории
	 *
	 * @return array
	 */
	private function list_category_query()
	{
		if($this->diafan->_route->page > 1)
		{
			$fields = ", '' AS text";
		}
		else
		{
			$fields = ", [text]";
		}
		foreach ($this->diafan->_languages->all as $l)
		{
			$fields .= ', act'.$l["id"];
		}
		$row = DB::query_fetch_array("SELECT id, [name], [anons], [anons_plus] ".$fields.", timeedit, [descr], [keywords], [canonical], sort, parent_id, [title_meta], access, theme, view, view_rows, [act], noindex FROM {shop_category}"
		." WHERE id=%d AND trash='0' AND site_id=%d"
		.(! $this->is_admin() ? " AND [act]='1'" : '')
		." ORDER BY sort ASC, id ASC", $this->diafan->_route->cat, $this->diafan->_site->id);
		return $row;
	}

	/**
	 * Формирует ссылки на предыдущую и следующую категории
	 *
	 * @param integer $sort номер для сортировки текущей категории
	 * @param integer $parent_id номер категории-родителя
	 * @return void
	 */
	private function list_category_previous_next($sort, $parent_id)
	{
		$previous = DB::query_fetch_array(
		"SELECT c.[name], c.id FROM {shop_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='shop' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.trash='0' AND c.site_id=%d"
		." AND (c.sort<%d OR c.sort=%d AND c.id<%d) AND c.parent_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." ORDER BY c.sort DESC, c.id DESC LIMIT 1", $this->diafan->_site->id, $sort, $sort, $this->diafan->_route->cat, $parent_id);
		if ($previous)
		{
			$this->result["previous"]["text"] = $previous["name"];
			$this->result["previous"]["id"]   = $previous["id"];
			$this->result["previous"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $previous["id"], "shop", 'cat');
		}
		$next = DB::query_fetch_array(
		"SELECT c.[name], c.id FROM {shop_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='shop' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.trash='0' AND c.site_id=%d"
		." AND (c.sort>%d OR c.sort=%d AND c.id>%d) AND c.parent_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." ORDER BY c.sort ASC, c.id ASC LIMIT 1", $this->diafan->_site->id, $sort, $sort, $this->diafan->_route->cat, $parent_id);
		if ($next)
		{
			$this->result["next"]["text"] = $next["name"];
			$this->result["next"]["id"] = $next["id"];
			$this->result["next"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $next["id"], "shop", 'cat');
		}
	}

	/**
	 * Получает из базы данных данные о текущем производителе для списка товаров производителя
	 *
	 * @return array
	 */
	private function list_brand_query()
	{
		if($this->diafan->_route->page > 1)
		{
			$fields = ", '' AS text";
		}
		else
		{
			$fields = ", [text]";
		}
		foreach ($this->diafan->_languages->all as $l)
		{
			$fields .= ', act'.$l["id"];
		}
		$row = DB::query_fetch_array("SELECT id, [name] ".$fields.", timeedit, [descr], [keywords], [canonical], sort, [title_meta], [act], theme, view, noindex FROM {shop_brand}"
		." WHERE id=%d AND trash='0' AND site_id=%d"
		.(! $this->is_admin() ? " AND [act]='1'" : '')
		." ORDER BY sort ASC, id ASC", $this->diafan->_route->brand, $this->diafan->_site->id);
		return $row;
	}

	/**
	 * Формирует ссылки на предыдущего и следующего производителя
	 *
	 * @param integer $sort номер для сортировки текущего производителя
	 * @return void
	 */
	private function list_brand_previous_next($sort)
	{
		$previous = DB::query_fetch_array(
		"SELECT id, [name] FROM {shop_brand}"
		. " WHERE [act]='1' AND trash='0' AND site_id=%d"
		. " AND (sort<%d OR sort=%d AND id<%d)"
		. " ORDER BY sort DESC, id DESC LIMIT 1", $this->diafan->_site->id, $sort, $sort, $this->diafan->_route->brand);
		if ($previous)
		{
			$this->result["previous"]["text"] = $previous["name"];
			$this->result["previous"]["id"]   = $previous["id"];
			$this->result["previous"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $previous["id"], "shop", "brand");
		}
		$next = DB::query_fetch_array(
		"SELECT id, [name] FROM {shop_brand}"
		. " WHERE [act]='1' AND trash='0' AND site_id=%d"
		. " AND (sort>%d OR sort=%d AND id>%d)"
		. " ORDER BY sort ASC, id ASC LIMIT 1", $this->diafan->_site->id, $sort, $sort, $this->diafan->_route->brand);
		if ($next)
		{
			$this->result["next"]["text"] = $next["name"];
			$this->result["next"]["id"] = $next["id"];
			$this->result["next"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $next["id"], "shop", "brand");
		}
	}

	/**
	 * Определяет значения META-тегов производителя
	 *
	 * @param array $row данные о текущем производителе
	 * @return void
	 */
	protected function meta_brand($row)
	{
		$this->result["timeedit"] = $row["timeedit"];
		$this->result["titlemodule"] = $row["name"];
		$this->result["edit_meta"]   = array("id" => $row["id"], "table" => "shop_brand");

		if(! empty($row["canonical"]))
		{
			$this->result["canonical"] = $row["canonical"];
		}
		if(! empty($row["noindex"]))
		{
			$this->result["noindex"] = $row["noindex"];
		}

		$config_title = $this->diafan->configmodules("title_tpl_brand");
		$config_keywords = $this->diafan->configmodules("keywords_tpl_brand");
		$config_descr = $this->diafan->configmodules("descr_tpl_brand");

		$this->result["title_meta"] = $row["title_meta"];
		if (! $row["title_meta"] && $config_title)
		{
			if($this->diafan->_route->page > 1)
			{
				$page = $this->diafan->_(' — Страница %d', false, $this->diafan->_route->page);
			}
			else
			{
				$page = '';
			}
			$this->result["title_meta"] = str_replace(
				array('%name', '%page'),
				array($row["name"], $page),
				$config_title
			);
		}

		$this->result["keywords"] = $row["keywords"];
		if (! $row["keywords"] && $config_keywords)
		{
			$this->result["keywords"] = str_replace(
				array('%name'),
				array($row["name"]),
				$config_keywords
			);
		}

		$this->result["descr"] = $row["descr"];
		if (! $row["descr"] && $config_descr)
		{
			$this->result["descr"] = str_replace(
				array('%name'),
				array($row["name"]),
				$config_descr
			);
		}
	}

	/**
	 * Генерирует данные для страницы товара
	 *
	 * @return void
	 */
	public function id()
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name" => "show",
			"cat_id" => $this->diafan->_route->cat,
			"show" => $this->diafan->_route->show,
			"lang_id" => _LANG,
			"site_id" => $this->diafan->_site->id,
			"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
			"discounts" => $this->person_discount_ids,
			"time" => $time
		);
		if (! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{
			$row = $this->id_query($time);
			if (empty($row))
			{
				Custom::inc('includes/404.php');
			}

			if ( ! empty($row['access']) && ! $this->access($row['id']))
			{
				Custom::inc('includes/403.php');
			}
			$this->diafan->_route->cat = $this->diafan->configmodules("cat") ? $row["cat_id"] : 0;
			$row["cat_id"] = $this->diafan->configmodules("cat") ? $row["cat_id"] : 0;

			$row["is_file"] = $this->diafan->configmodules("use_non_material_goods") ? $row["is_file"] : 0;
			if ($this->diafan->configmodules("images_element"))
			{
				$row["img"] = $this->diafan->_images->get(
						'medium', $row["id"], 'shop', 'element',
						$this->diafan->_site->id, $row["name"], 0, 0, 'large'
					);
				$row["preview_images"] = ! empty($row["img"][0]["vs"]["preview"]) ? true : false;
				if($row["preview_images"])
				{
					foreach ($row["img"] as $i => $dummy)
					{
						$row["img"][$i]["preview"] = $row["img"][$i]["vs"]["preview"];
					}
				}
			}
			$this->price($row);

			$this->prepare_param($row["id"], $this->diafan->_site->id);
			$row["brand"] = false;
			if($row["brand_id"])
			{
				if(! isset($this->cache["brand"][$row["brand_id"]]))
				{
					$b = DB::query_fetch_array("SELECT id, [name], site_id FROM {shop_brand} WHERE trash='0' AND [act]='1' AND id=%d", $row["brand_id"]);
					if($b)
					{
						$b["link"] = $this->diafan->_route->link($b["site_id"], $b["id"], "shop", "brand");
						if ($this->diafan->configmodules("images_brand"))
						{
							$b["img"] = $this->diafan->_images->get(
								'medium', $b["id"], 'shop', 'brand',
								$this->diafan->_site->id, $b["name"], 0, 0, $b["link"]
							);
						}
					}

					$this->cache["brand"][$row["brand_id"]] = $b;
				}
				$row["brand"] = $this->cache["brand"][$row["brand_id"]];
			}
			$additional_cost_rels = DB::query_fetch_all("SELECT a.id, a.[name], a.percent, a.price, a.amount, a.required, r.element_id, r.summ FROM {shop_additional_cost} AS a INNER JOIN {shop_additional_cost_rel} AS r ON r.additional_cost_id=a.id WHERE r.element_id=%d AND a.trash='0'", $row["id"]);

			$row["additional_cost"] = array();
			foreach($additional_cost_rels AS $a_c_rel)
			{
				if($a_c_rel["percent"] || $a_c_rel["amount"])
				{
					foreach($row["price_arr"] as $price)
					{
						if($a_c_rel["amount"] && $price["price"] >= $a_c_rel["amount"])
						{
							$a_c_rel["price_summ"][$price["price_id"]] = 0;
						}
						elseif($a_c_rel["percent"])
						{
							$a_c_rel["price_summ"][$price["price_id"]] = ($price["price"] * $a_c_rel["percent"]) / 100;
						}
						else
						{
							$a_c_rel["price_summ"][$price["price_id"]] = $a_c_rel["price"];
						}
						$a_c_rel["format_price_summ"][$price["price_id"]] = $this->diafan->_shop->price_format($a_c_rel["price_summ"][$price["price_id"]]);
					}
				}
				else
				{
					if(! $a_c_rel["summ"])
					{
						$a_c_rel["summ"] = $a_c_rel["price"];
					}
					if($a_c_rel["summ"])
					{
						$a_c_rel["format_summ"] = $this->diafan->_shop->price_format($a_c_rel["summ"]);
					}
				}
				$row["additional_cost"][] = $a_c_rel;
			}

			foreach ($row as $id => $value)
			{
				$this->result[$id] = $value;
			}
			$this->result["currency"] = $this->diafan->configmodules("currency");
			$this->param($this->result);

			$this->meta($row);

			$this->result["title_meta"] = str_replace('%article', $row["article"], $this->result["title_meta"]);
			$this->result["keywords"] = str_replace('%article', $row["article"], $this->result["keywords"]);
			$this->result["descr"] = str_replace('%article', $row["article"], $this->result["descr"]);

			$this->theme_view_element($row);

			$this->id_previous_next($row["sort"], $row["no_buy"], $time);

			$this->result["breadcrumb"] = $this->get_breadcrumb();

			if($row["act"])
			{
				//сохранение кеша
				$this->diafan->_cache->save($this->result, $cache_meta, $this->diafan->_site->module);
			}
		}
		$this->diafan->_route->cat = $this->result["cat_id"];

		if ( ! empty($this->result["previous"]["text"]))
		{
			$this->result["previous"]["text"] =
					$this->diafan->_useradmin->get($this->result["previous"]["text"], 'name', $this->result["previous"]["id"], 'shop', _LANG);
		}
		if ( ! empty($this->result["next"]["text"]))
		{
			$this->result["next"]["text"] =
					$this->diafan->_useradmin->get($this->result["next"]["text"], 'name', $this->result["next"]["id"], 'shop', _LANG);
		}
		foreach ($this->result["breadcrumb"] as $k => &$b)
		{
			if ($k == 0)
				continue;

			$b["name"] = $this->diafan->_useradmin->get($b["name"], 'name', $b["id"], 'shop_category', _LANG);
		}

		$this->counter_view();

		$this->prepare_data_element($this->result);
		$this->format_data_element($this->result, 'id');

		if($this->result["anons_plus"])
		{
			$this->result["text"] = $this->result["anons"].$this->result["text"];
			$this->result["anons"] = '';
		}

		$this->result["comments"] = $this->diafan->_comments->get();

		$this->result['shop_link'] = $this->diafan->_route->module($this->diafan->_site->module);
		$this->diafan->_keywords->get($this->result["text"]);
	}

	/**
	 * Получает из базы данных данные о текущем элементе для страницы элемента
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @return array
	 */
	private function id_query($time)
	{
		$fields = '';
		foreach ($this->diafan->_languages->all as $l)
		{
			$fields .= ', act'.$l["id"];
		}
		$row = DB::query_fetch_array("SELECT id, [name], [anons], [anons_plus], [text], timeedit, cat_id,"
		." site_id, brand_id, [canonical], [keywords], [descr], sort, [title_meta], hit, new, action, is_file,"
		." weight, width, height, length,"
		." no_buy, article, [measure_unit], access, theme, view, [act], date_start, date_finish, noindex".$fields." FROM {shop}"
		." WHERE id=%d AND trash='0' AND site_id=%d"
		.(! $this->is_admin() ? " AND [act]='1'" : "")
		.(! $this->is_admin() && $this->diafan->configmodules('where_period_element') ? " AND date_start<=".$time." AND (date_finish=0 OR date_finish>=".$time.")" : '')
		." LIMIT 1",
		$this->diafan->_route->show, $this->diafan->_site->id);
		return $row;
	}

	/**
	 * Формирует ссылки на предыдущий и следующий элемент
	 *
	 * @param integer $sort номер для сортировки текущего элемента
	 * @param boolean $no_buy товар временно отсутствует
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @return void
	 */
	private function id_previous_next($sort, $no_buy, $time)
	{
		switch($this->diafan->configmodules("sort"))
		{
			case 1:
				$order = 'e.id DESC';
				$order_previous = 'e.id ASC';
				break;
			case 2:
				$order = 'e.id ASC';
				$order_previous = 'e.id DESC';
				break;
			case 3:
				$order = 'e.name'._LANG.' ASC';
				$order_previous = 'e.name'._LANG.' DESC';
				break;
			default:
				$order = 'e.sort DESC, e.id DESC';
				$order_previous = 'e.sort ASC, e.id ASC';
		}
		$previous = DB::query_fetch_array(
		"SELECT e.[name], e.id FROM {shop} AS e"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='shop' AND a.element_type='element'" : "")
		.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=e.id AND prh.count_goods>0" : "")
		." WHERE e.[act]='1' AND e.trash='0' AND e.site_id=%d"
		.($this->diafan->configmodules("cat") ? " AND e.cat_id='".$this->diafan->_route->cat."'" : '')
		." AND (e.no_buy<'%d' OR e.no_buy='%d' AND (e.sort>%d OR e.sort=%d AND e.id>%d))"
		.($this->diafan->configmodules('where_period_element') ? " AND e.date_start<=".$time." AND (e.date_finish=0 OR e.date_finish>=".$time.")" : '')
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		.($this->diafan->configmodules('hide_missing_goods') ? " AND e.no_buy='0'" : "")
		." ORDER BY e.no_buy DESC, ".$order_previous." LIMIT 1",
		$this->diafan->_site->id, $no_buy, $no_buy, $sort, $sort, $this->diafan->_route->show
		);
		$next = DB::query_fetch_array(
		"SELECT e.[name], e.id FROM {shop} AS e"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='shop' AND a.element_type='element'" : "")
		.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=e.id AND prh.count_goods>0" : "")
		." WHERE e.[act]='1' AND e.trash='0' AND e.site_id=%d"
		.($this->diafan->configmodules("cat") ? " AND e.cat_id='".$this->diafan->_route->cat."'" : '')
		." AND (e.no_buy>'%d' OR e.no_buy='%d' AND (e.sort<%d OR e.sort=%d AND e.id<%d))"
		.($this->diafan->configmodules('where_period_element') ? " AND e.date_start<=".$time." AND (e.date_finish=0 OR e.date_finish>=".$time.")" : '')
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		.($this->diafan->configmodules('hide_missing_goods') ? " AND e.no_buy='0'" : "")
		." ORDER BY e.no_buy ASC, ".$order." LIMIT 1",
		$this->diafan->_site->id, $no_buy, $no_buy, $sort, $sort, $this->diafan->_route->show
		);
		if ($previous)
		{
			$this->result["previous"]["text"] = $previous["name"];
			$this->result["previous"]["id"] = $previous["id"];
			$this->result["previous"]["link"] = $this->diafan->_route->link($this->diafan->_site->id,  $previous["id"], "shop");
		}
		if ($next)
		{
			$this->result["next"]["text"] = $next["name"];
			$this->result["next"]["id"] = $next["id"];
			$this->result["next"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $next["id"], "shop");
		}
	}

	/**
	 * Генерирует данные для шаблонной функции: блок товаров
	 *
	 * @param integer $count количество товаров
	 * @param array $site_ids страницы сайта
	 * @param array $cat_ids категории
	 * @param array $ids товары
	 * @param array $brand_ids производители
	 * @param string $sort сортировка date - по дате, rand - случайно, price - по цене, sale - по количеству продаж
	 * @param integer $images количество изображений
	 * @param string $images_variation размер изображений
	 * @param string $param дополнительные параметры
	 * @param boolean $hits_only только хиты
	 * @param boolean $action_only только акции
	 * @param boolean $new_only только новинки
	 * @param boolean $discount_only только товары со скидкой
	 * @param string $tag тег
	 * @return array
	 */
	public function show_block($count, $site_ids, $cat_ids, $ids, $brand_ids, $sort, $images, $images_variation, $param, $hits_only, $action_only, $new_only, $discount_only, $tag)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name" => "block",
			"site_ids" => $site_ids,
			"cat_ids" => $cat_ids,
			"ids" => $ids,
			"brand_ids" => $brand_ids,
			"count" => $count,
			"lang_id" => _LANG,
			"images" => $images,
			"images_variation" => $images_variation,
			"param" => $param,
			"sort" => $sort,
			"current"  => ($this->diafan->_site->module == 'shop' && $this->diafan->_route->show ? $this->diafan->_route->show : ''),
			"hits_only" => (int) $hits_only,
			"action_only" => (int) $action_only,
			"new_only" => (int) $new_only,
			"discount_only" => (int) $discount_only,
			"access" => ($this->diafan->configmodules('where_access_element', 'shop') || $this->diafan->configmodules('where_access_cat', 'shop') ? $this->diafan->_users->role_id : 0),
			"discounts" => ($sort == "rand" || $sort == "sale" ? "" : $this->person_discount_ids),
			"time" => $time,
			"tag" => $tag,
		);

		if ($sort == "rand" || $sort == "sale" || ! $this->result = $this->diafan->_cache->get($cache_meta, "shop"))
		{
			$minus = array();
			$one_cat_id = count($cat_ids) == 1 && substr($cat_ids[0], 0, 1) !== '-' ? $cat_ids[0] : false;
			$one_brand_id = count($brand_ids) == 1 ? $brand_ids[0] : false;
			if(! $this->validate_attribute_site_cat('shop', $site_ids, $cat_ids, $minus))
			{
				return false;
			}
			if(! $this->validate_attribute_ids($ids, $minus))
			{
				return false;
			}
			if(! $this->validate_attribute_brand($brand_ids, $minus))
			{
				return false;
			}
			$params = array();
			if ($param)
			{
				$param = explode('&', $param);
				foreach ($param as $p)
				{
					if(strpos($p, '>=') !== false)
					{
						$operator = '>=';
					}
					elseif(strpos($p, '<=') !== false)
					{
						$operator = '<=';
					}
					elseif(strpos($p, '<>') !== false)
					{
						$operator = '<>';
					}
					elseif(strpos($p, '>') !== false)
					{
						$operator = '>';
					}
					elseif(strpos($p, '<') !== false)
					{
						$operator = '<';
					}
					else
					{
						$operator = '=';
					}
					list($id, $value) = explode($operator, $p, 2);
					$id = preg_replace('/[^0-9]+/', '', $id);
					if ( ! empty($params[$id]))
					{
						if (is_array($params[$id]))
						{
							$params[$id][] = $value;
							$operators[$id][] = $operator;
						}
						else
						{
							$params[$id] = array($params[$id], $value);
							$operators[$id] = array($operators[$id], $operator);
						}
					}
					else
					{
						$params[$id] = $value;
						$operators[$id] = $operator;
					}
				}
			}
			$where = '';
			$inner = '';
			$values = array();
			foreach ($params as $id => $value)
			{
				if (is_array($value))
				{
					$inner .= "
					INNER JOIN {shop_param_element} AS pe".$id." ON pe".$id.".element_id=e.id AND pe".$id.".param_id='".$id."'"
							. " AND pe".$id.".trash='0' AND (";
					foreach ($value as $i => $val)
					{
						if ($value[0] != $val)
						{
							if(in_array($operators[$id][$i], array('>', '<', '>=', '<=')))
							{
								$inner .= " AND ";
							}
							else
							{
								$inner .= " OR ";
							}
						}
						$inner .= "pe".$id.".value".$this->diafan->_languages->site.$operators[$id][$i]."'%h'";
						$values[] = $val;
					}
					$inner .= ")";
				}
				else
				{
					$inner .= "
					INNER JOIN {shop_param_element} AS pe".$id." ON pe".$id.".element_id=e.id AND pe".$id.".param_id='".$id."'"
					. " AND pe".$id.".trash='0' AND pe".$id.".value".$this->diafan->_languages->site.$operators[$id]."'%h'";
					$values[] = $value;
				}
			}

			if($cat_ids)
			{
				$inner .= " INNER JOIN {shop_category_rel} as r ON r.element_id=e.id"
				." AND r.cat_id IN (".implode(',', $cat_ids).")";
			}
			elseif(! empty($minus["cat_ids"]))
			{
				$inner .= " INNER JOIN {shop_category_rel} as r ON r.element_id=e.id"
				." AND r.cat_id NOT IN (".implode(',', $minus["cat_ids"]).")";
			}
			if($site_ids)
			{
				$where .= " AND e.site_id IN (".implode(",", $site_ids).")";
			}
			elseif(! empty($minus["site_ids"]))
			{
				$where .= " AND e.site_id NOT IN (".implode(",", $minus["site_ids"]).")";
			}
			if($ids)
			{
				$where .= " AND e.id IN (".implode(",", $ids).")";
			}
			elseif(! empty($minus["ids"]))
			{
				$where .= " AND e.id NOT IN (".implode(",", $minus["ids"]).")";
			}
			if($brand_ids)
			{
				$where .= " AND e.brand_id IN (".implode(",", $brand_ids).")";
			}
			elseif(! empty($minus["brand_ids"]))
			{
				$where .= " AND e.brand_id NOT IN (".implode(",", $minus["brand_ids"]).")";
			}
			if($tag)
			{
				$t = DB::query_fetch_array("SELECT id, [name] FROM {tags_name} WHERE [name]='%s' AND trash='0'", $tag);
				if(! $tag)
				{
					return false;
				}
				$inner .= " INNER JOIN {tags} AS t ON t.element_id=e.id AND t.element_type='element' AND t.module_name='shop' AND t.tags_name_id=".$t["id"];
			}

			$current = ($this->diafan->_site->module == 'shop' && $this->diafan->_route->show ? $this->diafan->_route->show : false);
			if($current && $ids && in_array($current, $ids))
			{
				$current = false;
			}

			if ($sort == "rand")
			{
				$max_count = DB::query_result("SELECT COUNT(DISTINCT e.id) FROM {shop} as e"
				.$inner
				.($discount_only ? " INNER JOIN {shop_price} AS pr ON pr.good_id=e.id AND pr.trash='0'"
				." AND pr.date_start<=".time()." AND (pr.date_start=0 OR pr.date_finish>=".time().")"
				." AND pr.currency_id=0"
				." AND pr.role_id".($this->diafan->_users->role_id ? " IN (0,".$this->diafan->_users->role_id.")" : "=0")
				." AND (pr.person='0'".($this->person_discount_ids ? " OR pr.discount_id IN(".implode(",", $this->person_discount_ids).")" : "").")"
				: '')
				.($this->diafan->configmodules('where_access_element', 'shop') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='shop' AND a.element_type='element'" : "")
				.($this->diafan->configmodules('hide_missing_goods', 'shop') && $this->diafan->configmodules('use_count_goods', 'shop') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=e.id AND prh.count_goods>0" : "")
				." WHERE e.[act]='1' AND e.trash='0'"
				.$where
				.($current ? " AND e.id<>".$current : '')
				.($hits_only ? " AND e.hit='1' " : "")
				.($action_only ? " AND e.action='1' " : "")
				.($new_only ? " AND e.new='1' " : "")
				.($discount_only ? "  AND (pr.discount_id>0 OR pr.old_price>pr.price)" : "")
				.($this->diafan->configmodules('where_period_element', 'shop') ? " AND e.date_start<=".$time." AND (e.date_finish=0 OR e.date_finish>=".$time.")" : '')
				.($this->diafan->configmodules('where_access_element', 'shop') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
				.($this->diafan->configmodules('hide_missing_goods', 'shop') ? " AND e.no_buy='0'" : ""),
				$values
				);
				$rands = array();
				for ($i = 1; $i <= min($max_count, $count); $i ++ )
				{
					do
					{
						$rand = mt_rand(0, $max_count - 1);
					}
					while (in_array($rand, $rands));
					$rands[] = $rand;
				}
			}
			else
			{
				$rands[0] = 1;
			}
			$this->result["rows"] = array();

			switch($sort)
			{
				case 'price':
					$order = ' ORDER BY MIN(pr.price) ASC';
					break;

				case 'sale':
					$order = ' ORDER BY count_sale DESC';
					break;

				case 'date':
					$order = ' ORDER BY e.id DESC';
					break;

				case 'rand':
					$order = '';
					break;

				default:
					switch($this->diafan->configmodules("sort", "shop", ($site_ids ? $site_ids[0] : 0)))
					{
						case 1:
							$order = ' ORDER BY e.id DESC';
							break;
						case 2:
							$order = ' ORDER BY e.id ASC';
							break;
						case 3:
							$order = ' ORDER BY e.name'._LANG.' ASC';
							break;
						default:
							$order = ' ORDER BY e.sort DESC';
					}
					break;
			}

			foreach ($rands as $rand)
			{
				$rows = DB::query_fetch_all("SELECT e.id, e.[name], e.[anons], e.timeedit, e.site_id, e.brand_id, e.no_buy, e.article,
				e.[measure_unit], e.hit, e.new, e.action, e.is_file".($sort == "sale" ? ", COUNT(g.id) AS count_sale" : "")."
				FROM {shop} AS e"
				. ($sort == "sale" ? " INNER JOIN {shop_order_goods} AS g ON g.good_id=e.id AND g.trash='0'" : '')
				. ($sort == "price" || $discount_only ? " INNER JOIN {shop_price} AS pr ON pr.good_id=e.id AND pr.trash='0'"
				." AND pr.date_start<=".time()." AND (pr.date_start=0 OR pr.date_finish>=".time().")"
				." AND pr.currency_id=0"
				." AND pr.role_id".($this->diafan->_users->role_id ? " IN (0,".$this->diafan->_users->role_id.")" : "=0")
				." AND (pr.person='0'".($this->person_discount_ids ? " OR pr.discount_id IN(".implode(",", $this->person_discount_ids).")" : "").")"
				: '')
				.$inner
				.($this->diafan->configmodules('where_access_element', 'shop') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='shop' AND a.element_type='element'" : "")
				.($this->diafan->configmodules('hide_missing_goods', 'shop') && $this->diafan->configmodules('use_count_goods', 'shop') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=e.id AND prh.count_goods>0" : "")
				." WHERE e.[act]='1' AND e.trash='0'"
				.($current ? " AND e.id<>".$current : '')
				.($hits_only ? " AND e.hit='1' " : "")
				.($action_only ? " AND e.action='1' " : "")
				.($new_only ? " AND e.new='1' " : "")
				.($discount_only ? "  AND (pr.discount_id>0 OR pr.old_price>pr.price)" : "")
				.$where
				.($this->diafan->configmodules('where_period_element', 'shop') ? " AND e.date_start<=".$time." AND (e.date_finish=0 OR e.date_finish>=".$time.")" : '')
				.($this->diafan->configmodules('where_access_element', 'shop') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
				.($this->diafan->configmodules('hide_missing_goods', 'shop') ? " AND e.no_buy='0'" : "")
				." GROUP BY e.id"
				.$order
				.' LIMIT '
				.($sort == "rand" ? $rand : 0).', '
				.($sort == "rand" ? 1 : $count), $values);
				$this->result["rows"] = array_merge($this->result["rows"], $rows);
			}
			$this->elements($this->result["rows"], 'block', array("count" => $images, "variation" => $images_variation));
			if(! empty($this->result["rows"]))
			{
				// если категория только одна, задаем ссылку на нее
				if ($one_cat_id)
				{
					$cat = DB::query_fetch_array("SELECT [name], site_id, id FROM {shop_category} WHERE id=%d LIMIT 1", $one_cat_id);

					$this->result["name"] = $cat["name"];
					$this->result["link_all"] = $this->diafan->_route->link($cat["site_id"], $cat["id"], 'shop', 'cat');
					$this->result["is_cat"] = true;
				}
				// если производитель только один, задаем ссылку на него
				elseif ($one_brand_id)
				{
					$brand = DB::query_fetch_array("SELECT [name], site_id, id FROM {shop_brand} WHERE id=%d LIMIT 1", $one_brand_id);

					$this->result["name"] = $brand["name"];
					$this->result["link_all"] = $this->diafan->_route->link($brand["site_id"], $brand["id"], 'shop', 'brand');
					$this->result["is_brand"] = true;
				}
				// если раздел сайта только один, то задаем ссылку на него
				elseif (count($site_ids) == 1)
				{
					$this->result["name"] = DB::query_result("SELECT [name] FROM {site} WHERE id=%d LIMIT 1", $site_ids[0]);
					$this->result["link_all"] = $this->diafan->_route->link($site_ids[0]);
				}
				if($tag)
				{
					$this->result["name"] .= ': '.$t["name"];
				}
			}

			//сохранение кеша
			if ($sort != "rand")
			{
				$this->diafan->_cache->save($this->result, $cache_meta, "shop");
			}
		}
		foreach ($this->result["rows"] as &$row)
		{
			$this->prepare_data_element($row);
		}
		foreach ($this->result["rows"] as &$row)
		{
			$this->format_data_element($row);
		}

		$this->result["view_rows"] = 'rows';
	}

	/**
	 * Валидация атрибута ids для шаблонных тегов
	 *
	 * @param array $ids производители
	 * @param array $minus производители, которые вычитаются
	 * @return boolean
	 */
	private function validate_attribute_ids(&$ids, &$minus)
	{
		if (! empty($ids) && count($ids) == 1 && empty($ids[0]))
		{
			$ids = array();
		}
		if (! empty($ids))
		{
			$new_ids = array();
			foreach ($ids as $id)
			{
				if(substr($id, 0, 1) == '-')
				{
					$id = substr($id, 1);
					if(preg_replace('/[^0-9]+/', '', $id) != $id)
					{
						$this->error_insert_tag('Атрибут id="%s" задан неверно. Номер товара %s должен быть числом.', 'shop', implode(',', $ids), $id);
						return false;
					}
					$minus["ids"][] = $id;
					continue;
				}
				$id = trim($id);
				if(preg_replace('/[^0-9]+/', '', $id) != $id)
				{
					$this->error_insert_tag('Атрибут id="%s" задан неверно. Номер товара %s должен быть числом.', 'shop', implode(',', $ids), $id);
					return false;
				}
				elseif(in_array($id, $new_ids))
				{
					$this->error_insert_tag('Атрибут ids="%s" задан неверно. Повторяется товар %s.', 'shop', implode(',', $ids), $id);
					return false;
				}
				else
				{
					$new_ids[] = $id;
				}
			}
			$ids = $new_ids;
			$new_ids = array();
			$isset_ids = array();
			if($ids)
			{
				$rows = DB::query_fetch_all("SELECT id, trash FROM {shop} WHERE id IN (%h)", implode(",", $ids));
				foreach ($rows as $row)
				{
					if($row["trash"])
					{
						$this->error_insert_tag('Атрибут ids="%s" задан неверно. Товар %d удален.', 'shop', implode(',', $ids), $row["id"]);
						return false;
					}
					$isset_ids[] = $row["id"];

					if(! in_array($row["id"], $new_ids))
					{
						$new_ids[] = $row["id"];
					}
				}
				// нет доступа к товару для текущего пользователя
				if(! $new_ids)
				{
					return false;
				}
				foreach ($ids as $id)
				{
					if(! in_array($id, $isset_ids))
					{
						$this->error_insert_tag('Атрибут ids="%s" задан неверно. Товар %s не существует.', 'shop', implode(',', $ids), $id);
						return false;
					}
				}
				$ids = $new_ids;
				return true;
			}
		}
		return true;
	}

	/**
	 * Валидация атрибута brand_id для шаблонных тегов
	 *
	 * @param array $brand_ids производители
	 * @param array $minus производители, которые вычитаются
	 * @return boolean
	 */
	private function validate_attribute_brand(&$brand_ids, &$minus)
	{
		if (! empty($brand_ids) && count($brand_ids) == 1 && empty($brand_ids[0]))
		{
			$brand_ids = array();
		}
		if (! empty($brand_ids))
		{
			$new_brand_ids = array();
			foreach ($brand_ids as $brand_id)
			{
				if(substr($brand_id, 0, 1) == '-')
				{
					$brand_id = substr($brand_id, 1);
					if(preg_replace('/[^0-9]+/', '', $brand_id) != $brand_id)
					{
						$this->error_insert_tag('Атрибут brand_id="%s" задан неверно. Номер производителя %s должен быть числом.', 'shop', implode(',', $brand_ids), $brand_id);
						return false;
					}
					$minus["brand_ids"][] = $brand_id;
					continue;
				}
				$brand_id = trim($brand_id);
				if(preg_replace('/[^0-9]+/', '', $brand_id) != $brand_id)
				{
					$this->error_insert_tag('Атрибут brand_id="%s" задан неверно. Номер производителя %s должен быть числом.', 'shop', implode(',', $brand_ids), $brand_id);
					return false;
				}
				elseif(in_array($brand_id, $new_brand_ids))
				{
					$this->error_insert_tag('Атрибут brand_id="%s" задан неверно. Повторяется производитель %s.', 'shop', implode(',', $brand_ids), $brand_id);
					return false;
				}
				else
				{
					$new_brand_ids[] = $brand_id;
				}
			}
			$brand_ids = $new_brand_ids;
			$new_brand_ids = array();
			$isset_brand_ids = array();
			if($brand_ids)
			{
				$rows = DB::query_fetch_all("SELECT id, trash FROM {shop_brand} WHERE id IN (%h)", implode(",", $brand_ids));
				foreach ($rows as $row)
				{
					if($row["trash"])
					{
						$this->error_insert_tag('Атрибут brand_id="%s" задан неверно. Производитель %d удален.', 'shop', implode(',', $brand_ids), $row["id"]);
						return false;
					}
					$isset_brand_ids[] = $row["id"];

					if(! in_array($row["id"], $new_brand_ids))
					{
						$new_brand_ids[] = $row["id"];
					}
				}
				// нет доступа к производит для текущего пользователя
				if(! $new_brand_ids)
				{
					return false;
				}
				foreach ($brand_ids as $brand_id)
				{
					if(! in_array($brand_id, $isset_brand_ids))
					{
						$this->error_insert_tag('Атрибут brand_id="%s" задан неверно. Производитель %s не существует.', 'shop', implode(',', $brand_ids), $brand_id);
						return false;
					}
				}
				$brand_ids = $new_brand_ids;
				return true;
			}
		}
		return true;
	}

	/**
	 * Генерирует данные для шаблонной функции: блок связанных товаров
	 *
	 * @param integer $count количество товаров
	 * @param integer $images количество изображений
	 * @param string $images_variation размер изображений
	 * @return array
	 */
	public function show_block_rel($count, $images, $images_variation)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name" => "block_rel",
			"count" => $count,
			"lang_id" => _LANG,
			"good_id" => $this->diafan->_route->show,
			"images" => $images,
			"images_variation" => $images_variation,
			"access" => ($this->diafan->configmodules('where_access_element', 'shop') || $this->diafan->configmodules('where_access_cat', 'shop') ? $this->diafan->_users->role_id : 0),
			"discounts" => $this->person_discount_ids,
			"time" => ($this->diafan->configmodules('where_period_element', 'shop') ? $time : '')
		);

		if (! $this->result = $this->diafan->_cache->get($cache_meta, "shop"))
		{
			$this->result["rows"] = DB::query_range_fetch_all(
			"SELECT e.id, e.[name], e.[anons], e.timeedit, e.site_id, e.brand_id, e.no_buy, e.article,"
			." e.[measure_unit], e.hit, e.new, e.action, e.is_file FROM {shop} AS e"
			." INNER JOIN {shop_rel} AS r ON e.id=r.rel_element_id AND r.element_id=%d"
			.($this->diafan->configmodules("rel_two_sided") ? " OR e.id=r.element_id AND r.rel_element_id=".$this->diafan->_route->show : '')
			.($this->diafan->configmodules('where_access_element', 'shop') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='shop' AND a.element_type='element'" : "")
			.($this->diafan->configmodules('hide_missing_goods', 'shop') && $this->diafan->configmodules('use_count_goods', 'shop') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=e.id AND prh.count_goods>0" : "")
			." WHERE e.[act]='1' AND e.trash='0'"
			.($this->diafan->configmodules('where_period_element', 'shop') ? " AND e.date_start<=".$time." AND (e.date_finish=0 OR e.date_finish>=".$time.")" : '')
			.($this->diafan->configmodules('where_access_element', 'shop') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			.($this->diafan->configmodules('hide_missing_goods', 'shop') ? " AND e.no_buy='0'" : "")
			." GROUP BY e.id"
			." ORDER BY e.id DESC",
			$this->diafan->_route->show, 0, $count
			);
			$this->elements($this->result["rows"], 'block', array("count" => $images, "variation" => $images_variation));
			$this->diafan->_cache->save($this->result, $cache_meta, "shop");
		}
		foreach ($this->result["rows"] as &$row)
		{
			$this->prepare_data_element($row);
		}
		foreach ($this->result["rows"] as &$row)
		{
			$this->format_data_element($row);
		}

		$this->result["view_rows"] = 'rows';
	}

	/**
	 * Генерирует данные для шаблонной функции: блок товаров, которые обычно покупают с текущим
	 *
	 * @param integer $count количество товаров
	 * @param integer $images количество изображений
	 * @param string $images_variation размер изображений
	 * @return array
	 */
	public function show_block_order_rel($count, $images, $images_variation)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		$this->result["rows"] = DB::query_range_fetch_all(
		"SELECT e.id, e.[name], e.[anons], e.timeedit, e.site_id, e.brand_id, e.no_buy, e.article,"
		." e.[measure_unit], e.hit, e.new, e.action, e.is_file, COUNT(g.good_id) AS count_good FROM {shop} AS e"
		." INNER JOIN {shop_order_goods} AS g ON g.good_id=e.id AND g.good_id<>%d"
		." INNER JOIN {shop_order_goods} AS eg ON eg.order_id=g.order_id AND eg.good_id=%d"
		.($this->diafan->configmodules('where_access_element', "shop") ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='shop' AND a.element_type='element'" : "")
		." WHERE e.[act]='1' AND e.trash='0'"
		.($this->diafan->configmodules('where_period_element', 'shop') ? " AND e.date_start<=".$time." AND (e.date_finish=0 OR e.date_finish>=".$time.")" : '')
		.($this->diafan->configmodules('where_access_element', "shop") ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY e.id"
		." ORDER BY count_good DESC",
		$this->diafan->_route->show, $this->diafan->_route->show, 0, $count
		);
		$this->elements($this->result["rows"], 'block', array("count" => $images, "variation" => $images_variation));

		foreach ($this->result["rows"] as &$row)
		{
			$this->prepare_data_element($row);
		}
		foreach ($this->result["rows"] as &$row)
		{
			$this->format_data_element($row);
		}

		$this->result["view_rows"] = 'rows';
	}

	/**
	 * Генерирует данные для шаблонной функции: блок производителей
	 *
	 * @param integer|string $count количество производителей
	 * @param array $site_ids страницы сайта
	 * @param array|string $cat_ids категории
	 * @param string $sort сортировка name - по имени, rand - случайно
	 * @param integer $images количество изображений
	 * @param string $images_variation размер изображений
	 * @return array
	 */
	public function show_brand($count, $site_ids, $cat_ids, $sort, $images, $images_variation)
	{
		$current_cat = false;
		if($cat_ids === "current")
		{
			if($this->diafan->_site->module == "shop" && (empty($site_ids[0]) || in_array($this->diafan->_site->id, $site_ids))
			   && $this->diafan->_route->cat)
			{
				$current_cat = true;
				$cat_ids = array($this->diafan->_route->cat);
			}
			else
			{
				$cat_ids = array();
			}
		}

		//кеширование
		$cache_meta = array(
			"name" => "block",
			"site_ids" => $site_ids,
			"cat_ids" => $cat_ids,
			"count" => $count,
			"lang_id" => _LANG,
			"sort" => $sort,
			"images" => $images,
			"images_variation" => $images_variation
		);

		if ($sort == "rand" || ! $this->result = $this->diafan->_cache->get($cache_meta, "shop"))
		{
			$minus = array();
			if(! $this->validate_attribute_site_cat('shop', $site_ids, $cat_ids, $minus))
			{
				return false;
			}
			$where = '';
			$inner = '';

			if($cat_ids)
			{
				$inner .= " INNER JOIN {shop_brand_category_rel} as r ON r.element_id=e.id"
				." AND r.cat_id IN (".implode(',', $cat_ids).")";
			}
			elseif(! empty($minus["cat_ids"]))
			{
				$inner .= " INNER JOIN {shop_brand_category_rel} as r ON r.element_id=e.id"
				." AND r.cat_id NOT IN (".implode(',', $minus["cat_ids"]).")";
			}
			if($site_ids)
			{
				$where .= " AND e.site_id IN (".implode(",", $site_ids).")";
			}
			elseif(! empty($minus["site_ids"]))
			{
				$where .= " AND e.site_id NOT IN (".implode(",", $minus["site_ids"]).")";
			}

			if ($sort == "rand" && $count !== "all")
			{
				$max_count = DB::query_result("SELECT COUNT(DISTINCT e.id) FROM {shop_brand} AS e"
				.$inner
				. " WHERE e.[act]='1' AND e.trash='0'"
				. $where);
				$rands = array();
				for ($i = 1; $i <= min($max_count, $count); $i ++ )
				{
					do
					{
						$rand = mt_rand(0, $max_count - 1);
					}
					while (in_array($rand, $rands));
					$rands[] = $rand;
				}
			}
			else
			{
				$rands[0] = 1;
			}
			$this->result["rows"] = array();

			switch($sort)
			{
				case 'name':
					$order = ' ORDER BY e.[name] ASC';
					break;

				case 'rand':
					if($count !== "all")
					{
						$order = '';
					}
					else
					{
						$order = ' ORDER BY RAND()';
					}
					break;

				default:
					$order = ' ORDER BY e.sort ASC';
					break;
			}

			foreach ($rands as $rand)
			{
				$rows = DB::query_fetch_all("SELECT e.id, e.[name], e.[text], e.site_id
				FROM {shop_brand} AS e"
				.$inner
				. " WHERE e.[act]='1' AND e.trash='0'"
				. $where
				. " GROUP BY e.id"
				. $order
				.($count !== "all" ?
					' LIMIT '
					.($sort == "rand" ? $rand : 0).', '
					.($sort == "rand" ? 1 : $count)
				: ''));
				$this->result["rows"] = array_merge($this->result["rows"], $rows);
			}

			foreach($this->result["rows"] as &$row)
			{
				if($images && $this->diafan->configmodules("images_brand", "shop", $row["site_id"]))
				{
					$this->diafan->_images->prepare($row["id"], 'shop', 'brand');
				}
				if(! $current_cat)
				{
					$this->diafan->_route->prepare($row["site_id"], $row["id"], "shop", "brand");
				}
			}
			foreach($this->result["rows"] as &$row)
			{
				if($current_cat)
				{
					$row["link"] = $this->diafan->_route->current_link(array("page", "brand", "show"), array("brand" => $row["id"]));
				}
				else
				{
					$row["link"] = $this->diafan->_route->link($row["site_id"], $row["id"], "shop", "brand");
				}

				if($images && $this->diafan->configmodules("images_brand", "shop", $row["site_id"]))
				{
					$row["img"] = $this->diafan->_images->get(
						$images_variation, $row["id"], 'shop', 'brand',
						$this->diafan->_site->id, $row["name"], 0, $images, $row["link"]
					);
				}
			}

			if($sort != "rand")
			{
				$this->diafan->_cache->save($this->result, $cache_meta, "shop");
			}
		}
		foreach ($this->result["rows"] as &$row)
		{
			$this->prepare_data_brand($row);
		}
		foreach ($this->result["rows"] as &$row)
		{
			$this->format_data_brand($row);
		}
	}

	/**
	 * Генерирует данные для шаблонной функции: блок категорий
	 *
	 * @param array $site_ids страницы сайта
	 * @param integer $images количество изображений
	 * @param string $images_variation размер изображений
	 * @param integer $count_level количество уровней
	 * @param boolean $number_elements выводить количество товаров в категории
	 * @return array
	 */
	public function show_category($site_ids, $images, $images_variation, $count_level, $number_elements)
	{
		//кеширование
		$cache_meta = array(
			"name" => "block",
			"site_ids" => $site_ids,
			"lang_id" => _LANG,
			"images" => $images,
			"images_variation" => $images_variation,
			"count_level" => $count_level,
			"number_elements" => $number_elements,
		);

		if (! $this->result = $this->diafan->_cache->get($cache_meta, "shop"))
		{
			$minus = array();
			$cat_ids = array();
			if(! $this->validate_attribute_site_cat('shop', $site_ids, $cat_ids, $minus))
			{
				return false;
			}
			$where = '';
			if($site_ids)
			{
				$where .= " AND site_id IN (".implode(",", $site_ids).")";
			}
			elseif(! empty($minus["site_ids"]))
			{
				$where .= " AND site_id NOT IN (".implode(",", $minus["site_ids"]).")";
			}
			$this->result["rows"] = DB::query_fetch_key_array("SELECT id, [name], [anons], site_id, parent_id FROM {shop_category} WHERE [act]='1' AND trash='0'"
			.$where." ORDER BY sort ASC", "parent_id");

			$ids = array();
			foreach ($this->result["rows"] as $parent_id => &$rows)
			{
				foreach ($rows as &$row)
				{
					$ids[] = $row["id"];
				}
			}

			if($number_elements && $ids)
			{
				$rng = DB::query_fetch_key_value("SELECT r.cat_id, COUNT(*) AS count FROM {shop_category_rel} AS r INNER JOIN {shop} AS s ON s.id = r.element_id WHERE s.[act]='1' AND s.trash='0' GROUP BY r.cat_id", "cat_id", "count");
				$this->number_elements($this->result["rows"], $rng);
			}

			if($count_level > 0)
			{
				$rs = array();
				$this->cut_tree($rs, $this->result["rows"], $count_level);
				$this->result["rows"] = $rs;
			}

			foreach ($this->result["rows"] as $parent_id => &$rows)
			{
				foreach ($rows as &$row)
				{
					if($images && $this->diafan->configmodules("images_cat", "shop", $row["site_id"]))
					{
						$this->diafan->_images->prepare($row["id"], 'shop', 'cat');
					}
					$this->diafan->_route->prepare($row["site_id"], $row["id"], "shop", "cat");
				}
			}
			foreach ($this->result["rows"] as $parent_id => &$rows)
			{
				foreach ($rows as &$row)
				{
					$row["link"] = $this->diafan->_route->link($row["site_id"], $row["id"], "shop", "cat");

					if($images && $this->diafan->configmodules("images_cat", "shop", $row["site_id"]))
					{
						$row["img"] = $this->diafan->_images->get(
							$images_variation, $row["id"], 'shop', 'cat',
							$this->diafan->_site->id, $row["name"], 0, $images, $row["link"]
						);
					}
				}
			}

			$this->diafan->_cache->save($this->result, $cache_meta, "shop");
		}
		foreach ($this->result["rows"] as $parent_id => &$rows)
		{
			foreach ($rows as &$row)
			{
				$this->prepare_data_category($row);
			}
		}
		foreach ($this->result["rows"] as $parent_id => &$rows)
		{
			foreach ($rows as &$row)
			{
				$this->format_data_category($row);
			}
		}
	}

	/**
	 * Обрезает дерево категорий по параметру count_level
	 *
	 * @param array $result результат
	 * @param array $rows все категории
	 * @param integer $count_level количество уровней
	 * @param integer $parent_id номер текущей категории-родителя
	 * @param integer $level уровень
	 * @return void
	 */
	private function cut_tree(&$result, $rows, $count_level, $parent_id = 0, $level = 1)
	{
		if (! empty($rows[$parent_id]))
		{
			foreach ($rows[$parent_id] as $row)
			{
				$result[$parent_id][] = $row;
				if($level < $count_level)
				{
					$this->cut_tree($result, $rows, $count_level, $row["id"], $level + 1);
				}
			}
		}
	}

	/**
	 * Подсчитывает количество элементов в категории
	 *
	 * @param array $rows все категории
	 * @param integer $count_level количество уровней
	 * @param integer $parent_id номер текущей категории-родителя
	 * @param integer $level уровень
	 * @return void
	 */
	private function number_elements(&$rows, $rng, $parent_id = 0)
	{
		$r = 0;
		if(empty($rows[$parent_id]))
		{
			return $r;
		}
		foreach ($rows[$parent_id] as &$row)
		{
			$row["number_elements"] = (! empty($rng[$row["id"]]) ? $rng[$row["id"]] : 0) + $this->number_elements($rows, $rng, $row["id"]);
			$r += $row["number_elements"];
		}
		return $r;
	}

	/**
	 * Генерирует данные для вывода товаров на странице тега
	 *
	 * @param array $element_ids номера товара
	 * @return array
	 */
	public function tags($element_ids)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		$this->result["rows"] = DB::query_fetch_all("SELECT e.id, e.[name], e.[anons], e.timeedit, e.site_id, e.brand_id, e.no_buy, e.article,"
		." e.[measure_unit], e.hit, e.new, e.action, e.is_file FROM {shop} AS e"
		." WHERE e.[act]='1' AND e.trash='0' AND e.id IN (%s)",
		implode(',', $element_ids));

		$this->elements($this->result["rows"], "list", "block");

		if ( ! $this->result["rows"])
		{
			return false;
		}

		foreach ($this->result["rows"] as &$row)
		{
			$this->prepare_data_element($row);
		}
		$this->result["hide_compare"] = true;
		$this->result();
		return $this->result;
	}

	/**
	 * Генерирует данные для поисковой выдачи
	 *
	 * @param array $element_ids номера товара
	 * @return array
	 */
	public function search($element_ids)
	{
		$rows = DB::query_fetch_key("SELECT e.id, e.[name], e.[anons], e.timeedit, e.site_id, e.brand_id, e.no_buy, e.article,"
		." e.[measure_unit], e.hit, e.new, e.action, e.is_file FROM {shop} AS e"
		. " WHERE e.[act]='1' AND e.trash='0' AND e.id IN (%s)", implode(',', $element_ids), "id");
		foreach($element_ids as $id)
		{
			$this->result["rows"][] = $rows[$id];
		}

		$this->elements($this->result["rows"], "list", "block");

		if ( ! $this->result["rows"])
		{
			return false;
		}

		foreach ($this->result["rows"] as &$row)
		{
			$this->prepare_data_element($row);
		}
		foreach ($this->result["rows"] as &$row)
		{
			$this->format_data_element($row);
		}
		$this->result["hide_compare"] = true;
		$this->result();
		return $this->result;
	}

	/**
	 * Генерирует контент для шаблонной функции: форма поиска по товарам
	 *
	 * @param array $site_ids страницы сайта
	 * @param array|string $cat_ids номера категорий
	 * @param string $ajax подгружать результаты поиска Ajax-запросом
	 * @return array
	 */
	public function show_search($site_ids, $cat_ids, $ajax)
	{
		//кеширование
		$cache_meta = array(
			"name" => "show_search",
			"lang_id" => _LANG,
			"cat_ids" => $cat_ids,
			"site_ids" => $site_ids,
			"access" => $this->diafan->configmodules('where_access_cat', 'shop') ? $this->diafan->_users->role_id : 0,
		);

		if (! $result = $this->diafan->_cache->get($cache_meta, "shop"))
		{
			if($cat_ids === 'all')
			{
				$cat_ids = array();
				$cat_ids_all = true;
			}
			$one_cat_id = count($cat_ids) == 1 && substr($cat_ids[0], 0, 1) !== '-' ? $cat_ids[0] : 0;
			$minus = array();
			if(! $this->validate_attribute_site_cat('shop', $site_ids, $cat_ids, $minus))
			{
				return false;
			}
			$result["cat_ids"] = array();
			if(count($cat_ids) > 1 || ! empty($cat_ids_all))
			{
				if(empty($cat_ids_all))
				{
					$cats = DB::query_fetch_all("SELECT id, [name], site_id, parent_id FROM {shop_category} WHERE id IN (%s) ORDER BY sort ASC", implode(',', $cat_ids));
				}
				else
				{
					$where = "";
					if($site_ids)
					{
						$where .= " AND c.site_id IN (".implode(',', $site_ids).")";
					}
					elseif(! empty($minus["site_ids"]))
					{
						$where .= " AND c.site_id NOT IN (".implode(',', $minus["site_ids"]).")";
					}
					if(! empty($minus["cat_ids"]))
					{
						$where .= " AND c.id NOT IN (".implode(",", $minus["cat_ids"]).")";
					}
					$cats = DB::query_fetch_all("SELECT c.id, c.[name], c.site_id, c.parent_id FROM {shop_category} AS c"
					.($this->diafan->configmodules('where_access_cat', 'shop') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='shop' AND a.element_type='cat'" : "")
					." WHERE c.[act]='1' AND c.trash='0'"
					.$where
					.($this->diafan->configmodules('where_access_cat', 'shop') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
					." GROUP BY c.id ORDER BY c.sort ASC");
				}
				foreach ($cats as &$cat)
				{
					$cat["level"] = 0;
					$cat_ids[] = $cat["id"];
					$parents[$cat["id"]] = $cat["parent_id"];
				}
				foreach ($cats as &$cat)
				{
					$parent = $cat["id"];
					$level = 0;
					while($parent)
					{
						if(! empty($parents[$parent]))
						{
							$parent = $parents[$parent];
							$level++;
						}
						else
						{
							$parent = 0;
						}
					}
					$cat["level"] = $level;
					$cats_h[$level ? $cat["parent_id"] : 0][] = $cat;
				}
				if($cats)
				{
					$this->list_cats_hierarhy($result["cat_ids"], $cats_h, ($one_cat_id ? $parents[$one_cat_id] : 0));
				}
			}
			elseif(count($cat_ids) == 1)
			{
				$result["cat_ids"][] = array("id" => $cat_ids[0]);
			}
			if(count($site_ids) > 1)
			{
				$result["site_ids"] = DB::query_fetch_all("SELECT id, [name] FROM {site} WHERE id IN (%s) ORDER BY sort ASC", implode(',', $site_ids));
				foreach ($result["site_ids"] as &$site)
				{
					$site["path"] = $this->diafan->_route->link($site["id"]);
				}
			}
			else
			{
				$result["site_ids"][] = array(
						"id" => $site_ids[0],
						"path" => $this->diafan->_route->link($site_ids[0])
					);
			}

			if ($this->diafan->configmodules("search_article", "shop", $site_ids[0]))
			{
				$result["article"] = array(
					"article" => 1,
					"value" => ''
				);
			}

			if ($this->diafan->configmodules("search_price", "shop", $site_ids[0]))
			{
				$result["price"] = array(
					"name" => 1,
					"value1" => 0,
					"value2" => 0
				);
			}

			if ($this->diafan->configmodules("search_brand", "shop", $site_ids[0]))
			{
				$result["brands"] = DB::query_fetch_all("SELECT b.id, b.[name], b.site_id FROM {shop_brand} AS b"
					.($cat_ids ? " INNER JOIN {shop_brand_category_rel} AS r ON r.element_id=b.id" : "")
					." WHERE b.[act]='1' AND b.trash='0' AND b.site_id IN (%s)"
					." AND (SELECT s.id FROM {shop} AS s ".($cat_ids ? " INNER JOIN {shop_category_rel} AS cs ON cs.element_id=s.id" : '')." WHERE s.brand_id=b.id AND s.[act]='1' AND s.trash='0' ".($cat_ids ? " AND cs.cat_id IN (%s)" : '')." LIMIT 1)"
					.($cat_ids ? " AND r.cat_id IN (0,%s) GROUP BY b.id" : "")
					." ORDER BY b.sort ASC",
					implode(',', $site_ids),
					implode(',', $cat_ids),
					implode(',', $cat_ids));
				$result["brand"] = array();
			}

			if ($this->diafan->configmodules("search_action", "shop", $site_ids[0]))
			{
				$result["action"] = array(
					"name" => 1,
					"value" => false
				);
			}

			if ($this->diafan->configmodules("search_hit", "shop", $site_ids[0]))
			{
				$result["hit"] = array(
					"name" => 1,
					"value" => false
				);
			}

			if ($this->diafan->configmodules("search_new", "shop", $site_ids[0]))
			{
				$result["new"] = array(
					"name" => 1,
					"value" => false
				);
			}

			$result["rows"] = DB::query_fetch_all("SELECT p.id, p.type, p.[name], p.[measure_unit], GROUP_CONCAT(c.cat_id SEPARATOR ',') as cat_ids FROM {shop_param} as p "
				." INNER JOIN {shop_param_category_rel} AS c ON p.id=c.element_id AND "
				.($cat_ids ? "(c.cat_id IN (".implode(',', $cat_ids).") OR c.cat_id=0)" : "c.cat_id=0")
				." WHERE p.search='1' AND p.trash='0'"
				.($site_ids ? " AND p.site_id IN (0, ".implode(",", $site_ids).")" : '')
				." GROUP BY p.id ORDER BY p.sort ASC");

			foreach ($result["rows"] as $i => &$row)
			{
				if(! isset($row["cat_ids"]))
				{
					$row["cat_ids"] = '';
				}
				if ($row["type"] == 'select' || $row["type"] == 'multiple')
				{
					$row["select_array"] = DB::query_fetch_key_value(
						"SELECT p.[name], p.id FROM {shop_param_select} AS p"
						// выводим значения, только есть товары, чтобы поиск не давал пустых результатов
						." INNER JOIN {shop_param_element} AS e ON p.param_id=e.param_id AND e.value".$this->diafan->_languages->site."=p.id"
						." INNER JOIN {shop} AS s ON e.element_id=s.id AND s.[act]='1' AND s.trash='0'"
						.($this->diafan->configmodules('hide_missing_goods') ? " AND s.no_buy='0'" : "")
						.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=s.id AND prh.count_goods>0" : "")
						.($cat_ids ? " INNER JOIN {shop_category_rel} AS c ON c.element_id=s.id AND c.cat_id IN (".implode(",", $cat_ids).")" : '')
						." WHERE p.param_id=%d GROUP BY p.id ORDER BY p.sort ASC", $row["id"], "id", "name");
					if(empty($row["select_array"]))
					{
						unset($result["rows"][$i]);
					}
				}
				elseif($row["type"] == 'checkbox')
				{
					$p_id = DB::query_result(
						"SELECT p.id FROM {shop_param} AS p"
						// выводим значения, только есть товары, чтобы поиск не давал пустых результатов
						." INNER JOIN {shop_param_element} AS e ON p.id=e.param_id"
						." INNER JOIN {shop} AS s ON e.element_id=s.id AND s.[act]='1' AND s.trash='0'"
						.($this->diafan->configmodules('hide_missing_goods') ? " AND s.no_buy='0'" : "")
						.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=s.id AND prh.count_goods>0" : "")
						.($cat_ids ? " INNER JOIN {shop_category_rel} AS c ON c.element_id=s.id AND c.cat_id IN (".implode(",", $cat_ids).")" : '')
						." WHERE p.id=%d LIMIT 1", $row["id"]);
					if(! $p_id)
					{
						unset($result["rows"][$i]);
					}
				}
			}
		}

		if (! empty($result["article"]))
		{
			$result["article"]["value"] = ! empty($_REQUEST["a"]) ? trim(htmlspecialchars(stripslashes($_REQUEST["a"]))) : '';
		}

		if (! empty($result["price"]))
		{
			$result["price"]["value1"] = $this->diafan->filter($_REQUEST, "int", "pr1");
			$result["price"]["value2"] = $this->diafan->filter($_REQUEST, "int", "pr2");
		}

		if (! empty($result["brands"]))
		{
			if(! empty($_REQUEST["brand"]))
			{
				if(is_array($_REQUEST["brand"]))
				{
					foreach($_REQUEST["brand"] as $b)
					{
						$b = $this->diafan->filter($b, "integer");
						if($b)
						{
							$result["brand"][] = $b;
						}
					}
				}
				else
				{
					$b = $this->diafan->filter($_REQUEST, "integer", "brand");
					if($b)
					{
						$result["brand"][] = $b;
					}
				}
			}
			elseif($this->diafan->_route->brand)
			{
				$result["brand"][] = $this->diafan->_route->brand;
			}
		}

		if (! empty($result["action"]) && ! empty($_REQUEST["ac"]))
		{
			$result["action"]["value"] = true;
		}

		if (! empty($result["hit"]) && ! empty($_REQUEST["hi"]))
		{
			$result["hit"]["value"] = true;
		}

		if (! empty($result["new"]) && ! empty($_REQUEST["ne"]))
		{
			$result["new"]["value"] = true;
		}

		foreach ($result["rows"] as &$row)
		{
			switch($row["type"])
			{
				case 'date':
				case 'datetime':
					$row["value1"] = $this->diafan->filter($_REQUEST, "string", "p".$row["id"]."_1");
					$row["value2"] = $this->diafan->filter($_REQUEST, "string", "p".$row["id"]."_2");
					break;

				case 'numtext':
					$row["value1"] = $this->diafan->filter($_REQUEST, "float", "p".$row["id"]."_1");
					$row["value2"] = $this->diafan->filter($_REQUEST, "float", "p".$row["id"]."_2");
					break;

				case 'checkbox':
					$row["value"] = $this->diafan->filter($_REQUEST, "int", "p".$row["id"]);
					break;

				case 'select':
				case 'multiple':
					$row["value"] = array();
					if ( ! empty($_REQUEST["p".$row["id"]]) && ! is_array($_REQUEST["p".$row["id"]]))
					{
						$row["value"][] = $this->diafan->filter($_REQUEST, "int", "p".$row["id"]);
					}
					elseif ( ! empty($_REQUEST["p".$row["id"]]) && is_array($_REQUEST["p".$row["id"]]))
					{
						foreach ($_REQUEST["p".$row["id"]] as $val)
						{
							$row["value"][] = intval($val);
						}
					}
					break;

				default:
					$row["value"] = array();
			}
		}

		if($this->diafan->_site->module == 'shop' && in_array($this->diafan->_site->id, $site_ids))
		{
			$result["site_id"] = $this->diafan->_site->id;
			foreach ($result["site_ids"] as &$row)
			{
				if($row["id"] == $this->diafan->_site->id)
				{
					$result["path"] = $row["path"];
				}
			}
		}
		else
		{
			$result["site_id"] = $result["site_ids"][0]["id"];
			$result["path"]    = $result["site_ids"][0]["path"];
		}
		if($this->diafan->_site->module == 'shop' && in_array($this->diafan->_route->cat, $cat_ids))
		{
			$result["cat_id"] = $this->diafan->_route->cat;
		}
		elseif(! empty($result["cat_ids"][0]["id"]) && count($result["cat_ids"]) == 1)
		{
			$result["cat_id"] = $result["cat_ids"][0]["id"];
		}
		else
		{
			$result["cat_id"] = 0;
		}

		$result["path"] = $this->diafan->_route->link($result["site_id"], $result["cat_id"], "shop", 'cat');
		if(! empty($this->sort_config['sort_directions'][$this->diafan->_route->sort]))
		{
			$search_param = $this->get_url_search_param();
			if(! empty($search_param)) $search_param='?action=search&'.$search_param;

			$result["path"] = $this->diafan->_route->current_link("", array('sort' => $this->diafan->_route->sort)).$search_param;
		}

		$result["send_ajax"] = $ajax;
		return $result;
	}

	/**
	 * Формирует дерево категорий для поиска
	 *
	 * @return void
	 */
	private function list_cats_hierarhy(&$result, $cats, $parent = 0)
	{
		if(empty($cats[$parent]))
			return;

		foreach ($cats[$parent] as $cat)
		{
			$result[] = $cat;
			$this->list_cats_hierarhy($result, $cats, $cat["id"]);
		}
	}

	/**
	 * Шаблонная функция: форма активации купона
	 *
	 * @return array
	 */
	public function show_add_coupon()
	{
		$result['form_tag'] = 'shop_add_coupon';
		$this->form_errors($result, $result['form_tag'], array(''));
		
		$result['coupon'] = false;

		$coupons = DB::query_fetch_all("SELECT c.id, c.coupon FROM {shop_discount} AS d"
			." INNER JOIN {shop_discount_person} AS p ON p.discount_id=d.id AND p.used='0'"
			." INNER JOIN {shop_discount_coupon} AS c ON c.discount_id=d.id AND c.id=p.coupon_id"
			." WHERE d.act='1' AND d.trash='0' AND (p.user_id>0 AND p.user_id=%d OR p.session_id='%s')"
			." AND (c.count_use=0 OR c.count_use>c.used) GROUP BY c.id",
			$this->diafan->_users->id, $this->diafan->_session->id);
		if($coupons)
		{
			$result["coupon"] = true;
			$result["coupons"] = array();
			foreach($coupons as $coupon)
			{
				$result["coupons"][] = $coupon["coupon"];
			}
		}
		return $result;
	}

	/**
	 * Форматирует данные о товаре для списка товаров
	 *
	 * @param array $rows все полученные из базы данных элементы
	 * @param string $function функция, для которой генерируется список товаров
	 * @param string $images_config настройки отображения изображений
	 * @return void
	 */
	public function elements(&$rows, $function = 'list', $images_config = '')
	{
		if (empty($this->result["timeedit"]))
		{
			$this->result["timeedit"] = '';
		}
		foreach ($rows as &$row)
		{
			$this->diafan->_shop->price_prepare_all($row["id"]);
		}
		foreach ($rows as &$row)
		{
			$this->price($row);
			if ($this->diafan->configmodules("images_element", "shop", $row["site_id"]))
			{
				if (is_array($images_config))
				{
					if($images_config["count"] > 0)
					{
						$this->diafan->_images->prepare($row["id"], "shop");
					}
				}
				elseif($this->diafan->configmodules("list_img_element", "shop", $row["site_id"]))
				{
					if($this->diafan->configmodules("list_img_element", "shop", $row["site_id"]) == 1)
					{
						$image_ids = array();
						foreach ($row["price_arr"] as $price)
						{
							if(! empty($price["image_rel"]))
							{
								$image_ids[] = $price["image_rel"];
							}
						}
						if(! $image_ids)
						{
							$this->diafan->_images->prepare($row["id"], "shop");
						}
					}
					else
					{
						$this->diafan->_images->prepare($row["id"], "shop");
					}
				}
			}
			if($row["brand_id"] && ! isset($this->cache["prepare_brand"][$row["brand_id"]]) && ! isset($this->cache["brand"][$row["brand_id"]]))
			{
				$this->cache["prepare_brand"][$row["brand_id"]] = true;
			}
			$this->diafan->_route->prepare($row["site_id"], $row["id"], "shop");

			$this->prepare_param($row["id"], $row["site_id"], $function);
			$ids[] = $row["id"];
		}
		if(isset($this->cache["prepare_brand"]))
		{
			$brands = DB::query_fetch_all("SELECT id, [name], site_id FROM {shop_brand} WHERE trash='0' AND [act]='1' AND id IN (%s)", implode(",", array_keys($this->cache["prepare_brand"])));
			foreach($brands as $b)
			{
				$this->diafan->_route->prepare($b["site_id"], $b["id"], "shop", "brand");
			}
			foreach($brands as $b)
			{
				$b["link"] = $this->diafan->_route->link($b["site_id"], $b["id"], "shop", "brand");
				$this->cache["brand"][$b["id"]] = $b;
			}
		}
		if($rows)
		{
			$additional_cost_rels = DB::query_fetch_key_array("SELECT a.id, a.[name], a.percent, a.price, a.amount, a.required, r.element_id, r.summ FROM {shop_additional_cost} AS a INNER JOIN {shop_additional_cost_rel} AS r ON r.additional_cost_id=a.id WHERE r.element_id IN (%s) AND a.trash='0'", implode(",", $ids), "element_id");
		}
		foreach ($rows as &$row)
		{
			if ( ! $this->diafan->configmodules("cat", "shop", $row["site_id"]))
			{
				$row["cat_id"] = 0;
			}
			if ($row["timeedit"] < $this->result["timeedit"])
			{
				$this->result["timeedit"] = $row["timeedit"];
			}
			unset($row["timeedit"]);

			if($row["brand_id"] && ! empty($this->cache["brand"][$row["brand_id"]]))
			{
				$row["brand"] = $this->cache["brand"][$row["brand_id"]];
			}
			else
			{
				$row["brand"] = false;
			}

			$row["additional_cost"] = array();
			if(! empty($additional_cost_rels[$row["id"]]))
			{
				foreach($additional_cost_rels[$row["id"]] AS $a_c_rel)
				{
					if($a_c_rel["percent"] || $a_c_rel["amount"])
					{
						foreach($row["price_arr"] as $price)
						{
							if($a_c_rel["amount"] && $price["price"] >= $a_c_rel["amount"])
							{
								$a_c_rel["price_summ"][$price["price_id"]] = 0;
							}
							elseif($a_c_rel["percent"])
							{
								$a_c_rel["price_summ"][$price["price_id"]] = ($price["price"] * $a_c_rel["percent"]) / 100;
							}
							else
							{
								$a_c_rel["price_summ"][$price["price_id"]] = $a_c_rel["price"];
							}
							$a_c_rel["format_price_summ"][$price["price_id"]] = $this->diafan->_shop->price_format($a_c_rel["price_summ"][$price["price_id"]]);
						}
					}
					else
					{
						if(! $a_c_rel["summ"])
						{
							$a_c_rel["summ"] = $a_c_rel["price"];
						}
						if($a_c_rel["summ"])
						{
							$a_c_rel["format_summ"] = $this->diafan->_shop->price_format($a_c_rel["summ"]);
						}
					}
					$row["additional_cost"][] = $a_c_rel;
				}
			}

			$row["link"] = $this->diafan->_route->link($row["site_id"], $row["id"], "shop");

			if ($this->diafan->configmodules("images_element", "shop", $row["site_id"]))
			{
				$count = 0;
				if (is_array($images_config))
				{
					$count = $images_config["count"];
					$link = $row["link"];
					if($images_config["count"] > 0)
					{
						$row["img"]  = $this->diafan->_images->get(
								$images_config["variation"], $row["id"], 'shop', 'element',
								$row["site_id"], $row["name"], 0,
								$images_config["count"],
								$row["link"]
							);
						$tag = $images_config["variation"];
					}
				}
				elseif($this->diafan->configmodules("list_img_element", "shop", $row["site_id"]))
				{
					$count = $this->diafan->configmodules("list_img_element", "shop", $row["site_id"]) == 1 ? 1 : 'all';
					$tag = 'medium';
					$link = ($count !== 'all' ? $row["link"] : 'large');
				}
				if($count && $count != 'all')
				{
					$image_ids = array();
					foreach ($row["price_arr"] as $price)
					{
						if(! empty($price["image_rel"]))
						{
							$image_ids[] = $price["image_rel"];
						}
					}
					if($image_ids)
					{
						$count = $image_ids;
					}
				}
				if($count)
				{
					$row["img"]  = $this->diafan->_images->get(
							$tag, $row["id"], 'shop', 'element',
							$row["site_id"], $row["name"], 0,
							$count == "all" ? 0 : $count,
							$link
						);
				}
			}

			$this->param($row, $function);
			$row["is_file"] = $this->diafan->configmodules("use_non_material_goods", "shop") ? $row["is_file"] : 0;
		}
		if(! isset($this->result["currency"]))
		{
			$this->result["currency"] = $this->diafan->configmodules("currency", "shop");
		}
	}

	/**
	 * Формирует данные о вложенных категориях
	 *
	 * @param integer $parent_id номер категории-родителя
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @return array
	 */
	private function get_children_category($parent_id, $time)
	{
		$children = DB::query_fetch_all(
		"SELECT c.id, c.[name], c.[anons], c.site_id FROM {shop_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='shop' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.parent_id=%d AND c.trash='0' AND c.site_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY c.id ORDER BY c.sort ASC, c.id ASC", $parent_id, $this->diafan->_site->id
		);

		foreach ($children as &$child)
		{
			if ($this->diafan->configmodules("images_cat") && $this->diafan->configmodules("list_img_cat"))
			{
				$this->diafan->_images->prepare($child["id"], 'shop', 'cat');
			}
			$this->diafan->_route->prepare($child["site_id"], $child["id"], "shop", "cat");
		}

		foreach ($children as &$child)
		{
			$child["link"] = $this->diafan->_route->link($child["site_id"], $child["id"], 'shop', 'cat');
			if ($this->diafan->configmodules("images_cat") && $this->diafan->configmodules("list_img_cat"))
			{
				$child["img"] = $this->diafan->_images->get(
					'medium', $child["id"], $this->diafan->_site->module, 'cat', $child["site_id"],
					$child["name"], 0, $this->diafan->configmodules("list_img_cat") == 1 ? 1 : 0,
					$child["link"]);
			}
			$child["rows"] = array();
			$chn = $this->diafan->get_children($child["id"], "shop_category");
			$chn[] = $child["id"];
			if ($this->diafan->configmodules("children_elements"))
			{
				$cat_ids = $chn;
			}
			else
			{
				$cat_ids = array($child["id"]);
			}
			if($this->diafan->configmodules("count_child_list"))
			{
				$child["rows"] = $this->get_children_category_elements_query($time, $cat_ids);
			}
			$child["count"] = $this->get_count_in_cat($chn, $time);
			unset($child["site_id"]);
		}
		return $children;
	}

	/**
	 * Получает из базы данных элементы вложенных категорий
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @param array $cat_ids номера категорий, элементы из которых выбираются
	 * @return array
	 */
	private function get_children_category_elements_query($time, $cat_ids)
	{
		switch($this->diafan->configmodules("sort"))
		{
			case 1:
				$order = 'e.id DESC';
				break;
			case 2:
				$order = 'e.id ASC';
				break;
			case 3:
				$order = 'e.name'._LANG.' ASC';
				break;
			default:
				$order = 'e.sort DESC';
		}
		$rows = DB::query_range_fetch_all(
		"SELECT e.id, e.[name], e.timeedit, e.[anons], e.site_id, e.brand_id, e.no_buy, e.article, e.hit, e.[measure_unit],"
		." e.new, e.action, e.is_file FROM {shop} AS e"
		." INNER JOIN {shop_category_rel} AS r ON e.id=r.element_id"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='shop' AND a.element_type='element'" : "")
		.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=e.id AND prh.count_goods>0" : "")
		." WHERE r.cat_id IN (%s) AND e.[act]='1' AND e.trash='0'"
		.($this->diafan->configmodules('where_period_element') ? " AND e.date_start<=".$time." AND (e.date_finish=0 OR e.date_finish>=".$time.")" : '')
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		.($this->diafan->configmodules('hide_missing_goods') ? " AND e.no_buy='0'" : "")
		." GROUP BY e.id ORDER BY ".$order,
		implode(',', $cat_ids),
		0, $this->diafan->configmodules("count_child_list")
		);
		$this->elements($rows);
		return $rows;
	}

	/**
	 * Считает количество товаров в категории
	 *
	 * @param array $cat_ids номер категории и всех вложенных в нее
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @return integer
	 */
	private function get_count_in_cat($cat_ids, $time)
	{
		return DB::query_result(
		"SELECT COUNT(DISTINCT e.id) FROM {shop} AS e"
		." INNER JOIN {shop_category_rel} AS r ON e.id=r.element_id"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='shop' AND a.element_type='element'" : "")
		.($this->diafan->configmodules('hide_missing_goods') && $this->diafan->configmodules('use_count_goods') ? " INNER JOIN {shop_price} AS prh ON prh.good_id=e.id AND prh.count_goods>0" : "")
		." WHERE r.cat_id IN (%s) AND e.[act]='1' AND e.trash='0'"
		.($this->diafan->configmodules('where_period_element') ? " AND e.date_start<=".$time." AND (e.date_finish=0 OR e.date_finish>=".$time.")" : '')
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		.($this->diafan->configmodules('hide_missing_goods') ? " AND e.no_buy='0'" : ""),
		implode(',', $cat_ids));
	}

	/**
	 * Формирует данные о цене товара
	 *
	 * @param array $row данные о товаре
	 * @return void
	 */
	private function price(&$row)
	{
		// массив всех характеристик, доступных к выбору при заказе
		if (! isset($this->result["depends_param"]))
		{
			$this->result["depends_param"] = DB::query_fetch_all("SELECT id, [name] FROM {shop_param} WHERE `type`='multiple' AND required='1' AND trash='0' ORDER BY sort ASC");
			foreach ($this->result["depends_param"] as &$row_param)
			{
				$row_param["values"] = DB::query_fetch_all("SELECT id, [name] FROM {shop_param_select} WHERE param_id=%d AND trash='0' ORDER BY sort ASC", $row_param["id"]);
			}
		}

		$price = array();
		$count = 0;
		$row["param_multiple"] = array();
		$rows = $this->diafan->_shop->price_get_all($row["id"], $this->diafan->_users->id);
		foreach ($rows as $row_price)
		{
			$empty_param = array();
			$row_price["param"] = array();
			$rows_param = DB::query_fetch_all("SELECT param_id, param_value FROM {shop_price_param} WHERE price_id=%d", $row_price["price_id"]);
			foreach ($rows_param as &$row_param)
			{
				if(! empty($row_param["param_value"]))
				{
					$row["param_multiple"][$row_param["param_id"]][$row_param["param_value"]] = 'depend';
					$row_price["param"][] = array("id" => $row_param["param_id"], "value" => $row_param["param_value"]);
				}
				else
				{
					$empty_param[] = $row_param["param_id"];
				}
			}
			if(! empty($empty_param))
			{
				$rows_param = DB::query_fetch_all("SELECT param_id, value".$this->diafan->_languages->site." AS value FROM {shop_param_element} WHERE element_id=%d AND param_id IN (%s)", $row["id"], implode(",", $empty_param));
				foreach ($rows_param as $row_param)
				{
					$row["param_multiple"][$row_param["param_id"]][$row_param["value"]] = 'select';
				}
			}
			$row_price["count_goods"] = $row_price["count_goods"] > 0 ? $row_price["count_goods"] : 0;
			$count += $row_price["count_goods"];
			$row_price["count"] = $this->diafan->configmodules("use_count_goods", "shop") ? ($row_price["count_goods"] > 0 ? $row_price["count_goods"] : 0 ) : true;
			$row_price["image_rel"] = DB::query_result("SELECT image_id FROM {shop_price_image_rel} WHERE price_id=%d LIMIT 1", $row_price["price_id"]);
			$price[] = $row_price;
		}
		$row["price_arr"] = $price;
		$row["count"] = $this->diafan->configmodules("use_count_goods", "shop") ? ($count > 0 ? $count : 0) : true;
		$row["price"] = ! empty($price[0]) ? $price[0]["price"] : 0;
		$row["old_price"] = ! empty($price[0]) ? $price[0]["old_price"] : 0;
		$row["discount"] = '';
		if(! empty($price[0]) && $price[0]["discount_id"])
		{
			if($price[0]["discount"])
			{
				$row["discount"] = $price[0]["discount"];
				$row["discount_currency"] = '%';
			}
			else
			{
				$row["discount"] = $this->diafan->_shop->price_format($price[0]["old_price"] - $price[0]["price"]);
				$row["discount_currency"] = $this->diafan->configmodules("currency", "shop");
			}
			$row["discount_finish"] = ! empty($price[0]["date_finish"]) ? $this->format_date($price[0]["date_finish"], "shop") : '';
		}
	}

	/**
	 * Возвращает результаты, сформированные в моделе
	 *
	 * @return void
	 */
	public function result()
	{
		$this->result["cart_link"] = $this->diafan->_route->module("cart");
		$this->result["wishlist_link"] = $this->diafan->_route->module("wishlist");
		$this->result["access_buy"] =  (! $this->diafan->configmodules('security_user', "shop") || $this->diafan->_users->id) ? false : true;
		if(! isset($this->result["hide_compare"]))
		{
			$this->result["hide_compare"] = $this->diafan->configmodules('hide_compare', "shop");
		}
		$this->result["buy_empty_price"] = $this->diafan->configmodules('buy_empty_price', "shop");
		if(! empty($this->result["depends_param"]))
		{
			foreach ($this->result["depends_param"] as &$param)
			{
				foreach ($param["values"] as &$value)
				{
					if(! empty($_REQUEST["p".$param["id"]]))
					{
						if(is_array($_REQUEST["p".$param["id"]]))
						{
							if(in_array($value["id"], $_REQUEST["p".$param["id"]]))
							{
								$value["selected"] =  true;
								break;
							}
						}
						else
						{
							if($_REQUEST["p".$param["id"]] == $value["id"])
							{
								$value["selected"] =  true;
								break;
							}
						}
					}
				}
			}
		}

		if($this->diafan->configmodules("one_click", "shop"))
		{
				Custom::inc('modules/cart/cart.model.php');
				$cart = new Cart_model($this->diafan);
				$this->result["one_click"] = $cart->one_click();
				$this->result["one_click"]["use"] = true;
		}
	}

	/**
	 * Задает выделение параметров, учитываемый при покупке товара
	 *
	 * @param array $row данные о товаре
	 * @return void
	 */
	private function select_price(&$row)
	{
		$row["wish"] = $this->diafan->_wishlist->get($row["id"], false, "count");

		$row["count_in_cart"] = $this->diafan->_cart->get_count_good($row["id"]);
		if(! $this->diafan->configmodules('buy_empty_price', "shop", $row["site_id"]))
		{
			$row["empty_price"] = true;
		}
		else
		{
			$row["empty_price"] = false;
		}
		if ( ! empty($row["price_arr"]))
		{
			$new_params = array();
			foreach ($row["price_arr"] as $id => $price)
			{
				if($row["price_arr"][$id]["price"])
				{
					$row["empty_price"] = false;
				}
				$row["price_arr"][$id]["price_no_format"] = $row["price_arr"][$id]["price"];
				$row["price_arr"][$id]["count_in_cart"] = $this->diafan->_cart->get_count_good($row["id"], $price["price_id"]);
				$row["price_arr"][$id]["price"] = $this->diafan->_shop->price_format($row["price_arr"][$id]["price"]);

				if(! empty($row["price_arr"][$id]["old_price"]))
				{
					$row["price_arr"][$id]["old_price"] = $this->diafan->_shop->price_format($row["price_arr"][$id]["old_price"]);
				}
			}
		}
		if(! empty($row["price"]))
		{
			$row["price"] = $this->diafan->_shop->price_format($row["price"]);
		}
		if(! empty($row["old_price"]))
		{
			$row["old_price"] = $this->diafan->_shop->price_format($row["old_price"]);
		}
	}

	/**
	 * Подготавливает дополнительные характеристики товара
	 *
	 * @param integer $id номер товара
	 * @param integer $site_id номер страницы, к которой прикреплен товар
	 * @param string $function функция, для которой выбираются параметры
	 * @return array
	 */
	private function prepare_param($id, $site_id, $function = "id")
	{
	}

	/**
	 * Получает дополнительные характеристики товара
	 *
	 * @param integer $id номер товара
	 * @param integer $site_id номер страницы, к которой прикреплен товар
	 * @param string $function функция, для которой выбираются параметры
	 * @return array
	 */
	private function param(&$good, $function = "id")
	{
		$values = DB::query_fetch_key_array("SELECT e.value".$this->diafan->_languages->site." as rvalue, e.[value], e.param_id, e.id FROM {shop_param_element} as e"
		. " LEFT JOIN {shop_param_select} as s ON e.param_id=s.param_id AND e.value".$this->diafan->_languages->site."=s.id"
		. " WHERE e.element_id=%d GROUP BY e.id ORDER BY s.sort ASC", $good["id"], "param_id");

		$rows = DB::query_fetch_all("SELECT p.id, p.[name], p.type, p.page, p.[measure_unit], p.config, p.[text], p.block, p.list, p.id_page FROM {shop_param} as p "
		. ($this->diafan->configmodules("cat", "shop", $good["site_id"]) ? " INNER JOIN {shop_category_rel} as c ON c.element_id=".$good["id"] : "")
		. " INNER JOIN {shop_param_category_rel} as cp ON cp.element_id=p.id "
		. ($this->diafan->configmodules("cat", "shop", $good["site_id"]) ?
				" AND (cp.cat_id=c.cat_id OR cp.cat_id=0) " : "")
		. " WHERE p.trash='0' "
		. " GROUP BY p.id ORDER BY p.sort ASC"
		);

		$good["param"] = array();
		$good["all_param"] = array();
		foreach ($rows as $row)
		{
			switch ($row["type"])
			{
				case "text":
				case "textarea":
				case "editor":
					if ( ! empty($values[$row["id"]][0]["value"]))
					{
						$row["value"] = $values[$row["id"]][0]["value"];
						$row["value_id"] = $values[$row["id"]][0]["id"];
					}
					break;

				case "date":
					if ( ! empty($values[$row["id"]][0]["rvalue"]))
					{
						$row["value"] = $this->diafan->formate_from_date($values[$row["id"]][0]["rvalue"]);
						$row["value_id"] = $values[$row["id"]][0]["id"];
					}
					break;

				case "datetime":
					if ( ! empty($values[$row["id"]][0]["rvalue"]))
					{
						$row["value"] = $this->diafan->formate_from_datetime($values[$row["id"]][0]["rvalue"]);
						$row["value_id"] = $values[$row["id"]][0]["id"];
					}
					break;

				case "select":
					$value = ! empty($values[$row["id"]][0]["rvalue"]) ? $values[$row["id"]][0]["rvalue"] : '';
					if ($value)
					{
						if (empty($this->cache["param_select"][$row["id"]][$value]))
						{
							$this->cache["param_select"][$row["id"]][$value] = DB::query_result("SELECT [name] FROM {shop_param_select} WHERE id=%d AND param_id=%d LIMIT 1", $values[$row["id"]][0]["rvalue"], $row["id"]);
						}
						if ($row["page"])
						{
							if (empty($this->cache["param_select_page"][$row["id"]][$value]))
							{
								$this->cache["param_select_page"][$row["id"]][$value] = $this->diafan->_route->link($good["site_id"], $value, "shop", 'param');
							}
							$row["link"] = $this->cache["param_select_page"][$row["id"]][$value];
						}
						$row["value"] = $this->cache["param_select"][$row["id"]][$value];
					}
					break;

				case "multiple":
					if ( ! empty($values[$row["id"]]))
					{
						$value = array();
						foreach ($values[$row["id"]] as $val)
						{
							if (empty($this->cache["param_select"][$row["id"]][$val["rvalue"]]))
							{
								$this->cache["param_select"][$row["id"]][$val["rvalue"]] =
										DB::query_result("SELECT [name] FROM {shop_param_select} WHERE id=%d AND param_id=%d LIMIT 1", $val["rvalue"], $row["id"]);
							}
							if ($row["page"])
							{
								if ($this->diafan->_site->module == 'shop' && $this->diafan->_route->param == $val["rvalue"])
								{
									$link = '';
								}
								else
								{
									if (empty($this->cache["param_select_page"][$row["id"]][$val["rvalue"]]))
									{
										$this->cache["param_select_page"][$row["id"]][$val["rvalue"]] = $this->diafan->_route->link($good["site_id"], $val["rvalue"], "shop", 'param');
									}
									$link = $this->cache["param_select_page"][$row["id"]][$val["rvalue"]];
								}
								$value[] = array("id" => $val["rvalue"], "name" => $this->cache["param_select"][$row["id"]][$val["rvalue"]], "link" => $link);
							}
							else
							{
								$value[] = $this->cache["param_select"][$row["id"]][$val["rvalue"]];
							}
						}
						$row["value"] = $value;
					}
					break;

				case "checkbox":
					$value = ! empty($values[$row["id"]][0]["rvalue"]) ? 1 : 0;
					if ( ! isset($this->cache["param_select"][$row["id"]][$value]))
					{
						$this->cache["param_select"][$row["id"]][$value] =
								DB::query_result("SELECT [name] FROM {shop_param_select} WHERE value=%d AND param_id=%d LIMIT 1", $value, $row["id"]);
					}
					if ( ! $this->cache["param_select"][$row["id"]][$value])
					{
						if($value == 1)
						{
							$row["value"] = '';
						}
					}
					else
					{
						$row["value"] = $this->cache["param_select"][$row["id"]][$value];
					}
					break;

				case "title":
					$row["value"] = '';
					break;

				case "images":
					$value = $this->diafan->_images->get('large', $good["id"], "shop", 'element', 0, '', $row["id"]);
					if(! $value)
						continue 2;

					$row["value"] = $value;
					break;

				case "attachments":
					$config = unserialize($row["config"]);
					if($config["attachments_access_admin"])
						continue 2;

					$value = $this->diafan->_attachments->get($good["id"], "shop", $row["id"]);
					if(! $value)
						continue 2;

					$row["value"] = $value;
					$row["use_animation"] = ! empty($config["use_animation"]) ? true : false;
					break;

				default:
					if ( ! empty($values[$row["id"]][0]["rvalue"]))
					{
						$row["value"] = $values[$row["id"]][0]["rvalue"];
						$row["value_id"] = $values[$row["id"]][0]["id"];
					}
					break;
			}
			if(isset($row["value"]))
			{
				$param = array(
					"id" => $row["id"],
					"name" => $row["name"],
					"value" => $row["value"],
					"value_id" => (! empty($row["value_id"]) ? $row["value_id"] : ''),
					"use_animation" => ! empty($row["use_animation"]) ? true : false,
					"text" => $row["text"],
					"type" => $row["type"],
					"measure_unit" => $row["measure_unit"],
					"link" => (! empty($row["link"]) ? $row["link"] : ''),
				);
				$good["all_param"][] = $param;
				switch($function)
				{
					case "block":
						if($row["block"])
						{
							$good["param"][] = $param;
						}
						break;

					case "list":
						if($row["list"])
						{
							$good["param"][] = $param;
						}
						break;

					case "id":
						if($row["id_page"])
						{
							$good["param"][] = $param;
						}
						break;
				}
			}
		}
	}

	/**
	 * Формирует SQL-запрос при поиске по товарам
	 *
	 * @return boolean true
	 */
	private function where(&$where, &$where_param, &$values, &$getnav, &$group)
	{
		$where = ' AND s.site_id=%d';
		$values[] = $this->diafan->_site->id;
		$values_param = array();

		$getnav = '?action=search';
		if(! empty($_REQUEST["cat_id"]))
		{
			$this->diafan->_route->cat = (int) preg_replace("/\D/", '', $_REQUEST['cat_id']);
			$catarr = array(0);
			$getnav .='&cat_id='.$this->diafan->_route->cat;
			if ($this->diafan->_route->cat)
			{
				$children = $this->diafan->get_children($this->diafan->_route->cat, "shop_category");
				$children[] = $this->diafan->_route->cat;
				$where_param .= " INNER JOIN {shop_category_rel} AS c ON s.id=c.element_id AND c.cat_id IN (".implode(',', $children).")";
			}
		}

		if(! empty($_REQUEST["a"]) && $this->diafan->configmodules("search_article"))
		{
			$where .= " AND LOWER(REPLACE(REPLACE(s.article, ' ', ''), '-', ''))='%h'";
			$_REQUEST["a"] = $this->diafan->filter($_REQUEST, "string", "a");
			$values[] = str_replace(array(' ', '-'), '', $_REQUEST["a"]);
			$getnav .= '&a='.$_REQUEST["a"];
		}

		if(! empty($_REQUEST["brand"]) && $this->diafan->configmodules("search_brand"))
		{
			$brand = array();
			if(is_array($_REQUEST["brand"]))
			{
				foreach($_REQUEST["brand"] as $b)
				{
					$b = $this->diafan->filter($b, "integer");
					if($b)
					{
						$brand[] = $b;
					}
				}
			}
			else
			{
				$b = $this->diafan->filter($_REQUEST, "integer", "brand");
				if($b)
				{
					$brand[] = $b;
				}
			}
			if($brand)
			{
				$where .= " AND s.brand_id".(count($brand) == 1 ? '='.$brand[0] : ' IN ('.implode(',', $brand).')');
				$getnav .= '&brand[]='.implode('&brand[]=', $brand);
			}
		}

		if(! empty($_REQUEST["ac"]) && $this->diafan->configmodules("search_action"))
		{
			$where .= " AND s.action='1'";
			$getnav .= '&ac=1';
		}

		if(! empty($_REQUEST["hi"]) && $this->diafan->configmodules("search_hit"))
		{
			$where .= " AND s.hit='1'";
			$getnav .= '&hi=1';
		}

		if(! empty($_REQUEST["ne"]) && $this->diafan->configmodules("search_new"))
		{
			$where .= " AND s.new='1'";
			$getnav .= '&ne=1';
		}

		if(! empty($_REQUEST["pr1"]) || ! empty($_REQUEST["pr2"]))
		{
			if(! empty($_REQUEST["pr1"]))
			{
				$pr1 = $this->diafan->filter($_REQUEST, "int", "pr1");
				$getnav .= '&pr1='.$pr1;
			}
			if(! empty($_REQUEST["pr2"]))
			{
				$pr2 = $this->diafan->filter($_REQUEST, "int", "pr2");
				$getnav .= '&pr2='.$pr2;
			}
			$where_param .= " INNER JOIN {shop_price} AS pr ON pr.good_id=s.id AND pr.trash='0'"
				." AND pr.date_start<=".time()." AND (pr.date_start=0 OR pr.date_finish>=".time().")"
				." AND pr.currency_id=0"
				." AND pr.role_id".($this->diafan->_users->role_id ? " IN (0,".$this->diafan->_users->role_id.")" : "=0")
				." AND (pr.person='0'".($this->person_discount_ids ? " OR pr.discount_id IN(".implode(",", $this->person_discount_ids).")" : "").")";
			$group = ", pr.price_id HAVING"
				.(! empty($_REQUEST["pr1"]) ? " MIN(ROUND(pr.price))>=".$pr1 : '')
				.(! empty($_REQUEST["pr2"]) ? (! empty($_REQUEST["pr1"]) ? " AND" : "")." MIN(pr.price)<=".$pr2 : '');
		}
		else
		{
			$where_param .= " LEFT JOIN {shop_price} AS pr ON pr.good_id=s.id AND pr.trash='0'"
				." AND pr.date_start<=".time()." AND (pr.date_start=0 OR pr.date_finish>=".time().")"
				." AND pr.currency_id=0"
				." AND pr.role_id".($this->diafan->_users->role_id ? " IN (0,".$this->diafan->_users->role_id.")" : "=0")
				." AND (pr.person='0'".($this->person_discount_ids ? " OR pr.discount_id IN(".implode(",", $this->person_discount_ids).")" : "").")";
		}
		$rows = DB::query_fetch_all("SELECT p.id, p.type, p.required FROM {shop_param} as p "
				." INNER JOIN {shop_param_category_rel} AS c ON p.id=c.element_id "
				.($this->diafan->configmodules("cat") ? " AND (c.cat_id=%d OR c.cat_id=0)" : "")
				." WHERE p.search='1' AND p.trash='0' GROUP BY p.id ORDER BY p.sort ASC", $this->diafan->_route->cat);
		foreach ($rows as $row)
		{
			if ($row["type"] == 'date' && (! empty($_REQUEST["p".$row["id"]."_1"]) || ! empty($_REQUEST["p".$row["id"]."_2"])))
			{
				$where_param .= " INNER JOIN {shop_param_element} AS pe".$row["id"]." ON pe".$row["id"].".element_id=s.id AND pe".$row["id"].".param_id='%d'"
					." AND pe".$row["id"].".trash='0'";
				$values_param[] = $row["id"];
				if (! empty($_REQUEST["p".$row["id"]."_1"]))
				{
					$where_param .= " AND pe".$row["id"].".value".$this->diafan->_languages->site.">='%s'";
					$values_param[] = $this->diafan->formate_in_date($_REQUEST["p".$row["id"]."_1"]);
					$getnav .= '&p'.$row["id"].'_1='.$this->diafan->filter($_REQUEST, "url", "p".$row["id"]."_1");
				}
				if (! empty($_REQUEST["p".$row["id"]."_2"]))
				{
					$where_param .= " AND pe".$row["id"].".value".$this->diafan->_languages->site."<='%s'";
					$values_param[] = $this->diafan->formate_in_date($_REQUEST["p".$row["id"]."_2"]);
					$getnav .= '&p'.$row["id"].'_2='.$this->diafan->filter($_REQUEST, "url", "p".$row["id"]."_2");
				}
			}
			elseif ($row["type"] == 'datetime' && (! empty($_REQUEST["p".$row["id"]."_1"]) || ! empty($_REQUEST["p".$row["id"]."_2"])))
			{
				$where_param .= " INNER JOIN {shop_param_element} AS pe".$row["id"]." ON pe".$row["id"].".element_id=s.id AND pe".$row["id"].".param_id='%d'"
					." AND pe".$row["id"].".trash='0'";
				$values_param[] = $row["id"];
				if(! empty($_REQUEST["p".$row["id"]."_1"]))
				{
					$where_param .= " AND pe".$row["id"].".value".$this->diafan->_languages->site.">='%s'";
					$values_param[] = $this->diafan->formate_in_datetime($_REQUEST["p".$row["id"]."_1"]);
					$getnav .= '&p'.$row["id"].'_1='.$this->diafan->filter($_REQUEST, "url", "p".$row["id"]."_1");
				}
				if(! empty($_REQUEST["p".$row["id"]."_2"]))
				{
					$where_param .= " AND pe".$row["id"].".value".$this->diafan->_languages->site."<='%s'";
					$values_param[] = $this->diafan->formate_in_datetime($_REQUEST["p".$row["id"]."_2"]);
					$getnav .= '&p'.$row["id"].'_2='.$this->diafan->filter($_REQUEST, "url", "p".$row["id"]."_2");
				}
			}
			elseif($row["type"] == 'numtext' && (! empty($_REQUEST["p".$row["id"]."_2"]) || ! empty($_REQUEST["p".$row["id"]."_1"])))
			{
				$val1 = $this->diafan->filter($_REQUEST, "float", "p".$row["id"]."_1");
				$val2 = $this->diafan->filter($_REQUEST, "float", "p".$row["id"]."_2");
				$where_param .= " INNER JOIN {shop_param_element} AS pe".$row["id"]." ON pe".$row["id"].".element_id=s.id AND pe".$row["id"].".param_id='%d'"
					." AND pe".$row["id"].".trash='0'"
					.($val1 ? " AND pe".$row["id"].".value".$this->diafan->_languages->site.">=%f" : '')
					.($val2 ? " AND pe".$row["id"].".value".$this->diafan->_languages->site."<=%f" : '')
				;
				$values_param[] = $row["id"];
				if ($val1)
				{
					$values_param[] = $val1;
					$getnav .= '&p'.$row["id"].'_1='.$val1;
				}
				if ($val2)
				{
					$values_param[] = $val2;
					$getnav .= '&p'.$row["id"].'_2='.$val2;
				}
			}
			elseif($row["type"] == 'checkbox' && ! empty($_REQUEST["p".$row["id"]]))
			{
				$where_param .= " INNER JOIN {shop_param_element} AS pe".$row["id"]." ON pe".$row["id"].".element_id=s.id AND pe".$row["id"].".param_id='%d'"
					." AND pe".$row["id"].".trash='0' AND pe".$row["id"].".value".$this->diafan->_languages->site."='1'";
				$values_param[] = $row["id"];
				$getnav .= '&p'.$row["id"].'=1';
			}
			elseif(($row["type"] == 'select' || $row["type"] == 'multiple') && ! empty($_REQUEST["p".$row["id"]]))
			{
				if (!is_array($_REQUEST["p".$row["id"]]))
				{
					$val = $this->diafan->filter($_REQUEST, "int", "p".$row["id"]);
					$getnav .= '&p'.$row["id"].'='.$val;
					$vals = array($val);
				}
				else
				{
					$vals = array();
					foreach ($_REQUEST["p".$row["id"]] as $val)
					{
						if ($val)
						{
							$val = intval($val);
							$vals[] = $val;
							$getnav .= '&p'.$row["id"].'[]='.$val;
						}
					}
				}
				if(! empty($vals))
				{
					if ($row["required"])
					{
						$where_param .= " INNER JOIN {shop_price_param} AS prp".$row["id"]." ON prp".$row["id"].".price_id=pr.price_id";
						$where .= " AND prp".$row["id"].".param_id=".$row["id"]." AND prp".$row["id"].".param_value IN (".implode(", ", $vals).",0) AND pe".$row["id"].".param_id=".$row["id"];
						if(empty($first_required_param))
						{
							$first_required_param = " AND prp".$row["id"].".price_id=";
						}
						else
						{
							$where .= $first_required_param."prp".$row["id"].".price_id";
						}
					}
					$where_param .= " ".($row["required"] ? "LEFT" : "INNER")." JOIN {shop_param_element} AS pe".$row["id"]." ON pe".$row["id"].".element_id=s.id AND pe".$row["id"].".param_id='%d'"
					." AND pe".$row["id"].".trash='0' AND pe".$row["id"].".value".$this->diafan->_languages->site." IN (".implode(", ", $vals).")";
					$values_param[] = $row["id"];
				}
			}
		}

		$values = array_merge($values_param, $values);
	}

	/**
	 * Генерирует данные для страницы сравнения товаров
	 *
	 * @return void
	 */
	public function compare()
	{
		if($this->diafan->configmodules("theme_list_compare"))
		{
			$this->result["theme"] = $this->diafan->configmodules("theme_list_compare");
		}
		if($this->diafan->configmodules("view_compare"))
		{
			$this->result["view"] = $this->diafan->configmodules("view_compare");
		}
		else
		{
			$this->result["view"] = 'compare';
		}
		$this->result["breadcrumb"] = $this->get_breadcrumb();
		$this->result["titlemodule"] = $this->diafan->_('Сравнение товаров', false);
		if (empty($_SESSION['shop_compare'][$this->diafan->_site->id]))
		{
			return;
		}

		$ids = array_keys($_SESSION['shop_compare'][$this->diafan->_site->id]);
		foreach ($ids as &$value)
		{
			$value = intval($value);
		}

		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		$this->result["rows"] = DB::query_fetch_all("SELECT id, [name], timeedit, site_id, brand_id, no_buy, article, [measure_unit], is_file
		FROM {shop} WHERE id IN (".implode(',', $ids).") AND site_id=%d AND
		[act]='1' AND trash='0'"
		.($this->diafan->configmodules('where_period_element') ? " AND date_start<=".$time." AND (date_finish=0 OR date_finish>=".$time.")" : ''),
		$this->diafan->_site->id);

		if ( ! $this->result["rows"])
		{
			return;
		}

		$this->elements($this->result["rows"], 'id');

		$param_ids = array();
		foreach ($this->result["rows"] as &$row)
		{
			$params = array();
			foreach ($row['param'] as $p)
			{
				if(! in_array($p["id"], $param_ids) && $p["value"])
				{
					$param_ids[] = $p['id'];
				}
				$params[$p["id"]] = $p;
			}
			$row['param'] = $params;
		}

		$this->result["existed_params"] = array();
		if($param_ids)
		{
			$this->result["existed_params"] = DB::query_fetch_all("SELECT id, [name] FROM {shop_param} WHERE trash='0' AND id IN(".implode(', ', $param_ids).") ORDER BY sort ASC");
		}

		if ($this->result["rows"])
		{
			foreach ($this->result["rows"] as &$row)
			{
				$this->select_price($row);
			}
		}

		$this->result["param_differences"] = $this->compare_get_param_difference($this->result["rows"], $this->result["existed_params"]);
	}

	/**
	 * Находит отличающиеся характеристики у сравниваемых товаров
	 *
	 * @param array $goods товары
	 * @param array $existed_params характеристики товаров
	 * @return array
	 */
	private function compare_get_param_difference(&$goods, &$existed_params)
	{
		if (empty($goods) || empty($existed_params))
		{
			return array();
		}
		$param_differences = array();
		$param_values = array();
		$existed_params_ids = array();

		foreach ($existed_params as $param)
		{
			$existed_params_ids[] = $param['id'];
		}

		foreach ($goods as $good)
		{
			$this->_compare_get_param_difference($good["param"], $param_differences, $param_values, $existed_params_ids);
		}

		return $param_differences;
	}

	/**
	 * Проходит по всем характеристикам товара и находит отличащиеся от общих значения
	 *
	 * @param array $params характеристики текущего товара
	 * @param array $param_differences отличающиеся характеристики, найденные до текущей итерации
	 * @param array $param_values значения характерстик, общие для всех товаров
	 * @param array $existed_params_ids все характеристики выбранных товаров
	 * @return void
	 */
	private function _compare_get_param_difference(&$params, &$param_differences, &$param_values, &$existed_params_ids)
	{
		$ids = array();

		foreach ($params as $param)
		{
			$ids[] = $param['id'];
			if (in_array($param['id'], $param_differences))
			{
				continue;
			}
			if (isset($param_values[$param['id']]) && $param_values[$param['id']] != $param["value"])
			{
				$param_differences[] = $param['id'];
				continue;
			}

			$param_values[$param['id']] = $param["value"];
		}

		foreach ($existed_params_ids as $id)
		{
			if (! in_array($id, $ids) && ! in_array($id, $param_differences))
			{
				$param_differences[] = $id;
			}
		}
	}

	/**
	 * Отдает купленный товар-файл
	 *
	 * @return void
	 */
	public function file_get()
	{
		$date = date('Y-m-d H:i');

		if ( ! $row = DB::query_fetch_array("SELECT a.id, a.name, a.extension, a.size,
			a.module_name
			FROM {shop_files_codes} as c INNER JOIN {attachments} AS a
			ON a.element_id=c.shop_id AND a.module_name='%s'
			WHERE code='%s' AND date_finish>='%s' LIMIT 1", $this->diafan->_site->module, $_REQUEST["code"], $date))
		{
			Custom::inc('includes/404.php');
		}

		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Cache-Control: max-age=86400');
		if ($row["extension"])
		{
			header("Content-type: ".$row["extension"]);
		}

		if ($row["size"] > 0)
		{
			header("Content-length: ".$row["size"]);
		}

		header("Content-Disposition: attachment; filename=".$row["name"]);
		header('Content-transfer-encoding: binary');
		header("Connection: close");

		$file_path = ABSOLUTE_PATH.USERFILES."/".$row["module_name"]."/files/".$row["id"];

		$handle = fopen($file_path, "r");

		echo fread($handle, $row["size"]);
		exit;
	}

	/**
	 * Позволяет добавлять характеристики товара для сортировки
	 *
	 * @return array
	 */
	private function expand_sort_with_params()
	{
		$sort_fields_names = array(1 => $this->diafan->_('Цена', false), 3 => $this->diafan->_('Наименование товара', false));

		$sort_directions = array(
			1 => 'MIN(pr.price) ASC',
			2 => 'MIN(pr.price) DESC',
			3 => 's.name'._LANG.' ASC',
			4 => 's.name'._LANG.' DESC'
		);

		$param_ids = array();

		$rows = DB::query_fetch_all("SELECT p.id, p.[name], p.type FROM {shop_param} AS p "
		. " INNER JOIN {shop_param_category_rel} AS cr ON cr.element_id=p.id AND cr.trash='0' "
		. ($this->diafan->_route->cat ? " AND (cr.cat_id=%d OR cr.cat_id=0)" : " AND cr.cat_id=0")
		. " WHERE p.trash='0' AND p.display_in_sort='1' AND p.type IN
		('text', 'numtext', 'date', 'datetime', 'checkbox') GROUP BY p.id ORDER BY p.sort", $this->diafan->_route->cat);

		foreach ($rows as $row)
		{
			switch($row["type"])
			{
				case 'text':
					$name = 'sp.[value]';
					break;
				case 'numtext':
					$name = 'CAST(sp.value'.$this->diafan->_languages->site.' AS DECIMAL(10, 2))';
					break;
				case 'date':
				case 'datetime':
				case 'checkbox':
					$name = 'sp.value'.$this->diafan->_languages->site;
					break;
			}
			$sort_directions[] = ' '.$name.' ASC ';
			$param_ids[count($sort_directions)] = $row['id'];
			$sort_fields_names[count($sort_directions)] = $row['name'];

			$sort_directions[] = ' '.$name.' DESC ';
			$param_ids[count($sort_directions)] = $row['id'];
		}

		$use_params_for_sort = $this->diafan->_route->sort > 4 ? true : false;

		return array('sort_fields_names' => $sort_fields_names, 'sort_directions' => $sort_directions,
			'param_ids' => $param_ids, 'use_params_for_sort' => $use_params_for_sort);
	}

	/**
	 * Формирует список характеристик, которые могут быть выбраны для сортировки
	 *
	 * @return array
	 */
	private function get_sort_links()
	{
		$result = array();

		$search_param = $this->get_url_search_param();
		if(! empty($search_param)) $search_param='?action=search&'.$search_param;

		foreach ($this->sort_config['sort_directions'] as $key => $value)
		{
			$result[$key] = $this->diafan->_route->sort != $key ? $this->diafan->_route->current_link("", array('sort' => $key)).$search_param : '';
		}

		return $result;
	}

	/**
	 * Получает параметры поиска из URL
	 *
	 * @return string
	 */
	private function get_url_search_param()
	{
		$param = array();
		if (!empty($_REQUEST['action']) && $_REQUEST['action'] == "search")
		{
			foreach ($_REQUEST as $k => $v)
			{
				switch ($k)
				{
					case 'rewrite':
					case 'action':
					case 'module_ajax':
						continue 2;

					default:
						if(is_array($v))
						{
							foreach ($v as $vv)
							{
								$vv = $this->diafan->filter($vv, "float");
								$param[] = $k.'[]='.$vv;
							}
							continue 2;
						}
						$v = $this->diafan->filter($v, "float");
				}
				$param[] = $k.'='.$v;
			}
			return implode('&', $param);
		}
		return '';
	}

	/**
	 * Подготовка к форматированию данных о категории для шаблона вне зоны кэша
	 *
	 * @param array $row category data
	 * @return void
	 */
	private function prepare_data_category(&$row)
	{
		$this->diafan->_rating->prepare($row["id"], 'shop', 'cat');
		if(! empty($row["children"]))
		{
			foreach ($row["children"] as &$ch)
			{
				$this->prepare_data_category($ch);
			}
		}
		if(! empty($row["rows"]))
		{
			foreach ($row["rows"] as &$ch)
			{
				$this->prepare_data_element($ch);
			}
		}
	}

	/**
	 * Форматирование данных о категории для шаблона вне зоны кэша
	 *
	 * @param array $row category data
	 * @return void
	 */
	private function format_data_category(&$row)
	{
		$row["name"] = $this->diafan->_useradmin->get($row["name"], 'name', $row["id"], 'shop_category', _LANG);
		if(! empty($row["anons"]))
		{
			$row["anons"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["anons"]), 'anons', $row["id"], 'shop_category', _LANG);
		}
		if(! empty($row["text"]))
		{
			$row["text"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["text"]), 'text', $row["id"], 'shop_category', _LANG);
		}
		$row["rating"] = $this->diafan->_rating->get($row["id"], 'shop', 'cat', (! empty($row["site_id"]) ? $row["site_id"] : 0));
		if(! empty($row["children"]))
		{
			foreach ($row["children"] as &$ch)
			{
				$this->format_data_category($ch);
			}
		}
		if(! empty($row["rows"]))
		{
			foreach ($row["rows"] as &$ch)
			{
				$this->format_data_element($ch);
			}
		}
	}

	/**
	 * Подготовка к форматированию данных о производителе для шаблона вне зоны кэша
	 *
	 * @param array $row brand data
	 * @return void
	 */
	private function prepare_data_brand(&$row)
	{
		if(! empty($row["rows"]))
		{
			foreach ($row["rows"] as &$ch)
			{
				$this->prepare_data_element($ch);
			}
		}
	}

	/**
	 * Форматирование данных о производителе для шаблона вне зоны кэша
	 *
	 * @param array $row brand data
	 * @return void
	 */
	private function format_data_brand(&$row)
	{
		$row["name"] = $this->diafan->_useradmin->get($row["name"], 'name', $row["id"], 'shop_brand', _LANG);
		if(! empty($row["text"]))
		{
			$row["text"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["text"]), 'text', $row["id"], 'shop_brand', _LANG);
		}
		if(! empty($row["rows"]))
		{
			foreach ($row["rows"] as &$ch)
			{
				$this->format_data_element($ch);
			}
		}
	}

	/**
	 * Подготовка к форматированию данных о товаре для шаблона вне зоны кэша
	 *
	 * @param array $row данные о товаре
	 * @return void
	 */
	private function prepare_data_element(&$row)
	{
		$this->diafan->_tags->prepare($row["id"], 'shop');
		$this->diafan->_rating->prepare($row["id"], 'shop');
		foreach($row["param"] as &$p)
		{
			if($p["type"] == "editor")
			{
				$p["value"] = $this->diafan->_tpl->htmleditor($p["value"]);
			}
			if($p["text"])
			{
				if(! isset($this->cache["param_text"][$p["id"]]))
				{
					$this->cache["param_text"][$p["id"]] = $this->diafan->_tpl->htmleditor($p["text"]);
				}
				$p["text"] = $this->cache["param_text"][$p["id"]];
			}
		}
	}

	/**
	 * Форматирование данных о товаре для шаблона вне зоны кэша
	 *
	 * @param array $row данные о товаре
	 * @param string $function функция, для которой генерируется список товаров
	 * @return void
	 */
	public function format_data_element(&$row, $function = 'list')
	{
		$this->select_price($row);

		if(! empty($row["price_arr"]))
		{
			foreach ($row["price_arr"] as $i => $price)
			{
				if ( ! empty($row['discount']))
				{
					$row["price_arr"][$i]["old_price"] = $this->diafan->_useradmin->get($row["price_arr"][$i]["old_price"], 'price', $row["price_arr"][$i]["price_id"], 'shop_price', '', 'text');
				}
				else
				{
					$row["price_arr"][$i]["price"] = $this->diafan->_useradmin->get($row["price_arr"][$i]["price"], 'price', $row["price_arr"][$i]["price_id"], 'shop_price', '', 'text');
				}
			}
		}
		elseif(! empty($row["price"]))
		{
			if ( ! empty($row['discount']))
			{
				$row["old_price"] = $this->diafan->_useradmin->get($row["old_price"], 'price', $row["price_id"], 'shop_price', '', 'text');
			}
			else
			{
				$row["price"] = $this->diafan->_useradmin->get($row["price"], 'price', $row["price_id"], 'shop_price', '', 'text');
			}
		}

		if ( ! empty($row["name"]))
		{
			$row["name"] = $this->diafan->_useradmin->get($row["name"], 'name', $row["id"], 'shop', _LANG);
		}
		if ( ! empty($row["article"]))
		{
			$row["article"] = $this->diafan->_useradmin->get($row["article"], 'article', $row["id"], 'shop', '', 'text');
		}
		if ( ! empty($row["text"]))
		{
			$row["text"] = $this->diafan->_useradmin->get($row["text"], 'text', $row["id"], 'shop', _LANG);
		}

		if ( ! empty($row["anons"]))
		{
			$row["anons"] = $this->diafan->_useradmin->get($row["anons"], 'anons', $row["id"], 'shop', _LANG);
		}

		if ( ! empty($row["param"]))
		{
			foreach ($row["param"] as $k => $param)
			{
				$row["param"][$k]["name"] = $this->diafan->_useradmin->get($param["name"], 'name', $param["id"], 'shop_param');
				if ( ! empty($param["value_id"]))
				{
					$lang = in_array($param["type"], array('text', 'textarea', 'editor')) ? _LANG : '';
					$row["param"][$k]["value"] = $this->diafan->_useradmin->get($param["value"], 'value', $param["value_id"], 'shop_param_element', $lang, $param["type"]);
				}
			}
		}
		//Представляет данные в разных форматах, удобных для использования в шаблоне
		foreach ($row["all_param"] as $param)
		{
			$row["ids_param"][$param["id"]] = $param;
			$row["names_param"][strip_tags($param["name"])] = $param;
		}

		$row["tags"] =  $this->diafan->_tags->get($row["id"], 'shop', 'element', (! empty($row["site_id"]) ? $row["site_id"] : 0));
		$row["rating"] = $this->diafan->_rating->get($row["id"], 'shop', 'element', (! empty($row["site_id"]) ? $row["site_id"] : 0), ($function == 'id' ? true : false));
	}
}
