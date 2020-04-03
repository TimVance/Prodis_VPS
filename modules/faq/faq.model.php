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
 * Faq_model
 */
class Faq_model extends Model
{
	/**
	 * Генерирует данные для списка всех вопросов без деления на категории
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
				"name"     => "list",
				"lang_id"  => _LANG,
				"page"     => $this->diafan->_route->page > 1 ? $this->diafan->_route->page : 1,
				"site_id"  => $this->diafan->_site->id,
				"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
				"time"     => $time
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
			"SELECT COUNT(DISTINCT e.id) FROM {faq} AS e"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='faq' AND a.element_type='element'" : "")
			." WHERE e.[act]='1' AND e.trash='0' AND e.site_id=%d"
			." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''),
			$this->diafan->_site->id, $time, $time
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
			"SELECT e.id, e.created, e.[name], e.[anons], e.[text], e.timeedit, e.site_id FROM {faq} AS e"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='faq' AND a.element_type='element'" : "")
			." WHERE e.[act]='1' AND e.trash='0' AND e.site_id=%d"
			." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." GROUP BY e.id ORDER BY e.created DESC, e.id DESC",
			$this->diafan->_site->id, $time, $time,
			$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
		);
		return $rows;
	}

	/**
	 * Генерирует данные для первой страницы модуля "Вопрос-Ответ"
	 *
	 * @return void
	 */
	public function first_page()
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name"     => "first_page",
			"lang_id"  => _LANG,
			"page" => $this->diafan->_route->page > 1 ? $this->diafan->_route->page : 1,
			"site_id"  => $this->diafan->_site->id,
			"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
			"time"     => $time
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
				$this->diafan->_route->prepare($row["site_id"], $row["id"], "faq", "cat");
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
					$cat_ids = $this->diafan->get_children($row["id"], "faq_category");
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

