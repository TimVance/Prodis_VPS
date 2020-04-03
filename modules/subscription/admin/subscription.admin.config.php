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
 * Subscription_admin_config
 */
class Subscription_admin_config extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'config' => array (
			'cat' => array(
				'type' => 'checkbox',
				'name' => 'Использовать категории',
				'help' => 'Позволяет включить/отключить категории рассылки.',
			),
			'captcha' => array(
				'type' => 'module',
				'name' => 'Использовать защитный код (капчу)',
				'help' => 'Для добавления сообщения пользователь должен ввести защитный код.',
			),
			'hr0' => 'hr',
			'subject' => array(
				'type' => 'text',
				'name' => 'Тема письма для рассылки',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %subject – тема рассылки.",
				'multilang' => true,
			),
			'message' => array(
				'type' => 'textarea',
				'name' => 'Шаблон письма для рассылки',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %text – текст рассылки,\n* %name – имя пользователя,\n* %link – ссылка для редактирования категорий рассылки,\n* %actlink – ссылка для отмены рассылки.",
				'multilang' => true,
			),
			'emailconf' => array(
				'type' => 'select',
				'name' => 'E-mail, указываемый в обратном адресе пользователю',
				'help' => "Возможные значения:\n\n* e-mail, указанный в параметрах сайта;\n* другой (при выборе этого значения появляется дополнительное поле **впишите e-mail**).",
				'multilang' => true,
			),
			'email' => array(
				'type' => 'none',
				'name' => 'впишите e-mail',
				'hide' => true,
				'multilang' => true,
			),
			'subscribe_in_registration' => array(
				'type' => 'checkbox',
				'name' => 'Выводить при регистрации галку «Подписаться на новости»',
				'help' => 'При регистрации пользователь может подписаться на рассылку. Если опция отключена, пользователь будет подписан автоматически.',
			),
			'subscribe_in_order' => array(
				'type' => 'checkbox',
				'name' => 'Выводить при оформлении заказа галку «Подписаться на новости»',
				'help' => 'При оформлении заказа пользователь может подписаться на рассылку. Если опция отключена, пользователь будет подписан автоматически.',
			),
			'hr1' => 'hr',
			'add_mail' => array(
				'type' => 'text',
				'name' => 'Сообщение после добавления e-mail',
				'help' => 'Сообщение пользователю, после успешной подписки на рассылку.',
				'multilang' => true,
			),
			'subject_user' => array(
				'type' => 'text',
				'name' => 'Тема письма для уведомлений пользователя о подписке на рассылку',
				'help' => "Тема письма, отправляемого пользователю, после успешной подписки на рассылку. Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru).",
				'multilang' => true,
			),
			'message_user' => array(
				'type' => 'textarea',
				'name' => 'Сообщение для уведомлений пользователя о подписке на рассылку',
				'help' => "Текст письма, отправляемого пользователю, после успешной подписки на рассылку. Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %subject – тема рассылки,\n* %link – ссылка для редактирование категорий рассылки, на которые подписан пользователь,\n* %actlink – ссылка, по которой подписчик будет отключен от рассылки.",
				'multilang' => true,
			),
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'config', // файл настроек модуля
	);
}