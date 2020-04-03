<?php
/**
 * Макрос для групповой операции: применение характеристик к категории
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
 * Ab_admin_param_group_category_rel
 */
class Ab_admin_param_group_category_rel extends Diafan
{
	/**
	 * Возвращает настройки
	 *
	 * @param string $value последнее выбранное групповое действие
	 * @return array|false
	 */
	public function show($value)
	{
		$config = array(
			'name' => 'Применить к категории',
			'rel' => array('category_unrel'),
		);

		if (count($this->diafan->categories))
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
		
		return $config;
	}

	/**
	 * Применяет характеристики к категории
	 *
	 * @return void
	 */
	public function action()
	{
		if(! empty($_POST["cat_id"]) || ! empty($_POST["ids"]))
		{
			$ids = $this->diafan->filter($_POST["ids"], "integer");
			DB::query("DELETE FROM {ab_param_category_rel} WHERE element_id IN(%s) AND cat_id IN(%d, 0)", implode(",", $ids), $_POST["cat_id"]);

			foreach ($ids as $id)
			{
				DB::query("INSERT INTO {ab_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $id, $_POST["cat_id"]);
			}
		}
	}
}