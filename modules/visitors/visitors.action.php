<?php
/**
 * Обработка запроса для модуля «Посещаемость»
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

class Visitors_action extends Action
{
	/**
	 * Обрабатывает полученные данные из формы
	 *
	 * @return void
	 */
	public function valid()
	{
		$this->diafan->_visitors->counter_init(true);
		$this->result["result"] = 'success';
	}
}
