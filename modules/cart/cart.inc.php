<?php
/**
 * Подключение модуля «Корзина товаров»
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
 */
if ( ! defined('DIAFAN'))
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
 * Cart_inc
 */
class Cart_inc extends Diafan
{
	/*
	 * @var array информация, записанная в корзину
	 */
	private $cart = array();

	/**
	 * Возвращает товары в корзине
	 * 
	 * @return array
	 */
	public function get()
	{
        if(! isset($this->cache["cart"]))
        {
            $this->prepare_get_data();
            $db_cart = $this->db_cart();
            if(! $db_cart)
            {
                return;
            }
            $cart["id"] = $db_cart["id"];
            $cart["rows"] = $this->db_goods();
            $cart["summ_goods"] = 0;
            $cart["old_summ_goods"] = 0;
            $cart["summ"] = 0;
            foreach ($cart["rows"] as &$row)
            {
                $row["good"] = $this->cache["get"]["goods"][$row["good_id"]];
                
                // цена
                $row["price"] = 0;
                if($row_price = $this->diafan->_shop->price_get_id($row["price_id"]))
                {
                    $row_price["price"] = $this->diafan->_shop->price_format($row_price["price"], true);
                    $row_price["old_price"] = $this->diafan->_shop->price_format($row_price["old_price"], true);
                    $row["price_count"] = $this->diafan->configmodules('use_count_goods', 'shop') ? $row_price["count_goods"] : 0;
                    $row["price"] = $row_price["price"];
                    $row["old_price"] = $row_price["old_price"];
                }
                if($row["good"]["is_file"])
                {
                    $row["price_count"] = 1;
                }
                
                // сопутствующие услуги для товара
                $row["additional_cost"] = array();
                foreach($row["additional_costs"] as $a)
                {
                    if(empty($this->cache["get"]["additional_cost_rels"][$a.'_'.$row["good_id"]]))
                        continue;
    
                    $a_c_rel = $this->cache["get"]["additional_cost_rels"][$a.'_'.$row["good_id"]];
                    $a_c_rel["price"] = $this->diafan->_shop->price_format($a_c_rel["price"], true);
                    if($a_c_rel["amount"] && $a_c_rel["amount"] <= $row["price"])
                    {
                        $a_c_rel["summ"] = 0;
                    }
                    elseif($a_c_rel["percent"])
                    {
                        $a_c_rel["summ"] = ($row["price"] * $a_c_rel["percent"]) / 100;
                    }
                    elseif(! $a_c_rel["summ"])
                    {
                        $a_c_rel["summ"] = $a_c_rel["price"];
                    }
                    if($a_c_rel["summ"])
                    {
                        $a_c_rel["summ"] = $this->diafan->_shop->price_format($a_c_rel["summ"], true);
                        $row["price"] += $a_c_rel["summ"];
                    }
                    $row["additional_cost"][] = $a_c_rel;
                }
    
                // скидка на товар
                $row["discount_id"] = $row_price["discount_id"];
                $row["discount_summ"] = 0;
                $row["percent"] = 0;
                if($row_price["discount_id"] && ! empty($this->cache["get"]["discounts"][$row_price["discount_id"]]))
                {
                    $discount = $this->cache["get"]["discounts"][$row_price["discount_id"]];
                    if(! empty($discount["deduction"]))
                    {
                        $row["percent"] = 0;
                        $row["discount_summ"] = $discount["deduction"];
                    }
                    else
                    {
                        $row["percent"] = $discount["discount"];
                        $row["discount_summ"] = $row_price["price"] / (100 - $discount["discount"]) * $discount["discount"];
                    }
                }
                elseif($row_price["old_price"] && $row_price["old_price"] > $row_price["price"])
                {
                    $row["discount_summ"] = $row_price["old_price"] - $row_price["price"];
                }
    
                $cart["summ_goods"] += $row["price"] * $row["count"];
            }
            $cart["count"] = count($cart["rows"]);
            
            if($cart["rows"])
            {
                //скидка на общую сумму заказа
                foreach ($this->cache["get"]["discounts_all"] as $ds)
                {
                    if($ds['deduction'])
                    {
                        $ds["discount_summ"] = $ds["deduction"];
                        $ds["percent"] = 0;
                    }
                    else
                    {
                        $ds["discount_summ"] = $cart["summ_goods"] * $ds["discount"] / 100;
                        $ds["percent"] = $ds["discount"];
                    }
                    // определяем текущую скидку
                    if($ds["threshold"] && $ds["threshold"] <= $cart["summ_goods"]
                       || $ds["threshold_cumulative"] && $ds["threshold_cumulative"] <= $this->cache["get"]["order_summ"])
                    {
                        if($ds["discount_summ"] < $cart["summ_goods"] && (empty($cart["discount_total"]) || $cart["discount_total"]["discount_summ"] < $ds["discount_summ"]))
                        {
                            $cart["discount_total"] = $ds;
                            $cart["discount"] = true;
                        }
                    }
                    // определяем следующую скидку, доступную при добавлении к заказу товаров
                    elseif($ds["threshold"] && (empty($cart["discount_next"]) || $cart["discount_next"]["discount_summ"] <= $ds["discount_summ"] && $cart["discount_next"]["threshold"] <= $ds["threshold"])
                    && (empty($cart["discount_total"]) || $cart["discount_total"]["discount_summ"] <= $ds["discount_summ"] && $cart["discount_total"]["threshold"] <= $ds["threshold"]))
                    {
                        $ds["summ"] = $ds["threshold"] - $cart["summ_goods"];
                        $cart["discount_next"] = $ds;
                    }
                }
                // распределяем общую скидку на товары
                if(! empty($cart["discount_total"]))
                {
                    $cart["old_summ_goods"] = $cart["summ_goods"];

                    $discount_total = 0;
        
                    foreach($cart["rows"] as $i => &$row)
                    {
                        if($i + 1 == count($cart["rows"]))
                        {
                            $discount_total_price = $this->diafan->_shop->price_format(($cart["discount_total"]["discount_summ"] - $discount_total) / $row["count"]);
                        }
                        else
                        {
                            $discount_total_price = $this->diafan->_shop->price_format($row["price"] * $cart["discount_total"]["discount_summ"] / $cart["old_summ_goods"], true);
                        }
                        
                        $row["old_price"] = (! empty($row["old_price"]) ? $row["old_price"] : $row["price"]);
                        $row["price"] = $row["price"] - $discount_total_price;
                        $row["summ"] = $row["price"] * $row["count"];
                        $row["percent"] = 0;
                        $row["discount_summ"] += $discount_total_price;
                        $discount_total += $discount_total_price * $row["count"];
                        if(! $row["discount_id"])
                        {
                            $row["discount_id"] = $cart["discount_total"]["id"];
                        }
                    }
                    $cart["discount_total"]["discount_summ"] = $discount_total;
                    $cart["summ_goods"] = $cart["summ_goods"] - $cart["discount_total"]["discount_summ"];
                }
                $cart["summ"] = $cart["summ_goods"];
        
                // сопутствующие услуги
                $cart["summ_additional_cost"] = 0;
                $cart["additional_cost"] = $this->cache["get"]["additional_costs"];
                foreach ($cart["additional_cost"] as &$a)
                {
                    $a['price'] = $this->diafan->_shop->price_format($a['price'], true);
                    $a["summ"] = $a['price'];
                    if($a['percent'])
                    {
                        $a["summ"] = $cart["summ_goods"] * $a['percent'] / 100;
                    }
                    if (! empty($a['amount']))
                    {
                        if ($a['amount'] <= $cart["summ_goods"])
                        {
                            $a["summ"] = 0;
                        }
                    }
                    if($a["checked"] = (in_array($a["id"], $db_cart["additional_costs"]) || $a["required"]))
                    {
                        $cart["summ_additional_cost"] += $a['summ'];
                    }
                }
                $cart["summ"] += $cart["summ_additional_cost"];
                $cart["cart_additional_cost"] = $db_cart["additional_costs"];
                $cart["cart_delivery"] = $db_cart["delivery_id"];
            }
            if($cart["summ"] != $db_cart["summ"] || $cart["count"] != $db_cart["count_goods"])
            {
                DB::query("UPDATE {shop_cart} SET summ=%f, count_goods=%f WHERE id=%d", $cart["summ"], $cart["count"], $db_cart["id"]);
                $this->cache["db_cart"]["summ"] = $cart["summ"];
                $this->cache["db_cart"]["count_goods"] = $cart["count"];
            }
            $this->cache["cart"] = $cart;
        }
        return $this->cache["cart"];
	}

