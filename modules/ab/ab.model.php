<?php
/**
 * Модель
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
 * Ab_model
 */
class Ab_model extends Model
{
	/**
	 * @var string ссылка на страницу объявлений
	 */
	private $current_link;

	/**
	 * @var string ссылка на страницу, подготовленная для подстановки дополнительных параметров
	 */
	private $current_link_module;

	/**
	 * @var string хэш пользователя
	 */
	private $user_hash;

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		$this->diafan = &$diafan;

		$this->sort_config = $this->expand_sort_with_params();

		if ($this->diafan->_route->sort > count($this->sort_config['sort_directions']))
		{
			Custom::inc('includes/404.php');
		}
	}

	/**
	 * Генерирует данные для списка объявлений, если отключены категории
	 *
	 * @return void
	 */
	public function list_()
	{
		if ($this->diafan->_route->cat)
		{
			Custom::inc('includes/404.php');
		}
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		$cache_meta = array(
			"name" => "list",
			"lang_id" => _LANG,
			"page" => $this->diafan->_route->page > 1 ? $this->diafan->_route->page : 1,
			"site_id" => $this->diafan->_site->id,
			"sort" => $this->diafan->_route->sort,
			"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
			"time" => $time,
			"year" => $this->diafan->_route->year,
			"month" => $this->diafan->_route->month,
			"day" => $this->diafan->_route->day,
		);
		//кеширование
		if ( ! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{
			if ($this->diafan->_route->year || $this->diafan->_route->month || $this->diafan->_route->day)
			{
				if ($this->diafan->_route->cat)
				{
					Custom::inc('includes/404.php');
				}
				if (! $this->diafan->_route->year || ! $this->diafan->_route->month && $this->diafan->_route->day)
				{
					Custom::inc('includes/404.php');
				}
				if ($this->diafan->_route->month)
				{
					$month_arr = array(
						'12' => $this->diafan->_('Декабрь', false),
						'11' => $this->diafan->_('Ноябрь', false),
						'10' => $this->diafan->_('Октябрь', false),
						'09' => $this->diafan->_('Сентябрь', false),
						'08' => $this->diafan->_('Август', false),
						'07' => $this->diafan->_('Июль', false),
						'06' => $this->diafan->_('Июнь', false),
						'05' => $this->diafan->_('Май', false),
						'04' => $this->diafan->_('Апрель', false),
						'03' => $this->diafan->_('Март', false),
						'02' => $this->diafan->_('Февраль', false),
						'01' => $this->diafan->_('Январь', false)
					);
					if ($this->diafan->_route->day)
					{
						$this->result["titlemodule"] =
								sprintf(
								$this->diafan->_('Объявления за %s', false), $this->format_date(mktime(0, 0, 0, $this->diafan->_route->month, $this->diafan->_route->day, $this->diafan->_route->year))
						);
					}
					else
					{
						$this->result["titlemodule"] =
								sprintf(
								$this->diafan->_('Объявления за %s %s года', false), $month_arr[$this->diafan->_route->month], $this->diafan->_route->year
						);
					}
				}
				else
				{
					$this->result["titlemodule"] =
							sprintf(
							$this->diafan->_('Объявления за %s год', false), $this->diafan->_route->year
					);
				}

				$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));
				if($this->diafan->_route->day)
				{
					$time1 = mktime(0, 0, 0, $this->diafan->_route->month, $this->diafan->_route->day, $this->diafan->_route->year);
					$time2 = $time1 + 86400;
				}
				elseif($this->diafan->_route->month)
				{
					$time1 = mktime(0, 0, 0, $this->diafan->_route->month, 1, $this->diafan->_route->year);
					$time2 = mktime(0, 0, 0, $this->diafan->_route->month, date("t", $time1), $this->diafan->_route->year) + 86400;
				}
				else
				{
					$time1 = mktime(0, 0, 0, 1, 1, $this->diafan->_route->year);
					$time2 = mktime(0, 0, 0, 1, 1, $this->diafan->_route->year + 1);
				}
				$time2 = $time2 < $time ? $time2 : $time;

				////navigation///
				$this->diafan->_paginator->nen = $this->list_date_query_count($time, $time1, $time2);
				$this->result["paginator"] = $this->diafan->_paginator->get();
				////navigation///

				$this->result["rows"] = $this->list_date_query($time, $time1, $time2);
				$this->result["breadcrumb"] = $this->get_breadcrumb();
			}
			else
			{
				////navigation///
				$this->diafan->_paginator->nen = $this->list_query_count($time);
				$this->result["paginator"] = $this->diafan->_paginator->get();
				////navigation///

				$this->result["rows"] = $this->list_query($time);
			}

			$this->elements($this->result["rows"]);

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
		$this->theme_view();

		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);

		$this->result["link_sort"] = $this->get_sort_links($this->sort_config['sort_directions']);

		$this->result['sort_config'] = $this->sort_config;
	}

	/**
	 * Получает из базы данных общее количество элементов, если не используются категории
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @return integer
	 */
	private function list_query_count($time)
	{
		$count = DB::query_result(
			"SELECT COUNT(DISTINCT e.id) FROM {ab} AS e"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='ab' AND a.element_type='element'" : "")
			." WHERE e.[act]='1' AND e.trash='0'"
			." AND e.site_id=%d AND e.created<%d"
			." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''),
			$this->diafan->_site->id, $time, $time, $time
		);
		return $count;
	}

	/**
	 * Получает из базы данных элементы на одной странице, если не используются категории
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @return array
	 */
	private function list_query($time)
	{
		$rows = DB::query_range_fetch_all(
			"SELECT s.id, s.created, s.[name], s.[anons], s.timeedit, s.site_id, s.user_id, s.[act], s.date_start, s.date_finish FROM {ab} AS s"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='ab' AND a.element_type='element'" : "")
			. ($this->sort_config['use_params_for_sort'] ? " LEFT JOIN {ab_param_element} AS sp  ON sp.element_id=s.id AND sp.trash='0' AND sp.param_id=".$this->sort_config['param_ids'][$this->diafan->_route->sort] : '')
			." WHERE s.[act]='1' AND s.trash='0' AND s.site_id=%d AND s.created<%d"
			." AND s.date_start<=%d AND (s.date_finish=0 OR s.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." GROUP BY s.id ORDER BY "
			.($this->diafan->_route->sort ? $this->sort_config['sort_directions'][$this->diafan->_route->sort] : 's.prior DESC, s.created DESC'),
			$this->diafan->_site->id, $time, $time, $time,
			$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
		);
		return $rows;
	}

	/**
	 * Получает из базы данных общее количество элементов за определенный период, если не используются категории
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @param integer $date_start начало периода в формате UNIX
	 * @param integer $date_finish конец периода в формате UNIX
	 * @return integer
	 */
	private function list_date_query_count($time, $date_start, $date_finish)
	{
		$count = DB::query_result(
			"SELECT COUNT(DISTINCT e.id) FROM {ab} AS e"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='ab' AND a.element_type='element'" : "")
			." WHERE e.[act]='1' AND e.trash='0'"
			." AND e.site_id=%d AND e.created>=%d AND e.created<%d"
			." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''),
			$this->diafan->_site->id, $time1, $time2, $time, $time
		);
		return $count;
	}

	/**
	 * Получает из базы данных элементы на одной странице за определенный период, если не используются категории
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @param integer $date_start начало периода в формате UNIX
	 * @param integer $date_finish конец периода в формате UNIX
	 * @return array
	 */
	private function list_date_query($time, $date_start, $date_finish)
	{
		$rows = DB::query_range_fetch_all(
			"SELECT s.id, s.created, s.[name], s.[anons], s.timeedit, s.cat_id, s.site_id, s.user_id, s.[act], s.date_start, s.date_finish FROM {ab} AS s"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='ab' AND a.element_type='element'" : "")
			. ($this->sort_config['use_params_for_sort'] ? " LEFT JOIN {ab_param_element} AS sp  ON sp.element_id=s.id AND sp.trash='0' AND sp.param_id=".$this->sort_config['param_ids'][$this->diafan->_route->sort] : '')
			." WHERE s.[act]='1'"
			." AND s.trash='0' AND s.site_id=%d AND s.created>=%d AND s.created<%d"
			." AND s.date_start<=%d AND (s.date_finish=0 OR s.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." GROUP BY s.id ORDER BY "
			.($this->diafan->_route->sort ? $this->sort_config['sort_directions'][$this->diafan->_route->sort] : 's.prior DESC, s.created DESC'),
			$this->diafan->_site->id, $time1, $time2, $time, $time,
			$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
		);
		return $rows;
	}

	/**
	 * Генерирует данные для списка объявлений, найденных с помощью поиска
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

		$this->where($where, $where_param, $values, $getnav);

		$values[] = $time;
		$values[] = $time;

		////navigation//
		$this->diafan->_paginator->get_nav = $getnav;
		$this->diafan->_paginator->nen = $this->list_search_query_count($where_param, $where, $values);
		$this->result["paginator"] = $this->diafan->_paginator->get();
		////navigation///

		if($this->diafan->_route->cat)
		{
			$cat = DB::query_fetch_array("SELECT view_rows, view, theme FROM {ab_category} WHERE id=%d AND [act]='1' AND trash='0'", $this->diafan->_route->cat);
			if($cat)
			{
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
		$this->result["titlemodule"] = $this->diafan->_('Поиск по объявлениям', false);
		if ( ! $this->diafan->_paginator->nen)
		{
			$this->result["error"] = $this->diafan->_('Извините, ничего не найдено.', false);
			return;
		}

		$this->result["rows"] = $this->list_search_query($where_param, $where, $values);

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
	}

	/**
	 * Получает из базы данных общее количество найденных при помощи поиска элементов
	 *
	 * @param string $where_param
	 * @param string $where
	 * @param array $values
	 * @return integer
	 */
	private function list_search_query_count($where_param, $where, $values)
	{
		$count = DB::query_result("SELECT COUNT(DISTINCT s.id) FROM {ab} AS s"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='ab' AND a.element_type='element'" : "")
		.$where_param
		." WHERE s.[act]='1' AND s.trash='0'"
		.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		.$where
		." AND s.date_start<=%d AND (s.date_finish=0 OR s.date_finish>=%d)",
		$values);
		return $count;
	}

	/**
	 * Получает из базы данных найденных при помощи поиска элементы на одной странице
	 *
	 * @param string $where_param
	 * @param string $where
	 * @param array $values
	 * @return array
	 */
	private function list_search_query($where_param, $where, $values)
	{
		$rows = DB::query_range_fetch_all("SELECT DISTINCT s.id, s.[name], s.timeedit, s.[anons], s.site_id, s.created, s.user_id, s.[act], s.date_start, s.date_finish FROM {ab} AS s"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='ab' AND a.element_type='element'" : "")
		.($this->sort_config['use_params_for_sort'] ? " LEFT JOIN {ab_param_element} AS sp  ON sp.element_id=s.id AND sp.trash='0' AND sp.param_id=".$this->sort_config['param_ids'][$this->diafan->_route->sort] : '')
		.$where_param
		." WHERE s.[act]='1' AND s.trash='0'"
		.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		.$where
		." AND s.date_start<=%d AND (s.date_finish=0 OR s.date_finish>=%d)"
		." GROUP BY s.id"
		." ORDER BY ".($this->diafan->_route->sort ? $this->sort_config['sort_directions'][$this->diafan->_route->sort] : 's.prior DESC, s.created DESC'),
		$values, $this->diafan->_paginator->polog, $this->diafan->_paginator->nastr);
		return $rows;
	}

	/**
	 * Генерирует данные для списка объявлений, соответствующих значению доп. характеристики
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
			"time" => $time
		);
		//кеширование
		if ( ! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{
			$param_value = DB::query_fetch_array("SELECT param_id, [name] FROM {ab_param_select} WHERE id=%d AND trash='0' LIMIT 1", $this->diafan->_route->param);
			if ( ! $param_value = DB::query_fetch_array("SELECT param_id, [name] FROM {ab_param_select} WHERE id=%d AND trash='0' AND page='1' LIMIT 1", $this->diafan->_route->param))
			{
				Custom::inc('includes/404.php');
			}
			if ( ! $param = DB::query_fetch_array("SELECT [name] FROM {ab_param} WHERE id=%d LIMIT 1", $param_value["param_id"]))
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

		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);

		$this->result["link_sort"] = $this->get_sort_links($this->sort_config['sort_directions']);

		$this->result['sort_config'] = $this->sort_config;
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
		"SELECT COUNT(DISTINCT s.id) FROM {ab} AS s "
		. " INNER JOIN {ab_param_element} AS e ON e.element_id=s.id"
		. ($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='ab' AND a.element_type='element'" : "")
		. " WHERE s.[act]='1' AND s.trash='0' AND e.param_id=%d AND e.value".$this->diafan->_languages->site."=%d"
		." AND s.date_start<=%d AND (s.date_finish=0 OR s.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''),
		$param_value["param_id"], $this->diafan->_route->param, $time, $time);
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
		$rows = DB::query_range_fetch_all(
		"SELECT s.id, s.[name], s.timeedit, s.[anons], s.site_id, s.created, s.user_id, s.[act], s.date_start, s.date_finish FROM {ab} AS s"
		." INNER JOIN {ab_param_element} AS e ON e.element_id=s.id"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='ab' AND a.element_type='element'" : "")
		.($this->sort_config['use_params_for_sort'] ? " LEFT JOIN {ab_param_element} AS sp  ON sp.element_id=s.id AND sp.trash='0' AND sp.param_id=".$this->sort_config['param_ids'][$this->diafan->_route->sort] : '')
		." WHERE s.[act]='1' AND s.trash='0' AND e.param_id=%d AND e.value".$this->diafan->_languages->site."=%d"
		." AND s.date_start<=%d AND (s.date_finish=0 OR s.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY s.id"
		." ORDER BY ".($this->diafan->_route->sort ? $this->sort_config['sort_directions'][$this->diafan->_route->sort] : 's.prior DESC, s.created DESC'),
		$param_id, $this->diafan->_route->param, $time, $time,
		$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr);

		return $rows;
	}

	/**
	 * Генерирует данные для списка объявлений текущего пользователя
	 *
	 * @return void
	 */
	public function list_my()
	{
		if(! $this->diafan->_users->id)
		{
			Custom::inc('includes/404.php');
		}
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		////navigation//
		$this->diafan->_paginator->get_nav = '?action=my';
		$this->diafan->_paginator->nen = $this->list_my_query_count($time);
		$this->result["paginator"] = $this->diafan->_paginator->get();
		////navigation///

		$this->result["breadcrumb"] = $this->get_breadcrumb();

		$this->result["rows"] = $this->list_my_query($time);
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

		if($this->diafan->configmodules("theme_list_my"))
		{
			$this->result["theme"] = $this->diafan->configmodules("theme_list_my");
		}
		if($this->diafan->configmodules("view_list_my"))
		{
			$this->result["view"] = $this->diafan->configmodules("view_list_my");
		}
		else
		{
			$this->result["view"] = 'list';
		}
		if($this->diafan->configmodules("view_list_my_rows"))
		{
			$this->result["view_rows"] = $this->diafan->configmodules("view_list_my_rows");
		}
		else
		{
			$this->result["view_rows"] = 'rows';
		}

		$this->result["link_sort"] = $this->get_sort_links($this->sort_config['sort_directions']);

		$this->result['sort_config'] = $this->sort_config;
	}

	/**
	 * Получает из базы данных общее количество элементов текущего пользователя
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @return integer
	 */
	private function list_my_query_count($time)
	{
		$count = DB::query_result("SELECT COUNT(DISTINCT s.id) FROM {ab} AS s"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='ab' AND a.element_type='element'" : "")
		." WHERE s.trash='0'"
		.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." AND s.site_id=%d AND s.user_id=%d",
		$this->diafan->_site->id, $this->diafan->_users->id);
		return $count;
	}

	/**
	 * Получает из базы данных элементы текущего пользователяна одной странице
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @return array
	 */
	private function list_my_query($time)
	{
		$rows = DB::query_range_fetch_all("SELECT DISTINCT s.id, s.[name], s.timeedit, s.[anons], s.cat_id, s.site_id, s.created, s.user_id, s.[act], s.date_start, s.date_finish FROM {ab} AS s"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='ab' AND a.element_type='element'" : "")
		.($this->sort_config['use_params_for_sort'] ? " LEFT JOIN {ab_param_element} AS sp  ON sp.element_id=s.id AND sp.trash='0' AND sp.param_id=".$this->sort_config['param_ids'][$this->diafan->_route->sort] : '')
		." WHERE s.trash='0'"
		.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." AND s.site_id=%d AND s.user_id=%d"
		." GROUP BY s.id"
		." ORDER BY ".($this->diafan->_route->sort ? $this->sort_config['sort_directions'][$this->diafan->_route->sort] : 's.prior DESC, s.created DESC'),
		$this->diafan->_site->id, $this->diafan->_users->id,
		$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr);
		return $rows;
	}

	/**
	 * Генерирует данные для первой страницы модуля
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
				$this->diafan->_route->prepare($row["site_id"], $row["id"], "ab", "cat");
				if ($this->diafan->configmodules("images_cat") && $this->diafan->configmodules("list_img_cat"))
				{
					$this->diafan->_images->prepare($row["id"], 'ab', 'cat');
				}
			}
			foreach ($this->result["categories"] as &$row)
			{
				if (empty($this->result["timeedit"]) || $row["timeedit"] > $this->result["timeedit"])
				{
					$this->result["timeedit"] = $row["timeedit"];
				}

				$row["children"] = $this->get_children_category($row["id"], $time);

				$children = $this->diafan->get_children($row["id"], "ab_category");
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

				$row["link_all"] = $this->diafan->_route->link($row["site_id"], $row["id"], 'ab', 'cat');

				if ($this->diafan->configmodules("images_cat") && $this->diafan->configmodules("list_img_cat"))
				{
					$row["img"] =
					$this->diafan->_images->get(
						'medium', $row["id"], 'ab', 'cat',
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
		"SELECT COUNT(DISTINCT c.id) FROM {ab_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='ab' AND a.element_type='element'" : "")
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
		"SELECT c.id, c.[name], c.[anons], c.timeedit, c.site_id FROM {ab_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='ab' AND a.element_type='element'" : "")
		." WHERE c.[act]='1' AND c.parent_id=0 AND c.trash='0' AND c.site_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		. " GROUP BY c.id ORDER by c.sort ASC, c.id ASC", $this->diafan->_site->id,
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
		$rows = DB::query_range_fetch_all(
		"SELECT e.id, e.[name], e.timeedit, e.[anons], e.site_id, e.created, e.user_id, e.[act], e.date_start, e.date_finish FROM {ab} AS e"
		." INNER JOIN {ab_category_rel} AS r ON e.id=r.element_id"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='ab' AND a.element_type='element'" : "")
		." WHERE r.cat_id IN (%s) AND e.[act]='1' AND e.trash='0'"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY e.id ORDER BY e.prior DESC, e.created DESC",
		implode(',', $cat_ids), $time, $time,
		0, $this->diafan->configmodules("count_list")
		);
		return $rows;
	}

	/**
	 * Генерирует данные для списка объявлений в категории
	 *
	 * @return void
	 */
	public function list_category()
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name" => "list",
			"cat_id" => $this->diafan->_route->cat,
			"lang_id" => _LANG,
			"page" => $this->diafan->_route->page > 1 ? $this->diafan->_route->page : 1,
			"site_id" => $this->diafan->_site->id,
			"sort" => $this->diafan->_route->sort,
			"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
			"time" => $time
		);
		if ( ! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{
			$row = $this->list_category_query();

			if ( ! $row)
			{
				Custom::inc('includes/404.php');
			}
			if (empty($row) || (! empty($row['access']) && ! $this->access($row['id'], 'ab', 'cat')))
			{
				Custom::inc('includes/403.php');
			}

			$this->result = $row;

			$this->result["breadcrumb"] = $this->get_breadcrumb();

			if ($this->diafan->configmodules("images_cat"))
			{
				$this->diafan->_images->prepare($row["id"], 'ab', 'cat');
			}

			$this->result["children"] = $this->get_children_category($row["id"], $time);

			if ($this->diafan->configmodules("images_cat"))
			{
				$this->result["img"] = $this->diafan->_images->get(
					'medium', $row["id"], 'ab', 'cat',
					$this->diafan->_site->id, $row["name"], 0, 0, 'large'
				);
			}

			if ($this->diafan->configmodules("children_elements"))
			{
				$cat_ids = $this->diafan->get_children($this->diafan->_route->cat, "ab_category");
				$cat_ids[] = $this->diafan->_route->cat;
			}
			else
			{
				$cat_ids = array($this->diafan->_route->cat);
			}

			////navigation//
			$this->diafan->_paginator->nen = $this->list_category_elements_query_count($time, $cat_ids);
			$this->result["paginator"] = $this->diafan->_paginator->get();
			////navigation///

			$this->result["rows"] = $this->list_category_elements_query($time, $cat_ids);
			$this->elements($this->result["rows"]);

			$this->meta_cat($row);
			$this->theme_view_cat($row);

			$this->list_category_previous_next($row["sort"], $row["parent_id"]);

			if($row["act"])
			{
				//сохранение кеша
				$this->diafan->_cache->save($this->result, $cache_meta, $this->diafan->_site->module);
			}
		}
		$this->prepare_data_category($this->result);
		$this->format_data_category($this->result);

		if($this->result["anons_plus"])
		{
			$this->result["text"] = $this->result["anons"].$this->result["text"];
		}

		$this->result["comments"] = $this->diafan->_comments->get(0, '', 'cat');

		if ( ! empty($this->result["previous"]["text"]))
		{
			$this->result["previous"]["text"] =
					$this->diafan->_useradmin->get($this->result["previous"]["text"], 'name', $this->result["previous"]["id"], 'ab_category', _LANG);
		}
		if ( ! empty($this->result["next"]["text"]))
		{
			$this->result["next"]["text"] =
					$this->diafan->_useradmin->get($this->result["next"]["text"], 'name', $this->result["next"]["id"], 'ab_category', _LANG);
		}

		foreach ($this->result["breadcrumb"] as $k => &$b)
		{
			if ($k == 0)
				continue;

			$b["name"] = $this->diafan->_useradmin->get($b["name"], 'name', $b["id"], 'ab_category', _LANG);
		}

		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);

		$this->diafan->_keywords->get($this->result["text"]);

		$this->result["link_sort"] = $this->get_sort_links($this->sort_config['sort_directions']);

		$this->result['sort_config'] = $this->sort_config;
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
		$row = DB::query_fetch_array("SELECT id, [name], [anons], [anons_plus] ".$fields.", timeedit, [descr], [keywords], [canonical], sort, parent_id, [title_meta], access, theme, view, view_rows, [act], noindex FROM {ab_category}"
		." WHERE id=%d AND trash='0' AND site_id=%d"
		.(! $this->is_admin() ? " AND [act]='1'" : '')
		." ORDER BY sort ASC, id ASC", $this->diafan->_route->cat, $this->diafan->_site->id);
		return $row;
	}

	/**
	 * Получает из базы данных количество элементов в категории
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @param array $cat_ids номера категорий, элементы из которых выбираются
	 * @return integer
	 */
	private function list_category_elements_query_count($time, $cat_ids)
	{
		$count = DB::query_result(
		"SELECT COUNT(DISTINCT e.id) FROM {ab} AS e"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='ab' AND a.element_type='element'" : "")
		." INNER JOIN {ab_category_rel} AS r ON e.id=r.element_id"
		." WHERE e.[act]='1' AND e.trash='0'"
		." AND r.cat_id IN (%s)"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''), implode(',', $cat_ids), $time, $time
		);
		return $count;
	}

	/**
	 * Получает из базы данных элементы для списка элементов в категории
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @param array $cat_ids номера категорий, элементы из которых выбираются
	 * @return array
	 */
	private function list_category_elements_query($time, $cat_ids)
	{
		$rows = DB::query_range_fetch_all(
		"SELECT s.id, s.[name], s.timeedit, s.[anons], s.site_id, s.created, s.user_id, s.[act], s.date_start, s.date_finish FROM {ab} AS s"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='ab' AND a.element_type='element'" : "")
		.($this->sort_config['use_params_for_sort'] ? " LEFT JOIN {ab_param_element} AS sp  ON sp.element_id=s.id AND sp.trash='0' AND sp.param_id=".$this->sort_config['param_ids'][$this->diafan->_route->sort] : '')
		." INNER JOIN {ab_category_rel} AS r ON s.id=r.element_id AND r.cat_id IN (%s)"
		." WHERE s.[act]='1' AND s.trash='0'"
		." AND s.date_start<=%d AND (s.date_finish=0 OR s.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY s.id "
		." ORDER BY ".($this->diafan->_route->sort ? $this->sort_config['sort_directions'][$this->diafan->_route->sort] : 's.prior DESC, s.created DESC'),
		implode(',', $cat_ids), $time, $time,
		$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
		);
		return $rows;
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
		"SELECT c.[name], c.id FROM {ab_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='ab' AND a.element_type='element'" : "")
		." WHERE c.[act]='1' AND c.trash='0' AND c.site_id=%d"
		." AND (c.sort<%d OR c.sort=%d AND c.id<%d) AND c.parent_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." ORDER BY c.sort DESC, c.id DESC LIMIT 1", $this->diafan->_site->id, $sort, $sort, $this->diafan->_route->cat, $parent_id);
		if ($previous)
		{
			$this->result["previous"]["text"] = $previous["name"];
			$this->result["previous"]["id"]   = $previous["id"];
			$this->result["previous"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $previous["id"], "ab", 'cat');
		}
		$next = DB::query_fetch_array(
		"SELECT c.[name], c.id FROM {ab_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='ab' AND a.element_type='element'" : "")
		." WHERE c.[act]='1' AND c.trash='0' AND c.site_id=%d"
		." AND (c.sort>%d OR c.sort=%d AND c.id>%d) AND c.parent_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		. " ORDER BY c.sort ASC, c.id ASC LIMIT 1", $this->diafan->_site->id, $sort, $sort, $this->diafan->_route->cat, $parent_id);
		if ($next)
		{
			$this->result["next"]["text"] = $next["name"];
			$this->result["next"]["id"] = $next["id"];
			$this->result["next"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $next["id"], "ab", 'cat');
		}
	}

	/**
	 * Генерирует данные для страницы объявления
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
			$row["unblock"] = ! $row["act"];

			$this->result = $row;
			if(! $this->diafan->configmodules("cat"))
			{
				$this->result["cat_id"] = 0;
			}
			$this->diafan->_route->cat = $this->result["cat_id"];

			if ($this->diafan->configmodules("images_element"))
			{
				$this->result["img"] = $this->diafan->_images->get(
					'medium', $row["id"], 'ab', 'element',
					$this->diafan->_site->id, $row["name"], 0, 0, 'large'
				);
			}

			$this->meta($row);
			$this->theme_view_element($row);
			$this->result["date"] = $this->format_date($row['created']);
			$this->result["param"] = $this->get_param($row["id"], $this->diafan->_site->id);

			$this->id_previous_next($row["created"], $row["prior"], $time);

			$this->result["breadcrumb"] = $this->get_breadcrumb();

			if($row["act"])
			{
				//сохранение кеша
				$this->diafan->_cache->save($this->result, $cache_meta, $this->diafan->_site->module);
			}
		}
		$this->diafan->_route->cat = $this->result["cat_id"];

		$this->prepare_data_element($this->result);
		$this->format_data_element($this->result);

		if ( ! empty($this->result["previous"]["text"]))
		{
			$this->result["previous"]["text"] =
					$this->diafan->_useradmin->get($this->result["previous"]["text"], 'name', $this->result["previous"]["id"], 'ab', _LANG);
		}
		if ( ! empty($this->result["next"]["text"]))
		{
			$this->result["next"]["text"] =
					$this->diafan->_useradmin->get($this->result["next"]["text"], 'name', $this->result["next"]["id"], 'ab', _LANG);
		}
		foreach ($this->result["breadcrumb"] as $k => &$b)
		{
			if ($k == 0)
				continue;

			$b["name"] = $this->diafan->_useradmin->get($b["name"], 'name', $b["id"], 'ab_category', _LANG);
		}

		$this->counter_view();
		if($this->result["anons_plus"])
		{
			$this->result["text"] = $this->result["anons"].$this->result["text"];
			$this->result["anons"] = '';
		}
		$this->result["comments"] = $this->diafan->_comments->get();

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
		$row = DB::query_fetch_array("SELECT id, [name], [anons], [anons_plus], [text], timeedit, cat_id, [keywords],"
		." [descr], [canonical], [title_meta], access, theme, view, created, prior, user_id, [act], date_start, date_finish, noindex".$fields." FROM {ab}"
		." WHERE id=%d AND trash='0' AND site_id=%d"
		.(! $this->is_admin() ? " AND ([act]='1' AND date_start<=%d AND (date_finish=0 OR date_finish>=%d) OR user_id>0 AND user_id=%d)" : '')
		." LIMIT 1",
		$this->diafan->_route->show, $this->diafan->_site->id, $time, $time, $this->diafan->_users->id);
		return $row;
	}

	/**
	 * Формирует ссылки на предыдущий и следующий элемент
	 *
	 * @param integer $created время создания текущего элемента
	 * @param boolean $prior важно
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @return void
	 */
	private function id_previous_next($created, $prior, $time)
	{
		$previous = DB::query_fetch_array(
		"SELECT e.[name], e.id FROM {ab} AS e"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='ab' AND a.element_type='element'" : "")
		." WHERE e.[act]='1' AND e.trash='0' AND e.site_id=%d"
		.($this->diafan->configmodules("cat") ? " AND e.cat_id='".$this->diafan->_route->cat."'" : '')
		." AND (e.prior>'%d' OR e.prior='%d' AND (e.created>%d OR e.created=%d AND e.id>%d))"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." ORDER BY e.prior ASC, e.created ASC, e.id ASC LIMIT 1",
		$this->diafan->_site->id, $prior, $prior, $created, $created, $this->diafan->_route->show, $time, $time
		);
		$next = DB::query_fetch_array(
		"SELECT e.[name], e.id FROM {ab} AS e"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='ab' AND a.element_type='element'" : "")
		." WHERE e.[act]='1' AND e.trash='0' AND e.site_id=%d"
		.($this->diafan->configmodules("cat") ? " AND e.cat_id='".$this->diafan->_route->cat."'" : '')
		." AND (e.prior<'%d' OR e.prior='%d' AND (e.created<%d OR e.created=%d AND e.id<%d))"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." ORDER BY e.prior DESC, e.created DESC LIMIT 1",
		$this->diafan->_site->id, $prior, $prior, $created, $created, $this->diafan->_route->show, $time, $time
		);
		if ($previous)
		{
			$this->result["previous"]["text"] = $previous["name"];
			$this->result["previous"]["id"] = $previous["id"];
			$this->result["previous"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $previous["id"], "ab");
		}
		if ($next)
		{
			$this->result["next"]["text"] = $next["name"];
			$this->result["next"]["id"] = $next["id"];
			$this->result["next"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $next["id"], "ab");
		}
	}

	/**
	 * Генерирует данные для формы редактировани объявления
	 *
	 * @return void
	 */
	public function edit()
	{
		if (! $this->diafan->_users->id)
		{
			Custom::inc('includes/403.php');
		}
		$row = DB::query_fetch_array("SELECT id, [name], [anons], [text], cat_id, date_finish, user_id, [act], date_start, date_finish FROM {ab}"
		." WHERE id=%d AND trash='0' AND site_id=%d LIMIT 1",
		$this->diafan->_route->edit, $this->diafan->_site->id);

		if (empty($row))
		{
			Custom::inc('includes/404.php');
		}
		if ( $row["user_id"] != $this->diafan->_users->id)
		{
			Custom::inc('includes/403.php');
		}

		$this->result = $row;
		$this->result["text"] = strip_tags($this->result["text"]);
		$this->result["anons"] = strip_tags($this->result["anons"]);

		if ($this->diafan->configmodules("images_element"))
		{
			$this->result["images"][0] = $this->diafan->_images->get(
				'medium', $row["id"], 'ab', 'element',
				$this->diafan->_site->id, $row["name"], 0, 0, 'large'
			);
		}

		$this->result["titlemodule"] = $this->diafan->_('Редактирование объявления', false);

		$this->result["view"] = 'edit';

		$this->result["breadcrumb"] = array();
		$current_link = $this->diafan->_route->link($this->diafan->_site->id);
		if($this->diafan->_site->id != 1)
		{
			$this->result["breadcrumb"][] = array("link" => $current_link, "name" => $this->diafan->_site->name);
		}
		$this->result["breadcrumb"][] = array("link" => $current_link.'?action=my', "name" => $this->diafan->_('Мои объявления'));

		$cache_meta = array(
			"name" => "edit_form",
			"lang_id" => _LANG,
			"site_id" => $this->diafan->_site->id,
			"access" => ($this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0)
		);
		if (! $result_content = $this->diafan->_cache->get($cache_meta, "ab"))
		{
			$cats = DB::query_fetch_all(
			"SELECT c.id, c.[name], c.parent_id FROM {ab_category} AS c"
			.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='ab' AND a.element_type='cat'" : "")
			." WHERE c.[act]='1' AND c.trash='0' AND c.site_id=%d"
			.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." GROUP BY c.id ORDER by c.sort ASC, c.id ASC", $this->diafan->_site->id
			);
			$cat_ids = array();
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
			$result_content["cat_ids"] = array();
			if($cats)
			{
				$this->list_cats_hierarhy($result_content["cat_ids"], $cats_h);
			}

			$result_content["rows"] = DB::query_fetch_all("SELECT p.id, p.type, p.[name], GROUP_CONCAT(c.cat_id SEPARATOR ',') AS cat_ids, p.required, p.[text], p.config FROM {ab_param} AS p "
			." INNER JOIN {ab_param_category_rel} AS c ON p.id=c.element_id AND "
			.($cat_ids ? "(c.cat_id IN (".implode(',', $cat_ids).") OR c.cat_id=0)" : "c.cat_id=0")
			." WHERE p.trash='0' GROUP BY p.id ORDER BY p.sort ASC");

			foreach ($result_content["rows"] as &$row)
			{
				switch($row["type"])
				{
					case 'select':
					case 'multiple':
						$row["select_array"] = DB::query_fetch_all(
							"SELECT [name], id FROM {ab_param_select}"
							." WHERE param_id=%d ORDER BY sort ASC", $row["id"]);
						if(empty($row["select_array"]))
						{
							unset($row);
						}
						break;

					case 'attachments':
						$config = unserialize($row["config"]);
						$row["max_count_attachments"] = ! empty($config["max_count_attachments"]) ? $config["max_count_attachments"] : 0;
						$row["attachments_access_admin"] = ! empty($config["attachments_access_admin"]) ? $config["attachments_access_admin"] : 0;
						$row["attachment_extensions"] = ! empty($config["attachment_extensions"]) ? $config["attachment_extensions"] : '';
						$row["use_animation"] = ! empty($config["use_animation"]) ? true : false;
						break;
				}
				$result_content["param_types_array"][$row["id"]] = $row["type"];
			}
			$this->diafan->_cache->save($result_content, $cache_meta, "ab");
		}
		foreach ($result_content as $k => $v)
		{
			$this->result[$k] = $v;
		}

		$rows = DB::query_fetch_all("SELECT value".$this->diafan->_languages->site." AS bvalue, [value], param_id FROM {ab_param_element} WHERE trash='0' AND element_id=%d", $this->diafan->_route->edit);
		foreach ($rows as &$row)
		{
			if(empty($this->result["param_types_array"][$row["param_id"]]))
				continue;

			switch ($this->result["param_types_array"][$row["param_id"]])
			{
				case 'editor':
					$this->result["param"]['p'.$row["param_id"]] = strip_tags($row["value"]);
					break;

				case 'text':
				case 'textarea':
					$this->result["param"]['p'.$row["param_id"]] = $row["value"];
					break;
				case 'multiple':
					$this->result["param"]['p'.$row["param_id"]][] = $row["bvalue"];
					break;

				case 'date':
					$this->result["param"]['p'.$row["param_id"]] = ($row["bvalue"] ? $this->diafan->formate_from_date($row["bvalue"]) : '');
					break;

				case 'datetime':
					$this->result["param"]['p'.$row["param_id"]] = ($row["bvalue"] ? $this->diafan->formate_from_datetime($row["bvalue"]) : '');
					break;

				default:
					$this->result["param"]['p'.$row["param_id"]] = $row["bvalue"];
			}
		}
		$fields = array('', 'cat_id', 'name', 'anons', 'text', 'date_finish', 'images');
		$this->result['form_tag'] = 'ab_edit';
		foreach ($this->result["rows"] as &$row)
		{
			switch ($row["type"])
			{
				case 'attachments':
					if(! $row["attachments_access_admin"])
					{
						$this->result['attachments'][$row["id"]] = $this->diafan->_attachments->get($this->diafan->_route->edit, 'ab', $row["id"]);
					}
					break;

				case 'images':
					$this->result['images'][$row["id"]] = $this->diafan->_images->get('large', $this->diafan->_route->edit, 'ab', 'element', 0, '', $row["id"]);
					break;
			}
			$row["text"] = $this->diafan->_tpl->htmleditor($row["text"]);
			$fields[] = 'p'.$row["id"];
		}
		$this->form_errors($this->result, $this->result['form_tag'], $fields);

		$this->result["form_name"] = $this->diafan->configmodules('form_name');
		$this->result["form_anons"] = $this->diafan->configmodules('form_anons');
		$this->result["form_text"] = $this->diafan->configmodules('form_text');
		$this->result["form_date_finish"] = $this->diafan->configmodules('form_date_finish');
		$this->result["form_images"] = $this->diafan->configmodules("images_element") && $this->diafan->configmodules('form_images');
		$this->result["hash"] = $this->diafan->_users->get_hash();
	}

	/**
	 * Генерирует данные для шаблонной функции: блок объявлений
	 *
	 * @param integer $count количество объявлений
	 * @param array $site_ids страницы сайта
	 * @param array $cat_ids категории
	 * @param string $sort сортировка default - по дате, rand - случайно
	 * @param integer $images количество изображений
	 * @param string $images_variation размер изображений
	 * @param string $param дополнительные параметры
	 * @param string $tag тег
	 * @return array
	 */
	public function show_block($count, $site_ids, $cat_ids, $sort, $images, $images_variation, $param, $tag)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name" => "block",
			"cat_ids" => $cat_ids,
			"site_ids" => $site_ids,
			"count" => $count,
			"lang_id" => _LANG,
			"images" => $images,
			"images_variation" => $images_variation,
			"param" => $param,
			"sort" => $sort,
			"current"  => ($this->diafan->_site->module == 'ab' && $this->diafan->_route->show ? $this->diafan->_route->show : ''),
			"access" => ($this->diafan->configmodules('where_access_element', 'ab') || $this->diafan->configmodules('where_access_cat', 'ab') ? $this->diafan->_users->role_id : 0),
			"time" => $time,
			"tag" => $tag,
		);

		if ($sort == "rand" || ! $result = $this->diafan->_cache->get($cache_meta, "ab"))
		{
			$minus = array();
			$one_cat_id = count($cat_ids) == 1 && substr($cat_ids[0], 0, 1) !== '-' ? $cat_ids[0] : false;
			if(! $this->validate_attribute_site_cat('ab', $site_ids, $cat_ids, $minus))
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
			$inner = "";
			$where = '';
			$values = array();
			foreach ($params as $id => $value)
			{
				if (is_array($value))
				{
					$inner .= "
					INNER JOIN {ab_param_element} AS pe".$id." ON pe".$id.".element_id=e.id AND pe".$id.".param_id='".$id."'"
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
					INNER JOIN {ab_param_element} AS pe".$id." ON pe".$id.".element_id=e.id AND pe".$id.".param_id='".$id."'"
					. " AND pe".$id.".trash='0' AND pe".$id.".value".$this->diafan->_languages->site.$operators[$id]."'%h'";
					$values[] = $value;
				}
			}
			$values[] = $time;
			$values[] = $time;

			if($cat_ids)
			{
				$inner .= " INNER JOIN {ab_category_rel} AS r ON r.element_id=e.id"
				." AND r.cat_id IN (".implode(',', $cat_ids).")";
			}
			elseif(! empty($minus["cat_ids"]))
			{
				$inner .= " INNER JOIN {ab_category_rel} AS r ON r.element_id=e.id"
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
			if($tag)
			{
				$t = DB::query_fetch_array("SELECT id, [name] FROM {tags_name} WHERE [name]='%s' AND trash='0'", $tag);
				if(! $tag)
				{
					return false;
				}
				$inner .= " INNER JOIN {tags} AS t ON t.element_id=e.id AND t.element_type='element' AND t.module_name='ab' AND t.tags_name_id=".$t["id"];
			}

			if ($sort == "rand")
			{
				$max_count = DB::query_result("SELECT COUNT(DISTINCT e.id) FROM {ab} AS e"
				.$inner
				.($this->diafan->configmodules('where_access_element', 'ab') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='ab' AND a.element_type='element'" : "")
				." WHERE e.[act]='1' AND e.trash='0'"
				.$where
				.($this->diafan->_site->module == 'ab' && $this->diafan->_route->show ? " AND e.id<>".$this->diafan->_route->show : '')
				." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
				.($this->diafan->configmodules('where_access_element', 'ab') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''), $values
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

			switch($sort)
			{
				case 'rand':
					$order = '';
					break;

				default :
					$order = ' ORDER BY e.prior, e.created DESC';
			}
			$result["rows"] = array();

			foreach ($rands as $rand)
			{
				$rows = DB::query_fetch_all("SELECT e.id, e.[name], e.[anons], e.timeedit, e.site_id, e.created, e.user_id, e.[act], e.date_start, e.date_finish FROM {ab} AS e"
				.$inner
				.($this->diafan->configmodules('where_access_element', 'ab') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='ab' AND a.element_type='element'" : "")
				." WHERE e.[act]='1' AND e.trash='0'"
				.($this->diafan->_site->module == 'ab' && $this->diafan->_route->show ? " AND e.id<>".$this->diafan->_route->show : '')
				.$where
				." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
				.($this->diafan->configmodules('where_access_element', 'ab') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
				." GROUP BY e.id"
				.$order
				.' LIMIT '
				.($sort == "rand" ? $rand : 0).', '
				.($sort == "rand" ? 1 : $count), $values);
				$result["rows"] = array_merge($result["rows"], $rows);
			}
			$this->elements($result["rows"], 'block', array("count" => $images, "variation" => $images_variation));

			// если категория только одна, задаем ссылку на нее
			if (! empty($result["rows"]) && $one_cat_id)
			{
				$cat = DB::query_fetch_array("SELECT [name], site_id, id FROM {ab_category} WHERE id=%d LIMIT 1", $one_cat_id);

				$result["name"] = $cat["name"];
				$result["link_all"] = $this->diafan->_route->link($cat["site_id"], $cat["id"], 'ab', 'cat');
				$result["category"] = true;
			}
			// если раздел сайта только один, то задаем ссылку на него
			elseif(! empty($result["rows"]) && count($site_ids) == 1)
			{
				$result["name"] = DB::query_result("SELECT [name] FROM {site} WHERE id=%d LIMIT 1", $site_ids[0]);
				$result["link_all"] = $this->diafan->_route->link($site_ids[0]);
				$result["category"] = false;
			}
			if(! empty($result["rows"]) && $tag)
			{
				$result["name"] .= ': '.$t["name"];
			}

			//сохранение кеша
			if ($sort != "rand")
			{
				$this->diafan->_cache->save($result, $cache_meta, "ab");
			}
		}
		foreach ($result["rows"] as &$row)
		{
			$this->prepare_data_element($row);
		}
		foreach ($result["rows"] as &$row)
		{
			$this->format_data_element($row);
		}

		$result["view_rows"] = 'rows_block';

		return $result;
	}

	/**
	 * Генерирует данные для шаблонной функции: блок связанных объявлений
	 *
	 * @param integer $count количество объявлений
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
			"element_id" => $this->diafan->_route->show,
			"images" => $images,
			"images_variation" => $images_variation,
			"access" => ($this->diafan->configmodules('where_access_element', 'ab') || $this->diafan->configmodules('where_access_cat', 'ab') ? $this->diafan->_users->role_id : 0),
			"time" => $time
		);

		if (! $result = $this->diafan->_cache->get($cache_meta, "ab"))
		{
			$result["rows"] = DB::query_range_fetch_all(
			"SELECT e.id, e.[name], e.[anons], e.timeedit, e.site_id, e.created, e.user_id, e.[act], e.date_start, e.date_finish FROM {ab} AS e"
			." INNER JOIN {ab_rel} AS r ON e.id=r.rel_element_id AND r.element_id=%d"
			.($this->diafan->configmodules("rel_two_sided") ? " OR e.id=r.element_id AND r.rel_element_id=".$this->diafan->_route->show : '')
			.($this->diafan->configmodules('where_access_element', 'ab') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='ab' AND a.element_type='element'" : "")
			." WHERE e.[act]='1' AND e.trash='0'"
			." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element', 'ab') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." GROUP BY e.id"
			." ORDER BY e.prior DESC, e.created DESC",
			$this->diafan->_route->show, $time, $time, 0, $count
			);
			$this->elements($result["rows"], 'block', array("count" => $images, "variation" => $images_variation));
			$this->diafan->_cache->save($result, $cache_meta, "ab");
		}
		foreach ($result["rows"] as &$row)
		{
			$this->prepare_data_element($row);
		}
		foreach ($result["rows"] as &$row)
		{
			$this->format_data_element($row);
		}

		$result["view_rows"] = 'rows_block_rel';

		return $result;
	}


	/**
	 * Генерирует контент для шаблонной функции: форма поиска по объявлениям
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
			"access" => $this->diafan->configmodules('where_access_cat', 'ab') ? $this->diafan->_users->role_id : 0,
		);

		if (! $result = $this->diafan->_cache->get($cache_meta, "ab"))
		{
			if($cat_ids === 'all')
			{
				$cat_ids = array();
				$cat_ids_all = true;
			}
			$minus = array();
			if(! $this->validate_attribute_site_cat('ab', $site_ids, $cat_ids, $minus))
			{
				return false;
			}
			$result["cat_ids"] = array();
			if(count($cat_ids) > 1 || ! empty($cat_ids_all))
			{
				if(empty($cat_ids_all))
				{
					$cats = DB::query_fetch_all("SELECT id, [name], site_id, parent_id FROM {ab_category} WHERE id IN (%s) ORDER BY sort ASC", implode(',', $cat_ids));
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
					$cats = DB::query_fetch_all("SELECT c.id, c.[name], c.site_id, c.parent_id FROM {ab_category} AS c"
					.($this->diafan->configmodules('where_access_cat', 'ab') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='ab' AND a.element_type='cat'" : "")
					." WHERE c.[act]='1' AND c.trash='0'"
					.$where
					.($this->diafan->configmodules('where_access_cat', 'ab') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
					." GROUP BY c.id ORDER BY c.sort ASC");
				}
				$cat_ids = array();
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
				$result["cat_ids"] = array();
				if($cats)
				{
					$this->list_cats_hierarhy($result["cat_ids"], $cats_h);
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
				$result["site_ids"][] = array("path" => $this->diafan->_route->link($site_ids[0]), "id" => $site_ids[0]);
			}

			$result["rows"] = DB::query_fetch_all("SELECT p.id, p.type, p.[name], GROUP_CONCAT(c.cat_id SEPARATOR ',') AS cat_ids FROM {ab_param} AS p "
			." INNER JOIN {ab_param_category_rel} AS c ON p.id=c.element_id AND "
			.($cat_ids ? "(c.cat_id IN (".implode(',', $cat_ids).") OR c.cat_id=0)" : "c.cat_id=0")
			." WHERE p.search='1' AND p.trash='0'"
			.($site_ids ? " AND p.site_id IN (0, ".implode(",", $site_ids).")" : '')
			." GROUP BY p.id ORDER BY p.sort ASC");

			foreach ($result["rows"] as &$row)
			{
				if ($row["type"] == 'select' || $row["type"] == 'multiple')
				{
					$row["select_array"] = DB::query_fetch_key_value(
						"SELECT p.[name], p.id FROM {ab_param_select} AS p"
						// выводим значения только если есть объявления, чтобы поиск не давал пустых результатов
						." INNER JOIN {ab_param_element} AS e ON p.param_id=e.param_id AND e.value".$this->diafan->_languages->site."=p.id"
						." INNER JOIN {ab} AS s ON e.element_id=s.id AND s.[act]='1' AND s.trash='0'"
						.($cat_ids ? " INNER JOIN {ab_category_rel} AS c ON c.element_id=s.id AND c.cat_id IN (".implode(",", $cat_ids).")" : '')
						." WHERE p.param_id=%d GROUP BY p.id ORDER BY p.sort ASC", $row["id"], "id", "name");
					if(empty($row["select_array"]))
					{
						unset($row);
					}
				}
			}
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
					$row["value1"] = $this->diafan->filter($_REQUEST, "int", "p".$row["id"]."_1");
					$row["value2"] = $this->diafan->filter($_REQUEST, "int", "p".$row["id"]."_2");
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

		if($this->diafan->_site->module == 'ab' && in_array($this->diafan->_site->id, $site_ids))
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
		if($this->diafan->_site->module == 'ab' && in_array($this->diafan->_route->cat, $cat_ids))
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

		$result["path"] = $this->diafan->_route->link($result["site_id"], $result["cat_id"], "ab", 'cat');
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
	 * Генерирует данные для формы добавления объявления
	 *
	 * @param array|string $cat_ids номера категорий
	 * @param array $site_ids страницы сайта
	 * @param boolean $insert_form форма выводится с помощью шаблонного тега
	 * @return array|boolean false
	 */
	public function form($site_ids = array(), $cat_ids = array(), $insert_form = false)
	{
		$attr_cat_ids = $cat_ids;
		if (! $insert_form)
		{
			$site_ids = array($this->diafan->_site->id);
			$cat_ids = array($this->diafan->_route->cat);
		}
		//кеширование
		$cache_meta = array(
			"name" => "show_form",
			"lang_id" => _LANG,
			"cat_ids" => $cat_ids,
			"site_ids" => $site_ids,
			"access" => $this->diafan->configmodules('where_access_cat', 'ab') ? $this->diafan->_users->role_id : 0,
		);

		if ($this->diafan->configmodules('only_user', 'ab', $site_ids[0]) && ! $this->diafan->_users->id)
		{
			return false;
		}

		if (! $result = $this->diafan->_cache->get($cache_meta, "ab"))
		{
			$minus = array();
			if(! $this->validate_attribute_site_cat('ab', $site_ids, $cat_ids, $minus))
			{
				return false;
			}
			$result["cat_ids"] = array();
			if(count($cat_ids) == 1)
			{
				$result["cat_ids"][] = array("id" => $cat_ids[0]);
			}
			else
			{
				if(! empty($cat_ids))
				{
					if($attr_cat_ids)
					{
						$cats = DB::query_fetch_all("SELECT id, [name], site_id, parent_id FROM {ab_category} WHERE id IN (%s) ORDER BY sort ASC", implode(',', $cat_ids));
					}
					else
					{
						$cats = DB::query_fetch_all("SELECT id, [name], site_id, parent_id FROM {ab_category} WHERE site_id IN (%s) ORDER BY sort ASC", implode(',', $site_ids));
					}
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
					$cats = DB::query_fetch_all("SELECT c.id, c.[name], c.site_id, c.parent_id FROM {ab_category} AS c"
					.($this->diafan->configmodules('where_access_cat', 'ab') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='ab' AND a.element_type='cat'" : "")
					." WHERE c.[act]='1' AND c.trash='0'"
					.$where
					.($this->diafan->configmodules('where_access_cat', 'ab') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
					." GROUP BY c.id ORDER BY c.sort ASC");
				}
				$cat_ids = array();
				foreach ($cats as &$cat)
				{
					$cat["level"] = 0;
					$cat_ids[] = $cat["id"];
					$parents[$cat["id"]] = $cat["parent_id"];
				}
				foreach ($cats as $i => &$cat)
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
				$result["cat_ids"] = array();
				if($cats)
				{
					$this->list_cats_hierarhy($result["cat_ids"], $cats_h);
				}
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
				$result["site_ids"][] = array("id" => $site_ids[0], "path" => $this->diafan->_route->link($site_ids[0]));
			}

			$result["rows"] = DB::query_fetch_all("SELECT p.id, p.type, p.[name], GROUP_CONCAT(c.cat_id SEPARATOR ',') as cat_ids, p.required, p.[text], p.config FROM {ab_param} as p "
			." INNER JOIN {ab_param_category_rel} AS c ON p.id=c.element_id AND "
			.($cat_ids ? "(c.cat_id IN (".implode(',', $cat_ids).") OR c.cat_id=0)" : "c.cat_id=0")
			." WHERE p.trash='0'"
			.($site_ids ? " AND p.site_id IN (0, ".implode(",", $site_ids).")" : '')
			." GROUP BY p.id ORDER BY p.sort ASC");

			foreach ($result["rows"] as &$row)
			{
				if ($row["type"] == 'select' || $row["type"] == 'multiple')
				{
					$row["select_array"] = DB::query_fetch_all(
						"SELECT [name], id FROM {ab_param_select}"
						." WHERE param_id=%d ORDER BY sort ASC", $row["id"]);
					if(empty($row["select_array"]))
					{
						unset($row);
					}
				}
				if($row["type"] == 'attachments')
				{
					$config = unserialize($row["config"]);
					$row["max_count_attachments"] = ! empty($config["max_count_attachments"]) ? $config["max_count_attachments"] : 0;
					$row["attachments_access_admin"] = ! empty($config["attachments_access_admin"]) ? $config["attachments_access_admin"] : 0;
					$row["attachment_extensions"] = ! empty($config["attachment_extensions"]) ? $config["attachment_extensions"] : '';
					$row["use_animation"] = ! empty($config["use_animation"]) ? true : false;
				}
			}
		}
		$fields = array('', 'site_id', 'cat_id', 'name', 'anons', 'text', 'date_finish', 'images', 'captcha');
		$result['form_tag'] = 'ab'.md5(serialize(array($site_ids, $cat_ids, $insert_form)));
		foreach ($result["rows"] as &$row)
		{
			$fields[] = 'p'.$row["id"];
			$row["text"] = $this->diafan->_tpl->htmleditor($row["text"]);
		}
		$this->form_errors($result, $result['form_tag'], $fields);

		$result["captcha"] = '';
		if ($this->diafan->_captcha->configmodules("ab", $site_ids[0]))
		{
			$result["captcha"] = $this->diafan->_captcha->get($result['form_tag'], $result["error_captcha"]);
		}

		if($this->diafan->_site->module == 'ab' && in_array($this->diafan->_site->id, $site_ids))
		{
			$result["site_id"] = $this->diafan->_site->id;
		}
		else
		{
			$result["site_id"] = $result["site_ids"][0]["id"];
		}

		if($this->diafan->_site->module == 'ab' && in_array($this->diafan->_route->cat, $cat_ids))
		{
			$result["cat_id"] = $this->diafan->_route->cat;
		}
		elseif(! empty($result["cat_ids"][0]["id"]))
		{
			$result["cat_id"] = $result["cat_ids"][0]["id"];
		}
		else
		{
			$result["cat_id"] = 0;
		}
		$result["form_name"] = $this->diafan->configmodules('form_name', 'ab', $site_ids[0]);
		$result["form_anons"] = $this->diafan->configmodules('form_anons', 'ab', $site_ids[0]);
		$result["form_text"] = $this->diafan->configmodules('form_text', 'ab', $site_ids[0]);
		$result["form_date_finish"] = $this->diafan->configmodules('form_date_finish', 'ab', $site_ids[0]);
		$result["form_images"] = $this->diafan->configmodules('form_images', 'ab', $site_ids[0]);

		return $result;
	}

	/**
	 * Формирует дерево категорий для поиска или формы
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
	 * Форматирует данные об объявлении для списка объявлений
	 *
	 * @param array $rows все полученные из базы данных элементы
	 * @param string $function функция, для которой генерируется список объявлений
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
			if ($this->diafan->configmodules("images_element", "ab", $row["site_id"]))
			{
				if (is_array($images_config))
				{
					if($images_config["count"] > 0)
					{
						$this->diafan->_images->prepare($row["id"], "ab");
					}
				}
				elseif($this->diafan->configmodules("list_img_element", "ab", $row["site_id"]))
				{
					$this->diafan->_images->prepare($row["id"], "ab");
				}
			}
			$this->diafan->_route->prepare($row["site_id"], $row["id"], "ab");
		}
		foreach ($rows as &$row)
		{
			if ($row["timeedit"] < $this->result["timeedit"])
			{
				$this->result["timeedit"] = $row["timeedit"];
			}
			unset($row["timeedit"]);

			$row["link"] = $this->diafan->_route->link($row["site_id"], $row["id"], "ab");

			if ($this->diafan->configmodules("images_element", "ab", $row["site_id"]))
			{
				if (is_array($images_config))
				{
					if($images_config["count"] > 0)
					{
						$row["img"]  = $this->diafan->_images->get(
								$images_config["variation"], $row["id"], 'ab', 'element',
								$row["site_id"], $row["name"], 0,
								$images_config["count"],
								$row["link"]
							);
					}
				}
				elseif($this->diafan->configmodules("list_img_element", "ab", $row["site_id"]))
				{
					$count = $this->diafan->configmodules("list_img_element", "ab", $row["site_id"]) == 1 ? 1 : 0;
					$row["img"]  = $this->diafan->_images->get(
							'medium', $row["id"], 'ab', 'element',
							$row["site_id"], $row["name"], 0,
							$count,
							($count ? $row["link"] : 'large')
						);
				}
			}

			$row["param"] = $this->get_param($row["id"], $row["site_id"], $function);
			$row["date"] = $this->format_date($row['created'], "ab", $row["site_id"]);
			unset($row["created"]);
			$row["unblock"] = ! $row["act"];
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
		"SELECT c.id, c.[name], c.[anons], c.site_id FROM {ab_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='ab' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.parent_id=%d AND c.trash='0' AND c.site_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY c.id ORDER BY c.sort ASC, c.id ASC", $parent_id, $this->diafan->_site->id
		);

		foreach ($children as &$child)
		{
			if ($this->diafan->configmodules("images_cat") && $this->diafan->configmodules("list_img_cat"))
			{
				$this->diafan->_images->prepare($child["id"], 'ab', 'cat');
			}
			$this->diafan->_route->prepare($child["site_id"], $child["id"], "ab", "cat");
		}
		foreach ($children as &$child)
		{
			$child["link"] = $this->diafan->_route->link($child["site_id"], $child["id"], 'ab', 'cat');
			if ($this->diafan->configmodules("images_cat") && $this->diafan->configmodules("list_img_cat"))
			{
				$child["img"] = $this->diafan->_images->get(
					'medium', $child["id"], 'ab', 'cat', $child["site_id"],
					$child["name"], 0, $this->diafan->configmodules("list_img_cat") == 1 ? 1 : 0,
					$child["link"]);
			}
			$child["rows"] = array();
			$chn = $this->diafan->get_children($child["id"], "ab_category");
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
		$rows = DB::query_range_fetch_all(
		"SELECT e.id, e.[name], e.timeedit, e.[anons], e.site_id, e.created, e.user_id, e.[act], e.date_start, e.date_finish FROM {ab} AS e"
		." INNER JOIN {ab_category_rel} AS r ON e.id=r.element_id"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='ab' AND a.element_type='element'" : "")
		." WHERE r.cat_id IN (%s) AND e.[act]='1' AND e.trash='0'"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		. " GROUP BY e.id ORDER BY e.prior DESC, e.created DESC",
		implode(',', $cat_ids), $time, $time,
		0, $this->diafan->configmodules("count_child_list")
		);
		$this->elements($rows);
		return $rows;
	}

	/**
	 * Считает количество объявлений в категории
	 *
	 * @param array $cat_ids номер категории и всех вложенных в нее
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @return integer
	 */
	private function get_count_in_cat($cat_ids, $time)
	{
		return DB::query_result(
		"SELECT COUNT(DISTINCT e.id) FROM {ab} AS e"
		." INNER JOIN {ab_category_rel} AS r ON e.id=r.element_id"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='ab' AND a.element_type='element'" : "")
		." WHERE r.cat_id IN (%s) AND e.[act]='1' AND e.trash='0'"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''),
		implode(',', $cat_ids), $time, $time);
	}

	/**
	 * Получает дополнительные характеристики объявлений
	 *
	 * @param integer $id номер объявления
	 * @param integer $site_id номер страницы, к которой прикреплено объявление
	 * @param string $function функция, для которой выбираются параметры
	 * @return array
	 */
	private function get_param($id, $site_id, $function = "id")
	{
		$values = DB::query_fetch_key_array("SELECT e.value".$this->diafan->_languages->site." as rvalue, e.[value], e.param_id, e.id FROM {ab_param_element} as e"
		." LEFT JOIN {ab_param_select} as s ON e.param_id=s.param_id AND e.value".$this->diafan->_languages->site."=s.id"
		." WHERE e.element_id=%d GROUP BY e.id ORDER BY s.sort ASC", $id, "param_id");

		$rows = DB::query_fetch_all("SELECT p.id, p.[name], p.type, p.page, p.[measure_unit], p.config, p.[text] FROM {ab_param} as p "
		.($this->diafan->configmodules("cat", "ab", $site_id) ? " INNER JOIN {ab_category_rel} as c ON c.element_id=".$id : "")
		." INNER JOIN {ab_param_category_rel} as cp ON cp.element_id=p.id "
		.($this->diafan->configmodules("cat", "ab", $site_id) ? " AND (cp.cat_id=c.cat_id OR cp.cat_id=0) " : "")
		." WHERE p.trash='0' "
		.($function == "block" ? " AND p.block='1'" : '')
		.($function == "list" ? " AND p.list='1'" : '')
		.($function == "id" ? " AND p.id_page='1'" : '')
		." GROUP BY p.id ORDER BY p.sort ASC"
		);

		$param = array();
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
							$this->cache["param_select"][$row["id"]][$value] = DB::query_result("SELECT [name] FROM {ab_param_select} WHERE id=%d AND param_id=%d LIMIT 1", $values[$row["id"]][0]["rvalue"], $row["id"]);
						}
						if ($row["page"])
						{
							if (empty($this->cache["param_select_page"][$row["id"]][$value]))
							{
								$this->cache["param_select_page"][$row["id"]][$value] = $this->diafan->_route->link($site_id, $value, "ab", 'param');
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
										DB::query_result("SELECT [name] FROM {ab_param_select} WHERE id=%d AND param_id=%d LIMIT 1", $val["rvalue"], $row["id"]);
							}
							if ($row["page"])
							{
								if ($this->diafan->_site->module == 'ab' && $this->diafan->_route->param == $val["rvalue"])
								{
									$link = '';
								}
								else
								{
									if (empty($this->cache["param_select_page"][$row["id"]][$val["rvalue"]]))
									{
										$this->cache["param_select_page"][$row["id"]][$val["rvalue"]] = $this->diafan->_route->link($site_id, $val["rvalue"], "ab", 'param');
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
								DB::query_result("SELECT [name] FROM {ab_param_select} WHERE value=%d AND param_id=%d LIMIT 1", $value, $row["id"]);
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
					$value = $this->diafan->_images->get('large', $id, "ab", 'element', 0, '', $row["id"]);
					if(! $value)
						continue 2;

					$row["value"] = $value;
					break;

				case "attachments":
					$config = unserialize($row["config"]);
					if($config["attachments_access_admin"])
						continue 2;

					$value = $this->diafan->_attachments->get($id, "ab", $row["id"]);
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
				$param[] = array(
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
			}
		}
		return $param;
	}

	/**
	 * Получает дополнительные характеристики объявлений для формы добавления
	 *
	 * @param integer $site_id страница сайта
	 * @param integer $cat_id категория
	 * @param boolean $use_cat категории используются
	 * @return array
	 */
	public function get_param_form($site_id, $cat_id, $use_cat)
	{
		$cache_meta = array(
			"name" => "ab_param",
			"lang_id" => _LANG,
			"cat_id" => $cat_id,
			"site_id" => $site_id
		);
		if (! $rows = $this->diafan->_cache->get($cache_meta, 'ab'))
		{
			if(! $use_cat)
			{
				$where = ' AND r.cat_id = 0';
			}
			elseif($cat_id)
			{
				$where = " AND (r.cat_id=%d OR r.cat_id=0)";
			}
			else
			{
				$where = " INNER JOIN {ab_category} AS c ON c.site_id=%d AND (c.id=r.cat_id OR r.cat_id=0)";
			}
			$rows = DB::query_fetch_all(
					"SELECT p.id, p.[name], p.type, p.required, p.[text], p.config,"
					." GROUP_CONCAT(r.cat_id SEPARATOR ',') AS cats FROM {ab_param} AS p"
					." INNER JOIN {ab_param_category_rel} AS r ON r.element_id=p.id"
					.$where
					." WHERE p.trash='0' GROUP BY p.id ORDER BY p.sort ASC", ($cat_id ? $cat_id : $site_id));

			foreach ($rows as &$row)
			{
				$row["cats"] = array_unique(explode(',', $row["cats"]));
				if ($row["type"] == 'select' || $row["type"] == 'multiple' || $row["type"] == 'checkbox')
				{
					$row["select_array"] = DB::query_fetch_all("SELECT [name], id, value FROM {ab_param_select} WHERE param_id=%d ORDER BY sort ASC", $row["id"]);
					foreach ($row["select_array"] as $row_select)
					{
						$row["select_values"][$row["type"] == 'checkbox' ? $row_select["value"] : $row_select["id"]] = $row_select["name"];
					}
				}
				if($row["type"] == 'attachments')
				{
					$config = unserialize($row["config"]);
					$row["max_count_attachments"] = ! empty($config["max_count_attachments"]) ? $config["max_count_attachments"] : 0;
					$row["attachments_access_admin"] = ! empty($config["attachments_access_admin"]) ? $config["attachments_access_admin"] : 0;
					$row["attachment_extensions"] = ! empty($config["attachment_extensions"]) ? $config["attachment_extensions"] : '';
					$row["use_animation"] = ! empty($config["use_animation"]) ? true : false;
				}
			}
			//сохранение кеша
			$this->diafan->_cache->save($rows, $cache_meta, 'ab');
		}
		return $rows;
	}

	/**
	 * Формирует SQL-запрос при поиске по объявлениям
	 *
	 * @return void
	 */
	private function where(&$where, &$where_param, &$values, &$getnav)
	{
		$where = ' AND s.site_id='.$this->diafan->_site->id;
		$values_param = array();

		$getnav = '?action=search';
		if(! empty($_REQUEST["cat_id"]))
		{
			$this->diafan->_route->cat = $this->diafan->filter($_REQUEST, "int", 'cat_id');
			$catarr = array(0);
			$getnav .='&cat_id='.$this->diafan->_route->cat;
			if ($this->diafan->_route->cat)
			{
				$children = $this->diafan->get_children($this->diafan->_route->cat, "ab_category");
				$children[] = $this->diafan->_route->cat;
				$where_param .= " INNER JOIN {ab_category_rel} AS c ON s.id=c.element_id AND c.cat_id IN (".implode(',', $children).")";
			}
		}
		$rows = DB::query_fetch_all("SELECT DISTINCT(p.id), p.type, p.required FROM {ab_param} as p "
				." INNER JOIN {ab_param_category_rel} AS c ON p.id=c.element_id "
				.($this->diafan->configmodules("cat") ? " AND (c.cat_id=%d OR c.cat_id=0)" : "")
				." WHERE p.search='1' AND p.trash='0' ORDER BY p.sort ASC", $this->diafan->_route->cat);
		foreach ($rows as $row)
		{
			if ($row["type"] == 'date' && (! empty($_REQUEST["p".$row["id"]."_1"]) || ! empty($_REQUEST["p".$row["id"]."_2"])))
			{
				$where_param .= "
							INNER JOIN {ab_param_element} AS pe".$row["id"]." ON pe".$row["id"].".element_id=s.id AND pe".$row["id"].".param_id='%d'"
					." AND pe".$row["id"].".trash='0'";
				$values_param[] = $row["id"];
				if(! empty($_REQUEST["p".$row["id"]."_1"]))
				{
					$where_param .= " AND pe".$row["id"].".value".$this->diafan->_languages->site.">='%s'";
					$values_param[] = $this->diafan->formate_in_date($_REQUEST["p".$row["id"]."_1"]);
					$getnav .= '&p'.$row["id"].'_1='.$this->diafan->filter($_REQUEST, "url", "p".$row["id"]."_1");
				}
				if(! empty($_REQUEST["p".$row["id"]."_2"]))
				{
					$where_param .= " AND pe".$row["id"].".value".$this->diafan->_languages->site."<='%s'";
					$values_param[] = $this->diafan->formate_in_date($_REQUEST["p".$row["id"]."_2"]);
					$getnav .= '&p'.$row["id"].'_2='.$this->diafan->filter($_REQUEST, "url", "p".$row["id"]."_2");
				}
			}
			elseif ($row["type"] == 'datetime' && (! empty($_REQUEST["p".$row["id"]."_1"]) || ! empty($_REQUEST["p".$row["id"]."_2"])))
			{
				$where_param .= "
							INNER JOIN {ab_param_element} AS pe".$row["id"]." ON pe".$row["id"].".element_id=s.id AND pe".$row["id"].".param_id='%d'"
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
			elseif ($row["type"] == 'numtext' && (! empty($_REQUEST["p".$row["id"]."_2"]) || ! empty($_REQUEST["p".$row["id"]."_1"])))
			{
				$val1 = $this->diafan->filter($_REQUEST, "float", "p".$row["id"]."_1");
				$val2 = $this->diafan->filter($_REQUEST, "float", "p".$row["id"]."_2");
				$where_param .= "
					INNER JOIN {ab_param_element} AS pe".$row["id"]." ON pe".$row["id"].".element_id=s.id AND pe".$row["id"].".param_id='%d'"
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
			elseif ($row["type"] == 'checkbox' && ! empty($_REQUEST["p".$row["id"]]))
			{
				$where_param .= "
							INNER JOIN {ab_param_element} AS pe".$row["id"]." ON pe".$row["id"].".element_id=s.id AND pe".$row["id"].".param_id='%d'"
					." AND pe".$row["id"].".trash='0' AND pe".$row["id"].".value".$this->diafan->_languages->site."='1'";
				$values_param[] = $row["id"];
				$getnav .= '&p'.$row["id"].'=1';
			}
			elseif(($row["type"] == 'select' || $row["type"] == 'multiple') && ! empty($_REQUEST["p".$row["id"]]))
			{
				if (!is_array($_REQUEST["p".$row["id"]]))
				{
					$val = $this->diafan->filter($_REQUEST, "int", "p".$row["id"]);
					if((! empty($_REQUEST["pr1"]) || ! empty($_REQUEST["pr2"])) && $row["required"])
					{
						$where .= " AND prp.param_id=".$row["id"]." AND prp.param_value".$this->diafan->_languages->site."='".$val."'";
					}
					else
					{
						$where_param .= "
						INNER JOIN {ab_param_element} AS pe".$row["id"]." ON pe".$row["id"].".element_id=s.id AND pe".$row["id"].".param_id='%d'"
						." AND pe".$row["id"].".trash='0' AND pe".$row["id"].".value".$this->diafan->_languages->site."='%d'";
						$values_param[] = $row["id"];
						$values_param[] = $val;
					}
					$getnav .= '&p'.$row["id"].'='.$val;
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
					if(! empty($vals))
					{
						$where_param .= " INNER JOIN {ab_param_element} AS pe".$row["id"]." ON pe".$row["id"].".element_id=s.id AND pe".$row["id"].".param_id='%d'"
						." AND pe".$row["id"].".trash='0' AND pe".$row["id"].".value".$this->diafan->_languages->site." IN (".implode(", ", $vals).")";
						$values_param[] = $row["id"];
					}
				}
			}
		}
		$values = array_merge($values_param, $values);
	}

	/**
	 * Позволяет добавлять характеристики объявления для сортировки
	 *
	 * @return array
	 */
	private function expand_sort_with_params()
	{
		$sort_fields_names = array(1 => $this->diafan->_('Дата добавления', false));

		$sort_directions = array(
			1 => 's.prior DESC, s.created DESC',
			2 => 's.prior ASC, s.created ASC',
		);

		$param_ids = array();

		$rows = DB::query_fetch_all("SELECT p.id, p.[name], p.type FROM {ab_param} AS p "
		. " INNER JOIN {ab_param_category_rel} AS cr ON cr.element_id=p.id AND cr.trash='0' "
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

		$use_params_for_sort = $this->diafan->_route->sort > 2 ? true : false;

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
		if(! empty($_REQUEST['action']) && $_REQUEST['action'] == "search")
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
	 * @return void
	 */
	private function prepare_data_category(&$row)
	{
		$this->diafan->_rating->prepare($row["id"], 'ab', 'cat');
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
	 * @return void
	 */
	private function format_data_category(&$row)
	{
		$row["name"] = $this->diafan->_useradmin->get($row["name"], 'name', $row["id"], 'ab_category', _LANG);
		if(! empty($row["anons"]))
		{
			$row["anons"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["anons"]), 'anons', $row["id"], 'ab_category', _LANG);
		}
		if(! empty($row["text"]))
		{
			$row["text"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["text"]), 'text', $row["id"], 'ab_category', _LANG);
		}
		$row["rating"] = $this->diafan->_rating->get($row["id"], 'ab', 'cat', (! empty($row["site_id"]) ? $row["site_id"] : 0));
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
	 * Подготовка к форматированию данных о элементе для шаблона вне зоны кэша
	 *
	 * @return void
	 */
	private function prepare_data_element(&$row)
	{
		$this->prepare_author($row["user_id"]);
		$this->diafan->_tags->prepare($row["id"], 'ab');
		$this->diafan->_rating->prepare($row["id"], 'ab');
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
	 * Форматирование данных о элементе для шаблона вне зоны кэша
	 *
	 * @return void
	 */
	public function format_data_element(&$row)
	{
		if ( ! empty($row["name"]))
		{
			$row["name"] = $this->diafan->_useradmin->get($row["name"], 'name', $row["id"], 'ab', _LANG);
		}
		if ( ! empty($row["text"]))
		{
			$row["text"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["text"]), 'text', $row["id"], 'ab', _LANG);
		}
		if ( ! empty($row["anons"]))
		{
			$row["anons"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["anons"]), 'anons', $row["id"], 'ab', _LANG);
		}
		if ( ! empty($row["param"]))
		{
			foreach ($row["param"] as $k => $param)
			{
				$row["param"][$k]["name"] = $this->diafan->_useradmin->get($param["name"], 'name', $param["id"], 'ab_param');
				if ( ! empty($param["value_id"]))
				{
					$lang = in_array($param["type"], array('text', 'textarea', 'editor')) ? _LANG : '';
					$row["param"][$k]["value"] = $this->diafan->_useradmin->get($param["value"], 'value', $param["value_id"], 'ab_param_element', $lang, $param["type"]);
				}
			}
		}
		//Представляет данные в разных форматах, удобных для использования в шаблоне
		foreach ($row["param"] as $k => $param)
		{
			$row["ids_param"][$param["id"]] = $param;
			$row["names_param"][strip_tags($param["name"])] = $param;
		}

		$row["tags"] =  $this->diafan->_tags->get($row["id"], 'ab', 'element', (! empty($row["site_id"]) ? $row["site_id"] : 0));
		$row["rating"] = $this->diafan->_rating->get($row["id"], 'ab', 'element', (! empty($row["site_id"]) ? $row["site_id"] : 0));
		$row["author"] = $this->get_author($row["user_id"]);

		if($this->diafan->_site->module == 'ab' && ! $this->current_link_module && ! $this->user_hash)
		{
			$this->current_link_module = str_replace('ROUTE_END', '', $this->diafan->_route->link($this->diafan->_site->id, 0, "ab", "element", false));
			$this->user_hash = $this->diafan->_users->get_hash();
		}
		if($this->diafan->_site->module == 'ab' && $this->diafan->_users->id && $row["user_id"] == $this->diafan->_users->id)
		{
			$row["edit_access"] = true;
			$row["edit_link"] = $this->current_link_module.'/edit'.$row["id"].ROUTE_END;
			$row["delete_access"] = true;
			$row["delete_link"] = $this->current_link_module.ROUTE_END.'?action=delete&id='.$row["id"].'&check_hash_user='.$this->user_hash;
			if(! $this->diafan->configmodules('premoderation', "ab"))
			{
				$row["block_access"] = true;
				$row["block_link"] = $this->current_link_module.ROUTE_END.'?action=block&id='.$row["id"].'&check_hash_user='.$this->user_hash;
			}
			else
			{
				$row["block_access"] = false;
			}
		}
		else
		{
			$row["edit_access"] = false;
			$row["delete_access"] = false;
			$row["block_access"] = false;
		}
	}

	/**
	 * Проверяет подключена ли предмодерация в настройках модуля
	 *
	 * @param integer $site_id страница сайта с подключенным модулем
	 * @return boolean
	 */
	public function check_premoderation($site_id = 0)
	{
		if($this->diafan->configmodules('premoderation', 'ab', $site_id) && $this->diafan->configmodules('premoderation', 'ab', $site_id) === '1')
		{
			return true;
		}
		if ($this->diafan->configmodules('premoderation', 'ab', $site_id) && in_array($this->diafan->_users->role_id, unserialize($this->diafan->configmodules('premoderation', 'ab', $site_id))))
		{
			return true;
		}
		return false;
	}
}
