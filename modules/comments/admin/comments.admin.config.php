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
 * Comments_admin_config
 */
class Comments_admin_config extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'config' => array (
			'format_date' => array(
				'type' => 'select',
				'name' => 'Формат даты',
				'help' => 'Позволяет настроить отображение даты в модуле.',
				'select' => array(
					0 => '01.05.2016',
					6 => '01.05.2016 14:45',
					1 => '1 мая 2016 г.',
					2 => '1 мая',
					3 => '1 мая 2016, понедельник',
					5 => 'вчера 15:30',
					4 => 'не отображать',
				),
			),
			'count_level' => array(
				'type' => 'numtext',
				'name' => 'Максимальная вложенность',
				'help' => 'Ограничивает вложенность дерева комментариев.',
			),
			'nastr' => array(
				'type' => 'numtext',
				'name' => 'Количество комментариев на странице',
				'help' => 'Количество комментариев первого уровня, показываемых на одной странице.',
			),
			'show_more' => array(
				'type' => 'checkbox',
				'name' => 'Включить «Показать ещё»',
				'help' => 'На странице комментариев появится кнопка «Показать ещё». Увеличивает количество одновременно выводимых комментариев в списке.',
			),
			'use_bbcode' => array(
				'type' => 'checkbox',
				'name' => 'Использовать bbCode',
				'help' => 'Подключает форматирование комментариев с помощью bbCode.',
			),
			'hr1' => 'hr',
			'user_name' => array(
				'type' => 'checkbox',
				'name' => 'Отображать имя пользователя, добавившего комментарий',
				'help' => 'Выводит имя пользователя, добавившего комментарий на сайте',
			),
			'only_user' => array(
				'type' => 'checkbox',
				'name' => 'Только для зарегистрированных пользователей',
				'help' => 'Параметр позволяет запретить незарегистрированным пользователям добавлять комментарии.',
			),
			'captcha' => array(
				'type' => 'module',
				'name' => 'Использовать защитный код (капчу)',
				'help' => 'Для добавления комментария пользователь должен ввести защитный код.',
			),
			'security_moderation' => array(
				'type' => 'checkbox',
				'name' => 'Модерация сообщений',
				'help' => 'Если отмечена, комментарии будут отображаться на сайте только после того, как администратор вручную установит активность. Если пользователь, добавивший комментарий имеет права администратора модуля, то комментарий появится на сайте сразу.',
			),
			'hr2' => 'hr',
			'error_insert_message' => array(
				'type' => 'text',
				'name' => 'Ваше сообщение уже имеется в базе',
				'help' => 'Сообщение пользователю при попытке повторного добавления сообщения.',
				'multilang' => true,
			),
			'add_message' => array(
				'type' => 'text',
				'name' => 'Спасибо! Ваш комментарий будет проверен в ближайшее время и появится на сайте.',
				'help' => 'Сообщение пользователю при удачном добавлении комментария.',
				'multilang' => true,
			),
			'hr3' => 'hr',
			'use_mail' => array(
				'type' => 'checkbox',
				'name' => 'Подписываться на новые комментарии',
				'help' => 'Возможность при комментировании на сайте оставить e-mail, на который будут приходить уведомления о новых комментариях ветки.',
			),
			'subject' => array(
				'type' => 'text',
				'name' => 'Тема письма для уведомлений о новых комментариях',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru).",
				'multilang' => true,
				'depend' => 'use_mail',
			),
			'message' => array(
				'type' => 'textarea',
				'name' => 'Сообщение для уведомлений о новых комментариях',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %message – комментарий,\n* %link – ссылка на страницу на сайте, где комментарий отображается,\n* %actlink – ссылка для отмены подписки.",
				'multilang' => true,
				'depend' => 'use_mail',
			),
			'emailconf' => array(
				'type' => 'select',
				'name' => 'E-mail, указываемый в обратном адресе пользователю',
				'help' => "Возможные значения:\n\n* e-mail, указанный в параметрах сайта;\n* другой (при выборе этого значения появляется дополнительное поле **впишите e-mail**).",
				'depend' => 'use_mail',
			),
			'email' => array(
				'type' => 'none',
				'name' => 'впишите e-mail',
				'hide' => true,
				'depend' => 'use_mail',
			),
			'hr4' => 'hr',
			'sendmailadmin' => array(
				'type' => 'checkbox',
				'name' => 'Уведомлять о поступлении новых комментариев на e-mail',
				'help' => 'Возможность уведомления администратора о поступлении новых комментариев из формы в пользовательской части сайта.',
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
			'subject_admin' => array(
				'type' => 'text',
				'name' => 'Тема письма для уведомлений',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru).",
				'depend' => 'sendmailadmin',
			),
			'message_admin' => array(
				'type' => 'textarea',
				'name' => 'Сообщение для уведомлений',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %message – комментарий,\n* %urlpage – ссылка на страницу на сайте, где комментарий отображается.",
				'depend' => 'sendmailadmin',
			),
			'hr5' => 'hr',
			'sendsmsadmin' => array(
				'type' => 'checkbox',
				'name' => 'Уведомлять о поступлении новых комментариев по SMS',
				'help' => 'Возможность отправлять SMS администратору при поступлении комментария. Параметр можно подключить, если в [Параметрах сайта](http://www.diafan.ru/dokument/full-manual/sysmodules/config/) настроены SMS-уведомления.',
			),
			'sms_admin' => array(
				'type' => 'text',
				'name' => 'Номер телефона в федеральном формате',
				'help' => 'Номер телефона для SMS-уведомлений администратора о новом комментарии.',
				'depend' => 'sendsmsadmin',
			),
			'sms_message_admin' => array(
				'type' => 'textarea',
				'name' => 'Сообщение для уведомлений',
				'help' => 'Текст сообщения для SMS-уведомлений администратора о новом комментарии. Не более 800 символов.',
				'depend' => 'sendsmsadmin',
			),
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
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
