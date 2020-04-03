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

class Rating_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Рейтинг";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "rating",
			"comment" => "Рейтинг элементов модулей",
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
					"name" => "module_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название модуля",
				),
				array(
					"name" => "element_type",
					"type" => "ENUM('element', 'cat') NOT NULL DEFAULT 'element'",
					"comment" => "тип элемента модуля",
				),
				array(
					"name" => "rating",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "средняя оценка",
				),
				array(
					"name" => "count_votes",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество оценок",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата последней оценки",
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
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "rating",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Рейтинг",
			"rewrite" => "rating",
			"group_id" => 2,
			"sort" => 23,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/rejtingi/",
			"children" => array(
				array(
					"name" => "Настройки",
					"rewrite" => "rating/config",
				),
			)
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "security",
			"value" => "4",
		),
		array(
			"name" => "rating",
			"module_name" => "ab",
			"value" => "1",
			"check_module" => true,
		),
		array(
			"name" => "rating",
			"module_name" => "clauses",
			"value" => "1",
			"check_module" => true,
		),
		array(
			"name" => "rating",
			"module_name" => "files",
			"value" => "1",
			"check_module" => true,
		),
		array(
			"name" => "rating",
			"module_name" => "news",
			"value" => "1",
			"check_module" => true,
		),
		array(
			"name" => "rating",
			"module_name" => "photo",
			"value" => "1",
			"check_module" => true,
		),
		array(
			"name" => "rating",
			"module_name" => "shop",
			"value" => "1",
			"check_module" => true,
		),
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		'rating' => array(
			array(
				'element_id' => 36,
				'module_name' => 'shop',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 4,
				'module_name' => 'photo',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 20,
				'module_name' => 'news',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 19,
				'module_name' => 'news',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 12,
				'module_name' => 'news',
				'rating' => 4,
				'count_votes' => 1,
			),
			array(
				'element_id' => 17,
				'module_name' => 'news',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 18,
				'module_name' => 'news',
				'rating' => 4,
				'count_votes' => 1,
			),
			array(
				'element_id' => 16,
				'module_name' => 'news',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 13,
				'module_name' => 'news',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 84,
				'module_name' => 'shop',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 85,
				'module_name' => 'shop',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 85,
				'module_name' => 'shop',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 8,
				'module_name' => 'shop',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 12,
				'module_name' => 'shop',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 20,
				'module_name' => 'shop',
				'rating' => 4,
				'count_votes' => 1,
			),
			array(
				'element_id' => 19,
				'module_name' => 'shop',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 7,
				'module_name' => 'clauses',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 6,
				'module_name' => 'clauses',
				'rating' => 4,
				'count_votes' => 1,
			),
			array(
				'element_id' => 11,
				'module_name' => 'clauses',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 2,
				'module_name' => 'files',
				'rating' => 5,
				'count_votes' => 1,
			),
			array(
				'element_id' => 1,
				'module_name' => 'files',
				'rating' => 3,
				'count_votes' => 2,
			),
		),
	);
}