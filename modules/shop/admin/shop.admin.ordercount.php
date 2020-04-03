<?php
/**
 * Отчет о продажах
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
 * Shop_admin_ordercount
 */
class Shop_admin_ordercount extends Frame_admin
{
	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'created' => array(
			'name' => 'Период',
			'type' => 'date_interval',
			'links' => true,
		),
		'status_id' => array(
			'type' => 'multiselect',
			'name' => 'Искать по статусу',
		),
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if ($this->diafan->is_action("edit"))
		{
			Custom::inc('includes/404.php');
		}

		$select = array();
		$rows = DB::query_fetch_all("SELECT id, [name], status, color FROM {shop_order_status} WHERE trash='0' ORDER BY sort ASC");
		foreach ($rows as $row)
		{
			$this->cache["status"][$row["id"]] = $row["status"];
			$this->cache["status_color"][$row["id"]] = $row["color"];
			$select[$row["id"]] = $row["name"];
			if(! isset($_GET["filter_status_id"]) && $row["status"] == 3)
			{
				$_GET["filter_status_id"][] = $row["id"];
			}
		}
		$this->diafan->variable_filter("status_id", 'select', $select);
	}

	/**
	 * Выводит список заказов
	 * @return void
	 */
	public function show()
	{
		$where   = " WHERE e.trash='0'".$this->diafan->where; //начало условия отбора: не удаленные заказы со статустом "Выполнен"

		$this->diafan->_paginator->page    = $this->diafan->_route->page;
		$this->diafan->_paginator->navlink = $this->diafan->_admin->rewrite.'/';
		$this->diafan->_paginator->get_nav = $this->diafan->get_nav;
		$this->diafan->_paginator->nen     = DB::query_result("SELECT COUNT(*) FROM {shop_order} AS e".$where);
		$result["links"] = $this->diafan->_paginator->get();

		$result["rows"] = array();
		$result["summ"] = DB::query_result("SELECT SUM(e.summ) FROM {shop_order} AS e".$where);
		$result["count"] = $this->diafan->_paginator->nen;
		$result["count_goods"] = DB::query_result("SELECT COUNT(g.id) FROM {shop_order_goods} AS g INNER JOIN {shop_order} AS e ON e.id=g.order_id".$where);
		if($from_coupon = DB::query_result("SELECT COUNT(*) FROM {shop_order} AS e".$where." AND e.coupon<>''"))
		{
			$result["from"]["coupon"] = $from_coupon;
		}
		if($from_log_mail = DB::query_result("SELECT COUNT(e.id) FROM {shop_order} AS e INNER JOIN {shop_cart_log_mail} AS m ON m.order_id=e.id ".$where))
		{
			$result["from"]["log_mail"]["count"] = $from_log_mail;
			$start = 0;
			$finish = time();
			if(! empty($_GET["filter_start_created"]))
			{
				$start = $this->diafan->unixdate($_GET["filter_start_created"]) - 86400 * 2;
			}
			if(! empty($_GET["filter_finish_created"]))
			{
				$finish = $this->diafan->unixdate($_GET["filter_finish_created"]);
			}
			$count_mail = DB::query_result("SELECT COUNT(*) FROM {shop_cart_log_mail} WHERE created>=%d AND created<%d", $start, $finish);
			$result["from"]["log_mail"]["percent"] = $count_mail ? round(($from_log_mail / $count_mail) * 100) : 0;
		}

		//забираем все заказы, удовлетворяющие фильтру
		$rows1 = DB::query_range_fetch_all("SELECT * FROM {shop_order} AS e".$where." ORDER BY created DESC", $this->diafan->_paginator->polog, $this->diafan->_paginator->nastr);
		if($order_ids = $this->diafan->array_column($rows1, "id"))
		{
			$goods = DB::query_fetch_all("SELECT * FROM {shop_order_goods} WHERE order_id IN (%s)", implode(',', $order_ids));
			foreach($goods as $row)
			{
				$shop_order_goods[$row["order_id"]][] = $row;
			}
			if($goods)
			{
				$params = DB::query_fetch_all("SELECT * FROM {shop_order_goods_param} WHERE order_goods_id IN (%s)", implode(',', $this->diafan->array_column($goods, "id")));
				foreach($params as $row)
				{
					$shop_order_param_goods[$row["order_goods_id"]][] = $row;
				}
				if($params)
				{
					$shop_param = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param} WHERE id IN (%s)", implode(',', $this->diafan->array_column($params, "param_id")), "id", "name");
					$shop_param_select = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param_select} WHERE id IN (%s)", implode(',', $this->diafan->array_column($params, "value")), "id", "name");
				}
				$shop = DB::query_fetch_key("SELECT id, [name], article FROM {shop} WHERE id IN (%s)", implode(',', $this->diafan->array_column($goods, "good_id")), "id");
			}
			$log_mail = DB::query_fetch_key(
				"SELECT * FROM {shop_cart_log_mail} WHERE order_id IN (%s)",
				implode(",", $order_ids), "order_id"
			);
			$coupons = array();
			foreach($rows1 as $row1)
			{
				if($row1["coupon"] && ! in_array($row1["coupon"], $coupons))
				{
					$coupons[] = str_replace("'", '', $row1["coupon"]);
				}
			}
			if($coupons)
			{
				$coupon_discount = DB::query_fetch_key("SELECT coupon, discount_id FROM {shop_discount_coupon} WHERE coupon IN ('".implode("','", $coupons)."') AND trash='0'", "coupon");
			}
		}
		if($user_ids = array_unique($this->diafan->array_column($rows1, "user_id")))
		{
			$users = DB::query_fetch_key("SELECT id, name, fio FROM {users} WHERE id IN (%s)", $user_ids, "id");
		}
		$result["orders"] = array();
		
		foreach ($rows1 as $row1)
		{
			//берем каждый заказ и забираем из БД его товары (ограничение 100 товаров на заказ)
			$rows = ! empty($shop_order_goods[$row1["id"]]) ? $shop_order_goods[$row1["id"]] : array();

			$row1["rows"] = array();
			foreach ($rows as $row)
			{
				$params = '';
				$rows_p = ! empty($shop_order_param_goods[$row["id"]]) ? $shop_order_param_goods[$row["id"]] : array();
				 
				foreach ($rows_p as $row_p)
				{
					$params .= ($params ? ', ' : '')
					.(! empty($shop_param[$row_p["param_id"]]) ? $shop_param[$row_p["param_id"]].': ' : '')
					.(! empty($shop_param_select[$row_p["value"]]) ? $shop_param_select[$row_p["value"]] : '');
				}
				$good = (! empty($shop[$row["good_id"]]) ? $shop[$row["good_id"]] : array());

				$row["link"] = BASE_PATH_HREF.'shop/edit'.$row["good_id"].'/';

				$row["name"] = ($good ? $good["name"].($good["article"] ? " ".$good["article"] : '')." ".$params : '');
				
				$row1["rows"][] = $row;
			}

			//выясняем, заказ делал пользователь или нет
			if ($row1["user_id"] && ! empty($users[$row1["user_id"]]))
			{
					$row1["user_link"] = BASE_PATH_HREF.'users/edit'.$row1["user_id"].'/';
					$row1["user"]      = $users[$row1["user_id"]]["fio"].' ('.$users[$row1["user_id"]]["name"].')';
			}
			if(! empty($log_mail[$row1["id"]]))
			{
				$row1["log_mail"] = $log_mail[$row1["id"]];
			}
			if(! empty($coupon_discount[$row1["coupon"]]))
			{
				$row1["coupon_discount"] = $coupon_discount[$row1["coupon"]];
			}
			$result["orders"][] = $row1;
		}

		$this->template($result);
	}

	/**
	 * Шаблон вывода
	 * @return boolean true
	 */
	public function template($result)
	{
		echo '<form action="" method="POST">
		<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
		<input type="hidden" name="action" value="">
		<input type="hidden" name="id" value="">
		<input type="hidden" name="module" value="">';
		$filter = $this->diafan->get_filter();
		if($filter)
		{
			echo '<div class="content__left">
			<div class="action-box"><div class="btn btn_blue btn_small btn_filter">
				<i class="fa fa-filter"></i>
				'.$this->diafan->_('Фильтровать').'
			</div></div>';
		}
		$paginator = '';
		if($result["links"])
		{
			$paginator = '<div class="paginator">'.$this->diafan->_tpl->get('get_admin', 'paginator', $result["links"]);
			$paginator .= '<div class="paginator__unit">
				'.$this->diafan->_('Показывать на странице').':
				<input name="nastr" type="text" value="'.$this->diafan->_paginator->nastr.'">
				<button type="button" class="btn btn_blue btn_small change_nastr">'.$this->diafan->_('ОК').'</button>
			</div>';
			$paginator .= '</div>';
		}
		echo $paginator;

		echo '
		<ul class="list list_stat do_auto_width">
			<li class="item item_heading">
				<div class="item__th">'.$this->diafan->_('Дата').'</div>
				<div class="item__th">'.$this->diafan->_('Товар').'</div>
				<div class="item__th">'.$this->diafan->_('Сумма').' '.$this->diafan->configmodules("currency", "shop").'</div>
				<div class="item__th">'.$this->diafan->_('Заказ №').'</div>
				<div class="item__th">'.$this->diafan->_('Пользователь').'</div>
				<div class="item__th">'.$this->diafan->_('Источник').'</div>
				<div class="item__th item__th_adapt"></div>
				<div class="item__th item__th_seporator"></div>
			</li>';
		foreach ($result["orders"] as $row)
		{
			foreach ($row["rows"] as $i => $good)
			{
				echo '
				<li class="item">
				<div class="item__in">
				<div class="no_important">'.(! $i ? date("d.m.Y H:i", $row["created"]) : '').'</div>
				<div class="user">';
				echo '<p><a href="'.$good["link"].'">'.$good["name"].'</a></p>';
				echo '</div>
				
				<div class="num">';
				echo '<p>'.$this->diafan->_shop->price_format($good["price"]).'</p>';
				echo '</div>
				<div class="num">';
				if(! $i)
				{
					echo '<a href="'.BASE_PATH_HREF.'order/edit'.$row["id"].'/">'.$this->diafan->_('Заказ').' '.$row["id"].'</a>';
				}
				echo '</div>
				<div class="user no_important">';
				if(! $i)
				{
					if (! empty($row["user"]))
					{
						echo '<a href="'.$row["user_link"].'">'.$row["user"].'</a>';
					} else 
					{
						echo $this->diafan->_('Без регистрации');
					}
				}
				echo '</div>
				<div class="user no_important">';
				if(! $i)
				{
					if(! empty($row["log_mail"]))
					{
						echo '<p>'.date("d.m.Y H:i", $row["log_mail"]["created"]).'<br>'.$this->diafan->_('письмо').'<i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Отправлено письмо из интерфейса «Брошенные корзины».').'"></i></p>';
					}
					if(! empty($row["coupon_discount"]))
					{
						echo '<p>'.$this->diafan->_('Купон').': <a href="'.BASE_PATH_HREF.'shop/discount/edit'.$row["coupon_discount"]["discount_id"].'/">'.$row["coupon_discount"]["coupon"].'</a></p>';
					}
					elseif(! empty($row["coupon"]))
					{
						echo '<p>'.$this->diafan->_('Купон').': '.$row["coupon"].'</p>';
					}
				}
	
				echo '</div>
				<div class="item__adapt mobile">
					<i class="fa fa-bars"></i>
					<i class="fa fa-caret-up"></i>
				</div>
				<div class="item__seporator mobile"></div>									
				
				</div></li>';
			}
		}
		
		echo '</ul>';
		echo $paginator;
		
		echo '<div class="orders_bottom">
			<p>'.$this->diafan->_('Всего товаров').': <span>'.$result["count_goods"].'</span></p>
			<p>'.$this->diafan->_('Всего заказов').': <span>'.$result["count"].'</span></p>
			<p>'.$this->diafan->_('На сумму').': <span>'.$this->diafan->_shop->price_format($result["summ"]).' '.$this->diafan->configmodules("currency", "shop").'</span></p>';
			echo '<p>'.$this->diafan->_('Средний чек').': <span>'.($result["count"] ? $this->diafan->_shop->price_format($result["summ"] / $result["count"]) : 0).' '.$this->diafan->configmodules("currency", "shop").'</span></p></div>';
			if(! empty($result["from"]))
			{
				echo '<p>'.$this->diafan->_('Источники').': <span><ul>';
				if(! empty($result["from"]["log_mail"]))
				{
					echo '<li>'.$this->diafan->_('письмо о брошенной корзине').': <b>'.$result["from"]["log_mail"]["count"].'</b>, '.$this->diafan->_('эффективность').': <b>'.$result["from"]["log_mail"]["percent"].'%</b><i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Рассчитывается как процент заказов в выбранном статусе к количеству писем, отправленных за указанный период и два дня ранее.').'"></i></li>';
				}
				if(! empty($result["from"]["coupon"]))
				{
					echo '<li>'.$this->diafan->_('купон').': <b>'.$result["from"]["coupon"].'</b></li>';
				}
				echo '</ul></span></p>';
			}

		if($filter)
		{
			echo '</div>';
		}
		echo '</form>';
		echo $filter;
	}
}