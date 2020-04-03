<?php
/**
 * Шаблон платежа через систему QIWI
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

if(! empty($result["from_qiwi"]))
{
	echo '<p>'.$this->diafan->_('Подтвердить оплату', false).':</p>';
	echo '<form id="rpay'.$result["order_id"].'" name="pay'.$result["order_id"].'" method="POST" action="">
	<input type="hidden" name="qiwi_id" value="'.$result["order_id"].'"> 
	<input type="submit" name="Submit" value="'.$this->diafan->_('Оплатить', false).'">
	</form>';
}
else
{
	echo $result["text"];
	?>
	<form name="pay" method="POST" target="_blank" action="http://w.qiwi.ru/setInetBill_utf.do">
		<?php echo $this->diafan->_('Введите номер Вашего телефона, зарегистрированного в системе Qiwi, без 8, 10 цифр, например, 9061231212.', false);?>:<br>
		<input type="text" name="to" value="" size="30">  &nbsp; 
		<input type="hidden" name="summ" value="<?php echo $result["summ"]; ?>">
		<input type="hidden" name="com" value=" <?php echo $result["desc"]; ?>">
		<input type="hidden" name="txn_id" value="<?php echo $result["order_id"]; ?>">
		<input type="hidden" name="from" value="<?php echo $result["qiwi_id"];?>">
		<p><input type="submit" value="<?php echo $this->diafan->_('Оплатить', false);?>"></p>
	</form>
	<?php
}