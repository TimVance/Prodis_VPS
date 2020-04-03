<?php
/**
 * Подключение модуля к административной части других модулей
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
 * Menu_admin_inc
 */
class Menu_admin_inc extends Diafan
{
	/**
	 * Редактирование поля "Показывать в меню"
	 * 
	 * @return void
	 */
	public function edit()
	{
		$show_in_menu = array();
		if (! $this->diafan->is_new)
		{
			$show_in_menu = DB::query_fetch_value("SELECT cat_id FROM {menu} WHERE module_name='%h' AND element_id=%d AND element_type='%s' AND trash='0' AND [act]='1'", $this->diafan->_admin->module, $this->diafan->id, $this->diafan->element_type(), "cat_id");
		}

		echo '
		<div class="unit">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>';
			$rows = DB::query_fetch_all("SELECT id, [name] FROM {menu_category} WHERE trash='0' AND [act]='1' ORDER BY sort ASC");
			foreach ($rows as $row)
			{
				echo '<input type="checkbox" name="menu_cat_ids[]" id="input_menu_cat_ids_'.$row["id"].'" value="'.$row["id"].'"'.(in_array($row["id"], $show_in_menu) ? ' checked' : '').' class="label_full"> <label for="input_menu_cat_ids_'.$row["id"].'">'.$row["name"].'</label>';
			}
			echo '
		</div>';
	}

	/**
	 * Сохранение поля "Показывать в меню"
	 * 
	 * @return void
	 */
	public function save()
	{
		if (! $this->diafan->save_sort)
		{
			$this->diafan->get_sort();
		}
		$save = array(
			"element_id" => $this->diafan->id,
			"module_name" => $this->diafan->_admin->module, 
			"element_type" => $this->diafan->element_type(),
			"site_id" => $this->diafan->get_site_id(),
			"is_new" => $this->diafan->is_new,
			"parent_id" => ! empty($_POST["parent_id"]) ? $_POST["parent_id"] : '',
			"name" => $_POST["name"],
			"old_name" => $this->diafan->values('name'),
			"access" => ! empty($_POST["access"]) ? 1 : 0,
			"old_access" => $this->diafan->values("access", (empty($_POST["access"]) ? 1 : 0)),
			"sort" => $this->diafan->save_sort,
			"act" => ! empty($_POST["act"]) || ! $this->diafan->is_variable('act') ? 1 : 0,
			"old_act" => $this->diafan->values('act'),
			"cat_id" => ! empty($_POST["cat_id"]) ? $_POST["cat_id"] : 0,
			"date_start" => ! empty($_POST["date_start"]) ? $this->diafan->unixdate($_POST["date_start"]) : 0,
			"old_date_start" => $this->diafan->values("date_start"),
			"date_finish" => ! empty($_POST["date_finish"]) ? $this->diafan->unixdate($_POST["date_finish"]) : 0,
			"old_date_finish" => $this->diafan->values("date_finish")
		);
		if($this->diafan->table == 'site' && $this->diafan->id == 1)
		{
			$save["act"] = 1;
		}
		$menu_cat_ids = ! empty($_POST["menu_cat_ids"]) ? $_POST["menu_cat_ids"] : array();

		$this->save_menu($save, $menu_cat_ids);
	}

