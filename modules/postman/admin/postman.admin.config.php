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
 * Menu_admin_config
 */
class Postman_admin_config extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'base' => array (
			'hr1' => array(
				'type' => 'title',
				'name' => 'Основные',
			),
			'auto_send' => array(
				'type' => 'checkbox',
				'name' => 'Автоматическая отправка уведомлений',
				'help' => 'Если отмечено, отправка уведомлений пройдет в автоматическом режиме. Иначе требуется инициализация отправки уведомлений в административной панели сайта.',
			),
			'del_after_send' => array(
				'type' => 'checkbox',
				'name' => 'Удалять отправленные уведомления',
				'help' => 'Автоматическое удаление уведомлений после отправки.',
			),
		),
		'mail' => array (
			'email' => array( // главный ящик администратора, владельца сайта, используется по умолчанию во всех уведомлениях
				'type' => 'email',
				'name' => 'E-mail сайта',
				'help' => 'Адрес, на который по умолчанию приходят уведомления и который указывается в обратном адресе всех писем. Здесь может быть указан только один адрес.',
			),
			'smtp_mail' => array(
				'type' => 'checkbox',
				'name' => 'Использовать SMTP-авторизацию при отправке почты с сайта',
				'help' => 'Обязательно используйте исходящую SMTP-авторизацию, иначе письма-уведомления с сайта могут блокироваться большинством спам-фильтров.',
			),
			'smtp_host' => array(
				'type' => 'text',
				'name' => 'SMTP-хост (например, tls://smtp.mail.ru)',
				'depend' => 'smtp_mail',

			),
			'smtp_login' => array(
				'type' => 'text',
				'name' => 'SMTP-логин (например, ivanov@mail.ru)',
				'help' => 'Ваш почтовый логин, для входа в почту.',
				'depend' => 'smtp_mail',
			),
			'smtp_password' => array(
				'type' => 'password',
				'name' => 'SMTP-пароль',
				'help' => 'Ваш почтовый пароль, для входа в почту.',
				'depend' => 'smtp_mail',
			),
			'smtp_port' => array(
				'type' => 'numtext',
				'name' => 'SMTP-порт (например, 465 или 587)',
				'help' => 'В большинстве случаев можно не указывать. Если используется протокол SSL, то чаще всего необходимо указывать SMTP-порт 465. Если используется протокол TLS, то чаще всего необходимо указывать SMTP-порт 587.',
				'depend' => 'smtp_mail',
			),
			'mail_defer' => array(
				'type' => 'checkbox',
				'name' => 'Включить отложенную отправку для почты',
				'help' => 'Отправка почтовых уведомлений будет проходить независимо от основных процессов.',
				'depend' => 'auto_send',
			),
		),
		'sms' => array (
			'sms' => array(
				'type' => 'checkbox',
				'name' => 'Подключить SMS-уведомления',
			),
			'backend' => array(
				'type' => 'select',
				'variable' => 'sms_provider',
				'name' => 'Поставщик услуг',
				'select' => array(
					"smsc" => "SMSC",
					"bytehand" => "Byte Hand",
				),
				'depend' => 'sms',
			),
			'sms_defer' => array(
				'type' => 'checkbox',
				'name' => 'Включить отложенную отправку для SMS',
				'help' => 'Отправка SMS уведомлений будет проходить независимо от основных процессов.',
				'depend' => 'sms,auto_send',
			),
		),
	);

	/**
	 * @var array названия табов
	 */
	public $tabs_name = array(
		'base' => 'Основные настройки',
		'mail' => 'Почта',
		'sms' => 'SMS',
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'tab_card', // использование вкладок
		'config', // файл настроек модуля
	);

	/**
	 * Проверка параметров подключения к SMTP
	 *
	 * @return void
	 */
	public function validate_config_variable_smtp_mail()
	{
		if(! empty($_POST["smtp_mail"]))
		{
			if(empty($_POST["smtp_host"]) || empty($_POST["smtp_login"]) || empty($_POST["smtp_password"]))
			{
				$this->diafan->set_error("smtp_mail", "Укажите хост, логин, пароль для SMTP-авторизации");
			}
		}
	}
}
