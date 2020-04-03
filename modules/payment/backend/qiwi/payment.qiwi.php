<?php
/**
 * Обработка данных, полученных от системы  QIWI
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

// запрос на подтверждение счета
if (!empty($_POST["qiwi_id"]) OR (! empty($_GET["order"]) AND $_GET["order"] <> 1)) 
{		
	$pay_id = $_POST["qiwi_id"]; //номер счет, статус которого интересует

	if (! empty($_GET["order"]) AND $_GET["order"] <> 1)
	{
		//номер счета, который пришел с киви
		$pay_id = $_GET["order"];
	}
}
if(empty($pay_id))
{
	Custom::inc('includes/404.php');
}

$pay = $this->diafan->_payment->check_pay($pay_id, 'qiwi');

if ($_GET["rewrite"] == "qiwi/success")
{
	// если неоплаченный счет есть, начинаем спрашивать на сайте киви его статус
	if ($pay["summ"] > 0)
	{
		//сформировали запрос XML
		$xml='<?xml version="1.0" encoding="utf-8"?>
<request>
<protocol-version>4.00</protocol-version>
<request-type>33</request-type>
<extra name="password">'.$pay["params"]["qiwi_password"].'</extra>
<terminal-id>'.$pay["params"]["qiwi_id"].'</terminal-id>
<bills-list>
<bill txn-id="'.$pay_id.'"/>
</bills-list>
</request>';
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, "http://ishop.qiwi.ru/xml");
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($ch, CURLOPT_POST,1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); 
		//ответ от киви
		$answer = curl_exec($ch);
		//если есть строка со статусом 60 (т.е. счет оплачен), и сумма равна выставленной изначально, то зачисляем деньги пользователю
		if (strpos($answer, 'status="60"') AND strpos($answer, 'sum="'.$pay["summ"]))
		{
			$this->diafan->_payment->success($pay);
		}
	}
}
$this->diafan->_payment->fail($pay);