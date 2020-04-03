<?php
/**
 * Шаблон товарный чек
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
?>

<html>
<head>
<title><?php echo $this->diafan->_('Товарный чек', false);?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">
body
{
	font-family: Arial, Helvetica, sans-serif;
}

p
{
	padding: 5px 0px 0px 5px;
}

.vas ul
{
	padding: 0px 10px 0px 15px;
}

.vas li
{
	list-style-type:circle;
}

h3
{
	padding:0px 0px 0px 5px;
	font-size:100%; 
}

h1
{
	padding:0px 0px 0px 5px; 
	font-size:120%;
}

li
{
	list-style-type: none;
	padding-bottom:5px;
	padding: 6px 0px 0px 5px;
}

.main
{
	font-size:12px;
}

.list
{
	font-size:12px;
	padding: 6px 15px 0px 5px;
}

.main input
{
	font-size:12px;
	background-color:#CCFFCC;
}

.text14
{
	font-family:"Times New Roman", Times, serif;
	font-size:14px;
}
.text14 strong
{
	font-family:"Times New Roman", Times, serif;
	font-size:11px;
}

.link
{
	font-size:12px;
}

.link a
{
	text-decoration:none;
	color:#006400;
}

.link_u
{
	font-size:12px;
}
.link_u a
{
	color:#006400;
}

table td{
	border:#000000 1px solid;
	padding: 5px;
}
</style>
</head>
<body>
<div class="text14">
<p><?php echo BASE_URL;?><br><font size="5"><?php echo TITLE;?></font></p><br>
<h1 style="width:720px; text-align: center;"><?php echo $this->diafan->_('Товарный чек', false);?> № <?php echo (!empty($values["order_id"]) ? $values["order_id"] : '');?>  <?php echo $this->diafan->_('от', false);?> &quot;<?php echo (!empty($values["date_d"]) ? $values["date_d"] : '');?>&quot; <?php echo (!empty($values["date_m"]) ? $values["date_m"] : '');?> <?php echo (!empty($values["date_y"]) ? $values["date_y"] : '');?> г.</h1>
<table width="720" bordercolor="#000000" style="border:#000000 1px solid;" cellpadding="0" cellspacing="0">
	<?php
		echo '
		<tr><td><b>'.$this->diafan->_('Наименование', false).'</b></td>
		<td><b>'.$this->diafan->_('Ед. изм.', false).'</b></td>
		<td><b>'.$this->diafan->_('Кол-во', false).'</b></td>';
		if($values["discount"])
		{
			echo '<td><b>'.$this->diafan->_('Цена, руб.', false).'</b></td>
			<td><b>'.$this->diafan->_('Скидка', false).'</b></td>
			<td><b>'.$this->diafan->_('Цена со скидкой, руб.', false).'</b></td>';
		}
		else
		{
			echo '<td><b>'.$this->diafan->_('Цена, руб.', false).'</b></td>';
		}
		echo '
		<td><b>'.$this->diafan->_('Сумма, руб.', false).'</b></td></tr>';
		if(!empty($values["goods"]))
		{
			foreach ($values["goods"] as $row)
			{
				echo '<tr>';
				
				echo '<td>'.$row['name'].($row['article'] ? ' '.$row['article'] : '').'</td>
				<td>'.(! empty($row["measure_unit"]) ? $row["measure_unit"] : $this->diafan->_('шт.', false)).'</td>
				<td>'.$row['count_goods'].'</td>';
				if($values["discount"])
				{
					echo '<td>'.$row["old_price"].'</td>
					<td>'.($row["discount"] ? $row["discount"] : '&nbsp;').'</td>';
				}
				echo '<td>'.$row["price"].'</td>
				<td>'.$row["summ"].'</td>';
				
				echo '</tr>';
			}
		}
		if(! empty($values["delivery"]))
		{
			echo '<tr><td>'.$this->diafan->_('Доставка', false).'</td>
			<td>&nbsp;</td>
			<td>1</td>
			<td>&nbsp;</td>';
			if($values["discount"])
			{
				echo '<td>&nbsp;</td>
				<td>&nbsp;</td>';
			}
			echo '<td>'.$values["delivery"]['price'].'</td></tr>';
		}
		echo '<tr><td>'.$this->diafan->_('Итого', false).'</td>
		<td>&nbsp;</td>
		<td><b>'.(! empty($values['count_goods']) ? $values['count_goods'] : '').'</b></td>
		<td>&nbsp;</td>';
		if($values["discount"])
		{
			echo '<td>'.(! empty($values["order_discount"]) ? $values["order_discount"] : '&nbsp;').'</td>
			<td>&nbsp;</td>';
		}
		echo '<td><b>'.(! empty($values['summ']) ? $values['summ'] : '').'</b>';
		if(! empty($values["tax"]))
		{
			echo '<br>'.$this->diafan->_('в т. ч. %s', true, $values["tax_name"]).'<br>'.$values["tax"];
		}
		echo '</td></tr>';
	?>
</table>
<div class="itogo" style="width:720px; text-align: left; padding: 30px 0 0 0;"><?php echo $this->diafan->_('Итого', false);?>: <u><?php echo $values['str_summ'];?></u>   <br>
<?php echo $this->diafan->_('Подпись', false);?> _____________________<br>
<?php echo $this->diafan->_('МП', false);?></div>

<div style="padding-top:40px;"><hr>
<?php echo $this->diafan->_('Покупатель', false);?>: <?php echo (! empty($user_fio) ? $user_fio : '');?><br>
<?php echo $this->diafan->_('Телефон', false);?>: <?php echo (! empty($user["phone"]) ? $user["phone"] : '');?><?php echo (! empty($user["phone-extra"]) ? ' '.$user["phone-extra"] : '');?><br>
<?php echo $this->diafan->_('Адрес доставки', false);?>:
<?php
echo (! empty($user["address"]) ? $user["address"].' ' : '');
echo (! empty($user["zip"]) ? $user["zip"].', ' : '');
echo (! empty($user["country"]) ? $user["country"].', ' : '');
echo (! empty($user["city"]) ? $user["city"].', ' : '');
echo (! empty($user["street"]) ? $user["street"].', ' : '');
echo (! empty($user["metro"]) ? $this->diafan->_('станция метро', false).' '.$user["metro"].', ' : '');
echo (! empty($user["building"]) ? $this->diafan->_('д.', false).' '.$user["building"].', ' : '');
echo (! empty($user["suite"]) ? $this->diafan->_('корпус', false).' '.$user["suite"].', ' : '');
echo (! empty($user["flat"]) ? $this->diafan->_('кв.', false).' '.$user["flat"] : '');
echo (! empty($user["entrance"]) ? $this->diafan->_('подъезд', false).' '.$user["entrance"] : '');
echo (! empty($user["floor"]) ? $this->diafan->_('этаж', false).' '.$user["floor"] : '');
echo (! empty($user["intercom"]) ? $this->diafan->_('домофон', false).' '.$user["intercom"] : '');
echo (! empty($user["cargolift"]) ? $this->diafan->_('наличие грузового лифта', false).' '.$user["cargolift"].', ' : '');
echo (! empty($user["comment"]) ? '<br><br>'.$user["comment"].', ' : '');

$address =
(! empty($user["city"]) ? $user["city"].', ' : '')
.(! empty($user["street"]) ? $user["street"].', ' : '')
.(! empty($user["building"]) ? $this->diafan->_('д.', false).' '.$user["building"].', ' : '')
.(! empty($user["suite"]) ? $this->diafan->_('корпус', false).' '.$user["suite"].', ' : '')
.(! empty($user["address"]) ? ' '.$user["address"].', ' : '');

if($address)
{
?>
<hr>
<iframe width="650" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo "http".(IS_HTTPS ? "s" : '')."://"; ?>maps.google.com/maps?ie=UTF8&iwloc=near&hl=ru&t=m&z=16&mrt=loc&geocode=&q=<?php echo urlencode($address); ?>&output=embed"></iframe>
<?php }?>
</div>
</div>
</body>
</html>