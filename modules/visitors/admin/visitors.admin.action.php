<?php
/**
 * Обработка POST-запросов в административной части модуля
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

class Visitors_admin_action extends Action_admin
{
	/**
	 * @var array действия, для которых не нужно проверять хэш пользователя
	 */
	protected $hash_no_check = array('valid');
	
	/**
	 * Вызывает обработку POST-запросов
	 * 
	 * @return void
	 */
	public function init()
	{
		if (! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'valid':
					$this->valid();
					break;
			}
		}
	}

	/**
	 * Обрабатывает полученные данные из формы
	 *
	 * @return void
	 */
	public function valid()
	{
		$this->diafan->_visitors->counter_init($_POST, true);
		$this->result["result"] = 'success';
	}
}
