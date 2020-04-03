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

class Comments_install extends Install
{
	/**
	 * @var string название
	 */

	public $title = "Комментарии";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "comments",
			"comment" => "Комментарии",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "текст комментария",
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
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
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор элемента модуля",
				),
				array(
					"name" => "parent_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор родителя из таблицы {comments}",
				),
				array(
					"name" => "count_children",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество вложенных комментариев",
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
		array(
			"name" => "comments_mail",
			"comment" => "Подписка пользователей на новые комментарии",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "mail",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "e-mail пользователя",
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
					"name" => "trash",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY element_id (element_id)",
				"KEY module_name (module_name(2))",
			),
		),
		array(
			"name" => "comments_parents",
			"comment" => "Родительские связи комментариев",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор комментария из таблицы {comments}",
				),
				array(
					"name" => "parent_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор родителя комментария из таблицы {comments}",
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
			"name" => "comments_param",
			"comment" => "Дополнительные поля комментариев",
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
					"name" => "type",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "тип",
				),
				array(
					"name" => "module_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название модуля",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "required",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "обязательно для заполнения: 0 - нет, 1 - да",
				),
				array(
					"name" => "show_in_list",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "показывать в списке: 0 - нет, 1 - да",
				),
				array(
					"name" => "show_in_form_auth",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "показывать в форме авторизованным пользователям: 0 - нет, 1 - да",
				),
				array(
					"name" => "show_in_form_no_auth",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "показывать в списке: 0 - нет, 1 - да",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "config",
					"type" => "TEXT",
					"comment" => "серилизованные настройки поля",
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
			"name" => "comments_param_element",
			"comment" => "Значения дополнительных полей комментариев",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "value",
					"type" => "TEXT",
					"comment" => "значение",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор характеристики из таблицы {comments_param}",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор комментария из таблицы {comments}",
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
				"KEY param_id (param_id)",
				"KEY value (value(5))",
			),
		),
		array(
			"name" => "comments_param_select",
			"comment" => "Варианты значения дополнительных полей комментариев с типом список",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор характеристики из таблицы {comments_param}",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "значение",
					"multilang" => true,
				),
				array(
					"name" => "value",
					"type" => "VARCHAR(1) NOT NULL DEFAULT ''",
					"comment" => "значение для типа характеристики «галочка»: 0 - нет, 1 - да",
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
				"KEY param_id (param_id)",
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "comments",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "captcha",
			"value" => 'a:1:{i:0;s:1:"0";}',
		),
		array(
			"name" => "format_date",
			"value" => "5",
		),
		array(
			"name" => "user_name",
			"value" => "1",
		),
		array(
			"name" => "error_insert_message",
			"value" => array(
				"Ваше сообщение уже имеется в базе.",
				"Your message already exists in the base.",
			),
		),
		array(
			"name" => "add_message",
			"value" => array(
				"Спасибо! Ваш комментарий будет проверен в ближайшее время и появится на сайте.",
				"Thank you! Your comment will check in near time and will publish on this page.",
			),
		),
		array(
			"name" => "count_level",
			"value" => "7",
		),
		array(
			"name" => "nastr",
			"value" => "10",
		),
		array(
			"name" => "security_moderation",
			"value" => "1",
		),		
		array(
			"name" => "use_mail",
			"value" => 0,
		),
		array(
			"name" => "subject",
			"value" => array(
				"Новый комментарий на сайте %title (%url)",
				"New comment on the site",
			),
		),
		array(
			"name" => "message",
			"value" => array(
				"Здравствуйте! Вы подписались на комментарии на сайте %title (%url).<br>На странице появился <a href=\"%link\">новый комментарий</a>:<br>%message<br><br>Отписаться можете по <a href=\"%actlink\">ссылке</a>.",
				"Hello! You have subscribed to comments on the site %title (%url).<br>On the page there's a <a href=\"%link\">new comment</a>:<br>%message<br><br>Can unsubscribe <a href=\"%actlink\">link</a>.",
			),
		),
		array(
			"name" => "sendmailadmin",
			"value" => "1",
		),
		array(
			"name" => "subject_admin",
			"value" => "%title (%url). Новый комментарий",
		),
		array(
			"name" => "message_admin",
			"value" => "Здравствуйте, администратор сайта %title (%url)! На странице <a href=\"%urlpage\">%urlpage</a> появился новый комментарий: %message",
		),
		array(
			"name" => "comments",
			"module_name" => "ab",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "comments",
			"module_name" => "clauses",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "comments",
			"module_name" => "faq",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "comments",
			"module_name" => "files",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "comments",
			"module_name" => "news",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "comments",
			"module_name" => "photo",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "comments_cat",
			"module_name" => "photo",
			"value" => 1,
			"check_module" => true,
		),
		array(
			"name" => "comments",
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
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Комментарии",
			"rewrite" => "comments",
			"group_id" => 2,
			"sort" => 24,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/kommentarii/",
			"children" => array(
				array(
					"name" => "Комментарии",
					"rewrite" => "comments",
					"act" => true,
				),
				array(
					"name" => "Конструктор формы",
					"rewrite" => "comments/param",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "comments/config",
				),
			)
		),
	);

	/**
	 * @var array SQL-запросы
	 */
	public $sql = array(
		"comments_param" => array(
			array(
				"id" => 1,
				"name" => array('Имя', 'Name'),
				"type" => "text",
				"required" => 1,
				"show_in_list" => 1,
				"show_in_form_no_auth" => 1,
			),
		)
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		"comments" => array(
			array(
				"text" => 'Хорошая палатка, добротная',
				"module_name" => "shop",
				"element_id" => 12,
				"user_id" => 2,
			),
			array(
				"text" => 'Приборы не очень большие, но вполне удобные. Тяжелее алюминиевых.',
				"module_name" => "shop",
				"element_id" => 76,
				"user_id" => 1,
			),
			array(
				"text" => 'Симпатичные сапоги',
				"module_name" => "photo",
				"element_id" => 6,				
				'param' => array(
					1 => 'Сергей',								
				)
			),
			array(
				"text" => 'У меня тоже такие есть, очень теплые и практичные!',
				"module_name" => "photo",
				"element_id" => 6,
				'param' => array(
					1 => 'Евгений',								
				)
			),
			array(
				"text" => 'Молодец, Федор! Желаем удачи!',
				"module_name" => "news",
				"element_id" => 20,				
				'param' => array(
					1 => 'Елена',								
				)
			),
			array(
				"text" => 'Присоединяемся к поздравлениям!',
				"module_name" => "news",
				"element_id" => 19,
				"user_id" => 2,
				'param' => array(
					1 => 'Верные друзья сайта',								
				)
			),			
		)
	);
}