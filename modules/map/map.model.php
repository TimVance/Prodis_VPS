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
 * Map_model
 */
class Map_model extends Model
{
	/**
	 * Генерирует данные для карты сайта
	 * 
	 * @return void
	 */
	public function show_list()
	{
		$this->map_parent_id();
		$this->result["view"] = 'list';
	}

	/**
	* Формирует URL страниц сайта
	* 
	* @param integer $parent_id номер страницы-родителя
	* @param integer $margin отступ слева
	* @return void
	*/
	private function map_parent_id($parent_id = 0, $margin = 0)
	{
		if ($parent_id != 0)
		{
			$margin += 10;
		}
		$rows = DB::query_fetch_all(
				"SELECT s.id, s.[name], s.module_name FROM {site} AS s"
				.($this->diafan->configmodules('where_access_element', 'site') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='site' AND a.element_type='element'" : "")
				." WHERE s.[act]='1' AND s.parent_id='%d' AND s.trash='0' AND s.map_no_show='0'"
				.($this->diafan->configmodules('where_access_element', 'site') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
				." GROUP BY s.id ORDER BY s.sort ASC, s.id DESC",
				$parent_id
			);
		foreach ($rows as $row)
		{ 
			$link = $this->diafan->_route->link($row["id"]);

			$this->result["rows"][] = array("margin" => $margin, "link" => $link, "name" => $row["name"]);
			if ($this->diafan->configmodules("cat", $row["module_name"], $row["id"]))
			{
				$this->map_module_category(0, $row["id"], $margin, $row["module_name"]);
			}
			else
			{
				//$this->map_module_element(0, $row["id"], $margin, $row["module_name"]);	
			}
			$this->map_parent_id($row["id"], $margin);
		}
	}

	/**
	* Формирует URL страниц категорий модуля
	* 
	* @param integer $id номер родителя
	* @param integer $site_id номер страницы
	* @param integer $margin отступ слева
	* @param string $module название модуля
	* @return void
	*/
	private function map_module_category($id, $site_id, $margin, $module)
	{
		$margin += 20;
		$rows = DB::query_fetch_all(
			"SELECT c.id, c.[name], c.site_id FROM {".$module."_category} AS c"
			.($this->diafan->configmodules('where_access_cat', $module) ? " LEFT JOIN {access} AS a ON a.element_id=c.id AND a.module_name='".$module."' AND a.element_type='cat'" : "")
			." WHERE c.[act]='1' AND c.parent_id='%d' AND c.site_id=%d"
			." AND c.trash='0' AND c.map_no_show='0'"
			.($this->diafan->configmodules('where_access_cat', $module) ? " AND (c.access='0' OR c.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." GROUP BY c.id ORDER BY c.sort ASC, c.id ASC",
			$id, $site_id
		);
		foreach ($rows as $row)
		{
			$link = $this->diafan->_route->link($row["site_id"], $row["id"], $module, 'cat');
			$this->result["rows"][] = array(
				"margin" => $margin,
				"link"   => $link,
				"name"   => $row["name"]
			);
			//$this->map_module_element($row["id"], $site_id, $margin, $module);
			$this->map_module_category($row["id"], $site_id, $margin, $module);
		}
	}

	/**
	* Формирует URL страниц элементов модуля
	* 
	* @param integer $id номер категории
	* @param integer $site_id номер страницы
	* @param integer $margin отступ слева
	* @param string $module название модуля
	* @return void
	private function map_module_element($id, $site_id, $margin, $module)
	{
		$margin += 20;
		$rows = DB::query_fetch_all(
			"SELECT e.id, e.[name], e.site_id FROM {".$module."} AS e"
			.($this->diafan->configmodules('where_access_element', $module) ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='".$module."' AND a.element_type='element'" : "")
			." WHERE e.[act]='1' AND e.site_id=%d".($id ? " AND cat_id='%d'" : "")
			." AND e.trash='0' AND e.map_no_show='0'"
			.($this->diafan->configmodules('where_access_element', $module) ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			." GROUP BY e.id ORDER BY e.sort ASC, e.id ASC",
			$site_id, $id
		);
		foreach ($rows as $row)
		{
			$link = $this->diafan->_route->link($row["site_id"], $row["id"], $module);
			$this->result["rows"][] = array(
				"margin" => $margin,
				"link"   => $link,
				"name"   => $row["name"]
			);
		}
	}
	*/
}