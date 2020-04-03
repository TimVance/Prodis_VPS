<?php
/**
 * Подключение модуля «Уведомления»
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
 * Postman_inc
 */
class Postman_inc extends Diafan
{
	/**
	 * @var object бэкэнд
	 */
	private $backend;

	/**
	 * Подключает расширения для подключения
	 *
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		list($prefix, $method) = explode('_', $name, 2);
		switch($prefix)
		{
			case 'db':
				if(! isset($this->backend['db']))
				{
					Custom::inc('modules/postman/inc/postman.inc.db.php');
					$this->backend['db'] = new Postman_inc_db($this->diafan);
				}
				$postman = &$this->backend['db'];
				break;

			case 'message':
				if(! isset($this->backend['message']))
				{
					Custom::inc('modules/postman/inc/postman.inc.message.php');
					$this->backend['message'] = new Postman_inc_message($this->diafan);
				}
				$postman = &$this->backend['message'];
				break;

			case 'defer':
				if(! isset($this->backend['defer']))
				{
					Custom::inc('modules/postman/inc/postman.inc.defer.php');
					$this->backend['defer'] = new Postman_inc_defer($this->diafan);
				}
				$postman = &$this->backend['defer'];
				break;

  			default:
				return false;
		}
		return call_user_func_array(array(&$postman, $method), $args);
	}
}
