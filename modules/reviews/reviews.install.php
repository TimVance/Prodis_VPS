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

class Reviews_install extends Install
{
	/**
	 * @var string название
	 */

	public $title = "Отзывы";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "reviews",
			"comment" => "Отзывы",
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
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
				),
				array(
					"name" => "readed",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "сообщение прочитано: 0 - нет, 1 - да",
				),
				array(
					"name" => "votes",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "средняя оценка",
				),
				array(
					"name" => "count_votes_plus",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество согласных",
				),
				array(
					"name" => "count_votes_minues",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество несогласных",
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
					"name" => "text",
					"type" => "TEXT",
					"comment" => "ответ",
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
			"name" => "reviews_param",
			"comment" => "Дополнительные поля отзывов",
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
					"name" => "info",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "смысловая нагрузка",
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
			"name" => "reviews_param_element",
			"comment" => "Значения дополнительных полей отзывов",
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
					"comment" => "идентификатор характеристики из таблицы {reviews_param}",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор отзыва из таблицы {reviews}",
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
			"name" => "reviews_param_select",
			"comment" => "Варианты значения дополнительных полей отзывов с типом список",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор характеристики из таблицы {reviews_param}",
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
			"name" => "reviews",
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
			"name" => "add_message",
			"value" => array(
				"Спасибо за Ваш отзыв!",
				"Thanks for your feedback!",
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
			"name" => "sendmailadmin",
			"value" => "1",
		),
		array(
			"name" => "subject_admin",
			"value" => "%title (%url). Новый отзыв",
		),
		array(
			"name" => "message_admin",
			"value" => "Здравствуйте, администратор сайта %title (%url)! На странице <a href=\"%urlpage\">%urlpage</a> появился новый отзыв: %message",
		),
		array(
			"name" => "subject",
			"value" =>  array(
				"%title (%url). Отзывы",
				"%title (%url). Reviews",
			),
		),
		array(
			"name" => "message",
			"value" =>  array(
				"Здравствуйте!<br>Вы оставили отзыв на сайте %title (%url).<br><b>Сообщение:</b> %message <br><b>Ответ:</b> %answer",
				"Hello!<br>You left a review on the site %title (%url).<br><b>Message:</b> %message<br><b>Answer:</b> %answer",
			),
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
			"name" => "Отзывы",
			"rewrite" => "reviews",
			"group_id" => 2,
			"sort" => 25,
			"act" => true,
			"children" => array(
				array(
					"name" => "Отзывы",
					"rewrite" => "reviews",
					"act" => true,
				),
				array(
					"name" => "Конструктор формы",
					"rewrite" => "reviews/param",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "reviews/config",
				),
			)
		),
	);

    /**
     * @var array страницы сайта
     */
    public $site = array(
        array(
			"parent_id" => 4,
            "name" => array('Отзывы', 'Reviews'),
            "act" => true,
            "rewrite" => "reviews",
			"text" => array('<insert name="show" module="reviews">', '<insert name="show" module="reviews">'),
			"menu" => 1,
			"module_name" => "reviews",
		),
	);

	/**
	 * @var array SQL-запросы
	 */
	public $sql = array(
		"reviews_param" => array(
			array(
				"id" => 1,
				"name" => array('Имя', 'Name'),
				"type" => "text",
				"info" => "name",
				"required" => 1,
				"show_in_form_no_auth" => 1,
			),
			array(
				"id" => 2,
				"name" => array('E-mail', 'E-mail'),
				"type" => "email",
				"info" => "email",
				"required" => 1,
				"show_in_form_auth" => 1,
				"show_in_form_no_auth" => 1,
			),
			array(
				"id" => 3,
				"name" => array('Город', 'City'),
				"type" => "text",
				"show_in_list" => 1,
				"show_in_form_no_auth" => 1,
				"show_in_form_auth" => 1,
			),
			array(
				"id" => 4,
				"name" => array('Аватар', 'Avatar'),
				"type" => "images",
				"info" => "avatar",
				"show_in_form_no_auth" => 1,
				"config" => 'a:1:{i:0;a:2:{s:4:"name";s:5:"large";s:2:"id";s:1:"5";}}',
			),
			array(
				"id" => 5,
				"name" => array('Оценка', 'Rating'),
				"type" => "radio",
				"info" => "rating",
				"show_in_list" => 1,
				"show_in_form_no_auth" => 1,
				"show_in_form_auth" => 1,
				'select' => array(
					array(
						'id' => 1,
						'name' => array(1, 1),
					),
					array(
						'id' => 2,
						'name' => array(2, 2),
					),
					array(
						'id' => 3,
						'name' => array(3, 3),
					),
					array(
						'id' => 4,
						'name' => array(4, 4),
					),
					array(
						'id' => 5,
						'name' => array(5, 5),
					),
				),
			),
			array(
				"id" => 6,
				"name" => array('Достоинства', 'Advantages'),
				"type" => "textarea",
				"show_in_list" => 1,
				"show_in_form_no_auth" => 1,
				"show_in_form_auth" => 1,
			),
			array(
				"id" => 7,
				"name" => array('Недостатки', 'Disadvantages'),
				"type" => "textarea",
				"show_in_list" => 1,
				"show_in_form_no_auth" => 1,
				"show_in_form_auth" => 1,
			),
			array(
				"id" => 8,
				"name" => array('Отзыв', 'Review'),
				"type" => "textarea",
				"show_in_list" => 1,
				"show_in_form_no_auth" => 1,
				"show_in_form_auth" => 1,
			),
			array(
				"id" => 9,
				"name" => array('Приложить файлы', 'Attach files'),
				"type" => "attachments",
				"show_in_list" => 1,
				"show_in_form_no_auth" => 1,
				"show_in_form_auth" => 1,
				"config" => 'a:11:{s:21:"max_count_attachments";i:3;s:21:"attachment_extensions";s:13:"jpg, png, zip";s:15:"recognize_image";i:0;s:24:"attachments_access_admin";i:0;s:16:"attach_big_width";i:0;s:17:"attach_big_height";i:0;s:18:"attach_big_quality";i:0;s:19:"attach_medium_width";i:0;s:20:"attach_medium_height";i:0;s:21:"attach_medium_quality";i:0;s:13:"use_animation";i:0;}',
			),
		)
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		"reviews" => array(
		)
	);
}