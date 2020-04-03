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
 * Save_functions_admin
 *
 * Функции сохранения поля
 */
class Save_functions_admin extends Diafan
{
	/**
	 * Сохранение поля "Категория"
	 *
	 * @return void
	 */
	public function save_variable_cat_id()
	{
		$this->diafan->save_cat_id = $this->diafan->filter($_POST, 'int', 'cat_id');

		$this->diafan->set_query("cat_id=%d");
		$this->diafan->set_value($this->diafan->save_cat_id);

		if ($this->diafan->config("element_multiple"))
		{
			DB::query("DELETE FROM {%s_category_rel} WHERE element_id=%d", $this->diafan->_admin->module, $this->diafan->id);
			DB::query("INSERT INTO {%s_category_rel} (element_id, cat_id) VALUES('%d', '%d')", $this->diafan->_admin->module, $this->diafan->id, $this->diafan->save_cat_id);

			if (! empty( $_POST["cat_ids"] ) && ! empty($_POST["user_additional_cat_id"]) && is_array($_POST["cat_ids"]))
			{
				foreach ($_POST["cat_ids"] as $cat_id)
				{
					if($cat_id != 'all' && $cat_id != $_POST["cat_id"])
					{
						DB::query("INSERT INTO {%s_category_rel} (element_id, cat_id) VALUES('%d', '%d')", $this->diafan->_admin->module, $this->diafan->id, $cat_id);
					}
				}
			}
		}
		elseif ($this->diafan->variable_list('plus') && ! $this->diafan->is_new && DB::query_result("SELECT cat_id FROM {%h} WHERE id=%d LIMIT 1", $this->diafan->table, $this->diafan->id) != $this->diafan->save_cat_id)
		{
			$children = $this->diafan->get_children($this->diafan->id, $this->diafan->table);
			if ($children)
			{
				DB::query("UPDATE {%h} SET cat_id=%d WHERE id IN (%h)", $this->diafan->table, $this->diafan->save_cat_id, implode(",", $children));
			}
		}
	}

	/**
	 * Сохранение поля "Страница"
	 *
	 * @return void
	 */
	public function save_variable_site_id()
	{
		$site_id = $this->diafan->get_site_id();

		$this->diafan->set_query("site_id='%d'");
		$this->diafan->set_value($site_id);

		if ($this->diafan->variable_list('plus') && ! $this->diafan->is_new)
		{
			$children = $this->diafan->get_children($this->diafan->id, $this->diafan->table);
			if($children)
			{
				DB::query("UPDATE {".$this->diafan->table."} SET site_id=%d WHERE id IN (%h)", $site_id, implode(",", $children));
			}
		}
	}

