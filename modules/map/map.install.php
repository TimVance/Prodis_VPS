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

if (! defined('DIAFAN')) {
	$path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}

class Map_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Карта сайта";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "map_index",
			"comment" => "Индекс для файла sitemap.xml",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "url",
					"type" => "TEXT",
					"comment" => "ссылка",
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
					"type" => "ENUM('element', 'cat', 'param', 'brand') NOT NULL DEFAULT 'element'",
					"comment" => "тип элемента модуля",
				),
				array(
					"name" => "timeedit",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время последнего редактирования",
				),
				array(
					"name" => "changefreq",
					"type" => "ENUM( 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never' ) NOT NULL DEFAULT 'always'",
					"comment" => "Changefreq",
				),
				array(
					"name" => "priority",
					"type" => "VARCHAR(3) NOT NULL DEFAULT ''",
					"comment" => "Priority",
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
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "map",
			"site" => true,
			"site_page" => true,
		),
	);

	/**
	 * @var array страницы сайта
	 */
	public $site = array(
		array(
			"name" => array('Карта сайта', 'Site map'),
			"act" => true,
			"module_name" => "map",
			"map_no_show" => true,
			"noindex" => true,
			"search_no_show" => true,
			"rewrite" => "map",
		),
	);

	/**
	 * Выполняет действия при установке модуля после основной установки
	 *
	 * @return void
	 */
	public function action_post()
	{
		$this->diafan->configmodules("full_index", "map", 0, false, 0);
	}
}