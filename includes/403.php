<?php
/**
 * Ошибка 403. Доступ запрещен
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

global $diafan;
Custom::inc('includes/controller.php');
$diafan->_site->theme = '403.php';
$diafan->_site->nocache = true;
$diafan->_site->timeedit = time();

header('HTTP/1.0 403 Forbidden');
header('Content-Type: text/html; charset=utf-8');

$mod = new Controller($diafan);
$diafan->_parser_theme->show_theme($mod);

if(! empty($diafan->_site->js_code["Visitors_inc_counter"]))
{
	echo $diafan->_site->js_code["Visitors_inc_counter"];
	unset($diafan->_site->js_code["Visitors_inc_counter"]);
	if(! empty($diafan->_admin->js_code["Visitors_inc_counter"]))
		unset($diafan->_admin->js_code["Visitors_inc_counter"]);
}

exit;
