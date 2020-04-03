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
 * Action_functions_admin
 *
 * Обработка POST-запросов
 */
class Action_functions_admin extends Action_admin
{
	/**
	 * Обработчик функции быстрого сохранения полей
	 *
	 * @return void
	 */
	public function fast_save()
	{
		$this->result["res"] = false;
		if(empty($_POST['name']) || empty($this->diafan->variables_list[$_POST['name']]) || empty($this->diafan->variables_list[$_POST['name']]["fast_edit"]))
			return;

		$func = 'fast_save_'.preg_replace('/[^a-z_]+/', '', $_POST['name']);
		$this->result["res"] = call_user_func_array (array(&$this->diafan, $func), array());
		if ($this->result["res"] === 'fail_function')
		{
			if(! empty($this->diafan->variables_list[$_POST['name']]["type"]) && $this->diafan->variables_list[$_POST['name']]["type"] == 'numtext')
			{
				$_POST['value'] = str_replace(' ', '', $_POST['value']);
			}
			elseif(! empty($this->diafan->variables_list[$_POST['name']]["type"]) && ($this->diafan->variables_list[$_POST['name']]["type"] == 'datetime' || $this->diafan->variables_list[$_POST['name']]["type"] == 'date'))
			{
				$_POST['value'] = $this->diafan->unixdate($_POST['value']);
				$_POST['type'] = false;
			}
			$this->result["res"] = (bool)DB::query('UPDATE {'.$this->diafan->table.'} SET `%h`="'.((bool)$_POST['type'] ? '%h' : '%d' ).'" WHERE id=%d LIMIT 1', $_POST['name'].($this->diafan->variable_multilang($_POST["name"], "multilang") ? _LANG : ''), $_POST['value'], $_POST['id']);
		}
		// Удаляет кэш модуля
		$this->diafan->_cache->delete("", $this->diafan->_admin->module);
	}

	/**
	 * Изменяет количество элементов на странице
	 *
	 * @return void
	 */
	public function change_nastr()
	{
		$nastr = $this->diafan->filter($_POST, 'int', 'nastr');
		if($this->diafan->_users->admin_nastr != $nastr)
		{
			if($nastr > 500)
			{
				$nastr = 500;
			}
			DB::query("UPDATE {users} SET admin_nastr=%d WHERE id=%d", $nastr, $this->diafan->_users->id);
		}
		$this->result["redirect"] = $this->diafan->get_admin_url('page');
	}

	/**
	 * Сохраняет настройки интерфейса администратора
	 *
	 * @return void
	 */
	public function settings()
	{
		if(! in_array($_POST["name"], array('nav_box_compress', 'useradmin_is_toggle')))
		{
			return;
		}
		$cfg = unserialize($this->diafan->_users->config);
		if(empty($_POST["value"]))
		{
			if(isset($cfg[$_POST["name"]]))
			{
				unset($cfg[$_POST["name"]]);
			}
		}
		else
		{
			$cfg[$_POST["name"]] = 1;
		}
		if($cfg)
		{
			$cfg = serialize($cfg);
		}
		else
		{
			$cfg = '';
		}
		DB::query("UPDATE {users} SET config='%s' WHERE id=%d", $cfg, $this->diafan->_users->id);
	}

	/**
	 * Подгружает список для сортировки элементов
	 *
	 * @return void
	 */
	public function sort()
	{
		if($this->diafan->variable_list("name", "variable"))
		{
			$list_name = $this->diafan->variable_list("name", "variable");
		}
		else
		{
			$list_name = 'name';
		}
		$list_name = ($this->diafan->variable_multilang($list_name) ? $list_name._LANG : $list_name);

		$lang_act = ($this->diafan->variable_multilang("act") ? _LANG : '');

		$text = '<select name="sort">';

		$parent_id = $this->diafan->filter($_POST, 'int', "parent_id");
		$cat_id = $this->diafan->filter($_POST, 'int', "cat_id");
		$site_id = $this->diafan->filter($_POST, 'int', "site_id");
		$sort = $this->diafan->filter($_POST, 'int', "sort");
		$id = $this->diafan->filter($_POST, 'int', "id");

		//список элементов, которые при сортировке стоят перед редактируемым элементом
		$rows = $this->diafan->get_select_from_db(array(
			"table" => $this->diafan->table,
			"name" => $list_name,
			"where" => (isset($_POST["parent_id"]) ? "parent_id=".$parent_id." AND " : '')
				.(isset($_POST["cat_id"]) ? "cat_id=".$cat_id." AND " : '')
				.(isset($_POST["site_id"]) ? "site_id=".$site_id." AND " : '')
				."sort".($this->diafan->variable_list('sort', 'desc') ? '>' : '<=').$sort
				.($this->diafan->is_variable("act") ? " AND act".$lang_act."='1'" : '')
				." AND id<>'".$id."'"
				.$this->diafan->where
				.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : ''),
			"order" => ($this->diafan->is_variable("act") ? "act".$lang_act." DESC," : '')
				.($this->diafan->variable_list('sort', 'desc') ? 'sort DESC, id DESC' : 'sort ASC, id ASC')
			));
		foreach($rows as $k => $v)
		{
			$text .= '<option value="'.$k.'">'.$v.'</option>';
		}

