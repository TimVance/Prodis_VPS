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
 * Visitors_admin_config
 */
class Visitors_admin_config extends Frame_admin
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
			'counter_enable' => array(
				'type' => 'checkbox',
				'name' => 'Активировать ведение Статистики CMS',
				'help' => 'Подключает статистику с использованием возможностей CMS.',
			),
			'counter_cache_extreme' => array(
				'type' => 'checkbox',
				'name' => 'Ведение Статистики CMS в режиме экстримального кэширования',
				'help' => 'Ведение статистических данных при режиме экстримального кэширования.',
				'depend' => 'counter_enable',
			),
			'counter_defer' => array(
				'type' => 'checkbox',
				'name' => 'Отложенный режим определения агента',
				'help' => 'Использовать отложенный режим для идентификации пользовательского агента.',
			),
			'counter_delete_log' => array(
				'type' => 'checkbox',
				'name' => 'Сбросить лог посещений',
				'no_save' => true,
				'help' => 'Если отметить, данные Лога посещений CMS будут удалены. Галка при этом не останется отмечена. Рекомендуется удалять данные, после внесения изменений в скрипты данного модуля.',
			),
			'counter_delete_stat' => array(
				'type' => 'checkbox',
				'name' => 'Сбросить статистику',
				'no_save' => true,
				'help' => 'Если отметить, данные Статистики CMS будут удалены. Галка при этом не останется отмечена. Рекомендуется удалять данные, после внесения изменений в скрипты данного модуля.',
			),
			'counter_delete_valid' => array(
				'type' => 'checkbox',
				'name' => 'Сбросить результаты валидации',
				'no_save' => true,
				'help' => 'Если отметить, данные о ранее пройденной валидации пользовательских агентов будут удалены. Сама валидация для таких агентов будет проведена повторно. Галка при этом не останется отмечена. Рекомендуется удалять данные, после внесения изменений в скрипты данного модуля.',
			),
			'hr2' => array(
				'type' => 'title',
				'name' => 'Идентификация',
			),
			'counter_active_interval' => array(
				'type' => 'numtext',
				'name' => 'Интервал между посещениями (в минутах)',
				'help' => 'Интервал с момента последней активности, в течении которого пользователь не считается уникальным. Значение задается в минутах. По умолчанию 1440 минут = 1 сутки.',
			),
			'counter_delay_activity_user' => array(
				'type' => 'numtext',
				'name' => 'Интервал между запросами пользователя (в секундах)',
				'help' => 'Задержка, при которой допускается, что посетитель, запросивший контент страницы, активен на сайте. Значение задается в секундах. По умолчанию 900 секунд = 15 минут.',
			),
			'counter_delay_activity_bot' => array(
				'type' => 'numtext',
				'name' => 'Интервал между запросами бота (в секундах)',
				'help' => 'Задержка, при которой допускается, что посетитель, запросивший контент страницы, активен на сайте. Значение задается в секундах. По умолчанию 30 секунд.',
			),
			'hr3' => array(
				'type' => 'title',
				'name' => 'Блокировка',
				'depend' => 'counter_enable',
			),
			'counter_block_access_bots' => array(
				'type' => 'checkbox',
				'name' => 'Блокировать доступ для спам-ботов по имени пользовательского агента',
				'help' => 'Блокирует доступ спам-ботов к страницам сайта по имени пользовательского агента. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
				'depend' => 'counter_enable',
			),
			'counter_block_access_search_bots' => array(
				'type' => 'checkbox',
				'name' => 'Блокировать доступ для поисковых ботов по имени пользовательского агента',
				'help' => 'Блокирует доступ поисковых ботов к страницам сайта по имени пользовательского агента. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
				'depend' => 'counter_enable',
			),
		),
		'yandex' => array (
			'hr4' => array(
				'type' => 'title',
				'name' => 'Основные',
			),
			'yandex_js_counter' => array(
				'type' => 'textarea',
				'name' => 'Код счетчика Яндекс.Метрики',
				'help' => 'Для использования Яндекс.Метрики разместите полученный JavaScript-код. Для получения кода необходимо перейти по адресу: https://metrika.yandex.ru/',
				'height' => 250,
			),
			'yandex_js_inside_head' => array(
				'type' => 'checkbox',
				'name' => 'Разместить код счетчика в зоне тега &lt;HEAD&gt;',
				'help' => 'Подключает JavaScript-код счетчика в зоне тега &lt;HEAD\>. По умолчанию код размещается в конце зоны тега &lt;BODY&gt;',
			),
			'hr5' => array(
				'type' => 'title',
				'name' => 'API Яндекс.Посещаемость',
			),
			'yandex_mail_login' => array(
				'type' => 'text',
				'name' => 'E-mail для авторизации',
				'help' => 'Для использования API Яндекс.Метрики необходимо указать действующий электронный почтовый адрес, зарегистрированный на Яндекс. Для регистрации почтового адреса необходимо перейти по адресу: https://yandex.ru/',
			),
			'hr6' => array(
				'type' => 'hr',
			),
			'yandex_client_id' => array(
				'type' => 'text',
				'name' => 'ID зарегистрированного приложения',
				'help' => 'Идентификатор зарегистрированного приложения, полученный через OAuth-сервер Яндекса.',
			),
			'yandex_client_password' => array(
				'type' => 'text',
				'name' => 'Пароль для зарегистрированного приложения',
				'help' => 'Пароль зарегистрированного приложения, полученный через OAuth-сервер Яндекса.',
			),
			'yandex_counter_id' => array(
				'type' => 'text',
				'name' => 'Идентификатор счетчика',
				'help' => 'Идентификатор счетчика, информацию о котором Вы хотите получить. Для того, чтобы узнать идентификатор необходимо перейти по адресу: https://metrika.yandex.ru/',
			),
		),
		'google' => array (
			'hr7' => array(
				'type' => 'title',
				'name' => 'Основные',
			),
			'google_js_counter' => array(
				'type' => 'textarea',
				'name' => 'Код счетчика Google Analytics',
				'help' => 'Для использования Google Analytics разместите полученный JavaScript-код. Для получения кода необходимо перейти по адресу: https://analytics.google.com/',
				'height' => 250,
			),
			'google_js_inside_head' => array(
				'type' => 'checkbox',
				'name' => 'Разместить код счетчика в зоне тега &lt;HEAD&gt;',
				'help' => 'Подключает JavaScript-код счетчика в зоне тега &lt;HEAD\>. По умолчанию код размещается в конце зоны тега &lt;BODY&gt;',
			),
			'hr8' => array(
				'type' => 'title',
				'name' => 'API Google Analytics',
			),
			'google_mail_login' => array(
				'type' => 'text',
				'name' => 'E-mail для авторизации',
				'help' => 'Для использования API Google Analytics необходимо указать действующий аккаунт Google. Для регистрации аккаунта необходимо перейти по адресу: https://accounts.google.com/',
			),
			'hr9' => array(
				'type' => 'hr',
			),
			'google_project_id' => array(
				'type' => 'text',
				'name' => 'ID приложения',
				'help' => 'Идентификатор зарегистрированного приложения, полученный через OAuth-сервер Goolgle.',
			),
			'google_client_id' => array(
				'type' => 'text',
				'name' => 'ID клиента',
				'help' => 'Идентификатор клиента зарегистрированного приложения, полученный через OAuth-сервер Goolgle.',
			),
			'google_client_password' => array(
				'type' => 'text',
				'name' => 'Пароль для зарегистрированного приложения',
				'help' => 'Пароль зарегистрированного приложения, полученный через OAuth-сервер Goolgle.',
			),
			'google_counter_id' => array(
				'type' => 'text',
				'name' => 'Идентификатор счетчика',
				'help' => 'Идентификатор счетчика, информацию о котором Вы хотите получить. Для того, чтобы узнать идентификатор необходимо перейти по адресу: http://google.com/analytics/',
			),
		),
	);

	/**
	 * @var array названия табов
	 */
	public $tabs_name = array(
		'base' => 'CMS',
		'yandex' => 'Яндекс',
		'google' => 'Google',
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'tab_card', // использование вкладок
		'config', // файл настроек модуля
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{

	}

	/**
	 * Определяет дополнительные подсказки для полей
	 *
	 * @param string $key название текущего поля или текст подсказки
	 * @return string
	 */
	public function helper($key = '')
	{
		if (! $key)
		{
			$key = $this->diafan->key;
		}
		if(! $this->diafan->is_variable($key))
		{
			$helper = $key;
			$key = rand(0, 3333);
		}
		elseif (! $help = $this->diafan->variable($key, 'helper'))
		{
			return '';
		}

		return '
		<div class="helper">
			<input type="checkbox" id="'.$this->diafan->key.'_helper'.'" class="checkbox hide"/>
			<label for="'.$this->diafan->key.'_helper'.'" class="btn btn_blue btn_small btn_helper">'.$this->diafan->_('Инструкция').'</label>
			<div>'.$this->diafan->_($help).'</div>
		</div>';
	}

	/**
	 * Редактирование поля "ID зарегистрированного приложения"
	 *
	 * @return void
	 */
	public function edit_config_variable_yandex_client_id()
	{
		$helper = 'Для использования API Яндекс.Метрики необходимо получить идентификатор зарегистрированного приложения через OAuth-сервер Яндекса.<br>Для этого необходимо перейти по адресу: <b><a href="https://oauth.yandex.ru/client/new">https://oauth.yandex.ru/client/new</a></b>.<br><br>Далее необходимо заполнить следующие поля.<br>- «Название» допускается указать любое удобное для Вас название.<br>- «Платформы» необходимо отметить пункт «Веб-сервисы» и заполнить поле «Callback URI #1», указав следующий URL-адрес: <b>http://'.MAIN_DOMAIN.'/</b><br>Далее, нажав кнопку «Добавить», заполнить поле «Callback URI #2», указав следующий URL-адрес: <b>https://'.MAIN_DOMAIN.'/</b><br>- в раскрывающемся списке «Яндекс.Метрики» необходимо отметить пункт «Получение статистики, чтение параметров своих и доверенных счётчиков».<br>- «Ссылка на приложение» допускается указать, например, свой сайт (запись не обязательна).<br><br>Далее сохраняем внесенные изменения и получаем для дальнейших действий «Id приложения» и «Пароль приложения», которые записываем в настройках cms.';
		$this->diafan->variable($this->diafan->key, 'helper', $helper);

		$type = $this->diafan->variable('', 'type');
		$this->diafan->show_table_tr(
						$type,
						$this->diafan->key,
						$this->diafan->value,
						$this->diafan->variable_name(),
						$this->diafan->help(),
						$this->diafan->variable_disabled(),
						$this->diafan->variable('', 'maxlength'),
						$this->diafan->variable('', 'select'),
						$this->diafan->variable('', 'select_db'),
						$this->diafan->variable('', 'depend'),
						$this->diafan->variable('', 'attr')
					);
		echo $this->diafan->helper();
	}

	/**
	 * Редактирование поля "ID зарегистрированного приложения"
	 *
	 * @return void
	 */
	public function edit_config_variable_google_project_id()
	{
		$helper = 'Для использования API Google Analytics необходимо получить идентификатор зарегистрированного приложения через OAuth-сервер Goolgle.<br>Для этого необходимо создать проект, перейдя в консоль API Google:<br><b><a href="https://console.developers.google.com/start/api?id=analytics&amp;credential=client_key&amp;hl=ru">https://console.developers.google.com/start/api?id=analytics&amp;credential=client_key&amp;hl=ru</a></b> или <b><a href="https://code.google.com/apis/console/">https://code.google.com/apis/console/</a></b><br><br>В качестве авторизованного домена необходимо указать: <b>'.MAIN_DOMAIN.'</b><br><br>Сохраните полученный идентификатор проекта в настройки cms. Далее включите Google Analytics API (Меню -> API и Сервисы -> Панель управления -> Analytics API). Далее необходимо создать идентификатор клиента (Меню -> API и Сервисы -> Учетные данные) - Идентификатор клиента OAuth.<br><br>В качестве типа приложения выберите веб-приложение.<br>Введите название.<br>Поле Разрешенные источники JavaScript оставьте пустым.<br>В поле Разрешенные URI перенаправления введите <b>http://'.MAIN_DOMAIN.'</b> и <b>https://'.MAIN_DOMAIN.'</b><br><br>Нажмите кнопку Создать.<br>В настройки cms внесите полученные данные (идентификатор клиента и секретный ключ).<br>Выберите созданные учетные данные и нажмите Скачать файл JSON.<br>Сохраните файл под именем <b>client_secrets.json</b>.<br><br>Посмотрев содержание данного файла можно сверить с введенными данными в настройки cms.';
		$this->diafan->variable($this->diafan->key, 'helper', $helper);

		$type = $this->diafan->variable('', 'type');
		$this->diafan->show_table_tr(
						$type,
						$this->diafan->key,
						$this->diafan->value,
						$this->diafan->variable_name(),
						$this->diafan->help(),
						$this->diafan->variable_disabled(),
						$this->diafan->variable('', 'maxlength'),
						$this->diafan->variable('', 'select'),
						$this->diafan->variable('', 'select_db'),
						$this->diafan->variable('', 'depend'),
						$this->diafan->variable('', 'attr')
					);
		echo $this->diafan->helper();
	}

	/**
	 * Сохраняет настроек конфигурации модуля
	 *
	 * @return boolean
	 */
	public function save()
	{
		if(! empty($_POST["counter_delete_log"]))
		{
			if($this->diafan->configmodules('counter_enable', 'visitors'))
			{
				DB::query("DELETE FROM {config} WHERE module_name='%h' AND name='%h' AND site_id=%d AND (lang_id="._LANG." OR lang_id=0)", $this->diafan->_admin->module, 'counter_enable', $this->diafan->_route->site);
			}
			DB::query("TRUNCATE TABLE {visitors_session}");
			DB::query("TRUNCATE TABLE {visitors_url}");
		}
		if(! empty($_POST["counter_delete_stat"]))
		{
			if($this->diafan->configmodules('counter_enable', 'visitors'))
			{
				DB::query("DELETE FROM {config} WHERE module_name='%h' AND name='%h' AND site_id=%d AND (lang_id="._LANG." OR lang_id=0)", $this->diafan->_admin->module, 'counter_enable', $this->diafan->_route->site);
			}
			DB::query("TRUNCATE TABLE {visitors_stat_traffic}");
			DB::query("TRUNCATE TABLE {visitors_stat_traffic_source}");
			DB::query("TRUNCATE TABLE {visitors_stat_traffic_pages}");
			DB::query("TRUNCATE TABLE {visitors_stat_traffic_names_search_bot}");
		}
		if(! empty($_POST["counter_delete_valid"]))
		{
			$this->diafan->configmodules('counter_timeedit_installed', 'visitors', 0, 0, time());
		}
		parent::save();
	}
}