	/**
	 * Подготоваливает данные из базы данных для формирования списка товаров в корзине
	 *
	 * @return void
	 */
	private function prepare_get_data()
	{
        $cart = $this->db_cart();
        $rows = $this->db_goods();

        if(! $rows)
            return;

		// все товары одним запросом
		$good_ids = array_unique($this->diafan->array_column($rows, "good_id"));
		$this->cache["get"]["goods"] = DB::query_fetch_key("SELECT * FROM {shop} WHERE [act]='1' AND id IN (%s) AND trash='0'", implode(",", $good_ids), "id");
		
		// все связи цен и изображений одним запросом
		$price_ids = array_unique($this->diafan->array_column($rows, "price_id"));
		
		// все сопутствующие услуги товаров одним запросом
		$this->cache["get"]["additional_cost_rels"] = DB::query_fetch_key("SELECT a.id, a.[name], a.percent, a.price, a.amount, r.element_id, r.summ, CONCAT(a.id, '_', r.element_id) AS `key` FROM {shop_additional_cost} AS a INNER JOIN {shop_additional_cost_rel} AS r ON r.additional_cost_id=a.id WHERE r.element_id IN (%s) AND a.trash='0'", implode(',', $good_ids), "key");
		
		// все сопутствующие услуги для заказа одним запросом
		$this->cache["get"]["additional_costs"] = DB::query_fetch_all("SELECT id, [name], price, percent, [text], amount, required FROM {shop_additional_cost} WHERE [act]='1' AND trash='0' AND shop_rel='0' ORDER by sort ASC");

		// подготовка цен, характеристик и услуг
		foreach ($rows as $row)
		{
			$this->diafan->_shop->price_prepare_id($row["price_id"]);
		}
		
		$discount_ids = array();

		$uniq = array();
        // актуализация товаров в корзине
		foreach ($rows as $i => $row)
		{
            // удаление дублей, могут возникнуть в результате слияния корзин
            $uniq_id = $row["good_id"].'_'.$row["price_id"].'_'.$row["param"].'_'.$row["additional_cost"];
            if(in_array($uniq_id, $uniq))
            {
                DB::query("DELETE FROM {shop_cart_goods} WHERE id=%d", $row["id"]);
                $this->set_cache_good(false, $i);
				continue;
            }
            $uniq[] = $uniq_id;

			if (empty($this->cache["get"]["goods"][$row["good_id"]]))
			{
				// удаление из корзины товара, которого нет на сайте
                DB::query("DELETE FROM {shop_cart_goods} WHERE id=%d", $row["id"]);
                $this->set_cache_good(false, $i);
				continue;
			}
            $row["good"] = $this->cache["get"]["goods"][$row["good_id"]];

			$row["price"] = $this->diafan->_shop->price_get_id($row["price_id"]);
			if (empty($row["price"]))
			{
                $wparam = array();
                if($row["param"])
                {
                    $wparam = DB::query_fetch_key_value("SELECT id, param_id FROM {shop_param_select} WHERE id IN(%s)", $row["param"], "param_id", "id");
                }
                if($row["price"] = $this->diafan->_shop->price_get($row["good_id"], $wparam))
                {
                    $row["price_id"] = $row["price"]["price_id"];
                    DB::query("UPDATE {shop_cart_goods} SET price_id=%d WHERE id=%d", $row["price_id"], $row["id"]);
                    $this->set_cache_good(array("price_id" => $row["price_id"]), $i);
                }
                else
                {
                    // удаление из корзины товара, у которого нет цены
                    DB::query("DELETE FROM {shop_cart_goods} WHERE id=%d", $row["id"]);
                    $this->set_cache_good(false, $i);
                    continue;
                }
			}
            if($row["good"]["is_file"] && $row["count"] > 1)
            {
                $row["count"] = 1;
                $this->set_cache_good(array("count" => 1), $i);
                DB::query("UPDATE {shop_cart_goods} SET created=%d, `count`=%f WHERE id=%d", time(), 1, $row["id"]);
            }
			if($row["price"]["discount_id"] && ! in_array($row["price"]["discount_id"], $discount_ids))
			{
				$discount_ids[] = $row["price"]["discount_id"];
			}
			if($this->diafan->configmodules("use_count_goods", "shop"))
			{
                $count_price_id = 0;
				foreach ($this->cache["db_goods"] as $r)
                {
                    if($r["price_id"] == $row["price_id"] && $r["id"] != $row["id"])
                    {
                        $count_price_id += $r["count"];
                    }
                }
				if($row["price"]["count_goods"] < $row["count"] + $count_price_id)
				{
                    // если товара на скадале недостаточно, оставляем в корзине столько сколько есть на складе
                    if($row["price"]["count_goods"] > $count_price_id)
                    {
                        $row["count"] = $row["price"]["count_goods"] - $count_price_id;
                        $this->set_cache_good(array("count" => $row["count"]), $i);
                        DB::query("UPDATE {shop_cart_goods} SET created=%d, `count`=%f WHERE id=%d", time(), $row["count"], $row["id"]);
                    }
                    // если и этого не хватает, переносим в вишлист
                    else
                    {
                        $wparam = array();
                        if($row["param"])
                        {
                            $wparam = DB::query_fetch_key_value("SELECT id, param_id FROM {shop_param_select} WHERE id IN(%s)", $row["param"], "param_id", "id");
                        }
                        $this->diafan->_wishlist->set(1, $row["good_id"], $wparam, (is_array($row["additional_cost"]) ? implode(',', $row["additional_cost"]) : $row["additional_cost"]), "count");
                        $this->diafan->_wishlist->write();
                        DB::query("DELETE FROM {shop_cart_goods} WHERE id=%d", $row["id"]);
                    }
				}
			}
		}

		// запрашиваем все скидки на товары
		if($discount_ids)
		{
			$this->cache["get"]["discounts"] = DB::query_fetch_key("SELECT id, discount, deduction FROM {shop_discount} WHERE id IN (%s)", implode(",", $discount_ids), "id");
		}

		//скидка на общую сумму заказа
		$person_discount_ids = $this->diafan->_shop->price_get_person_discounts();
		$this->cache["get"]["discounts_all"] = DB::query_fetch_all("SELECT id, discount, amount, deduction, threshold, threshold_cumulative FROM"
			." {shop_discount} WHERE act='1' AND trash='0' AND (threshold_cumulative>0 OR threshold>0)"
			." AND role_id".($this->diafan->_users->role_id ? ' IN (0, '.$this->diafan->_users->role_id.')' : '=0')
			." AND (person='0'".($person_discount_ids ? " OR id IN(".implode(",", $person_discount_ids).")" : "").")"
			." AND date_start<=%d AND (date_finish=0 OR date_finish>=%d) ORDER BY threshold_cumulative ASC, threshold ASC", time(), time()
		);
		
		// общая сумма заказов
		$this->cache["get"]["order_summ"] = 0;
		if($this->diafan->_users->id)
		{
			$this->cache["get"]["order_summ"] = DB::query_result("SELECT SUM(summ) FROM {shop_order} WHERE user_id=%d AND (status='1' OR status='3') AND trash='0'", $this->diafan->_users->id);
		}
        elseif($cart["orders"])
        {
			$this->cache["get"]["order_summ"] = DB::query_result("SELECT SUM(summ) FROM {shop_order} WHERE user_id=0 AND (status='1' OR status='3') AND id IN (%s) AND trash='0'", implode(',', $this->diafan->filter(explode(',', $cart["orders"]), "int")));
        }
	}

