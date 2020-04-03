<?php
/**
 * Обработка POST-запросов в административной части модуля
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
if ( ! defined('DIAFAN'))
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
 * Ab_admin_action
 */
class Ab_admin_action extends Action_admin
{
	/**
	 * Вызывает обработку POST-запросов
	 * 
	 * @return void
	 */
	public function init()
	{
		if ( ! empty($_POST["action"]))
		{
			switch ($_POST["action"])
			{
				case 'param_category_rel':
				case 'param_category_unrel':
					$this->param_category();
					break;
			}
		}
	}

	/**
	 * Прикрепляет/открепляет характеристику к категории
	 * 
	 * @return void
	 */
	private function param_category()
	{
		if(! empty($_POST["cat_id"]) || ! empty($_POST["ids"]))
		{
			$ids = array();
			foreach ($_POST["ids"] as $id)
			{
				$id = intval($id);
				if($id)
				{
					$ids[] = $id;
				}
			}
			if($ids)
			{
				if($_POST["action"] == 'param_category_rel')
				{
					DB::query("DELETE FROM {ab_param_category_rel} WHERE element_id IN(%s) AND cat_id IN(%d, 0)", implode(",", $ids), $_POST["cat_id"]);
				}
				else
				{
					DB::query("DELETE FROM {ab_param_category_rel} WHERE element_id IN(%s) AND cat_id=%d", implode(",", $ids), $_POST["cat_id"]);
				}
			}
			if($_POST["action"] == 'param_category_rel')
			{
				foreach ($ids as $id)
				{
					DB::query("INSERT INTO {ab_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $id, $_POST["cat_id"]);
				}
			}
			else
			{
				// выбираем все характеристики из выделенных, которые прикреплены к каким-нибудь категориям
				$cats_rel = DB::query_fetch_value("SELECT DISTINCT(element_id) FROM {ab_param_category_rel} WHERE element_id IN (%s)", implode(",", $ids), "element_id");
				// если характеристика не прикреплена ни к одной категории, делаем ее общей
				foreach($ids as $id)
				{
					if(! in_array($id, $cats_rel))
					{
						DB::query("INSERT INTO {ab_param_category_rel} (element_id) VALUES (%d)", $id);
					}
				}
			}
		}
	}
}
