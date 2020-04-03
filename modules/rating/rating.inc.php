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
 * Rating_inc
 */
class Rating_inc extends Model
{
	/**
	 * Показывает рейтинг для элемента
	 *
	 * @param integer $element_id номер элемента модуля, по умолчанию текущий элемент модуля
	 * @param string $module_name название модуля, по умолчанию текущий модуль
	 * @param string $element_type тип данных
	 * @param integer $site_id страница сайта, к которой прикреплен элемент, по умолчанию текущая страница сайта
	 * @param boolean $full полная информация
	 * @return string
	 */
	function get($element_id = 0, $module_name = '', $element_type = 'element', $site_id = 0, $full = false)
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

		if(! $this->diafan->configmodules("rating".($element_type != 'element' ? "_".$element_type : ""), $module_name, $site_id))
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
						$this->cache["rating"][$pr_module_name][$pr_element_type][$pr_element_id] = 0;
						$values[] = $pr_element_id;
						$v_array[] = '%d';
					}
					$wh[] = "element_type='%s' AND element_id".(count($arr) > 1 ? " IN (".implode(",", $v_array).")" : "=%d");
				}
				$where[] = "module_name='%h' AND (".implode(" OR ", $wh).")";
			}
			$rows = DB::query_fetch_all("SELECT * FROM {rating} WHERE (".implode(" OR ", $where).") AND trash='0'", $values);
			foreach ($rows as $row)
			{
				$this->cache["rating"][$row["module_name"]][$row["element_type"]]['e'.$row["element_id"]] = $row;
			}
			if ($this->diafan->_session->id && $this->diafan->configmodules('security', 'rating') == 3)
			{
				$values[] = $this->diafan->configmodules('only_user', 'rating') ? $this->diafan->_users->id : $this->diafan->_session->id;
				$rows = DB::query_fetch_all("SELECT * FROM {log_note}"
					." WHERE (".implode(" OR ", $where).") AND include_name='rating' AND session_id='%s'",
					$values);
				foreach ($rows as $row)
				{
					$this->cache["rating_log"][$row["element_id"].$row["module_name"].$row["element_type"]] = true;
				}
			}
			unset($this->cache["prepare"]);
		}

		$this->result["module_name"] = $module_name;
		$this->result["element_id"]  = $element_id;
		$this->result["element_type"]  = $element_type;
		$this->result["full"]  = $full;
		
		$this->result["rating"] = round($this->cache["rating"][$module_name][$element_type]['e'.$element_id]["rating"]);
		
		$this->result["overall_rating"] = round($this->cache["rating"][$module_name][$element_type]['e'.$element_id]["rating"] * $this->cache["rating"][$module_name][$element_type]['e'.$element_id]["count_votes"]);
		
		$this->result["count_votes"] = $this->cache["rating"][$module_name][$element_type]['e'.$element_id]["count_votes"];

		$this->result["disabled"] = false;

		if ($this->diafan->configmodules('only_user', 'rating') && ! $this->diafan->_users->id)
		{
			$this->result["disabled"] = true;
		}

		if ($this->diafan->_session->id && $this->diafan->configmodules('security', 'rating') == 3
		   && ! empty($this->cache["rating_log"][$element_id.$module_name.$element_type]))
		{
			$this->result["disabled"] = true;
		}

		if ($this->diafan->_session->id && $this->diafan->configmodules('security', 'rating') == 4
		   && ! empty($_SESSION["rating"][$element_id.$module_name.$element_type]))
		{
			$this->result["disabled"] = true;
		}

		return $this->diafan->_tpl->get('get', 'rating', $this->result);
	}

	/**
	 * Запоминает данные элемента, которому нужно будет вывести рейтинг
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

		if(isset($this->cache["rating"][$module_name][$element_type]['e'.$element_id]))
		{
			return;
		}
		if(! isset($this->cache["prepare"][$module_name][$element_type]['e'.$element_id]))
		{
			$this->cache["prepare"][$module_name][$element_type]['e'.$element_id] = $element_id;
		}
	}

	/**
	 * Удаляет рейтинг для одного или нескольких элементов
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
		DB::query("DELETE FROM {rating} WHERE module_name='%s' AND element_type='%s' AND element_id".$where, $module_name, $element_type, $value);
	}

	/**
	 * Удаляет все рейтинги элементов модуля
	 *
	 * @param string $module_name название модуля
	 * @return void
	 */
	public function delete_module($module_name)
	{
		DB::query("DELETE FROM {trash} WHERE module_name='rating' AND element_id IN (SELECT id FROM {rating} WHERE module_name='%s')", $module_name);
		DB::query("DELETE FROM {rating} WHERE module_name='%s';", $module_name);
	}
}