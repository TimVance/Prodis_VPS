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
 * Clauses_model
 */
class Clauses_model extends Model
{
	/**
	 * Генерирует данные для списка всех статей без деления на категории
	 *
	 * @return array
	 */
	public function list_()
	{
		if ($this->diafan->_route->cat)
		{
			Custom::inc('includes/404.php');
		}
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		$cache_meta = array(
				"name"     => "list",
				"lang_id" => _LANG,
				"page"     => $this->diafan->_route->page > 1 ? $this->diafan->_route->page : 1,
				"site_id"  => $this->diafan->_site->id,
				"time"     => $time,
				"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
			);

		//кеширование
		if (! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{
			////navigation//
			$this->diafan->_paginator->nen = $this->list_query_count($time);
			$this->result["paginator"] = $this->diafan->_paginator->get();
			////navigation///

			$this->result["rows"] = $this->list_query($time);
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
			"SELECT COUNT(DISTINCT e.id) FROM {clauses} AS e"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='clauses' AND a.element_type='element'" : "")
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
			"SELECT e.id, e.created, e.[name], e.[anons], e.timeedit, e.site_id FROM {clauses} AS e"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='clauses' AND a.element_type='element'" : "")
			." WHERE e.[act]='1' AND e.trash='0' AND e.site_id=%d AND e.created<%d"
			." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." GROUP BY e.id ORDER BY e.sort DESC, e.id DESC",
			$this->diafan->_site->id, $time, $time, $time,
			$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
		);
		return $rows;
	}

	/**
	 * Генерирует данные для первой страницы статей
	 *
	 * @return void
	 */
	public function first_page()
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name"     => "first_page",
			"lang_id" => _LANG,
			"page" => $this->diafan->_route->page > 1 ? $this->diafan->_route->page : 1,
			"time"     => $time,
			"site_id"  => $this->diafan->_site->id,
			"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0)
		);
		if (! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{
			////navigation//
			$this->diafan->_paginator->nen = $this->first_page_cats_query_count();
			$this->diafan->_paginator->nastr = $this->diafan->configmodules("nastr_cat");
			$this->result["paginator"] = $this->diafan->_paginator->get();
			////navigation///

			$this->result["categories"] = $this->first_page_cats_query();
			foreach ($this->result["categories"] as &$row)
			{
				$this->diafan->_route->prepare($row["site_id"], $row["id"], "clauses", "cat");
				if ($this->diafan->configmodules("images_cat") && $this->diafan->configmodules("list_img_cat"))
				{
					$this->diafan->_images->prepare($row["id"], 'clauses', 'cat');
				}
			}
			foreach ($this->result["categories"] as &$row)
			{
				if (empty($this->result["timeedit"]) || $row["timeedit"] > $this->result["timeedit"])
				{
					$this->result["timeedit"] = $row["timeedit"];
				}

				$row["children"] = $this->get_children_category($row["id"], $time);

				if ($this->diafan->configmodules("children_elements"))
				{
					$cat_ids = $this->diafan->get_children($row["id"], "clauses_category");
					$cat_ids[] = $row["id"];
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

				$row["link_all"] = $this->diafan->_route->link($row["site_id"], $row["id"], 'clauses', 'cat');

				if ($this->diafan->configmodules("images_cat") && $this->diafan->configmodules("list_img_cat"))
				{
					$row["img"] =
					$this->diafan->_images->get(
							'medium', $row["id"], 'clauses', 'cat',
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
		"SELECT COUNT(DISTINCT c.id) FROM {clauses_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='clauses' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.parent_id=0 AND c.trash='0' AND c.site_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''),
		$this->diafan->_site->id
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
		"SELECT c.id, c.[name], c.[anons], c.timeedit, c.site_id FROM {clauses_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='clauses' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.parent_id=0 AND c.trash='0' AND c.site_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY c.id ORDER by c.sort ASC, c.id ASC",
		$this->diafan->_site->id,
		$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr);
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
		"SELECT e.id, e.[name], e.timeedit, e.[anons], e.site_id, e.created FROM {clauses} AS e"
		." INNER JOIN {clauses_category_rel} AS r ON e.id=r.element_id"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='clauses' AND a.element_type='element'" : "")
		." WHERE r.cat_id IN (%s) AND e.[act]='1' AND e.trash='0' AND e.created<%d"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY e.id ORDER BY e.sort DESC, e.id DESC",
		implode(',', $cat_ids), $time, $time, $time, 0, $this->diafan->configmodules("count_list")
		);
		return $rows;
	}

	/**
	 * Генерирует данные для списка статей в категории
	 *
	 * @return void
	 */
	public function list_category()
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
				"name"     => "list",
				"cat_id"   => $this->diafan->_route->cat,
				"lang_id" => _LANG,
				"page"     => $this->diafan->_route->page > 1 ? $this->diafan->_route->page : 1,
				"time"     => $time,
				"site_id"  => $this->diafan->_site->id,
				"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
			);
		if (! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{
			$row = $this->list_category_query();

			if (! $row)
			{
				Custom::inc('includes/404.php');
			}
			if (empty($row) || (! empty($row['access']) && ! $this->access($row['id'], 'clauses')))
			{
				Custom::inc('includes/403.php');
			}

			$this->result = $row;

			$this->result["breadcrumb"] = $this->get_breadcrumb();

			if ($this->diafan->configmodules("images_cat"))
			{
				$this->diafan->_images->prepare($row["id"], 'clauses', 'cat');
			}

			$this->result["children"] = $this->get_children_category($row["id"], $time);

			if ($this->diafan->configmodules("images_cat"))
			{
				$this->result["img"] = $this->diafan->_images->get(
						'medium', $row["id"], 'clauses', 'cat',
						$this->diafan->_site->id, $row["name"], 0, 0, 'large'
					);
			}

			if ($this->diafan->configmodules("children_elements"))
			{
				$cat_ids = $this->diafan->get_children($this->diafan->_route->cat, "clauses_category");
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
		$this->result["text"] = $this->diafan->_useradmin->get($this->result["text"], 'text', $this->diafan->_route->cat, 'clauses_category', _LANG);
		if($this->result["anons_plus"])
		{
			$this->result["text"] = $this->result["anons"].$this->result["text"];
		}

		$this->result["comments"] = $this->diafan->_comments->get(0, '', 'cat');

		$this->prepare_data_category($this->result);
		$this->format_data_category($this->result);

		if (! empty($this->result["previous"]["text"]))
		{
			$this->result["previous"]["text"] =
				$this->diafan->_useradmin->get($this->result["previous"]["text"], 'name', $this->result["previous"]["id"], 'clauses_category', _LANG);
		}
		if (! empty($this->result["next"]["text"]))
		{
			$this->result["next"]["text"] =
				$this->diafan->_useradmin->get($this->result["next"]["text"], 'name', $this->result["next"]["id"], 'clauses_category', _LANG);
		}
		foreach ($this->result["breadcrumb"] as $k => &$b)
		{
			if ($k == 0)
				continue;

			$b["name"] = $this->diafan->_useradmin->get($b["name"], 'name', $b["id"], 'clauses_category', _LANG);
		}

		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);
		$this->diafan->_keywords->get($this->result["text"]);
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
		$row = DB::query_fetch_array("SELECT id, [name], [anons], [anons_plus] ".$fields.", timeedit, [descr], [keywords], [canonical], sort, parent_id, [title_meta], access, theme, view, view_rows, [act], noindex FROM {clauses_category}"
		." WHERE id=%d AND trash='0' AND site_id=%d"
		.(! $this->is_admin() ?  " AND [act]='1'" : '')
		." ORDER BY sort ASC, id ASC",
		$this->diafan->_route->cat, $this->diafan->_site->id);
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
		"SELECT COUNT(DISTINCT e.id) FROM {clauses} AS e"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='clauses' AND a.element_type='element'" : "")
		." INNER JOIN {clauses_category_rel} AS r ON e.id=r.element_id"
		." AND e.id=r.element_id AND r.cat_id IN (%s)"
		." WHERE e.[act]='1' AND e.trash='0' AND e.created<%d"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''),
		implode(',', $cat_ids), $time, $time, $time
		);
		return $count;
	}

	/**
	 * Получает из базы данных элементы для списка элементов в категории
	 *
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @param array $cat_ids номера категорий, элементы из которых выбираются
	 * @return arary
	 */
	private function list_category_elements_query($time, $cat_ids)
	{
		$rows = DB::query_range_fetch_all(
		"SELECT e.id, e.[name], e.timeedit, e.[anons], e.site_id, e.created FROM {clauses} AS e"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='clauses' AND a.element_type='element'" : "")
		." INNER JOIN {clauses_category_rel} AS r ON e.id=r.element_id AND r.cat_id IN (%s)"
		." WHERE e.[act]='1' AND e.trash='0' AND e.created<%d"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY e.id ORDER BY e.sort DESC, e.id DESC",
		implode(',', $cat_ids), $time, $time, $time,
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
		"SELECT c.[name], c.id FROM {clauses_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='clauses' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.trash='0' AND c.site_id=%d"
		." AND (c.sort<%d OR c.sort=%d AND c.id<%d) AND c.parent_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." ORDER BY c.sort DESC, c.id DESC LIMIT 1", $this->diafan->_site->id, $sort, $sort, $this->diafan->_route->cat, $parent_id);
		if ($previous)
		{
			$this->result["previous"]["text"] = $previous["name"];
			$this->result["previous"]["id"]   = $previous["id"];
			$this->result["previous"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $previous["id"], "clauses", 'cat');
		}
		$next = DB::query_fetch_array(
		"SELECT c.[name], c.id FROM {clauses_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='clauses' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.trash='0' AND c.site_id=%d"
		." AND (c.sort>%d OR c.sort=%d AND c.id>%d) AND c.parent_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." ORDER BY c.sort ASC, c.id ASC LIMIT 1",
		$this->diafan->_site->id, $sort, $sort, $this->diafan->_route->cat, $parent_id);
		if ($next)
		{
			$this->result["next"]["text"] = $next["name"];
			$this->result["next"]["id"] = $next["id"];
			$this->result["next"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $next["id"], "clauses", 'cat');
		}
	}

	/**
	 * Генерирует данные для страницы статьи
	 *
	 * @return array
	 */
	public function id()
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
				"name"    => "show",
				"cat_id"  => $this->diafan->_route->cat,
				"show"    => $this->diafan->_route->show,
				"lang_id" => _LANG,
				"site_id" => $this->diafan->_site->id,
				"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
				"time"    => $time
			);
		if (! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{
			$row = $this->id_query($time);
			if (empty($row))
			{
				Custom::inc('includes/404.php');
			}

			if (! empty($row['access']) && ! $this->access($row['id']))
			{
				Custom::inc('includes/403.php');
			}
			$this->result = $row;
			if(! $this->diafan->configmodules("cat"))
			{
				$this->result["cat_id"] = 0;
			}
			$this->diafan->_route->cat = $this->result["cat_id"];

			if ($this->diafan->configmodules("images_element"))
			{
				$this->result["img"] = $this->diafan->_images->get(
						'medium', $row["id"], 'clauses', 'element',
						$this->diafan->_site->id, $row["name"], 0, 0, 'large'
					);
			}

			if ($this->result["cat_id"])
			{
				$this->result["allclauses"]["link"] = $this->diafan->_route->link($row["site_id"], $row["cat_id"], "clauses", 'cat');
			}
			else
			{
				$this->result["allclauses"]["link"] = $this->diafan->_route->link($row["site_id"]);
			}
			$this->id_previous_next($row["sort"], $time);

			$this->result["date"] = $this->format_date($row['created']);

			$this->meta($row);
			$this->theme_view_element($row);

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

		if($this->result["anons_plus"])
		{
			$this->result["text"] = $this->result["anons"].$this->result["text"];
			$this->result["anons"] = '';
		}

		if (! empty($this->result["previous"]["text"]))
		{
			$this->result["previous"]["text"] =
				$this->diafan->_useradmin->get($this->result["previous"]["text"], 'name', $this->result["previous"]["id"], 'clauses', _LANG);
		}
		if (! empty($this->result["next"]["text"]))
		{
			$this->result["next"]["text"] =
				$this->diafan->_useradmin->get($this->result["next"]["text"], 'name', $this->result["next"]["id"], 'clauses', _LANG);
		}
		foreach ($this->result["breadcrumb"] as $k => &$b)
		{
			if ($k == 0)
				continue;

			$b["name"] = $this->diafan->_useradmin->get($b["name"], 'name', $b["id"], 'clauses_category', _LANG);
		}

		$this->counter_view();
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
		$row = DB::query_fetch_array("SELECT id, [name], [anons], [anons_plus], [text], timeedit, created, cat_id,"
		." [keywords], [descr], [canonical], site_id, [title_meta], access, theme, view, sort, [act], date_start, date_finish, noindex".$fields." FROM {clauses}"
		." WHERE id=%d AND trash='0' AND site_id=%d"
		.(! $this->is_admin() ? " AND [act]='1' AND created<%d AND date_start<=%d AND (date_finish=0 OR date_finish>=%d)" : '')
		." LIMIT 1",
		$this->diafan->_route->show, $this->diafan->_site->id, $time, $time, $time);
		return $row;
	}

	/**
	 * Формирует ссылки на предыдущий и следующий элемент
	 *
	 * @param integer $sort номер для сортировки текущего элемента
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @return void
	 */
	private function id_previous_next($sort, $time)
	{
		$previous = DB::query_fetch_array(
				"SELECT e.[name], e.id FROM {clauses} AS e"
				.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='clauses' AND a.element_type='element'" : "")
				." WHERE e.[act]='1' AND e.trash='0' AND e.site_id=%d"
				.($this->diafan->configmodules("cat") ? " AND e.cat_id='".$this->diafan->_route->cat."'" : '')
				. " AND (e.sort>%d OR e.sort=%d AND e.id>%d)"
				." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
				.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
				." ORDER BY e.sort ASC, e.id ASC LIMIT 1",
				$this->diafan->_site->id, $sort, $sort, $this->diafan->_route->show, $time, $time
			);
		if ($previous)
		{
			$this->result["previous"]["text"] = $previous["name"];
			$this->result["previous"]["id"]   = $previous["id"];
			$this->result["previous"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $previous["id"], "clauses");
		}
		$next = DB::query_fetch_array(
			"SELECT e.[name], e.id FROM {clauses} AS e"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='clauses' AND a.element_type='element'" : "")
			." WHERE e.[act]='1' AND e.trash='0' AND e.site_id=%d"
			.($this->diafan->configmodules("cat") ? " AND e.cat_id='".$this->diafan->_route->cat."'" : '')
			. " AND (e.sort<%d OR e.sort=%d AND e.id<%d)"
			." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." ORDER BY e.sort DESC, e.id DESC LIMIT 1",
			$this->diafan->_site->id, $sort, $sort, $this->diafan->_route->show, $time, $time
		);
		if ($next)
		{
			$this->result["next"]["text"] = $next["name"];
			$this->result["next"]["id"]   = $next["id"];
			$this->result["next"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $next["id"], "clauses");
		}
	}

	/**
	 * Генерирует данные для шаблонной функции: блок статей
	 *
	 * @param integer $count количество статей
	 * @param array $site_ids страницы сайта
	 * @param array $cat_ids категории
	 * @param string $sort сортировка date - по дате, rand - случайно, keywords - статьи, похожие по названию для текущей страницы
	 * @param integer $images количество изображений
	 * @param string $images_variation размер изображений
	 * @param string $tag тег
	 * @return array
	 */
	public function show_block($count, $site_ids, $cat_ids, $sort, $images, $images_variation, $tag)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		if($sort == 'keywords')
		{
			if($this->diafan->_site->titlemodule)
			{
				$title = $this->diafan->_site->titlemodule;
			}
			else
			{
				$title = $this->diafan->_site->name;
			}
		}
		$where = '';

		//кеширование
		$cache_meta = array(
			"name"     => "block",
			"cat_ids"  => $cat_ids,
			"site_ids" => $site_ids,
			"count"    => $count,
			"lang_id"  => _LANG,
			"time"     => $time,
			"sort"     => $sort.($sort == 'keywords' ? $title : ''),
			"current"  => ($this->diafan->_site->module == 'clauses' && $this->diafan->_route->show ? $this->diafan->_route->show : ''),
			"images"   => $images,
			"images_variation" => $images_variation,
			"access" => ($this->diafan->configmodules('where_access_element', 'clauses') || $this->diafan->configmodules('where_access_cat', 'clauses') ? $this->diafan->_users->role_id : 0),
			"tag" => $tag,
		);

		if ($sort == "rand" || ! $result = $this->diafan->_cache->get($cache_meta, "clauses"))
		{
			$minus = array();
			$one_cat_id = count($cat_ids) == 1 && substr($cat_ids[0], 0, 1) !== '-' ? $cat_ids[0] : false;
			if(! $this->validate_attribute_site_cat('clauses', $site_ids, $cat_ids, $minus))
			{
				return false;
			}
			$inner = "";
			if($cat_ids)
			{
				$inner = " INNER JOIN {clauses_category_rel} as r ON r.element_id=e.id"
				." AND r.cat_id IN (".implode(',', $cat_ids).")";
			}
			elseif(! empty($minus["cat_ids"]))
			{
				$inner = " INNER JOIN {clauses_category_rel} as r ON r.element_id=e.id"
				." AND r.cat_id NOT IN (".implode(',', $minus["cat_ids"]).")";
			}
			$where = '';
			if($site_ids)
			{
				$where .= " AND e.site_id IN (".implode(",", $site_ids).")";
			}
			elseif(! empty($minus["site_ids"]))
			{
				$where .= " AND e.site_id NOT IN (".implode(",", $minus["site_ids"]).")";
			}
			if($sort == 'keywords')
			{
				Custom::inc('includes/searchwords.php');
				$searchwords = new Searchwords();
				$searchwords->max_length = $this->diafan->configmodules("max_length", "search");
				$names = $searchwords->prepare($title);
				if(empty($names))
				{
					return false;
				}

				$keys = DB::query_fetch_key_value("SELECT id, keyword FROM {search_keywords} WHERE keyword IN ('".implode("', '", $names)."')", "keyword", "id");
				if(count($keys) < count($names))
				{
					return;
				}
				$inner .= " INNER JOIN {search_results} AS sr ON sr.element_id=e.id AND sr.table_name='clauses'";
				$inner .= " INNER JOIN {search_index} AS i ON sr.id=i.result_id AND i.keyword_id IN ('".implode("', '", $keys)."') AND i.rating=0";
			}
			if($this->diafan->_site->module == 'clauses' && $this->diafan->_route->show)
			{
				$where .= $where.' AND e.id<>'.$this->diafan->_route->show;
			}
			if($tag)
			{
				$t = DB::query_fetch_array("SELECT id, [name] FROM {tags_name} WHERE [name]='%s' AND trash='0'", $tag);
				if(! $tag)
				{
					return false;
				}
				$inner .= " INNER JOIN {tags} AS t ON t.element_id=e.id AND t.element_type='element' AND t.module_name='clauses' AND t.tags_name_id=".$t["id"];
			}

			if ($sort == "rand")
			{
				$max_count = DB::query_result(
						"SELECT COUNT(DISTINCT e.id) FROM {clauses} as e"
						.$inner
						.($this->diafan->configmodules('where_access_element', 'clauses') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='clauses' AND a.element_type='element'" : "")
						." WHERE e.[act]='1' AND e.trash='0' AND e.created<%d"
						.($this->diafan->_site->module == 'clauses' && $this->diafan->_route->show ? " AND e.id<>".$this->diafan->_route->show : '')
						." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
						.$where
						.($this->diafan->configmodules('where_access_element', 'clauses') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''),
						$time, $time, $time
					);
				$rands = array();
				for ($i = 1; $i <= min($max_count, $count); $i++)
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
			$result["rows"] = array();

			foreach ($rands as $rand)
			{
				switch($sort)
				{
					case "date":
						$order = ' ORDER BY e.created DESC, e.id DESC';
						break;

					case "rand":
						$order = '';
						break;

					default:
						$order = ' ORDER BY e.sort DESC, e.id DESC';
						break;
				}
				$rows = DB::query_range_fetch_all(
						"SELECT e.id, e.[name],e.[anons], e.timeedit, e.site_id, e.created FROM {clauses} AS e"
						.$inner
						.($this->diafan->configmodules('where_access_element', 'clauses') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='clauses' AND a.element_type='element'" : "")
						." WHERE e.[act]='1' AND e.trash='0' AND e.created<%d"
						.($this->diafan->_site->module == 'clauses' && $this->diafan->_route->show ? " AND e.id<>".$this->diafan->_route->show : '')
						." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
						.$where
						.($this->diafan->configmodules('where_access_element', 'clauses') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
						." GROUP BY e.id"
						.$order,
						$time, $time, $time,
						$sort == "rand" ? $rand : 0,
						$sort == "rand" ? 1     : $count
					);

				$result["rows"] = array_merge($result["rows"], $rows);
			}
			$this->elements($result["rows"], array("count" => $images, "variation" => $images_variation));

			// если категория только одна, задаем ссылку на нее
			if (! empty($result["rows"]) && $one_cat_id)
			{
				$cat = DB::query_fetch_array("SELECT [name], site_id, id FROM {clauses_category} WHERE id=%d LIMIT 1", $one_cat_id);

				$result["name"] = $cat["name"];
				$result["link_all"] = $this->diafan->_route->link($cat["site_id"], $cat["id"], 'clauses', 'cat');
				$result["category"] = true;
			}
			// если раздел сайта только один, то задаем ссылку на него
			elseif (! empty($result["rows"]) && count($site_ids) == 1)
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
				$this->diafan->_cache->save($result, $cache_meta, "clauses");
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
	 * Генерирует данные для шаблонной функции: блок связанных статей
	 *
	 * @param integer $count количество статей
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
			"access" => ($this->diafan->configmodules('where_access_element', 'clauses') || $this->diafan->configmodules('where_access_cat', 'clauses') ? $this->diafan->_users->role_id : 0),
			"time" => $time
		);

		if (! $result = $this->diafan->_cache->get($cache_meta, "clauses"))
		{
			$result["rows"] = DB::query_range_fetch_all(
			"SELECT e.id, e.[name], e.[anons], e.created, e.timeedit, e.site_id FROM {clauses} AS e"
			." INNER JOIN {clauses_rel} AS r ON e.id=r.rel_element_id AND r.element_id=%d"
			.($this->diafan->configmodules("rel_two_sided") ? " OR e.id=r.element_id AND r.rel_element_id=".$this->diafan->_route->show : '')
			.($this->diafan->configmodules('where_access_element', 'clauses') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='clauses' AND a.element_type='element'" : "")
			." WHERE e.[act]='1' AND e.trash='0' AND e.created<%d"
			." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element', 'clauses') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." GROUP BY e.id"
			." ORDER BY e.sort DESC",
			$this->diafan->_route->show, $time, $time, $time, 0, $count
			);
			$this->elements($result["rows"], array("count" => $images, "variation" => $images_variation));
			$this->diafan->_cache->save($result, $cache_meta, "clauses");
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
	 * Форматирует данные о статьях для списка статей
	 *
	 * @param array $rows все полученные из базы данных элементы
	 * @param string $images_config настройки отображения изображений
	 * @return void
	 */
	public function elements(&$rows, $images_config = '')
	{
		if (empty($this->result["timeedit"]))
		{
			$this->result["timeedit"] = '';
		}
		foreach ($rows as &$row)
		{
			if ($this->diafan->configmodules("images_element", "clauses", $row["site_id"]))
			{
				if (is_array($images_config))
				{
					if($images_config["count"] > 0)
					{
						$this->diafan->_images->prepare($row["id"], "clauses");
					}
				}
				elseif($this->diafan->configmodules("list_img_element", "clauses", $row["site_id"]))
				{
					$this->diafan->_images->prepare($row["id"], "clauses");
				}
			}
			$this->diafan->_route->prepare($row["site_id"], $row["id"], "clauses");
		}
		foreach ($rows as &$row)
		{
			if ($row["timeedit"] < $this->result["timeedit"])
			{
				$this->result["timeedit"] = $row["timeedit"];
			}
			unset($row["timeedit"]);
			$row["link"] = $this->diafan->_route->link($row["site_id"], $row["id"], "clauses");

			if ($this->diafan->configmodules("images_element", "clauses", $row["site_id"]))
			{
				if (is_array($images_config))
				{
					if($images_config["count"] > 0)
					{
						$row["img"]  = $this->diafan->_images->get(
								$images_config["variation"], $row["id"], 'clauses', 'element',
								$row["site_id"], $row["name"], 0,
								$images_config["count"],
								$row["link"]
							);
					}
				}
				elseif($this->diafan->configmodules("list_img_element", "clauses", $row["site_id"]))
				{
					$count = $this->diafan->configmodules("list_img_element", "clauses", $row["site_id"]) == 1 ? 1 : 0;
					$row["img"]  = $this->diafan->_images->get(
							'medium', $row["id"], 'clauses', 'element',
							$row["site_id"], $row["name"], 0,
							$count,
							($count ? $row["link"] : 'large')
						);
				}
			}
			$row["date"] = $this->format_date($row['created'], "clauses", $row["site_id"]);
			unset($row["created"]);
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
		"SELECT c.id, c.[name], c.[anons], c.site_id FROM {clauses_category} AS c"
		.($this->diafan->configmodules('where_access_cat', 'clauses') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='clauses' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.parent_id=%d AND c.trash='0' AND c.site_id=%d"
		.($this->diafan->configmodules('where_access_cat', 'clauses') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY c.id ORDER BY c.sort ASC, c.id ASC", $parent_id, $this->diafan->_site->id
		);

		foreach ($children as &$child)
		{
			if ($this->diafan->configmodules("images_cat") && $this->diafan->configmodules("list_img_cat"))
			{
				$this->diafan->_images->prepare($child["id"], 'clauses', 'cat');
			}
			$this->diafan->_route->prepare($child["site_id"], $child["id"], "clauses", "cat");
		}

		foreach ($children as &$child)
		{
			$child["link"] = $this->diafan->_route->link($child["site_id"], $child["id"], 'clauses',  'cat');
			if ($this->diafan->configmodules("images_cat") && $this->diafan->configmodules("list_img_cat"))
			{
				$child["img"] = $this->diafan->_images->get(
					'medium', $child["id"], 'clauses', 'cat',
					$child["site_id"], $child["name"], 0,
					$this->diafan->configmodules("list_img_cat") == 1 ? 1 : 0, $child["link"]);
			}
			$child["rows"] = array();
			if($this->diafan->configmodules("count_child_list"))
			{
				if ($this->diafan->configmodules("children_elements"))
				{
					$cat_ids = $this->diafan->get_children($child["id"], "clauses_category");
					$cat_ids[] = $child["id"];
				}
				else
				{
					$cat_ids = array($child["id"]);
				}
				$child["rows"] = $this->get_children_category_elements_query($time, $cat_ids);
			}
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
		"SELECT e.id, e.[name], e.timeedit, e.[anons], e.site_id, e.created FROM {clauses} AS e"
		." INNER JOIN {clauses_category_rel} AS r ON e.id=r.element_id"
		.($this->diafan->configmodules('where_access_element', 'clauses') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='clauses' AND a.element_type='element'" : "")
		." WHERE r.cat_id IN (%s) AND e.[act]='1' AND e.trash='0' AND e.created<%d"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element', 'clauses') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY e.id ORDER BY e.sort DESC, e.id DESC",
		implode(',', $cat_ids), $time, $time, $time,
		0, $this->diafan->configmodules("count_child_list")
		);
		$this->elements($rows);
		return $rows;
	}

	/**
	 * Подготовка к форматированию данных о категории для шаблона вне зоны кэша
	 *
	 * @return void
	 */
	private function prepare_data_category(&$row)
	{
		$this->diafan->_rating->prepare($row["id"], 'clauses', 'cat');
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
		$row["name"] = $this->diafan->_useradmin->get($row["name"], 'name', $row["id"], 'clauses_category', _LANG);
		if(! empty($row["anons"]))
		{
			$row["anons"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["anons"]), 'anons', $row["id"], 'clauses_category', _LANG);
		}
		if(! empty($row["text"]))
		{
			$row["text"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["text"]), 'text', $row["id"], 'clauses_category', _LANG);
		}
		$row["rating"] = $this->diafan->_rating->get($row["id"], 'clauses', 'cat', (! empty($row["site_id"]) ? $row["site_id"] : 0));
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
		$this->diafan->_tags->prepare($row["id"], 'clauses');
		$this->diafan->_rating->prepare($row["id"], 'clauses');
	}

	/**
	 * Форматирование данных о элементе для шаблона вне зоны кэша
	 *
	 * @return void
	 */
	public function format_data_element(&$row)
	{
		if (! empty($row["name"]))
		{
			$row["name"] = $this->diafan->_useradmin->get($row["name"], 'name', $row["id"], 'clauses', _LANG);
		}
		if (! empty($row["text"]))
		{
			$row["text"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["text"]), 'text', $row["id"], 'clauses', _LANG);
		}
		if(! empty($row["anons"]))
		{
			$row["anons"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["anons"]), 'anons', $row["id"], 'clauses', _LANG);
		}
		if (! empty($row["date"]))
		{
			$row["date"] = $this->diafan->_useradmin->get($row["date"], 'created', $row["id"], 'clauses');
		}

		$row["tags"] =  $this->diafan->_tags->get($row["id"], 'clauses', 'element', (! empty($row["site_id"]) ? $row["site_id"] : 0));
		$row["rating"] = $this->diafan->_rating->get($row["id"], 'clauses', 'element', (! empty($row["site_id"]) ? $row["site_id"] : 0));
	}
}