	/**
	 * Возвращает товар в корзине
	 *
	 * @param integer $good_id идентификатор товара. Если не указан, возвращается вся корзина
	 * @param array|string $param идентификаторы характеристик товара из таблицы {shop_param_select}
	 * @param array|string $additional_cost идентификаторы сопутствующих услуг
	 * @return array
	 */
	public function get_good($good_id, $param, $additional_cost)
	{
        if(is_array($param))
        {
            sort($param);
            $param = implode(',', $param);
        }
        
        if(is_array($additional_cost))
        {
           sort($additional_cost);
           $additional_cost = implode(',', $additional_cost);
        }
        
        $rows = $this->db_goods();
        foreach($rows as $row)
        {
            if($row["good_id"] == $good_id && $row["param"] == $param && $row["additional_cost"] == $additional_cost)
            {
                return $row;
            }
        }
        return false;
	}

	/**
	 * Возвращает количество товара в корзине
	 *
	 * @param integer $good_id идентификатор товара. Если не указан, возвращается вся корзина
	 * @param integer $price_id идентификатор цены
	 * @return float
	 */
	public function get_count_good($good_id, $price_id = 0)
	{
        $rows = $this->db_goods();
        $count = 0;
        foreach($rows as $row)
        {
            if($row["good_id"] == $good_id && (! $price_id || $row["price_id"] == $price_id))
            {
                $count += $row["count"];
            }
        }
        return $count;
	}

