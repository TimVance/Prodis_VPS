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

class Update_install extends Install
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = true;

	/**
	 * @var string название
	 */
	public $title = "Обновление DIAFAN.CMS";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "update_return",
			"comment" => "Точки возврата",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "current",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "текущая точка: 0 - нет, 1 - да",
				),
				array(
					"name" => "hash",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "уникальный хэш",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
				),
				array(
					"name" => "version",
					"type" => "VARCHAR(32) NOT NULL DEFAULT ''",
					"comment" => "версия сборки",
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
			"name" => "update",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Обновление CMS",
			"rewrite" => "update",
			"group_id" => 6,
			"sort" => 42,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/obnovleniya/",
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "hash",
			"module_name" => "update",
			"value" => "2064512dec4653c9376d005f030d8eb58c7ef1e9",
		),
	);

	/**
	 * Выполняет действия при установке модуля после основной установки
	 *
	 * @return void
	 */
	public function action_post()
	{
		if(! IS_DEMO)
		{
			$this->diafan->_update->first_return();
		}
	}
}