	/**
	 * Сохранение пункта в меню
	 * 
	 * @param array $save данные об элементе
	 * @param array $menu_cat_ids категории меню, в которых нужно отображить пункт
	 * @return boolean true
	 */
	public function save_menu($save, $menu_cat_ids)
	{
		$edit_menu_cat_ids = array();

		//определяем неактивные меню
		if(! isset($this->cache["menu_category_noact"]))
		{
			$this->cache["menu_category_noact"] = DB::query_fetch_value("SELECT id FROM {menu_category} WHERE [act]='0'", "id");
		}

		$show_in_menu = array();
		if (! $save["is_new"])
		{
			$show_in_menu_element = DB::query_fetch_value("SELECT cat_id FROM {menu} WHERE module_name='%s' AND element_id=%d AND element_type='%s' AND trash='0'", $save["module_name"], $save["element_id"], $save["element_type"], "cat_id");
			// исключаем пункты из неактивного меню
			foreach($show_in_menu_element as $id)
			{
				if(! in_array($id, $this->cache["menu_category_noact"]))
				{
					$show_in_menu[] = $id;
				}
			}
		}
		if (! $menu_cat_ids)
		{
			// если не отмечено ни одно меню, но существовали пункты раньше, удаляет их
			if ($show_in_menu)
			{
				foreach ($show_in_menu as $menu_cat_id)
				{
					$this->diafan->_menu->delete($save["element_id"], $save["module_name"], $save["element_type"], $menu_cat_id);

					if(! in_array($menu_cat_id, $edit_menu_cat_ids))
					{
						$edit_menu_cat_ids[] = $menu_cat_id;
					}
				}
			}
		}
		else
		{
			// просматривает новые пункты
			foreach ($menu_cat_ids as $menu_cat_id)
			{
				// если пункта не существовало раньше, добавляет
				if (! in_array($menu_cat_id, $show_in_menu))
				{
					$edit_menu = true;

					// ищет пункт в меню - родитель
					$parent_id = 0;
					// родитель текущего элемента
					if ($save["parent_id"])
					{
						$parent_id = DB::query_result("SELECT id FROM {menu} WHERE module_name='%h' AND element_id=%d AND element_type='%s' AND cat_id=%d AND trash='0' LIMIT 1", $save["module_name"], $save["parent_id"], $save["element_type"], $menu_cat_id);
					}
					// категория текущего элемента
					if (! $parent_id && $save["cat_id"])
					{
						$parent_id = DB::query_result("SELECT id FROM {menu} WHERE module_name='%s' AND element_id=%d AND element_type='cat' AND cat_id=%d AND trash='0' LIMIT 1", $save["module_name"], $save["cat_id"], $menu_cat_id);
					}
					// страница сайта текущего элемента
					if (! $parent_id && $save["site_id"])
					{
						$parent_id = DB::query_result("SELECT id FROM {menu} WHERE module_name='site' AND element_id=%d AND element_type='element' AND cat_id=%d AND trash='0' LIMIT 1", $save["site_id"], $menu_cat_id);
					}

					$fields = array("[name], module_name, element_id, element_type, cat_id, parent_id, access, date_start, date_finish, sort, [act]");
					$mask = array("'%h', '%s', %d, '%s', %d, %d, '%d', %d, %d, %d, '%d'");
					$values = array(
						$save["name"],
						$save["module_name"],
						$save["element_id"],
						$save["element_type"],
						$menu_cat_id,
						$parent_id,
						$save["access"],
						$save["date_start"],
						$save["date_finish"],
						$save["sort"],
						$save["act"]
					);

					// добавляет к запросу поля активность и имя из других языковых версий
					if(count($this->diafan->_languages->all) > 1)
					{
						$table = $this->diafan->table_element_type($save["module_name"], $save["element_type"]);
						$element = DB::query_fetch_array("SELECT * FROM {%h} WHERE id=%d LIMIT 1", $table, $save["element_id"]);
						foreach ($this->diafan->_languages->all as $l)
						{
							if ($l["id"] != _LANG)
							{
								$fields[] = 'act'.$l["id"];
								$mask[] = "'%d'";
								if(isset($element["act".$l["id"]]))
								{
									$values[] = $element["act".$l["id"]];
									
								}
								elseif(isset($element["act"]))
								{
									$values[] = $element["act"];
								}
								else
								{
									$values[] = '1';
								}
								if(isset($element["name".$l["id"]]))
								{
									$fields[] = 'name'.$l["id"];
									$mask[] = "'%h'";
									$values[] = $element["name".$l["id"]];
								}
								elseif(isset($element["name"]))
								{
									$fields[] = 'name'.$l["id"];
									$mask[] = "'%h'";
									$values[] = $element["name"];
								}
							}
						}
					}

					// добавляет новый пункт
					$new_menu_id = DB::query("INSERT INTO {menu} (".($fields ? implode(',', $fields) : '').")"
						." VALUES (".($mask ? implode(',', $mask) : '').")",
						$values
					);
					if ($parent_id)
					{
						$parents = $this->diafan->get_parents($parent_id, "menu");
						$parents[] = $parent_id;
						foreach ($parents as $parent_id)
						{
							DB::query("INSERT INTO {menu_parents} (element_id, parent_id) VALUES (%d, %d)", $new_menu_id, $parent_id);
						}
						if(! in_array($menu_cat_id, $edit_menu_cat_ids))
						{
							$edit_menu_cat_ids[] = $menu_cat_id;
						}
					}
					if (! $save["sort"])
					{
						DB::query("UPDATE {menu} SET sort=id WHERE id=%d", $new_menu_id);
					}
				}
			}
			// просматривает существующие пункты меню элемента
			foreach ($show_in_menu as $menu_cat_id)
			{
				// если пункт есть, но в новых пунктах его нет, то удаляет пункт
				if (! in_array($menu_cat_id, $menu_cat_ids))
				{
					$this->diafan->_menu->delete($save["element_id"], $save["module_name"], $save["element_type"], $menu_cat_id);

					if(! in_array($menu_cat_id, $edit_menu_cat_ids))
					{
						$edit_menu_cat_ids[] = $menu_cat_id;
					}
				}
				// если пунт существует и есть в новых, то обновляет информацию (имя, доступ, период показа, активность)
				else
				{
					$rows = DB::query_fetch_all("SELECT id, [name], [act] FROM {menu} WHERE module_name='%h' AND element_id=%d AND element_type='%s' AND cat_id=%d AND trash='0'", $save["module_name"], $save["element_id"], $save["element_type"], $menu_cat_id);
					foreach ($rows as $row)
					{
						if ($row["name"] == $save['old_name'] || $save["act"] != $save["old_act"])
						{
							$edit_menu = true;
							DB::query("UPDATE {menu} SET [name]='%h', [act]='%d' WHERE id=%d", $save["name"], $save["act"], $row["id"]);
						}
						if ($save["access"] && ! $save["old_access"] || ! $save["access"] && $save["old_access"])
						{
							$edit_menu = true;
							DB::query("UPDATE {menu} SET access='%d' WHERE id=%d", $save["access"], $row["id"]);
						}
						if ($save["date_start"] != $save["old_date_start"])
						{
							$edit_menu = true;
							DB::query("UPDATE {menu} SET date_start=%d WHERE id=%d", $save["date_start"], $row["id"]);
						}
						if ($save["date_finish"] != $save["old_date_finish"])
						{
							$edit_menu = true;
							DB::query("UPDATE {menu} SET date_finish=%d WHERE id=%d", $save["date_finish"], $row["id"]);
						}
					}
				}
			}
		}
		if($edit_menu_cat_ids)
		{
			// пересчитывает поле count_children
			$rows = DB::query_fetch_all("SELECT id FROM {menu} WHERE cat_id IN (%s) AND trash='0'", implode(",", $edit_menu_cat_ids)); 
			foreach ($rows as $row)
			{
				$count = DB::query_result("SELECT COUNT(*) FROM  {menu_parents} WHERE parent_id=%d", $row["id"]);
				DB::query("UPDATE {menu} SET count_children=%d WHERE id=%d", $count, $row["id"]);
			}
		}
		if(! empty($edit_menu) || $edit_menu_cat_ids)
		{
			$this->diafan->_cache->delete("", "menu");
			return true;
		}
		return false;
	}

