<?php
/**
 * Количество непрочитанных уведомлений службы поддержки для меню административной панели
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
 * Account_admin_count
 */
class Account_admin_count extends Diafan
{
	/**
	 * Возвращает количество непрочитанных уведомлений службы поддержки для меню административной панели
	 *
	 * @param integer $site_id страница сайта, к которой прикреплен модуль
	 * @return integer
	 */
	public function count($site_id)
	{
		$count = 0;
		if(Custom::exists('modules/account/admin/account.admin.support.tab_count.php'))
		{
			Custom::inc('modules/account/admin/account.admin.support.tab_count.php');
			$class = 'Account_admin_support_tab_count';
			if (method_exists($class, 'count'))
			{
				$class_count_menu = new $class($this->diafan);
				$count += $class_count_menu->count();
			}
		}
		return $count;
	}
}
