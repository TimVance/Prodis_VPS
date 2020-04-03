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

class Ab_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Объявления";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "ab",
			"comment" => "Объявления",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "TEXT",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "date_start",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата начала показа",
				),
				array(
					"name" => "date_finish",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата окончания показа",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "map_no_show",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "не показывать на карте сайта: 0 - нет, 1 - да",
				),
				array(
					"name" => "changefreq",
					"type" => "ENUM( 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never' ) NOT NULL DEFAULT 'always'",
					"comment" => "Changefreq для sitemap.xml",
				),
				array(
					"name" => "priority",
					"type" => "VARCHAR(3) NOT NULL DEFAULT ''",
					"comment" => "Priority для sitemap.xml",
				),
				array(
					"name" => "noindex",
					"type" => "ENUM('0','1') NOT NULL DEFAULT '0'",
					"comment" => "не индексировать: 0 - нет, 1 - да",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор основной категории из таблицы {ab_category}",
				),
				array(
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
				),
				array(
					"name" => "keywords",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "ключевые слова, тег Keywords",
					"multilang" => true,
				),
				array(
					"name" => "descr",
					"type" => "TEXT",
					"comment" => "описание, тэг Description",
					"multilang" => true,
				),
				array(
					"name" => "canonical",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "канонический тег",
					"multilang" => true,
				),
				array(
					"name" => "title_meta",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "заголовок окна в браузере, тег Title",
					"multilang" => true,
				),
				array(
					"name" => "anons",
					"type" => "TEXT",
					"comment" => "анонс",
					"multilang" => true,
				),
				array(
					"name" => "anons_plus",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "добавлять анонс к описанию: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "prior",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "важно, всегда сверху: 0 - нет, 1 - да",
				),
				array(
					"name" => "timeedit",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
				array(
					"name" => "access",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "доступ ограничен: 0 - нет, 1 - да",
				),
				array(
					"name" => "admin_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший объявление в административной части",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "theme",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон страницы сайта",
				),
				array(
					"name" => "view",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон модуля",
				),
				array(
					"name" => "readed",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
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
				"KEY site_id (`site_id`)",
				"KEY user_id (`user_id`)",
			),
		),
		array(
			"name" => "ab_category",
			"comment" => "Категории объявлений",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "TEXT",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "map_no_show",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "не показывать на карте сайта: 0 - нет, 1 - да",
				),
				array(
					"name" => "changefreq",
					"type" => "ENUM( 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never' ) NOT NULL DEFAULT 'always'",
					"comment" => "Changefreq для sitemap.xml",
				),
				array(
					"name" => "priority",
					"type" => "VARCHAR(3) NOT NULL DEFAULT ''",
					"comment" => "Priority для sitemap.xml",
				),
				array(
					"name" => "noindex",
					"type" => "ENUM('0','1') NOT NULL DEFAULT '0'",
					"comment" => "не индексировать: 0 - нет, 1 - да",
				),
				array(
					"name" => "parent_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор родителя из таблицы {ab_category}",
				),
				array(
					"name" => "count_children",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество вложенных категорий",
				),
				array(
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
				),
				array(
					"name" => "keywords",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "ключевые слова, тег Keywords",
					"multilang" => true,
				),
				array(
					"name" => "descr",
					"type" => "TEXT",
					"comment" => "описание, тэг Description",
					"multilang" => true,
				),
				array(
					"name" => "canonical",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "канонический тег",
					"multilang" => true,
				),
				array(
					"name" => "title_meta",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "заголовок окна в браузере, тег Title",
					"multilang" => true,
				),
				array(
					"name" => "anons",
					"type" => "TEXT",
					"comment" => "анонс",
					"multilang" => true,
				),
				array(
					"name" => "anons_plus",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "добавлять анонс к описанию: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "timeedit",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
				array(
					"name" => "access",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "доступ ограничен: 0 - нет, 1 - да",
				),
				array(
					"name" => "admin_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший категорию в административной части",
				),
				array(
					"name" => "theme",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон страницы сайта",
				),
				array(
					"name" => "view",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон модуля",
				),
				array(
					"name" => "view_rows",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон модуля для элементов в списке категории",
				),
				array(
					"name" => "view_element",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон модуля для элементов в категории",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY parent_id (`parent_id`)",
				"KEY site_id (`site_id`)",
			),
		),
		array(
			"name" => "ab_category_parents",
			"comment" => "Родительские связи категорий объявлений",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {ab_category}",
				),
				array(
					"name" => "parent_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории-родителя из таблицы {ab_category}",
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
		array(
			"name" => "ab_category_rel",
			"comment" => "Связи объявлений и категорий",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор объявления из таблицы {ab}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {ab_category}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY cat_id (`cat_id`)",
			),
		),
		array(
			"name" => "ab_counter",
			"comment" => "Счетчик просмотров объявлений",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор объявления из таблицы {ab}",
				),
				array(
					"name" => "count_view",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество просмотров",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY element_id (`element_id`)",
			),
		),
		array(
			"name" => "ab_param",
			"comment" => "Дополнительные характеристики объявлений",
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
					"name" => "type",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "тип",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "search",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "характеристика выводится в форме поиска: 0 - нет, 1 - да",
				),
				array(
					"name" => "list",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "характеристика выводится в списке: 0 - нет, 1 - да",
				),
				array(
					"name" => "block",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "характеристика выводится в блоке объявлений: 0 - нет, 1 - да",
				),
				array(
					"name" => "id_page",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "характеристика выводится на странице объявления: 0 - нет, 1 - да",
				),
				array(
					"name" => "required",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "обязательно для заполнения: 0 - нет, 1 - да",
				),
				array(
					"name" => "page",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "отдельная страница для значений: 0 - нет, 1 - да",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "config",
					"type" => "TEXT",
					"comment" => "дополнительные настройки поля",
				),
				array(
					"name" => "display_in_sort",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "выводится в блоке для сортировки: 0 - нет, 1 - да",
				),
				array(
					"name" => "measure_unit",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "единица измерения",
					"multilang" => true,
				),
				array(
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
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
			"name" => "ab_param_category_rel",
			"comment" => "Связи дополнительных характеристик объявлений и категорий",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор характеристики из таблицы {ab_param}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {ab_category}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY cat_id (`cat_id`)",
			),
		),
		array(
			"name" => "ab_param_element",
			"comment" => "Значений дополнительных характеристик объявлений",
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
					"multilang" => true,
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор характеристики из таблицы {ab_param}",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор объявления из таблицы {ab}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY element_id (`element_id`)",
				"KEY param_id (`param_id`)",
			),
		),
		array(
			"name" => "ab_param_select",
			"comment" => "Варианты значений дополнительных характеристик объявлений типа список",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор характеристики из таблицы {ab_param}",
				),
				array(
					"name" => "value",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "значение для типа характеристики «галочка»: 0 - нет, 1 - да",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "значение",
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
			"name" => "ab_rel",
			"comment" => "Связи похожих объявлений",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор объявления из таблицы {ab}",
				),
				array(
					"name" => "rel_element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор похожего объявления из таблицы {ab}",
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
			"name" => "ab",
			"admin" => true,
			"site" => true,
			"site_page" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array (
			'name' => 'Объявления',
			'rewrite' => 'ab',
			'group_id' => 1,
			'sort' => 9,
			'act' => true,
			"add" => true,
			"add_name" => "Объявление",
			'children' => array (
				array (
					'name' => 'Объявления',
					'rewrite' => 'ab',
					'act' => true,
				),
				array (
					'name' => 'Характеристики',
					'rewrite' => 'ab/param',
					'act' => true,
				),
				array (
					'name' => 'Категории',
					'rewrite' => 'ab/category',
					'act' => true,
				),
				array (
					'name' => 'Статистика',
					'rewrite' => 'ab/counter',
					'act' => true,
				),
				array (
					'name' => 'Настройки',
					'rewrite' => 'ab/config',
				),
			),
		),
	);

	/**
	 * @var array страницы сайта
	 */
	public $site = array(
		array(
			"name" => array('Объявления', 'Ads'),
			"module_name" => "ab",
			"rewrite" => "ads",
			"menu" => 1,
			"parent_id" => 2,
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "images_element",
			"value" => 1,
		),
		array(
			"name" => "use_animation",
			"value" => 1,
		),
		array(
			"name" => "list_img_element",
			"value" => 1,
		),
		array(
			"name" => "count_list",
			"value" => 3,
		),
		array(
			"name" => "count_child_list",
			"value" => 2,
		),
		array(
			"name" => "counter",
			"value" => 1,
		),
		array(
			"name" => "nastr",
			"value" => 10,
		),
		array(
			"name" => "nastr_cat",
			"value" => 10,
		),
		array(
			"name" => "children_elements",
			"value" => 1,
		),
		array(
			"name" => "rel_two_sided",
			"value" => 1,
		),
		array(
			"name" => "add_message",
			"value" => array(
				"Объявление успешно добавлено.",
				"Ad successfully added."
			),
		),
		array(
			"name" => "subject_admin",
			"value" => "Новое объявление на сайте %title (%url)",
		),
		array(
			"name" => "message_admin",
			"value" => "Здравствуйте, администратор сайта %title (%url)!<br>В рубрике Объявления появилось новое объявление:<br>%message",
		),
		array(
			"name" => "images_variations_element",
			"value" => 'a:2:{i:0;a:2:{s:4:"name";s:6:"medium";s:2:"id";i:1;}i:1;a:2:{s:4:"name";s:5:"large";s:2:"id";i:3;}}',
		),
		array(
			"name" => "form_name",
			"value" => 1,
		),
		array(
			"name" => "form_anons",
			"value" => 1,
		),
		array(
			"name" => "form_text",
			"value" => 1,
		),
		array(
			"name" => "form_date_finish",
			"value" => 1,
		),
		array(
			"name" => "form_images",
			"value" => 1,
		),
		array(
			"name" => "captcha",
			"value" => 'a:1:{i:0;s:1:"0";}',
		),
		array(
			"name" => "comments",
			"value" => 1,
		),
		array(
			"name" => "cat",
			"value" => 1,
		),
		array(
			"name" => "rating",
			"value" => 1,
		),
		array(
			"name" => "show_more",
			"value" => '1',
		),
	);

	/**
	 * @var array SQL-запросы
	 */
	public $sql = array(
		"ab_param" => array(
			array(
				"id" => 1,
				"name" => array('Для связи', 'For communication'),
				"type" => "text",
			),
		)
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		'ab_category' => array(
			array(
				'id' => 1,
				'name' => array('Продам', 'Sale'),
			),
			array(
				'id' => 2,
				'name' => array('Куплю', 'Buy'),
			),
		),
		'ab_param' => array(
			array(
				'id' => 4,
				'name' => array('Цена', 'Price'),
				'type' => 'numtext',
				'required' => true,
				'search' => true,
				'list' => true,
				'block' => true,
				'id_page' => true,
				'cat_id' => 1,
				'measure_unit' => array('руб.', 'rub.'),
			),
			array(
				'id' => 5,
				'name' => array('Состояние', 'Condition'),
				'type' => 'select',
				'list' => true,
				'search' => 1,
				'id_page' => true,
				'select' => array(
					array(
						'id' => 3,
						'name' => array('Новый', 'New'),
					),
					array(
						'id' => 4,
						'name' => array('Б/У', 'Secondhand'),
					),
				),
			),
			array(
				'id' => 1,
				'name' => array('Имя', 'Name'),
				'type' => 'text',
				'list' => true,
				'id_page' => true,
				'required' => true,
			),
			array(
				'id' => 2,
				'name' => array('e-mail', 'e-mail'),
				'type' => 'email',
				'id_page' => true,
			),
			array(
				'id' => 3,
				'name' => array('Для связи', 'Contact'),
				'type' => 'text',
				'id_page' => true,
			),
			array(
				'id' => 6,
				'name' => array('Фотография', 'Photo'),
				'type' => 'images',
				'list' => true,
				'id_page' => true,
				'config' => 'a:1:{i:0;a:2:{s:4:"name";s:5:"large";s:2:"id";s:1:"1";}}',
			),
		),
		'ab' => array(
			array(
				'name' => array('Продам рюкзак', 'Selling backpack'),
				'anons' => array('<p>Рюкзак для зимней охоты, светлый</p>', '<p>Backpack for winter hunting, light</p>'),
				'text' => array('<p>Компактный среднеразмерный рюкзак для любителей охоты и рыбалки.<br /> В двух больших боковых карманах и одном с фронтальной части, без труда разместятся термос, фонарь, боеприпасы, перчатки, и прочие «мелочи», к которым необходим быстрый доступ. Силовые пряжки типа «Fast» выполнены из пластика повышенной прочности. Цвет подобран таким образом, что рюкзак будет отлично сочетаться с такими охотничьими костюмами, как «Беркут», «Полигон» и «Форест».</p>', '<p>Compact mid-size backpack for hunting and fishing. <br /> In two large side pockets and one from the front, easily accommodate a thermos, flashlight, ammunition, gloves, and other "stuff" to want to access fast. Power buckle type «Fast» made ​​of high-strength plastic. Color was chosen so that the backpack will go perfectly with these costumes hunting as "Berkut", "Polygon" and "Forest".</p>'),
				'cat_id' => 1,
				'param' => array(
					1 => 'Авраам',
					2 => 'avraam@muil.ru',
					3 => '+79994445522',
					4 => 1600,
					5 => 4,
				),
				'images' => array(
					'332_rukzak.jpg',
					'333_rukzak.jpg',
				),
			),
			array(
				'name' => array('Палатку для походов', ''),
				'anons' => array('<p>Куплю в хорошем состоянии палатку.</p>', ''),
				'text' => array('<p>На 4 человек, желательно раздельную 2+2.</p>', ''),
				'cat_id' => 2,
				'user_id' => 1,
				'param' => array(
					1 => 'Сергей',
					2 => 'serg@ishubolonku.ru',
					3 => '+75558884477',
					5 => 4,
				),
			),
			array(
				'name' => array('Флисовая куртка', ''),
				'anons' => array('<p>Флисовая куртка на полной молнии.</p>', ''),
				'text' => array('<p>Воротник-стойка защитит от попадания холодного воздуха.<br /> Низ куртки регулируется эластичным шнуром.<br /> Интересный дизайн подчеркнёт Вашу индивидуальность.</p>', ''),
				'cat_id' => 1,
				'param' => array(
					1 => 'Виталий',
					2 => 'me@diafan.ru',
					3 => 'Пишите на почту me@diafan.ru',
					4 => 2000,
					5 => 3,
				),
				'images' => array(
					'334_flisovaya_kurtka.jpg',
					'335_flisovaya_kurtka.jpg',
					'336_flisovaya_kurtka.jpg',
				),
			),
		),
	);
}