	/**
	 * Возвращает количество товаров в корзине
	 * 
	 * @return integer
	 */
	public function get_count()
	{
        $db_cart = $this->db_cart();
		return $db_cart ? $db_cart["count_goods"] : 0;
	}

	/**
	 * Возвращает общую стоимость товаров в корзине
	 * 
	 * @return float
	 */
	public function get_summ()
	{
        $db_cart = $this->db_cart();
		return $db_cart ? $db_cart["summ"] : 0;
	}

	/**
	 * Возвращает идентификатор последнего заказа
	 * 
	 * @return integer
	 */
	public function get_last_order()
	{
        if($this->diafan->_users->id)
        {
            $order_id = DB::query_result("SELECT id FROM {shop_order} WHERE user_id=%d AND trash='0' ORDER BY id DESC LIMIT 1", $this->diafan->_users->id);
            return $order_id;
        }
        else
        {
            $db_cart = $this->db_cart();
            if(! empty($db_cart["orders"]))
            {
                $orders = explode(',', $db_cart["orders"]);
                return array_pop($orders);
            }
        }
        return 0;
	}

	/**
	 * Добавляет корзину
	 * 
	 * @return void
	 */
	public function add()
	{
        if(! $db_cart = $this->db_cart())
        {
            $id = DB::query("INSERT INTO {shop_cart} (user_id, session_id) VALUES (%d, '%s')",
            $this->diafan->_users->id,
            (! $this->diafan->_users->id ? $this->diafan->_session->id : '')
            );
            $this->set_cache_cart(array(
                "id" => $id,
                "user_id" => $this->diafan->_users->id,
                "session_id" => (! $this->diafan->_users->id ? $this->diafan->_session->id : ''),
            ));
        }
        return $this->cache["db_cart"]["id"];
    }

