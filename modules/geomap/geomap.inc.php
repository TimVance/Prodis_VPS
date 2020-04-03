<?php
/**
 * Подключение модуля
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
 * Geomap_inc
 */
class Geomap_inc extends Model
{
	/**
	 * Показывает точку на карте для элемента
	 *
	 * @param integer $element_id номер элемента модуля, по умолчанию текущий элемент модуля
	 * @param string $module_name название модуля, по умолчанию текущий модуль
	 * @param string $element_type тип данных
	 * @param integer $site_id страница сайта, к которой прикреплен элемент, по умолчанию текущая страница сайта
	 * @return string
	 */
	public function get($element_id = 0, $module_name = '', $element_type = 'element', $site_id = 0)
	{
		if(! $module_name)
		{
			$module_name = $this->diafan->_site->module;
		}
		if(! $element_id)
		{
			$element_id = ($element_type == 'element' ? $this->diafan->_route->show : $this->diafan->_route->$element_type);
		}
		if(! $site_id)
		{
			$site_id = $this->diafan->_site->id;
		}

		if(! $this->diafan->configmodules("geomap_".$element_type, $module_name, $site_id))
		{
			return false;
		}
		$this->prepare($element_id, $module_name, $element_type);
		if(! empty($this->cache["prepare"]))
		{
			$where = array();
			$values = array();
			foreach ($this->cache["prepare"] as $pr_module_name => $array)
			{
				$values[] = $pr_module_name;
				$wh = array();
				foreach ($array as $pr_element_type => $arr)
				{
					$values[] = $pr_element_type;
					$v_array = array();
					foreach ($arr as $pr_element_id => $a)
					{
						$this->cache["geomap"][$pr_module_name][$pr_element_type][$pr_element_id] = 0;
						$values[] = $pr_element_id;
						$v_array[] = '%d';
					}
					$wh[] = "element_type='%s' AND element_id".(count($arr) > 1 ? " IN (".implode(",", $v_array).")" : "=%d");
				}
				$where[] = "module_name='%h' AND (".implode(" OR ", $wh).")";
			}
			$rows = DB::query_fetch_all("SELECT * FROM {geomap} WHERE (".implode(" OR ", $where).") AND trash='0'", $values);
			foreach ($rows as $row)
			{
				$this->cache["geomap"][$row["module_name"]][$row["element_type"]]['e'.$row["element_id"]] = $row["point"];
			}
			unset($this->cache["prepare"]);
		}
		if(! empty($this->cache["geomap"][$module_name][$element_type]['e'.$element_id]))
		{
			$result["point"] = $this->cache["geomap"][$module_name][$element_type]['e'.$element_id];
			$backend = $this->diafan->configmodules("backend", "geomap");
			if($backend)
			{
				include(Custom::path('modules/geomap/backend/'.$backend.'/geomap.'.$backend.'.view.get.php'));
			}
		}
	}

	/**
	 * Запоминает данные элемента, которому нужно будет вывести точки на карте
	 *
	 * @param integer $element_id номер элемента модуля, по умолчанию текущий элемент модуля
	 * @param strint $module_name название модуля, по умолчанию текущий модуль
	 * @param string $element_type тип данных
	 * @return void
	 */
	public function prepare($element_id = 0, $module_name = '', $element_type = 'element')
	{
		if(! $module_name)
		{
			$module_name = $this->diafan->_site->module;
		}
		if(! $element_id)
		{
			$element_id = ($element_type == 'element' ? $this->diafan->_route->show : $this->diafan->_route->$element_type);
		}

		if(isset($this->cache["geomap"][$module_name][$element_type]['e'.$element_id]))
		{
			return;
		}
		if(! isset($this->cache["prepare"][$module_name][$element_type]['e'.$element_id]))
		{
			$this->cache["prepare"][$module_name][$element_type]['e'.$element_id] = $element_id;
		}
	}

