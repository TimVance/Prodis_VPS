<?php
/**
 * Подключение модуля «Посещаемость» для работы URL
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if ( ! defined('DIAFAN'))
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
 * Visitors_inc_prepare
 */
class Visitors_inc_prepare extends Diafan
{
	/**
	 * Обработка URL
	 *
	 * @return void
	 */
	public function rewrite()
	{
		if(isset($_GET["state"]))
		{// ответ от API Яндекс.Метрики и API Google Analytics
			parse_str($_GET["state"], $params);
			if(isset($params["rewrite"]))
			{
				if(isset($_GET["rewrite"])) unset($_GET["rewrite"]);
				if(isset($_GET["scope"])) unset($_GET["scope"]);
				unset($_GET["state"]);
				if(isset($_GET["code"]))
				{
					$_SESSION["visitors"]["api"]["code"] = $_GET["code"];
					unset($_GET["code"]);
				}
				$rewrite = $this->diafan->params_append('http'.(IS_HTTPS ? "s" : '').'://'.getenv("HTTP_HOST").$params["rewrite"], $_GET);
				$this->diafan->redirect($rewrite, 302);
			}
		}
	}
}

/**
 * Visitors_prepare_exception
 *
 * Исключение для работы URL
 */
class Visitors_prepare_exception extends Exception{}
