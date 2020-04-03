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

class Files_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Файловый архив";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "files",
			"comment" => "Файлы в файловом архиве",
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
					"comment" => "идентификатор основной категории из таблицы {files_category}",
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
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший файл в административной части",
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
			"name" => "files_links",
			"comment" => "Внешние ссылки на файлы для файлового архива",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор файла из таблицы {files}",
				),
				array(
					"name" => "link",
					"type" => "VARCHAR( 255 ) NOT NULL DEFAULT ''",
					"comment" => "внешняя ссылка",
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
			),
		),
		array(
			"name" => "files_rel",
			"comment" => "Связи похожих файлов",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор вопроса из таблицы {files}",
				),
				array(
					"name" => "rel_element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор связанного вопроса из таблицы {files}",
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
			"name" => "files_category",
			"comment" => "Категории файлового архива",
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
					"comment" => "идентификатор родителя из таблицы {files_category}",
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
				"KEY parent_id (parent_id)",
				"KEY site_id (site_id)",
			),
		),
		array(
			"name" => "files_category_parents",
			"comment" => "Родительские связи категорий файлового архива",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT( 11 ) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {files_category}",
				),
				array(
					"name" => "parent_id",
					"type" => "INT( 11 ) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории-родителя из таблицы {files_category}",
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
			"name" => "files_category_rel",
			"comment" => "Связи файлов и категорий в файловом архиве",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор файла из таблицы {files}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {files_category}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY cat_id (cat_id)",
			),
		),
		array(
			"name" => "files_counter",
			"comment" => "Счетчик просмотров файлов в файловом архиве",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор файла из таблицы {files}",
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
			"name" => "files",
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
			"name" => "Файловый архив",
			"rewrite" => "files",
			"group_id" => 1,
			"sort" => 8,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/fajlovyj_arxiv/",
			"children" => array(
				array(
					"name" => "Файлы",
					"rewrite" => "files",
					"act" => true,
				),
				array(
					"name" => "Категории",
					"rewrite" => "files/category",
					"act" => true,
				),
				array (
					'name' => 'Статистика',
					'rewrite' => 'files/counter',
					'act' => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "files/config",
				),
			)
		),
	);

	/**
	 * @var array страницы сайта
	 */
	public $site = array(
		array(
			"name" => array('Файловый архив', 'Files'),
			"module_name" => "files",
			"rewrite" => "files",
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
			"value" => "1",
		),
		array(
			"name" => "list_img_element",
			"value" => "1",
		),
		array(
			"name" => "use_animation",
			"value" => "1",
		),
		array(
			"name" => "nastr",
			"value" => 20,
		),
		array(
			"name" => "nastr_cat",
			"value" => 10,
		),
		array(
			"name" => "count_list",
			"value" => "3",
		),
		array(
			"name" => "count_child_list",
			"value" => "2",
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
			"name" => "attachment_extensions",
			"value" => "doc, gif, jpg, jpeg, mpg, pdf, png, txt, zip",
		),
		array(
			"name" => "images_variations_element",
			"value" => 'a:2:{i:0;a:2:{s:4:"name";s:6:"medium";s:2:"id";i:1;}i:1;a:2:{s:4:"name";s:5:"large";s:2:"id";i:3;}}',
		),
		array(
			"name" => "rating",
			"value" => "1",
		),
		array(
			"name" => "comments",
			"value" => "1",
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
		'files' => array(
			array(
				'name' => array('Палатка «Каван 4» в PDF', 'Tent Cavan-4 in PDF format'),
				'anons' => array('<p>Инструкция к палатке Каван-4 в PDF-формате</p>', '<p>The instruction to tent Cavan-4 in PDF format.</p>'),
				'text' => array('<p>Инструкция доступна для скачивания в PDF-формате.</p><p>В документе полная копия информации, идущей в бумажном виде при покупке палатки.</p>', '<p>Instruction is available for download in PDF-format.</p> <p>The paper copy of the full information coming in paper form when buying a tent.</p>'),
				'rewrite' => 'files/palatka-kavan-4',
				'attachments' => array(
					array(
						'name' => 'palatka_kvan.pdf',
						'extension' => 'text/plain',
						'content' => 'Палатка Каван-4',
					),
				),				
			),
			array(
				'name' => array('Инструкция PDF к палатке Гори', ''),
				'anons' => array('<p>Доступна к скачиванию.</p>', ''),
				'text' => array('<p><span>В документе полная копия информации, идущей в бумажном виде при покупке палатки</span></p>', ''),
				'rewrite' => 'files/instruktsiya-pdf-k-palatke-gori',
				'attachments' => array(
					array(
						'name' => 'palatka_gori.pdf',
						'extension' => 'text/plain',
						'content' => 'Палатка Гори 3',
					),
				),				
			),			
		),
	);
}