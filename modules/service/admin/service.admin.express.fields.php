<?php
/**
 * Администрирование описания импорта/экспорта записей базы данных
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
function inc_file_service($diafan)
{
	$ajax = false;
	if (! empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest' || ! empty($_POST["ajax"]))
	{
		$ajax = true;
	}
	if(defined('IS_ADMIN') && IS_ADMIN)
	{
		if(! $ajax) $_SESSION['Service_admin_express']["mode_express_choice"] = 'fields';
	}
	if ($diafan->_route->cat)
	{
		Custom::inc("modules/service/admin/service.admin.express.fields.element.php");
		inc_file_express_modules( $diafan );
		return 'service_admin_express_fields_element';
	}
	else
	{
		Custom::inc("modules/service/admin/service.admin.express.fields.category.php");
		return 'Service_admin_express_fields_category';
	}
}
