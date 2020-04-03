<?php
/**
 * Количество неотвеченных сообщений из формы обратной связи для меню административной панели
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
 * Feedback_admin_count
 */
class Feedback_admin_count extends Diafan
{
	/**
	 * Возвращает количество неотвеченных сообщений из формы обратной связи для меню административной панели
	 * @param integer $site_id страница сайта, к которой прикреплен модуль
	 * @return integer
	 */
	public function count($site_id)
	{
		$count = DB::query_result("SELECT COUNT(*) FROM {feedback} WHERE readed='0' AND trash='0'".($site_id ? " AND site_id=".$site_id : ""));
		return $count;
	}
}