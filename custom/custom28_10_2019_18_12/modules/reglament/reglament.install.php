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

class Reglament_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Регламент";

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Регламент",
			"rewrite" => "reglament",
			"group_id" => 4,
			"sort" => 15,
			"act" => true,
			"add" => true,
			"add_name" => "Товар",
			"docs" => "",
			"children" => array()
		),
	);


    /**
     * @var array таблицы в базе данных
     */
    public $tables = array(
        array(
            "name" => "reglament",
            "comment" => "Регламент",
            "fields" => array(
                array(
                    "name" => "id",
                    "type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
                    "comment" => "идентификатор",
                ),
                array(
                    "name" => "sort",
                    "type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
                    "comment" => "подрядковый номер для сортировки",
                ),
                array(
                    "name" => "name",
                    "type" => "TEXT",
                    "comment" => "название",
                ),
                array(
                    "name" => "created",
                    "type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
                    "comment" => "дата создания",
                ),
                array(
                    "name" => "next_step",
                    "type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
                    "comment" => "следующий шаг",
                ),
                array(
                    "name" => "last_action",
                    "type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
                    "comment" => "Последняя активность",
                ),
                array(
                    "name" => "ids",
                    "type" => "TEXT",
                    "comment" => "Список заказов",
                ),
                array(
                    "name" => "timeedit",
                    "type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
                    "comment" => "время последнего изменения в формате UNIXTIME",
                ),
                array(
                    "name" => "reglament",
                    "type" => "TEXT",
                    "comment" => "",
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
            "name" => "reglament",
            "admin" => true,
            "site" => true,
            "site_page" => true,
        ),
    );


}
