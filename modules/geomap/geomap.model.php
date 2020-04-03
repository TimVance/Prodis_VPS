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
 * Geomap_model
 */
class Geomap_model extends Model
{
	/**
	 * Генерирует список найденных страниц
	 * 
	 * @return void
	 */
	public function show()
	{
		if (! isset($_GET["module"]))
		{
			$_GET["module"] = '';
		}
		$this->result = array();

		$rows_search = DB::query_fetch_all("SELECT * FROM {geomap} WHERE trash='0' AND element_type='element'".($_GET["module"] ? " AND module_name='%s'" : ''), $_GET["module"]);
		$rows_module = array();
		$this->result["modules"] = array();
		foreach ($rows_search as $row)
		{
			$rows_module[$row["module_name"]][] = $row["element_id"];
			$points[$row["module_name"].$row["element_id"]] = $row["point"];
		}
		$this->result["rows"] = array();
		foreach($rows_module as $table_name => $ids)
		{
			if($table_name == 'site')
			{
				$rows = DB::query_fetch_all("SELECT r.[name], r.id FROM {site} AS r"
				.($this->diafan->configmodules('where_access', 'all') ? " LEFT JOIN {access} AS a ON a.element_id=r.id AND a.module_name='site' AND a.element_type='element'" : "")
				." WHERE r.id IN (%s) AND r.trash='0' AND r.[act]='1'"
				.($this->diafan->configmodules('where_access', 'all') ? " AND (r.access='0' OR r.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
				." GROUP BY r.id LIMIT ".count($ids), implode(',', $ids));
				foreach($rows as $row)
				{
					$this->diafan->_route->prepare($row["id"], $row["id"], $table_name);
				}
				foreach($rows as $row)
				{
					$row["link"] = $this->diafan->_route->link($row["id"]);
					$row["name"] = $this->diafan->short_text($row["name"], 20);
					$row["point"] = $points['site'.$row["id"]];
					$this->result["rows"][] = $row;
				}
			}
			else
			{
				$rows = DB::query_fetch_all("SELECT r.[name], r.id, r.site_id FROM {%s} AS r"
				.($this->diafan->configmodules('where_access', 'all') ? " LEFT JOIN {access} AS a ON a.element_id=r.id AND a.module_name='".$table_name."' AND a.element_type='element'" : "")
				." WHERE r.id IN (%s) AND r.trash='0' AND r.[act]='1'"
				.($this->diafan->configmodules('where_access', 'all') ? " AND (r.access='0' OR r.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
				." GROUP BY r.id LIMIT ".count($ids), $table_name, implode(',', $ids));
				foreach($rows as $row)
				{
					$this->diafan->_route->prepare($row["site_id"], $row["id"], $table_name);
				}
				foreach($rows as $row)
				{
					$row["link"] = $this->diafan->_route->link($row["site_id"], $row["id"], $table_name);
					$row["name"] = $this->diafan->short_text($row["name"], 20);
					$row["point"] = $points[$table_name.$row["id"]];
					$this->result["rows"][] = $row;
				}
			}
		}
		$this->result["backend"] = $this->diafan->configmodules("backend", "geomap");
		$this->result["view"] = 'show';
	}
}