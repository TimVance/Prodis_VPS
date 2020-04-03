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

class Messages_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Личные сообщения";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "messages",
			"comment" => "Личные сообщения пользователей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "author",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}, написавшего сообщение",
				),
				array(
					"name" => "to_user",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users} получателя сообщения",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "текст сообщения",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "readed",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "сообщение прочитано: 0 - нет, 1 - да",
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
		array(
			"name" => "messages_user",
			"comment" => "Контакты пользователей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users} кто инициировал контакт",
				),
				array(
					"name" => "contact_user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users} с кем создан контакт",
				),
				array(
					"name" => "date_update",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата последнего сообщения",
				),
				array(
					"name" => "readed",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "все сообщения прочитаны: 0 - нет, 1 - да",
				),
				array(
					"name" => "count_message",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество сообщений",
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
			"name" => "messages",
			"site" => true,
			"site_page" => true,
		),
	);

	/**
	 * @var array страницы сайта
	 */
	public $site = array(
		array(
			"parent_id" => 3,
			"name" => array('Сообщения', 'Messages'),
			"act" => true,
			"module_name" => "messages",
			"map_no_show" => true,
			"noindex" => true,
			"search_no_show" => true,
			"rewrite" => "messages",
		),
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		'messages' => array(
			array(
				'author' => 2,
				'to_user' => 1,
				'readed' => 1,
				'text' => array('Привет, как дела?')
			),
			array(
				'author' => 1,
				'to_user' => 2,
				'readed' => 1,
				'text' => array('Привет! Все в пордяке. Как ты?')
			),
			array(
				'author' => 2,
				'to_user' => 1,
				'text' => array('Супер круто!'),
				'created' => 'now',
			),
		),
		'messages_user' => array(
			array(
				'user_id' => 2,
				'contact_user_id' => 1,
				'readed' => 1,
				'date_update' => 'now',
			),
			array(
				'user_id' => 1,
				'contact_user_id' => 2,
				'readed' => 0,
				'date_update' => 'now',
			),
		),	
	);
}