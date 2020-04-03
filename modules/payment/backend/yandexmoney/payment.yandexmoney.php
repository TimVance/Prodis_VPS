<?php
/**
 * Работа с платежной системой Яндекс.Касса
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

if (empty($_POST['orderNumber']))
{
	if (! empty($_POST['ordernumber']))
	{
		$_POST['orderNumber'] = $_POST['ordernumber'];
	}
	elseif (! empty($_GET['orderNumber']))
	{
		$_POST['orderNumber'] = $_GET['orderNumber'];
	}
	elseif (! empty($_GET['ordernumber']))
	{
		$_POST['orderNumber'] = $_GET['ordernumber'];
	}
	else
	{
		Custom::inc('includes/404.php');
	}
}

// принудительная отмена сжатия данных
$this->diafan->_site->nozip = true;

$pay = $this->diafan->_payment->check_pay($_POST['orderNumber'], 'yandexmoney');

if($_POST['action'] == 'checkOrder' && !empty($pay["params"]['yandex_scid']) && $_POST['scid'] == $pay["params"]['yandex_scid'])
{
	if($pay["summ"] == $_POST['orderSumAmount'])
	{
		header('Content-type: application/xml; charset=utf-8');
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<checkOrderResponse'
					.' performedDatetime ="'.$_POST['orderCreatedDatetime'].'"'
					.' code="0"'
					.' invoiceId="'.$_POST['invoiceId'].'"'
					.' shopId="'.$_POST['shopId'].'"'
					.'/>';
		exit;
	}
	else
	{
		Custom::inc('includes/404.php');
	}
}

//если это подтверждение оплаты и номер магазина - наш
if($_POST['action'] == 'paymentAviso' && !empty($pay["params"]['yandex_scid']) && $_POST['scid'] == $pay["params"]['yandex_scid'])
{
	$out_summ = $_POST['orderSumAmount'];

	$chkstring =
	$_POST['action'].';'
	.$_POST['orderSumAmount'].';'
	.$_POST['orderSumCurrencyPaycash'].';'
	.$_POST['orderSumBankPaycash'].';'
	.$pay["params"]['yandex_shopid'].';' //номер магазина в Яндекс.деньгах
	.$_POST['invoiceId'].';'
	.$_POST['customerNumber'].';'
	.$pay["params"]['yandex_password'] //shopPassword из анкеты магазина в Яндекс.Деньгах
	;

	if($_POST['md5'] == strtoupper(md5($chkstring)))
	{
		$this->diafan->_payment->success($pay, 'pay');

		$code = 0;
	}
	else
	{
		$code = 1;
	}

	header('Content-type: application/xml; charset=utf-8');
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<paymentAvisoResponse'
				.' performedDatetime ="'.$_POST['orderCreatedDatetime'].'"'
				.' code="'.$code.'"'
				.' invoiceId="'.$_POST['invoiceId'].'"'
				.' shopId="'.$_POST['shopId'].'"'
				.'/>';
	exit;
}


// оплата прошла успешно
if ($_GET["rewrite"] == "yandexmoney/success")
{
	$this->diafan->_payment->success($pay, 'redirect');
}

$this->diafan->_payment->fail($pay);
