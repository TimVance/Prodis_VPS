<?php
/**
 * Макрос для групповой операции: перемещение элементов в категорию
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
 * Group_cat_id
 */
class Group_cat_id extends Diafan
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
			$name = 'Переместить в альбом';
		}
		else
		{
			$name = 'Переместить в категорию';
		}

		$config = array(
			'name' => $name,
			'rel' => array('cat_multi', 'cat_del'),
		);

		if ($this->diafan->config('element_multiple'))
		{
			$cats = array();
			$count = 0;
			foreach ($this->diafan->categories as $row)
			{
				$cats[$row["parent_id"]][] = $row;
				$count++;
			}

			if ($count > 0)
			{
				$config["html"] = '<select name="cat_id">';
				$config["html"] .= $this->diafan->get_options($cats, $cats[0], array($this->diafan->_route->cat)).'</select>';
			}
		}
		elseif ($this->diafan->config('category_flat') && count($this->diafan->categories))
		{
			$config["html"] = '<select name="cat_id">';
			$config["html"] .= $this->diafan->get_options(array(), $this->diafan->categories, array($this->diafan->_route->cat)).'</select>';
		}

		return $config;
	}

	/**
	 * Перемещает элементы в категорию
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
		foreach ($_POST['ids'] as $id)
		{
			$id = intval($id);
			if ($this->diafan->config("element_multiple"))
			{
				DB::query("DELETE FROM {%s_category_rel} WHERE element_id=%d", $this->diafan->_admin->module, $id);
				DB::query("INSERT INTO {%s_category_rel} (element_id, cat_id) VALUES('%d', '%d')", $this->diafan->_admin->module, $id, $cat_id);

				if($this->diafan->config("element_site"))
				{
					$site_id = DB::query_result("SELECT site_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->diafan->table, $cat_id);
				}
				else
				{
					$site_id = 0;
				}
				DB::query("UPDATE {%h} SET cat_id=%d".($site_id ? ", site_id=".$site_id : "")." WHERE id IN (%h)", $this->diafan->table, $cat_id, $id);

			}
			elseif ($cat_id && DB::query_result("SELECT cat_id FROM {%h} WHERE id=%d LIMIT 1", $this->diafan->table, $id) != $cat_id)
			{
				$children = array($id);
				if($this->diafan->variable_list('plus'))
				{
					$children = $this->diafan->get_children($id, $this->diafan->table);
					$children[] = $id;
				}

				if($this->diafan->config("element_site"))
				{
					$site_id = DB::query_result("SELECT site_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->diafan->table, $cat_id);
				}
				else
				{
					$site_id = 0;
				}
				DB::query("UPDATE {%h} SET cat_id=%d".($site_id ? ", site_id=".$site_id : "")." WHERE id IN (%h)", $this->diafan->table, $cat_id, implode(",", $children));
			}
		}
		$this->result["status"] = true;
	}
}