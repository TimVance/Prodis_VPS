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

class Search_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = 'Поиск';
 
	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "search_results",
			"comment" => "Индексированные для поиска элементы",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор элемента",
				),
				array(
					"name" => "table_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "таблица элемента",
				),
				array(
					"name" => "lang_id",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор языка сайта из таблицы {languages}",
				),
				array(
					"name" => "access",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "доступ к элементу ограничен",
				),
				array(
					"name" => "rating",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "рейтинг для сортировки результатов",
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
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "search_keywords",
			"comment" => "Индексированные для поиска слова",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "keyword",
					"type" => "VARCHAR(255) NOT NULL DEFAULT ''",
					"comment" => "уникальное слово",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY ( `keyword` ( 3 ) )",
			),
		),
		array(
			"name" => "search_index",
			"comment" => "Связи слов и проиндексированных для поиска элементов",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "keyword_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор слова из таблицы {search_keywords}",
				),
				array(
					"name" => "result_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор проиндексированного элемента из таблицы {saerch_results}",
				),
				array(
					"name" => "rating",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "рейтинг для сортировки результатов",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY keyword_id (`keyword_id`)",
			),
		),
		array(
			"name" => "search_history",
			"comment" => "История поисковых запросов",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "name",
					"type" => "TEXT",
					"comment" => "поисковый запрос",
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
			"name" => "search",
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
			"name" => array('Поиск', 'Search'),
			"act" => true,
			"module_name" => "search",
			"map_no_show" => true,
			"noindex" => true,
			"search_no_show" => true,
			"rewrite" => "search",
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Поиск по сайту",
			"rewrite" => "search",
			"group_id" => 3,
			"sort" => 32,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/poisk/",
			"children" => array(
				array(
					"name" => "Индексация",
					"rewrite" => "search",
					"act" => true,
				),
				array(
					"name" => "История поиска",
					"rewrite" => "search/history",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "search/config",
				),
			)
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "nastr",
			"value" => "10",
		),
		array(
			"name" => "count_history",
			"value" => "10",
		),
		array(
			"name" => "show_more",
			"value" => '1',
		),
		array(
			"name" => "auto_index",
			"value" => '1',
		),
	);
}