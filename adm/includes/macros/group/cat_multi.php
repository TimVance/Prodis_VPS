<?php
/**
 * Макрос для групповой операции: добавление дополнительной категории для элементов
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
 * Group_cat_multi
 */
class Group_cat_multi extends Diafan
{
	/**
	 * Возвращает настройки
	 *
	 * @param string $value последнее выбранное групповое действие
	 * @return array|false
	 */
	public function show($value)
	{
		if (! $this->diafan->config('element')
			|| ! count($this->diafan->categories)
			|| ! $this->diafan->config("element_multiple"))
		{
			return false;
		}
		if($this->diafan->_admin->module == 'photo')
		{
			$name = 'Дополнительная альбом';
		}
		else
		{
			$name = 'Дополнительная категория';
		}

		return array(
			'name' => $name,
		);
	}

	/**
	 * Добавляет дополнительную категорию для элементов
	 *
	 * @return void
	 */
	public function action()
	{
		if (empty( $_POST['ids'] ) || empty($_POST['cat_id']))
		{
			return;
		}
		if (! $this->diafan->config("element_multiple"))
		{
			return;
		}

		$cat_id = $this->diafan->filter($_POST, 'int', 'cat_id');
		$site_id = $this->diafan->filter($_POST, 'int', 'site_id');
		$ids = array();
		foreach ($_POST['ids'] as $id)
		{
			$id = intval($id);
			if($id)
			{
				$ids[] = $id;
			}
		}

		if($this->diafan->config("element_site"))
		{
			$site_id = DB::query_result("SELECT site_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->diafan->table, $cat_id);
		}
		else
		{
			$site_id = 0;
		}

		if($ids)
		{
			DB::query("DELETE FROM {%s_category_rel} WHERE element_id IN (%s) AND cat_id=%d", $this->diafan->_admin->module, implode(",", $ids), $cat_id);
			foreach($ids as $id)
			{
				DB::query("INSERT INTO {%s_category_rel} (element_id, cat_id) VALUES ('%d', '%d')", $this->diafan->_admin->module, $id, $cat_id);
			}
			// если категория не была задана, то назначаем дополнительную категорию главной
			DB::query("UPDATE {%h} SET cat_id=%d".($site_id ? ", site_id=".$site_id : "")." WHERE id IN (%h) AND cat_id=0", $this->diafan->table, $cat_id, implode(",", $ids));
		}
		$this->result["status"] = true;
	}
}