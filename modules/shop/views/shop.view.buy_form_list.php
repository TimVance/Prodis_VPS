<?php
/**
 * Шаблон кнопки «Купить», в котором характеристики, влияющие на цену выводятся в виде выпадающего списка
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

if (! empty($result["result"]["access_buy"]))
	return false;

if($result["row"]["empty_price"])
	return false;

$action = '';
if(! $result["result"]["cart_link"] || $result["row"]["no_buy"] || empty($result["row"]["count"]))
{
	$action = 'buy';
}

echo '
<form method="post" action="" class="js_shop_form shop_form ajax">
<input type="hidden" name="good_id" value="'. $result["row"]["id"].'">
<input type="hidden" name="module" value="shop">
<input type="hidden" name="action" value="'.$action.'">';

if ($result["row"]["price_arr"])
{
	$depends_param = array();
	foreach ($result["result"]["depends_param"] as $param)
	{
		$depends_param[$param["id"]]["name"] = $param["name"];
		foreach($param["values"] as $value)
		{
			$depends_param[$param["id"]]["values"][$value["id"]] = $value["name"];
		}
	}
	foreach($result["row"]["price_arr"] as $price)
	{
		echo '<form method="post" action="" class="js_shop_form shop_form ajax">
		<input type="hidden" name="good_id" value="' . $result["row"]["id"] . '">
		<input type="hidden" name="module" value="shop">
		<input type="hidden" name="action" value="'.$action.'">
		<input type="hidden" name="ajax" value="">';
		echo '<div class="js_shop_form_param shop_form_param">';
		foreach($price["param"] as $param)
		{
			echo ' <input type="hidden" name="param'.$param["id"].'" value="'.$param["value"].'">';
			echo $depends_param[$param["id"]]["name"].': '.$depends_param[$param["id"]]["values"][$param["value"]];
		}
		foreach ($result["result"]["depends_param"] as $param)
		{
			if(! empty($result["row"]["param_multiple"][$param["id"]]))
			{
				if(count($result["row"]["param_multiple"][$param["id"]]) == 1)
				{
					foreach($result["row"]["param_multiple"][$param["id"]] as $value => $depend)
					{
						if($depend == 'select')
						{
							echo '<input type="hidden" name="param'.$param["id"].'" value="'.$value.'">';
						}
					}
				}
				else
				{
					$select = '';
					foreach($param["values"] as $value)
					{
						if(! empty($result["row"]["param_multiple"][$param["id"]][$value["id"]])
						   && $result["row"]["param_multiple"][$param["id"]][$value["id"]] == 'select')
						{
							if(! $select)
							{
								$select = ' '.$param["name"].': <select name="param'.$param["id"].'" class="cs-select inpselect'.($result["row"]["param_multiple"][$param["id"]][$value["id"]] == 'depend' ? ' depend_param js_shop_depend_param' : '').'">';
							}

							$select .= '<option value="'.$value["id"].'"'
							.(! empty($_GET["p" . $param["id"]]) && $_GET["p" . $param["id"]] == $value["id"] ? ' selected' : '')
							.'>'.$value["name"].'</option>';
						}
					}
					if($select)
					{
						echo $select.'</select> ';
					}
				}
			}
		}
		echo '</div>';
		echo '<div class="shop_price"><span class="shop_price_value">' . $price["price"] . '</span> <span class="shop_price_currency">' . $result["result"]["currency"] . '</span></div>';
		if(! empty($price["old_price"]))
		{
			echo '<div class="shop_old_price">' . $this->diafan->_('Старая цена') . ': <span class="shop_price_value">' . $price["old_price"] . '</span>'
			. ' <span class="shop_price_currency">' . $result["result"]["currency"] . '</span></div>';
		}
		if (! $price["count"] || empty($price["price_no_format"]) && ! $result['result']["buy_empty_price"] || $result["row"]["no_buy"])
		{
			echo '<div class="js_shop_no_buy shop_no_buy">' . $this->diafan->_('Товар временно отсутствует') . '</div>';
			echo '
			<div class="js_shop_waitlist shop_waitlist">
				'.$this->diafan->_('Сообщить когда появится на e-mail').'
				<input type="text" name="mail" value="'.$this->diafan->_user->mail.'" class="inptext">
				<span class="button_wrap"><input type="button" class="button" value="'.$this->diafan->_('Ок', false).'" action="wait"></span>
				<div class="errors error_waitlist" style="display:none"></div>
			</div>';
		}
		else
		{
			echo '<div class="js_shop_buy shop_buy to-cart">';
				if (empty($result["row"]['is_file']))
				{
					echo $this->diafan->_('Кол-во', false).': <input type="text" value="1" name="count" class="number" pattern="[0-9]+([\.|,][0-9]+)?" step="any">';
					if(! empty($result["row"]["measure_unit"]))
					{
						echo ' '.$result["row"]["measure_unit"].' ';
					}
				}
				echo '<input type="button" class="button solid" value="'.$this->diafan->_('Купить', false).'" action="buy">';
			echo '</div>';
		}
		echo '<div class="error">';
		if(! empty($price["count_in_cart"]))
		{
			$measure_unit = ! empty($result["row"]["measure_unit"]) ? $result["row"]["measure_unit"] : $this->diafan->_('шт.');
			echo $this->diafan->_('В <a href="%s">корзине</a> %s %s', true, BASE_PATH_HREF.$result["result"]["cart_link"], $price["count_in_cart"], $measure_unit);
		}
		echo '</div>
		</form>';
	}
}

//форма быстрого заказа
if(! empty($result["result"]["one_click"]))
{
	$result["result"]["one_click"]["good_id"] = $result["row"]["id"];
	echo $this->get('one_click', 'cart', $result["result"]["one_click"]);
}

$this->diafan->_site->js_view[] = 'modules/shop/js/shop.buy_form.js';