<?php
/**
 * Шаблон формы платежной системы
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

if(! empty($result["message"]))
{
	echo $result["message"];
}

if(! empty($result["payment"]))
{	
	include_once(Custom::path('modules/payment/backend/'.$result["payment"].'/payment.'.$result["payment"].'.view.php'));
}