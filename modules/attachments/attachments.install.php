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

class Attachments_install extends Install
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = true;

	/**
	 * @var string название
	 */
	public $title = "Прикрепленные файлы";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "attachments",
			"comment" => "Прикрепленные файлы",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название файла",
				),
				array(
					"name" => "module_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название модуля",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор элемента модуля",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор поля или характеристики с типом «файлы»",
				),
				array(
					"name" => "extension",
					"type" => "VARCHAR(255) NOT NULL DEFAULT ''",
					"comment" => "расширение файла",
				),
				array(
					"name" => "size",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "размер файла",
				),
				array(
					"name" => "is_image",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "это изображение: 0 - нет, 1 - да",
				),
				array(
					"name" => "access_admin",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "доступ только для администратора: 0 - нет, 1 - да",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY `element_id` (`element_id`)",
				"KEY `module_name` (`module_name`(2))",
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "attachments",
			"admin" => true,
			"site" => true,
			"title" => "Прикрепленные файлы",
		),
	);
}