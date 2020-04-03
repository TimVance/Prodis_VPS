<?php
/**
 * Товарный чек
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

header('Content-Type: text/html; charset=utf-8');
$rews = explode('/', $_GET["rewrite"]);
if($rews < 2)
{
	Custom::inc('includes/404.php');
}

if (! $this->diafan->_users->roles('init', 'order'))
{
	Custom::inc('includes/404.php');
}

$element_id = intval($rews[1]);
if (empty($element_id))
{
    Custom::inc('includes/404.php');
}

$row = DB::query_fetch_array(
	"SELECT id, user_id, summ, created, delivery_id, delivery_summ FROM {shop_order} WHERE id=%d AND trash='0' LIMIT 1",
	$element_id);

if (empty($row))
{
    Custom::inc('includes/404.php');
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

$order_created = $row["created"];
$order_summ = $row["summ"];

$values["order_id"] = $row["id"];
$values["date_d"] = date("d", $order_created);
$values["date_m"] = $months_array[date("m", $order_created)];
$values["date_y"] = date("Y", $order_created);

$values["goods"] = array();
$values["discount"] = false;

$values["delivery"] = array();
if(! empty($row["delivery_id"]))
{
	$values["delivery"] = DB::query_fetch_array("SELECT [name] FROM {shop_delivery} WHERE id=%d LIMIT 1", $row["delivery_id"]);
	$values["delivery"]['price'] = $row["delivery_summ"];
}

$user_fio = '';
$params = DB::query_fetch_key_value("SELECT param_id, value FROM {shop_order_param_element} WHERE element_id=%d", $row["id"], "param_id", "value");

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
			$user_fio .= ($user_fio ? ' ' : '').$params[$row_p["id"]];
			break;
	}
	$user[$row_p["info"]] = (! empty($user[$row_p["info"]]) ? ', ' : '').$params[$row_p["id"]];
}

$order_summ = 0;
$order_count = 0;

$rows = DB::query_fetch_all("SELECT a.id, a.[name], a.price, a.amount, s.id AS sid, s.summ, s.order_goods_id FROM {shop_additional_cost} AS a INNER JOIN {shop_order_additional_cost} AS s ON s.additional_cost_id=a.id AND s.order_id=%d WHERE a.trash='0'", $row["id"]); 
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

$rows = DB::query_fetch_all("SELECT * FROM {shop_order_goods} where order_id=%d", $row["id"]);
foreach ($rows as $row_good)
{
	$order_summ += $row_good["price"] * $row_good["count_goods"];
	$order_count += $row_good["count_goods"];
	$depend = '';
	$params = array();
	$rows_p = DB::query_fetch_all("SELECT * FROM {shop_order_goods_param} WHERE order_goods_id=%d", $row_good["id"]);
	foreach ($rows_p as $row_p)
	{
		$params[$row_p["param_id"]] = $row_p["value"];

		if(! $row_p["value"])
			continue;
		$param_name = DB::query_result("SELECT [name] FROM {shop_param} WHERE id=%d LIMIT 1", $row_p["param_id"]);
		$param_value = DB::query_result("SELECT s.[name] FROM {shop_param_select} as s WHERE s.id=%d AND s.param_id=%d LIMIT 1", $row_p["value"], $row_p["param_id"]);
		$depend .= ($depend ? ', ' : ' ').$param_name.': '.$param_value;
	}
	$row_price = $this->diafan->_shop->price_get($row_good["good_id"], $params, false);
	$row_good["old_price"] = $row_price["old_price"] ? $row_price["old_price"] : $row_price["price"];
	$row_good["discount"] = '';
	if($row_good["discount_id"])
	{
		if(empty($discounts[$row_good["discount_id"]]))
		{
			$d = DB::query_fetch_array("SELECT discount, deduction FROM {shop_discount} WHERE id=%d LIMIT 1", $row_good["discount_id"]);
			$discounts[$row_good["discount_id"]] = $d["discount"] ? $d["discount"].'%' : $d["deduction"].' '.$this->diafan->configmodules("currency", "shop");
		}
		$row_good["discount"] = $discounts[$row_good["discount_id"]];
		$values["discount"] = true;
	}
	elseif($row_good["old_price"] && $row_good["old_price"] != $row_good["price"])
	{
		$row_good["discount"] = ceil(100 - $row_good["price"]/$row_good["old_price"] * 100).' %';
		$values["discount"] = true;
	}
	$row_shop = DB::query_fetch_array("SELECT [name], article, [measure_unit] FROM {shop} WHERE id=%d LIMIT 1", $row_good["good_id"]);
	$row_good["name"] = $row_shop["name"].$depend;
	$row_good["article"] = $row_shop["article"];
	$row_good["measure_unit"] = $row_shop["measure_unit"];
	$row_good["summ"] = $this->diafan->_shop->price_format($row_good["price"] * $row_good["count_goods"]);
	if(! $row_good["old_price"])
	{
		$row_good["old_price"] = $row_good["price"];
	}
	$row_good["price"] = $this->diafan->_shop->price_format($row_good["price"]);
	$row_good["old_price"] = $this->diafan->_shop->price_format($row_good["old_price"]);
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
		$row_good["old_price"] = $row_good["price"];
		$values["goods"][] = $row_good;
	}
}

if(! empty($values["delivery"]))
{
	$order_summ += $values["delivery"]['price'];
	$values["delivery"]['price'] = $this->diafan->_shop->price_format($values["delivery"]['price']);
	$order_count++;
}
if($order_summ != $row["summ"])
{
	$values["order_discount"] = $this->diafan->_shop->price_format($order_summ - $row["summ"]);
	$values["discount"] = true;
}

if($this->diafan->configmodules('tax', 'shop'))
{
	$values["tax"] = $this->diafan->_shop->price_format($row["summ"] * $this->diafan->configmodules('tax', 'shop') / (100 + $this->diafan->configmodules('tax', 'shop')));
	$values["tax_name"] = $this->diafan->configmodules('tax_name', 'shop');
}
$values['summ'] = $this->diafan->_shop->price_format($row["summ"]);
$values['count_goods'] = $order_count;
if($this->diafan->_languages->is_ru)
{
	include_once(ABSOLUTE_PATH.Custom::path("modules/payment/backend/non_cash/payment.non_cash.num2str.php"));
	$ns = new Num_to_str;

	$format_price_1 = 0;
	if ($this->diafan->configmodules("format_price_1", "shop"))
	{
		$format_price_1 = $this->diafan->configmodules("format_price_1", "shop");
	}
	$values['str_summ'] = $ns->get($row["summ"], $format_price_1);
}
else
{
	$values['str_summ'] = $this->diafan->_shop->price_format($row["summ"]);
}
include_once(ABSOLUTE_PATH.Custom::path('modules/order/backend/packing_list/order.packing_list.get.view.php'));

