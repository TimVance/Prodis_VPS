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
 * Menu_model
 */
class Menu_model extends Model
{
	/**
	 * Генерирует данные для шаблонной функции: блок меню
	 *
	 * @param integer $id номер меню
	 * @return array
	 */
	public function show_block($id)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name"    => "block",
			"id"      => $id,
			"lang_id" => _LANG,
			"time"    => $time,
			"access" => ($this->diafan->configmodules('where_access', 'all') ? $this->diafan->_users->role_id : 0),
		);

		if (! $this->result = $this->diafan->_cache->get($cache_meta, "menu"))
		{
			if (! $id)
			{
				$id = 1;
			}
			$row_menu = DB::query_fetch_array(
					"SELECT m.[name], m.show_all_level, m.hide_parent_link, m.show_title, m.current_link, m.only_image, m.menu_template FROM {menu_category} AS m"
					.($this->diafan->configmodules('where_access_cat', 'menu') ? " LEFT JOIN {access} AS a ON a.element_id=m.id AND a.module_name='menu' AND a.element_type='cat'" : "")
					." WHERE m.id=%d AND m.[act]='1' AND m.trash='0'"
					.($this->diafan->configmodules('where_access_cat', 'menu') ? " AND (m.access='0' OR m.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
					." LIMIT 1",
					$id
				);
			if (! $row_menu)
			{
				return false;
			}
			
			$this->result['menu_category_site_rel'] = DB::query_fetch_value("SELECT site_id FROM {menu_category_site_rel} WHERE element_id=%d", $id, "site_id");

			if ($row_menu["show_title"])
			{
				$this->result["name"] = $row_menu["name"];
			}
			$this->result["show_all_level"]   = $row_menu["show_all_level"];
			$this->result["hide_parent_link"] = $row_menu["hide_parent_link"];
			$this->result["current_link"]     = $row_menu["current_link"];
			$this->result["only_image"]     = $row_menu["only_image"];
			$this->result["menu_template"]     = $row_menu["menu_template"];

			$this->result["rows"] = DB::query_fetch_key_array(
				"SELECT m.id, m.[name], m.[text], m.module_name, m.element_type, m.element_id, m.parent_id, m.othurl, m.attributes, m.target_blank FROM {menu} AS m"
				.($this->diafan->configmodules('where_access', 'all') ? " LEFT JOIN {access} AS a ON a.element_id=m.element_id AND a.module_name=m.module_name AND a.element_type=m.element_type" : "")
				." WHERE m.cat_id=%d AND m.[act]='1' AND m.trash='0'"
				." AND m.date_start<=%d AND (m.date_finish=0 OR m.date_finish>=%d)"
				.($this->diafan->configmodules('where_access', 'all') ? " AND (m.access='0' OR m.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
				." GROUP BY m.id ORDER BY m.sort ASC, m.id ASC", 
				$id, $time, $time, "parent_id"
			);
			foreach ($this->result["rows"] as $parent_id => &$rows)
			{
				foreach ($rows as &$row)
				{
					$this->diafan->_route->prepare(0, $row["element_id"], $row["module_name"], $row["element_type"]);
					if($this->diafan->configmodules("images_element", "menu"))
					{
						$this->diafan->_images->prepare($row["id"], "menu");
					}
				}
			}
			foreach ($this->result["rows"] as $parent_id => &$rows)
			{
				foreach ($rows as &$row)
				{
					$row["link"] = $this->diafan->_route->link(0, $row["element_id"], $row["module_name"], $row["element_type"]);

					if($this->diafan->configmodules("images_element", "menu"))
					{
						$images = $this->diafan->_images->get('large', $row["id"], 'menu', 'element', 0, $row["name"], 0, 1);
						$row["img"] = $images ? $images[0] : '';
					}
					if(! empty($row['img']) && ! empty($this->result["only_image"]))
					{
						$row['name'] = '';
					}
					$row["active"] = false;
					$row["active_child"] = false;
					if($row["attributes"])
					{
						$row["attributes"] = ' '.$row["attributes"];
					}
					if($row["target_blank"])
					{
						$row["attributes"] .= ' target="_blank"';
					}
					$row["children"] = 0;
					if(! empty($this->result["rows"][$row["id"]]))
					{
						$row["children"] = count($this->result["rows"][$row["id"]]);
					}
				}
			}
			$this->diafan->_cache->save($this->result, $cache_meta, "menu");
		}

		if(! empty($this->result['menu_category_site_rel']) && ! in_array($this->diafan->_site->id, $this->result['menu_category_site_rel']) && ! in_array(0, $this->result['menu_category_site_rel']))
		{
		    return false;
		}
		$this->result["parent_id"] = 0;
		$this->result["level"] = 1;

		$this->menu_active_chain();

		return $this->result;
	}

	/**
	 * Выделяет пункты меню активной цепи (текущая страница и ее родители)
	 *
	 * @return void
	 */
	private function menu_active_chain()
	{
		$current_link = $this->diafan->_route->current_link(array('page', 'sort'));
		$current_link_find = false;
		$parents = array();
		foreach ($this->result["rows"] as $parent_id => $rows)
		{
			foreach ($rows as $i => $row)
			{
				$this->result["rows"][$parent_id][$i]["name"] = $this->diafan->_useradmin->get($row["name"], 'name', $row["id"], 'menu', _LANG);
				if (empty($row["othurl"]) && $row["link"] == $current_link)
				{
					$this->result["rows"][$parent_id][$i]["active"] = true;
					$current_link_find = $row["id"];
				}
				$parents[$row["id"]] = $row["parent_id"];
			}
		}
		if (! $current_link_find)
		{
			foreach ($this->result["rows"] as $parent_id => $rows)
			{
				foreach ($rows as $i => $row)
				{
					$element_type = $row["element_type"];
					if($element_type == 'element')
					{
						$element_type = 'show';
					}
					if ($row["element_id"] == $this->diafan->_route->$element_type
						&& $row["module_name"] == $this->diafan->_site->module)
					{
						$this->result["rows"][$parent_id][$i]["active_child"] = true;
						$current_link_find = $row["id"];
						continue;
					}
				}
				if ($current_link_find)
					continue;
			}
		}
		if (! $current_link_find)
		{
			foreach ($this->result["rows"] as $parent_id => $rows)
			{
				foreach ($rows as $i => $row)
				{
					if ($row["element_id"] == $this->diafan->_site->id
						&& $row["element_type"] == 'element'
						&& $row["module_name"] == 'site')
					{
						$this->result["rows"][$parent_id][$i]["active_child"] = true;
						$current_link_find = $row["id"];
						continue;
					}
				}
				if ($current_link_find)
					continue;
			}
		}
		if ($current_link_find)
		{
			$parent_id = $parents[$current_link_find];
			while ($parent_id > 0)
			{
				$id = $parent_id;
				$parent_id = 0; 
				if(array_key_exists($id,$parents)) {
				    $parent_id = $parents[$id];
				    foreach ($this->result["rows"][$parent_id] as $i => $row)
				    {
					    if ($row["id"] == $id)
					    {
						    $this->result["rows"][$parent_id][$i]["active_child"] = true;
						    continue;
					    }
				    }
				}
				
			}
		}
	}
}
