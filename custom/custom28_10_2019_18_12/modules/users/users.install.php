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

class Users_install extends Install
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = true;

	/**
	 * @var string название
	 */
	public $title = "Пользователи";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "users",
			"comment" => "Пользователи",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(60) NOT NULL DEFAULT ''",
					"comment" => "логин",
				),
				array(
					"name" => "password",
					"type" => "VARCHAR(32) NOT NULL DEFAULT ''",
					"comment" => "пароль в зашифрованном виде",
				),
				array(
					"name" => "mail",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "e-mail",
				),
				array(
					"name" => "phone",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "телефон",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата регистрации",
				),
				array(
					"name" => "fio",
					"type" => "VARCHAR(255) NOT NULL DEFAULT ''",
					"comment" => "ФИО",
				),
				array(
					"name" => "role_id",
					"type" => "TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор типа пользователя из таблицы {users_role}",
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "активен на сайте: 0 - нет, 1 - да",
				),
				array(
					"name" => "htmleditor",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "использовать визуальный редактор: 0 - нет, 1 - да",
				),
				array(
					"name" => "copy_files",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "cохранять картинки с внешних сайтов, при вставке контента в визуальный редактор: 0 - нет, 1 - да",
				),
				array(
					"name" => "useradmin",
					"type" => "ENUM('0', '1', '2') NOT NULL DEFAULT '0'",
					"comment" => "панель быстрого редактирования: 0 - отключена, 1 - включена, 2 - только панель без режима редактирования",
				),
				array(
					"name" => "start_admin",
					"type" => "VARCHAR(30) NOT NULL DEFAULT ''",
					"comment" => "стартовая страница административной части",
				),
				array(
					"name" => "lang_id",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор языка сайта из таблицы {languages}",
				),
				array(
					"name" => "admin_nastr",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "колечество элементов на странице в административной части",
				),
				array(
					"name" => "identity",
					"type" => "VARCHAR(255) NOT NULL DEFAULT ''",
					"comment" => "URL на страницу в соц. сети",
				),
				array(
					"name" => "config",
					"type" => "TEXT",
					"comment" => "Настройки пользователя",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY `name` (`name`(1))",
			),
		),
		array(
			"name" => "users_actlink",
			"comment" => "Код активации аккаунта",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "link",
					"type" => "VARCHAR(255) NOT NULL DEFAULT ''",
					"comment" => "код активации",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '1'",
					"comment" => "количество неудачных попыток",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "users_role",
			"comment" => "Типы пользователей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "registration",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "задавать при регистрации: 0 - нет, 1 - да",
				),
				array(
					"name" => "only_self",
					"type" => " ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "видеть только свои материалы: 0 - нет, 1 - да",
				),
                array(
                    "name" => "sort",
                    "type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
                    "comment" => "подрядковый номер для сортировки",
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
			"name" => "users_role_perm",
			"comment" => "Права типов пользователей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "role_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор типа пользователя из таблицы {users_role}",
				),
				array(
					"name" => "perm",
					"type" => "TEXT",
					"comment" => "права на действия: all, init, edit, del",
				),
				array(
					"name" => "rewrite",
					"type" => "TEXT",
					"comment" => "тег доступа (например, название модуля)",
				),
				array(
					"name" => "type",
					"type" => "ENUM('site','admin') NOT NULL DEFAULT 'admin'",
					"comment" => "часть сайта: site - пользовательская, admin - административная",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "users_param",
			"comment" => "Дополнительные поля с данными о пользователях",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "type",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "тип",
				),
				array(
					"name" => "show_in_page",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "выводить на странице пользователя: 0 - нет, 1 - да",
				),
				array(
					"name" => "show_in_form_auth",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "выводить в форме редактирования данных для авторизованных пользователей: 0 - нет, 1 - да",
				),
				array(
					"name" => "show_in_form_no_auth",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "выводить в форме регистрации: 0 - нет, 1 - да",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "required",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "обязательно для заполнения: 0 - нет, 1 - да",
				),
				array(
					"name" => "config",
					"type" => "TEXT",
					"comment" => "серилизованные данные о настройках поля",
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
			"name" => "users_param_element",
			"comment" => "Значения дополнительных полей с данными о пользователях",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "value",
					"type" => "TEXT",
					"comment" => "значение",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор поля из таблицы {users_param}",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
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
			"name" => "users_param_select",
			"comment" => "Варианты значений дополнительных полей с данными о пользователях для типа список",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор поля из таблицы {users_param}",
				),
				array(
					"name" => "value",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "значение для типа характеристики «галочка»: 0 - нет, 1 - да",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY param_id (`param_id`)",
			),
		),
		array(
			"name" => "users_param_role_rel",
			"comment" => "Связи дополнительных полей с данными о пользователях и типов пользователей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор поля из таблицы {users_param}",
				),
				array(
					"name" => "role_id",
					"type" => "TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор типа пользователя из таблицы {users_role}",
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
			"name" => "users_token",
			"comment" => "Электронные ключи пользователей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "element_type",
					"type" => "VARCHAR(10) NOT NULL DEFAULT ''",
					"comment" => "тип ключа",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "token",
					"type" => "CHAR(32) NOT NULL DEFAULT ''",
					"comment" => "электронный ключ",
				),
				array(
					"name" => "date_start",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата начала действия электронного ключа",
				),
				array(
					"name" => "date_finish",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата окончания действия электронного ключа",
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
			"name" => "users",
			"admin" => true,
			"site" => true,
		),
		array(
			"name" => "userpage",
			"site" => true,
			"site_page" => true,
			"title" => "Страница пользователя",
		),
		array(
			"name" => "usersettings",
			"site" => true,
			"site_page" => true,
			"title" => "Настройки аккаунта",
		),
		array(
			"name" => "registration",
			"site" => true,
			"site_page" => true,
			"title" => "Регистрация",
		),
		array(
			"name" => "reminding",
			"site" => true,
			"site_page" => true,
			"title" => "Восстановление доступа",
		),
	);

	/**
	 * @var array страницы сайта
	 */
	public $site = array(
		array(
			"id" => 3,
			"sort" => 5,
			"name" => array('Страница пользователя', 'User page'),
			"act" => true,
			"module_name" => "userpage",
			"map_no_show" => true,
			"noindex" => true,
			"search_no_show" => true,
			"rewrite" => "user",
			"children" => array(
				array(
					"name" => array('Настройки аккаунта', 'Settings'),
					"act" => true,
					"module_name" => "usersettings",
					"map_no_show" => true,
					"noindex" => true,
					"search_no_show" => true,
					"rewrite" => "settings",
				),
			),
		),
		array(
			"name" => array('Регистрация', 'Registration'),
			"act" => true,
			"module_name" => "registration",
			"map_no_show" => true,
			"noindex" => true,
			"search_no_show" => true,
			"rewrite" => "registration",
		),
		array(
			"name" => array('Восстановление доступа', 'Restore access to your account'),
			"act" => true,
			"module_name" => "reminding",
			"map_no_show" => true,
			"noindex" => true,
			"search_no_show" => true,
			"rewrite" => "reminding",
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Пользователи сайта",
			"rewrite" => "users",
			"group_id" => 2,
			"sort" => 16,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/polzovateli/",
			"children" => array(
				array(
					"name" => "Пользователи",
					"rewrite" => "users",
					"act" => true,
				),
				array(
					"name" => "Права доступа",
					"rewrite" => "users/role",
					"act" => true,
				),
				array(
					"name" => "Конструктор формы регистрации",
					"rewrite" => "users/param",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "users/config",
				),
			)
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "captcha",
			"value" => 'a:1:{i:0;s:1:"0";}',
		),
		array(
			"name" => "sendmailadmin",
			"value" => "1",
		),
		array(
			"name" => "act",
			"value" => "1",
		),
		array(
			"name" => "subject_admin",
			"value" => "%title (%url). Новый пользователь",
		),
		array(
			"name" => "message_admin",
			"value" => "Здравствуйте, администратор сайта %title (%url)!<br>На сайте появился новый пользователь: %fio (%login), %email.",
		),
		array(
			"name" => "mes",
			"value" => array(
				"Вы удачно зарегистрированы. Для активации аккаунта пройдите по ссылке, высланной на ваш e-mail.",
				"Congratulations! You have successful registered. In order to activate your account please click the link send to your e-mail.",
			),
		),
		array(
			"name" => "subject",
			"value" => array(
				'Вы зарегистрированы на сайте %title (%url)',
				'You registered on web site %title (%url).',
			),
		),
		array(
			"name" => "message",
			"value" => array(
				"Здравствуйте, %fio!<br>Вы зарегистрированы на сайте %title (%url).<br>Логин: %login<br>Пароль: %password<br>Для активации аккаунта пройдите по <a href=\"%actlink\">ссылке</a>.<br>Ссылка действует в течении суток.",
				"Hello, %fio!<br>You registered on web site %title (%url).<br>Username: %login<br>Password: %password<br>In order to activate your account please click <a href=\"%actlink\">the link</a>.<br>Link act in next day.",
			),
		),
		array(
			"name" => "subject_act",
			"value" => array(
				'Ваш аккаунт на сайте %title (%url) активирован администратором',
				'Your account on the website %title (%url) is activated by the administrator',
			),
		),
		array(
			"name" => "message_act",
			"value" => array(
				"Здравствуйте, %fio!<br>Ваш аккаунт на сайте %title (%url) активирован администратором.<br>Логин: %login",
				"Hello, %fio!<br>Your account on the website %title (%url) is activated by the administrator.<br>Username: %login",
			),
		),
		array(
			"name" => "mes_reminding",
			"value" => array(
				"На ваш e-mail отправлена ссылка на форму изменения пароля.",
				"In your e-mail sent a link to the password change form.",
			),
		),
		array(
			"name" => "subject_reminding",
			"value" => array(
				"Восстановление доступа к сайту %title (%url).",
				"Restore access to the site %title (%url).",
			),
		),
		array(
			"name" => "message_reminding",
			"value" => array(
				"Здравствуйте, %fio!<br>Вы запросили восстановление доступа к сайту %title (%url).<br>Для изменения пароля пройдите <a href=\"%actlink\">по ссылке</a>.",
				"Hello, %fio!<br>You have requested the restoration of access to the site %title (%url).<br>To change the password, <a href=\"%actlink\">click here</a>.",
			),
		),
		array(
			"name" => "subject_reminding_new_pass",
			"value" => array(
				"Новый пароль на сайте %title (%url)",
				"New password on the site %title (%url)",
			),
		),
		array(
			"name" => "message_reminding_new_pass",
			"value" => array(
				"Здравствуйте, %fio!<br>Вы изменили пароль на сайте %title (%url).<br>Логин: %login<br>Пароль: %password",
				"Hello, %fio!<br>You have changed your password on the site %title (%url).<br>Username: %login<br>Password: %password",
			),
		),
		array(
			"name" => "avatar",
			"value" => "1",
		),
		array(
			"name" => "avatar_width",
			"value" => "50",
		),
		array(
			"name" => "avatar_height",
			"value" => "50",
		),
		array(
			"name" => "avatar_quality",
			"value" => "80",
		),
	);

	/**
	 * @var array SQL-запросы
	 */
	public $sql = array(
		"users_role" => array(
			array(
				"id" => 1,
				"name" => array('Пользователь', 'User'),
				"registration" => 1,
			),
			array(
				"id" => 2,
				"name" => array('Модератор', 'Moderator'),
			),
			array(
				"id" => 3,
				"name" => array('Администратор', 'Administrator'),
			),
		),
		"users_role_perm" => array(
			array(
				"rewrite" => 'all',
				"perm" => 'all',
				"role_id" => 3,
				"type" => 'admin',
			),
			array(
				"rewrite" => 'useradmin',
				"perm" => 'edit',
				"role_id" => 3,
				"type" => 'site',
			),
			array(
				"rewrite" => 'forum',
				"perm" => 'moderator',
				"role_id" => 3,
				"type" => 'site',
			),
		)
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		'config' => array(
			array(
				"name" => "avatar_none",
				"value" => "1",
			),
		),
		'users' => array(
			array(
				"id" => 2,
				"name" => "michael",
				"mail" => "michael@diafan.ru",
				"fio" => "Михаил Волков",
				"role_id" => 1,
				"copy" => array('avatar/michael.png'),
			),
			array(
				"id" => 3,
				"name" => "sergey",
				"mail" => "sergey@diafan.ru",
				"fio" => "Сергей Орлов",
				"role_id" => 1,
				"copy" => array('avatar_none.png'),
			),
		),
	);

	/**
	 * Выполняет действия при установке модуля
	 *
	 * @return void
	 */
	protected function action()
	{
		if (! empty($_SESSION["install_admin_name"]))
		{
			$this->sql["users"][] =
			array(
				"id" => 1,
				"name" => $_SESSION["install_admin_name"],
				"password" => encrypt($_SESSION["install_admin_pass"]),
				"mail" => $_SESSION["install_admin_mail"],
				"fio" => $_SESSION["install_admin_fio"],
				"role_id" => 3,
				"htmleditor" => 1,
				"useradmin" => 1,
				"copy_files" => 1,
			);
		}
	}
}
