<?php
/**
 * Количество непроверенных отзывов, если подключено модерирование отзывов, для меню административной панели
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
 * Reviews_admin_count
 */
class Reviews_admin_count extends Diafan
{
	/**
	 * Возвращает количество непроверенных отзывов, если подключено модерирование отзывов, для меню административной панели
	 * @return integer
	 */
	public function count()
	{
		$count = DB::query_result("SELECT COUNT(*) FROM {reviews} WHERE readed='0' AND trash='0'");
		return $count;
	}
}