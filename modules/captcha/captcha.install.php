<?php
/**
 * Установка модуля
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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

class Captcha_install extends Install
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = true;

	/**
	 * @var string название
	 */
	public $title = "Captcha";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "captcha_qa",
			"comment" => "Вопросы для капчи типа Вопрос-ответ",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "TEXT",
					"comment" => "текст вопроса",
					"multilang" => true,
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "is_write",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "ответы не отображаются на сайте: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "captcha_qa_answers",
			"comment" => "Варианты ответов для капчи типа Вопрос-ответ",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "текст ответа",
					"multilang" => true,
				),
				array(
					"name" => "is_right",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "ответ правильный: 0 - нет, 1 - да",
				),
				array(
					"name" => "captcha_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор вопроса из таблицы {captcha}",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY captcha_id (captcha_id)",
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "captcha",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Captcha",
			"rewrite" => "captcha",
			"group_id" => 5,
			"sort" => 37,
			"act" => true,
		),
	);

	/**
    * @var array настройки
    */
    public $config = array(
        array(
            "name" => "backend",
            "value" => "kcaptcha",
        ),        
    );
}