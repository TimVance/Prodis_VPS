<?php
/**
 * Обрабатывает полученные данные из формы
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

class Cart_action extends Action
{
	/**
	 * Пересчет суммы заказа
	 *
	 * @return void
	 */
	public function recalc()
	{
		$rows = $this->diafan->_cart->db_goods();
		$new_count = array();
		$in_cart = 0;
		foreach ($rows as $row)
		{
			if (! empty($_POST['del'.$row["id"]]))
			{
				$new_count[$row["id"]] = 0;
				continue;
			}
			$in_cart++;
			if(empty($_POST['editshop'.$row["id"]]))
			{
				$new_count[$row["id"]] = 1;
				continue;
			}
			$new_count[$row["id"]] = $this->diafan->filter($_POST, 'float', 'editshop'.$row["id"]);
		}
		$this->diafan->_cart->edit(array(
			"delivery_id" => $this->diafan->filter($_POST, 'int', 'delivery_id'),
			"additional_cost" => (! empty($_POST["additional_cost_ids"]) ? $this->diafan->filter($_POST["additional_cost_ids"], 'int') : ''),
		));
		if($new_count)
		{
			$this->diafan->_cart->edit_goods($new_count);
		}
		if(! $in_cart && $this->diafan->_site->module == 'cart')
		{
			$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->module('cart');
			return;
		}
		$this->result["errors"][0] = $this->diafan->_('Изменения сохранены.', false);

		$block = $this->model->show_block();
		$form = $this->model->form_table();

		$block["error"] = $this->result["errors"][0];

		$this->result["data"] = array(
			"#show_cart" => $this->diafan->_tpl->get('info', 'cart', $block),
			".cart_table" => $this->diafan->_tpl->get('table', 'cart', $form),
		);
		$this->diafan->_site->js_view = array();
		if (! $in_cart)
		{
			$this->result["data"]['.cart_form, .cart_autorization'] = false;
		}
	}

	/**
	 * Очистить корзину
	 *
	 * @return void
	 */
	public function clear()
	{
		$this->diafan->_cart->clear();
		$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->module('cart');
	}

	/**
	 * Оформление заказа
	 *
	 * @return void
	 */
	public function order($cart = false, $is_one_click = false)
	{
		$this->tag = 'cart';
		$where = "show_in_form".($is_one_click ? "_one_click" : '')."='1'";
		$params = $this->model->get_params(array("module" => "shop", "table" => "shop_order", "where" => $where, "fields" => "info"));

		$this->empty_required_field(array("params" => $params));

		if ($this->result())
			return;

		if(! $cart)
		{
			$cart = $this->diafan->_cart->get();
		}
		if(! $cart["rows"])
		{
			$this->result["errors"][0] = $this->diafan->_('Корзина пуста. Обновите страницу.', false);
			return;
		}

		// проверка минимальной / максимальной суммы заказа
		$this->check_order_amount();

		if ($this->result())
			return;

		// доставка
		$delivery_id = 0;
		$delivery_summ = 0;
		$delivery_info = '';
		if($cart["cart_delivery"])
		{
			$delivery = DB::query_fetch_array("SELECT * FROM {shop_delivery} WHERE [act]='1' AND trash='0' AND id=%d LIMIT 1", $cart["cart_delivery"]);
			if($delivery)
			{
				// стоимость доставки рассчитывается сторонним скриптом
				if($delivery["service"])
				{
					$this->diafan->_delivery->get($delivery);
					// стоимость доставки может быть равна 0 при определенной сумме заказа
					// тогда при достижении этой суммы, стоимость доставки не рассчитывается
					$threshold = DB::query_fetch_array("SELECT * FROM {shop_delivery_thresholds} WHERE delivery_id=%d AND price=0 AND amount>0", $cart["cart_delivery"]);
					if($threshold)
					{
						if($threshold["amount"] <= $cart["summ"])
						{
							$delivery['price'] = 0;
						}
					}
					if(is_null($delivery['price']))
					{
						$this->result["errors"][0] = $this->diafan->_('Выбранный способ доставки не доступен в вашем городе.', false);
					}
					elseif($delivery['price'] === false)
					{
						$this->result["errors"][0] = $this->diafan->_('Пожалуйста, задайте дополнительные опции доставки.', false);
					}
	
					if ($this->result())
						return;
	
					$delivery_summ = $delivery['price'];
					if (! empty($delivery["service_info"]))
					{
						$delivery_info = $delivery["service_info"];
					}
				}
				else
				{
					$delivery_summ = DB::query_result("SELECT price FROM {shop_delivery_thresholds} WHERE delivery_id=%d AND amount<=%f ORDER BY amount DESC LIMIT 1", $cart["cart_delivery"], $cart["summ"]);
				}
				$delivery_summ = $this->diafan->_shop->price_format($delivery_summ, true);
				$delivery_id = $delivery["id"];
				$cart["summ"] = $cart["summ"] + $delivery_summ;
			}
		}

		$status = DB::query_fetch_array("SELECT * FROM {shop_order_status} WHERE status='0' LIMIT 1");

		$order_id = DB::query("INSERT INTO {shop_order} (user_id, created, status, status_id, lang_id, discount_id, discount_summ, summ, delivery_summ, delivery_info, delivery_id) VALUES (%d, %d, '0', %d, %d, %d, %f, %f, %f, '%s', %d)",
			$this->diafan->_users->id,
			time(),
			$status["id"],
			_LANG,
			(! empty($cart["discount_total"]["id"]) ? $cart["discount_total"]["id"] : 0),
			(! empty($cart["discount_total"]["discount_summ"]) ? $cart["discount_total"]["discount_summ"] : 0),
			$cart["summ"],
			$delivery_summ, $delivery_info, $delivery_id
		);

		if(! empty($_POST["tmpcode"]))
		{
			DB::query("UPDATE {images} SET element_id=%d, tmpcode='' WHERE module_name='shop_order' AND element_id=0 AND tmpcode='%s'", $order_id, $_POST["tmpcode"]);
		}

		$this->insert_values(array("id" => $order_id, "table" => "shop_order", "params" => $params));

		if ($this->result())
			return;

        // все значения характеристик одним запросом
		$param_select_ids = $this->diafan->filter(array_unique(explode(',', implode(',', $this->diafan->array_column($cart["rows"], "param")))), "int");
		$select_params = DB::query_fetch_key_value("SELECT id, param_id FROM {shop_param_select} WHERE id IN (%s) AND trash='0'", implode(',', $param_select_ids), "id", "param_id");
    
		foreach ($cart["rows"] as $row)
		{
			if($row["count"] <= 0)
				continue;

			$shop_good_id = DB::query("INSERT INTO {shop_order_goods} (order_id, good_id, count_goods, price, discount_id) VALUES (%d, %d, %f, %f, %d)", $order_id, $row["good_id"], $row["count"], $row["price"], $row["discount_id"]);

			foreach ($row["params"] as $value)
			{
				if(! empty($select_params[$value]))
				{
					DB::query("INSERT INTO {shop_order_goods_param} (order_goods_id, value, param_id) VALUES ('%d', '%d', '%d')", $shop_good_id, $value, $select_params[$value]);
				}
			}
			if($row["additional_cost"])
			{
				foreach($row["additional_cost"] as $a_c)
				{
					DB::query("INSERT INTO {shop_order_additional_cost} (order_goods_id, order_id, additional_cost_id, summ) VALUES (%d, %d, %d, %f)", $shop_good_id, $order_id, $a_c["id"], $a_c["summ"]);
				}
			}
		}

		// сопутствующие услуги
		foreach ($cart["additional_cost"] as $a)
		{
			if($a["checked"])
			{
				DB::query("INSERT INTO {shop_order_additional_cost} (order_id, additional_cost_id, summ) VALUES (%d, %d, %f)",
				$order_id, $a["id"], $a["summ"]);
			}
		}

		if($this->diafan->configmodules('order_redirect', 'shop'))
		{
			if(preg_match("/^[0-9]+$/", $this->diafan->configmodules('order_redirect', 'shop')))
			{
				$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->link($this->diafan->configmodules('order_redirect', 'shop'));
			}
			else
			{
				$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->configmodules('order_redirect', 'shop').ROUTE_END;
			}
		}
		if(empty($_POST["payment_id"]))
		{
			if(empty($this->result["redirect"]))
			{
				$this->result["data"] = array(
					'.cart_table_form' => false,
					'form' => str_replace(
						array('%id', '%summ'),
						array($order_id, $cart["summ"]),
						$this->diafan->configmodules('mes', 'shop')
					),
				);
			}
			$payment = false;
		}
		else
		{
			$this->diafan->_payment->add_pay($order_id, 'cart', $_POST["payment_id"], $cart["summ"]);

			$payment = $this->diafan->_payment->get($_POST["payment_id"]);

			if($payment["payment"])
			{
				$this->result["redirect"] = BASE_PATH_HREF.str_replace('ROUTE_END', '', $this->diafan->_route->link($this->diafan->_site->id, 0, "cart", 'element', false)).'/step2/show'.$order_id.ROUTE_END.'?code='.$this->diafan->_payment->code;
			}
			elseif(empty($this->result["redirect"]))
			{
				$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->link($this->diafan->_site->id).'#top';
			}
		}
		$this->send_sms();

		if(! $is_one_click)
		{
			$this->diafan->_cart->clear($order_id);
		}
		elseif(! $this->diafan->_users->id)
		{
			$this->diafan->_cart->edit(array("order_id" => $order_id));
		}
		
		if(! empty($cart["id"]) && $log = DB::query_fetch_array("SELECT * FROM {shop_cart_log_mail} WHERE order_id=0 AND cart_id=%d ORDER BY created DESC LIMIT 1", $cart["id"]))
		{
			DB::query("UPDATE {shop_cart_log_mail} SET order_id=%d WHERE id=%d", $order_id, $log["id"]);
		}

		$this->send_mails($order_id, $params, $payment);

		$order = array(
			"id" => $order_id,
			"count_minus" => false,
			"lang_id" => _LANG,
		);
		$this->diafan->_order->set_status($order, $status);

		// если у пользователя купон на скидку с ограниченным действием, используем один раз купон
		$rows_coupon = DB::query_fetch_all("SELECT c.id, c.count_use, c.used, c.discount_id, c.coupon FROM {shop_discount} AS d"
			." INNER JOIN {shop_discount_coupon} AS c ON c.discount_id=d.id"
			." INNER JOIN {shop_discount_person} AS p ON p.discount_id=d.id AND p.coupon_id=c.id AND p.used='0'"
			." WHERE d.act='1' AND d.trash='0' AND (c.count_use=0 OR c.count_use>c.used) AND (p.user_id>0 AND p.user_id=%d OR p.session_id='%s')"
			." GROUP BY c.id",
			$this->diafan->_users->id, $this->diafan->_session->id);
		$coupons = array();
		foreach ($rows_coupon as $coupon)
		{
			DB::query("UPDATE {shop_discount_coupon} SET used=used+1 WHERE id=%d", $coupon["id"]);
			DB::query("UPDATE {shop_discount_person} SET used='1' WHERE discount_id=%d AND coupon_id=%d AND used='0' AND (user_id>0 AND user_id=%d OR session_id='%s')", $coupon["discount_id"], $coupon["id"], $this->diafan->_users->id, $this->diafan->_session->id);
			if(! DB::query_result("SELECT COUNT(*) FROM {shop_discount_coupon} WHERE discount_id=%d AND (count_use=0 OR count_use>used)", $coupon["discount_id"]))
			{
				DB::query("UPDATE {shop_discount} SET act='0' WHERE id=%d", $coupon["discount_id"]);
				$this->diafan->_shop->price_calc(0, $coupon["discount_id"]);
			}
			$coupons[] = $coupon["coupon"];
		}
		if($coupons)
		{
			DB::query("UPDATE {shop_order} SET coupon='%s' WHERE id=%d", implode(',', $coupons), $order_id);
		}
		$this->result["result"] = "success";
	}

	/**
	 * Проверка минимальной/максимальной суммы заказа
	 *
	 * @return boolean
	 */
	private function check_order_amount()
	{
		if(! $this->diafan->configmodules('order_min_summ', 'shop') && ! $this->diafan->configmodules('order_max_summ', 'shop'))
		{
			return true;
		}
		$cart = $this->diafan->_cart->get();
		
		$order_summ = $cart["summ_goods"];
		if(! empty($cart["discount_total"]["discount_summ"]) && ! $this->diafan->configmodules('order_summ_discount', 'shop'))
		{
			$order_summ += $cart["discount_total"]["discount_summ"];
		}
		if(! empty($cart["summ_additional_cost"]) && ! $this->diafan->configmodules('order_summ_additional_cost', 'shop'))
		{
			$order_summ -= $cart["summ_additional_cost"];
		}
		if($order_min_summ = $this->diafan->configmodules('order_min_summ', 'shop'))
		{
			$order_min_summ = (float) preg_replace("/[^0-9\.+]/", "", str_replace(',', '.', $order_min_summ));
		}
		else $order_min_summ = 0;
		if($order_max_summ = $this->diafan->configmodules('order_max_summ', 'shop'))
		{
			$order_max_summ = (float) preg_replace("/[^0-9\.+]/", "", str_replace(',', '.', $order_max_summ));
		}
		else $order_max_summ = 0;
		if($order_min_summ > 0 && $order_summ < $order_min_summ)
		{
			$this->result["errors"][0] = $this->diafan->_('Сумма заказа %s не должна быть меньше %s.', false, $this->diafan->_shop->price_format($order_summ).' '.$this->diafan->configmodules('currency', 'shop'), $this->diafan->_shop->price_format($order_min_summ).' '.$this->diafan->configmodules('currency', 'shop'));
		}
		elseif($order_max_summ > 0 && $order_summ > $order_max_summ)
		{
			$this->result["errors"][0] = $this->diafan->_('Сумма заказа %s не должна быть больше %s.', false, $this->diafan->_shop->price_format($order_summ).' '.$this->diafan->configmodules('currency', 'shop'), $this->diafan->_shop->price_format($order_max_summ).' '.$this->diafan->configmodules('currency', 'shop'));
		}

		if(! empty($this->result["errors"]))
		{
			return false;
		}
		return true;
	}

	/**
	 * Загружает изображение
	 *
	 * @return void
	 */
	public function upload_image()
	{
		if(empty($_POST["tmpcode"]))
		{
			return;
		}
		if(empty($_POST["images_param_id"]))
		{
			return;
		}
		$param_id = $this->diafan->filter($_POST, "int", "images_param_id");

		$this->result["result"] = 'success';
		if (! empty($_FILES['images'.$param_id]) && $_FILES['images'.$param_id]['tmp_name'] != '' && $_FILES['images'.$param_id]['name'] != '')
		{
			try
			{
				$this->diafan->_images->upload(0, "shop_order", 'element', 0, $_FILES['images'.$param_id]['tmp_name'], $this->diafan->translit($_FILES['images'.$param_id]['name']), false, $param_id, $_POST["tmpcode"]);
			}
			catch(Exception $e)
			{
				Dev::$exception_field = 'p'.$param_id;
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}
			$images = $this->diafan->_images->get('large', 0, "shop_order", 'element', 0, '', $param_id, 0, '', $_POST["tmpcode"]);
			$this->result["data"] = $this->diafan->_tpl->get('images', "cart", $images);
		}
	}

	/**
	 * Удаляет изображение
	 *
	 * @return void
	 */
	public function delete_image()
	{
		if(empty($_POST["id"]))
		{
			return;
		}
		if(empty($_POST["tmpcode"]))
		{
			return;
		}
		$row = DB::query_fetch_array("SELECT * FROM {images} WHERE module_name='shop_order' AND id=%d AND tmpcode='%s'", $_POST["id"], $_POST["tmpcode"]);
		if(! $row)
		{
			return;
		}
		$this->diafan->_images->delete_row($row);
		$this->result["result"] = 'success';
	}

	/**
	 * Оформление быстрого заказа
	 *
	 * @return void
	 */
	public function one_click()
	{
		$cart = array();
		if(! empty($_POST["good_id"]))
		{
			$good_id = $this->diafan->filter($_POST, 'int', 'good_id');
			$this->tag = 'shop'.$good_id;

			$row = DB::query_fetch_array("SELECT id FROM {shop} WHERE id=%d AND trash='0' AND [act]='1' LIMIT 1", $good_id);

			if (empty($row['id']))
			{
				$this->result["errors"][0] = 'ERROR';
				return;
			}

			$params = array();

			$rows_param = DB::query_fetch_all(
					"SELECT p.[name], p.id FROM {shop_param} AS p"
					." INNER JOIN {shop_param_element} AS e ON e.element_id=%d AND e.param_id=p.id"
					." WHERE p.`type`='multiple' AND p.required='1' GROUP BY p.id",
					$good_id
				);
			foreach ($rows_param  as $row_param)
			{
				if (empty($_POST["param".$row_param["id"]]))
				{
					$this->result["errors"][0] = $this->diafan->_('Пожалуйста, выберите %s.', false, $row_param["name"]);
					return;
				}
				$params[$row_param["id"]] = $this->diafan->filter($_POST, "int", "param".$row_param["id"]);
			}
			$price = $this->diafan->_shop->price_get($good_id, $params);
			if (! $price)
			{
				//$this->result["errors"][0] = $this->diafan->_('Товара с заданными параметрами не существует.');
				//return;
			}
			$cart_summ = $price["price"];

			$count = $this->diafan->filter($_POST, "int", "count", 1);
			$count = $count > 0 ? $count : 1;

			$additional_cost_arr = DB::query_fetch_all("SELECT a.id, a.[name], a.percent, a.price, a.amount, r.element_id, r.summ FROM {shop_additional_cost} AS a"
			." INNER JOIN {shop_additional_cost_rel} AS r ON a.id=r.additional_cost_id"
			." WHERE r.element_id=%d AND r.trash='0' AND (a.required='1'"
			.(! empty($_POST["additional_cost"]) ? " OR r.additional_cost_id IN (".implode(',', $this->diafan->filter($_POST["additional_cost"], "int")).")" : '').")"
			." GROUP BY a.id", $good_id);
			foreach($additional_cost_arr as &$a_c_rel)
			{
				$a_c_rel["price"] = $this->diafan->_shop->price_format($a_c_rel["price"], true);
				if($a_c_rel["amount"] && $a_c_rel["amount"] <= $price["price"])
				{
					$a_c_rel["summ"] = 0;
				}
				elseif($a_c_rel["percent"])
				{
					$a_c_rel["summ"] = ($price["price"] * $a_c_rel["percent"]) / 100;
				}
				elseif(! $a_c_rel["summ"])
				{
					$a_c_rel["summ"] = $a_c_rel["price"];
				}
				if($a_c_rel["summ"])
				{
					$a_c_rel["summ"] = $this->diafan->_shop->price_format($a_c_rel["summ"], true);
					$cart_summ += $a_c_rel["summ"];
				}
			}

			$cart["rows"][] = array(
				"count" => $count,
				"good_id" => $good_id,
				"params" => $params,
				"param" => implode(',', $params),
				"additional_cost" => $additional_cost_arr,
				"price" => $price["price"],
				"discount_id" => $price["discount_id"],
			);

			DB::query("UPDATE {shop} SET counter_buy=counter_buy+1 WHERE id=%d", $good_id);
			$cart["summ"] = $cart_summ;
			$cart["count_goods"] = 1;
			$cart["additional_cost"] = array();
			$cart["cart_delivery"] = false;
			$cart["discount_id"] = 0;
		}
		return $this->order($cart, true);
	}

	/**
	 * Отправляет письма администратору сайта и пользователю, сделавшему заказ
	 *
	 * @param integer $order_id номер заказа
	 * @param array $params поля формы, заполняемой пользователем
	 * @param array $payment платежная система
	 * @return void
	 */
	private function send_mails($order_id, $params, $payment)
	{
		$cart = $this->diafan->_tpl->get('table_mail', 'cart', $this->diafan->_order->get($order_id));
		$payment_name = '';
		if($payment)
		{
			$payment_name = $payment["name"];
			if($payment["payment"] == 'non_cash')
			{
				$p = DB::query_fetch_array("SELECT code, id FROM {payment_history} WHERE module_name='cart' AND element_id=%d", $order_id);
				$payment_name .= ', <a href="'.BASE_PATH.'payment/get/non_cash/ul/'.$p["id"].'/'.$p["code"].'/">'.$this->diafan->_('Счет для юридических лиц', false).'</a>,
				<a href="'.BASE_PATH.'payment/get/non_cash/fl/'.$p["id"].'/'.$p["code"].'/">'.$this->diafan->_('Квитанция для физических лиц', false).'</a>';
			}
		}

		$user_email = $this->diafan->_users->mail;
		$user_phone = $this->diafan->_users->phone;
		$user_fio = $this->diafan->_users->fio;
		foreach ($params as $param)
		{
			if ($param["type"] == "email" && ! empty($_POST["p".$param["id"]]))
			{
				$user_email = $_POST["p".$param["id"]];
			}
			if ($param["info"] == "phone" && ! empty($_POST["p".$param["id"]]))
			{
				$user_phone = $_POST["p".$param["id"]];
			}
			if ($param["info"] == "name" && ! empty($_POST["p".$param["id"]]))
			{
				$user_fio = $_POST["p".$param["id"]];
			}
		}

		//send mail admin
		$subject = str_replace(array('%title', '%url', '%id', '%message'),
				   array(TITLE, BASE_URL, $order_id, $this->message_admin_param),
				   $this->diafan->configmodules('subject_admin', 'shop')
				  );

		$message = str_replace(
			array('%title',
				'%url',
				'%id',
				'%message',
				'%order',
				'%payment',
				'%fio'
			),
			array(
				TITLE,
				BASE_URL,
				$order_id,
				$this->message_admin_param,
				$cart,
				$payment_name,
				$user_fio
			),
			$this->diafan->configmodules('message_admin', 'shop'));

		$this->diafan->_postman->message_add_mail(
				$this->diafan->configmodules("emailconfadmin", 'shop') ? $this->diafan->configmodules("email_admin", 'shop') : EMAIL_CONFIG,
				$subject,
				$message,
				$this->diafan->configmodules("emailconf", 'shop') ? $this->diafan->configmodules("email", 'shop') : EMAIL_CONFIG
			);

		if(in_array("subscription", $this->diafan->installed_modules))
		{
			if(! empty($user_phone))
			{
				$user_phone = preg_replace('/[^0-9]+/', '', $user_phone);
				if(! DB::query_result("SELECT id FROM {subscription_phones} WHERE phone='%s' AND trash='0'", $user_phone))
				{
					DB::query("INSERT INTO {subscription_phones} (phone, name, created, act) VALUES ('%s', '%h', %d, '1')", $user_phone, $user_fio, time());
				}
			}
		}

		//send mail user
		if (empty($user_email))
		{
			return;
		}

		if(in_array("subscription", $this->diafan->installed_modules) && (! empty($_POST['subscribe_in_order']) || ! $this->diafan->configmodules('subscribe_in_order', 'subscription')))
		{
			$row_subscription = DB::query_fetch_array("SELECT * FROM {subscription_emails} WHERE mail='%s' AND trash='0' LIMIT 1", $user_email);

			if(empty($row_subscription))
			{
				$code = md5(rand(111, 99999));
				DB::query("INSERT INTO {subscription_emails} (created, mail, name, code, act) VALUES (%d, '%s', '%s', '%s', '1')", time(), $user_email, $user_fio, $code);
			}
			elseif(! $row_subscription["act"])
			{
				DB::query("UPDATE {subscription_emails} SET act='1', created=%d WHERE id=%d", $row_subscription['id'], time());
			}
		}

		$subject = str_replace(
				array('%title', '%url', '%id'),
				array(TITLE, BASE_URL, $order_id),
				$this->diafan->configmodules('subject', 'shop')
			);

		$message = str_replace(
				array('%title', '%url', '%id', '%message', '%order', '%payment', '%fio'),
				array(
					TITLE,
					BASE_URL,
					$order_id,
					$this->message_param,
					$cart,
					$payment_name,
					$user_fio
				),
				$this->diafan->configmodules('message', 'shop')
			);
		$this->diafan->_postman->message_add_mail(
			$user_email,
			$subject,
			$message,
			$this->diafan->configmodules("emailconf", 'shop') ? $this->diafan->configmodules("email", 'shop') : EMAIL_CONFIG
		);
	}

	/**
	 * Отправляет администратору SMS-уведомление
	 *
	 * @return void
	 */
	private function send_sms()
	{
		if (! $this->diafan->configmodules("sendsmsadmin", 'shop'))
			return;

		$message = $this->diafan->configmodules("sms_message_admin", 'shop');

		$to   = $this->diafan->configmodules("sms_admin", 'shop');

		$this->diafan->_postman->message_add_sms($message, $to);
	}
}
