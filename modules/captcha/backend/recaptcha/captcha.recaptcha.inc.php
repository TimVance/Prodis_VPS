<?php
/**
 * Подключение для работы с капчей «reCAPTCHA»
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

/**
 * Captcha_recaptcha_inc
 */
class Captcha_recaptcha_inc extends Diafan
{
	/**
	 * Выводит капчу
	 * 
	 * @param string $modules метка капчи
	 * @param string $error ошибка ввода кода, если запрос передан не через Ajax
	 * @param boolean $is_update капча генерируется для обновления
	 * @return string
	 */
	public function get($modules, $error, $is_update)
	{
		if($is_update)
		{
			return "recaptcha";
		}
		$result["public_key"] = $this->diafan->configmodules('recaptcha_public_key', 'captcha');
		$result["error"] = $error;
		$result["modules"] = $modules;
		ob_start();
		include(Custom::path('modules/captcha/backend/recaptcha/captcha.recaptcha.view.php'));
		$data = ob_get_contents();
		ob_end_clean();
		if (! empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest'
			// для IE
			|| ! empty($_POST["ajax"]))
		{
			$this->diafan->_site->js_view = array();
			$data .= '
			<script type="text/javascript">
				recaptcha["recaptcha_div_'.$result["modules"].'"] = grecaptcha.render("recaptcha_div_'.$result["modules"].'", {
				  "sitekey" : "'.$this->diafan->configmodules('recaptcha_public_key', 'captcha').'"
				});
			</script>';
		}
		else
		{
			$this->diafan->_site->js_view[] = 'https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit';
		}
		return $data;
	}

	/**
	 * Проверяет правильность ввода капчи
	 * 
	 * @return string|boolean false
	 */
	public function check($modules)
	{
		if(empty($_POST['g-recaptcha-response']))
		{
			return $this->diafan->_('Вы не прошли проверку.', false);
		}

		$url = 'https://www.google.com/recaptcha/api/siteverify';
	
		$secret = urlencode($this->diafan->configmodules('recaptcha_private_key', 'captcha'));
		$recaptcha = urlencode($_POST['g-recaptcha-response']);
		$ip = urlencode($_SERVER['REMOTE_ADDR']);
		
		$url_data = $url.'?secret='.$secret.'&response='.$recaptcha.'&remoteip='.$ip;
		$curl = curl_init();
		
		curl_setopt($curl,CURLOPT_URL,$url_data);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
		
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		
		
		$res = curl_exec($curl);
		curl_close($curl);
		
		$res = json_decode($res);
		
		if(! $res->success)
		{
			return $this->diafan->_('Неправильно введен защитный код.', false);
		}
		return false;
	}
}