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

class Tags_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Теги";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "tags",
			"comment" => "Связи тегов и элементов модулей",
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
					"name" => "tags_name_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор тега из таблицы {tags_name}",
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "access",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "доступ к элементу ограничен",
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
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY element_id (element_id)",
				"KEY module_name (module_name(2))",
				"KEY tags_name_id (tags_name_id)",
			),
		),
		array(
			"name" => "tags_name",
			"comment" => "Теги",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(70) NOT NULL DEFAULT ''",
					"comment" => "тег",
					"multilang" => true,
				),
				array(
					"name" => "map_no_show",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "не показывать на карте сайта: 0 - нет, 1 - да",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
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
					"name" => "title_meta",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "заголовок окна в браузере, тег Title",
					"multilang" => true,
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "timeedit",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
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
			"name" => "tags",
			"admin" => true,
			"site" => true,
			"site_page" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Теги",
			"rewrite" => "tags",
			"group_id" => 1,
			"sort" => 10,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/tegi/",
			"children" => array(
				array(
					"name" => "Настройки",
					"rewrite" => "tags/config",
					"act" => true,
				),
			)
		),
	);

	/**
	 * @var array страницы сайта
	 */
	public $site = array(
		array(
			"name" => array('Теги', 'Tags'),
			"act" => true,
			"module_name" => "tags",
			"rewrite" => "tags",
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "nastr",
			"value" => 10,
		),
		array(
			"name" => "tags",
			"module_name" => "ab",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "tags",
			"module_name" => "clauses",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "tags",
			"module_name" => "faq",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "tags",
			"module_name" => "files",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "tags",
			"module_name" => "news",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "tags",
			"module_name" => "photo",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "tags",
			"module_name" => "shop",
			"value" => 1,
			"check_module" => true,
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
		'tags_name' => array(
			array(
				'id' => 1,
				'name' => array('Федор Конюхов', 'Fyodor Konyukhov'),
				'rewrite' => 'tags/fedor-konyukhov',
			),
			array(
				'id' => 2,
				'name' => array('Детский спорт', 'Children sport'),
				'rewrite' => 'tags/detskiy-sport',
			),
			array(
				'id' => 3,
				'name' => array('Рыболовство', 'Fishery'),
				'rewrite' => 'tags/rybolovstvo',
			),
			array(
				'id' => 4,
				'name' => array('Поздравления', 'Congratulation'),
				'rewrite' => 'tags/pozdravleniya',
			),
			array(
				'id' => 5,
				'name' => array('Туризм', 'Tourism'),
				'rewrite' => 'tags/turizm',
			),			
		),
		'tags' => array(
			array(
				'element_id' => 15,
				'module_name' => 'news',
				'tags_name_id' => 1,
			),
			array(
				'element_id' => 16,
				'module_name' => 'news',
				'tags_name_id' => 1,
			),
			array(
				'element_id' => 20,
				'module_name' => 'news',
				'tags_name_id' => 1,
			),
			array(
				'element_id' => 8,
				'module_name' => 'news',
				'tags_name_id' => 2,
			),
			array(
				'element_id' => 4,
				'module_name' => 'news',
				'tags_name_id' => 3,
			),
			array(
				'element_id' => 5,
				'module_name' => 'news',
				'tags_name_id' => 3,
			),
			array(
				'element_id' => 7,
				'module_name' => 'news',
				'tags_name_id' => 3,
			),
			array(
				'element_id' => 4,
				'module_name' => 'photo',
				'tags_name_id' => 3,
			),
			array(
				'element_id' => 2,
				'module_name' => 'news',
				'tags_name_id' => 4,
			),
			array(
				'element_id' => 3,
				'module_name' => 'news',
				'tags_name_id' => 4,
			),
			array(
				'element_id' => 19,
				'module_name' => 'news',
				'tags_name_id' => 4,
			),
			array(
				'element_id' => 12,
				'module_name' => 'news',
				'tags_name_id' => 5,
			),
			array(
				'element_id' => 16,
				'module_name' => 'clauses',
				'tags_name_id' => 5,
			),
		),
	);
}