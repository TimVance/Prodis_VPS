<?php
/**
 * Подключение для работы с тегами
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
 * Tags_inc
 */
class Tags_inc extends Model
{
	/**
	 * Выводит подключенные к элементу тэги
	 *
	 * @param integer $element_id номер элемента, к котором прикреплены теги, по умолчанию текущий элемент модуля
	 * @param strint $module_name название модуля, по умолчанию модуль, прикрепленный к текущей странице сайта
	 * @param string $element_type тип данных
	 * @param integer $site_id страница сайта, к которой прикреплен элемент, по умолчанию текущая страница сайта
	 * @return string|boolean false
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

		if (! $this->diafan->configmodules("tags", $module_name, $site_id) || ! $element_id)
			return false;

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
						$this->cache["tags"][$pr_module_name][$pr_element_type][$pr_element_id] = array();
						$values[] = $pr_element_id;
						$v_array[] = '%d';
					}
					$wh[] = "t.element_type='%s' AND t.element_id".(count($arr) > 1 ? " IN (".implode(",", $v_array).")" : "=%d");
				}
				$where[] = "t.module_name='%h' AND (".implode(" OR ", $wh).")";
			}
			$rows = DB::query_fetch_all("SELECT n.[name], n.id, t.module_name, t.element_id, t.element_type FROM {tags_name} AS n"
				." INNER JOIN {tags} AS t ON t.tags_name_id=n.id"
				." WHERE (".implode(" OR ", $where).") AND n.trash='0' AND t.trash='0'"
				." ORDER BY n.sort ASC", $values);
			foreach ($rows as $row)
			{
				$this->diafan->_route->prepare(0, $row["id"], "tags");
			}
			foreach ($rows as $row)
			{
				$row["link"] = $this->diafan->_route->link(0, $row["id"], "tags");
				$this->cache["tags"][$row["module_name"]][$row["element_type"]]['e'.$row["element_id"]][] = $row;
			}
			unset($this->cache["prepare"]);
		}
	
		return $this->diafan->_tpl->get('get', 'tags', $this->cache["tags"][$module_name][$element_type]['e'.$element_id]);
	}

	/**
	 * Запоминает данные элемента, которому нужно будет вывести теги
	 *
	 * @param integer $element_id номер элемента, к котором прикреплены теги, по умолчанию текущий элемент модуля
	 * @param strint $module_name название модуля, по умолчанию модуль, прикрепленный к текущей странице сайта
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

		if(isset($this->cache["tags"][$module_name][$element_type]['e'.$element_id]))
		{
			return;
		}
		if(! isset($this->cache["prepare"][$module_name][$element_type]['e'.$element_id]))
		{
			$this->cache["prepare"][$module_name][$element_type]['e'.$element_id] = $element_id;
		}
	}

	/**
	 * Удаляет теги для одного или нескольких элементов
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
		DB::query("DELETE FROM {tags} WHERE module_name='%s' AND element_type='%s' AND element_id".$where, $module_name, $element_type, $value);
	}

	/**
	 * Удаляет все теги модуля
	 *
	 * @param string $module_name название модуля
	 * @return void
	 */
	public function delete_module($module_name)
	{
		DB::query("DELETE FROM {trash} WHERE module_name='tags' AND element_id IN (SELECT id FROM {tags} WHERE module_name='%s')", $module_name);
		DB::query("DELETE FROM {tags} WHERE module_name='%s'", $module_name);
	}
}