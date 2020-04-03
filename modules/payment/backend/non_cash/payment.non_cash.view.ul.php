<?php
/**
 * Шаблон квитации для юридического лица
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

?>
<html>
<head>
<title>Счет для юридических лиц</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">
body { margin: 5; background-color: white; color: black; font-family: Verdana; }
TD { color: #000000; font-family: Verdana; font-size: 13px}
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000">
<table width="607" border="0" cellpadding="0" cellspacing="0">
  <tr> 
    <td width="85" height="80" valign="middle" align="center"></td>
    <td width="522" valign="top"><b><?php echo (! empty($values["non_cash_name"]) ? $values["non_cash_name"] : '');?></b><br>
      ИНН <?php echo (! empty($values["non_cash_inn"]) ? $values["non_cash_inn"] : '');?>, ОГРН <?php echo (! empty($values["non_cash_ogrn"]) ? $values["non_cash_ogrn"] : '');?><br>
      <?php echo (! empty($values["non_cash_address"]) ? $values["non_cash_address"] : '');?>
    </td>
  </tr>
  <tr valign="bottom" align="center"> 
    <td colspan="2" height="30" valign="bottom"><b>Образец заполнения платежного поручения</b></td>
  </tr>
  <tr> 
    <td height="75" colspan="2" valign="top"> 
      <table width="100%" cellpadding="4" cellspacing="0" style="border: 1px solid black; border-width: 1px 1px 0px 0px">
        <tr> 
          <td width="317" height="36" valign="top" bgcolor="#FFFFFF" style="border: 1px solid black; border-width: 0px 0px 1px 1px">Получатель:<br>
            ИНН <b><?php echo (! empty($values["non_cash_inn"]) ? $values["non_cash_inn"] : '');?> <?php echo (! empty($values["non_cash_kpp"]) ? '</b> КПП <b>'.$values["non_cash_kpp"] : '');?> <?php echo (! empty($values["non_cash_name"]) ? $values["non_cash_name"] : '');?></b></td>
          <td width="90" valign="bottom" bgcolor="#FFFFFF" align="right" style="border: 1px solid black; border-width: 0px 0px 1px 1px">Сч. №</td>
          <td valign="bottom" width="185" bgcolor="#FFFFFF" style="border: 1px solid black; border-width: 0px 0px 1px 1px"><b><?php echo (! empty($values["non_cash_rs"]) ? $values["non_cash_rs"] : '');?></b></td>
        </tr>
        <tr> 
          <td valign="top" bgcolor="#FFFFFF" height="36" style="border: 1px solid black; border-width: 0px 0px 1px 1px">Банк получателя:<br>
            <b><?php echo (! empty($values["non_cash_bank"]) ? $values["non_cash_bank"] : '');?></b></td>
          <td valign="top" bgcolor="#FFFFFF" align="right" style="border: 1px solid black; border-width: 0px 0px 1px 1px">БИК<br>
            Кор. Сч. №</td>

          <td valign="top" bgcolor="#FFFFFF" style="border: 1px solid black; border-width: 0px 0px 1px 1px"><b><?php echo (! empty($values["non_cash_bik"]) ? $values["non_cash_bik"] : '');?><br>
            </b> <b><?php echo (! empty($values["non_cash_ks"]) ? $values["non_cash_ks"] : '');?></b> </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr valign="middle" align="center"> 
    <td height="45" colspan="2" valign="middle"><b><font size="3">СЧЕТ №<?php echo (! empty($values["order_id"]) ? $values["order_id"] : '');?> от 
      <?php echo $values["order_created"];?> г.</font></b></td>
  </tr>
  <tr> 
    <td height="48" colspan="2" valign="top"><b>Заказчик:</b> <?php echo (! empty($values["user_fio"]) ? $values["user_fio"] : '');?><br>
      <b>Плательщик:</b> <?php echo (! empty($values["user_fio"]) ? $values["user_fio"] : '');?> <br>
    </td>
  </tr>
  <tr> 
    <td height="148" colspan="2" valign="top"> 
      <table width="100%" cellpadding="4" cellspacing="0" style="border: 1px solid black; border-width: 1px 1px 0px 0px">
        <tr> 
          <td width="25" height="38" valign="top" align="center" style="border: 1px solid black; border-width: 0px 0px 1px 1px"><b>№</b></td>
          <td width="317" valign="top" align="center" style="border: 1px solid black; border-width: 0px 0px 1px 1px"><b>Наименование 
            товара, услуг</b> </td>
          <td valign="top" width="54" align="center" style="border: 1px solid black; border-width: 0px 0px 1px 1px"><b>Ед.изм.</b></td>

          <td valign="top" width="42" align="center" style="border: 1px solid black; border-width: 0px 0px 1px 1px"><b>Кол-во</b></td>
          <td valign="top" width="39" align="center" style="border: 1px solid black; border-width: 0px 0px 1px 1px"><b>Цена</b></td>
          <td valign="top" width="76" align="right" style="border: 1px solid black; border-width: 0px 0px 1px 1px"><b>Сумма</b></td>
        </tr>
<?php if(! empty($values["goods"])){ $k = 0; foreach ($values["goods"] as $k => $row){?>
		<tr>
			<td valign=middle align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px"><?php echo $k+1;?></td>
			<td valign=top align=left style="border: 1px solid black; border-width: 0px 0px 1px 1px"><?php echo $row["name"];?></td>
			<td valign=top align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px"><?php echo (! empty($row["measure_unit"]) ? $row["measure_unit"] : $this->diafan->_('шт.', false));?></td>
			<td valign=middle align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px"><?php echo $row["count_goods"];?></td>
			<td valign=middle align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px"><nobr><?php echo $row["price"];?></nobr></td>
			<td valign=middle align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px"><?php echo $row["summ"];?></td>
		</tr>
<?php }}?>
<?php if(! empty($values["discount"]) && ! empty($values["goods"])){?>
		<tr>
			<td valign=middle align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px"><?php $k++; echo $k+1;?></td>
			<td valign=top align=left style="border: 1px solid black; border-width: 0px 0px 1px 1px">Скидка</td>
			<td valign=top align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px">&nbsp;</td>
			<td valign=middle align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px">&nbsp;</td>
			<td valign=middle align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px"><nobr><?php echo $values['discount'];?></nobr></td>
			<td valign=middle align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px"><?php echo $values['discount'];?></td>
		</tr>
<?php }?>
<?php if(! empty($values["delivery"]) && ! empty($values["goods"])){?>
		<tr>
			<td valign=middle align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px"><?php $k++; echo $k+1;?></td>
			<td valign=top align=left style="border: 1px solid black; border-width: 0px 0px 1px 1px"><?php echo $values["delivery"]['name'];?></td>
			<td valign=top align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px">&nbsp;</td>
			<td valign=middle align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px">1</td>
			<td valign=middle align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px"><nobr><?php echo $values["delivery"]['price'];?></nobr></td>
			<td valign=middle align=right style="border: 1px solid black; border-width: 0px 0px 1px 1px"><?php echo $values["delivery"]['summ'];?></td>
		</tr>
<?php }?>
        <tr> 
          <td valign="top" colspan="5" rowspan="3" align="right" style="border: 1px solid black; border-width: 0px 0px 1px 1px"><b>Итого:<br>
            <img src="<?php echo BASE_PATH;?>adm/img/empty.gif" height=7>
			<?php echo (empty($values["tax"]) ? '' : '<br></b><b>В том числе '.$values["tax_name"]);?><br>

            <img src="<?php echo BASE_PATH;?>adm/img/empty.gif" height=7><br>
            Всего к оплате:</b></td>
          <td height="22" valign="top" align="right" style="border: 1px solid black; border-width: 0px 0px 1px 1px"><b><?php echo $values['summ'];?></b></td>
        </tr>
        <?php echo (empty($values["tax"]) ? '' : '<tr><td height="22" valign="top" align="right" style="border: 1px solid black; border-width: 0px 0px 1px 1px">'.$values["tax"].'</td></tr>');?>
        <tr> 
          <td height="22" valign="top" align="right" style="border: 1px solid black; border-width: 0px 0px 1px 1px"><b><?php echo $values['summ'];?></b></td>

        </tr>
      </table>
    </td>
  </tr>
  <tr> 
  <?php if($values["module_name"] == 'cart')
  {
    echo '<td height="49" colspan="2" valign="bottom">Всего наименований '.$values['count'].', на сумму:<br> 
      <b>'.$values['str_summ'].'</b></td>';
  }?>
  </tr>

  <tr valign="bottom"> 
    <td height="70" colspan="2" valign="bottom">
	<?php if(file_exists(ABSOLUTE_PATH.USERFILES.'/shop/non_cash.pechat.jpg'))
	{
		echo '<div style="position:absolute; width: 150px;height: 150px;left:250px; z-index: -1; ">
			<img src="'.BASE_PATH.USERFILES.'/shop/non_cash.pechat.jpg">
		</div>';
	}?>
  <br><br>
		Руководитель предприятия: ____________________________ (<?php echo (!empty($values["non_cash_director"]) ? $values["non_cash_director"] : '');?>)<br>
    Главный бухгалтер: ___________________________________ (<?php echo (!empty($values["non_cash_glbuh"]) ? $values["non_cash_glbuh"] : '');?>)
	</td>
  </tr>
  <tr> 
    <td height="60" colspan="2">&nbsp;</td>
  </tr>
</table>
</body>
</html>