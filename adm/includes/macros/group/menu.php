<?php
/**
 * Макрос для групповой операции: добавление пункта в меню
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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
 * Group_menu
 */
class Group_menu extends Diafan
{
	/**
	 * Возвращает настройки
	 *
	 * @param string $value последнее выбранное групповое действие
	 * @return array|false
	 */
	public function show($value)
	{
		if (! $this->diafan->is_variable("menu"))
		{
			return false;
		}

		$config = array(
			'name' => 'Отображается в меню',
		);

		$config['html'] = '';
		$rows = DB::query_fetch_all("SELECT id, [name] FROM {menu_category} WHERE trash='0' AND [act]='1' ORDER BY id DESC");
		foreach ($rows as $row)
		{
			$config['html'] .= '<input type="checkbox" name="menu_cat_ids[]" id="input_menu_cat_ids_'.$row["id"].'" value="'.$row["id"].'"> <label for="input_menu_cat_ids_'.$row["id"].'">'.$row["name"].'</label><br>';
		}

		return $config;
	}
		

	/**
	 * Добавляет пункты в меню
	 *
	 * @return void
	 */
	public function action()
	{
		Custom::inc('modules/menu/admin/menu.admin.inc.php');

		$_POST['ids'] = array_reverse($_POST['ids']);
		$ids = $this->diafan->filter($_POST['ids'], "ids");
		if(! empty($ids))
		{
			$rows = DB::query_fetch_all("SELECT * FROM {%h} WHERE id IN (%s)", $this->diafan->table, implode(",", $ids));
			foreach($rows as $row)
			{
				$save = array(
					"element_id" => $row["id"],
					"module_name" => $this->diafan->_admin->module, 
					"element_type" => $this->diafan->element_type(),
					"is_new" => false,
					"parent_id" => ! empty($row["parent_id"]) ? $row["parent_id"] : '',
					"cat_id" => ! empty($row["cat_id"]) ? $row["cat_id"] : 0,
					"site_id" => ! empty($row["site_id"]) ? $row["site_id"] : 0,
					"name" => htmlspecialchars_decode($row["name"._LANG]),
					"old_name" => $row["name"._LANG],
					"access" => $row["access"],
					"old_access" => $row["access"],
					"sort" => ! empty($row["sort"]) ? $row["sort"] : 0,
					"act" => $row["act"._LANG],
					"date_start" => ! empty($row["date_start"]) ? $row["date_start"] : 0,
					"old_date_start" => ! empty($row["date_start"]) ? $row["date_start"] : 0,
					"date_finish" => ! empty($row["date_finish"]) ? $row["date_finish"] : 0,
					"old_date_finish" => ! empty($row["date_finish"]) ? $row["date_finish"] : 0
				);
				$menu_cat_ids = ! empty($_POST["menu_cat_ids"]) ? $_POST["menu_cat_ids"] : array();
				$menu_cat_ids = array_unique($menu_cat_ids);
				$menu_inc = new Menu_admin_inc($this->diafan);
				$menu_inc->save_menu($save, $menu_cat_ids);
			}
		}
	}
}