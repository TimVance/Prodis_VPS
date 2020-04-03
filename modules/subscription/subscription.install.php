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

class Subscription_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Рассылки";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "subscription",
			"comment" => "Рассылки",
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
					"name" => "send",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "рассылка отправлена: 0 - нет, 1 - да",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {subscription_category}",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "текст рассылки",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY cat_id (`cat_id`)",
			),
		),
		array(
			"name" => "subscription_category",
			"comment" => "Категории рассылок",
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
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "parent_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор родителя из таблицы {subscription_category}",
				),
				array(
					"name" => "count_children",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество вложенных категорий",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY parent_id (`parent_id`)",
			),
		),
		array(
			"name" => "subscription_category_rel",
			"comment" => "Связи рассылок и категорий",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор рассылки из таблицы {subscription}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {subscription_category}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY cat_id (`cat_id`)",
			),
		),
		array(
			"name" => "subscription_category_parents",
			"comment" => "Родительские связи категорий рассылок",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {subscription_category}",
				),
				array(
					"name" => "parent_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор родителя категории из таблицы {subscription_category}",
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
			"name" => "subscription_emails",
			"comment" => "Подписчики на рассылку",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата добавления",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "имя",
				),
				array(
					"name" => "mail",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "e-mail",
				),
				array(
					"name" => "code",
					"type" => "VARCHAR(32) NOT NULL DEFAULT ''",
					"comment" => "код доступа к управлению подпиской",
				),
				array(
					"name" => "act",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "получает рассылку: 0 - нет, 1 - да",
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
			"name" => "subscription_emails_cat_unrel",
			"comment" => "Отключенные категории рассылок у подписчиков",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор подписчика из таблицы {subscription_emails}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {subscription_category}",
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
			"name" => "subscription_phones",
			"comment" => "Телефоны для рассылок",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата добавления",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "имя",
				),
				array(
					"name" => "phone",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "телефон",
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "получает рассылку: 0 - нет, 1 - да",
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
			"name" => "subscription_sms",
			"comment" => "SMS-рассылки",
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
					"name" => "send",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "рассылка отправлена: 0 - нет, 1 - да",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
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
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "subscription",
			"admin" => true,
			"site" => true,
			"site_page" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Рассылки",
			"rewrite" => "subscription",
			"group_id" => 2,
			"sort" => 21,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/rassylki/",
			"children" => array(
				array(
					"name" => "E-mail рассылки",
					"rewrite" => "subscription",
					"act" => true,
				),
				array(
					"name" => "Категории",
					"rewrite" => "subscription/category",
					"act" => true,
				),
				array(
					"name" => "Подписчики",
					"rewrite" => "subscription/emails",
					"act" => true,
				),
				array(
					"name" => "SMS-рассылки",
					"rewrite" => "subscription/sms",
					"act" => true,
				),
				array(
					"name" => "Номера телефонов",
					"rewrite" => "subscription/phones",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "subscription/config",
				),
			)
		),
	);

	/**
	 * @var array страницы сайта
	 */
	public $site = array(
		array(
			"name" => array('Рассылки', 'Subscription'),
			"act" => true,
			"module_name" => "subscription",
			"rewrite" => "subscription",
			"map_no_show" => true,
			"noindex" => true,
			"search_no_show" => true,
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
			"name" => "subject",
			"value" => array(
				"Рассылка сайта %title (%url). %subject",
				"Subscription of web site %title (%url). %subject",
			),
		),
		array(
			"name" => "message",
			"value" => array(
				"Рассылка сайта %title (%url). %text Для изменения списка категорий рассылок пройдите <a href=\"%link\">по ссылке</a>.<br>Если Вы хотите отписаться от рассылки, пройдите <a href=\"%actlink\">по ссылке</a>.",
				"Subscription of web site %title (%url).%text If you want to change the mailing list of categories, please <a href=\"%link\">click here</a>.<br>If you wish to unsubscribe, please <a href=\"%actlink\">click here</a>.",
			),
		),
		array(
			"name" => "add_mail",
			"value" => "E-mail успешно добавлен. Вам отправлено уведомление.",
		),
		array(
			"name" => "subject_user",
			"value" => array(
				"Подписка на рассылку с  сайта %title (%url)",
				"Subscribe to the site %title (%url)",
			),
		),
		array(
			"name" => "message_user",
			"value" => array(
				"Здравствуйте! Вы подписались на рассылку с сайта %title (%url).<br>Для изменения списка категорий рассылок пройдите <a href=\"%link\">по ссылке</a>.<br>Если Вы хотите отписаться от рассылки, пройдите <a href=\"%actlink\">по ссылке</a>.",
				"Hello! You are subscribed to the site %title (%url). <br>If you want to change the mailing list of categories, please <a href=\"%link\">click here</a>.<br>If you wish to unsubscribe, please <a href=\"%actlink\">click here</a>."
			),
		),
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		"config" => array(
			array(
				"name" => "cat",
				"value" =>  1,
			),
		),
		"subscription_category" => array(
			array(
				"id" => 1,
				"name" => array('Новости компании', 'Company news'),
			),
			array(
				"id" => 2,
				"name" => array('Скидки и акции', 'Discounts and promotions'),
			),			
		),
		"subscription" => array(
			array(
				"cat_id" => 1,
				"name" => 'У нас новые магазины!',
				"text" => '<p>Друзья!</p>
<p>Ждем вас в наших новых магазинах!</p>',
			),
			array(
				"cat_id" => 2,
				"name" => 'Скидки в честь десятилетия!',
				"text" => '<p>Здравствуйте!</p>\r\n<p>С удовольствием информируем Вас о том, что видео-рилоик для ТВ увидел свет!</p>',
			),
		),
		"subscription_emails" => array(
			array(
				"name" => 'Mail.ru',
				"mail" => 'mail@mail.ru',
				"code" => 'a81bf0a9a5ec47090cfb79ab0a0061cd',
			),
			array(
				"name" => 'Yandex',
				"mail" => 'yandex@yandex.ru',
				"code" => '7f6e46f1fff481b76b631821978fb39e',
			),	
			array(
				"name" => 'Google',
				"mail" => 'google@google.com',
				"code" => 'fbef23ca0a97bd720c1a90287eade81e',
			),
			array(
				"name" => '',
				"mail" => 'lena@mysite.com',
				"code" => '4a3beebada84e75f248ecc9d6275a9e3',
			),					
		),
		"subscription_phones" => array(
			array(
				"name" => '+1395938223',
				"phone" => '+79885554466',
			),
			array(
				"name" => 'Игорь Семенов',
				"phone" => '+7999-888-66-55',
			),
			array(
				"name" => 'Иван Иванович Иванов',
				"phone" => '+79995554488',
			),
			array(
				"name" => 'Елена Костровая',
				"phone" => '+78885557744',
			),					
		),
	);
}