<?php
/**
 * Подключение модуля «Уведомления» для работы с отложенной отправкой сообщений
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
 * Postman_inc_defer
 */
class Postman_inc_defer extends Diafan
{
	const URL = 'postman/send/';

	/**
	 * Инициирует отложенную отправку уведомлений
	 *
	 * @return void
	 */
	public function init()
	{
		if($this->diafan->configmodules('auto_send', 'postman') && $this->diafan->_postman->db_count_sent() > 0)
		{
			$this->diafan->fast_request(BASE_PATH.self::URL);
		}
	}
}

/**
 * Postman_defer_exception
 *
 * Исключение для почтовых отправлений
 */
class Postman_defer_exception extends Exception{}
