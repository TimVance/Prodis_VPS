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
 * Menu_inc
 */
class Menu_inc extends Model
{
	/**
	 * Удаляет один или несколько пунктов меню
	 *
	 * @param integer|array $element_ids номер одного или нескольких элементов
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @param integer $menu_cat_id номер категории меню
	 * @return void
	 */
	public function delete($element_ids, $module_name, $element_type = 'element', $menu_cat_id = 0)
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
		$menu_cat_id = intval($menu_cat_id);
		$ids = DB::query_fetch_value("SELECT id FROM {menu} WHERE module_name='%h' AND element_id".$where." AND element_type='%s'".($menu_cat_id ? " AND cat_id=".$menu_cat_id : ''), $module_name, $value, $element_type, "id");

		$children = array();
		foreach($ids as $id)
		{
			$children[] = $id;
			$children = array_merge($children, $this->diafan->get_children($id, "menu", true));
		}
		if($children)
		{
			DB::query("DELETE FROM {menu_parents} WHERE parent_id IN (%s)", implode(',', $children));
			DB::query("DELETE FROM {menu_parents} WHERE element_id IN (%s)", implode(',', $children));
			DB::query("DELETE FROM {menu} WHERE id IN (%s)", implode(',', $children));
		}
	}

	/**
	 * Удаляет все пункты меню модуля
	 *
	 * @param string $module_name название модуля
	 * @return void
	 */
	public function delete_module($module_name)
	{
		DB::query("DELETE FROM {trash} WHERE module_name='menu' AND element_id IN (SELECT id FROM {menu} WHERE module_name='%s')", $module_name);
		$ids = DB::query_fetch_value("SELECT id FROM {menu} WHERE module_name='%h'", $module_name, "id");

		$children = array();
		foreach($ids as $id)
		{
			$children[] = $id;
			$children = array_merge($children, $this->diafan->get_children($id, "menu", true));
		}
		if($children)
		{
			DB::query("DELETE FROM {menu_parents} WHERE parent_id IN (%s)", implode(',', $children));
			DB::query("DELETE FROM {menu_parents} WHERE element_id IN (%s)", implode(',', $children));
			DB::query("DELETE FROM {menu} WHERE id IN (%s)", implode(',', $children));
		}
	}
}
