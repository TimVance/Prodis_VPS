<?php
/**
 * Шаблон платежа через систему WebMoney
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

echo $result["text"];
echo '<form id="pay" name="pay" method="POST" action="https://merchant.webmoney.ru/lmi/payment.asp">
<b>'.$this->diafan->_('Платеж на %d WMR.', false, $result["summ"]).'</b> &nbsp;
<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="'.$result['summ'].'">
<input type="hidden" name="LMI_PAYMENT_DESC" value="'.$result['desc'].'">
<input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="'.$result['desc_base64'].'">
<input type="hidden" name="LMI_PAYMENT_NO" value="'.$result["order_id"].'">
<input type="hidden" name="LMI_PAYEE_PURSE" value="'.$result["wm_target"].'">';
if (isset($result["LMI_SIM_MODE"]))
{
	echo '<input type="hidden" name="LMI_SIM_MODE" value="'.$result["LMI_SIM_MODE"].'">';
}
echo '<input type="hidden" name="RND" value="'.$result["rnd"].'">
<p><input type="submit" value="'.$this->diafan->_('Оплатить', false).'"></p>
</form>';