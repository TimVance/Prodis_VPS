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


class Images_install extends Install
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = true;

	/**
	 * @var string название
	 */
	public $title = "Изображения";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "images",
			"comment" => "Прикрепленные изображения",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "image_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор изображения из таблицы {images}, если запись является ссылкой на изображение",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "название файла",
				),
				array(
					"name" => "module_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название модуля",
				),
				array(
					"name" => "element_type",
					"type" => "ENUM('element', 'cat', 'brand') NOT NULL DEFAULT 'element'",
					"comment" => "тип элемента модуля",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор элемента модуля",
				),
				array(
					"name" => "alt",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "атрибут alt",
					"multilang" => true,
				),
				array(
					"name" => "title",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "атрибут title",
					"multilang" => true,
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор поля или характеристики с типом «изображения»",
				),
				array(
					"name" => "size",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "размер файла",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "tmpcode",
					"type" => "VARCHAR(32) NOT NULL DEFAULT ''",
					"comment" => "временный идентификатор, если изображение прикрепляется к еще не созданному элементу",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "folder_num",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "номер папки",
				),
				array(
					"name" => "hash",
					"type" => "CHAR(32) NOT NULL DEFAULT ''",
					"comment" => "хэш файла",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY module_name (module_name(2))",
				"KEY element_id (element_id)",
				"KEY element_type (element_type)",
			),
		),
		array(
			"name" => "images_editor_folders",
			"comment" => "Папки изображений в плагине для визуального редактора",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "parent_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор папки-родителя из таблицы {images_editor_folders}",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "images_variations",
			"comment" => "Варианты загрузки изображений",
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
				),
				array(
					"name" => "folder",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "название папки",
				),
				array(
					"name" => "quality",
					"type" => "TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "качество для изображений в формет JPEG",
				),
				array(
					"name" => "param",
					"type" => "TEXT",
					"comment" => "информация о применяемых действиях",
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
			"name" => "images",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Обработка изображений",
			"rewrite" => "images",
			"group_id" => 5,
			"sort" => 35,
			"act" => true,
			"children" => array(
				array(
					"name" => "Настройки",
					"rewrite" => "images/config",
				),
			)
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "images_variations_element",
			"module_name" => "editor",
			"value" => 'a:1:{i:0;a:1:{s:2:"id";s:1:"2";}}',
		),
		array(
			"name" => "hash_compare",
			"value" => "1",
		),
	);

	/**
	 * @var array SQL-запросы
	 */
	public $sql = array(
		"images_variations" => array(
			array(
				"id" => 1,
				"name" => 'Маленькое изображение (превью)',
				"folder" => "small",
				"param" => 'a:1:{i:0;a:4:{s:4:"name";s:6:"resize";s:5:"width";i:180;s:6:"height";i:180;s:3:"max";i:0;}}',
				"quality" => 90,
			),
			array(
				"id" => 2,
				"name" => 'Среднее изображение',
				"folder" => "medium",
				"param" => 'a:1:{i:0;a:4:{s:4:"name";s:6:"resize";s:5:"width";i:300;s:6:"height";i:300;s:3:"max";i:0;}}',
				"quality" => 90,
			),
			array(
				"id" => 3,
				"name" => 'Большое изображение (полная версия)',
				"folder" => "large",
				"param" => 'a:1:{i:0;a:4:{s:4:"name";s:6:"resize";s:5:"width";i:1200;s:6:"height";i:1200;s:3:"max";i:0;}}',
				"quality" => 90,
			),
			array(
				"id" => 4,
				"name" => 'Превью товара',
				"folder" => "preview",
				"param" => 'a:1:{i:0;a:4:{s:4:"name";s:6:"resize";s:5:"width";s:3:"113";s:6:"height";s:3:"113";s:3:"max";i:0;}}',
				"quality" => 90,
			),
			array(
				"id" => 5,
				"name" => 'Аватар для отзывов',
				"folder" => "avatar",
				"param" => 'a:2:{i:0;a:4:{s:4:"name";s:6:"resize";s:5:"width";s:2:"50";s:6:"height";s:2:"50";s:3:"max";i:1;}i:1;a:7:{s:4:"name";s:4:"crop";s:5:"width";s:2:"50";s:6:"height";s:2:"50";s:8:"vertical";s:3:"top";s:11:"vertical_px";s:0:"";s:10:"horizontal";s:4:"left";s:13:"horizontal_px";s:0:"";}}',
				"quality" => 90,
			),
		)
	);

	/**
	 * Выполняет действия при установке модуля
	 *
	 * @return void
	 */
	protected function action()
	{
		File::create_dir(USERFILES.'/images');
		File::create_dir(USERFILES.'/ufiles');
	}
}
