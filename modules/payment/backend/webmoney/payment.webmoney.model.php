<?php
/**
 * Формирует данные для формы платежной системы WebMoney
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

class Payment_webmoney_model extends Diafan
{
	/**
     * Формирует данные для формы платежной системы "WebMoney"
     * 
     * @param array $params настройки платежной системы
     * @param array $pay данные о платеже
     * @return array
     */
	public function get($params, $pay)
	{
		$result["text"]      = $pay["text"];
		$result["desc"]      = $pay["desc"];
		$result["desc_base64"] = base64_encode($pay["desc"]);
		$result["wm_target"] = $params["wm_target"];
		
		$result["summ"]      = $pay["summ"];
		$result["order_id"]  = $pay["id"];

		$result["rnd"]       = strtoupper(substr(md5(uniqid(microtime(), 1)).getmypid(), 1, 8));

		// режим тестирования:
		//  0 или не отсутствует: Для всех тестовых платежей сервис будет имитировать успешное выполнение;
		//  1: Для всех тестовых платежей сервис будет имитировать выполнение с ошибкой (платеж не выполнен);
		//  2: Около 80% запросов на платеж будут выполнены успешно, а 20% - не выполнены.
		if(!  empty($params["wm_test"]))
		{
			$result["LMI_SIM_MODE"] = 2;
		}
		return $result;
	}
}