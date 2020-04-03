<?php
/**
 * Обработка запроса при клике на ссылку баннера
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
 * Bs_action
 */
class Bs_action extends Action
{
	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function init()
	{
		if (! empty($_POST['banner_id']))
		{
			$row = DB::query_fetch_array("SELECT * FROM {bs} WHERE id=%d LIMIT 1", $_POST['banner_id']);
			if(! $row)
			{
				return;
			}
			if ($row['check_click'] && $row['show_click'])
			{
				DB::query("UPDATE {bs} SET click=click+1, show_click=show_click-1 WHERE id=%d", $row['id']);
			}
			else
			{
				DB::query("UPDATE {bs} SET click=click+1 WHERE id=%d", $row['id']);
			}
			$this->result["result"] = "success";
		}
	}
}