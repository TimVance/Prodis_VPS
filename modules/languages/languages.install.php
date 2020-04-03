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

class Languages_install extends Install
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = true;

	/**
	 * @var string название
	 */
	public $title = "Языки сайта";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "languages",
			"comment" => "Языки сайта",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "shortname",
					"type" => "VARCHAR(10) NOT NULL DEFAULT ''",
					"comment" => "скоращенное название",
				),
				array(
					"name" => "base_admin",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "язык является основным для административной части: 0 - нет, 1 - да",
				),
				array(
					"name" => "base_site",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "язык является основным для пользовательской части: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "languages_translate",
			"comment" => "Перевод интерфейса",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
				),
				array(
					"name" => "text_translate",
					"type" => "TEXT",
					"comment" => "перевод",
				),
				array(
					"name" => "lang_id",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор языка из таблицы {languages}",
				),
				array(
					"name" => "module_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название модуля, для которого применим перевод",
				),
				array(
					"name" => "type",
					"type" => "ENUM('admin', 'site') NOT NULL DEFAULT 'admin'",
					"comment" => "часть сайта: admin - административная, site - пользовательская",
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
			"name" => "languages",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Языки сайта",
			"rewrite" => "languages",
			"group_id" => 5,
			"sort" => 36,
			"act" => true,
			"children" => array(
				array(
					"name" => "Языки сайта",
					"rewrite" => "languages",
					"act" => true,
				),
				array(
					"name" => "Перевод интерфейса",
					"rewrite" => "languages/translate",
					"act" => true,
				),
			)
		),
	);

	/**
	 * @var array SQL-запросы
	 */
	public $sql = array(
		"languages" => array(
			array(
				"id" => 1,
				"name" => 'ru',
				"shortname" => 'ru',
				"base_site" => 1,
				"base_admin" => 1,
			),
		)
	);

	/**
	 * Выполняет действия при установке модуля
	 *
	 * @return void
	 */
	protected function action()
	{
		if (count($this->langs) > 1)
		{
			$this->sql["languages"][] =
			array(
				"id" => 2,
				"name" => 'eng',
				"shortname" => 'eng',
			);
		}
	}

	/**
	 * Выполняет действия при установке модуля после основной установки
	 *
	 * @return void
	 */
	public function action_post()
	{
		if(count($this->langs) > 1 && file_exists(ABSOLUTE_PATH.'langs/eng'))
		{
			$this->diafan->_languages->import(ABSOLUTE_PATH.'langs/eng', 2);
		}
	}
}