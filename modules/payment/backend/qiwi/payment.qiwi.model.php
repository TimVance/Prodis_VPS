<?php
/**
 * Формирует данные для формы платежной системы QIWI
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

class Payment_qiwi_model extends Diafan
{
	/**
     * Формирует данные для формы платежной системы "QIWI"
     * 
     * @param array $params настройки платежной системы
     * @param array $pay данные о платеже
     * @return array
     */
	public function get($params, $pay)
	{
		if (! empty($_GET["order"]) && $_GET["order"] == 1)
		{
			$result["from_qiwi"] = true;
		}
		$result["text"]     = $pay['text'];
		$result["summ"]     = $pay['summ'];
		$result["desc"]     = $pay["desc"];
		$result["order_id"] = $pay["id"];
		$result["qiwi_id"]  = $params["qiwi_id"];
		return $result;
	}
}