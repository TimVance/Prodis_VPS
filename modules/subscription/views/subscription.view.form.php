<?php
/**
 * Шаблон формы подписки на рассылки
 * 
 * Шаблонный тег <insert name="show_form" module="subscription" [template="шаблон"]>:
 * блок вывода формы подписки на рассылки
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

echo '
<form method="POST" enctype="multipart/form-data" action="" class="subscription ajax">
<input type="hidden" name="module" value="subscription">
<input type="hidden" name="action" value="add">
<input type="hidden" name="form_tag" value="'.$result["form_tag"].'">
<i class="fa fa-envelope"></i>
<h3>'.$this->diafan->_('Подписаться на рассылку').'</h3>
<input type="email" name="mail">
<input type="submit" class="button white" value="'.$this->diafan->_('Подписаться', false).'">
<div class="errors error_mail"'.($result["error_mail"] ? '>'.$result["error_mail"] : ' style="display:none">').'</div>
<div class="errors error"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>';
echo $result["captcha"];
echo '
</form>';