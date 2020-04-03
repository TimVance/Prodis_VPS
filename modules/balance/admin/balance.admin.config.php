<?php
/**
 * Настройки модуля
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

/**
 * Balance_admin_config
 */
class Balance_admin_config extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'config' => array (
			'currency' => array(
				'type' => 'text',
				'name' => 'Название валюты',
				'help' => 'Название валюты баланса.',
				'multilang' => true,
			),
			'mes' => array(
				'type' => 'textarea',
				'name' => 'Сообщение о пополнении баланса перед оплатой',
				'help' => 'Сообщение, получаемое пользователем по окончании пополнения баланса.',
				'multilang' => true,
			),
			'desc_payment' => array(
				'type' => 'text',
				'name' => 'Описание платежа',
				'help' => "Используется платежными системами. Можно добавлять:\n\n* %id – номер платежа.",
				'multilang' => true,
			),
			'payment_success_text' => array(
				'type' => 'textarea',
				'name' => 'Платеж успешно принят',
				'help' => 'Сообщение, которое увидит пользователь, если платеж успешно принят платежной системой.',
			),
			'payment_fail_text' => array(
				'type' => 'textarea',
				'name' => 'Платеж не принят',
				'help' => 'Сообщение, которое увидит пользователь, если платеж не принят платежной системой.',
			),
			'hr2' => 'hr',
			'subject' => array(
				'type' => 'text',
				'name' => 'Тема письма пользователю о пополнении баланса',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %id – номер платежа.",
				'multilang' => true,
			),
			'message' => array(
				'type' => 'textarea',
				'name' => 'Сообщение пользователю о пополнении баланса',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %payment – способ оплаты,\n* %fio – имя пользователя,\n* %id – номер платежа.",
				'multilang' => true,
			),
			'hr3' => 'hr',
			'subject_admin' => array(
				'type' => 'text',
				'name' => 'Тема письма администратору о пополнении баланса',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %id – номер платежа.",
				'depend' => 'sendmailadmin',
			),
			'message_admin' => array(
				'type' => 'textarea',
				'name' => 'Текст письма администратору о пополнении баланса',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %payment – способ оплаты,\n* %fio – имя пользователя, пополнившего  баланс,\n* %id – номер платежа.",
				'depend' => 'sendmailadmin',
			),
			'emailconfadmin' => array(
				'type' => 'function',
				'name' => 'E-mail для уведомлений администратора',
				'help' => "Возможные значения:\n\n* e-mail, указанный в параметрах сайта;\n* другой (при выборе этого значения появляется дополнительное поле **впишите e-mail**).",
				'depend' => 'sendmailadmin',
			),
			'email_admin' => array(
				'type' => 'none',
				'name' => 'впишите e-mail',
				'hide' => true,
			),
			'hr4' => 'hr',
			'sendsmsadmin' => array(
				'type' => 'checkbox',
				'name' => 'Уведомлять о пополнении баланса по SMS',
				'help' => 'Возможность отправлять SMS администратору при пополнении баланса. Параметр можно подключить, если в [Параметрах сайта](http://www.diafan.ru/dokument/full-manual/sysmodules/config/) настроены SMS-уведомления.',
			),
			'sms_admin' => array(
				'type' => 'text',
				'name' => 'Номер телефона в федеральном формате',
				'help' => 'Номер телефона для SMS-уведомлений администратора о пополнении баланса.',
				'depend' => 'sendsmsadmin',
			),
			'sms_message_admin' => array(
				'type' => 'textarea',
				'name' => 'Сообщение для уведомлений',
				'help' => 'Текст сообщения для SMS-уведомлений администратора о пополнении баланса. Не более 800 символов.',
				'depend' => 'sendsmsadmin',
			),
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'element_site', // делит элементы по разделам (страницы сайта, к которым прикреплен модуль)
		'config', // файл настроек модуля
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if(! $this->diafan->configmodules("sms", 'postman'))
		{
			$this->diafan->variable("sendsmsadmin", "disabled", true);
			$name = $this->diafan->_($this->diafan->variable("sendsmsadmin", "name")).'<br>'.$this->diafan->_('Необходимо %sнастроить%s SMS-уведомления.', '<a href="'.BASE_PATH_HREF.'config/">', '</a>');
			$this->diafan->variable("sendsmsadmin", "name", $name);
			$this->diafan->configmodules("sendsmsadmin", $this->diafan->_admin->module, $this->diafan->_route->site, _LANG, 0);
		}
	}
}