		$text .= '<option value="'.$id.'" selected>----'.( $_POST["name"] ? $_POST["name"] : $id ).'---</option>';

		//список элементов, которые при сортировке стоят после редактируемого элемента
		$rows = $this->diafan->get_select_from_db(array(
			"table" => $this->diafan->table,
			"name" => $list_name,
			"where" => (isset($_POST["parent_id"]) ? "parent_id=".$parent_id." AND " : '')
				.(isset($_POST["cat_id"]) ? "cat_id=".$cat_id." AND " : '')
				.(isset($_POST["site_id"]) ? "site_id=".$site_id." AND " : '')
				."sort".($this->diafan->variable_list('sort', 'desc') ? '<=' : ">").$sort
				.($this->diafan->is_variable("act") ? " AND act".$lang_act."='1'" : '')
				." AND id<>'".$id."'"
				.$this->diafan->where
				.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : ''),
			"order" => ($this->diafan->is_variable("act") ? "act".$lang_act." DESC," : '')
				.($this->diafan->variable_list('sort', 'desc') ? 'sort DESC, id DESC' : 'sort ASC, id ASC')
			));
		foreach($rows as $k => $v)
		{
			$text .= '<option value="'.$k.'">'.$v.'</option>';
		}
		$text .= '<option value="down">----'.$this->diafan->_('Вниз').'---</option></select>';

		$this->result["data"] = $text;
	}

	/**
	 * Подгружает список родителей
	 *
	 * @return void
	 */
	public function parent_id()
	{
		$_POST['parent_id'] = $this->diafan->filter($_POST, 'int', 'parent_id');

		if($this->diafan->variable_list("name", "variable"))
		{
			$list_name = $this->diafan->variable_list("name", "variable");
		}
		else
		{
			$list_name = 'name';
		}

		$rows = DB::query_fetch_all("SELECT id, ".($this->diafan->variable_multilang($list_name) ? '['.$list_name.']' : $list_name).", parent_id FROM {".$this->diafan->table."} WHERE id<>%d"
		.($this->diafan->_admin->module == 'site' ? " AND id<>1" : '')
		.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '')
		." ORDER BY id DESC", $_POST['id']);

		foreach ($rows as $row)
		{
			$row["name"] = $row[$list_name];
			$cats[$row["parent_id"]][] = $row;
		}
		$this->result["data"] = '<select name="parent_id" upload="1">
			<option value="">'.( $this->diafan->_admin->module == 'site' ? $this->diafan->_('Главная') : '' ).'</option>'
			.$this->diafan->get_options($cats, $cats[0], array ($_POST["parent_id"])).'</select>';
	}

	/**
	 * Подгружает список пользователей
	 *
	 * @return void
	 */
	public function user_list()
	{
		$this->result["data"] = '<ul class="user_search_select">';
		$rows = DB::query_fetch_all("SELECT id, name, fio FROM {users} WHERE name LIKE '%s%%' OR fio LIKE '%s%%'", $_POST["search"], $_POST["search"]);
		foreach ($rows as $row)
		{
			$this->result["data"] .= '<li user_id="'.$row["id"].'"><span>'.$row["fio"].' ('.$row["name"].')</span></li>';
			$find = true;
		}
		if(empty($find))
		{
			$this->result["data"] .= $this->diafan->_('Ничего не найдено.');
		}
		$this->result["data"] .= '</ul>';
	}

	/**
	 * Подгружает список категорий
	 *
	 * @return void
	 */
	public function cat_list()
	{
		$ids = array();
		$cat_id = $this->diafan->filter($_POST, 'int', 'cat_id');
		if($cat_id)
		{
			$ids[] = $cat_id;
		}
		if(! empty($_POST['cat_ids']))
		{
			$cat_ids = is_array($_POST['cat_ids']) ? $_POST['cat_ids'] : array($_POST['cat_ids']);
			foreach ($cat_ids as $id)
			{
				$id = (int) $id;
				if($id)
				{
					$ids[] = $id;
				}
			}
		}
		if($ids)
		{
			$ids = implode(",", $ids);
		}
		else $ids = '';

		$this->result["data"] = '<ul class="cat_search_select">';
		$rows = DB::query_fetch_all("SELECT id, [name] FROM {%s_category} WHERE [name] LIKE '%s%%' AND trash='0'".($ids ? " AND id NOT IN (%s)" : ""), $this->diafan->_admin->module, $_POST["search"], $ids);
		$find = false;
		foreach ($rows as $row)
		{
			$this->result["data"] .= '<li cat_id="'.$row["id"].'" href="'.BASE_PATH_HREF.$this->diafan->_admin->module.'/category/edit'.$row["id"].'/">'.$row["name"].'</li>';
			$find = true;
		}
		if(empty($find))
		{
			$this->result["data"] .= $this->diafan->_('Ничего не найдено.');
		}
		$this->result["data"] .= '</ul>';
	}
}
