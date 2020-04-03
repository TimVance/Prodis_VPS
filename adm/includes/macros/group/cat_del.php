<?php
/**
 * Макрос для групповой операции: удаление элементов из категории
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
 * Group_cat_del
 */
class Group_cat_del extends Diafan
{
	/**
	 * Возвращает настройки
	 *
	 * @param string $value последнее выбранное групповое действие
	 * @return array|false
	 */
	public function show($value)
	{
		if (! $this->diafan->config('element') || ! count($this->diafan->categories))
		{
			return false;
		}
		if($this->diafan->_admin->module == 'photo')
		{
			$name = 'Удалить из альбома';
		}
		else
		{
			$name = 'Удалить из категории';
		}

		return array(
			'name' => $name,
		);
	}

	/**
	 * Удаляет элементы из категории
	 *
	 * @return void
	 */
	public function action()
	{
		if (empty( $_POST['ids'] ) || empty($_POST['cat_id']))
		{
			return;
		}

		$cat_id = $this->diafan->filter($_POST, 'int', 'cat_id');
		$ids = array();
		foreach ($_POST['ids'] as $id)
		{
			$id = intval($id);
			if($id)
			{
				$ids[] = $id;
			}
		}

		if($ids)
		{
			DB::query("UPDATE {%h} SET cat_id=0 WHERE id IN (%s) AND cat_id=%d", $this->diafan->table, implode(",", $ids), $cat_id);
			if ($this->diafan->config("element_multiple"))
			{
				DB::query("DELETE FROM {%s_category_rel} WHERE element_id IN (%s) AND cat_id=%d", $this->diafan->_admin->module, implode(",", $ids), $cat_id);
				// если удалили главную категорию, то одну из дополнительных делаем главной
				$empty_ids = DB::query_fetch_value("SELECT id FROM {%h} WHERE id IN (%s) AND cat_id=0", $this->diafan->table, implode(",", $ids), "id");
				if($empty_ids)
				{
					$cat_ids_new = DB::query_fetch_key_value("SELECT element_id, cat_id FROM {%s_category_rel} WHERE element_id IN (%s) AND trash='0'", $this->diafan->_admin->module, implode(",", $empty_ids), "element_id", "cat_id");
					if($cat_ids_new)
					{
						$site_ids_new = DB::query_fetch_key_value("SELECT site_id, id FROM {%s_category} WHERE id IN (%s)", $this->diafan->_admin->module, implode(",", $cat_ids_new), "id", "site_id");
						foreach($cat_ids_new as $id => $cat_id)
						{
							DB::query("UPDATE {%h} SET cat_id=%d".(! empty($site_ids_new[$cat_id]) ? ", site_id=".$site_ids_new[$cat_id] : "")." WHERE id=%d", $this->diafan->table, $cat_id, $id);
						}
					}
				}
			}
		}
		$this->result["status"] = true;
	}
}