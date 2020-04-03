<?php
/**
 * Шаблон формы пополнения баланса пользователя
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

echo '<p>'.$this->diafan->_('Сумма на балансе').': '.$result['balance']["summ"].' '.$result['balance']["currency"].'</p>';

if(empty($result["payments"]))
{
	return;
}

echo '<form action="" method="POST" class="balance_form ajax">
<input type="hidden" name="module" value="balance">
<input type="hidden" name="form_tag" value="'.$result["form_tag"].'">
<input type="hidden" name="action" value="recharge">';

echo '<div class="infofield">'.$this->diafan->_('Выберите способ пополнения баланса').':</div>';

echo $this->get('list', 'payment', $result["payments"]);

echo '<p>
'.$this->diafan->_('Сумма').':

<input type="number" min="0" value="0" name="summ" value="0">

<input type="submit" value="'.$this->diafan->_('Пополнить', false).'">
</p>';
echo '<div class="errors error_summ"'.($result["error_summ"] ? '>'.$result["error_summ"] : ' style="display:none">').'</div>';

echo '</form>';
