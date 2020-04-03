<?php
/**
 * Платежная квитанция на оплату
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

header('Content-Type: text/html; charset=utf-8');
$rews = explode('/', $_GET["rewrite"]);
if($rews < 3)
{
	Custom::inc('includes/404.php');
}

$element_id = intval($rews[2]);
if (empty($element_id))
{
    Custom::inc('includes/404.php');
}

$code = ! empty($rews[3]) ? $rews[3] : '';

switch($rews[1])
{
	case 'fl':
		include_once(ABSOLUTE_PATH.Custom::path("modules/payment/backend/non_cash/payment.non_cash.fl.php"));
		break;

	case 'ul':
		include_once(ABSOLUTE_PATH.Custom::path("modules/payment/backend/non_cash/payment.non_cash.ul.php"));
		break;

	default:
		Custom::inc('includes/404.php');
}