<?php
/**
 * Подключение модуля «Посещаемость»
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
 * Visitors_inc
 */
class Visitors_inc extends Diafan
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
			case 'prepare':
				if(! isset($this->backend['prepare']))
				{
					Custom::inc('modules/visitors/inc/visitors.inc.prepare.php');
					$this->backend['prepare'] = new Visitors_inc_prepare($this->diafan);
				}
				$visitors = &$this->backend['prepare'];
				break;

			case 'counter':
				if(! isset($this->backend['counter']))
				{
					Custom::inc('modules/visitors/inc/visitors.inc.counter.php');
					$this->backend['counter'] = new Visitors_inc_counter($this->diafan);
				}
				$visitors = &$this->backend['counter'];
				break;

  			case 'yandex':
				if(! isset($this->backend['yandex']))
				{
					Custom::inc('modules/visitors/inc/visitors.inc.yandex.php');
					$this->backend['yandex'] = new Visitors_inc_yandex($this->diafan);
				}
				$visitors = &$this->backend['yandex'];
				break;

  			case 'google':
				if(! isset($this->backend['google']))
				{
					Custom::inc('modules/visitors/inc/visitors.inc.google.php');
					$this->backend['google'] = new Visitors_inc_google($this->diafan);
				}
				$visitors = &$this->backend['google'];
				break;

  			default:
				return false;
		}
		return call_user_func_array(array(&$visitors, $method), $args);
	}
}