	/**
	 * Изменяет запись в корзине
	 * 
	 * @param array $data изменяемые данные
	 * @return void
	 */
	public function edit($data)
	{
        if(isset($data["additional_cost"]) && is_array($data["additional_cost"]))
        {
            $data["additional_cost"] = array_unique($data["additional_cost"]);
            sort($data["additional_cost"]);
            $data["additional_cost"] = implode(',', $data["additional_cost"]);
        }

        $db_cart = $this->db_cart();
        if(! $db_cart)
        {
            $db_cart = array(
                "delivery_id" => ! empty($data["delivery_id"]) ? $data["delivery_id"] : '',
                "additional_cost" => ! empty($data["additional_cost"]) ? $data["additional_cost"] : '',
                "name" => ! empty($data["name"]) ? $data["name"] : '',
                "mail" => ! empty($data["mail"]) ? $data["mail"] : '',
                "orders" => !empty($data["order_id"]) ? $data["order_id"] : '',
            );
            $db_cart["id"] = DB::query("INSERT INTO {shop_cart} (user_id, session_id, name, mail, delivery_id, additional_cost, orders) VALUES (%d, '%s', '%h', '%h', %d, '%h', '%s')",
            $this->diafan->_users->id,
            (! $this->diafan->_users->id ? $this->diafan->_session->id : ''),
            $db_cart["name"],
            $db_cart["mail"],
            $db_cart["delivery_id"],
            $db_cart["additional_cost"],
            $db_cart["orders"]);
            $this->set_cache_cart($db_cart);
            return;
        }
        
        $name = ! empty($data["name"]) ? $data["name"] : $db_cart["name"];
        $mail = ! empty($data["mail"]) ? $data["mail"] : $db_cart["mail"];
        $additional_cost = (isset($data["additional_cost"]) ? $data["additional_cost"] : $db_cart["additional_cost"]);
        $delivery_id = (isset($data["delivery_id"]) ? $data["delivery_id"] : $db_cart["delivery_id"]);
        $orders = $db_cart["orders"];
        if(!empty($data["order_id"]))
        {
            if($orders)
            {
                $orders .= ',';
            }
            $orders .= $data["order_id"];
        }
        
        if($delivery_id == $db_cart["delivery_id"]
           && $additional_cost == $db_cart["additional_cost"]
           && $name == $db_cart["name"]
           && $mail == $db_cart["mail"]
           && $orders == $db_cart["orders"])
        {
            return;
        }
        DB::query("UPDATE {shop_cart} SET name='%h', mail='%h', delivery_id=%d, additional_cost='%h', orders='%s' WHERE id=%d",
        $name,
        $mail,
        $delivery_id,
        $additional_cost,
        $orders,
        $db_cart["id"]);

        $this->set_cache_cart(array(
            "delivery_id" => $delivery_id,
            "additional_cost" => $additional_cost,
            "name" => $name,
            "mail" => $mail,
        ));
    }

