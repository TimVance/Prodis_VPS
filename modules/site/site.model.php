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
 * Site_model
 */
class Site_model extends Model
{
	/**
	 * Генерирует данные для
	 * шаблонного тега <insert name="show_block" module="site" id="номер_страницы" [template="шаблон"]>:
	 * выводит блок на сайте
	 *
	 * @param integer $id номер блока на сайте
	 * @return array
	 */
	public function show_block($id)
	{
		$time = mktime(1, 0, 0);
		$row = DB::query_fetch_array(
			"SELECT s.[text], s.[name], s.title_no_show FROM {site_blocks} AS s"
			.($this->diafan->configmodules('where_access_blocks', 'site') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='site' AND a.element_type='blocks'" : "")
			." INNER JOIN {site_blocks_site_rel} AS r ON r.element_id=s.id"
			." WHERE s.id=%d AND s.[act]='1' AND s.trash='0'"
			." AND (r.site_id=%d OR r.site_id=0)"
			." AND s.date_start<=".$time." AND (s.date_finish=0 OR s.date_finish>=".$time.")"
			.($this->diafan->configmodules('where_access_blocks', 'site') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." LIMIT 1",
			$id,
			$this->diafan->_site->id
		);
		if (! $row)
		{
			return;
		}
		if($row["text"])
		{
			$result["text"] = $this->diafan->_useradmin->get($this->diafan->_tpl->htmleditor($row["text"]), 'text', $id, 'site_blocks', _LANG);
		}
		if(! empty($result["text"]))
		{
			if(! $row["title_no_show"])
			{
				$result["name"] = $row["name"];
			}
			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Генерирует данные для
	 * шаблонного тега show_dynamic динамический блок на сайте
	 *
	 * @param integer $id номер блока на сайте
	 * @param integer $element_id номер элемента, для которого будет выведено значение блока
	 * @param string $module_name модуль элемента, для которого будет выведено значение блока
	 * @param string $element_type тип элемента, для которого будет выведено значение блока
	 * @return array
	 */
	public function show_dynamic($id, $element_id, $module_name, $element_type)
	{
		if(! $element_type)
		{
			$element_type = 'element';
		}
		if(! $module_name)
		{
			$module_name = $this->diafan->_site->module;
			if(! $module_name)
			{
				$module_name = 'site';
			}
		}
		if(! $element_id)
		{
			if($this->diafan->_site->module)
			{
				if($this->diafan->_route->show)
				{
					$element_id = $this->diafan->_route->show;
				}
				elseif($this->diafan->_route->cat)
				{
					$element_type = 'cat';
					$element_id = $this->diafan->_route->cat;
				}
				elseif($this->diafan->_route->param)
				{
					$element_type = 'param';
					$element_id = $this->diafan->_route->param;
				}
				elseif($this->diafan->_route->brand)
				{
					$element_type = 'brand';
					$element_id = $this->diafan->_route->brand;
				}
			}

			if(empty($element_id))
			{
				$module_name = 'site';
				$element_id = $this->diafan->_site->id;
			}
		}
		$time = mktime(1, 0, 0);
		$row = DB::query_fetch_array(
			"SELECT s.[name], s.title_no_show, s.type, s.id FROM {site_dynamic} AS s"
			.($this->diafan->configmodules('where_access_dynamic', 'site') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='site' AND a.element_type='dynamic'" : "")
			." INNER JOIN {site_dynamic_module} AS m ON m.dynamic_id=s.id"
			." WHERE s.id=%d AND s.[act]='1' AND s.trash='0'"
			." AND s.date_start<=".$time." AND (s.date_finish=0 OR s.date_finish>=".$time.")"
			." AND (m.module_name='%h' OR m.module_name='') AND (m.element_type='%h'"
			.($element_type == 'element' ? " OR m.element_type='cat'" : '')
			."OR m.element_type='')"
			.($this->diafan->configmodules('where_access_dynamic', 'site') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." LIMIT 1",
			$id,
			$module_name,
			$element_type
		);
		if (! $row)
		{
			return;
		}
		switch($row["type"])
		{
			case 'text':
			case 'textarea':
			case 'editor':
				$lang = _LANG;
				break;

			default:
				$lang = $this->diafan->_languages->site;
				break;
		}
		$where = " dynamic_id=%d  AND module_name='%h' AND (element_id=%d AND element_type='%s'";
		if($this->diafan->_site->module_parents)
		{
			$where .= " OR element_id IN (".implode(',', $this->diafan->_site->module_parents).") AND element_type='".$element_type."' AND parent='1'";
		}
		if($module_name == 'site')
		{
			if($this->diafan->_site->parents)
			{
				$where .= " OR element_id IN (".implode(',', $this->diafan->_site->parents).") AND element_type='".$element_type."' AND parent='1'";
			}
		}
		if($this->diafan->_site->module_cats)
		{
			$where .= " OR element_id IN (".implode(',', $this->diafan->_site->module_cats).") AND element_type='cat' AND category='1'";
		}
		$where .= ')';
		if($module_name != 'site')
		{
			$where .= " OR dynamic_id=%d AND module_name='site' AND element_type='element' AND (element_id=".$this->diafan->_site->id;
			if($this->diafan->_site->parents)
			{
				$where .= " OR element_id IN (".implode(',', $this->diafan->_site->parents).") AND parent='1'";
			}
			$where .= ')';
		}
		$ds = DB::query_fetch_all(
			"SELECT value".$lang." AS value, id, module_name FROM {site_dynamic_element}"
			." WHERE ".$where,
			$row["id"], $module_name, $element_id, $element_type);
		if(count($ds) > 1)
		{
			foreach($ds as $d)
			{
				if($d["module_name"] != 'site')
					$dynamic = $d;
			}
		}
		else
		{
			if($ds)
			{
				$dynamic = $ds[0];
			}
		}
		if(! empty($dynamic))
		{
			switch($row["type"])
			{
				case 'editor':
					$dynamic["value"] = $this->diafan->_tpl->htmleditor($dynamic["value"]);
					break;

				case 'date':
				case 'datetime':
					$dynamic["value"] = $this->format_date($dynamic["value"], $dynamic["module_name"]);
					break;
			}
			$result["text"] = $this->diafan->_useradmin->get($dynamic["value"], "value", $dynamic["id"], 'site_dynamic_element', $lang, $row["type"]);
		}
		else
		{
			$result["text"] = '';
		}
		if(! empty($result["text"]))
		{
			if(! $row["title_no_show"])
			{
				$result["name"] = $row["name"];
			}
			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Генерирует данные для
	 * шаблонного тега <insert name="show_links" module="site" [template="шаблон"]>:
	 * выводит ссылки на страницы нижнего уровня, принадлежащие текущей странице
	 *
	 * @return array
	 */
	public function show_links()
	{
		$cache_meta = array(
				"name"     => "page",
				"id"       => $this->diafan->_site->id,
				"lang_id" => _LANG,
				"access" => ($this->diafan->configmodules('where_access_element', 'site') ? $this->diafan->_users->role_id : 0),
			);
		$page = $this->diafan->_cache->get($cache_meta, 'site');
		if (! isset($page["links"]))
		{
			$page["links"] = DB::query_fetch_all(
				"SELECT s.id, s.[name] FROM {site} AS s"
				.($this->diafan->configmodules('where_access_element', 'site') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='site' AND a.element_type='element'" : "")
				." WHERE s.parent_id=%d AND s.trash='0' AND s.[act]='1' AND s.map_no_show='0'"
				.($this->diafan->configmodules('where_access_element', 'site') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
				." GROUP BY s.id ORDER BY s.sort ASC",
				$this->diafan->_site->id
			);
			foreach ($page["links"] as &$row)
			{
				$row["link"] = $this->diafan->_route->link($row["id"]);
			}
			//сохранение кеша
			$this->diafan->_cache->save($page, $cache_meta, 'site');
		}
		foreach ($page["links"] as &$row)
		{
			$this->diafan->_route->prepare(0, $row["id"], 'site', 'element');
		}
		foreach ($page["links"] as &$row)
		{
			$row["name"] = $this->diafan->_useradmin->get($row["name"], 'name', $row["id"], 'site', _LANG);
		}
		return $page["links"];
	}

	/**
	 * Генерирует данные для
	 * шаблонного тега <insert name="show_previous_next" module="site" [template="шаблон"]>:
	 * выводит ссылки на предыдующую и следующую страницы
	 *
	 * @return array
	 */
	public function show_previous_next()
	{
		if ($this->diafan->_site->hide_previous_next || $this->diafan->_site->module && ($this->diafan->_route->cat || $this->diafan->_route->show) || $this->diafan->_site->id == 1)
		{
			return;
		}

		$cache_meta = array(
				"name"     => "page",
				"id"       => $this->diafan->_site->id,
				"lang_id" => _LANG,
				"access" => ($this->diafan->configmodules('where_access_element', 'site') ? $this->diafan->_users->role_id : 0),
			);
		$page = $this->diafan->_cache->get($cache_meta, 'site');
		if (! isset($page["previous"]) && ! isset($page["next"]))
		{
			$page["previous"] = array();
			$page["next"]     = array();

			$sort = DB::query_result("SELECT sort FROM {site} WHERE id=%d LIMIT 1", $this->diafan->_site->id);
			$previous = DB::query_fetch_array(
				"SELECT s.id, s.[name] FROM {site} AS s"
				.($this->diafan->configmodules('where_access_element', 'site') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='site' AND a.element_type='element'" : "")
				." WHERE s.[act]='1' AND s.trash='0' AND s.id<>1 AND s.map_no_show='0'"
				.($this->diafan->configmodules('where_access_element', 'site') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
				." AND (s.sort<%d OR s.sort=%d AND s.id<%d) AND s.parent_id=%d ORDER BY s.sort DESC, id DESC LIMIT 1",
				$sort, $sort, $this->diafan->_site->id, $this->diafan->_site->parent_id);

			$next = DB::query_fetch_array(
				"SELECT s.id, s.[name] FROM {site} AS s"
				.($this->diafan->configmodules('where_access_element', 'site') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='site' AND a.element_type='element'" : "")
				." WHERE s.[act]='1' AND s.trash='0' AND s.id<>1 AND s.map_no_show"
				.($this->diafan->configmodules('where_access_element', 'site') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
				." AND (s.sort>%d OR s.sort=%d AND s.id>%d) AND s.parent_id=%d ORDER BY s.sort ASC, id ASC LIMIT 1",
				$sort, $sort, $this->diafan->_site->id, $this->diafan->_site->parent_id);
			if ($previous)
			{
				$this->diafan->_route->prepare(0, $previous["id"], 'site', 'element');
			}
			if ($next)
			{
				$this->diafan->_route->prepare(0, $next["id"], 'site', 'element');
			}
			if ($previous)
			{
				$previous["link"] = $this->diafan->_route->link($previous["id"]);
				$page["previous"] = $previous;
			}
			if ($next)
			{
				$next["link"] = $this->diafan->_route->link($next["id"]);
				$page["next"] = $next;
			}
			//сохранение кеша
			$this->diafan->_cache->save($page, $cache_meta, 'site');
		}
		if ($page["previous"])
		{
			$page["previous"]["name"] = $this->diafan->_useradmin->get($page["previous"]["name"], 'name', $page["previous"]["id"], 'site', _LANG);
		}
		if ($page["next"])
		{
			$page["next"]["name"] = $this->diafan->_useradmin->get($page["next"]["name"], 'name', $page["next"]["id"], 'site', _LANG);
		}
		$result["previous"] = $page["previous"];
		$result["next"] = $page["next"];
		return $result;
	}

	/**
	 * Генерирует данные для
	 * шаблонного тега <insert name="show_images" module="site" [template="шаблон"]>:
	 * выводит изображения, прикрепленные к странице сайта
	 * (если в конфигурации модуля «Страницы сайта» включен параметры «Использовать изображения»)
	 *
	 * @return array
	 */
	public function show_images()
	{
		$result["id"] = $this->diafan->_site->id;
		$result["images"] = array();
		if ($this->diafan->configmodules('images_element', 'site'))
		{
			$cache_meta = array(
					"name"     => "page",
					"id"       => $this->diafan->_site->id,
					"lang_id"  => _LANG,
					"user_id"  => $this->diafan->_users->id ? true : false
				);
			$page = $this->diafan->_cache->get($cache_meta, 'site');
			if (! isset($page["images"]))
			{
				$result["images"] = $this->diafan->_images->get('medium', $this->diafan->_site->id, 'site', 'element',  $this->diafan->_site->id, $this->diafan->_site->name, 0, 0, 'large');
				//сохранение кеша
				$this->diafan->_cache->save($page, $cache_meta, 'site');
			}
			else
			{
				$result["images"] = $page["images"];
			}
		}
		return $result;
	}
}
