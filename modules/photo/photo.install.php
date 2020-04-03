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

class Photo_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Фотогалерея";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "photo",
			"comment" => "Фотографии",
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
					"comment" => "идентификатор основного альбома из таблицы {photo_category}",
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
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший фотографию в административной части",
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
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY site_id (site_id)",
			),
		),
		array(
			"name" => "photo_rel",
			"comment" => "Связи похожих фотографий",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор фотографии из таблицы {photo}",
				),
				array(
					"name" => "rel_element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор связанной фотографии из таблицы {photo}",
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
			"name" => "photo_category",
			"comment" => "Альбомы фотографий",
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
					"comment" => "идентификатор родителя из таблицы {photo_category}",
				),
				array(
					"name" => "count_children",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество вложенных альбомов",
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
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший альбом в административной части",
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
				"KEY parent_id (parent_id)",
				"KEY site_id (site_id)",
			),
		),
		array(
			"name" => "photo_category_parents",
			"comment" => "Родительские связи альбомов фотографий",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT( 11 ) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор альбома из таблицы {photo_category}",
				),
				array(
					"name" => "parent_id",
					"type" => "INT( 11 ) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор альбома-родителя из таблицы {photo_category}",
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
			"name" => "photo_category_rel",
			"comment" => "Связи фотографий с альбомами",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор фотографии из таблицы {photo}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор альбома из таблицы {photo_category}",
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
			"name" => "photo_counter",
			"comment" => "Счетчик просмотров фотографий",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор фотографии из таблицы {photo}",
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
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "photo",
			"admin" => true,
			"site" => true,
			"site_page" => true,
		),
	);

	/**
	 * @var array страницы сайта
	 */
	public $site = array(
		array(
			"name" => array('Фотогалерея', 'Photogallery'),
			"act" => true,
			"module_name" => "photo",
			"rewrite" => "photo",
			"menu" => 1,
			"parent_id" => 2,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Фотогалерея",
			"rewrite" => "photo",
			"group_id" => 1,
			"sort" => 7,
			"act" => true,
			"add" => true,
			"add_name" => "Фотография",
			"docs" => "http://www.diafan.ru/moduli/fotogalereya/",
			"children" => array(
				array(
					"name" => "Фотографии",
					"rewrite" => "photo",
					"act" => true,
				),
				array(
					"name" => "Альбомы",
					"rewrite" => "photo/category",
					"act" => true,
				),
				array (
					'name' => 'Статистика',
					'rewrite' => 'photo/counter',
					'act' => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "photo/config",
				),
			)
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "images_element",
			"value" => "1",
		),
		array(
			"name" => "page_show",
			"value" => "1",
		),
		array(
			"name" => "use_animation",
			"value" => "1",
		),
		array(
			"name" => "count_list",
			"value" => "1",
		),
		array(
			"name" => "count_child_list",
			"value" => "1",
		),
		array(
			"name" => "children_elements",
			"value" => "1",
		),
		array(
			"name" => "counter",
			"value" => "1",
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
			"name" => "images_variations_element",
			"value" => 'a:2:{i:0;a:2:{s:4:"name";s:6:"medium";s:2:"id";i:2;}i:1;a:2:{s:4:"name";s:5:"large";s:2:"id";i:3;}}',
		),
		array(
			"name" => "show_more",
			"value" => '1',
		),
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		'config' => array(
			array(
				'name' => 'cat',
				'value' =>  1,
			),
			array(
				'name' => 'rating',
				'value' =>  1,
			),
			array(
				'name' => 'comments',
				'value' =>  1,
			),
		),
		'photo_category' => array(
			array(
				'name' => array('Фотографии наших покупателей', 'Photos of our buyers'),
				'rewrite' => 'photo/users',
			),
			array(
				'name' => array('Палатки', 'Tents'),
				'rewrite' => 'photo/palatki',
			),
		),
		'photo' => array(
			array(
				'id' => 6,
				'name' => array('Акита крупно', 'Big Akita'),
				'cat_id' => 1,
				'images' => array(
					'314_3.jpg',
				),
				'rewrite' => 'photo/users/akita2',
			),
			array(
				'id' => 5,
				'name' => array('Зимние ботинки Акита супер', 'Winter boots super Akita'),
				'cat_id' => 1,
				'rel' => array(4, 6),
				'images' => array(
					'313_1.jpg',
				),
				'rewrite' => 'photo/users/akita-super',
			),
			array(
				'id' => 4,
				'name' => array('Зимние ботинки Акита', 'Winter boots Akita'),
				'cat_id' => 1,
				'images' => array(
					'312_2.jpg',
				),
				'rewrite' => 'photo/users/akita1',
			),
			array(
				'id' => 3,
				'name' => array('Куртка шторм зимой', 'Jacket winter storm'),
				'cat_id' => 1,
				'rel' => array(1, 2),
				'images' => array(
					'309_3.jpg',
				),
				'rewrite' => 'wintershtorm',
			),
			array(
				'id' => 2,
				'name' => array('Куртка Шторм', 'Storm jacket'),
				'cat_id' => 1,
				'images' => array(
					'308_2.jpg',
				),
				'rewrite' => 'photo/users/kurtka2',
			),
			array(
				'id' => 1,
				'name' => array('Куртка Шторм', 'Storm jacket'),
				'cat_id' => 1,
				'images' => array(
					'307_1.jpg',
				),
				'rewrite' => 'photo/users/shtorm1',
			),
			array(
				'id' => 18,
				'name' => array('Мой дом - палатка', 'My home - a tent'),
				'cat_id' => 2,
				'images' => array(
					'348_palatka6.jpg',
				),
				'rewrite' => 'photo/palatki/moy-dom-palatka',
			),
			array(
				'id' => 17,
				'name' => array('Синяя палатка у предгорья', 'Blue tent in the foothills'),
				'cat_id' => 2,
				'images' => array(
					'347_palatka10.jpg',
				),
				'rewrite' => 'photo/palatki/sinyaya-palatka-u-predgorya',
			),
			array(
				'id' => 16,
				'name' => array('Внутри палатки', 'Inside the tent'),
				'cat_id' => 2,
				'images' => array(
					'346_palatka5.jpg',
				),
				'rewrite' => 'photo/palatki/vnutri-palatki',
			),
			array(
				'id' => 15,
				'name' => array('Желтая палатка', 'Yellow tent'),
				'cat_id' => 2,
				'images' => array(
					'345_palatka12.jpg',
				),
				'rewrite' => 'photo/palatki/zheltaya-palatka',
			),
			array(
				'id' => 14,
				'name' => array('Две палатки', 'Two tents'),
				'cat_id' => 2,
				'images' => array(
					'344_palatka11.jpg',
				),
				'rewrite' => 'photo/palatki/dve-palatki',
			),
			array(
				'id' => 13,
				'name' => array('Палатка с навесом', 'Tent with canopy'),
				'cat_id' => 2,
				'images' => array(
					'343_palatka7.jpg',
				),
				'rewrite' => 'photo/palatki/palatka-s-navesom',
			),
			array(
				'id' => 12,
				'name' => array('Палатка-гараж', 'Tent garage'),
				'cat_id' => 2,
				'images' => array(
					'342_palatka8.jpg',
				),
				'rewrite' => 'photo/palatki/palatka-garazh',
			),
			array(
				'id' => 11,
				'name' => array('Палатка с навесом в лесу', 'Tent with canopy in the forest'),
				'cat_id' => 2,
				'images' => array(
					'341_palatka4.jpg',
				),
				'rewrite' => 'photo/palatki/palatka-s-navesom-v-lesu',
			),
			array(
				'id' => 10,
				'name' => array('Палатка коричневая', 'Brown tent'),
				'cat_id' => 2,
				'images' => array(
					'340_palatka9.jpg',
				),
				'rewrite' => 'photo/palatki/palatka-korichnevaya',
			),
			array(
				'id' => 9,
				'name' => array('Палатка Greenel', 'Tent Greenel'),
				'cat_id' => 2,
				'images' => array(
					'339_palatka2.jpg',
				),
				'rewrite' => 'photo/palatki/palatka-greenel',
			),
			array(
				'id' => 8,
				'name' => array('Зеленая палатка', 'Green tent'),
				'cat_id' => 2,
				'images' => array(
					'338_palatka1.jpg',
				),
				'rewrite' => 'photo/palatki/zelenaya-palatka',
			),
			array(
				'id' => 7,
				'name' => array('Тент', 'Tent'),
				'cat_id' => 2,
				'images' => array(
					'337_palatka3.jpg',
				),
				'rewrite' => 'photo/palatki/tent',
			),			
		),
	);
}