	/**
	 * Редактирование/добавление точки на карте
	 *
	 * @param integer $element_id номер элемента модуля при редактировании точки
	 * @param string $module_name название модуля при редактировании точки
	 * @param string $element_type тип данных
	 * @param integer $site_id страница сайта, к которой прикреплен элемент, по умолчанию текущая страница сайта
	 * @return string
	 */
	public function add($element_id = 0, $module_name = '', $element_type = 'element', $site_id = 0)
	{
		if(! $site_id)
		{
			$site_id = $this->diafan->_site->id;
		}
		if(! $this->diafan->configmodules("geomap_".$element_type, $module_name, $site_id))
		{
			return false;
		}
		$this->prepare($element_id, $module_name, $element_type);
		if(! empty($this->cache["prepare"]))
		{
			$where = array();
			$values = array();
			foreach ($this->cache["prepare"] as $pr_module_name => $array)
			{
				$values[] = $pr_module_name;
				$wh = array();
				foreach ($array as $pr_element_type => $arr)
				{
					$values[] = $pr_element_type;
					$v_array = array();
					foreach ($arr as $pr_element_id => $a)
					{
						$this->cache["geomap"][$pr_module_name][$pr_element_type][$pr_element_id] = 0;
						$values[] = $pr_element_id;
						$v_array[] = '%d';
					}
					$wh[] = "element_type='%s' AND element_id".(count($arr) > 1 ? " IN (".implode(",", $v_array).")" : "=%d");
				}
				$where[] = "module_name='%h' AND (".implode(" OR ", $wh).")";
			}
			$rows = DB::query_fetch_all("SELECT * FROM {geomap} WHERE (".implode(" OR ", $where).") AND trash='0'", $values);
			foreach ($rows as $row)
			{
				$this->cache["geomap"][$row["module_name"]][$row["element_type"]]['e'.$row["element_id"]] = $row["point"];
			}
			unset($this->cache["prepare"]);
		}
		$result["point"] = '';
		if(! empty($this->cache["geomap"][$module_name][$element_type]['e'.$element_id]))
		{
			$result["point"] = $this->cache["geomap"][$module_name][$element_type]['e'.$element_id];
		}
		$backend = $this->diafan->configmodules("backend", "geomap");
		if($backend)
		{
			include(Custom::path('modules/geomap/backend/'.$backend.'/geomap.'.$backend.'.view.add.php'));
		}
	}

	/**
	 * Сохранение точки на карте
	 *
	 * @param integer $element_id номер элемента модуля
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @param integer $site_id страница сайта
	 * @return string
	 */
	public function save($element_id = 0, $module_name = '', $element_type = 'element', $site_id = 0)
	{
		if(! $this->diafan->configmodules("geomap_".$element_type, $module_name, $site_id))
		{
			return false;
		}
		$row = DB::query_fetch_array("SELECT * FROM {geomap} WHERE element_id=%d AND module_name='%h' AND element_type='%h' AND trash='0'", $element_id, $module_name, $element_type);
		if($row)
		{
			if(! empty($_POST["geomap_point"]))
			{
				DB::query("UPDATE {geomap} SET point='%h' WHERE id=%d", $_POST["geomap_point"], $row["id"]);
			}
			else
			{
				DB::query("DELETE FROM {geomap} WHERE id=%d", $row["id"]);
			}
		}
		else
		{
			if(! empty($_POST["geomap_point"]))
			{
				DB::query("INSERT INTO {geomap} (element_id, module_name, element_type, point) VALUES (%d, '%h', '%h', '%h')", $element_id, $module_name, $element_type, $_POST["geomap_point"]);
			}
		}
	}

	/**
	 * Удаляет точки для одного или нескольких элементов
	 *
	 * @param integer|array $element_ids номер одного или нескольких элементов
	 * @param strint $module_name название модуля
	 * @param string $element_type тип данных
	 * @return void
	 */
	public function delete($element_ids, $module_name, $element_type = 'element')
	{
		if(is_array($element_ids))
		{
			$where = " IN (%s)";
			$value = preg_replace('/[^0-9,]+/', '', implode(",", $element_ids));
		}
		else
		{
			$where = "=%d";
			$value = $element_ids;
		}
		DB::query("DELETE FROM {geomap} WHERE module_name='%s' AND element_type='%s' AND element_id".$where, $module_name, $element_type, $value);
	}

	/**
	 * Удаляет все точки элементов модуля
	 *
	 * @param string $module_name название модуля
	 * @return void
	 */
	public function delete_module($module_name)
	{
		DB::query("DELETE FROM {trash} WHERE module_name='geomap' AND element_id IN (SELECT id FROM {geomap} WHERE module_name='%s')", $module_name);
		DB::query("DELETE FROM {geomap} WHERE module_name='%s';", $module_name);
	}
}