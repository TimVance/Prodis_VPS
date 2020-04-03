<?php
/**
 * Обработка запроса при добавлении товара в корзину
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

class Shop_action extends Action
{
	/**
	 * Добавляет товар в корзину
	 *
	 * @return void
	 */
	public function buy()
	{
		if (empty($_POST['good_id']) || $this->diafan->configmodules('security_user') && ! $this->diafan->_users->id)
		{
			return false;
		}
		if(! $cart_link = $this->diafan->_route->module("cart"))
		{
			return false;
		}

		$count = $this->diafan->filter($_POST, "float", "count", 1);
		$count = $count > 0 ? $count : 1;

		$good_id = $this->diafan->filter($_POST, 'int', 'good_id');
		$this->tag = 'shop'.$good_id;

		$row = DB::query_fetch_array("SELECT id, is_file, [measure_unit] FROM {shop} WHERE id=%d AND trash='0' AND [act]='1' LIMIT 1", $good_id);

		if (empty($row['id']))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}

		$params = array();

		$rows_param = DB::query_fetch_all(
			"SELECT p.[name], p.id FROM {shop_param} AS p"
			." INNER JOIN {shop_param_element} AS e ON e.element_id=%d AND e.param_id=p.id"
			." WHERE p.`type`='multiple' AND p.required='1' AND p.trash='0' GROUP BY p.id",
			$good_id
		);
		foreach ($rows_param as $row_param)
		{
			if (empty($_POST["param".$row_param["id"]]))
			{
				$this->result["errors"][0] = $this->diafan->_('Пожалуйста, выберите %s.', false, $row_param["name"]);
				return;
			}
			$params[$row_param["id"]] = $this->diafan->filter($_POST, "int", "param".$row_param["id"]);
		}

		$additional_costs = DB::query_fetch_value("SELECT a.id FROM {shop_additional_cost} AS a"
		." INNER JOIN {shop_additional_cost_rel} AS r ON a.id=r.additional_cost_id"
		." WHERE r.element_id=%d AND r.trash='0' AND (a.required='1'"
		.(! empty($_POST["additional_cost"]) ? " OR r.additional_cost_id IN (".implode(',', $this->diafan->filter($_POST["additional_cost"], "int")).")" : '').")"
        ." GROUP BY a.id",
		$good_id, "id");
		
		if($cart_good = $this->diafan->_cart->get_good($good_id, $params, $additional_costs))
		{
			// товар-файл добавляем только один раз
			if(! $row["is_file"])
			{
				$count = $cart_good["count"] + $count;
				$cart_good = $this->diafan->_cart->edit_goods(array($cart_good["id"] => $count));
				if(! $cart_good)
				{
					$this->result["errors"][0] = $this->diafan->_('Извините, Вы запросили больше товара, чем имеется на складе.', false);
					return;
				}
			}
		}
		else
		{
			$price = $this->diafan->_shop->price_get($good_id, $params);
			if (! $price)
			{
				$this->result["errors"][0] = $this->diafan->_('Товара с заданными параметрами не существует.');
				return;
			}

			if(! $cart_good = $this->diafan->_cart->add_good($good_id, $price, $params, $additional_costs, $count))
			{
				$this->result["errors"][0] = $this->diafan->_('Извините, Вы запросили больше товара, чем имеется на складе.', false);
				return;
			}
		}

		DB::query("UPDATE {shop} SET counter_buy=counter_buy+1 WHERE id='%d'", $good_id);
		
		if($this->diafan->_site->module == 'cart')
		{
			$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->current_link().'?'.rand(0, 99999);
			return;
		}

		$measure_unit = ! empty($row["measure_unit"]) ? $row["measure_unit"] : $this->diafan->_('шт.');
		$this->result["errors"][0] = $this->diafan->_('В <a href="%s">корзине</a> %s %s', false, BASE_PATH_HREF.$cart_link.'?'.rand(0, 999999), $cart_good["count"], $measure_unit);

		Custom::inc('modules/cart/cart.model.php');
		$model = new Cart_model($this->diafan);
		$cart_tpl = $model->show_block();
		$this->result["data"] = array("#show_cart" => $this->diafan->_tpl->get('info', 'cart', $cart_tpl));
	}

	/**
	 * Проверяет наличие товаров в корзине после пересчета быстрой корзины
	 *
	 * @return void
	 */
	public function check()
	{
		$good_id = $this->diafan->filter($_POST, 'int', 'good_id');
		$this->tag = 'shop'.$good_id;

		$count_good = $this->diafan->_cart->get($good_id, false, false, "count");
		if($count_good)
		{
			if(! $cart_link = $this->diafan->_route->module("cart"))
			{
				return false;
			}
			$measure_unit = DB::query_result("SELECT [measure_unit] FROM {shop} WHERE id=%d", $good_id);
			if(! $measure_unit)
			{
				$measure_unit = $this->diafan->_('шт.');
			}
			$this->result["errors"][0] = $this->diafan->_('В <a href="%s">корзине</a> %s %s', false, BASE_PATH_HREF.$cart_link.'?'.rand(0, 999999), $count_good, $measure_unit);
		}
		else
		{
			$this->result["errors"][0] = '';
		}
	}

	/**
	 * Добавляет товар в список пожеланий
	 *
	 * @return void
	 */
	public function wish()
	{
		if($this->diafan->configmodules('security_user', 'shop'))
		{
			$this->check_user();

			if ($this->result())
					return;
		}

		if (empty($_POST['good_id']))
			return;

		if(! $wish_link = $this->diafan->_route->module("wishlist"))
		{
			return false;
		}

		$good_id = $this->diafan->filter($_POST, 'int', 'good_id');

		$row = DB::query_fetch_array("SELECT id, is_file FROM {shop} WHERE id=%d AND trash='0' AND [act]='1' LIMIT 1", $good_id);

		if (empty($row['id']))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}

		$params = array();

		$rows_param = DB::query_fetch_all(
				"SELECT p.[name], p.id FROM {shop_param} AS p"
				." INNER JOIN {shop_param_element} AS e ON e.element_id=%d AND e.param_id=p.id"
				." WHERE p.`type`='multiple' AND p.required='1' AND p.trash='0' GROUP BY p.id",
				$good_id
			);
		foreach ($rows_param as $row_param)
		{
			if (empty($_POST["param".$row_param["id"]]))
			{
				$this->result["errors"][0] = $this->diafan->_('Пожалуйста, выберите %s.', false, $row_param["name"]);
				return;
			}
			$params[$row_param["id"]] = $this->diafan->filter($_POST, "int", "param".$row_param["id"]);
		}

		$additional_cost_arr = array();
		$additional_cost = '';
		if(! empty($_POST["additional_cost"]))
		{
			$a_cs = array();
			foreach($_POST["additional_cost"] as $a_c)
			{
				$a_c = $this->diafan->filter($a_c, "integer");
				if($a_c)
				{
					$a_cs[] = $a_c;
				}
			}
			if($a_cs)
			{
				$additional_cost_arr = DB::query_fetch_value("SELECT additional_cost_id FROM {shop_additional_cost_rel} WHERE element_id=%d AND trash='0' AND additional_cost_id IN (%s)", $good_id, implode(',', $a_cs), "additional_cost_id");
			}
		}

		$additional_cost_arr_2 = DB::query_fetch_value("SELECT r.additional_cost_id FROM {shop_additional_cost_rel} AS r INNER JOIN {shop_additional_cost} AS a ON a.id=r.additional_cost_id WHERE r.element_id=%d AND r.trash='0' AND a.required='1'", $good_id, "additional_cost_id");
		if($additional_cost_arr_2)
		{
			$additional_cost_arr = array_unique(array_merge($additional_cost_arr, $additional_cost_arr_2));
		}

		if($additional_cost_arr)
		{
			sort($additional_cost_arr);
			$additional_cost = implode(',', $additional_cost_arr);
		}

		$count = $this->diafan->filter($_POST, "float", "count", 1);
		$count = $count > 0 ? $count : 1;

		$count_good = $this->diafan->_wishlist->get($good_id, false, false, "count");
		if($count_good)
		{
			$count_good = 0;
			$params = false;
			$additional_cost = false;
		}
		else
		{
			$count_good += $count;
		}

		$wishlist = array(
				"count" => $count_good,
				"is_file" => $row['is_file'],
			);
		if($err = $this->diafan->_wishlist->set($wishlist, $good_id, $params, $additional_cost))
		{
			$this->result["errors"][0] = $err;
			return;
		}
		$this->diafan->_wishlist->write();

		Custom::inc('modules/wishlist/wishlist.model.php');
		$model = new Wishlist_model($this->diafan);
		$wishlist_tpl = $model->show_block();
		$this->result["data"] = array("#show_wishlist" => $this->diafan->_tpl->get('info', 'wishlist', $wishlist_tpl));

		if($this->diafan->_site->module == 'wishlist')
		{
			$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->current_link().'?'.rand(0, 99999);
		}
	}

	/**
	 * Добавляет товар в список ожиданий
	 *
	 * @return void
	 */
	public function wait()
	{
		if($this->diafan->configmodules('security_user', 'shop'))
		{
			$this->check_user();

			if ($this->result())
					return;
		}

		if (empty($_POST['good_id']))
			return;

		$good_id = $this->diafan->filter($_POST, 'int', 'good_id');

		$row = DB::query_fetch_array("SELECT id FROM {shop} WHERE id=%d AND trash='0' AND [act]='1' LIMIT 1", $good_id);

		if (empty($row['id']))
		{
			$this->result["errors"]["waitlist"] = 'ERROR';
			return;
		}
		if(empty($_POST["mail"]))
		{
			$this->result["errors"]["waitlist"] = $this->diafan->_('Пожалуйста, укажите e-mail.', false);
			return;
		}
		Custom::inc('includes/validate.php');
		$mes = Validate::mail($_POST["mail"]);
		if ($mes)
		{
			$this->result["errors"]["waitlist"] = $this->diafan->_($mes);
			return;
		}

		$params = array();

		$rows_param = DB::query_fetch_all(
				"SELECT p.[name], p.id FROM {shop_param} AS p"
				." INNER JOIN {shop_price} AS pr ON pr.good_id=%d"
				." INNER JOIN {shop_price_param} AS e ON e.price_id=pr.price_id AND e.param_id=p.id AND e.param_value>0"
				." WHERE p.`type`='multiple' AND p.required='1' GROUP BY p.id",
				$good_id
			);
		foreach ($rows_param as $row_param)
		{
			if (empty($_POST["param".$row_param["id"]]))
			{
				$this->result["errors"]["waitlist"] = $this->diafan->_('Пожалуйста, выберите %s.', false, $row_param["name"]);
				return;
			}
			else
			{
				$params[$row_param["id"]] = $this->diafan->filter($_POST, "int", "param".$row_param["id"]);
			}
		}
		asort($params);
		$params = serialize($params);
		if($id = DB::query_result("SELECT id FROM {shop_waitlist} WHERE trash='0' AND good_id=%d AND mail='%h' AND param='%s' LIMIT 1", $good_id, $_POST["mail"], $params))
		{
			DB::query("UPDATE {shop_waitlist} SET created=%d WHERE id=%d", time(), $id);
		}
		else
		{
			DB::query("INSERT INTO {shop_waitlist} (good_id, mail, param, created, user_id, lang_id) VALUES (%d,  '%h', '%s', %d, %d, %d)", $good_id, $_POST["mail"], $params, time(), $this->diafan->_users->id, _LANG);
		}

		$this->result["errors"]["waitlist"] = $this->diafan->_('Спасибо! Мы уведомим Вас когда товар поступит на склад.', false);
	}

	/**
	 * Активирует купон
	 *
	 * @return void
	 */
	public function add_coupon()
	{
		if (empty($_POST["coupon"]))
		{
			$this->result["errors"][0] = $this->diafan->_('Вы ввели ошибочный код купона.', false);
			return;
		}

		$coupon = DB::query_fetch_array("SELECT c.id, c.coupon FROM {shop_discount} AS d"
			." INNER JOIN {shop_discount_coupon} AS c ON c.discount_id=d.id"
			." INNER JOIN {shop_discount_person} AS p ON p.discount_id=d.id AND p.coupon_id=c.id AND p.used='0'"
			." WHERE d.act='1' AND d.trash='0' AND (p.user_id>0 AND p.user_id=%d OR p.session_id='%s')"
			." AND (c.count_use=0 OR c.count_use>c.used) LIMIT 1",
			$this->diafan->_users->id, $this->diafan->_session->id, "id");
		if(! empty($coupon) && $coupon["coupon"] == $_POST["coupon"])
		{
			$this->result["errors"][0] = $this->diafan->_('Вы активировали купон ранее.', false);
			return;
		}
		$discounts = DB::query_fetch_all("SELECT d.id, c.count_use, c.used, d.date_finish, c.id as coupon_id FROM {shop_discount} AS d"
			." INNER JOIN {shop_discount_coupon} AS c ON c.discount_id=d.id"
			." WHERE d.act='1' AND d.trash='0' AND c.coupon='%s'", $_POST["coupon"]);
		if (empty($discounts))
		{
			$this->result["errors"][0] = $this->diafan->_('Вы ввели ошибочный код купона.', false);
			return;
		}
		$errors = array();
		foreach($discounts as $key => $discount)
		{
			if($discount["count_use"] && $discount["count_use"] <= $discount["used"])
			{
				$errors[] = $this->diafan->_('Купон использован кем-то другим.', false);
				unset($discounts[$key]);
				continue;
			}
			if($discount["date_finish"] && $discount["date_finish"] < time())
			{
				$errors[] = $this->diafan->_('Время действия купона истекло.', false);
				unset($discounts[$key]);
				continue;
			}
		}
		if(! empty($errors) && empty($discounts))
		{
			$this->result["errors"][0] = reset($errors);
			return;
		}
		foreach($discounts as $discount)
		{
			$other_user = DB::query_result("SELECT COUNT(*) FROM {shop_discount_person} WHERE discount_id=%d AND used='0'", $discount["id"]);
			DB::query("INSERT INTO {shop_discount_person} (user_id, session_id, discount_id, coupon_id) VALUES (%d, '%s', %d, %d)", $this->diafan->_users->id, $this->diafan->_session->id, $discount["id"], $discount["coupon_id"]);

			if(! $other_user)
			{
				$this->diafan->_shop->price_calc(0, $discount["id"]);
				$this->diafan->_cache->delete('', 'shop');
			}
		}
		$this->result["redirect"] = getenv('HTTP_REFERER');
	}

	/**
	 * Добавляет/удаляет товар для сравнения
	 *
	 * @return void
	 */
	public function compare_goods()
	{
		if (empty($_POST['id']) || empty($_POST["site_id"]))
		{
			return;
		}
		$id = $this->diafan->filter($_POST, "int", "id");
		$site_id = $this->diafan->filter($_POST, "int", "site_id");
		if (empty($_POST['add']))
		{
			if (isset($_SESSION['shop_compare'][$site_id][$id]))
			{
				unset($_SESSION['shop_compare'][$site_id][$id]);
			}
		}
		else
		{
			$_SESSION['shop_compare'][$site_id][$id] = 1;
		}
		$this->result['result'] = 'ok';
	}

	/**
	 * Очищает список сравнения
	 *
	 * @return void
	 */
	public function compare_delete_goods()
	{
		if (isset($_SESSION['shop_compare'][$this->diafan->_site->id]))
		{
			unset($_SESSION['shop_compare'][$this->diafan->_site->id]);
		}
		$this->result['redirect'] = BASE_PATH_HREF.$this->diafan->_route->link($this->diafan->_site->id).'?action=compare';
	}

	/**
	 * Поиск товаров
	 *
	 * @return void
	 */
	public function search()
	{
		$this->model->list_search();
		$this->model->result();
		$this->model->result["ajax"] = true;
		$this->result["data"] = $this->diafan->_tpl->get($this->model->result["view"], 'shop', $this->model->result);
		$this->result["result"] = 'success';
	}
}