				$row["link_all"] = $this->diafan->_route->link($row["site_id"], $row["id"], 'faq', 'cat');
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
		"SELECT COUNT(DISTINCT c.id) FROM {faq_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='faq' AND a.element_type='cat'" : "")
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
		"SELECT c.id, c.[name], c.[anons], c.timeedit, c.site_id FROM {faq_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='faq' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.parent_id=0 AND c.trash='0' AND c.site_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY c.id ORDER by c.sort ASC, c.id ASC",
		$this->diafan->_site->id,
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
		"SELECT e.id, e.[name], e.timeedit, e.[anons], e.[text], e.site_id, e.created FROM {faq} AS e"
		." INNER JOIN {faq_category_rel} AS r ON e.id=r.element_id"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='faq' AND a.element_type='element'" : "")
		." WHERE r.cat_id IN (%s) AND e.[act]='1' AND e.trash='0'"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY e.id ORDER BY e.created DESC, e.id DESC",
		implode(',', $cat_ids), $time, $time, 0, $this->diafan->configmodules("count_list")
		);
		return $rows;
	}

	/**
	 * Генерирует данные для списка вопросов в категории
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
				"lang_id"  => _LANG,
				"page"     => $this->diafan->_route->page > 1 ? $this->diafan->_route->page : 1,
				"site_id"  => $this->diafan->_site->id,
				"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
				"time"     => $time
			);
		if (! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{
			$row = $this->list_category_query();

			if (! $row)
			{
				Custom::inc('includes/404.php');
			}
			if (empty($row) || (! empty($row['access']) && ! $this->access($row['id'], 'faq', 'cat')))
			{
				Custom::inc('includes/403.php');
			}

			$this->result = $row;

			$this->result["breadcrumb"] = $this->get_breadcrumb();

			$this->result["children"] = $this->get_children_category($row["id"], $time);

			if ($this->diafan->configmodules("children_elements"))
			{
				$cat_ids = $this->diafan->get_children($this->diafan->_route->cat, "shop_category");
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

		if (! empty($this->result["previous"]["text"]))
		{
			$this->result["previous"]["text"] =
				$this->diafan->_useradmin->get($this->result["previous"]["text"], 'name', $this->result["previous"]["id"], 'faq_category', _LANG);
		}
		if (! empty($this->result["next"]["text"]))
		{
			$this->result["next"]["text"] =
				$this->diafan->_useradmin->get($this->result["next"]["text"], 'name', $this->result["next"]["id"], 'faq_category', _LANG);
		}
		foreach ($this->result["breadcrumb"] as $k => &$b)
		{
			if ($k == 0)
				continue;

			$b["name"] = $this->diafan->_useradmin->get($b["name"], 'name', $b["id"], 'faq_category', _LANG);
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
		$row = DB::query_fetch_array("SELECT id, [name], [anons], [anons_plus] ".$fields.", timeedit, [descr], [keywords], [canonical], sort, parent_id, [title_meta], access, theme, view, view_rows, [act], noindex FROM {faq_category}"
		." WHERE id=%d AND trash='0' AND site_id=%d"
		.(! $this->is_admin() ? " AND [act]='1'" : '')
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
		"SELECT COUNT(DISTINCT e.id) FROM {faq} AS e"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='faq' AND a.element_type='element'" : "")
		." INNER JOIN {faq_category_rel} AS r ON e.id=r.element_id"
		." AND e.id=r.element_id AND r.cat_id IN (%s)"
		." WHERE e.[act]='1' AND e.trash='0'"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''),
		implode(',', $cat_ids), $time, $time
		);;
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
		"SELECT e.id, e.[name], e.timeedit, e.[anons], e.[text], e.site_id, e.created FROM {faq} AS e"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='faq' AND a.element_type='element'" : "")
		." INNER JOIN {faq_category_rel} AS r ON e.id=r.element_id AND r.cat_id IN (%s)"
		." WHERE e.[act]='1' AND e.trash='0'"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY e.id ORDER BY e.created DESC, e.id DESC",
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
		"SELECT c.[name], c.id FROM {faq_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='faq' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.trash='0' AND c.site_id=%d"
		." AND (c.sort<%d OR c.sort=%d AND c.id<%d) AND c.parent_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." ORDER BY c.sort DESC, c.id DESC LIMIT 1", $this->diafan->_site->id, $sort, $sort, $this->diafan->_route->cat, $parent_id);
		if ($previous)
		{
			$this->result["previous"]["text"] = $previous["name"];
			$this->result["previous"]["id"]   = $previous["id"];
			$this->result["previous"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $previous["id"], "faq", 'cat');
		}
		$next = DB::query_fetch_array(
		"SELECT c.[name], c.id FROM {faq_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='faq' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.trash='0' AND c.site_id=%d"
		." AND (c.sort>%d OR c.sort=%d AND c.id>%d) AND c.parent_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." ORDER BY c.sort ASC, c.id ASC LIMIT 1", $this->diafan->_site->id, $sort, $sort, $this->diafan->_route->cat, $parent_id);
		if ($next)
		{
			$this->result["next"]["text"] = $next["name"];
			$this->result["next"]["id"] = $next["id"];
			$this->result["next"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $next["id"], "faq", 'cat');
		}
	}

	/**
	 * Генерирует данные для страницы вопроса
	 *
	 * @return void
	 */
	public function id()
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
				"name"     => "show",
				"cat_id"   => $this->diafan->_route->cat,
				"show"     => $this->diafan->_route->show,
				"lang_id"  => _LANG,
				"site_id"  => $this->diafan->_site->id,
				"access" => ($this->diafan->configmodules('where_access_element') || $this->diafan->configmodules('where_access_cat') ? $this->diafan->_users->role_id : 0),
				"time"     => $time
			);
		if (! $this->result = $this->diafan->_cache->get($cache_meta, $this->diafan->_site->module))
		{
			$row = $this->id_query($time);
			if (empty($row))
			{
				Custom::inc('includes/404.php');
			}

			if(! empty($row['access']) && ! $this->access($row['id']))
			{
				Custom::inc('includes/403.php');
			}
			$this->result = $row;
			if(! $this->diafan->configmodules("cat"))
			{
				$this->result["cat_id"] = 0;
			}
			$this->diafan->_route->cat = $this->result["cat_id"];
			$this->result["attachments"]   = $this->diafan->_attachments->get($row["id"], 'faq');
			$this->result["use_animation"] = $this->diafan->configmodules("use_animation");

			if ($this->result["cat_id"])
			{
				$this->result["allfaq"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $row["cat_id"], "faq", 'cat');
			}
			else
			{
				$this->result["allfaq"]["link"] = $this->diafan->_route->link($this->diafan->_site->id);
			}
			$this->id_previous_next($row["created"], $time);

			$this->result["date"] = $this->format_date($row['created']);

			$row["name"] = $this->diafan->short_text($row["anons"], 100);
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

		if (! empty($this->result["previous"]["text"]))
		{
			$this->result["previous"]["text"] =
				$this->diafan->_useradmin->get($this->result["previous"]["text"], 'name', $this->result["previous"]["id"], 'faq', _LANG);
		}
		if (! empty($this->result["next"]["text"]))
		{
			$this->result["next"]["text"] =
				$this->diafan->_useradmin->get($this->result["next"]["text"], 'name', $this->result["next"]["id"], 'faq', _LANG);
		}
		foreach ($this->result["breadcrumb"] as $k => &$b)
		{
			if ($k == 0)
				continue;

			$b["name"] = $this->diafan->_useradmin->get($b["name"], 'name', $b["id"], 'faq_category', _LANG);
		}

		$this->counter_view();

		$this->result["comments"] = $this->diafan->_comments->get();
		$this->diafan->_keywords->get($this->result["anons"]);
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
		$row = DB::query_fetch_array("SELECT id, [name], timeedit, created, cat_id, [keywords],"
		." [descr], [canonical], [anons], [text], site_id, [title_meta], access, theme, view, [act], date_start, date_finish, noindex".$fields." FROM {faq}"
		." WHERE id=%d AND trash='0' AND site_id=%d"
		.(! $this->is_admin() ? " AND [act]='1' AND date_start<=%d AND (date_finish=0 OR date_finish>=%d)" : "")
		." LIMIT 1",
		$this->diafan->_route->show, $this->diafan->_site->id, $time, $time);
		return $row;
	}

	/**
	 * Формирует ссылки на предыдущий и следующий элемент
	 *
	 * @param integer $created время создания текущего элемента
	 * @param integer $time текущее время, округленное до минут, в формате UNIX
	 * @return void
	 */
	private function id_previous_next($created, $time)
	{
		$previous = DB::query_fetch_array(
			"SELECT e.[anons], e.id FROM {faq} AS e"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='faq' AND a.element_type='element'" : "")
			." WHERE e.[act]='1' AND e.trash='0' AND e.site_id=%d"
			.($this->diafan->configmodules("cat") ? " AND e.cat_id='".$this->diafan->_route->cat."'" : '')
			." AND (e.created>%d OR e.created=%d AND e.id>%d)"
			." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." ORDER BY e.created ASC, e.id ASC LIMIT 1",
			$this->diafan->_site->id, $created, $created, $this->diafan->_route->show, $time, $time
		);
		$next = DB::query_fetch_array(
			"SELECT e.[anons], e.id FROM {faq} AS e"
			.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='faq' AND a.element_type='element'" : "")
			." WHERE e.[act]='1' AND e.trash='0' AND e.site_id=%d"
			.($this->diafan->configmodules("cat") ? " AND e.cat_id='".$this->diafan->_route->cat."'" : '')
			." AND (e.created<%d OR e.created=%d AND e.id<%d)"
			." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." ORDER BY e.created DESC, e.id DESC LIMIT 1",
			$this->diafan->_site->id, $created, $created, $this->diafan->_route->show, $time, $time
		);
		if ($previous)
		{
			$this->result["previous"]["text"] = $this->diafan->short_text($previous["anons"]);
			$this->result["previous"]["id"]   = $previous["id"];
			$this->result["previous"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $previous["id"], "faq");
		}
		if ($next)
		{
			$this->result["next"]["text"] = $this->diafan->short_text($next["anons"]);
			$this->result["next"]["id"]   = $next["id"];
			$this->result["next"]["link"] = $this->diafan->_route->link($this->diafan->_site->id, $next["id"], "faq");
		}
	}

	/**
	 * Генерирует данные для формы добавления вопроса
	 *
	 * @param integer $site_id номер страницы
	 * @param integer $cat_id номер категории
	 * @param boolean $insert_form форма выводится с помощью шаблонного тега
	 * @return array
	 */
	public function form($site_id = 0, $cat_id = 0, $insert_form = false)
	{
		if (! $insert_form)
		{
			$site_id = $this->diafan->_site->id;
			$cat_id = $this->diafan->_route->cat;
		}
		else
		{
			$cat_ids = array($cat_id);
			$site_ids = array($site_id);
			$minus = array();
			if(! $this->validate_attribute_site_cat('faq', $site_ids, $cat_ids, $minus))
			{
				return false;
			}
			if(! empty($site_ids[0]))
			{
				$site_id = $site_ids[0];
			}
			else
			{
				$site_id = 0;
			}
			if(! empty($cat_ids))
			{
				$cat_id = $cat_ids[0];
			}
			else
			{
				$cat_id = 0;
			}
		}
		$result = array();

		if (! $this->diafan->configmodules('only_user', 'faq', $site_id) || $this->diafan->_users->id)
		{
			$fields = array('', 'name', 'question', 'email', 'attachments', 'captcha');
			$result['form_tag'] = 'faq'.md5(serialize(array($site_id, $cat_id, $insert_form)));
			$this->form_errors($result, $result['form_tag'], $fields);
			$result["captcha"] = '';
			if ($this->diafan->_captcha->configmodules('faq', $site_id))
			{
				$result["captcha"] = $this->diafan->_captcha->get($result['form_tag'], $result['error_captcha']);
			}
			$result["site_id"] = $site_id;
			$result["cat_id"] = $cat_id;

			$result["attachments"] = false;
			if ($this->diafan->configmodules('attachments', 'faq', $site_id) && $this->diafan->configmodules("max_count_attachments", 'faq', $site_id))
			{
				$result["attachments"]           = true;
				$result["max_count_attachments"] = $this->diafan->configmodules("max_count_attachments", 'faq', $site_id);
				$result["attachment_extensions"] = $this->diafan->configmodules("attachment_extensions", 'faq', $site_id);
			}
			$result["name"] = $this->diafan->_users->fio;
		}
		return $result;
	}

	/**
	 * Генерирует данные для шаблонной функции: вопросов и ответов
	 *
	 * @param integer $count количество вопросов
	 * @param array $site_ids страницы сайта
	 * @param array $cat_ids категории
	 * @param string $sort сортировка date - по дате, rand - случайно
	 * @param boolean $often часто задаваемые вопросы
	 * @param string $tag тег
	 * @return array
	 */
	public function show_block($count, $site_ids, $cat_ids, $sort, $often, $tag)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name"     => "block",
			"cat_ids" => $cat_ids,
			"site_ids" => $site_ids,
			"count"    => $count,
			"lang_id" => _LANG,
			"sort"     => $sort,
			"current"  => ($this->diafan->_site->module == 'faq' && $this->diafan->_route->show ? $this->diafan->_route->show : ''),
			"access" => ($this->diafan->configmodules('where_access_element', 'faq') || $this->diafan->configmodules('where_access_cat', 'faq') ? $this->diafan->_users->role_id : 0),
			"time"     => $time,
			"tag" => $tag,
		);

		if ($sort == "rand" || ! $result = $this->diafan->_cache->get($cache_meta, "faq"))
		{
			$minus = array();
			$one_cat_id = count($cat_ids) == 1 && substr($cat_ids[0], 0, 1) !== '-' ? $cat_ids[0] : false;
			if(! $this->validate_attribute_site_cat('faq', $site_ids, $cat_ids, $minus))
			{
				return false;
			}
			$where = '';
			$inner = "";
			if($cat_ids)
			{
				$inner = " INNER JOIN {faq_category_rel} as r ON r.element_id=e.id"
				." AND r.cat_id IN (".implode(',', $cat_ids).")";
			}
			elseif(! empty($minus["cat_ids"]))
			{
				$inner = " INNER JOIN {faq_category_rel} as r ON r.element_id=e.id"
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
				$inner .= " INNER JOIN {tags} AS t ON t.element_id=e.id AND t.element_type='element' AND t.module_name='faq' AND t.tags_name_id=".$t["id"];
			}

			if ($sort == "rand")
			{
				$max_count = DB::query_result(
					"SELECT COUNT(DISTINCT e.id) FROM {faq} as e"
					.$inner
					.($this->diafan->configmodules('where_access_element', 'faq') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='faq' AND a.element_type='element'" : "")
					." WHERE e.[act]='1' AND e.trash='0'"
					.($this->diafan->_site->module == 'faq' && $this->diafan->_route->show ? " AND e.id<>".$this->diafan->_route->show : '')
					.$where
					.($often ? " AND often='1'" : "")
					." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
					.($this->diafan->configmodules('where_access_element', 'faq') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''), $time, $time
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
				$rows = DB::query_range_fetch_all(
					"SELECT e.id, e.[name],e.[anons], e.[text], e.timeedit, e.site_id, e.created FROM {faq} AS e"
					.$inner
					.($this->diafan->configmodules('where_access_element', 'faq') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='faq' AND a.element_type='element'" : "")
					." WHERE e.[act]='1' AND e.trash='0'"
					.($this->diafan->_site->module == 'faq' && $this->diafan->_route->show ? " AND e.id<>".$this->diafan->_route->show : '')
					.$where
					.($often ? " AND often='1'" : "")
					." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
					.($this->diafan->configmodules('where_access_element', 'faq') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
					." GROUP BY e.id"
					.($sort == "date" ? ' ORDER BY e.created DESC, e.id DESC' : ''),
					$time, $time,
					$sort == "rand" ? $rand : 0,
					$sort == "rand" ? 1     : $count
				);
				$result["rows"] = array_merge($result["rows"], $rows);
			}
			$this->elements($result["rows"]);

			$result["name"] = $often ? $this->diafan->_('Часто задаваемые вопросы', false) : '';
			// если категория только одна, задаем ссылку на нее
			if(! empty($result["rows"]) && $one_cat_id)
			{
				$cat = DB::query_fetch_array("SELECT [name], site_id, id FROM {faq_category} WHERE id=%d LIMIT 1", $one_cat_id);

				$result["name"]     = $often ? $result["name"] : $cat["name"];
				$result["link_all"] = $this->diafan->_route->link($cat["site_id"], $cat["id"], 'faq', 'cat');
				$result["category"] = true;
			}
			// если раздел сайта только один, то задаем ссылку на него
			elseif(! empty($result["rows"]) && count($site_ids) == 1)
			{
				$result["name"]     = $often ? $result["name"] : DB::query_result("SELECT [name] FROM {site} WHERE id=%d LIMIT 1", $site_ids[0]);
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
				$this->diafan->_cache->save($result, $cache_meta, "faq");
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
	 * Генерирует данные для шаблонной функции: блок связанных вопросов
	 *
	 * @param integer $count количество вопросов
	 * @return array
	 */
	public function show_block_rel($count)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name" => "block_rel",
			"count" => $count,
			"lang_id" => _LANG,
			"element_id" => $this->diafan->_route->show,
			"access" => ($this->diafan->configmodules('where_access_element', 'faq') || $this->diafan->configmodules('where_access_cat', 'faq') ? $this->diafan->_users->role_id : 0),
			"time" => $time
		);

		if (! $result = $this->diafan->_cache->get($cache_meta, "faq"))
		{
			$result["rows"] = DB::query_range_fetch_all(
			"SELECT e.id, e.[name], e.[anons], e.[text], e.created, e.timeedit, e.site_id FROM {faq} AS e"
			. " INNER JOIN {faq_rel} AS r ON e.id=r.rel_element_id AND r.element_id=%d"
			.($this->diafan->configmodules("rel_two_sided") ? " OR e.id=r.element_id AND r.rel_element_id=".$this->diafan->_route->show : '')
			. ($this->diafan->configmodules('where_access_element', 'faq') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='faq' AND a.element_type='element'" : "")
			. " WHERE e.[act]='1' AND e.trash='0'"
			." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
			.($this->diafan->configmodules('where_access_element', 'faq') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			. " GROUP BY e.id"
			. ' ORDER BY e.created DESC',
			$this->diafan->_route->show, $time, $time, 0, $count
			);
			$this->elements($result["rows"]);
			$this->diafan->_cache->save($result, $cache_meta, "faq");
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
	 * Форматирует данные о вопросам и ответах для списка
	 *
	 * @param array $rows все полученные из базы данных элементы
	 * @return void
	 */
	public function elements(&$rows)
	{
		if (empty($this->result["timeedit"]))
		{
			$this->result["timeedit"] = '';
		}
		foreach ($rows as &$row)
		{
			if ($this->diafan->configmodules('page_show', 'faq', $row["site_id"]))
			{
				$this->diafan->_route->prepare($row["site_id"], $row["id"], "faq");
			}
		}
		foreach ($rows as &$row)
		{
			if ($row["timeedit"] < $this->result["timeedit"])
			{
				$this->result["timeedit"] = $row["timeedit"];
			}
			unset($row["timeedit"]);

			if ($this->diafan->configmodules('page_show', 'faq', $row["site_id"]))
			{
				$row["link"] = $this->diafan->_route->link($row["site_id"], $row["id"], "faq");
			}
			else
			{
				$row["link"] = '';
			}

			if($this->diafan->configmodules('count_letter_list', 'faq', $row["site_id"]))
			{
				$row["anons"] = $this->diafan->short_text($row["anons"], $this->diafan->configmodules('count_letter_list', 'faq', $row["site_id"]));
				$row["text"]  = ! empty($row["text"]) ? $this->diafan->short_text($row["text"], $this->diafan->configmodules('count_letter_list', 'faq', $row["site_id"])) : '';
			}
			$row["date"]  = $this->format_date($row['created'], "faq", $row["site_id"]);
			unset($row["created"]);
		}
	}

	/**
	 * Генерирует данные для вывода вопросов на странице тега
	 *
	 * @param integer $element_ids номера вопроса
	 * @return array
	 */
	public function tags($element_ids)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		$this->result["rows"] = DB::query_fetch_all("SELECT e.id, e.[anons], e.[text], e.timeedit, e.site_id, e.created FROM {faq} AS e"
		. " WHERE e.[act]='1' AND e.trash='0' AND e.id IN (%s)"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)",
		implode(',', $element_ids), $time, $time);

		$this->elements($this->result["rows"]);

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
		return $this->result;
	}

	/**
	 * Генерирует данные для вывода найденных модулем "Поиск" вопросов
	 *
	 * @param integer $element_ids номера вопроса
	 * @return array
	 */
	public function search($element_ids)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		$this->result["rows"] = DB::query_fetch_all("SELECT e.id, e.[anons], e.[text], e.timeedit, e.site_id, e.created FROM {faq} AS e"
		. " WHERE e.[act]='1' AND e.trash='0' AND e.id IN (%s)"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)",
		implode(',', $element_ids), $time, $time);

		$this->elements($this->result["rows"]);

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
		return $this->result;
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
		"SELECT c.id, c.[name], c.[anons], c.site_id FROM {faq_category} AS c"
		.($this->diafan->configmodules('where_access_cat') ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='faq' AND a.element_type='cat'" : "")
		." WHERE c.[act]='1' AND c.parent_id=%d AND c.trash='0' AND c.site_id=%d"
		.($this->diafan->configmodules('where_access_cat') ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY c.id ORDER BY c.sort ASC, c.id ASC", $parent_id, $this->diafan->_site->id
		);

		foreach ($children as &$child)
		{
			$this->diafan->_route->prepare($child["site_id"], $child["id"], "faq", "cat");
		}

		foreach ($children as &$child)
		{
			$child["link"] = $this->diafan->_route->link($child["site_id"], $child["id"], 'faq', 'cat');
			$child["rows"] = array();
			if($this->diafan->configmodules("count_child_list"))
			{
				if ($this->diafan->configmodules("children_elements"))
				{
					$cat_ids = $this->diafan->get_children($child["id"], "faq_category");
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
		"SELECT e.id, e.[name], e.timeedit, e.[anons], e.[text], e.site_id, e.created FROM {faq} AS e"
		." INNER JOIN {faq_category_rel} AS r ON e.id=r.element_id"
		.($this->diafan->configmodules('where_access_element') ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='faq' AND a.element_type='element'" : "")
		." WHERE r.cat_id IN (%s) AND e.[act]='1' AND e.trash='0'"
		." AND e.date_start<=%d AND (e.date_finish=0 OR e.date_finish>=%d)"
		.($this->diafan->configmodules('where_access_element') ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." GROUP BY e.id ORDER BY e.created DESC, e.id DESC",
		implode(',', $cat_ids), $time, $time, 0, $this->diafan->configmodules("count_child_list")
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
		$this->diafan->_rating->prepare($row["id"], 'faq', 'cat');
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
		$row["name"] = $this->diafan->_useradmin->get($row["name"], 'name', $row["id"], 'faq_category', _LANG);
		if(! empty($row["anons"]))
		{
			$row["anons"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["anons"]), 'anons', $row["id"], 'faq_category', _LANG);
		}
		if(! empty($row["text"]))
		{
			$row["text"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["text"]), 'text', $row["id"], 'faq_category', _LANG);
		}
		$row["rating"] = $this->diafan->_rating->get($row["id"], 'faq', 'cat', (! empty($row["site_id"]) ? $row["site_id"] : 0));
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
		if(! empty($row["img"]))
		{
			foreach($row["img"] as &$i)
			{
				$i["src"] = BASE_PATH.$i["src"];
				foreach($i["vs"] as &$v)
				{
					$v = BASE_PATH.$v;
				}
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
		$this->diafan->_tags->prepare($row["id"], 'faq');
		$this->diafan->_rating->prepare($row["id"], 'faq');
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
			$row["name"] = $this->diafan->_useradmin->get($row["name"], 'name', $row["id"], 'faq', _LANG);
		}
		if (! empty($row["text"]))
		{
			$row["text"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["text"]), 'text', $row["id"], 'faq', _LANG);
		}
		if(! empty($row["anons"]))
		{
			$row["anons"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["anons"]), 'anons', $row["id"], 'faq', _LANG);
		}
		if (! empty($row["date"]))
		{
			$row["date"] = $this->diafan->_useradmin->get($row["date"], 'created', $row["id"], 'faq');
		}

		$row["tags"] =  $this->diafan->_tags->get($row["id"], 'faq', 'element', (! empty($row["site_id"]) ? $row["site_id"] : 0));
		$row["rating"] = $this->diafan->_rating->get($row["id"], 'faq', 'element', (! empty($row["site_id"]) ? $row["site_id"] : 0));
	}
}
