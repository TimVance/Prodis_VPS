<?php
/**
 * Работа с SMS-оператором «SMSC»
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
 * Postman_smsc_sms
 */
class Postman_smsc_sms extends Diafan
{
	/**
	 * Отправляет SMS
	 * 
	 * @param string $text текст SMS
	 * @param string $to номер получателя
	 * @param string $error_output вывод ошибки
	 * @param string $trace_output вывод трассировки
	 * @return boolean
	 */
	public function send($text, $to, &$error_output = '', &$trace_output = '')
	{
		$opts = array(
			'http' => array(
				'ignore_errors'=> TRUE,
			)
		);
		$context = stream_context_create($opts);

		$sms_login = $this->diafan->configmodules("smsc_login", 'postman');
		$sms_password = $this->diafan->configmodules("smsc_psw", 'postman');
		$sms_signature = $this->diafan->configmodules("smsc_signature", 'postman');
		$result = file_get_contents("https://smsc.ru/sys/send.php?login=".urlencode($sms_login)."&psw=".urlencode($sms_password)."&phones=".$to."&sender=".urlencode($sms_signature)."&mes=".$text."&charset=utf-8&fmt=3", FALSE, $context);
		

		$trace_output = implode(PHP_EOL, $http_response_header)."\n\n".$result;

		$http_code = false;
		if(! empty($http_response_header[0]))
		{
			preg_match('/\d{3}/', $http_response_header[0], $matches);
			if(! empty($matches[0]))
			{
				$http_code = $matches[0];
			}
		}

		$result = json_decode($result);

		if (! is_object($result))
		{
			$error_output = 'ERROR: Bad response';
			return false;
		}
		if(isset($result->error) || $http_code != 200)
		{
			$error_output = 'ERROR '.$result->error.(isset($result->error_code) ? ': '.$result->error_code : '');
			return false;
		}
		else $error_output = '';
		return true;
	}
}