<?php
/**
 * Настройки SMS-провайдера «SMSC»
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

class Postman_smsc_admin extends Diafan
{
	public $config = array(
		'name' => 'SMSC',
		'params' => array(
			'help' => array(
				'type' => 'info',
				'name' => 'Для подключения SMS-уведомлений требуется <a href="https://smsc.ru/" target="_blank">регистрация</a>.',
				'help' => 'SMS-рассылки интегрирована в модули «Обратная связь», «Оформление заказа», «Комментарии», «Вопрос-Ответ» для уведомления администраторов. А также в модуль «Рассылки» для массовой рассылки SMS. Подключить SMS-уведомления нужно в настройках соответствующего модуля. Для включения SMS на сайте необходимо зарегистрироваться в системе [SMSC](https://smsc.ru/).',
			),
			'login' => array(
				'type' => 'text',
				'name' => 'Логин',
				'help' => 'Логин Клиента (данные из настроек сервиса SMSC).',
			),
			'psw' => array(
				'type' => 'text',
				'name' => 'Пароль',
				'help' => 'Пароль Клиента (данные из настроек сервиса SMSC).',
			),
			'signature' => array(
				'type' => 'text',
				'name' => 'Подпись',
				'help' => 'Подпись отправителя SMS (данные из настроек сервиса SMSC).',
			),
		),
	);
}