	/**
	 * Добавляет товар в корзину
	 * 
	 * @param integer $good_id идентификатор товара
	 * @param array $price данные о цене товара из таблицы {shop_price}
	 * @param array|string $param идентификаторы характеристик в из таблицы {shop_param_select}
	 * @param array|string $additional_cost массив идентификаторов сопутствующих услуг
	 * @param float $count количество
	 * 
	 * @return array добавленный товар
	 */
	public function add_good($good_id, $price, $param, $additional_cost, $count)
	{
        if(! $count)
        {
            $count = 1;
        }
        if(is_array($param))
        {
            sort($param);
            $param = implode(',', $param);
        }
        
        if(is_array($additional_cost))
        {
           sort($additional_cost);
           $additional_cost = implode(',', $additional_cost);
        }

        $price_id = (! empty($price) ? $price["price_id"] : 0);
        $rows = $this->db_goods();
        if($price && $this->diafan->configmodules('use_count_goods', 'shop'))
        {
            // количество товара в корзине других модификаций
            $count_price_id = 0;
            foreach ($rows as $row)
            {
                if($row["price_id"] == $price_id)
                {
                    $count_price_id += $row["count"];
                }
            }
            if ($count_price_id + $count > $price["count_goods"])
            {
                $count = $price["count_goods"] - $count_price_id;
                if($count <= 0)
                {
                    return false;
                }
            }
        }
        foreach ($rows as $i => $row)
        {
            if($row["good_id"] == $good_id && $row["price_id"] == $price_id && $row["param"] == $param && $row["additional_cost"] == $additional_cost)
            {
                $row["count"] += $count;
                DB::query("UPDATE {shop_cart_goods} SET created=%d, `count`=%f WHERE id=%d", time(), $row["count"], $row["id"]);
                $this->set_cache_good(array("count" => $row["count"]), $i);
                $upd_i = $i;
                $updated = true;
                continue;
            }
        }
        if(empty($updated))
        {
            if(! $cart = $this->db_cart())
            {
                $cart["id"] = $this->add();
            }
            $id = DB::query("INSERT INTO {shop_cart_goods} (cart_id, good_id, created, `count`, param, additional_cost, price_id) VALUES (%d, %d, %d, %f, '%s', '%s', %d)", $cart["id"], $good_id, time(), $count, $param, $additional_cost, $price_id);
            
            $this->set_cache_good(array(
                "id" => $id,
                "good_id" => $good_id,
                "param" => $param,
                "additional_cost" => $additional_cost,
                "count" => $count,
                "price_id" => $price_id,
            ));
            $upd_i = count($this->cache["db_goods"]) - 1;
        }
        $this->recalc();
        // возвращает последний из измененных товаров
        return $this->cache["db_goods"][$upd_i];
	}

	/**
	 * Изменяет запись о товарах в корзине
	 * 
	 * @param array $new_count обновляемое количество товара: ключи - идентификаторы записей из таблицы {shop_cart_goods}, значения - количество. Массив содержит только обновляемые значения
	 * @return void|array
	 */
	public function edit_goods($new_count)
	{
        $rows = $this->db_goods();
        foreach($rows as $i => $row)
        {
            if(! isset($new_count[$row["id"]]) || $new_count[$row["id"]] < 0)
            {
                continue;
            }
            if($rows[$i]["new_count"] = $new_count[$row["id"]])
            {
                $this->diafan->_shop->price_prepare_id($row["price_id"]);
            }
        }
        $count_goods = 0;
        $upd = 0;
        $upd_i = 0;
        foreach($rows as $i => $row)
        {
            if(! isset($new_count[$row["id"]]))
            {
                $count_goods++;
                continue;
            }
            if($new_count[$row["id"]] < 0)
                continue;

            $upd++;
            if($this->diafan->configmodules('use_count_goods', 'shop'))
            {
                if($price = $this->diafan->_shop->price_get_id($row["price_id"]))
                {
                    // количество товара в корзине других модификаций
                    $count_price_id = 0;
                    foreach ($rows as $r)
                    {
                        if($r["price_id"] == $row["price_id"] && ($r["additional_cost"] != $row["additional_cost"] || $r["param"] != $row["param"]))
                        {
                            $count_price_id += $r["new_count"];
                        }
                    }
                    if ($count_price_id + $row["new_count"] > $price["count_goods"])
                    {
                        $row["new_count"] = $price["count_goods"] - $count_price_id;
                        if($row["new_count"] <= 0)
                        {
                            $row["new_count"] = 1;
                        }
                    }
                }
            }
            if($row["new_count"])
            {
                DB::query("UPDATE {shop_cart_goods} SET created=%d, `count`=%f WHERE id=%d", time(), $row["new_count"], $row["id"]);
                $this->set_cache_good(array("count" => $row["new_count"]), $i);
                $count_goods++;
                $upd_i = $i;
            }
            else
            {
                DB::query("DELETE FROM {shop_cart_goods} WHERE id=%d", $row["id"]);
                $this->set_cache_good(false, $i);
            }
        }
        if(! $count_goods)
        {
            $this->clear();
            return;
        }
        // запрашиваем всю корзину, чтобы пересчитать количество
        if($upd)
        {
            $this->recalc();
        }
        // возвращает последний из измененных товаров
        return $this->cache["db_goods"][$upd_i];
	}

