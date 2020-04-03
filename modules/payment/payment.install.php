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


class Payment_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Оплата";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "payment",
			"comment" => "Методы оплаты",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "payment",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "платежная система",
				),
				array(
					"name" => "params",
					"type" => "TEXT",
					"comment" => "серилизованные настройки платежной системы",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "payment_history",
			"comment" => "История платежей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "элемент модуля, для которого производится платеж (номер заказа или идентификатор пользователя)",
				),
				array(
					"name" => "status",
					"type" => "ENUM('request_pay', 'pay') NOT NULL DEFAULT 'request_pay'",
					"comment" => "статус платежа: request_pay - запрошен, pay - оплачен",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "summ",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "сумма платежа",
				),
				array(
					"name" => "payment_id",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор метода оплаты из таблицы {payment}",
				),
				array(
					"name" => "module_name",
					"type" => "ENUM('cart', 'balance') NOT NULL DEFAULT 'cart'",
					"comment" => "модуль, в котором используются платежи",
				),
				array(
					"name" => "code",
					"type" => "VARCHAR(32) NOT NULL DEFAULT '0'",
					"comment" => "код доступа к платежным документам",
				),
				array(
					"name" => "payment_data",
					"type" => "VARCHAR(50) NOT NULL DEFAULT '0'",
					"comment" => "данные о платеже из платежной системы",
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
			"name" => "payment",
			"admin" => true,
			"site" => true,
		),		
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Оплата",
			"rewrite" => "payment",
			"group_id" => 4,
			"sort" => 14,
			"act" => true,
			"children" => array(
				array(
					"name" => "Методы оплаты",
					"rewrite" => "payment",
					"sort" => 1,
					"act" => true,
				),
				array(
					"name" => "История платежей",
					"rewrite" => "payment/history",
					"sort" => 2,
					"act" => true,
				),				
			)
		),						
	);

	/**
	 * @var array SQL-запросы
	 */
	public $sql = array(
		"payment" => array(
			array(
				"name" => array('Наличными курьеру', 'Cash on hand'),
				"text" => array('Заказ необходимо оплатить курьеру на руки наличными', 'Pay for goods on hand in cash courier'),
			),
			array(
				"name" => array('Оплата счета балансом', 'Payment by balance'),
				"text" => array('Вы можете оплатить счет используя баланс', 'You can pay the bill using balance'),
				"payment" => 'balance',
				"module_name" => 'balance',
			),
		)
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		"payment" => array(
			array(
				"name" => array('WebMoney', 'WebMoney'),
				"text" => array('Используйте мгновенную оплату заказа через систему WebMoney. Это ускорит доставку Вашего заказа.', 'Thank you for your purchase! Use an instant payment for the order via WebMoney. This will speed up delivery of your order.'),
				"payment" => 'webmoney',
			),
			array(
				"name" => array('Robokassa', 'Robokassa'),
				"text" => array('Robokassa позволяет оплатить заказ одним из удобных для Вас способом.', 'The system allows Robokassa Pay a convenient way for you.'),
				"payment" => 'robokassa',
			),
			array(
				"name" => array('Банковские платежи', 'Bank payments'),
				"text" => array('Распечатайте квитанцию и оплатить в ближайшем отделении банка.', 'Print your receipt and pay at the nearest branch of the bank.'),
				"payment" => 'non_cash',
				"params" => 'a:14:{s:13:"non_cash_name";s:50:"ООО &quot;Бумажный зоопарк&quot;";s:13:"non_cash_ogrn";s:13:"0000000000000";s:12:"non_cash_inn";s:10:"0000000000";s:12:"non_cash_kpp";s:9:"000000000";s:11:"non_cash_rs";s:20:"00000000000000000000";s:13:"non_cash_bank";s:27:"ООО &quot;Банк&quot;";s:12:"non_cash_bik";s:9:"000000000";s:11:"non_cash_ks";s:20:"00000000000000000000";s:16:"non_cash_address";s:43:"ул. Центральная д. 13 оф. 5";s:17:"non_cash_director";s:20:"Иванов И. И.";s:14:"non_cash_glbuh";s:22:"Петрова П. П.";s:23:"non_cash_tax_department";s:29:"Налоговый орган";s:14:"non_cash_okato";s:11:"00000000000";s:12:"non_cash_nds";s:2:"18";}',
			),
			array(
				"name" => array('Яндекс.Касса', 'Yandex.Kassa'),
				"text" => array('Яндекс.Касса — сервис электронных платежей, с ее помощью можно платить с банковских карт, электронными деньгами, наличными и другими способами. За безопасность сервиса отвечают Яндекс.Деньги.', 'Yandeks.Kassa - electronic payment service, you can use it to pay with bank cards, electronic money, cash or other methods. Security service responsible Yandex.Money.'),
				"payment" => 'yandexmoney',
			),
			array(
				"name" => array('QIWI', 'QIWI'),
				"text" => array('Оплата мобильной связи, коммунальных услуг, покупок в интернет-магазинах и др. через платежные терминалы, интернет и мобильный телефон.', 'Payment of mobile communications, utilities, shopping in online stores and other through payment terminals, Internet and mobile phone.'),
				"payment" => 'qiwi',
			),
		)
	);
}
