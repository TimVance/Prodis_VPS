<?php
/**
 * Платежная квитанция на оплату для физ.лица
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

if (empty($code))
{
    Custom::inc('includes/404.php');
}

$pay = DB::query_fetch_array("SELECT * FROM {payment_history} WHERE id=%d AND code='%s'", $element_id, $code);

if(empty($pay))
{
	Custom::inc('includes/404.php');
}

switch($pay["module_name"])
{
	case 'balance':
		$row["summ"] = $pay["summ"];
		$row["created"] = $pay["created"];
		break;

	case 'cart':			
		$row = DB::query_fetch_array("SELECT user_id, summ, created, delivery_id, delivery_summ FROM {shop_order} WHERE id=%d AND trash='0' LIMIT 1",$pay["element_id"]);
		break;		
}

if (empty($row))
{
    Custom::inc('includes/404.php');
}

$values = unserialize(DB::query_result("SELECT params FROM {payment} WHERE id=%d LIMIT 1", $pay['payment_id']));

$order_created = $row["created"];
$order_summ = $row["summ"];

if($pay["module_name"] == 'cart')
{
	$values["delivery"] = array();
	if(! empty($row["delivery_id"]))
	{
		$values["delivery"] = DB::query_fetch_array("SELECT [name] FROM {shop_delivery} WHERE id=%d LIMIT 1", $row["delivery_id"]);
		$values["delivery"]['price'] = $row["delivery_summ"];
	}
	
	$values['user_fio'] = '';
	$params = DB::query_fetch_key_value("SELECT param_id, value FROM {shop_order_param_element} WHERE element_id=%d", $pay["element_id"], "param_id", "value");
	
	$rows_p = DB::query_fetch_all("SELECT id, info, type FROM {shop_order_param} WHERE trash='0'");
	foreach ($rows_p as $row_p)
	{
		if(empty($params[$row_p["id"]]))
			continue;
	
		switch($row_p["info"])
		{
			case 'name':
			case 'firstname':
			case 'lastname':
			case 'fathersname':
				$values['user_fio'] .= ($values['user_fio'] ? ' ' : '').$params[$row_p["id"]];
				break;
		}
		$user[$row_p["info"]] = (! empty($user[$row_p["info"]]) ? ', ' : '').$params[$row_p["id"]];
	}
}
else
{
	$row_user = DB::query_fetch_array("SELECT * FROM {users} WHERE id=%d", $pay["element_id"]);
	if (empty($row_user))
	{
		Custom::inc('includes/404.php');
	}
	$values['user_fio'] = $row_user["fio"];
}

$months_array = array(
	'01' => $this->diafan->_('января'),
	'02' => $this->diafan->_('февраля'),
	'03' => $this->diafan->_('марта'),
	'04' => $this->diafan->_('апреля'),
	'05' => $this->diafan->_('мая'),
	'06' => $this->diafan->_('июня'),
	'07' => $this->diafan->_('июля'),
	'08' => $this->diafan->_('августа'),
	'09' => $this->diafan->_('сентября'),
	'10' => $this->diafan->_('октября'),
	'11' => $this->diafan->_('ноября'),
	'12' => $this->diafan->_('декабря')
);

foreach (array(
	'non_cash_name',
	'non_cash_kpp',
	'non_cash_inn',
	'non_cash_tax_department',
	'non_cash_okato',
	'non_cash_ogrn',
	'non_cash_rs',
	'non_cash_bank',
	'non_cash_bik',
	'non_cash_ks',
	'non_cash_kbk',
	'non_cash_address',
	'non_cash_director',
	'non_cash_glbuh') as $name)
{
	if(! isset($values[$name]))
	{
		$values[$name] = '';
	}
}
if($pay["module_name"] == 'cart')
{
	$module_name_config = 'shop';
}
else
{
	$module_name_config = $pay["module_name"];
}
$values["order_name"] = str_replace(
	'%id',
	$pay["element_id"],
	$this->diafan->configmodules("desc_payment", $module_name_config)
);
$values["summ_rub"] = number_format($order_summ, 0, ',', ' ');
if ($this->diafan->configmodules("format_price_1", "shop"))
{
	$values["summ_kop"] = $order_summ * 100 % 100;
	
	if($values["summ_kop"] < 10)
	{
		$values["summ_kop"] = '0'.$values["summ_kop"];
	}
}
else
{
	$values["summ_kop"] = "00";
}
$values["date_d"] = date("d", $order_created);
$values["date_m"] = $months_array[date("m", $order_created)];
$values["date_y"] = date("Y", $order_created);

include_once(ABSOLUTE_PATH.Custom::path('modules/payment/backend/non_cash/payment.non_cash.view.fl.php'));