<?php
/**
 * Прямое обращение к файлам бэкенда капчи
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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

$rewrite_array = explode('/', $_GET["rewrite"]);

if($this->diafan->configmodules('backend', 'captcha') != $rewrite_array[0])
{
	Custom::inc('includes/404.php');
}

if(! Custom::exists('modules/captcha/backend/'.$rewrite_array[0].'/captcha.'.$rewrite_array[0].'.php'))
{
	Custom::inc('includes/404.php');
}
include_once(Custom::path('modules/captcha/backend/'.$rewrite_array[0].'/captcha.'.$rewrite_array[0].'.php'));
exit;