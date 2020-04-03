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
 * Reviews_admin_config
 */
class Reviews_admin_config extends Frame_admin
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
			'nastr' => array(
				'type' => 'numtext',
				'name' => 'Количество отзывов на странице',
				'help' => 'Количество отзывов первого уровня, показываемых на одной странице.',
			),
			'show_more' => array(
				'type' => 'checkbox',
				'name' => 'Включить «Показать ещё»',
				'help' => 'На странице отзывов появится кнопка «Показать ещё». Увеличивает количество одновременно выводимых отзывов в списке.',
			),
			'hr1' => 'hr',
			'user_name' => array(
				'type' => 'checkbox',
				'name' => 'Отображать имя пользователя, добавившего отзыв',
				'help' => 'Выводит имя пользователя, добавившего отзыв на сайте',
			),
			'only_user' => array(
				'type' => 'checkbox',
				'name' => 'Только для зарегистрированных пользователей',
				'help' => 'Параметр позволяет запретить незарегистрированным пользователям добавлять отзывы.',
			),
			'captcha' => array(
				'type' => 'module',
				'name' => 'Использовать защитный код (капчу)',
				'help' => 'Для добавления отзыва пользователь должен ввести защитный код.',
			),
			'security_moderation' => array(
				'type' => 'checkbox',
				'name' => 'Модерация отзывов',
				'help' => 'Если отмечена, отзывы будут отображаться на сайте только после того, как администратор вручную установит активность. Если пользователь, добавивший отзыв имеет права администратора модуля, то отзыв появится на сайте сразу.',
			),
			'hr2' => 'hr',
			'add_message' => array(
				'type' => 'textarea',
				'name' => 'Сообщение пользователю после отправки отзыва',
				'multilang' => true,
			),
			'hide_form' => array(
				'type' => 'checkbox',
				'name' => 'Скрывать форму после отправки отзыва',
				'help' => 'Позволяет скрывать форму после отправки отзыва.',
			),
			'hr3' => 'hr',
			'subject' => array(
				'type' => 'text',
				'name' => 'Тема письма для ответа',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru).",
				'multilang' => true,
			),
			'message' => array(
				'type' => 'textarea',
				'name' => 'Сообщение для ответа',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %message – сообщение,\n* %answer – ответ.",
				'multilang' => true,
			),
			'emailconf' => array(
				'type' => 'select',
				'name' => 'E-mail, указываемый в обратном адресе пользователю',
				'help' => "Возможные значения:\n\n* e-mail, указанный в параметрах сайта;\n* другой (при выборе этого значения появляется дополнительное поле **впишите e-mail**).",
			),
			'email' => array(
				'type' => 'none',
				'name' => 'впишите e-mail',
				'hide' => true,
			),
			'hr4' => 'hr',
			'sendmailadmin' => array(
				'type' => 'checkbox',
				'name' => 'Уведомлять о поступлении новых отзывов на e-mail',
				'help' => 'Возможность уведомления администратора о поступлении новых отзывов из формы в пользовательской части сайта.',
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
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %message – отзыв,\n* %urlpage – ссылка на страницу на сайте, где отзыв отображается.",
				'depend' => 'sendmailadmin',
			),
			'hr5' => 'hr',
			'sendsmsadmin' => array(
				'type' => 'checkbox',
				'name' => 'Уведомлять о поступлении новых отзывов по SMS',
				'help' => 'Возможность отправлять SMS администратору при поступлении отзыва. Параметр можно подключить, если в [Параметрах сайта](http://www.diafan.ru/dokument/full-manual/sysmodules/config/) настроены SMS-уведомления.',
			),
			'sms_admin' => array(
				'type' => 'text',
				'name' => 'Номер телефона в федеральном формате',
				'help' => 'Номер телефона для SMS-уведомлений администратора о новом отзыве.',
				'depend' => 'sendsmsadmin',
			),
			'sms_message_admin' => array(
				'type' => 'textarea',
				'name' => 'Сообщение для уведомлений',
				'help' => 'Текст сообщения для SMS-уведомлений администратора о новом отзыве. Не более 800 символов.',
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
