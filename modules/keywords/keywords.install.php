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

class Keywords_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Перелинковка";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "keywords",
			"comment" => "Ключевые слова для перелинковки",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "ключевое слово",
				),
				array(
					"name" => "link",
					"type" => "TEXT",
					"comment" => "ссылка",
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "keywords",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Перелинковка",
			"rewrite" => "keywords",
			"group_id" => 3,
			"sort" => 28,
			"act" => true,
			"children" => array(
				array(
					"name" => "Ключевые слова",
					"rewrite" => "keywords",
					"act" => true,
				),
				array(
					"name" => "Импорт/экспорт",
					"rewrite" => "keywords/importexport",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "keywords/config",
				),
			)
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "max",
			"value" => 2,
		),
		array(
			"name" => "keywords",
			"module_name" => "site",
			"value" => "1",
		),
		array(
			"name" => "keywords",
			"module_name" => "ab",
			"value" => "1",
			"check_module" => true,
		),
		array(
			"name" => "keywords",
			"module_name" => "clauses",
			"value" => "1",
			"check_module" => true,
		),
		array(
			"name" => "keywords",
			"module_name" => "faq",
			"value" => "1",
			"check_module" => true,
		),
		array(
			"name" => "keywords",
			"module_name" => "files",
			"value" => "1",
			"check_module" => true,
		),
		array(
			"name" => "keywords",
			"module_name" => "news",
			"value" => "1",
			"check_module" => true,
		),
		array(
			"name" => "keywords",
			"module_name" => "photo",
			"value" => "1",
			"check_module" => true,
		),
		array(
			"name" => "keywords",
			"module_name" => "shop",
			"value" => "1",
			"check_module" => true,
		),
	);	
}