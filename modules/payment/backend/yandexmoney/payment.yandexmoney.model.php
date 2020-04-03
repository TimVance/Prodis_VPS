<?php
/**
 * Формирует данные для формы платежной системы Яндекс.Касса
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

class Payment_yandexmoney_model extends Diafan
{
	/**
     * Формирует данные для формы платежной системы "YandexMoney"
     * 
     * @param array $params настройки платежной системы
     * @param array $pay данные о платеже
     * @return array
     */
	public function get($params, $pay)
	{
		$result["summ"]      = $pay["summ"];
		$result["order_id"]  = $pay["id"];
		$result["text"]      = $pay["text"];
		$result["desc"]      = $pay["desc"];
		$result["scid"]      = $params["yandex_scid"];
		$result["shopid"]    = $params["yandex_shopid"];
		$result["test"]      = ! empty($params["yandex_test"]) ? true : false;
		$result["types"]     = (! empty($params["yandex_types"]) ? $params["yandex_types"] : array()); 
		$result["cart_rewrite"] = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='site' AND trash='0' AND element_type='element' AND element_id IN (SELECT id FROM {site} WHERE module_name='%s' AND [act]='1' AND trash='0')", $pay['module_name']);
		$result["cust_id"] = $pay["element_id"];
		
		$result["cust_email"] = '';
		if(! empty($pay["details"]["email"]))
		{
			list($result["cust_email"], ) = explode(' ', $pay["details"]["email"]);
			$result["cust_email"] = str_replace('"', '&quot;', $result["cust_email"]);
		}
		$result["cust_phone"] = '';
		if(! empty($pay["details"]["phone"]))
		{
			list($result["cust_phone"], ) = explode(' ', $pay["details"]["phone"]);
			$result["cust_phone"] = preg_replace('/^(7|8)+/', '+7', preg_replace('/[^0-9]+/', '', $result["cust_phone"]));
		}

		$result["cust_name"] = str_replace('"', '&quot;', trim((! empty($pay["details"]["name"]) ? $pay["details"]["name"].' ' : '')
		.(! empty($pay["details"]["firstname"]) ? $pay["details"]["firstname"].' ' : '')
		.(! empty($pay["details"]["lastname"]) ? $pay["details"]["lastname"].' ' : '')
		.(! empty($pay["details"]["fathersname"]) ? $pay["details"]["fathersname"].' ' : '')));

		$result["cust_addr"] = str_replace('"', '&quot;', trim(
		(! empty($pay["details"]["address"]) ? $pay["details"]["address"].' ' : '')
		.(! empty($pay["details"]["zip"]) ? $pay["details"]["zip"].' ' : '')
		.(! empty($pay["details"]["country"]) ? $pay["details"]["country"].' ' : '')
		.(! empty($pay["details"]["city"]) ? $pay["details"]["city"].' ' : '')
		.(! empty($pay["details"]["metro"]) ? $this->diafan->_('Метро', false).' '.$pay["details"]["metro"].' ' : '')
		.(! empty($pay["details"]["street"]) ? $this->diafan->_('Улица', false).' '.$pay["details"]["street"].' ' : '')
		.(! empty($pay["details"]["building"]) ? $this->diafan->_('Дом', false).' '.$pay["details"]["building"].' ' : '')
		.(! empty($pay["details"]["suite"]) ? $this->diafan->_('Корпус', false).' '.$pay["details"]["suite"].' ' : '')
		.(! empty($pay["details"]["flat"]) ? $this->diafan->_('Квартира', false).' '.$pay["details"]["flat"].' ' : '')
		.(! empty($pay["details"]["entrance"]) ? $this->diafan->_('Подъезд', false).' '.$pay["details"]["entrance"].' ' : '')
		.(! empty($pay["details"]["floor"]) ? $this->diafan->_('Этаж', false).' '.$pay["details"]["floor"].' ' : '')
		.(! empty($pay["details"]["intercom"]) ? $this->diafan->_('Домофон', false).' '.$pay["details"]["intercom"].' ' : '')));

		$result["order_details"] = '';
		$items = array();
		if(! empty($pay["details"]["discount"]))
		{
			$s = 0;
			foreach($pay["details"]["goods"] as &$r)
			{
				$s += $r["summ"];
			}
			foreach($pay["details"]["goods"] as &$r)
			{
				$r["price"] = number_format($r["price"] * ($pay["summ"]/$s), 2, '.', '');
				$r["summ"] = number_format($r["price"] * $r["count"], 2, '.', '');
			}
		}
		if(! empty($pay["details"]["goods"]))
		{
			foreach($pay["details"]["goods"] as $row)
			{
				$result["order_details"] .= $row["name"].' '.$row["article"].' '
				.$this->diafan->_('цена', false).' '.$row["price"].' '
				.$this->diafan->_('количество', false).' '.$row["count"].' '
				.$this->diafan->_('сумма', false).' '.$row["summ"]."\n";

				$items[] = array(
					"quantity" => $row["count"],
					"price" => array(
						"amount" => $row["price"],
						"currency" => "RUB"
					),
					"tax" => ! empty($params["yandex_tax"]) ? $params["yandex_tax"] : '',
					"vat_code" => ! empty($params["yandex_tax"]) ? $params["yandex_tax"] : '',
					"text" => $row["name"].($row["article"] ? ' '.$row["article"] : ''),
					"payment_mode" => "full_prepayment",
					"payment_subject" => "commodity",
				);
			}
		}
		if(! empty($pay["details"]["additional"]))
		{
			$result["order_details"] .= $this->diafan->_('Сопутствующие услуги', false)."\n";
			foreach($pay["details"]["additional"] as $row)
			{
				$result["order_details"] .= $row["name"].' '
				.$this->diafan->_('сумма', false).' '.$row["summ"]."\n";

				$items[] = array(
					"quantity" => 1,
					"price" => array(
						"amount" => $row["summ"],
						"currency" => "RUB"
					),
					"vat_code" => ! empty($params["yandex_tax"]) ? $params["yandex_tax"] : '',
					"tax" => ! empty($params["yandex_tax"]) ? $params["yandex_tax"] : '',
					"text" => $row["name"],
					"payment_mode" => "full_prepayment",
					"payment_subject" => "commodity",
				);
			}
		}
		if(! empty($pay["details"]["delivery"]))
		{
			$result["order_details"] .= $this->diafan->_('Доставка', false)."\n";
			$result["order_details"] .= $pay["details"]["delivery"]["name"].' '
			.$this->diafan->_('сумма', false).' '.$pay["details"]["delivery"]["summ"]."\n";

			$items[] = array(
				"quantity" => 1,
				"price" => array(
					"amount" => $pay["details"]["delivery"]["summ"],
					"currency" => "RUB"
				),
				"vat_code" => ! empty($params["yandex_tax"]) ? $params["yandex_tax"] : '',
				"tax" => ! empty($params["yandex_tax"]) ? $params["yandex_tax"] : '',
				"text" => $this->diafan->_('Доставка', false),
				"payment_mode" => "full_prepayment",
				"payment_subject" => "service",
			);
		}
		if(! empty($pay["details"]["discount"]))
		{
			$result["order_details"] .= $this->diafan->_('Скидка', false)." ".$pay["details"]["discount"]."\n";
		}
		
		if($result["order_details"])
		{
			$result["order_details"] = str_replace('"', '&quot;', trim($result["order_details"]));
		}
		else
		{
			$result["order_details"] = $result["desc"];
		}
		$taxSystem = 
		Custom::inc('plugins/json.php');
		$result["receipt"] = json_encode(array(
			"customerContact" => $result["cust_phone"] ? $result["cust_phone"] : $result["cust_email"],
			"taxSystem" => ! empty($params["yandex_taxSystem"]) ? $params["yandex_taxSystem"] : '',
			"items" => $items,
		), JSON_UNESCAPED_UNICODE);
		return $result;
	}
}