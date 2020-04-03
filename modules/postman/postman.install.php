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

class Postman_install extends Install
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = true;

	/**
	 * @var string название
	 */
	public $title = "Уведомления";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "postman",
			"comment" => "Уведомления",
			"fields" => array(
				array(
					"name" => "master_id",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "главный идентификатор в формате UNIXTIME",
				),
				array(
					"name" => "slave_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "вторичный идентификатор в интервале master_id",
				),
				array(
					"name" => "id",
					"type" => "TEXT",
					"comment" => "объединенный идентификатор",
				),
				array(
					"name" => "type",
					"type" => "ENUM('mail', 'sms') NOT NULL DEFAULT 'mail'",
					"comment" => "тип уведомления: mail - электронные письма, sms - короткие сообщения",
				),
				array(
					"name" => "recipient",
					"type" => "TEXT",
					"comment" => "получатель/получатели",
				),
				array(
					"name" => "subject",
					"type" => "TEXT",
					"comment" => "тема письма",
				),
				array(
					"name" => "body",
					"type" => "TEXT",
					"comment" => "содержание письма",
				),
				array(
					"name" => "from",
					"type" => "TEXT",
					"comment" => "адрес отправителя",
				),
				array(
					"name" => "auto",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '1'",
					"comment" => "метод отправки уведомления: 0 - ручной, 1 - автоматический",
				),
				array(
					"name" => "timeedit",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
				array(
					"name" => "timesent",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время отправки уведомления в формате UNIXTIME",
				),
				array(
					"name" => "status",
					"type" => "ENUM('0', '1', '2') NOT NULL DEFAULT '0'",
					"comment" => "отчет об отправке уведомления: 0 - не отправлено, 1 - отправлено, 2 - ошибка отправления",
				),
				array(
					"name" => "error",
					"type" => "TEXT",
					"comment" => "отчет об ошибке отправления",
				),
				array(
					"name" => "trace",
					"type" => "TEXT",
					"comment" => "трассировка отправления",
				),
			),
			"keys" => array(
				"PRIMARY KEY (`master_id`, `slave_id`)",
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "postman",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Уведомления",
			"rewrite" => "postman",
			"group_id" => 3,
			"sort" => 40,
			"act" => true,
			"children" => array(
				array(
					"name" => "Список отправлений",
					"rewrite" => "postman",
					"sort" => "1",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "postman/config",
				),
			),
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "mail_defer",
			"value" => "1",
		),
		array(
			"name" => "sms_defer",
			"value" => "1",
		),
		array(
			"name" => "del_after_send",
			"value" => "1",
		),
		array(
			"name" => "auto_send",
			"value" => "1",
		),
	);

	/**
	 * Выполняет действия при установке модуля после основной установки
	 *
	 * @return void
	 */
	public function action_post()
	{
		$email_config = '';
		if(defined('IS_DEMO') && IS_DEMO)
		{
			$email_config = 'demo@'.getenv('HTTP_HOST');
		}
		elseif(! empty($_SESSION["install_admin_mail"]))
		{
			include_once (ABSOLUTE_PATH.'includes/validate.php');
			$email_config = (! Validate::mail($_SESSION["install_admin_mail"]) ? $this->diafan->filter($_SESSION, 'string', "install_admin_mail") : '');
		}
		if($email_config)
		{
			$this->diafan->configmodules("email", 'postman', 0, 0, $email_config);
		}
	}
}