	/**
	 * Пересчитывает сумму и количество товаров в корзине
	 * 
	 * @return void
	 */
	private function recalc()
    {
        unset($this->cache["cart"]);
        $this->get();
    }

	/**
	 * Очищает коризину
	 *
	 * @param integer $order_id идентификатор добавленного заказа, если корзина очищается после совершения заказа
	 * @return void
	 */
	public function clear($order_id = 0)
	{
        $db_cart = $this->db_cart();
        if($db_cart["id"])
        {
            DB::query("DELETE FROM {shop_cart_goods} WHERE cart_id=%d", $db_cart["id"]);
            // не удаляем запись о корзине у неавторизованных пользователей, если собраны контакты или есть заказы
            if($this->diafan->_users->id || ! $db_cart["name"] && ! $db_cart["mail"] && ! $db_cart["orders"] && ! $order_id)
            {
                DB::query("DELETE FROM {shop_cart} WHERE id=%d", $db_cart["id"]);
            }
            else
            {
                $orders = ($db_cart["orders"] ? explode(',', $db_cart["orders"]) : array());
                if($order_id)
                {
                    $orders[] = $order_id;
                }
                DB::query("UPDATE {shop_cart} SET summ=0, count_goods=0, additional_cost='', orders='%s' WHERE id=%d", implode(',', $orders), $db_cart["id"]);
            }
            unset($this->cache["cart"]);
            unset($this->cache["db_cart"]);
            unset($this->cache["db_goods"]);
        }
    }
    
	/**
	 * Получает запись из БД о корзине пользователя
	 * 
	 * @return array
	 */
    public function db_cart()
    {
        if(! isset($this->cache["db_cart"]))
        {
            $this->cache["db_cart"] = array();
            $carts = DB::query_fetch_all("SELECT * FROM {shop_cart} WHERE ".
            ($this->diafan->_users->id ?
             "(user_id=".$this->diafan->_users->id." OR user_id=0 AND session_id='".$this->diafan->_session->id."')"
             : "session_id='".$this->diafan->_session->id."'")
            ." AND trash='0'");
            if($carts)
            {
                $this->cache["db_cart"] = $carts[0];
                // слияние корзин
                if(count($carts) > 1)
                {
                    $uniq_goods = DB::query_fetch_value("SELECT CONCAT(good_id,'_',price_id,'_',param,'_',additional_cost) as v FROM {shop_cart_goods} WHERE cart_id=%d", $carts[0]["id"], "v");
                    for($i = 1; $i < count($carts); $i++)
                    {
                        if($uniq_goods)
                        {
                            DB::query("DELETE FROM {shop_cart_goods} WHERE cart_id=%d AND CONCAT(good_id,'_',price_id,'_',param,'_',additional_cost) IN ('".implode("','", $uniq_goods)."')", $carts[$i]["id"]);
                        }

                        DB::query("UPDATE {shop_cart_goods} SET cart_id=%d WHERE cart_id=%d", $carts[0]["id"], $carts[$i]["id"]);
                        DB::query("DELETE FROM {shop_cart} WHERE id=%d", $carts[$i]);
                        $carts[0]["orders"] .= ($carts[0]["orders"] && $carts[$i]["orders"] ? ',' :'').$carts[$i]["orders"];
                        $carts[0]["additional_cost"] .= ($carts[0]["additional_cost"] && $carts[$i]["additional_cost"] ? ',' :'').$carts[$i]["additional_cost"];
                        $carts[0]["name"] = $carts[0]["name"] ? $carts[0]["name"] : $carts[$i]["name"];
                        $carts[0]["mail"] = $carts[0]["mail"] ? $carts[0]["mail"] : $carts[$i]["mail"];
                        $carts[0]["delivery_id"] = $carts[0]["delivery_id"] ? $carts[0]["delivery_id"] : $carts[$i]["delivery_id"];
                    }
                    if($carts[0]["orders"])
                    {
                        $orders = array_unique(explode(',', $carts[0]["orders"]));
                        sort($orders);
                        $carts[0]["orders"] = implode(',', $orders);
                    }
                    if($carts[0]["additional_cost"])
                    {
                        $additional_cost = array_unique(explode(',', $carts[0]["additional_cost"]));
                        sort($additional_cost);
                        $carts[0]["additional_cost"] = implode(',', $additional_cost);
                    }
                    if($carts[0]["orders"] && $this->diafan->_users->id)
                    {
                        DB::query("UPDATE {shop_order} SET user_id=%d WHERE user_id=0 AND id IN (%s)", $this->diafan->_users->id, preg_replace('/[^0-9\,]+/', '', $carts[0]["orders"]));
                        $carts[0]["orders"] = '';
                    }
                    DB::query("UPDATE {shop_cart} SET user_id=%d, session_id='%s', orders='%s', name='%s', mail='%s', additional_cost='%s', delivery_id=%d WHERE id=%d",
                    $this->diafan->_users->id,
                    ($this->diafan->_users->id ? '' : $this->diafan->_session->id),
                    $carts[0]["orders"],
                    $carts[0]["name"],
                    $carts[0]["mail"],
                    $carts[0]["additional_cost"],
                    $carts[0]["delivery_id"],
                    $carts[0]["id"]);
                    $this->cache["db_cart"] = $carts[0];
                }
                elseif($this->diafan->_users->id && ! $this->cache["db_cart"]["user_id"])
                {
                    // после авторизации переписываем корзину на пользователя
                    DB::query("UPDATE {shop_cart} SET user_id=%d, session_id='', orders='' WHERE id=%d", $this->diafan->_users->id, $this->cache["db_cart"]["id"]);
                    // все совершенные ранее заказа записываем на пользователя
                    if($this->cache["db_cart"]["orders"])
                    {
                        DB::query("UPDATE {shop_order} SET user_id=%d WHERE user_id=0 AND id IN (%s)", $this->diafan->_users->id, preg_replace('/[^0-9\,]+/', '', $this->cache["db_cart"]["orders"]));
                        $this->cache["db_cart"]["orders"] = '';
                    }
                }
                $this->cache["db_cart"]["additional_costs"] = explode(',', $this->cache["db_cart"]["additional_cost"]);
            }
        }
        return $this->cache["db_cart"];
    }
    
