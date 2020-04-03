<?php
/**
 * Шаблон информации о товарах в корзине
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

$goods = '%s товаров на %s';
if(substr($result["count"], -1) == 1 || substr($result["count"], -1) == 1 && substr($result["count"], -2, 1) != 1)
{
	$goods = '%s товар на %s';
}
elseif(substr($result["count"], -1) > 1 && substr($result["count"], -1) < 5 && substr($result["count"], -2, 1) != 1)
{
	$goods = '%s товара на %s';
}

echo '<a href="'.$result["link"].'"><i class="fa fa-shopping-cart"></i>'.$this->diafan->_('Корзина') . ': ';
echo $this->diafan->_($goods, true, $result["count"], $result["summ"].'&nbsp;'.$result["currency"]);
echo (empty($result["count"]) ? '' : '<span class="button">'.$this->diafan->_('Оформить').'</span>');
echo '</a>';
if(! empty($result["rows"]))
{
	echo '<form action="" method="POST" class="js_cart_block_form cart_block_form ajax">
	<input type="hidden" name="module" value="cart">
	<input type="hidden" name="action" value="recalc">
	<input type="hidden" name="form_tag" value="'.$result["form_tag"].'">';
	//шапка таблицы
	echo '<table class="cart" cellspacing="0">';
	//товары
	if(! empty($result["rows"]))
	{
		foreach ($result["rows"] as $row)
		{
			echo '
			<tr>
				<td class="cart_img">';
			if (! empty($row["img"]))
			{
				echo '<a href="'.BASE_PATH_HREF.$row["link"].'"><img src="'.$row["img"]["src"].'" width="'.$row["img"]["width"].'" height="'.$row["img"]["height"].'" alt="'.$row["img"]["alt"].'" title="'.$row["img"]["title"].'"></a> ';
			}
			echo '</td>
				<td class="cart_name">';
				if(! empty($row["cat"]))
				{
					echo '<a href="'.BASE_PATH_HREF.$row["cat"]["link"].'">'.$row["cat"]["name"].'</a> / ';
				}
				echo '<a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["name"];
				echo (! empty($row["article"]) ? '<br/>'.$this->diafan->_('Артикул').': '.$row["article"] : '');
				echo '</a>';
				if($row["additional_cost"])
				{
					foreach($row["additional_cost"] as $a)
					{
						echo '<br>'.$a["name"];
						if($a["summ"])
						{
							echo ' + '.$a["format_summ"].' '.$result["currency"];
						}
					}
				}
				if(! $row["count"])
				{
					echo '<div class="cart_no_buy">'.$this->diafan->_('Товар временно отсутствует').'</div>';
				}
				echo '</td>
				<td class="js_cart_count cart_count"><nobr><span class="js_cart_count_minus cart_count_minus">-</span> <input type="text" class="number" value="'.$row["count"].'" min="0" name="editshop'.$row["id"].'" size="2"> <span class="js_cart_count_plus cart_count_plus">+</span></nobr></td>';
				echo '<td class="cart_price">'.$row["price"].'</td>
				<td class="cart_summ">'.$row["summ"].'</td>
				<td class="cart_remove"><span class="js_cart_remove" confirm="'.$this->diafan->_('Вы действительно хотите удалить товар из корзины?', false).'"><input type="hidden" id="del'.$row["id"].'" name="del'.$row["id"].'" value="0"></span></td>
			</tr>';
		}
	
		// общая скидка от объема
		if(! empty($result["discount_total"]) || ! empty($result["discount_next"]))
		{
			echo '
				<tr>
					<td colspan="3">';
			if(! empty($result["discount_next"]))
			{
				echo $this->diafan->_('До скидки %s осталось %s', true, $result["discount_next"]["discount"], $result["discount_next"]["summ"]);
			}
			echo '</td>
			<td>&nbsp;</td>
			<td class="cart_discount_total">';
			if(! empty($result["discount_total"]))
			{
				echo $this->diafan->_('Скидка').' '.$result["discount_total"]["discount"];
			}
			echo '</td>
			<td class="cart_last_td">&nbsp;</td>
			</tr>';
		}

		//итоговая строка для товаров
		echo '<tr class="cart_last_tr">
			<td class="cart_total" colspan="2">'.$this->diafan->_('Итого за товары').'</td>
			<td class="js_cart_count cart_count">'.$result["count"].'</td>
			<td>&nbsp;</td>
		<td class="cart_summ">';
		if(! empty($result["discount_total"]))
		{
			echo '<div class="cart_summ_old_total">'.$result["old_summ_goods"].'</div>';
		}
		echo $result["summ_goods"];
		echo '</td>
				'.(empty($result["hide_form"]) ? '<td class="cart_last_td">&nbsp;</td>' : '').'
			</tr>';
	}
	echo '</table>';
	echo '
	<div class="error"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>';
	echo '</form>';
}