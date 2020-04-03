<?php
/**
 * Модель модуля «Страница пользователя»
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

/**
 * Userpage_model
 */
class Userpage_model extends Model
{
	/**
	 * Генерирует данные для страницы пользователя
	 * 
	 * @return void
	 */
	public function show()
	{
		if(empty($_GET["name"]) && ! $this->diafan->_users->id)
		{
			Custom::inc('includes/404.php');
		}
		if(empty($_GET["name"]))
		{
			$name = $this->diafan->_users->name;
		}
		else
		{
			$name = $_GET["name"];
		}

		$this->result = DB::query_fetch_array("SELECT id, fio, name, created FROM {users} WHERE name='%s' AND act='1' AND trash='0' LIMIT 1", $name);
		if(! $this->result)
		{
			Custom::inc('includes/404.php');
		}
		$this->diafan->_site->query_string = '?name='.urlencode($this->result["name"]);

		$this->result['created'] = $this->format_date($this->result['created'], 'users');

		if ($this->diafan->configmodules("avatar", "users"))
		{
			$this->result["avatar"] = file_exists(ABSOLUTE_PATH.USERFILES.'/avatar/'.$this->result['name'].'.png');
			if(! $this->result["avatar"] && $this->diafan->configmodules("avatar_none", "users"))
			{
				$this->result["avatar_none"] = BASE_PATH.USERFILES.'/avatar_none.png';
			}
			$this->result["avatar_width"] = $this->diafan->configmodules("avatar_width", "users");
			$this->result["avatar_height"] = $this->diafan->configmodules("avatar_height", "users");
		}

		$this->result['param'] = $this->get_params(array("module" => "users", "where" => "show_in_page='1'"));
		$param_types_array = array();
		foreach ($this->result["param"] as &$row)
		{
			if($row["type"] == "attachments")
			{
				$config = unserialize($row["config"]);
				if($config["attachments_access_admin"])
				{
					unset($row);
					continue;
				}
				$row["use_animation"] = ! empty($config["use_animation"]) ? true : false;
			}
			$param_types_array[$row["id"]] = $row;
		}

		$rows = DB::query_fetch_all("SELECT value, param_id FROM {users_param_element} WHERE trash='0' AND element_id=%d", $this->result["id"]);
		foreach ($rows as &$row)
		{
			if(empty($param_types_array[$row["param_id"]]))
				continue;

			switch ($param_types_array[$row["param_id"]]["type"])
			{
				case 'multiple':
				case 'select':
					$user_param[$row["param_id"]][] = $param_types_array[$row["param_id"]]["select_values"][$row["value"]];
					break;
				case 'checkbox':
					$user_param[$row["param_id"]] = @$param_types_array[$row["param_id"]]["select_values"][$row["value"]];
					break;
				case 'date':
					$user_param[$row["param_id"]] = $this->diafan->formate_from_date($row["value"]);
					break;
				case 'datetime':
					$user_param[$row["param_id"]] = $this->diafan->formate_from_datetime($row["value"]);
					break;
				default:
					$user_param[$row["param_id"]] = $row["value"];
			}
		}
		foreach ($this->result['param'] as &$row)
		{
			if($row["type"] == "attachments")
			{
				$row["value"] = $this->diafan->_attachments->get($this->result["id"], "users", $row["id"]);
			}
			elseif($row["type"] == "images")
			{
				$row["value"] = $this->diafan->_images->get('large', $this->result["id"], "users", 'element', 0, '', $row["id"]);
			}
			else
			{
				$row["value"] = ! empty($user_param[$row["id"]]) ? $user_param[$row["id"]] : '';
			}
			if(! $row["value"] && $row["type"] == 'checkbox')
			{
				$row["value"] = @$row["select_values"][0];
			}
		}

		if(in_array('messages', $this->diafan->installed_modules))
		{
			$this->result['form_messages'] = $this->diafan->_users->id && $this->result["id"] != $this->diafan->_users->id && $this->diafan->_route->id_module("messages");
		}

		if($this->diafan->_users->id == $this->result["id"])
		{
			$this->result['orders'] = $this->orders();

			if(in_array('balance', $this->diafan->installed_modules))
			{
				$this->result['balance'] = array(
					'summ'     => $this->diafan->_balance->get(),
					'currency' => $this->diafan->configmodules("currency", "balance"),
					'link'     => $this->diafan->_route->module("balance")
				);
			}
		}

		$this->result["view"] = 'show';
	}

	/**
	 * Формирует список заказов пользователя
	 * @return array
	 */
	public function orders()
	{
		if(! $this->diafan->_users->id)
			return;

		if(! $this->diafan->_route->id_module('cart'))
			return;

		$orders = array();

		$status = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_order_status} ORDER BY sort ASC", "id", "name");

