<?php
/**
 * Прямое обращение к файлам расширений
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

if(! DB::query_result("SELECT id FROM {shop_order_backend} WHERE trash='0' AND act='1' AND backend='%s'", $rewrite_array[0]))
{
	Custom::inc('includes/404.php');
}

if(! Custom::exists('modules/order/backend/'.$rewrite_array[0].'/order.'.$rewrite_array[0].'.php'))
{
	Custom::inc('includes/404.php');
}

include_once(Custom::path('modules/order/backend/'.$rewrite_array[0].'/order.'.$rewrite_array[0].'.php'));
exit;
