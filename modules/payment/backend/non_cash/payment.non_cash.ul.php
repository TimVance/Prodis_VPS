<?php
/**
 * Платежная квитанция на оплату для юр.лица
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


$values["module_name"] = $pay["module_name"];

$order_created = $row["created"];
$order_summ = $row["summ"];

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

$values["order_id"] = $pay["element_id"];
$values["order_created"] = date("d.m.Y", $order_created);

$values["goods"] = array();

if($pay["module_name"] == 'cart')
{
	$values["delivery"] = array();
	if(! empty($row["delivery_id"]))
	{
		$values["delivery"] = DB::query_fetch_array("SELECT [name] FROM {shop_delivery} WHERE id=%d LIMIT 1", $row["delivery_id"]);
		$values["delivery"]['price'] = $row["delivery_summ"];
	}
	
	$values["user_fio"] = '';
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
				$values["user_fio"] .= ($values["user_fio"] ? ' ' : '').$params[$row_p["id"]];
				break;
		}
		$user[$row_p["info"]] = (! empty($user[$row_p["info"]]) ? ', ' : '').$params[$row_p["id"]];
	}

	$order_summ = 0;
	$order_count = 0;

	$rows = DB::query_fetch_all("SELECT a.id, a.[name], a.price, a.amount, s.id AS sid, s.summ, s.order_goods_id FROM {shop_additional_cost} AS a INNER JOIN {shop_order_additional_cost} AS s ON s.additional_cost_id=a.id AND s.order_id=%d WHERE a.trash='0'", $pay["element_id"]); 
	foreach ($rows as $row_good)
	{
		if($row_good["sid"])
		{
			$row_good['price'] = $row_good['summ'];
		}
		else
		{
			if (! empty($row_good['amount']))
			{
				if ($row_good['amount'] < $summ)
				{
					$row_good['price'] = 0;
				}
			}
		}
		$order_summ += $row_good["price"];
		$order_count++;
		$row_good["article"] = '';
		$row_good["count_goods"] = 1;
		$row_good["summ"] = $this->diafan->_shop->price_format($row_good["price"]);
		$row_good["discount"] = 0;
		$additional_costs[$row_good["order_goods_id"]][] = $row_good;
	}
	$rows = DB::query_fetch_all("SELECT * FROM {shop_order_goods} where order_id = %d", $pay["element_id"]);
	foreach ($rows as $row_good)
	{
		$order_summ += $row_good["price"] * $row_good["count_goods"];
		$order_count += $row_good["count_goods"];
		$depend = '';
		$rows_p = DB::query_fetch_all("SELECT * FROM {shop_order_goods_param} WHERE order_goods_id=%d", $row_good["id"]);
		foreach ($rows_p as $row_p)
		{
			if(! $row_p["value"])
				continue;
			$param_name = DB::query_result("SELECT [name] FROM {shop_param} WHERE id=%d LIMIT 1", $row_p["param_id"]);
			$param_value = DB::query_result("SELECT s.[name] FROM {shop_param_select} as s WHERE s.id=%d AND s.param_id=%d LIMIT 1", $row_p["value"], $row_p["param_id"]);
			$depend .= ($depend ? ', ' : ' ').$param_name.': '.$param_value;
		}
		$row_shop = DB::query_fetch_array("SELECT [name], [measure_unit] FROM {shop} WHERE id=%d LIMIT 1", $row_good["good_id"]);
		$row_good["name"] = $row_shop["name"].$depend;
		$row_good["measure_unit"] = $row_shop["measure_unit"];
		$row_good["summ"] = $this->diafan->_shop->price_format($row_good["price"] * $row_good["count_goods"]);
		$row_good["price"] = $this->diafan->_shop->price_format($row_good["price"]);
		$values["goods"][] = $row_good;

		if(! empty($additional_costs[$row_good["id"]]))
		{
			foreach($additional_costs[$row_good["id"]] as $a)
			{
				$a["price"] = $this->diafan->_shop->price_format($a["price"]/$row_good["count_goods"]);
				$a["old_price"] = $this->diafan->_shop->price_format($a["price"]/$row_good["count_goods"]);
				$values["goods"][] = $a;
			}
		}
	}
	if(! empty($additional_costs[0]))
	{
		foreach($additional_costs[0] as $row_good)
		{
			$row_good["price"] = $this->diafan->_shop->price_format($row_good["price"]);
			$row_good["old_price"] = $this->diafan->_shop->price_format($row_good["price"]);
			$values["goods"][] = $row_good;
		}
	}
	if($values["delivery"])
	{
		$order_summ += $values["delivery"]['price'];
		$values["delivery"]['price'] = $this->diafan->_shop->price_format($values["delivery"]['price']);
		$values["delivery"]['summ'] = $values["delivery"]['price'];
		$order_count++;
	}
	if($order_summ != $row["summ"])
	{
		$values["discount"] = $this->diafan->_shop->price_format($row["summ"] - $order_summ);
	}
}
else
{
	$row_user = DB::query_fetch_array("SELECT * FROM {users} WHERE id=%d", $pay["element_id"]);
	$values["user_fio"] = $row_user["fio"];

	$values["goods"][] = array(
		"id" => $pay["element_id"],
		"article" => '',
		"name" => str_replace(
			'%id',
			$pay["id"],
			$this->diafan->configmodules("desc_payment", "balance")
		),
		"count_goods" => 1,
		"price" => $this->diafan->_shop->price_format($row["summ"]),
		"summ" => $this->diafan->_shop->price_format($row["summ"]),
		"old_price" => $this->diafan->_shop->price_format($row["summ"]),
		"discount" => 0,
	);
	$order_count = 1;
}

if($this->diafan->configmodules('tax', 'shop'))
{
	$values["tax"] = $this->diafan->_shop->price_format($row["summ"] * $this->diafan->configmodules('tax', 'shop') / (100 + $this->diafan->configmodules('tax', 'shop')));
	$values["tax_name"] = $this->diafan->configmodules('tax_name', 'shop');
}
$values['summ'] = $this->diafan->_shop->price_format($row["summ"]);
$values['count'] = $order_count;

$format_price_1 = 0;
if ($this->diafan->configmodules("format_price_1", "shop"))
{
	$format_price_1 = $this->diafan->configmodules("format_price_1", "shop");
}
include_once(ABSOLUTE_PATH.Custom::path("modules/payment/backend/non_cash/payment.non_cash.num2str.php"));
$ns = new Num_to_str;
$values['str_summ'] = $ns->get($row["summ"], $format_price_1);
include_once(ABSOLUTE_PATH.Custom::path('modules/payment/backend/non_cash/payment.non_cash.view.ul.php'));