	/**
	 * Сохранение поля "Страница"
	 *
	 * @return integer
	 */
	public function get_site_id()
	{
		if(! isset($this->cache["site_id"]))
		{
			if($this->diafan->table == 'site')
			{
				$this->cache["site_id"] = $this->diafan->id;
			}
			else
			{
				$this->cache["site_id"] = $this->diafan->filter($_POST, 'int', 'site_id');
				if(! $this->cache["site_id"])
				{
					if(! $this->diafan->config("element_site"))
					{
						$this->cache["site_id"] = DB::query_result("SELECT id FROM {site} WHERE module_name='%s' AND trash='0' AND [act]='1' LIMIT 1", $this->diafan->_admin->module);
					}
					else
					{
						if ($this->diafan->config("element"))
						{
							$this->cache["site_id"] = DB::query_result("SELECT site_id FROM {".$this->diafan->table."_category} WHERE id=%d LIMIT 1", $_POST["cat_id"]);
						}
						elseif ($this->diafan->config("category"))
						{
							if ($this->diafan->variable_list('plus') && $_POST["parent_id"])
							{
								$this->cache["site_id"] = DB::query_result("SELECT site_id FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $_POST["parent_id"]);
							}
						}
					}
				}
			}
		}
		return  $this->cache["site_id"];
	}

	/**
	 * Сохранение поля "Расположение"
	 * @return void
	 */
	public function save_variable_site_ids()
	{
		$this->diafan->update_table_rel($this->diafan->table."_site_rel", "element_id", "site_id", ! empty($_POST['site_ids']) ? $_POST['site_ids'] : array(), $this->diafan->id, $this->diafan->is_new);
	}

	/**
	 * Сохранение поля "Сортировка"
	 *
	 * @return void
	 */
	public function save_variable_sort()
	{
		$this->diafan->get_sort();
		$this->diafan->set_query("sort=%d");
		$this->diafan->set_value($this->diafan->save_sort);
	}

	/**
	 * Сохранение поля "Сортировка"
	 *
	 * @return void
	 */
	public function get_sort()
	{
		if (! $this->diafan->variable_list('sort'))
		{
			return;
		}
		if ($this->diafan->save_sort)
		{
			return;
		}
		if ($this->diafan->is_new)
		{
			$this->diafan->save_sort = $this->diafan->id;
			return;
		}
		$sort_old = $this->diafan->values("sort", $this->diafan->id);

		$lang_act = ($this->diafan->variable_multilang("act") ? _LANG : '');

		//не сортируются неактивные элементы
		if ($this->diafan->is_variable("act") && (! $this->diafan->values("act".$lang_act) || empty($_POST["act"])))
		{
			$this->diafan->save_sort = $sort_old;
			return;
		}

		//переменная $_POST["sort"] - id элемента, перед которым должен выводится редактируемый элемент
		if (empty($_POST["sort"]) || $_POST["sort"] == $this->diafan->id)
		{
			$this->diafan->save_sort = $sort_old;
			return;
		}

		$sort_new = '';
		//"down" - установить ниже всех
		if (! empty($_POST["sort"]) && $_POST["sort"] == "down")
		{
			if($this->diafan->variable_list('sort', 'desc'))
			{
				$this->diafan->save_sort = 1;
				DB::query("UPDATE {".$this->diafan->table."} sort=sort+1"
				." WHERE 1=1"
				.($this->diafan->variable_list('plus') ? " AND parent_id='".$_POST["parent_id"]."'" : '')
				.($this->diafan->config("element") ? ' AND cat_id="'.$_POST["cat_id"].'"' : '')
				.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '')
				.($this->diafan->is_variable("act") ? " AND act".$lang_act."='1'" : '')) + 1;
			}
			else
			{
				$this->diafan->save_sort = DB::query_result("SELECT MAX(sort) FROM {".$this->diafan->table."}"
				." WHERE 1=1"
				.($this->diafan->variable_list('plus') ? " AND parent_id='".$_POST["parent_id"]."'" : '')
				.($this->diafan->config("element") ? ' AND cat_id="'.$_POST["cat_id"].'"' : '')
				.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '')
				.($this->diafan->is_variable("act") ? " AND act".$lang_act."='1'" : '')) + 1;
			}
			return;
		}
		elseif (! empty($_POST["sort"]))
		{
			$this->diafan->save_sort = DB::query_result("SELECT sort FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $_POST["sort"]);
			if(! $this->diafan->save_sort)
			{
				DB::query("UPDATE {".$this->diafan->table."} SET sort=sort+1");
				if($this->diafan->variable_list('sort', 'desc'))
				{
					$this->diafan->save_sort = 2;
				}
				else
				{
					$this->diafan->save_sort = 1;
				}
				$sort_old++;
			}
			elseif($this->diafan->save_sort == $sort_old)
			{
				if($this->diafan->variable_list('sort', 'desc'))
				{
					$s = $_POST["sort"];
				}
				else
				{
					$s = $this->diafan->id;
				}
				DB::query("UPDATE {".$this->diafan->table."} SET sort=sort+1 WHERE sort>=%d AND id<>%d", $sort_old, $s);
				return;
			}
		}

		if ($sort_old > $this->diafan->save_sort)
		{
			DB::query("UPDATE {".$this->diafan->table."} SET sort=sort+1 WHERE sort>%d AND id<>%d"
			.($this->diafan->variable_list('plus') ? " AND parent_id='".$_POST["parent_id"]."'" : '')
			.($this->diafan->config("element") ? ' AND cat_id="'.$_POST["cat_id"].'"' : '')
			.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '')
			.($this->diafan->is_variable("act") ? " AND act".$lang_act."='1'" : ''),
			$this->diafan->save_sort, $this->diafan->id);
		}
		else
		{
			if(! $this->diafan->variable_list('sort', 'desc'))
			{
				$this->diafan->save_sort--;
			}
			DB::query("UPDATE {".$this->diafan->table."} SET sort=sort-1 WHERE sort>%d AND sort<=%d AND id<>%d"
			.($this->diafan->variable_list('plus') ? " AND parent_id='".$_POST["parent_id"]."'" : '')
			.($this->diafan->config("element") ? ' AND cat_id="'.$_POST["cat_id"].'"' : '')
			.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '')
			.($this->diafan->is_variable("act") ? " AND act".$lang_act."='1'" : ''),
			$sort_old, $this->diafan->save_sort, $this->diafan->id);
		}
	}

	/**
	 * Сохранение поля "Псевдоссылка"
	 *
	 * @return void
	 */
	public function save_variable_rewrite()
	{
		if (! $this->diafan->is_save_rewrite)
		{
			$this->diafan->get_rewrite();
		}
	}

	/**
	 * Сохранение псевдоссылки
	 *
	 * @return void
	 */
	public function get_rewrite()
	{
		$this->diafan->is_save_rewrite = true;

		$site_id = $this->diafan->get_site_id();

		// если изменился раздел сайта, к которому прикреплен элемент
		if ($site_id != $this->diafan->values("site_id"))
		{
			if ($this->diafan->config("category") && ! $this->diafan->is_new)
			{
				if($this->diafan->variable_list('plus'))
				{
					$child = $this->diafan->get_children($this->diafan->id, $this->diafan->table);

					if($child)
					{
						// меняем раздел сайта у всех вложенных категорий
						DB::query("UPDATE {".$this->diafan->table."} SET site_id=%d WHERE id IN (%s)",
						$site_id,
						implode(",", $child));
					}

					$child[] = $this->diafan->id;

					// меняем раздел сайта у всех элементов, принадлежащих текущей и вложенным категориям
					DB::query("UPDATE {".str_replace("_category", "", $this->diafan->table)."} SET site_id=%d WHERE cat_id IN (%s)",
					$site_id,
					implode(",", $child));
				}
				else
				{
					// меняем раздел сайта у всех элементов, принадлежащих текущей категории
					DB::query("UPDATE {".str_replace("_category", "", $this->diafan->table)."}"
					." SET site_id=%d WHERE cat_id=%d",
					$site_id,
					$this->diafan->id);
				}
			}
		}
		$rewrite = $_POST["rewrite"];

		$text = $_POST[$this->diafan->variable_list("name", "variable") ? $this->diafan->variable_list("name", "variable") : 'name'];

		$element_id = $this->diafan->id;
		$module_name = $this->diafan->_admin->module;
		$element_type = $this->diafan->element_type();
		$cat_id = ! empty($_POST["cat_id"]) ? $_POST["cat_id"] : 0;
		$parent_id = ! empty($_POST["parent_id"]) ? $_POST["parent_id"] : 0;
		$add_parents = $this->diafan->is_new || ! empty($_POST["is_new"]) ? true : false;
		$change_children = ! $this->diafan->is_new && $this->diafan->variable_list('plus');

		$this->diafan->_route->save($rewrite, $text, $element_id, $module_name, $element_type, $site_id, $cat_id, $parent_id, $add_parents, $change_children);

		$this->diafan->save_rewrite_redirect();
	}

	/**
	 * Сохранение редиректа
	 *
	 * @return void
	 */
	public function save_rewrite_redirect()
	{
		$site_id = $this->diafan->get_site_id();

		// если обновили раздел, то у вложенных элементов тоже обновляем раздел
		if ($site_id != $this->diafan->values("site_id"))
		{
			if ($this->diafan->config("category") && ! $this->diafan->is_new)
			{
				$child = $this->diafan->get_children($this->diafan->id, $this->diafan->table);

				if($child)
				{
					DB::query("UPDATE {".str_replace("_category", "", $this->diafan->table)."}"
					." SET site_id=%d WHERE cat_id IN (%s)",
					$site_id, implode(",", $child));
				}
			}
		}
		// если редирект не прописан, удаляем запись о редиректе
		if (! $_POST["rewrite_redirect"])
		{
			DB::query("DELETE FROM {redirect} WHERE module_name='%s' AND element_id=%d AND element_type='%s'",
				$this->diafan->_admin->module, $this->diafan->id, $this->diafan->element_type());
			return;
		}
		// ищем есть ли запись о редиректе
		if (! $this->diafan->is_new)
		{
			$row = DB::query_fetch_array("SELECT * FROM {redirect} WHERE module_name='%s' AND element_id=%d AND element_type='%s' LIMIT 1",
				$this->diafan->_admin->module, $this->diafan->id, $this->diafan->element_type());
		}
		// если нашли, то обновляем
		if (! empty($row["id"]))
		{
			if($row["redirect"] == $_POST["rewrite_redirect"] && $row["code"] == $_POST["rewrite_code"])
				return;

			DB::query("UPDATE {redirect} SET redirect='%s', code=%d WHERE id=%d",
				$_POST["rewrite_redirect"], $_POST["rewrite_code"], $row["id"]);
		}
		// иначе добавляем новую запись
		else
		{
			DB::query("INSERT INTO {redirect} (redirect, code, module_name, element_id, element_type)"
				." VALUES ('%s', %d, '%s', %d, '%s')",
				$_POST["rewrite_redirect"], $_POST["rewrite_code"], $this->diafan->_admin->module,
				$this->diafan->id, $this->diafan->element_type());
		}
	}

	/**
	 * Сохранение поля "Родитель"
	 *
	 * @return void
	 */
	public function save_variable_parent_id()
	{
		$this->diafan->save_parent_id = $this->diafan->filter($_POST, 'int', 'parent_id');

		$this->diafan->set_query("parent_id='%d'");
		$this->diafan->set_value($_POST["parent_id"]);

		// если пункт новый, просто добавляем всех его родителей в table_parents и увеличиваем у родителей количество детей
		if ($this->diafan->is_new)
		{
			if ($_POST["parent_id"])
			{
				$parents = $this->diafan->get_parents($_POST["parent_id"], $this->diafan->table);
				$parents[] = $_POST["parent_id"];
				foreach ($parents as $parent_id)
				{
					DB::query("UPDATE {".$this->diafan->table."} SET count_children=count_children+1 WHERE id=%d", $parent_id);
					DB::query("INSERT INTO {".$this->diafan->table."_parents} (element_id, parent_id) VALUES (%d, %d)", $this->diafan->id, $parent_id);
				}
			}
			return;
		}
		// если родитель не изменился, уходим
		if ($this->diafan->values("parent_id") == $_POST["parent_id"])
		{
			return;
		}

		$children = $this->diafan->get_children($this->diafan->id, $this->diafan->table);
		$children[] = $this->diafan->id;
		$count_children = count($children);

		// если родитель был, у текущего элемента и его детей удаляем всех старых родителей, вышего текущего элемента
		// у старых родителей выше текущего элемента уменьшаем количество детей
		if ($this->diafan->values("parent_id"))
		{
			$old_parents = $this->diafan->get_parents($this->diafan->id, $this->diafan->table);
			foreach ($old_parents as $parent_id)
			{
				DB::query("DELETE FROM {".$this->diafan->table."_parents} WHERE element_id IN (%h) AND parent_id=%d", implode(",", $children), $parent_id);
				DB::query("UPDATE {".$this->diafan->table."} SET count_children=count_children-%d WHERE id=%d", $count_children, $parent_id);
			}
		}
		// если новый родитель задан, то текущему элементу и его детям прибавляем новых родителей и увеличиваем у родителей количество детей
		if ($_POST["parent_id"])
		{
			$parents = $this->diafan->get_parents($_POST["parent_id"], $this->diafan->table);
			$parents[] = $_POST["parent_id"];
			foreach ($parents as $parent_id)
			{
				DB::query("UPDATE {".$this->diafan->table."} SET count_children=count_children+%d WHERE id=%d", $count_children, $parent_id);
				foreach ($children as $child)
				{
					DB::query("INSERT INTO {".$this->diafan->table."_parents} (element_id, parent_id) VALUES (%d, %d)", $child, $parent_id);
				}
			}
		}
	}

	/**
	 * Сохранение поля "Время редактирования"
	 *
	 * @return void
	 */
	public function save_variable_timeedit()
	{
		$this->diafan->set_query("timeedit='%s'");
		$this->diafan->set_value(time());
	}

	/**
	 * Сохранение поля "Показывать на сайте"
	 *
	 * @return void
	 */
	public function save_variable_act()
	{
		$lang = $this->diafan->variable_multilang("act") ? _LANG : '';

		$_POST["act"] = ! empty($_POST["act"]) ? '1' : '0';

		if($this->diafan->values('act') == $_POST["act"])
		{
			return;
		}

		if ($this->diafan->variable_list('plus'))
		{
			$ids = $this->diafan->get_children($this->diafan->id, $this->diafan->table, array (), false);
		}
		$ids[] = $this->diafan->id;
		if ($ids)
		{
			DB::query("UPDATE {".$this->diafan->table."} SET act".$lang."='".( ! empty( $_POST["act"] ) ? "1".( $this->diafan->is_variable("timeedit") ? "', timeedit='".time() : '' ) : '0' )."' WHERE id IN (".implode(',', $ids).")");
		}
		if ($this->diafan->config('category') && $this->diafan->values('act') != $_POST["act"])
		{
			DB::query("UPDATE {".str_replace('_category', '', $this->diafan->table)."} SET act".$lang."='".( ! empty( $_POST["act"] ) ? "1".( $this->diafan->is_variable("timeedit") ? "', timeedit='".time() : '' ) : '0' )."' WHERE cat_id IN (".implode(',', $ids).")");
		}
		foreach ($this->diafan->installed_modules as $module)
		{
			if (Custom::exists('modules/'.$module.'/admin/'.$module.'.admin.inc.php'))
			{
				Custom::inc('modules/'.$module.'/admin/'.$module.'.admin.inc.php');
				$func = 'act';
				$class = ucfirst($module).'_admin_inc';
				if (method_exists($class, 'act'))
				{
					$admin_act = new $class($this->diafan);
					$admin_act->act($this->diafan->table, $ids, ! empty( $_POST["act"] ) ? 1 : 0);
				}
			}
		}
	}

	/**
	 * Сохранение поля "Доступ"
	 *
	 * @return void
	 */
	public function save_variable_access()
	{
		$roles = array ();
		$old_roles = array ();
		$new_roles = array ();
		$element_id = $this->diafan->id;
		$element_type = $this->diafan->element_type();

		$roles = DB::query_fetch_value("SELECT id FROM {users_role} WHERE trash='0'", "id");
		$roles[] = 0;
		if ($this->diafan->values('access'))
		{
			$old_roles = DB::query_fetch_value("SELECT role_id FROM {access} WHERE element_id=%d AND element_type='%s' AND module_name='%s'", $element_id, $element_type, $this->diafan->_admin->module, "role_id");
		}

		foreach ($_POST['access_role'] as $role_id)
		{
			$new_roles[] = intval($role_id);
		}
		// отмечены все роли
		if (empty( $_POST["access"]))
		{
			$new_roles = array();
		}
		$this->diafan->set_query("access='%d'");
		$this->diafan->set_value($new_roles ? 1 : 0);

		// если доступ ограничен, то запоминаем в настройки, что в модуле где-то ограничен доступ,
		// чтобы добавить в SQL запрос на фронденде
		if($new_roles)
		{
			$this->diafan->configmodules('where_access_'.$element_type, $this->diafan->_admin->module, 0, 0, true);
			$this->diafan->configmodules('where_access', 'all', 0, 0, true);
		}
		elseif($this->diafan->configmodules('where_access_'.$element_type, $this->diafan->_admin->module, 0))
		{
			if(! DB::query_result("SELECT id FROM {access} WHERE module_name='%s' AND element_id<>%d AND element_type='%s' LIMIT 1", $this->diafan->_admin->module, $element_id, $element_type))
			{
				$this->diafan->configmodules('where_access_'.$element_type, $this->diafan->_admin->module, 0, 0, 0);
				if(! DB::query_result("SELECT id FROM {config} WHERE module_name<>'all' AND value='1' AND name LIKE 'where_access%' LIMIT 1"))
				{
					$this->diafan->configmodules('where_access', 'all', 0, 0, 0);
				}
				else
				{
					$this->diafan->configmodules('where_access', 'all', 0, 0, true);
				}
			}
		}

		// изменений нет
		if (! array_diff($new_roles, $old_roles) && ! array_diff($old_roles, $new_roles))
		{
			return true;
		}

		// Роли, которым дан доступ
		$diff_new_roles = array_diff($new_roles, $old_roles);
		foreach ($diff_new_roles as $role_id)
		{
			DB::query("INSERT INTO {access} (element_id, element_type, module_name, role_id) VALUES (%d, '%s', '%s', %d)", $element_id, $element_type, $this->diafan->_admin->module, $role_id);
		}
		// Роли, для которых теперь нет доступа
		$diff_old_roles = array_diff($old_roles, $new_roles);
		if ($diff_old_roles)
		{
			DB::query("DELETE FROM {access} WHERE element_id=%d AND element_type='%s' AND module_name='%s' AND role_id IN (%s)", $element_id, $element_type, $this->diafan->_admin->module, implode(",", $diff_old_roles));
		}

		// для категории можем изменить доступ для вложенных категорий и элементов
		if ($this->diafan->config('category_rel'))
		{
			$rows = DB::query_fetch_all("SELECT id, access FROM {".$this->diafan->_admin->module."} WHERE cat_id=%d", $cat_id);
			foreach ($rows as $row)
			{
				$old_roles_el = array ();
				if ($row["access"])
				{
					$old_roles_el = DB::query_fetch_value("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='element'", $row["id"], $this->diafan->_admin->module, "role_id");
				}
				// Если доступ полностью совпадает, то изменения синхронные
				if (!array_diff($old_roles_el, $old_roles) && !array_diff($old_roles, $old_roles_el))
				{
					foreach ($diff_new_roles as $role_id)
					{
						DB::query("INSERT INTO {access} (element_id, element_type, module_name, role_id) VALUES (%d, 'element', '%s', %d)", $row["id"], $this->diafan->_admin->module, $role_id);
					}
					if ($diff_old_roles)
					{
						DB::query("DELETE FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='element' AND role_id IN (%s)", $row["id"], $this->diafan->_admin->module, implode(",", $diff_old_roles));
					}
					if (! $new_roles && $row["access"] || $new_roles && !$row["access"])
					{
						DB::query("UPDATE {".$this->diafan->_admin->module."} SET access='%d' WHERE id=%d", $row["access"] ? 0 : 1, $row["id"]);
					}
				}
				else
				{
					$diff_old_roles_el = array_diff($old_roles_el, $new_roles);
					foreach ($diff_new_roles_el as $role_id)
					{
						DB::query("DELETE FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='element' AND role_id=%d", $row["id"], $this->diafan->_admin->module, $role_id);
					}
				}
			}

			$children = $this->diafan->get_children($cat_id, $this->diafan->table);
			if ($children)
			{
				$rows = DB::query_fetch_all("SELECT id, access FROM {".$this->diafan->table."} WHERE id IN (%s)", implode(",", $children));
				foreach ($rows as $row)
				{
					$old_roles_el = array ();
					if ($row["access"])
					{
						$old_roles_el = DB::query_fetch_value("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='cat'", $row["id"], $this->diafan->_admin->module,"role_id");
					}
					// Если доступ полностью совпадает, то изменения синхронные
					if (!array_diff($old_roles_el, $old_roles) && !array_diff($old_roles, $old_roles_el))
					{
						foreach ($diff_new_roles as $role_id)
						{
							DB::query("INSERT INTO {access} (element_id, module_name, element_type, role_id) VALUES (%d, '%s', 'cat', %d)", $row["id"], $this->diafan->_admin->module, $role_id);
						}
						if ($diff_old_roles)
						{
							DB::query("DELETE FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='cat' AND role_id IN (%s)", $row["id"], $this->diafan->_admin->module, implode(",", $diff_old_roles));
						}
						if (! $new_roles && $row["access"] || $new_roles && !$row["access"])
						{
							DB::query("UPDATE {".$this->diafan->table."} SET access='%d' WHERE id=%d", $row["access"] ? 0 : 1, $row["id"]);
						}
					}
					else
					{
						$diff_old_roles_el = array_diff($old_roles_el, $new_roles);
						foreach ($diff_new_roles_el as $role_id)
						{
							DB::query("DELETE FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='cat' AND role_id=%d", $row["id"], $this->diafan->_admin->module, $role_id);
						}
					}
				}
			}
			if(! DB::query_result("SELECT id FROM {access} WHERE module_name='%s' AND element_type='element' LIMIT 1", $this->diafan->_admin->module))
			{
				$this->diafan->configmodules('where_access_element', $this->diafan->_admin->module, 0, 0, 0);
				if(! DB::query_result("SELECT id FROM {config} WHERE module_name<>'all' AND value='1' AND name LIKE 'where_access%' LIMIT 1"))
				{
					$this->diafan->configmodules('where_access', 'all', 0, 0, 0);
				}
				else
				{
					$this->diafan->configmodules('where_access', 'all', 0, 0, true);
				}
			}
			else
			{
				$this->diafan->configmodules('where_access_element', $this->diafan->_admin->module, 0, 0, 1);
				$this->diafan->configmodules('where_access', 'all', 0, 0, true);
			}
		}
	}

	/**
	 * Сохранение поля "Период показа"
	 *
	 * @return void
	 */
	public function save_variable_date_period()
	{
		$this->diafan->set_query("date_start=%d");
		$this->diafan->set_value($this->diafan->unixdate($_POST["date_start"]));

		$this->diafan->set_query("date_finish=%d");
		$this->diafan->set_value($this->diafan->unixdate($_POST["date_finish"]));

		$element_type = $this->diafan->element_type();

		if(! empty($_POST["date_start"]) || ! empty($_POST["date_finish"]))
		{
			$this->diafan->configmodules('where_period_'.$element_type, $this->diafan->_admin->module, 0, 0, true);
			$this->diafan->configmodules('where_period', 'all', 0, 0, true);
		}
		elseif($this->diafan->configmodules('where_period_'.$element_type, $this->diafan->_admin->module, 0))
		{
			if(! DB::query_result("SELECT id FROM {%s} WHERE (date_start>0 OR date_finish>0) AND id<>%d LIMIT 1", $this->diafan->table, $this->diafan->id))
			{
				$this->diafan->configmodules('where_period_'.$element_type, $this->diafan->_admin->module, 0, 0, 0);
				if(! DB::query_result("SELECT id FROM {config} WHERE module_name<>'all' AND value='1' AND name LIKE 'where_period%%' LIMIT 1"))
				{
					$this->diafan->configmodules('where_period', 'all', 0, 0, 0);
				}
				else
				{
					$this->diafan->configmodules('where_period', 'all', 0, 0, true);
				}
			}
		}
	}

	/**
	 * Сохранение поля "Период показа"
	 *
	 * @return void
	 */
	public function get_date_period()
	{
		if($this->diafan->is_variable('created'))
		{
			if($this->diafan->unixdate($_POST["created"]) > time())
			{
				if(empty($_POST["date_start"]))
				{
					$_POST["date_start"] = $_POST["created"];
				}
			}
			else
			{
				if($this->diafan->unixdate($_POST["date_start"]) == $this->diafan->values("created") && empty($_POST["date_finish"]))
				{
					$_POST["date_start"] = '';
				}
			}
		}
	}

	/**
	 * Сохранение поля "Динамические блоки"
	 *
	 * @return void
	 */
	public function save_variable_dynamic()
	{
		$element_type = $this->diafan->element_type();

		$dynamic = DB::query_fetch_all("SELECT b.* FROM {site_dynamic} AS b"
			." INNER JOIN {site_dynamic_module} AS m ON m.dynamic_id=b.id"
			." WHERE b.trash='0'"
			." AND (m.module_name='%h' OR m.module_name='') AND (m.element_type='%h' OR m.element_type='')"
			." GROUP BY b.id",
			$this->diafan->_admin->module, $element_type
		);

		if($this->diafan->is_new)
		{
			$element_dynamic = array();
		}
		else
		{
			$element_dynamic = DB::query_fetch_key("SELECT * FROM {site_dynamic_element} WHERE element_id=%d AND element_type='%s' AND module_name='%s'", $this->diafan->id, $element_type, $this->diafan->_admin->module, "dynamic_id");
		}
		$dynamic_ids = array();
		foreach($dynamic as $d)
		{
			$not_empty_multitext = false;
			if(in_array($d["type"], array('text', 'textarea', 'editor')))
			{
				foreach($this->diafan->_languages->all as $l)
				{
					if($l["id"] != _LANG && ! empty($element_dynamic[$d["id"]]["value".$l["id"]]))
					{
						$not_empty_multitext = true;
					}
				}
			}

			$dynamic_ids[] = $d["id"];
			if(! empty($_POST["dynamic".$d["id"]]) || $not_empty_multitext)
			{
				$value = $_POST["dynamic".$d["id"]];
				$multilang = false;
				switch($d["type"])
				{
					case 'text':
						$mask = "'%h'";
						$multilang = true;
						break;

					case 'email':
						$mask = "'%h'";
						break;

					case 'textarea':
					case 'editor':
						$mask = "'%s'";
						$multilang = true;
						$value = $this->diafan->save_field_editor("dynamic".$d["id"]);
						break;

					case 'numtext':
						$mask = "'%d'";
						break;

					case 'floattext':
						$mask = "'%f'";
						break;

					case 'date':
					case 'datetime':
						$value = $this->diafan->unixdate($value);
						$mask = "'%d'";
						break;
				}
				$parent = 0;
				if($this->diafan->variable_list('plus') && ! empty($_POST["dynamic_parent".$d["id"]]))
				{
					$parent = 1;
				}
				$category = 0;
				if($this->diafan->config("category") && ! empty($_POST["dynamic_category".$d["id"]]))
				{
					$category = 1;
				}
				if(! empty($element_dynamic[$d["id"]]))
				{
					DB::query("UPDATE {site_dynamic_element} SET ".($multilang ? "[value]" : "value".$this->diafan->_languages->site)."=".$mask.", parent='%d', category='%d' WHERE id=%d", $value, $parent, $category, $element_dynamic[$d["id"]]["id"]);
				}
				elseif($value)
				{
					DB::query("INSERT INTO {site_dynamic_element} (dynamic_id, module_name, element_id, element_type, ".($multilang ? "[value]" : "value".$this->diafan->_languages->site).", parent, category) VALUES (%d, '%s', %d, '%s', ".$mask.", '%d', '%d')", $d["id"], $this->diafan->_admin->module, $this->diafan->id, $element_type, $value, $parent, $category);
				}
			}
			elseif(! empty($element_dynamic[$d["id"]]))
			{
				$del_ids[] = $element_dynamic[$d["id"]]["id"];
			}
		}
		foreach($element_dynamic as $dynamic_id => $d)
		{
			if(! in_array($dynamic_id, $dynamic_ids))
			{
				$del_ids[] = $d["id"];
			}
		}
		if(isset($del_ids))
		{
			DB::query("DELETE FROM {site_dynamic_element} WHERE id IN (%s)", implode(",", $del_ids));
		}
	}

	/**
	 * Сохранение поля "Не показывать на карте сайта"
	 *
	 * @return void
	 */
	public function save_variable_map_no_show()
	{
		if ($this->diafan->variable_list('plus'))
		{
			$ids = $this->diafan->get_children($this->diafan->id, $this->diafan->table, array (), false);
		}
		$ids[] = $this->diafan->id;
		if ($ids)
		{
			DB::query("UPDATE {".$this->diafan->table."} SET map_no_show='".(! empty( $_POST["map_no_show"]) ? "1" : '0' )."' WHERE id IN (".implode(',', $ids).")");
		}
		if ($this->diafan->config('category'))
		{
			DB::query("UPDATE {".str_replace('_category', '', $this->diafan->table)."} SET map_no_show='".(! empty($_POST["map_no_show"]) ? "1" : '0' )."' WHERE cat_id IN (".implode(',', $ids).")");
		}
	}

	/**
	 * Сохранение поля "Редактор"
	 * @return void
	 */
	public function save_variable_admin_id()
	{
		if(! $this->diafan->values("admin_id"))
		{
			$this->diafan->set_query("admin_id=%d");
			$this->diafan->set_value($this->diafan->_users->id);
		}
	}

	/**
	 * Сохранение поля "Автор"
	 * @return void
	 */
	public function save_variable_user_id()
	{
		if(! $this->diafan->variable('user_id', 'disabled'))
		{
			$this->diafan->set_query("user_id=%d");
			$this->diafan->set_value($_POST["user_id"]);
		}
	}

	/**
	 * Сохранение поля "Похожие элементы"
	 * @return void
	 */
	public function save_variable_rel_elements(){}

    public function save_variable_rel_goods(){}
    public function save_variable_replication(){}

	/**
	 * Сохранение поля "Дополнительные параметры"
	 *
	 * @return void
	 */
	public function save_variable_param($where = '')
	{
		$ids = array();
		$rows = DB::query_fetch_all("SELECT id, type, config FROM {".$this->diafan->table."_param}"
			." WHERE trash='0'".$where." ORDER BY sort ASC");

		foreach ($rows as $row)
		{
			if($row["type"] == 'attachments')
			{
				Custom::inc('modules/attachments/admin/attachments.admin.inc.php');
				$attachments = new Attachments_admin_inc($this->diafan);
				$attachments->save_param($row["id"], $row["config"]);
				continue;
			}

			if($row["type"] == "editor")
			{
				$_POST['param'.$row["id"]] = $this->diafan->save_field_editor('param'.$row["id"]);
			}

			if ($row["type"] == "multiple")
			{
				DB::query("DELETE FROM {".$this->diafan->table."_param_element} WHERE param_id=%d AND element_id=%d", $row["id"], $this->diafan->id);
				if (! empty($_POST['param'.$row["id"]]) && is_array($_POST['param'.$row["id"]]))
				{
					foreach ($_POST['param'.$row["id"]] as $v)
					{
						DB::query(
							"INSERT INTO {".$this->diafan->table."_param_element} (value, param_id, element_id) VALUES ('%s', %d, %d)",
							$v,
							$row["id"],
							$this->diafan->id
						);
					}
				}
				$ids[] = $row["id"];
			}
			elseif (! empty($_POST['param'.$row["id"]]))
			{
				$id = 0;
				if (! $this->diafan->_route->is_new)
				{
					$id = DB::query_result("SELECT id FROM {".$this->diafan->table."_param_element} WHERE param_id=%d AND element_id=%d LIMIT 1", $row["id"], $this->diafan->id);
				}
				switch($row["type"])
				{
					case "date":
						$_POST['param'.$row["id"]] = $this->diafan->formate_in_date($_POST['param'.$row["id"]]);
						break;

					case "datetime":
						$_POST['param'.$row["id"]] = $this->diafan->formate_in_datetime($_POST['param'.$row["id"]]);
						break;

					case "numtext":
						$_POST['param'.$row["id"]] = str_replace(',', '.', $_POST['param'.$row["id"]]);
						break;
				}
				$multilang = in_array($row["type"], array("text", "editor", "textarea")) && ($this->diafan->variable_multilang("name") || $this->diafan->variable_multilang("text"));
				if ($id)
				{
					DB::query(
						"UPDATE {".$this->diafan->table."_param_element} SET ".($multilang ? '[value]' : 'value')
						."='%s' WHERE id=%d",
						$_POST['param'.$row["id"]],
						$id
					);
					DB::query("DELETE FROM {".$this->diafan->table."_param_element} WHERE param_id=%d AND element_id=%d AND id<>%d", $row["id"], $this->diafan->id, $id);
				}
				else
				{
					DB::query(
						"INSERT INTO {".$this->diafan->table."_param_element} (".($multilang ? '[value]' : 'value')
						.", param_id, element_id) VALUES ('%s', %d, %d)",
						$_POST['param'.$row["id"]],
						$row["id"],
						$this->diafan->id
					);
				}
				$ids[] = $row["id"];
			}
		}
		DB::query("DELETE FROM {".$this->diafan->table."_param_element} WHERE".($ids ? " param_id NOT IN (".implode(", ", $ids).") AND" : "")." element_id=%d", $this->diafan->id);
	}

	/**
	 * Сохранение поля "Значения поля конструктора"
	 * @return void
	 */
	public function save_variable_param_select()
	{
		$name = $this->diafan->variable_multilang("name") ? '[name]' : 'name';
		switch ($_POST["type"])
		{
			case "select":
			case "radio":
			case "multiple":
				if(! empty($_POST["param_textarea_check"]))
				{
					$values = DB::query_fetch_value("SELECT id FROM {".$this->diafan->table."_select} WHERE param_id=%d", $this->diafan->id, "id");
					$strings = explode("\n", $_POST["param_textarea"]);
					$sort = 1;
					foreach ($strings as $i => $data)
					{
						$data = trim($data);
						if(empty($data) && $data !== "0")
						{
							continue;
						}
						$id = (! empty($values[$i]) ? $values[$i] : '');
						if($id)
						{
							DB::query("UPDATE {".$this->diafan->table."_select} SET ".$name."='%h', sort=%d WHERE id=%d", $data, $sort, $id);
						}
						else
						{
							$id = DB::query("INSERT INTO {".$this->diafan->table."_select} (param_id, ".$name.", sort) VALUES (%d, '%h', %d)", $this->diafan->id, $data, $sort);
						}
						$sort++;
						$ids[] = $id;
					}
				}
				else
				{
					$ids = array();
					if(! empty($_POST["paramv"]))
					{
						$sort = 1;
						foreach ($_POST["paramv"] as $key => $value)
						{
							$value = trim($value);
							if (! $value && $value !== "0")
								continue;

							$id = 0;
							if ( ! empty($_POST["param_id"][$key]))
							{
								$id = $_POST["param_id"][$key];
							}
							if ($id)
							{
								DB::query("UPDATE {".$this->diafan->table."_select} SET ".$name."='%h', sort=%d WHERE id=%d", $value, $sort, $id);
							}
							else
							{
								$id = DB::query("INSERT INTO {".$this->diafan->table."_select} (param_id, ".$name.", sort) VALUES (%d, '%h', %d)", $this->diafan->id, $value, $sort);
							}
							$sort++;
							$ids[] = $id;
						}
					}
				}

				if ( ! empty($ids))
				{
					$del_ids = DB::query_fetch_value("SELECT id FROM {".$this->diafan->table."_select} WHERE param_id=%d AND id NOT IN (%s)", $this->diafan->id, implode(",", $ids), "id");
					if($del_ids)
					{
						DB::query("DELETE FROM {".$this->diafan->table."_select} WHERE id IN (%s)", implode(",", $del_ids));
						DB::query("DELETE FROM {".$this->diafan->table."_param_element} WHERE param_id=%d AND value IN (%s)", $this->diafan->id, implode(",", $del_ids));
					}
				}

				break;
			case "checkbox":
				if ($this->diafan->values("type") == "checkbox" && ($_POST["paramk_check1"] || $_POST["paramk_check0"]))
				{
					$rows = DB::query_fetch_all("SELECT id, value FROM {".$this->diafan->table."_select} WHERE param_id=%d", $this->diafan->id);
					foreach ($rows as $row)
					{
						if ($row["value"] == 1)
						{
							DB::query("UPDATE {".$this->diafan->table."_select} SET ".$name."='%h' WHERE id=%d", $_POST["paramk_check1"], $row["id"]);
							$check1 = true;
						}
						elseif ($row["value"] == 0)
						{
							DB::query("UPDATE {".$this->diafan->table."_select} SET ".$name."='%h' WHERE id=%d", $_POST["paramk_check0"], $row["id"]);
							$check0 = true;
						}
					}
					DB::query("DELETE FROM {".$this->diafan->table."_select} WHERE param_id=%d AND value NOT IN (0,1)", $this->diafan->id);

				}
				else
				{
					DB::query("DELETE FROM {".$this->diafan->table."_select} WHERE param_id=%d", $this->diafan->id);
				}
				if (empty($check0) && $_POST["paramk_check0"])
				{
					DB::query("INSERT INTO {".$this->diafan->table."_select} (param_id, value, ".$name.") VALUES (%d, 0, '%h')", $this->diafan->id, $_POST["paramk_check0"]);
				}
				if (empty($check1) && $_POST["paramk_check1"])
				{
					DB::query("INSERT INTO {".$this->diafan->table."_select} (param_id, value, ".$name.") VALUES (%d, 1, '%h')", $this->diafan->id, $_POST["paramk_check1"]);
				}

				break;

			default:
				DB::query("DELETE FROM {".$this->diafan->table."_select} WHERE param_id=%d", $this->diafan->id);
		}
		$types = $this->diafan->variable("type", "select");
		if(! empty($types["attachments"]))
		{
			Custom::inc('modules/attachments/admin/attachments.admin.inc.php');
			$attachment = new Attachments_admin_inc($this->diafan);
			$attachment->save_config_param();
		}
		if(! empty($types["images"]))
		{
			Custom::inc('modules/images/admin/images.admin.inc.php');
			$images = new Images_admin_inc($this->diafan);
			$images->save_config_param();
		}
	}

	/**
	 * Сохранение поля "Анонс"
	 * @return void
	 */
	public function save_variable_anons()
	{
		$this->diafan->set_query("anons_plus"._LANG."='%d'");
		$this->diafan->set_value(empty($_POST["anons_plus"]) ? 0 : 1);

		$this->diafan->set_query("anons"._LANG."='%s'");
		$this->diafan->set_value($this->diafan->save_field_editor('anons'));
	}

	/**
	 * Сохранение поля "Бэкенд"
	 * @return void
	 */
	public function save_variable_backend()
	{
		if(! $variable = $this->diafan->variable('backend', 'variable'))
		{
			$variable = 'backend';
		}
		if (empty($_POST['backend']))
		{
			$this->diafan->set_query($variable."='%s'");
			$this->diafan->set_value('');
			return;
		}
		
		$backend = $this->diafan->filter($_POST, "string", "backend");		
		if (! Custom::exists('modules/'.$this->diafan->_admin->module.'/backend/'.$backend.'/'.$this->diafan->_admin->module.'.'.$backend.'.admin.php'))
		{
			$this->diafan->set_query($variable."='%s'");
			$this->diafan->set_value('');
			return;
		}
		Custom::inc('modules/'.$this->diafan->_admin->module.'/backend/'.$backend.'/'.$this->diafan->_admin->module.'.'.$backend.'.admin.php');
		$config_class = ucfirst($this->diafan->_admin->module).'_'.$backend.'_admin';
		$class = new $config_class($this->diafan);

		$values = array();
		foreach ($class->config["params"] as $key => $name)
		{
			if(! empty($name["type"]) && $name["type"] == 'info')
			{
				continue;
			}
			if(! empty($name["type"]) && $name["type"] == 'function')
			{
				if (is_callable(array(&$class, "save_variable_".$key)))
				{
					$value = call_user_func_array(array(&$class, "save_variable_".$key), array());
					if($value)
					{
						$values[$key] = $value;
					}
					continue;
				}
			}
			if ( ! empty($_POST[$backend.'_'.$key]))
			{				
				$values[$key] = $this->diafan->filter($_POST, 'string', $backend.'_'.$key);
			}
		}
		$this->diafan->set_query($variable."='%s'");
		$this->diafan->set_value($backend);

		$this->diafan->set_query("params='%s'");
		$this->diafan->set_value(serialize($values));
	}

	/**
	 * Сохранение поля "Шаблон страницы для разных ситуаций"
	 * @return void
	 */
	public function save_config_variable_themes(){}

	/**
	 * Сохранение поля "Доступ к файлам только для администратора"
	 *
	 * @return void
	 */
	public function save_config_variable_attachments_access_admin()
	{
		$this->diafan->set_query("attachments_access_admin='%d'");
		$this->diafan->set_value(1);
	}

	/**
	 * Сохранение поля "Электронный адрес администратора - другой" для конфигурации модуля
	 *
	 * @return void
	 */
	public function save_config_variable_email_admin()
	{
		if(! empty($_POST["email_admin"]))
		{
			if(is_array($_POST["email_admin"]))
			{
				$values = array();
				foreach ($_POST["email_admin"] as $v)
				{
					if(trim($v))
					{
						$values[] = trim($v);
					}
				}
				$value = implode(',', $values);
			}
			else
			{
				$value = $_POST["email_admin"];
			}
			$this->diafan->set_query("email_admin='%s'");
			$this->diafan->set_value($value);
		}
	}

	/**
	 * Сохранение поля "Настройка - в модуле ограничен доступ"
	 *
	 * @return void
	 */
	public function save_config_variable_where_access()
	{
		if($this->diafan->_route->site)
			return;

		$rows = DB::query_fetch_all("SELECT * FROM {config} WHERE module_name='%s' AND value='1' AND name LIKE 'where_access%'", $this->diafan->_admin->module);
		foreach($rows as $row)
		{
			$this->diafan->set_query($row["name"]."='%d'");
			$this->diafan->set_value(1);
		}
	}

	/**
	 * Сохранение поля "Бэкенд" для файла настроек
	 * @return void
	 */
	public function save_config_variable_backend()
	{
		if(! $variable = $this->diafan->variable('backend', 'variable'))
		{
			$variable = 'backend';
		}
		if (empty($_POST['backend']))
		{
			$this->diafan->set_query($variable."='%s'");
			$this->diafan->set_value('');
			return;
		}
		
		$backend = $this->diafan->filter($_POST, "string", "backend");		
		if (! Custom::exists('modules/'.$this->diafan->_admin->module.'/backend/'.$backend.'/'.$this->diafan->_admin->module.'.'.$backend.'.admin.php'))
		{
			$this->diafan->set_query($variable."='%s'");
			$this->diafan->set_value('');
			return;
		}
		Custom::inc('modules/'.$this->diafan->_admin->module.'/backend/'.$backend.'/'.$this->diafan->_admin->module.'.'.$backend.'.admin.php');
		$config_class = ucfirst($this->diafan->_admin->module).'_'.$backend.'_admin';
		$class = new $config_class($this->diafan);

		$values = array();
		foreach ($class->config["params"] as $key => $name)
		{
			if(! empty($name["type"]) && $name["type"] == 'info')
			{
				continue;
			}
			if(! empty($name["type"]) && $name["type"] == 'function')
			{
				if (is_callable(array(&$class, "save_variable_".$key)))
				{
					$this->diafan->set_query($backend."_".$key."='%s'");
					$value = call_user_func_array(array(&$class, "save_variable_".$key), array());
					$this->diafan->set_value($value);
					continue;
				}
			}
			if ( ! empty($_POST[$backend.'_'.$key]))
			{				
				$this->diafan->set_query($backend."_".$key."='%s'");
				$this->diafan->set_value($this->diafan->filter($_POST, 'string', $backend.'_'.$key));
			}
		}
		$this->diafan->set_query($variable."='%s'");
		$this->diafan->set_value($backend);
	}
}
