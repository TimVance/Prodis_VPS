<?php
/**
 * Шаблон таблицы с товарами в списке желаний
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

//шапка таблицы
echo '<table class="wishlist">
	<tr>
		<th class="wishlist_first_th"></th>
		<th>'.$this->diafan->_('Наименование товара').'</th>
		<th>'.$this->diafan->_('Количество').'</th>
		<th>'.$this->diafan->_('Цена').', '.$result["currency"].'</th>
		<th>'.$this->diafan->_('Сумма').', '.$result["currency"].'</th>
		<th>'.$this->diafan->_('Удалить').'</th>
		<th class="wishlist_last_th"></th>
		
	</tr>';
//товары
if(! empty($result["rows"]))
{
	foreach ($result["rows"] as $row)
	{
		echo '
		<tr class="js_wishlist_item wishlist_item">
			<td class="wishlist_img">';
		if(! empty($row["img"]))
		{
			echo '<a href="'.BASE_PATH_HREF.$row["link"].'"><img src="'.$row["img"]["src"].'" width="'.$row["img"]["width"].'" height="'.$row["img"]["height"].'" alt="'.$row["img"]["alt"].'" title="'.$row["img"]["title"].'"></a> ';
		}
		echo '</td>
			<td class="wishlist_name"><a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["name"].(! empty($row["article"]) ? '<br/>'.$this->diafan->_('Артикул').': '.$row["article"] : '').'</a>';
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
			echo '</td>
			<td class="js_wishlist_count wishlist_count"><nobr>
			<span class="js_wishlist_count_minus wishlist_count_minus">-</span>
			<input type="text" class="number" value="'.$row["count"].'" min="0" name="editshop'.$row["id"].'" size="2">
			<span class="js_wishlist_count_plus wishlist_count_plus">+</span>
			</nobr></td>
			<td class="wishlist_price">'.$row["price"].'</td>
			<td class="wishlist_summ">'.$row["summ"].'</td>
			<td class="wishlist_remove"><span class="js_wishlist_remove" confirm="'.$this->diafan->_('Вы действительно хотите удалить товар из списка отложенных товаров?', false).'"><input type="hidden" id="del'.$row["id"].'" name="del'.$row["id"].'" value="0"></span></td>
			<td class="js_wishlist_buy wishlist_buy">';
		if($row["buy"] &&  $result["access_buy"])
		{
			echo '<input type="button" value="'.$this->diafan->_('Купить', false).'" good_id="'.$row["id"].'">';
		}
		echo '</td>
		</tr>';
	}
}


//итоговая строка таблицы
echo '
	<tr class="wishlist_last_tr">
		<td class="wishlist_total" colspan="2">'.$this->diafan->_('ИТОГО').'</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td class="wishlist_summ">'.$result["summ"].'</td>
		<td>&nbsp;</td>
		<td class="wishlist_last_td">&nbsp;</td>
	</tr>
</table>';