<?php
/**
 * Настройки платежной системы QIWI для административного интерфейса
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

class Payment_qiwi_admin
{
	public $config;

	public function __construct()
	{
		$this->config = array(
			"name" => 'QIWI',
			"params" => array(
				'qiwi_id' => 'Номер терминала',
				'qiwi_password' =>  'Пароль'
			)
		);
	}
}