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

class Bs_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Баннеры";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "bs",
			"comment" => "Баннеры",
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
					"name" => "alt",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "атрибут alt для баннера-изображения",
					"multilang" => true,
				),
				array(
					"name" => "title",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "атрибут title для баннера-изображения",
					"multilang" => true,
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "type",
					"type" => "ENUM('1','2','3') NOT NULL DEFAULT '1'",
					"comment" => "тип: 1 - изображение, 2 - флэш, 3 - HTML",
				),
				array(
					"name" => "file",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "имя файла, загруженного в папку userfls/bs",
				),
				array(
					"name" => "html",
					"type" => "TEXT",
					"comment" => "HTML код баннера-блока",
				),
				array(
					"name" => "link",
					"type" => "TEXT",
					"comment" => "ссылка на баннер-изображение или баннер-флэш",
					"multilang" => true,
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {bs_category}",
				),
				array(
					"name" => "check_number",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "ограничить количество показов: 0 - нет, 1 - да",
				),
				array(
					"name" => "check_click",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "ограничить по количеству кликов: 0 - нет, 1 - да",
				),
				array(
					"name" => "show_click",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "ограничить по количеству кликов: осталось кликов",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
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
					"name" => "show_number",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "ограничить количество показов: осталось показов",
				),
				array(
					"name" => "click",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "всего кликов",
				),
				array(
					"name" => "width",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "ширина флэш-баннера",
				),
				array(
					"name" => "height",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "высота флэш-баннера",
				),
				array(
					"name" => "check_user",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "ограничить количество показов посетителю в сутки: 0 - нет, 1 - да",
				),
				array(
					"name" => "show_user",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество показов посетителю в сутки",
				),
				array(
					"name" => "count_view",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "всего показов",
				),
				array(
					"name" => "target_blank",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "открывать ссылку в новом окне",
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
			"name" => "bs_category",
			"comment" => "Категории баннеров",
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
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
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
			"name" => "bs_site_rel",
			"comment" => "Данные о том, на каких страницах сайта выводятся баннеры",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор баннера из таблицы {bs_category}",
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
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "bs",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Баннеры",
			"rewrite" => "bs",
			"group_id" => 1,
			"sort" => 8,
			"act" => true,
			"add" => true,
			"add_name" => "Баннер",
			"docs" => "http://www.diafan.ru/moduli/bannery/",
			"children" => array(
				array(
					"name" => "Баннеры",
					"rewrite" => "bs",
					"act" => true,
				),
				array(
					"name" => "Категории",
					"rewrite" => "bs/category",
					"act" => true,
				),
				array(
					"name" => "Настройка",
					"rewrite" => "bs/config",
				),
			)
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "cat",
			"value" => 1,
		),		
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		'bs_category' => array(
			array(
				'id' => 1,
				'name' => 'Слайдер на главной',
				'act' => 1,				
			),
			array(
				'id' => 2,
				'name' => 'Сквозной баннер внизу',
				'act' => 1,	
			),
		),
		'bs' => array(
			array(				
				'name' => array('Палатки и тенты', 'Tents and awnings'),				
				'type' => 1,
				'text' => array('<h2>Палатки и тенты</h2> <p>для экстремальных условий</p>', '<h2>Tents and awnings</h2> <p>for extreme conditions</p>'),
				'file' => 'sample_slide_01_1_1.jpg',
				'link' => array('BASE_PATHshop/palatki/', 'BASE_PATHeng/shop/palatki/'),				
				'target_blank' => '1',
				'site_id' => 1,
				'cat_id' => 1,
				'copy' => array('bs/sample_slide_01_1_1.jpg'),
			),
			array(
				'name' => array('Рюкзаки', 'Backpacks'),				
				'type' => 1,
				'text' => array('<h2>Рюкзаки</h2> <p>для дальних походов</p>', '<h2>Backpacks</h2> <p>for long-distance camping</p>'),
				'file' => 'sample_slide_02_2_2.jpg',
				'link' => array('BASE_PATHshop/ryukzaki/', 'BASE_PATHeng/shop/ryukzaki/'),
				'target_blank' => '1',
				'site_id' => 1,
				'cat_id' => 1,
				'copy' => array('bs/sample_slide_02_2_2.jpg'),
			),
			array(
				'name' => array('Спецпредложение', 'Special offer'),				
				'type' => 1,
				'file' => 'special_01_4.png',
				'link' => array('BASE_PATHshop/drugoe/', 'BASE_PATHeng/shop/drugoe/'),				
				'target_blank' => '1',				
				'cat_id' => 2,
				'copy' => array('bs/special_01_4.png'),
			),			
		),
	);
}