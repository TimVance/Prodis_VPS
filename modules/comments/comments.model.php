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
 * Comments_model
 */
class Comments_model extends Model
{
	/**
	 * Генерирует данные для шаблонной функции: блок комментариев
	 * 
	 * @param integer $count количество комментариев
	 * @param array $element_ids элементы
	 * @param array $modules модули
	 * @param string $element_type тип элемента
	 * @param string $sort сортировка date - по дате, rand - случайно
	 * @return array
	 */
	public function show_block($count, $element_ids, $modules, $element_type, $sort)
	{
		$where = '';
		foreach ($modules as $i => $module)
		{
			$module = preg_replace('/[^a-z_]+/', '', trim($module));
			if(! $module)
			{
				unset($modules[$i]);
			}
		}
		if($modules)
		{
			$where .= " AND module_name IN ('".implode("','", $modules)."')";
		}
		foreach ($element_ids as $i => $element_id)
		{
			$element_id = intval(trim($element_id));
			if(empty($element_id))
			{
				unset($element_ids[$i]);
			}
		}
		if(! empty($element_ids))
		{
			$where .= " AND element_id IN (".implode(",", $element_ids).")";
		}

		if ($sort == "rand")
		{
			$max_count = DB::query_result(
				"SELECT COUNT(*) FROM {comments}"
				." WHERE act='1' AND trash='0'".$where
				." AND element_type='%s'", $element_type
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

		$where_list = "show_in_list='1'";

		$params_list = $this->get_params(array("module" => "comments", "where" => $where_list));
		
		$prepare_elements = array();

		$result["rows"] = array();
		foreach ($rands as $rand)
		{
			$rows = DB::query_range_fetch_all(
				"SELECT created, user_id, text, id, module_name, element_id, element_type FROM {comments}"
				." WHERE act='1' AND trash='0'".$where
				." AND element_type='%s'"
				.($sort == "date" ? ' ORDER BY created DESC, id DESC' : ''),
				$element_type,
				$sort == "rand" ? $rand : 0,
				$sort == "rand" ? 1 : $count
			);
			foreach ($rows as &$row)
			{
				if ($this->diafan->configmodules('user_name', 'comments') && $row["user_id"])
				{
					$this->prepare_author($row["user_id"]);
				}
				$row["text"] = $this->diafan->short_text($row["text"], 300);
				$this->diafan->_route->prepare(0, $row["element_id"], $row["module_name"], $row["element_type"]);
				$this->diafan->_comments->prepare_param_values($row["id"]);
				$prepare_elements[$row["module_name"]][] = $row["element_id"];
			}
			$result["rows"] = array_merge($result["rows"], $rows);
		}
		foreach($prepare_elements as $p_module_name => $p_ids)
		{
			$elements[$p_module_name] = DB::query_fetch_key_value("SELECT id, [name] FROM {%s} WHERE id IN (%s)", $p_module_name, implode(",", $p_ids), "id", "name");
		}
		foreach ($result["rows"] as &$row)
		{
			if($this->diafan->_site->timeedit < $row['created'])
			{
				$this->diafan->_site->timeedit = $row['created'];
			}
			$row['date'] = $this->diafan->_useradmin->get($this->format_date($row['created'], "comments"), 'created', $row["id"], 'comments');
	
			if ($this->diafan->configmodules('user_name', 'comments') && $row["user_id"])
			{
				$row["name"] = $this->get_author($row["user_id"]);
			}
	
			$row["text"] = $this->diafan->_useradmin->get($row["text"], 'text', $row["id"], 'comments');
			$row["params"] = $this->diafan->_comments->get_param_values($row["id"], $params_list);
			$row["link"] = $this->diafan->_route->link(0, $row["element_id"], $row["module_name"], $row["element_type"]);
			$row["theme_name"] = ! empty($elements[$row["module_name"]][$row["element_id"]]) ? $elements[$row["module_name"]][$row["element_id"]] : '';
		}

		$result["view_rows"] = 'rows_block';

		return $result;
	}
}