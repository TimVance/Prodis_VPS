<?php
/**
 * Обработка запроса при пересчете суммы покупки в списке желаний
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

class Wishlist_action extends Action 
{
    /**
     * Пересчет суммы заказа
     * 
     * @return void
     */
	public function recalc()
	{
		$wishlist = $this->diafan->_wishlist->get();
		$newcount = 0;
		if ($wishlist)
		{
			foreach ($wishlist as $good_id => $array)
			{
				foreach ($array as $param => $arr)
				{
					foreach ($arr as $additional_cost => $c)
					{
						$index = $good_id.'_'.str_replace(array('{',':',';','}',' ','"',"'"), '', $param).'_'.$additional_cost;
						if (! empty($_POST['del'.$index]))
						{
							$_POST['editshop'.$index] = 0;
						}
						$_POST['editshop'.$index] = $this->diafan->filter($_POST, 'float', 'editshop'.$index);
						$newcount += $_POST['editshop'.$index];
						$this->diafan->_wishlist->set($_POST['editshop'.$index], $good_id, $param, $additional_cost, "count");
					}
				}
			}
		}
		$this->diafan->_wishlist->write();
		$this->result["errors"][0] = $this->diafan->_('Изменения сохранены.', false);
		if(! $newcount && $this->diafan->_site->module == 'wishlist')
		{
			Custom::inc('plugins/json.php');
			$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->module('wishlist');
			return;
		}
		$this->model->form();
		$wishlist_tpl = $this->model->show_block();
		$this->result["data"] = array(
			".wishlist_table" => $this->diafan->_tpl->get('table', 'wishlist', $this->model->result),
			"#show_wishlist" => $this->diafan->_tpl->get('info', 'wishlist', $wishlist_tpl)
		);
    }

	/**
	 * Добавляет товар в корзину
	 * 
	 * @return void
	 */
	public function buy()
	{
		if (empty($_POST['good_id']))
		{
			return;
		}
		if(! $cart_link = $this->diafan->_route->module("cart"))
		{
			return false;
		}

		$count = $this->diafan->filter($_POST, 'int', "count", 1);
		$count = $count > 0 ? $count : 1;

		$wishlist = $this->diafan->_wishlist->get();
		if ($wishlist)
		{
			foreach ($wishlist as $good_id => $array)
			{
				foreach ($array as $param => $arr)
				{
					foreach ($arr as $additional_cost => $c)
					{
						if($good_id.'_'.str_replace(array('{',':',';','}',' ','"',"'"), '', $param).'_'.$additional_cost == $_POST['good_id'])
						{
                            $params = unserialize($param);

                            $price = $this->diafan->_shop->price_get($good_id, $params);
                            if (! $price)
                            {
                                $this->result["errors"][0] = $this->diafan->_('Товара с заданными параметрами не существует.');
                                return;
                            }
                            
							if(! $cart_good = $this->diafan->_cart->add_good($good_id, $price, $params, $additional_cost, $count))
                            {
                                $this->result["errors"][0] = $this->diafan->_('К сожалению, товар закончился на складе.', false);
                                return;
							}
	
							$this->diafan->_wishlist->set('', $good_id, $param, $additional_cost);
							$this->diafan->_wishlist->write();
	
							DB::query("UPDATE {shop} SET counter_buy=counter_buy+1 WHERE id='%d'", $good_id);
	
							Custom::inc('modules/cart/cart.model.php');
							$model = new Cart_model($this->diafan);
							$cart_tpl = $model->show_block();
	
							$this->model->form();

							Custom::inc('modules/wishlist/wishlist.model.php');
							$model = new Wishlist_model($this->diafan);
							$wishlist_tpl = $model->show_block();
	
							$this->result["data"] = array(
								"#show_cart" => $this->diafan->_tpl->get('info', 'cart', $cart_tpl),
								"#show_wishlist" => $this->diafan->_tpl->get('info', 'wishlist', $wishlist_tpl),
								'.wishlist_table' => $this->diafan->_tpl->get('table', 'wishlist', $this->model->result)
							);
	
							$this->result["errors"][0] = $this->diafan->_('Товар добавлен в <a href="%s">корзину</a>.', false, BASE_PATH_HREF.$cart_link);
	
							$this->result["result"] = 'success';
	
						}
					}
				}
			}
		}
	}
}