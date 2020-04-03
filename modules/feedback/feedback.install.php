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

class Feedback_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Обратная связь";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "feedback",
			"comment" => "Сообщения из обратной связи",
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
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
				),
				array(
					"name" => "lang_id",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор языка сайта из таблицы {languages}",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "ответ",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "admin_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший сообщение в административной части",
				),
				array(
					"name" => "readed",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "сообщение прочитано: 0 - нет, 1 - да",
				),
				array(
					"name" => "url",
					"type" => "TEXT",
					"comment" => "страница, с которой отправлено сообщение",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY site_id (site_id)",
			),
		),
		array(
			"name" => "feedback_param",
			"comment" => "Поля конструктора формы обратной связи",
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
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
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
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "config",
					"type" => "TEXT",
					"comment" => "дополнительные настройки поля",
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
			"name" => "feedback_param_element",
			"comment" => "Значения полей конструктора формы обратной связи, заполненные в сообщении",
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
					"comment" => "идентификатор характеристики из таблицы {feedback_param}",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор сообщения из таблицы {feedback}",
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
			"name" => "feedback_param_select",
			"comment" => "Варианты значений полей конструктора с типом список",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор характеристики из таблицы {feedback_param}",
				),
				array(
					"name" => "value",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "значение для типа характеристики «галочка»: 0 - нет, 1 - да",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "значение",
					"multilang" => true,
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
			"name" => "feedback",
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
			"parent_id" => 4,
			"name" => array('Обратная связь', 'Feedback'),
			"act" => true,
			"module_name" => "feedback",
			"rewrite" => "feedback",
			"menu" => 1,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Обратная связь",
			"rewrite" => "feedback",
			"group_id" => 2,
			"sort" => 19,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/obratnaya_svyaz/",
			"children" => array(
				array(
					"name" => "Сообщения",
					"rewrite" => "feedback",
					"act" => true,
				),
				array(
					"name" => "Конструктор формы",
					"rewrite" => "feedback/param",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "feedback/config",
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
			"value" => "2",
		),
		array(
			"name" => "sendmailadmin",
			"value" => "1",
		),
		array(
			"name" => "add_message",
			"value" => array(
				'<div align="center"><b>Спасибо за ваше сообщение!</b></div>',
				'<div align="center"><b>Thank you for your message!</b></div>',
			),
		),
		array(
			"name" => "subject",
			"value" =>  array(
				"%title (%url). Обратная связь",
				"%title (%url). Feedback",
			),
		),
		array(
			"name" => "message",
			"value" =>  array(
				"Здравствуйте!<br>Вы оставили сообщение в форме обратной связи на сайте %title (%url).<br><b>Сообщение:</b> %message <br><b>Ответ:</b> %answer",
				"Hello!<br>You added message on the web site %title (%url).<br><b>Message:</b> %message<br><b>Answer:</b> %answer",
			),
		),
		array(
			"name" => "subject_admin",
			"value" => "%title (%url). Новое сообщение в рубрике Обратная связь",
		),
		array(
			"name" => "message_admin",
			"value" => "Здравствуйте, администратор сайта %title (%url)!<br>В рубрике Обратная связь появилось новое сообщение:<br>%message",
		),
	);

	/**
	 * @var array SQL-запросы
	 */
	public $sql = array(
		"feedback_param" => array(
			array(
				"id" => 1,
				"name" => array('Ваше имя', 'Your name'),
				"type" => "text",
				"sort" => 2,
			),
			array(
				"id" => 2,
				"name" => array('Ваш e-mail', 'Your e-mail'),
				"type" => "email",	
				"sort" => 6,			
			),
			array(
				"id" => 3,
				"name" => array('Ваше сообщение', 'Your message'),
				"type" => "textarea",
				"sort" => 8,
			),
			array(
				"id" => 4,
				"name" => array('Дата Вашего рождения', 'Your date of birth'),
				"type" => "date",
				"sort" => 3,
			),
			array(
				"id" => 5,
				"name" => array('Вы турист?', 'Are you a tourist?'),
				"type" => "select",
				"sort" => 4,
				"select" => array(
					array(
						"id" => 1,
						"name" => array('Да', 'Yes'),
					),
					array(
						"id" => 2,
						"name" => array('Нет', 'No'),
					),
				),
			),
			array(
				"id" => 6,
				"name" => array('Ваш телефон', 'Your phone number'),
				"type" => "phone",
				"sort" => 7,
			),
			array(
				"id" => 7,
				"name" => array('Для связи', 'For link'),
				"type" => "title",
				"sort" => 5,
			),
			array(
				"id" => 8,
				"name" => array('О Вас', 'About you'),
				"type" => "title",
				"sort" => 1,
			),
		)
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		'site' => array(
			array(
				'id' => 'feedback',
				'text' => array(
					'<p>Независимо от ваших спортивных достижений, возраста и профессии Вы можете на нашем сайте поделиться впечатлениями о своих путешествиях и приключениях. Давайте путешествовать вместе!</p>',
					'<p>You can share your impressions by your trips and adventures in spite of your age, profession or achievements in sports. Let\'s travel together!</p>',
				),
			),
		),
		'feedback' => array(
			array(
				'param' => array(
					1 => 'Наталья',
					2 => 'adw@site.com',
					3 => 'Хочу предложить рекламу Вашего сайта. С кем поговорить?',
					4 => '1976-11-14',
					5 => 2,
					6 => '+79998885544',
				)
			),
			array(
				'param' => array(
					1 => 'Семен',
					2 => 'mail@site.com',
					3 => 'Я бы хотел купить туристическое оборудование для группы, в количестве 15 комплектов рюкзаков, 5 палаток и прочее.
Свяжитесь со мной, хочу обсудить скидку.',
					4 => '1987-08-02',
					5 => 1,
				)
			),
		)
	);
}