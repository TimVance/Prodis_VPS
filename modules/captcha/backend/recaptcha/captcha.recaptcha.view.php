<?php
/**
 * Шаблон reCAPTCHA
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

echo '<div class="captcha js_captcha">
	<div id="recaptcha_div_'.$result["modules"].'" data-sitekey="'.$this->diafan->configmodules('recaptcha_public_key', 'captcha').'"></div>
<div class="errors error_captcha"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>
</div>';

$this->diafan->_site->js_view[] = BASE_PATH.Custom::path('modules/captcha/js/captcha.get_recaptcha_.js');