<?php
/**
 * Администрирование импорта/экспорт данных
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
 * Подключает редактирование списка категорий или полей
 */
function inc_file_shop($diafan)
{
	if ($diafan->_route->cat)
	{
		Custom::inc("modules/shop/admin/shop.admin.importexport.element.php");
		return 'Shop_admin_importexport_element';
	}
	else
	{
		Custom::inc("modules/shop/admin/shop.admin.importexport.category.php");
		return 'Shop_admin_importexport_category';
	}
}