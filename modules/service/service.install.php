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

class Service_install extends Install
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = true;

	/**
	 * @var string название
	 */
	public $title = "Модули и БД";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "access",
			"comment" => "Доступ к элементам модулей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор элемента модуля",
				),
				array(
					"name" => "element_type",
					"type" => "VARCHAR(20) NOT NULL DEFAULT 'element'",
					"comment" => "тип элемента модуля",
				),
				array(
					"name" => "module_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название модуля",
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
				"KEY module_name (module_name(2))",
				"KEY element_id (element_id)",
				"KEY element_type (element_type)",
			),
		),
		array(
			"name" => "log",
			"comment" => "Лог неудачных попыток авторизации",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "ip",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "IP-адрес пользователя",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата в формате UNIXTIME",
				),
				array(
					"name" => "count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '1'",
					"comment" => "количество неудачных попыток",
				),
				array(
					"name" => "info",
					"type" => "TEXT",
					"comment" => "дополнительная информация",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY ip (ip(4))",
			),
		),
		array(
			"name" => "log_note",
			"comment" => "Лог голосований и оценок",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
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
					"name" => "element_type",
					"type" => "ENUM('element', 'cat') NOT NULL DEFAULT 'element'",
					"comment" => "тип элемента модуля",
				),
				array(
					"name" => "note",
					"type" => "INT(7) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "оценка",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "ip",
					"type" => "VARCHAR(62) NOT NULL DEFAULT ''",
					"comment" => "IP-адрес пользователя",
				),
				array(
					"name" => "session_id",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "идентификатор сессии из таблицы {sessions}",
				),
				array(
					"name" => "include_name",
					"type" => "VARCHAR(10) NOT NULL DEFAULT ''",
					"comment" => "подключенный модуль",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY element_id (element_id)",
				"KEY session_id (session_id(2))",
				"KEY include_name (include_name(2))",
				"KEY module_name (module_name(2))",
			),
		),
		array(
			"name" => "modules",
			"comment" => "Модули",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(30) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "module_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название основного модуля",
				),
				array(
					"name" => "site",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "используется на сайте: 0 - нет, 1 - да",
				),
				array(
					"name" => "site_page",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "подключается к странице сайта: 0 - нет, 1 - да",
				),
				array(
					"name" => "admin",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "используется в административной части: 0 - нет, 1 - да",
				),
				array(
					"name" => "title",
					"type" => "VARCHAR( 100 ) NOT NULL DEFAULT ''",
					"comment" => "название для пользователей",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "redirect",
			"comment" => "Редиректы",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "redirect",
					"type" => "VARCHAR(255) NOT NULL DEFAULT ''",
					"comment" => "исходная псевдоссылка, с которой иустановлен редирект",
				),
				array(
					"name" => "code",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "код редиректа",
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
					"name" => "element_type",
					"type" => "ENUM('element', 'cat', 'brand', 'param') NOT NULL DEFAULT 'element'",
					"comment" => "тип элемента модуля",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY module_name (module_name(2))",
				"KEY element_id (element_id)",
				"KEY element_type (element_type)",
			),
		),
		array(
			"name" => "rewrite",
			"comment" => "Псевдоссылки",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "rewrite",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "псевдоссылка",
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
					"name" => "element_type",
					"type" => "ENUM('element', 'cat', 'brand', 'param') NOT NULL DEFAULT 'element'",
					"comment" => "тип элемента модуля",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY module_name (module_name(2))",
				"KEY element_id (element_id)",
			),
		),
		array(
			"name" => "sessions",
			"comment" => "Сессии",
			"fields" => array(
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "session_id",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "уникальный идентификатор сессии",
				),
				array(
					"name" => "hostname",
					"type" => "VARCHAR(128) NOT NULL DEFAULT ''",
					"comment" => "хост",
				),
				array(
					"name" => "user_agent",
					"type" => "TEXT",
					"comment" => "браузер пользователя",
				),
				array(
					"name" => "timestamp",
					"type" => "VARCHAR(20) NOT NULL DEFAULT '0'",
					"comment" => "время, до которого сессия действует",
				),
				array(
					"name" => "session",
					"type" => "TEXT",
					"comment" => "серилизованные данные сессии",
				),
			),
			"keys" => array(
				"PRIMARY KEY (session_id)",
				"KEY user_id (user_id)",
			),
		),
		array(
			"name" => "sessions_hash",
			"comment" => "Контрольные хэши авторизованных пользователей",
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
					"name" => "hash",
					"type" => "CHAR(32) NOT NULL DEFAULT ''",
					"comment" => "хеш",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "service_express_fields",
			"comment" => "Описание полей файлов импорта",
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
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор описания файла из таблицы {service_express_fields_category}",
				),
				array(
					"name" => "type",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "тип",
				),
				array(
					"name" => "params",
					"type" => "TEXT",
					"comment" => "серилизованные данные о поле",
				),
				array(
					"name" => "required",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "серилизованные данные о поле",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "выдавать ошибку, если поле не заполнено: 0 - нет, 1 - да",
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
			"name" => "service_express_fields_category",
			"comment" => "Описание файлов импорта",
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
				),
				array(
					"name" => "module_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название модуля",
				),
				array(
					"name" => "type",
					"type" => "ENUM('element', 'category', 'brand') NOT NULL DEFAULT 'element'",
					"comment" => "тип данных: element - элементы, category - категории",
				),
				array(
					"name" => "delete_items",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "удалять не описанные в файле данные: 0 - нет, 1 - да",
				),
				array(
					"name" => "add_new_items",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "добавить только новые данные, описанные в файле: 0 - нет, 1 - да",
				),
				array(
					"name" => "act_items",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "опубликовать данные, описанные в файле: 0 - нет, 1 - да",
				),
				array(
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {*module_name*_category}",
				),
				array(
					"name" => "menu_cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {menu_category}",
				),
				array(
					"name" => "count_part",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество строк, выгружаемых за один проход скрипта",
				),
				array(
					"name" => "sub_delimiter",
					"type" => "VARCHAR(20) NOT NULL DEFAULT '|'",
					"comment" => "разделитель данных внутри поля",
				),
				array(
					"name" => "header",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '1'",
					"comment" => "первая строка - названия столбцов: 0 - нет, 1 - да",
				),
				array(
				    "name" => "sort",
				    "type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
				    "comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "trash",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
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
			"name" => "service",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Модули и БД",
			"rewrite" => "service",
			"group_id" => 5,
			"sort" => 34,
			"act" => true,
			"children" => array(
				array(
					"name" => "Установка модулей",
					"rewrite" => "service",
					"act" => true,
				),
				array(
					"name" => "Восстановление БД",
					"rewrite" => "service/repair",
					"act" => true,
				),
				array(
					"name" => "Экспорт/импорт БД",
					"rewrite" => "service/db",
					"act" => true,
				),
				array(
					"name" => "Экспорт/импорт данных",
					"rewrite" => "service/express",
					"act" => true,
					"children" => array(
					),
				),
				array(
					"name" => "Импорт",
					"rewrite" => "service/express/import",
					"act" => false,
				),
				array(
					"name" => "Экспорт",
					"rewrite" => "service/express/export",
					"act" => false,
				),
				array(
					"name" => "Сохраненные импорт/экспорт",
					"rewrite" => "service/express/fields",
					"act" => false,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "service/config",
				),
			),
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "method",
			"module_name" => "route",
			"value" => "1",
		),
		array(
			"name" => "translit_array",
			"module_name" => "route",
			"value" => " |а|б|в|г|д|е|ё|ж|з|и|й|к|л|м|н|о|п|р|с|т|у|ф|х|ц|ч|ш|щ|ы|э|ю|я|А|Б|В|Г|Д|Е|Ё|Ж|З|И|Й|К|Л|М|Н|О|П|Р|С|Т|У|Ф|Х|Ц|Ч|Ш|Щ|Ы|Э|Ю|Я````-|a|b|v|g|d|e|yo|zh|z|i|y|k|l|m|n|o|p|r|s|t|u|f|kh|ts|ch|sh|sch|y|e|yu|ya|A|B|V|G|D|E|YO|ZH|Z|I|Y|K|L|M|N|O|P|R|S|T|U|F|KH|TS|CH|SH|SCH|Y|E|YU|YA",
		),
		array(
			"name" => "express_preview_enable",
			"value" => "1",
		),
		array(
			"name" => "express_count_part",
			"value" => "1000",
		),
	);

	/**
	 * Выполняет действия при установке модуля после основной установки
	 *
	 * @return void
	 */
	public function action_post()
	{
		File::save_file('Options -Indexes
<files *.txt>
<IfModule mod_authz_core.c>
Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
Order deny,allow
Deny from all
</IfModule>
</files>', 'cache/.htaccess');
	}
}
