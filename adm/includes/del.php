<?php
/**
 * @package    DIAFAN.CMS
 *
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
 * Del_admin
 *
 * Удаление элемента
 */
class Del_admin extends Diafan
{
	private $current_trash = 0;

	/**
	 * Удаляет элемент
	 *
	 * @return void
	 */
	public function del()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(getenv('HTTP_REFERER'));
		}

		//проверка прав пользователя на удаление
		if (! $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(getenv('HTTP_REFERER'));
		}

		if(DB::query_result("SELECT COUNT(*) FROM {trash}") > 1000)
		{
			$this->diafan->redirect(URL.'error10/'.$this->diafan->get_nav);
			return;
		}

		$redirect = URL;

		if (! empty($_POST["id"]))
		{
			$ids = array($_POST["id"]);
			if ($this->diafan->variable_list('plus'))
			{
				$parent_id = DB::query_result("SELECT parent_id FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $_POST["id"]);
				if($parent_id && $this->diafan->_route->parent != $parent_id)
				{
					$redirect = str_replace('parent'.$this->diafan->_route->parent.'/', '', $redirect).'parent'.$parent_id.'/';
				}
			}
		}
		else
		{
			$ids = $_POST["ids"];
		}
		foreach($ids as $id)
		{
			$id = intval($id);
			if($id)
			{
				$del_ids[] = $id;
			}
		}
		if(! empty($del_ids))
		{
			$element_type = $this->diafan->element_type();
			if($element_type == 'param')
			{
				$table = $this->diafan->_admin->module.'_param';
			}
			elseif($element_type == 'cat')
			{
				$table = $this->diafan->table;
			}
			else
			{
				$table = $this->diafan->table_element_type($this->diafan->_admin->module, $element_type);
			}

			foreach($del_ids as $del_id)
			{
				$this->current_trash = 0;
				$this->current_trash = $this->del_or_trash($table, $del_id);
				$this->del_rows(array($del_id), $table, $this->diafan->_admin->module, $element_type);
			}
			if (! empty($this->cache["parent"]))
			{
				foreach($this->cache["parent"] as $table)
				{
					// пересчитывает поле count_children
					$rows = DB::query_fetch_all("SELECT t.id, t.count_children, COUNT(p.id) as cnt FROM {".$table."} AS t LEFT JOIN {".$table."_parents}  AS p ON p.parent_id=t.id AND p.trash='0' WHERE t.trash='0' GROUP BY t.id");
					foreach ($rows as $row)
					{
						if($row["count_children"] != $row["cnt"])
						{
							DB::query("UPDATE {".$table."} SET count_children=%d WHERE id=%d", $row["cnt"], $row["id"]);
						}
					}
				}
			}

			if ($_POST["action"] == "trash")
			{
				$rows = DB::query_fetch_all("SELECT t.id, t.count_children, COUNT(p.id) as cnt FROM {trash} AS t INNER JOIN {trash_parents}  AS p ON p.parent_id=t.id GROUP BY t.id");
				foreach ($rows as $row)
				{
					if($row["count_children"] != $row["cnt"])
					{
						DB::query("UPDATE {trash} SET count_children=%d WHERE id=%d", $row["cnt"], $row["id"]);
					}
				}
			}
			$this->diafan->_cache->delete("", $this->diafan->_admin->module);
		}
		$this->diafan->redirect($redirect.$this->diafan->get_nav);
	}

	/**
	 * Удаляет элемент
	 *
	 * @param array $ids идентификаторы удаляемых элементов
	 * @param string $table таблица
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @return void
	 */
	private function del_rows($ids, $table, $module_name, $element_type = '')
	{
		if ($this->diafan->element_type() != $element_type)
		{
			if(! isset($this->cache["class_".$element_type]))
			{
				$e_type = '';
				if($element_type == 'cat')
				{
					$e_type = 'category';
				}
				elseif($element_type == 'import')
				{
					$e_type = 'importexport.element';
				}
				elseif($element_type == 'import_category')
				{
					$e_type = 'importexport.category';
				}
				elseif($element_type == 'express_fields')
				{
					$e_type = str_replace('_', '.', $element_type);
					Custom::inc('modules/'.$module_name.'/admin/'.$module_name.'.admin.'.$e_type.'.php');
					$name_func_module = 'inc_file_'.$module_name;
					if (function_exists($name_func_module))
					{
						$name_class_module = $name_func_module($this->diafan);
						$name_class_module = strtolower($name_class_module);
						if(0 === strpos($name_class_module, $module_name.'_admin_'.$element_type))
						{
							$e_type .= str_replace('_', '.', substr($name_class_module, strlen($module_name.'_admin_'.$element_type)));
						}
						else
						{
							if ($diafan->_route->cat) $e_type = $e_type.'.element';
							else $e_type = $e_type.'.category';
						}
					}
					else
					{
						if ($diafan->_route->cat)
						{
							Custom::inc('modules/'.$module_name.'/admin/'.$module_name.'.admin'.$e_type.'.element'.'.php');
							inc_file_express_modules( $diafan );
							$e_type = $e_type.'.element';
						}
						else
						{
							Custom::inc('modules/'.$module_name.'/admin/'.$module_name.'.admin'.$e_type.'.category'.'.php');
							$e_type = $e_type.'.category';
						}
					}
				}
				elseif($element_type != 'element')
				{
					$e_type = $element_type;
				}
				Custom::inc('modules/'.$module_name.'/admin/'.$module_name.'.admin'.($e_type ? '.'.$e_type : '').'.php');
				$class = ucfirst($module_name).'_admin'.($e_type ? '_'.str_replace('.', '_', $e_type) : '');
				$this->cache["class_".$element_type] = new $class($this->diafan);
				$this->cache["class_".$element_type]->diafan->_frame = $this->cache["class_".$element_type];
			}
			$class = &$this->cache["class_".$element_type];
		}
		else
		{
			$class = &$this->diafan;
		}

		if ($class->variable_list("plus"))
		{
			$chs = DB::query_fetch_value("SELECT DISTINCT(element_id) FROM {%s_parents} WHERE parent_id IN (%s) AND element_id NOT IN (%s)", $table,  implode(",", $ids),  implode(",", $ids), "element_id");
			$ids = array_merge($ids, $chs);
			$this->del_or_trash_where($table, "id IN (".implode(",", $chs).")");
			$this->del_or_trash_where($table."_parents", "element_id  IN (".implode(",", $ids).")");
			if (empty($this->cache["parent"]) || ! in_array($table, $this->cache["parent"]))
			{
				$this->cache["parent"][] = $table;
			}
		}
		$this->include_modules('delete', array($ids, $module_name, $element_type));

		if($element_type != 'param')
		{
			$this->del_or_trash_where("rewrite", "element_id IN (".implode(",", $ids).") AND module_name='".$module_name."' AND element_type='".$element_type."'");

			$this->del_or_trash_where("redirect", "element_id IN (".implode(",", $ids).") AND module_name='".$module_name."' AND element_type='".$element_type."'");

			$this->del_or_trash_where("access", "element_id IN (".implode(",", $ids).") AND module_name='".$module_name."' AND element_type='".$element_type."'");

			$this->del_or_trash_where("site_dynamic_element", "element_id IN (".implode(",", $ids).") AND module_name='".$module_name."' AND element_type='".$element_type."'");
		}
		// функция удаления, описанная в модуле
		if(is_callable(array(&$class, 'delete')))
		{
			call_user_func_array(array(&$class, 'delete'), array($ids));
		}

		if($class->config("category"))
		{
			$cat_element_type = $element_type == 'cat' ? 'element' : str_replace('_category', '', $element_type);
			$cat_elements = DB::query_fetch_value("SELECT id FROM {%s} WHERE cat_id IN (".implode(",", $ids).")", str_replace('_category', '', $table), "id");
			if($cat_elements)
			{
				$this->del_or_trash_where(str_replace('_category', '', $table), "id IN (".implode(",", $cat_elements).")");
				$this->del_rows($cat_elements, str_replace('_category', '', $table), $module_name, $cat_element_type);
				if ($class->config("category_rel"))
				{
					$this->del_or_trash_where($table."_category_rel", "cat_id IN (".implode(",", $ids).")");
				}
			}
		}

		if ($class->config("element_multiple"))
		{
			$this->del_or_trash_where($table."_category_rel", "element_id IN (".implode(",", $ids).") AND trash='0'");
		}

		// удаляет значения списка для полей конструктора
		if($element_type == 'param')
		{
			$this->del_or_trash_where($table."_element", "param_id IN (".implode(",", $ids).")");
			$select_ids = DB::query_fetch_value("SELECT id FROM {%s_select} WHERE param_id IN (".implode(",", $ids).")", $table, "id");
			if($select_ids)
			{
				$this->del_or_trash_where("rewrite", "element_id IN ('".implode(',', $select_ids).") AND module_name='".$module_name."' AND element_type='param'");
			}

			$this->del_or_trash_where($table."_select",  "param_id IN (".implode(",", $ids).")");
			if ($class->is_variable("category"))
			{
				$this->del_or_trash_where($table."_category_rel", "element_id IN (".implode(",", $ids).")");
			}
		}
	}

	/**
	 * Помечает строку на удаление или удаляет строку из базы данных
	 *
	 * @param string $table название таблицы
	 * @param integer $del_id номер удаляемой записи
	 * @return integer|boolean true
	 */
	public function del_or_trash($table, $del_id)
	{
		if ($_POST["action"] == "trash")
		{
			DB::query("UPDATE {".$table."} SET trash='1' WHERE id=%d", $del_id);
			$id = DB::query("INSERT INTO {trash} (table_name, module_name, element_id, created, parent_id, user_id) VALUES ('%s', '%s', '%d', '%d', '%d', '%d')", $table, $this->diafan->_admin->module, $del_id, time(), $this->current_trash, $this->diafan->_users->id);
			DB::query("INSERT INTO {trash_parents} (`element_id`, `parent_id`) VALUES (%d, %d)", $id, $this->current_trash);
			return $id;
		}
		else
		{
			$trash_id = DB::query_result("SELECT id FROM {trash} WHERE element_id=%d AND table_name='%s' LIMIT 1", $del_id, $table);
			DB::query("DELETE FROM {trash} WHERE id=%d", $trash_id);
			DB::query("DELETE FROM {trash_parents} WHERE parent_id=%d OR element_id=%d", $trash_id, $trash_id);
			DB::query("DELETE FROM {".$table."} WHERE id=%d", $del_id);
		}
		return true;
	}

	/**
	 * Помечает строки на удаление или удаляет строки из базы данных
	 *
	 * @param string $table название таблицы
	 * @param string $del_where SQL-условие для удаление записей в базе данных
	 * @return void
	 */
	public function del_or_trash_where($table, $del_where)
	{
		if ($_POST["action"] == "trash")
		{
			list($module_name, ) = explode('_', $table);
			$rows = DB::query_fetch_all("SELECT * FROM {".$table."} WHERE ".$del_where." AND trash='0'");
			foreach ($rows as $row)
			{
				$id = DB::query("INSERT INTO {trash} (table_name, module_name, element_id, created, parent_id, user_id) VALUES ('%s', '%s', '%d', '%d', '%d', '%d')", $table, $module_name, $row["id"], time(), $this->current_trash, $this->diafan->_users->id);
				DB::query("INSERT INTO {trash_parents} (`element_id`, `parent_id`) VALUES (%d, %d)", $id, $this->current_trash);
			}
			DB::query("UPDATE {".$table."} SET trash='1' WHERE ".$del_where);

		}
		else
		{
			$del_ids = DB::query_result("SELECT GROUP_CONCAT(id SEPARATOR ',') FROM {".$table."} WHERE ".$del_where);
			if($del_ids)
			{
				$trash_ids = DB::query_fetch_value("SELECT id FROM {trash} WHERE element_id IN (%s) AND table=%h", $del_ids, $table, "id");
				DB::query("DELETE FROM {trash} WHERE id IN (%s)", implode(",", $trash_ids));
				DB::query("DELETE FROM {trash_parents} WHERE parent_id IN (%s) OR element_id IN (%s)", implode(",", $trash_ids), implode(",", $trash_ids));
				DB::query("DELETE FROM {".$table."} WHERE ".$del_where);
			}
		}
	}

	/**
	 * Подключает удаление информации, описанной в модуле
	 *
	 * @param string $method название метода
	 * @param array $args аргументы для метода
	 * @return void
	 */
	public function include_modules($method, $args)
	{
		if (! isset($this->cache["include_modules"]))
		{
			$this->cache["include_modules"] = array();
			foreach ($this->diafan->installed_modules as $module)
			{
				if (Custom::exists('modules/'.$module.'/admin/'.$module.'.admin.inc.php'))
				{
					Custom::inc('modules/'.$module.'/admin/'.$module.'.admin.inc.php');
					$class = ucfirst($module).'_admin_inc';
					if (method_exists($class, $method))
					{
						$this->cache["include_modules"][] = new $class($this->diafan);
					}
				}
			}
		}
		foreach ($this->cache["include_modules"] as &$obj)
		{
			call_user_func_array (array(&$obj, $method), $args);
		}
	}

	/**
	 * Удаляет элементы из корзины
	 *
	 * @param string $table название таблицы
	 * @param integer $id номер удаляемого элемента
	 * @param integer $trash_id номер записи в корзине, с которой связано удаление
	 * @param boolean $tree удаление учитывает дерева связей
	 * @return void
	 */
	public function del_from_trash($table, $id, $trash_id, $tree = true)
	{
		list($module) = explode('_', $table);
		if (Custom::exists('modules/'.$module.'/admin/'.$module.'.admin.inc.php'))
		{
			Custom::inc('modules/'.$module.'/admin/'.$module.'.admin.inc.php');
			$func = 'del_from_trash';
			$class = ucfirst($module).'_admin_inc';
			if (method_exists($class, $func))
			{
				$class_admin_inc = new $class($this->diafan);
				$class_admin_inc->del_from_trash($id, $table);
			}
		}
		DB::query("DELETE FROM {".$table."} WHERE id=%d", $id);
		if($trash_id !== false)
		{
			DB::query("DELETE FROM {trash} WHERE id=%d", $trash_id);
			DB::query("DELETE FROM {trash_parents} WHERE parent_id=%d OR element_id=%d", $trash_id, $trash_id);
		}

		if($tree && $trash_id !== false)
		{
			$rows = DB::query_fetch_all("SELECT element_id, table_name, id FROM {trash} WHERE parent_id=%d", $trash_id);
			foreach ($rows as $row)
			{
				$this->diafan->del_from_trash($row["table_name"], $row["element_id"], $row["id"]);
			}
		}
	}
}
