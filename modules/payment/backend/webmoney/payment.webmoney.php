<?php
/**
 * Обработка данных, полученных от системы WebMoney
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

if (! isset($_POST['LMI_PAYMENT_NO']) || preg_match('/^\d+$/', $_POST['LMI_PAYMENT_NO']) != 1)
{
	Custom::inc('includes/404.php');
}

$pay = $this->diafan->_payment->check_pay($_POST['LMI_PAYMENT_NO'], 'webmoney');

// проверка валидности запроса
if ($_GET["rewrite"] == "webmoney/result")
{
	File::save_file(serialize($_POST), 'tmp/wm'.time().'_'.rand(0, 999));

	if (isset($_POST['LMI_PREREQUEST']) && $_POST['LMI_PREREQUEST'] == 1)
	{
		if (! isset($_POST['RND']) || preg_match('/^[A-Z0-9]{8}$/', $_POST['RND'], $match) != 1)
		{
			Custom::inc('includes/404.php');
		}
		if($pay["summ"] != $_POST['LMI_PAYMENT_AMOUNT'])
		{
			Custom::inc('includes/404.php');
		}
		header('Content-Type: text/html; charset=utf-8');
		echo 'YES';
	}
	else
	{
		if($pay["summ"] != $_POST['LMI_PAYMENT_AMOUNT'])
		{
			Custom::inc('includes/404.php');
		}

		$chkstring = $pay["params"]['wm_target']
			.$_POST["LMI_PAYMENT_AMOUNT"]
			.$pay['id']
			.$_POST['LMI_MODE']
			.$_POST['LMI_SYS_INVS_NO']
			.$_POST['LMI_SYS_TRANS_NO']
			.$_POST['LMI_SYS_TRANS_DATE']
			.$pay["params"]['wm_secret']
			.$_POST['LMI_PAYER_PURSE']
			.$_POST['LMI_PAYER_WM'];

		$hash = strtoupper(hash("sha256", $chkstring));

		if ($_POST['LMI_PAYEE_PURSE'] != $pay["params"]['wm_target'] || $_POST['LMI_HASH'] != $hash)
		{
			Custom::inc('includes/404.php');
		}
		$this->diafan->_payment->success($pay, 'pay');
	}
	exit;
}

// оплата прошла успешно
if ($_GET["rewrite"] == "webmoney/success")
{
	$this->diafan->_payment->success($pay, 'redirect');
}

$this->diafan->_payment->fail($pay);
