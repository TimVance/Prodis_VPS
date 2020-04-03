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

class Balance_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Баланс пользователя";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "balance",
			"comment" => "Баланс пользователей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "summ",
					"type" => "DOUBLE UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "сумма на балансе",
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
			"name" => "balance",
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
			"parent_id" => 3,
			"name" => array('Баланс', 'Balance'),
			"rewrite" => "balance",
			"text" => array(''),
			"map_no_show" => true,
			"noindex" => true,
			"search_no_show" => true,
			"module_name" => "balance",
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Оплата",
			"rewrite" => "payment",
			"group_id" => "4",
			"sort" => 14,
			"act" => true,	
			"children" => array(
				array(
					"name" => "Баланс",
					"rewrite" => "balance",
					"sort" => 3,
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "balance/config",
				),			
			)
		),						
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "mes",
			"value" => array(
				"Для продолжения процедуры пополнения баланса, требуется его оплатить.",
				"To proceed with the recharge is required to pay it.",
			),
		),
		array(
			"name" => "subject",
			"value" => array(
				"Вы пополнили баланс на сайте %title (%url).",
				"You deposit balance on web site %title (%url).",
			),
		),
		array(
			"name" => "message",
			"value" => array(
				"Здравствуйте!<br>Вы пополнили баланс на сайте %title (%url):<br><br>Способ оплаты: %payment<br>Номер платежа: %id",
				"Hello, %fio!<br>You deposit balance online on web site %title (%url):<br><br>Payment: %payment<br>#%id",
			),
		),
		array(
			"name" => "subject_admin",
			"value" => "%title (%url). Пополненение баланса",
		),
		array(
			"name" => "message_admin",
			"value" => "Здравствуйте, администратор сайта %title (%url)!<br>На сайте произведено пополнение баланса пользователя.<br>Способ оплаты: %payment<br>Номер счета: %id",
		),		
		array(
			"name" => "desc_payment",
			"value" => array(
				"Popolnenie balanca #%id",
				"Recharge #%id",
			),
		),
		array(
			"name" => "payment_success_text",
			"value" => array(
				"<p>Спасибо, платеж успешно принят. Деньги зачислены на счет.</p>",
				"<p>Thank you for the payment was successful.</p>",
			),
		),
		array(
			"name" => "payment_fail_text",
			"value" => array(
				"<p>Извините, платеж не прошел.</p>",
				"<p>Sorry, the payment failed.</p>",
			),
		),
		array(
			"name" => "currency",
			"value" => array("руб.", "rub."),
		),
	);
}