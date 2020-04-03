<?php
/**
 * Шаблон формы стандартной капчи
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

$codeint = rand(1111, 9999);

echo '<div class="block captcha">';

echo '
<img src="'.BASE_PATH.(IS_ADMIN ? ADMIN_FOLDER.'/' : '').'captcha/get/kcaptcha/'.$result["modules"].$codeint.'" width="159" height="80" class="code_img captcha-image">
<input type="hidden" name="captchaint" value="'.$codeint.'">
<input type="hidden" name="captcha_update" value="">

<span class="input-title">'.$this->diafan->_('Введите код с картинки').':</span>
<input type="text" name="captcha" value="" autocomplete="off">
<br>
<div class="js_captcha_update captcha_update"><a href="javascript:void(0)"><i class="fa fa-refresh">&nbsp;</i></a></div>';
echo '</div>';

echo '<div class="errors error_captcha"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>';