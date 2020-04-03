<?php
/**
 * Макрос для групповой операции: открепление характеристик от категории
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
 * Shop_admin_param_group_category_unrel
 */
class Shop_admin_param_group_category_unrel extends Diafan
{
	/**
	 * Возвращает настройки
	 *
	 * @param string $value последнее выбранное групповое действие
	 * @return array|false
	 */
	public function show($value)
	{
		return array(
			'name' => 'Открепить от категории',
		);
	}

	/**
	 * Открепляет характеристики от категории
	 *
	 * @return void
	 */
	public function action()
	{
		if(! empty($_POST["cat_id"]) || ! empty($_POST["ids"]))
		{
			$ids = $this->diafan->filter($_POST["ids"], "integer");
			DB::query("DELETE FROM {shop_param_category_rel} WHERE element_id IN(%s) AND cat_id=%d", implode(",", $ids), $_POST["cat_id"]);

			// выбираем все характеристики из выделенных, которые прикреплены к каким-нибудь категориям
			$cats_rel = DB::query_fetch_value("SELECT DISTINCT(element_id) FROM {shop_param_category_rel} WHERE element_id IN (%s)", implode(",", $ids), "element_id");
			// если характеристика не прикреплена ни к одной категории, делаем ее общей
			foreach($ids as $id)
			{
				if(! in_array($id, $cats_rel))
				{
					DB::query("INSERT INTO {shop_param_category_rel} (element_id) VALUES (%d)", $id);
				}
			}
		}
	}
}