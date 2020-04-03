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


class Votes_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Опросы";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "votes",
			"comment" => "Опросы",
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
					"name" => "no_result",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "не показывать результаты: 0 - нет, 1 - да",
				),
				array(
					"name" => "userversion",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "пользователи могут дать свой вариант ответа: 0 - нет, 1 - да",
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
			"name" => "votes_answers",
			"comment" => "Варианты ответов на опросы",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "votes_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор вопроса из таблицы {votes}",
				),
				array(
					"name" => "count_votes",
					"type" => "INT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество ответов",
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
				"KEY votes_id (votes_id)",
			),
		),
		array(
			"name" => "votes_site_rel",
			"comment" => "Данные о том, на каких страницах сайта выводятся опросы",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор вопроса из таблицы {votes}",
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
			"name" => "votes_userversion",
			"comment" => "Варианты ответов пользователей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "votes_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор вопроса из таблицы {votes}",
				),
				array(
					"name" => "text",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "текст",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY votes_id (votes_id)",
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "votes",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Опросы",
			"rewrite" => "votes",
			"group_id" => 2,
			"sort" => 20,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/oprosy/",
			"children" => array(
				array(
					"name" => "Опросы",
					"rewrite" => "votes",
					"act" => true,
				),
				array(
					"name" => "Варианты пользователей",
					"rewrite" => "votes/userversion",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "votes/config",
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
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		'votes' => array(
			array(
				'id' => 1,
				'name' => array('Как часто вы ходите в походы?', 'How often do you go camping?'),
				'answers' => array(
					array(
						'text' => array('Ни разу не ходил', 'Hed never been'),
					),
					array(
						'text' => array('Раз в год', 'Annually'),
					),
					array(
						'text' => array('Раз в месяц', 'Monthly'),
					),
					array(
						'text' => array('Я живу в походах', 'It\'s way of my life'),
					),					
				),
			),
			array(
				'id' => 2,
				'name' => array('Сколько вам лет?', 'How old are you?'),
				'answers' => array(
					array(
						'text' => array('до 20', 'up to 20'),
					),
					array(
						'text' => array('20-30', '30-30'),
					),
					array(
						'text' => array('30-50', '30-50'),
					),
					array(
						'text' => array('старше 50', 'over 50'),
					),
				),
			),			
		),		
	);
}