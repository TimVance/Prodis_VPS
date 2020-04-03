<?php
/**
 * Количество неактивных тем и сообщений на форуме для меню административной панели
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
 * Forum_admin_count
 */
class Forum_admin_count extends Diafan
{
	/**
	 * Возвращает количество неактивных тем и сообщений на форуме объявлений для меню административной панели
	 * @param integer $site_id страница сайта, к которой прикреплен модуль
	 * @return integer
	 */
	public function count($site_id)
	{
		$count = DB::query_result("SELECT COUNT(*) FROM {forum} WHERE act='0' AND trash='0'");
		$count += DB::query_result("SELECT COUNT(*) FROM {forum_messages} WHERE act='0' AND trash='0'");
		return $count;
	}
}