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

class Custom_install extends Install
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = true;

	/**
	 * @var string название
	 */
	public $title = "Темы и дизайн";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "custom",
			"comment" => "Темы",
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
					"comment" => "текущая тема: 0 - нет, 1 - да",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
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
			"name" => "custom",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Темы и дизайн",
			"rewrite" => "custom",
			"group_id" => 3,
			"sort" => 30,
			"act" => true,
		),
	);

	/**
	 * Выполняет действия при установке модуля после основной установки
	 *
	 * @return void
	 */
	public function action_post()
	{
		if(Custom::name() == 'my')
		{
			DB::query("INSERT INTO {custom} (created, name) VALUES (%d, 'my')", time());

			Custom::inc('includes/config.php');
			$config = new Config();
			$config->save(array('CUSTOM' => Custom::name()), $this->diafan->_languages->all);

			if(! file_exists(ABSOLUTE_PATH.'custom/my/install.sql'))
				return;
	
			Custom::inc("adm/includes/frame.php");
			Custom::inc("modules/service/admin/service.admin.db.php");
			$obj = new Service_admin_db($this->diafan);
			$obj->import_query(ABSOLUTE_PATH.'custom/my/install.sql', false);
		}
	}
}