		$rows = DB::query_fetch_all("SELECT id, status, status_id, summ, created FROM {shop_order} WHERE trash='0' AND user_id='%d' ORDER by created DESC", $this->diafan->_users->id);
		
		foreach ($rows as &$row)
		{
			$ids[] = $row["id"];
		}
		

		if(! empty($ids))
		{
			$additional_costs = DB::query_fetch_key_array("SELECT a.id, a.[name], s.summ, s.order_goods_id FROM {shop_additional_cost} AS a
			INNER JOIN {shop_order_additional_cost} AS s ON s.additional_cost_id=a.id AND s.order_id IN (%s)
			WHERE a.trash='0' AND a.shop_rel='1'
			ORDER BY a.sort ASC", implode(',', $ids), "order_goods_id");
			
			$order_goods = DB::query_fetch_key_array("SELECT good_id, price, id, `count_goods`, order_id FROM {shop_order_goods} WHERE trash='0' AND order_id IN (%s)", implode(',', $ids), "order_id");
		}

		$itogo = 0;
		foreach ($rows as &$row)
		{
			$order = array(
				'status'  => $row['status'],
				'status_name'  => (! empty($status[$row['status_id']]) ? $status[$row['status_id']] : ''),
				'summ'    => $this->diafan->_shop->price_format($row["summ"]),
				'created' => $this->format_date($row['created'], 'users'),
				'id'      => $row['id']);
			if(! $row["status"] && in_array('payment', $this->diafan->installed_modules))
			{
				$pay = DB::query_fetch_array("SELECT payment_id, id, code FROM {payment_history} WHERE module_name='cart' AND element_id=%d", $row["id"]);
				if(! isset($cart_rewrite))
				{
					$cart_rewrite = DB::query_result("SELECT r.rewrite FROM {rewrite} AS r INNER JOIN {site} AS s ON s.id=r.element_id AND s.module_name='cart' WHERE r.element_type='element' AND r.module_name='site'");
				}
				$order["link_to_pay"] = $cart_rewrite.'/step2/show'.$row["id"].ROUTE_END.'?code='.$pay["code"].'';
			}

			if ($row["status"] == '3')
			{
				$itogo = $itogo + $row["summ"];
			}

			$good_ids = array();
			$order['goods'] = array();
			$rs  = ! empty($order_goods[$row['id']]) ? $order_goods[$row['id']] : array();
			foreach ($rs as $r)
			{
				$r["additional_cost"] = array();
				if(! empty($additional_costs[$r["id"]]))
				{
					foreach($additional_costs[$r["id"]] as $a)
					{
						$a["format_summ"] = $this->diafan->_shop->price_format($a["summ"]);
						$r["price"] += $a["summ"] / $r["count_goods"];
						$r["additional_cost"][] = $a;
					}
				}
				$r["price"] = $this->diafan->_shop->price_format($r["price"]);
				$rows_p = DB::query_fetch_all("SELECT * FROM {shop_order_goods_param} WHERE order_goods_id=%d", $r["id"]); 
				foreach ($rows_p as $row_p)
				{
					if(! $row_p["value"])
						continue;
					$param_name  = DB::query_result("SELECT [name] FROM {shop_param} WHERE id=%d LIMIT 1", $row_p["param_id"]);
					$param_value = DB::query_result("SELECT [name] FROM {shop_param_select} WHERE id=%d AND param_id=%d LIMIT 1", $row_p["value"], $row_p["param_id"]);
					$r["params"][] = array("name" => $param_name, "value" => $param_value);
				}
				$order['goods'][] = $r;
				$good_ids[] = $r["good_id"];
			}

			if(! empty($good_ids))
			{
				$rs  = DB::query_fetch_all("SELECT id, site_id, [name] FROM {shop} WHERE trash='0' AND id IN (%s)", implode(",", $good_ids));
				foreach ($rs as $r)
				{
					$goods[$r["id"]] = array(
						"link" => $this->diafan->_route->link($r['site_id'], $r['id'], 'shop'),
						"name" => $r["name"],
					);
				}
			}
			foreach ($order['goods'] as &$good)
			{
				$good["name"] = (! empty($goods[$good["good_id"]]) ? $goods[$good["good_id"]]["name"] : '');
				$good["link"] = (! empty($goods[$good["good_id"]]) ? $goods[$good["good_id"]]["link"] : '');
			}

			$orders[] = $order;
		}

		$total = $this->diafan->_shop->price_format($itogo);

		return array('rows' => $orders, 'total' => $total, 'currency' => $this->diafan->configmodules("currency", "shop"));
	}
}