	/**
	 * Помечает пункты меню на удаление или удаляет пункты меню
	 * 
	 * @param array $element_ids номера элементов
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @return void
	 */
	public function delete($element_ids, $module_name, $element_type)
	{
		$del_ids = array();
		$rows = DB::query_fetch_all("SELECT id, count_children FROM {menu} WHERE element_id IN (".implode(",", $element_ids).") AND module_name='%h' AND element_type='%s'", $module_name, $element_type);

		foreach ($rows as $row)
		{
			$children = array();
			if ($row["count_children"])
			{
				$children = $this->diafan->get_children($row["id"], "menu");
			}
			$children[] = $row["id"];
			foreach ($children as $id)
			{
				if (! in_array($id, $del_ids))
				{
					$del_ids[] = $id;
				}
			}
		}
		if ($del_ids)
		{
			$this->diafan->del_or_trash_where("menu_parents", "element_id IN (".implode(",", $del_ids).")");
			$rows = DB::query_fetch_all("SELECT parent_id FROM {menu} WHERE id IN (".implode(",", $del_ids).")");
			foreach ($rows as $row)
			{
				if ($row["parent_id"])
				{
					$count = DB::query_result("SELECT COUNT(*) FROM {menu_parents} WHERE trash='0' AND parent_id=%d LIMIT 1", $row["parent_id"]);
					DB::query("UPDATE {menu} SET count_children=%d WHERE trash='0' AND id=%d", $count, $row["parent_id"]);
				}
			}
			$this->diafan->del_or_trash_where("menu", "id IN (".implode(",", $del_ids).")");
			$this->diafan->diafan->_cache->delete("", "menu");
		}   
	}

	/**
	 * Блокирует/разблокирует пункты меню
	 * 
	 * @param string $table таблица
	 * @param array $element_ids номера элементов, к которым прикреплены теги
	 * @param integer $act блокировать/разблокировать
	 * @return void
	 */
	public function act($table, $element_ids, $act)
	{
		// при активации пунктов меню активирует соответствующие им элементы
		if ($table == "menu")
		{
			if(! $act)
				return;

			if (! $this->diafan->is_action("save"))
			{
				foreach ($element_ids as $element_id)
				{
					$row = DB::query_fetch_array("SELECT * FROM {menu} WHERE id=%d LIMIT 1", $element_id);

					$table_name = $this->diafan->table_element_type($row["module_name"], $row["element_type"]);

					if (! DB::query_result("SELECT [act] FROM {".$table_name."} WHERE id=%d LIMIT 1", $row["element_id"]))
					{
						DB::query("UPDATE {".$table_name."} SET [act]='1' WHERE id=%d", $row["element_id"]);
						$this->diafan->_cache->delete("", $row["module_name"]);
					}
				}
			}
			else
			{
				if ($_POST["module_name"])
				{
					$table_name = $this->diafan->table_element_type($_POST["module_name"], $_POST["element_type"]);
					if (! DB::query("SELECT [act] FROM {%h} WHERE id=%d LIMIT 1", $table_name, $id))
					{
						DB::query("UPDATE {%h} SET [act]='1' WHERE id=%d", $table_name, $id);
						$this->diafan->_cache->delete("", $_POST["module_name"]);
					}
				}
			}
		}
		// при активации/деактивации элементов активирует/деактивирует пункы меню им соответствующие
		elseif (! $this->diafan->is_variable("menu"))
		{
			$element_type = $this->diafan->element_type();
			list($module_name, ) = explode("_", $table);

			DB::query("UPDATE {menu} SET [act]='%d' WHERE module_name='%h' AND element_type='%s' AND element_id IN (%h)", $act, $module_name, $element_type, implode(',', $element_ids));

			$this->diafan->_cache->delete("", "menu");
		}
	}
}