	/**
	 * Получает запись из БД о товарах в корзине
	 * 
	 * @return array
	 */
    public function db_goods()
    {
        if(! isset($this->cache["db_cart"]))
        {
            $this->db_cart();
        }
        if(! isset($this->cache["db_goods"]))
        {
            $this->cache["db_goods"] = array();
            
            if(! empty($this->cache["db_cart"]["id"]))
            {
                $rows = DB::query_fetch_all("SELECT * FROM {shop_cart_goods} WHERE cart_id=%d ORDER BY good_id ASC", $this->cache["db_cart"]["id"]);
                foreach($rows as $row)
                {
                    $this->set_cache_good($row);
                }
            }
            $count_goods = (! empty($this->cache["db_cart"]["count_goods"]) ? $this->cache["db_cart"]["count_goods"] : 0);
            if(count($this->cache["db_goods"]) != $count_goods)
            {
                $this->recalc();
            }
        }
        return $this->cache["db_goods"];
    }
    
	/**
	 * Обновляет текущие данные о корзине
	 *
	 * @param array $data данные о корзине
	 * @return void
	 */
    private function set_cache_cart($data)
    {
        $fields = array("id", "count_goods", "summ", "user_id", "session_id", "delivery_id", "additional_cost", "name", "mail", "orders");
        foreach($fields as $field)
        {
            $value = '';
            if(isset($data[$field]))
            {
                $value = $data[$field];
            }
            elseif(isset($this->cache["db_cart"][$field]))
            {
                $value = $this->cache["db_cart"][$field];
            }
            $this->cache["db_cart"][$field] = $value;
        }
        $this->cache["db_cart"]["additional_costs"] = explode(',', $this->cache["db_cart"]["additional_cost"]);
    }
    
	/**
	 * Обновляет текущие данные о товарах
	 *
	 * @param array $data данные о товаре
	 * @param integer $i номер строке в текущих данных
	 * @return void
	 */
    private function set_cache_good($data, $i = false)
    {
        if(! $data)
        {
            if(isset($this->cache["db_goods"][$i]))
            {
                unset($this->cache["db_goods"][$i]);
            }
            return;
        }
        $fields = array("id", "count", "good_id", "price_id", "additional_cost", "param");

        if($i === false)
        {
            $row = array();
        }
        else
        {
            $row = $this->cache["db_goods"][$i];
        }
        foreach($fields as $field)
        {
            if(isset($data[$field]))
            {
                $row[$field] = $data[$field];
            }
            if(! isset($row[$field]))
            {
                $row[$field] = '';
            }
        }
        $row["params"] = explode(',', $row["param"]);
        $row["additional_costs"] = explode(',', $row["additional_cost"]);
        if($i === false)
        {
            $this->cache["db_goods"][] = $row;
        }
        else
        {
            $this->cache["db_goods"][$i] = $row;
        }
    }
}