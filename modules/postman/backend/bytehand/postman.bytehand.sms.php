<?php
/**
 * Работа с SMS-оператором «Byte Hand»
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
 * Postman_bytehand_sms
 */
class Postman_bytehand_sms extends Diafan
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
	public function send($text, $to, &$error_output, &$trace_output)
	{
		$opts = array(
			'http' => array(
				'ignore_errors'=> TRUE,
			)
		);
		$context = stream_context_create($opts);
		$fp = @fsockopen('bytehand.com', 3800, $errno, $errstr);
		if($fp)
		{
			$sms_id = $this->diafan->configmodules("bytehand_id", 'postman');
			$sms_key = $this->diafan->configmodules("bytehand_key", 'postman');
			$sms_signature = $this->diafan->configmodules("bytehand_signature", 'postman');
			$result = file_get_contents("http://bytehand.com:3800/send?id=".urlencode($sms_id)."&key=".urlencode($sms_key)."&to=".$to."&from=".urlencode($sms_signature)."&text=".$text, FALSE, $context);
		}
		else
		{
			if($errno == 0)
			{
				$error_output = 'ERROR: socket initialization';
			}
			else
			{
				$error_output = 'ERROR '.$errno.': '.$errstr;
			}
			$trace_output = 'Socket is not initialized';
			return false;
		}

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

		if (! is_object($result) || ! isset($result->status) || ! isset($result->description))
		{
			$error_output = 'ERROR: Bad response';
			return false;
		}
		if($result->status != 0 || $http_code != 200)
		{
			$error_output = 'ERROR '.$result->status.': '.$result->description;
			return false;
		}
		else $error_output = '';
		
		return true;
	}
}