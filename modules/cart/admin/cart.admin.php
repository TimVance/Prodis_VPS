<?php
/**
 * Брошенные корзины
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
 * Cart_admin
 */
class Cart_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_cart';
	
	/**
	 * @var string тип элемента
	 */
	public $element_type = 'element';

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'goods' => array(
			'name' => 'Товары',
		),
		'name' => array(
			'name' => 'Пользователь',
			'sql' => true,
		),
		'mail' => array(
			'name' => 'E-mail',
			'sql' => true,
		),
		'sended' => array(
			'name' => 'Отправления',
			'type' => 'function',
		),
		'user_id' => array(
			'type' => 'none',
			'sql' => true,
		),
		'orders' => array(
			'name' => 'Заказы',
			'sql' => true,
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),
		'actions' => array(
			'trash' => true,
		),
	);

	/**
	 * Выводит список корзин
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Выводит товары в заказе
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_goods($row, $var)
	{
		if(! isset($this->cache["prepare"]["cart"]))
		{
			$rows_goods = DB::query_fetch_all(
				"SELECT * FROM {shop_cart_goods} WHERE cart_id IN (%s)",
				implode(",", $this->diafan->rows_id)
			);
			$this->cache["prepare"]["cart"] = array();
			if($rows_goods)
			{
				foreach($rows_goods as $c)
				{
					$this->cache["prepare"]["cart"][$c["cart_id"]][] = $c;
				}
				// все товары одним запросом
				$good_ids = array_unique($this->diafan->array_column($rows_goods, "good_id"));
				$this->cache["prepare"]["goods"] = DB::query_fetch_key("SELECT * FROM {shop} WHERE [act]='1' AND id IN (%s) AND trash='0'", implode(",", $good_ids), "id");
	
				// все значения характеристик одним запросом
				$param_select_ids = $this->diafan->filter(array_unique(explode(',', implode(',', $this->diafan->array_column($rows_goods, "param")))), "int");
				$this->cache["prepare"]["params_select"] = DB::query_fetch_key("SELECT id, param_id, [name] FROM {shop_param_select} WHERE id IN (%s) AND trash='0'", implode(",", $param_select_ids), "id");
		
				if($this->cache["prepare"]["params_select"])
				{
					// все характеристики одним запросом
					$param_ids = array_unique($this->diafan->array_column($this->cache["prepare"]["params_select"], "param_id"));
					$this->cache["prepare"]["params"] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param} WHERE id IN (%s) AND trash='0'", implode(",", $param_ids), "id", "name");
				}
			}
		}
		if(empty($this->cache["prepare"]["cart"][$row["id"]]))
		{
			return '';
		}
		$text = '<div>';
		foreach($this->cache["prepare"]["cart"][$row["id"]] as $i => $c)
		{
			if(empty($this->cache["prepare"]["goods"][$c["good_id"]]))
			{
				continue;
			}
			$good = $this->cache["prepare"]["goods"][$c["good_id"]];
			if($i)
			{
				$text .= '<br>';
			}
			$text .= date("d.m.Y H:i", $c["created"]).' <a href="'.BASE_PATH_HREF.'shop/edit'.$c["good_id"].'/">'.$good["name"._LANG].($good["article"] ? " ".$good["article"] : '');

			$params = explode(',', $c["param"]);
			foreach ($params as $value)
			{
				if(empty($this->cache["prepare"]["params_select"][$value]))
					continue;

				$p_id = $this->cache["prepare"]["params_select"][$value]["param_id"];
				
				if(empty($this->cache["prepare"]["params"][$p_id]))
					continue;
				$text .= ', '.$this->cache["prepare"]["params"][$p_id].': '.$this->cache["prepare"]["params_select"][$value]["name"];
			}
			$text .= '</a>';
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит даты отправлений писем
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_sended($row, $var)
	{
		if(! isset($this->cache["prepare"]["sended"]))
		{
			$this->cache["prepare"]["sended"] = DB::query_fetch_key_array(
				"SELECT * FROM {shop_cart_log_mail} WHERE cart_id IN (%s)",
				implode(",", $this->diafan->rows_id), "cart_id"
			);
		}
		if(empty($this->cache["prepare"]["sended"][$row["id"]]))
		{
			return '<div></div>';
		}
		$text = '<div>';
		foreach($this->cache["prepare"]["sended"][$row["id"]] as $i => $l)
		{
			if($i)
			{
				$text .= '<br>';
			}
			$text .= date("d.m.Y H:i", $l["created"]);
			if($l["order_id"])
			{
				$text .= ' <a href="'.BASE_PATH_HREF.'order/edit'.$a["order_id"].'/">'.$this->diafan->_('Заказа №').$a["id"].'</a>';
			}
			$text .= '</a>';
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит пользователя
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_name($row, $var)
	{
		if(! isset($this->cache["prepare"]["users"]))
		{
			$this->cache["prepare"]["users"] = DB::query_fetch_key(
				"SELECT id, fio, mail FROM {users} AS u WHERE id IN (%s)",
				implode(",", array_unique($this->diafan->array_column($this->diafan->rows, "user_id"))),
				"id"
			);
		}
		$user = array();
		$text = '<div class="name">';
		if($row["user_id"] && ! empty($this->cache["prepare"]["users"][$row["user_id"]]))
		{
			$user = $this->cache["prepare"]["users"][$row["user_id"]];
			$text .= '<a href="'.BASE_PATH_HREF.'users/edit'.$row["user_id"].'/">'.($row["name"] ? $row["name"] : $user["fio"]).'</a>';
		}
		else
		{
			$text .= $row["name"];
		}
		$text .= '</div>';
		$text .= '<div class="name">';
		if($row["mail"])
		{
			$text .= $row["mail"];
		}
		elseif(! empty($user["mail"]))
		{
			$text .= $user["mail"];
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит заказы
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_orders($row, $var)
	{
		if(! isset($this->cache["prepare"]["orders"]))
		{
			$this->cache["prepare"]["orders"] = DB::query_fetch_key_array(
				"SELECT id, user_id FROM {shop_order} WHERE user_id IN (%s)",
				implode(",", array_unique($this->diafan->array_column($this->diafan->rows, "user_id"))),
				"user_id"
			);
		}
		$text = '<div>';
		if($row["user_id"])
		{
			$orders = false;
			if(! empty($this->cache["prepare"]["orders"][$row["user_id"]]))
			{
				$orders = $this->diafan->array_column($this->cache["prepare"]["orders"][$row["user_id"]], "id");
			}
		}
		else
		{
			$orders = ($row["orders"] ? explode(',', $row["orders"]) : false);
		}
		if($orders)
		{
			foreach($orders as $i => $order_id)
			{
				if($i)
				{
					$text .= ',';
				}
				$text .= ' <a href="'.BASE_PATH_HREF.'order/edit'.$order_id.'/">№'.$order_id.'</a>';
			}
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит e-mail
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_mail($row, $var)
	{}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("shop_cart_goods", "cart_id IN (".implode(",", $del_ids).")");
	}
}