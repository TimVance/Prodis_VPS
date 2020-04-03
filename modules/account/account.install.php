<?php
/**
 * Установка модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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

class Account_install extends Install
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = true;

	/**
	 * @var string название
	 */
	public $title = "Кабинет пользователя";

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "account",
			"admin" => true,
			"site" => false,
			"site_page" => false,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Кабинет пользователя",
			"rewrite" => "account",
			"icon_name" => "user",
			"group_id" => 6,
			"sort" => 31,
			"act" => true,
			"children" => array(
				array(
					"name" => "Персональная страница",
					"rewrite" => "account",
					"act" => true,
				),
				array(
					"name" => "Служба поддержки",
					"rewrite" => "account/support",
					"act" => true,
				),
				array(
					"name" => "Поиск веб-мастера",
					"rewrite" => "account/projects",
					"act" => true,
				),
			),
		),
	);
}
