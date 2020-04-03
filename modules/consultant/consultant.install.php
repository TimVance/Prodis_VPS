<?php
/**
 * Установка модуля
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

class Consultant_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "On-line консультант";

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "consultant",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "On-line консультант",
			"rewrite" => "consultant",
			"group_id" => 3,
			"sort" => 27,
			"act" => true
		),
	);

	/**
    * @var array настройки
    */
    public $config = array(
        array(
            "name" => "backend",
            "value" => "jivosite",
        ),        
    );
}