<?php
/**
 * Шаблон таблицы с товарами, отправляемый пользователю по почте
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN')) {
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
echo '<table cellpadding="5" bgcolor="#eeeeee">
	<thead><tr>
		<th bgcolor="#f9f9f9"></th>
		<th bgcolor="#f9f9f9">'.$this->diafan->_('Наименование товара').'</th>';
		if(! empty($result["measure_unit"]))
		{
			echo '<th bgcolor="#f9f9f9">'.$this->diafan->_('Единица измерения').'</th>';
		}
		echo '
		<th bgcolor="#f9f9f9">'.$this->diafan->_('Кол-во').'</th>
		<th bgcolor="#f9f9f9" nowrap>'.$this->diafan->_('Цена').'('.$result["currency"].')</th>';
		if($result["discount"])
		{
			echo '<th bgcolor="#f9f9f9" nowrap>'.$this->diafan->_('Цена со скидкой').'('.$result["currency"].')</th>';
			echo '<th bgcolor="#f9f9f9">'.$this->diafan->_('Скидка').'(%)</th>';
		}
		echo '<th bgcolor="#f9f9f9" nowrap>'.$this->diafan->_('Сумма').'('.$result["currency"].')</th>
	</tr></thead><tbody>';

//товары
if(! empty($result["rows"]))
{
	foreach ($result["rows"] as $row)
	{
		echo '
		<tr>
			<td bgcolor="#ffffff">';
		if (!empty($row["img"]))
		{
			echo '<a href="'.BASE_PATH_HREF.$row["link"].'"><img src="http'.(IS_HTTPS ? "s" : '')."://".getenv("HTTP_HOST").$row["img"]["src"].'" width="'.$row["img"]["width"].'" height="'.$row["img"]["height"].'" alt="'.$row["img"]["alt"].'" title="'.$row["img"]["title"].'"></a> ';
		}
		echo '</td>
			<td bgcolor="#ffffff">';
			if(! empty($row["cat"]))
			{
				echo '<a href="'.BASE_PATH_HREF.$row["cat"]["link"].'">'.$row["cat"]["name"].'</a> / ';
			}
			echo '<a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["name"];
			if(! empty($row["param"]))
			{
				foreach($row["param"] as $name => $value)
				{
					echo ', '.$name.': '.$value;
				}
			}
			if(! empty($row["article"]))
			{
				echo '<br/>'.$this->diafan->_('Артикул').': '.$row["article"];
			}
			echo '</a>';
			if(! empty($row["additional_cost"]))
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
			echo '</td>';
			if(! empty($result["measure_unit"]))
			{
				echo '<td bgcolor="#ffffff">'.($row["measure_unit"] ? $row["measure_unit"] : $this->diafan->_('шт.')).'</td>';
			}
			echo '
			<td bgcolor="#ffffff">'.$row["count"].'</td>';
            if($result["discount"])
			{
			    echo '<td bgcolor="#ffffff">'.($row["old_price"] ? $row["old_price"] : $row["price"]).'</td>';
				echo '<td bgcolor="#ffffff">'.($row["old_price"] ? $row["price"] : '').'</td>';
			    echo '<td bgcolor="#ffffff">'.($row["discount"] ? $row["discount"] : '').'</td>';
			}
			else
			{
				echo '<td bgcolor="#ffffff">'.$row["price"].'</td>';
			}
			echo '
			<td bgcolor="#ffffff">'.$row["summ"].'</td>
		</tr>';
	}

	if(! empty($result["old_summ_goods"]))
	{
		echo '
			<tr>
				<td bgcolor="#ffffff" colspan="'.($result["discount"] ? 6 : 4).'" align="right">'.$this->diafan->_('Итого без скидок').'</td>
				<th bgcolor="#ffffff">'.$result["old_summ_goods"].'</th>
		</tr>';
	}
	
	// общая скидка от объема
	if(! empty($result["discount_summ"]))
	{
		echo '
			<tr>
				<td bgcolor="#ffffff" colspan="'.($result["discount"] ? 6 : 4).'" align="right">'.$this->diafan->_('Общая скидка').'</td>
				<th bgcolor="#ffffff">'.$result["discount_summ"].'</th>
		</tr>';
	}
	


	//итоговая строка для товаров
	echo '
		<tr>
                        
			<td bgcolor="#fefefe" colspan="2" align="right">'.$this->diafan->_('Всего товаров').'</td>';
	if(! empty($result["measure_unit"]))
	{
		echo '<td bgcolor="#ffffff"></td>';
	}
	echo '<th bgcolor="#fefefe">'.$result["count"].'</th>
	<td align="right" bgcolor="#ffffff" colspan="'.($result["discount"] ? 3 : 1).'">'.$this->diafan->_('на сумму').'</td>';
	echo '
	<th bgcolor="#fefefe">';
	echo $result["summ_goods"];
	echo '</th>
	</tr>';
	
	

	$count_clm = 0;
	if(! empty($result["discount"]))
	{
		$count_clm += 2;
	}
	if(! empty($result["measure_unit"]))
	{
		$count_clm++;
	}
	
	//дополнительно
	if (! empty($result["additional_cost"])) 
	{
		echo '<tr><th colspan="'.($count_clm + 5).'" bgcolor="#ffffff" align="right">'.$this->diafan->_('Дополнительно').'</th></tr>';
		foreach ($result["additional_cost"] as $row)
		{
			if ($row['amount'])
			{
				$row['text'] .= '<br>'.$this->diafan->_('Бесплатно от суммы').' '.$row['amount'].' '.$result["currency"];
			}
			echo '
			<tr>
				<td bgcolor="#ffffff" colspan="'.($count_clm + 3).'">
					<div class="cart_additional_cost_name">'.$row["name"].'</div>
				</td>
				<td bgcolor="#ffffff">'.($row['percent'] ? $row['percent'].'%' : $row["price"]).'</td>
				<th bgcolor="#ffffff">'.$row["summ"].'</th>
			</tr>';
		}
	}

	//способы доставки
	if (! empty($result["delivery"])) 
	{
		echo '<tr><td colspan="'.($count_clm + 4).'" bgcolor="#ffffff" align="right">'.$this->diafan->_('Способ доставки').': '.$result["delivery"]["name"].'
			</td>
			<th bgcolor="#ffffff">'.$result["delivery"]["summ"].'</th>
		</tr>';
	}
}


//итоговая строка таблицы
echo '
	<tr>
		<th bgcolor="#ffffff" align="right" colspan="'.($count_clm + 4).'">'.$this->diafan->_('Итого к оплате').'</th>';

	echo '<th bgcolor="#ffffff">'.$result["summ"];
	if(! empty($result["tax"]))
	{
		echo '<br>'.$this->diafan->_('в т. ч. %s', true, $result["tax_name"]).'<br>'.$result["tax"];
	}
	echo '</th>
	</tr></tbody>
</table>';
