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

class Admin_install extends Install
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = true;

	/**
	 * @var string название
	 */
	public $title = "Страницы админски";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "admin",
			"comment" => "Страницы в админки",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "parent_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор родителя из таблицы {admin}",
				),
				array(
					"name" => "count_children",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество вложенных страниц",
				),
				array(
					"name" => "group_id",
					"type" => "ENUM( '1', '2', '3', '4', '5', '6', '7') NOT NULL DEFAULT '1'",
					"comment" => "группа",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "rewrite",
					"type" => "VARCHAR(30) NOT NULL DEFAULT ''",
					"comment" => "псевдоссылка",
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать в меню: 0 - нет, 1 - да",
				),
				array(
					"name" => "add",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "ссылка на добавление элемента в быстром меню: 0 - нет, 1 - да",
				),
				array(
					"name" => "add_name",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "текст ссылки на добавление элемента в быстром меню",
				),
				array(
					"name" => "sort",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "порядковый номер для сортировки",
				),
				array(
					"name" => "docs",
					"type" => "VARCHAR( 255 ) NOT NULL DEFAULT ''",
					"comment" => "ссылка на раздел в документации",
				),
				array(
					"name" => "icon_name",
					"type" => "VARCHAR(30) NOT NULL DEFAULT ''",
					"comment" => "иконка модуля для административной части сайта",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY parent_id (parent_id)",
			),
		),
		array(
			"name" => "admin_parents",
			"comment" => "Родительские связи страниц админки",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы из таблицы {admin}",
				),
				array(
					"name" => "parent_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы родителя из таблицы {admin}",
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
			"name" => "admin",
			"admin" => true,
			"title" => "Страницы админки",
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Страницы админки",
			"rewrite" => "admin",
			"group_id" => 1,
			"sort" => 1,
			"act" => false,
		),
	);
}
