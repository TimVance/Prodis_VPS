<?php
/**
 * Настройки SMS-провайдера «Byte Hand»
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

class Postman_bytehand_admin extends Diafan
{
	public $config = array(
		'name' => 'Byte Hand',
		'params' => array(
			'help' => array(
				'type' => 'info',
				'name' => 'Для подключения SMS-уведомлений требуется <a href="https://www.bytehand.com/" target="_blank">регистрация</a>.',
				'help' => 'SMS-рассылки интегрирована в модули «Обратная связь», «Оформление заказа», «Комментарии», «Вопрос-Ответ» для уведолмения администраторов. А также в модуль «Рассылки» для массовой рассылки SMS. Подключить SMS-уведомления нужно в настройках соответствующего модуля. Для включения SMS на сайте необходимо зарегистрироваться в системе [SMSC](https://www.bytehand.com/). На хостинге должны быть открыты соответствующие порты (обычно 3800).',
			),
			'id' => array(
				'type' => 'text',
				'name' => 'ID',
				'help' => 'Идентификатор пользователя. Целое число, больше нуля (данные из настроек сервиса Byte Hand).',
			),
			'key' => array(
				'type' => 'text',
				'name' => 'Ключ',
				'help' => 'Уникальный ключ пользователя – строка из 16 символов, пример AB48D920104CE241 (данные из настроек сервиса Byte Hand).',
			),
			'signature' => array(
				'type' => 'text',
				'name' => 'Подпись',
				'help' => 'Подпись отправителя SMS (данные из настроек сервиса Byte Hand).',
			),
		),
	);
}
