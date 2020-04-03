<?php
/**
 * Модель модуля Список желаний
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
 * Wishlist_model
 */
class Wishlist_model extends Model
{
	/**
	 * Генерирует данные для формы редактирования товаров
	 * 
	 * @return void
	 */
	public function form()
	{
		$this->form_errors($this->result, 'wishlist', array(''));
		$this->result["currency"] = $this->diafan->configmodules("currency", "shop");
		$this->result["summ"] = 0;
		$this->result["count"] = 0;
		$wishlist = $this->diafan->_wishlist->get();
		if ($wishlist)
		{
			$k = 0;
			foreach ($wishlist as $good_id => $array)
			{
				if (! $row = DB::query_fetch_array("SELECT id, [name], article, site_id, no_buy FROM {shop} WHERE [act]='1' AND id = %d AND trash='0' LIMIT 1", $good_id))
				{
					continue;
				}
				$link = $this->diafan->_route->link($row["site_id"], $row["id"], "shop");
				$img = $this->diafan->_images->get('medium', $good_id, 'shop', 'element', $row["site_id"], $row["name"]);
				foreach ($array as $param => $arr)
				{
					foreach ($arr as $additional_cost => $c)
					{
						$this->result["rows"][$k]["id"] = $row["id"];
						$this->result["rows"][$k]["buy"] = ! $row["no_buy"];
						$this->result["rows"][$k]["name"] = $row["name"];
						$this->result["rows"][$k]["article"] = $row["article"];
						$this->result["rows"][$k]["link"] = $link;
						$query = array();
						$params = unserialize($param);
						foreach ($params as $id => $value)
						{
							$query[] = 'p'.$id.'='.$value;
							if (empty($param_names[$id]))
							{
								$param_names[$id] = DB::query_result("SELECT [name] FROM {shop_param} WHERE id=%d LIMIT 1", $id);
							}
							if (empty($select_names[$id][$value]))
							{
								$select_names[$id][$value] =
									DB::query_result("SELECT [name] FROM {shop_param_select} WHERE param_id=%d AND id=%d LIMIT 1", $id, $value);
							}
	
							$this->result["rows"][$k]["name"] .= ', '.$param_names[$id].': '.$select_names[$id][$value];
						}
						$row_price = $this->diafan->_shop->price_get($good_id, $params);
						$price = $row_price["price"];
						if(! $row_price["count_goods"] && $this->diafan->configmodules("use_count_goods", "shop"))
						{
							$this->result["rows"][$k]["buy"] = false;
						}
						
						$this->result["rows"][$k]["additional_cost"] = array();
						if($additional_cost)
						{
							$additional_cost_rels = DB::query_fetch_all("SELECT a.id, a.[name], a.percent, a.price, a.amount, r.element_id, r.summ FROM {shop_additional_cost} AS a INNER JOIN {shop_additional_cost_rel} AS r ON r.additional_cost_id=a.id WHERE r.element_id=%d AND a.id IN (%s) AND a.trash='0'", $good_id, $additional_cost);
							foreach($additional_cost_rels as $a_c_rel)
							{
								if($a_c_rel["amount"] && $a_c_rel["amount"] <= $row_price["price"])
								{
									$a_c_rel["summ"] = 0;
								}
								elseif($a_c_rel["percent"])
								{
									$a_c_rel["summ"] = ($row_price["price"] * $a_c_rel["percent"]) / 100;
								}
								elseif(! $a_c_rel["summ"])
								{
									$a_c_rel["summ"] = $a_c_rel["price"];
								}
								if($a_c_rel["summ"])
								{
									$a_c_rel["format_summ"] = $this->diafan->_shop->price_format($a_c_rel["summ"]);
								}
								$price += $a_c_rel["summ"];
								$this->result["rows"][$k]["additional_cost"][] = $a_c_rel;
							}
						}
	
						$this->result["rows"][$k]["link"] .= ! empty($query) ? '?'.implode('&', $query) : '';
						$this->result["rows"][$k]["count"] = $c["count"];
						if ($img)
						{
							if($price_image_rel = DB::query_result("SELECT image_id FROM {shop_price_image_rel} WHERE price_id=%d LIMIT 1", $row_price["price_id"]))
							{
								foreach ($img as $i)
								{
									if($i["id"] == $price_image_rel)
									{
										$this->result["rows"][$k]["img"] = $i;
									}
								}
							}
							if(empty($this->result["rows"][$k]["img"]))
							{
								$this->result["rows"][$k]["img"] = $img[0];
							}
						}
						$this->result["rows"][$k]["id"] = $row["id"].'_'.str_replace(array('{',':',';','}',' ','"',"'"), '', $param).'_'.$additional_cost;
						$this->result["rows"][$k]["price"] = $this->diafan->_shop->price_format($price);
						$this->result["rows"][$k]["summ"] = $this->diafan->_shop->price_format($price * $c["count"]);
	
						$this->result["summ"] += $price * $c["count"];
						$this->result["count"] += $c["count"];
						$k++;
					}
				}
			}
		}
		$this->result["summ"] = $this->diafan->_shop->price_format($this->result["summ"]);
		$this->result["access_buy"] =  (! $this->diafan->configmodules('security_user', "shop") || $this->diafan->_users->id) ? true : false;
		if(! $this->diafan->_route->module("cart"))
		{
			$this->result["access_buy"] = false;
		}
		$this->result["view"] = 'form';
	}

	/**
	 * Генерирует данные для шаблонной функции: выводит информацию о заказанных товарах
	 * 
	 * @return array
	 */
	public function show_block()
	{
		$link = $this->diafan->_route->module("wishlist").'?'.rand(0, 999999);
		if(! $link)
		{
			return false;
		}
		$result["link"] = BASE_PATH_HREF.$link;

		$result["count"] = $this->diafan->_wishlist->get_count();

		return $result;
	}
}