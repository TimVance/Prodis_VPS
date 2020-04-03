<?php
/**
 * Количество доступных обновлений для меню административной панели
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
 * Update_admin_count
 */
class Update_admin_count extends Diafan
{
	/**
	 * Возвращает количество доступных обновлений для меню административной панели
	 * @param integer $site_id страница сайта, к которой прикреплен модуль
	 * @return integer
	 */
	public function count($site_id)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));
		if(! isset($_SESSION["update_count"])
		|| ! isset($_SESSION["update_count"]["time"])
		|| ! isset($_SESSION["update_count"]["value"])
		|| $_SESSION["update_count"]["time"] != $time)
		{
			$this->diafan->_admin->js_view[] = 'modules/update/admin/js/update.admin.count.js';
			$count = 0;
		}
		else
		{
			$count = (int) $_SESSION["update_count"]["value"];
			if(! empty($_SESSION["update_count"]["messages"]))
			{
				$this->diafan->_admin->js_code[] = '<script language="javascript" type="text/javascript">
$(document).ready(function(e){
	var item = $(".wrap .col-right").eq(0);
	if(item.length)
	{
		item.prepend(\''.addslashes($_SESSION["update_count"]["messages"]).'\');
	}
});
</script>';
			}
		}
		return $count;
	}
}
