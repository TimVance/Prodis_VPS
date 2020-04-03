<?php
/**
 * Настройки платежной системы WebMoney для административного интерфейса
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

class Payment_webmoney_admin
{
	public $config;

	public function __construct()
	{
		$this->config = array(
			"name" => 'Webmoney',
			"params" => array(
				'wm_target' => 'Webmoney: кошелек',
				'wm_secret' => 'Webmoney: секретный ключ',
				'wm_test' => array('name' => 'Тестовый режим', 'type' => 'checkbox'),
			)
		);
	}
}