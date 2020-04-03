<?php
/**
 * Редактирование параметров сайта
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
 * Config_admin
 */
class Config_admin extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'base' => array (
			'name' => array(
				'type' => 'none',
				'name' => 'Название сайта',
				'help' => 'Если на сайте используются несколько языковых версий, то поле «Название сайта» нужно заполнять для каждой версии.',
				'no_save' => true,
			),
			'hr1' => 'hr',
			'db_host' => array(
				'type' => 'text',
				'name' => 'Host для базы данных',
				'help' => 'Хост для подключения к базе данных. Например, localhost. Данные обычно предоставляются хостингом при регистрации.',
				'disabled' => true,
			),
			'db_name' => array(
				'type' => 'text',
				'name' => 'База данных',
				'help' => 'Название базы данных. Данные обычно предоставляются хостингом при регистрации или создается база данных в панеле управления хостингом. При создании базы данных рекомендуется выбирать кодировку UTF8.',
				'disabled' => true,
			),
			'db_user' => array(
				'type' => 'text',
				'name' => 'Пользователь базы данных',
				'help' => 'Данные обычно предоставляются хостингом при регистрации или создается база данных в панеле управления хостингом.',
				'disabled' => true,
			),
			'db_pass' => array(
				'type' => 'password',
				'name' => 'Пароль для базы данных',
				'help' => 'Данные обычно предоставляются хостингом при регистрации или создается база данных в панеле управления хостингом.',
				'disabled' => true,
			),
			'db_prefix' => array(
				'type' => 'text',
				'name' => 'Префикс (например, diafan_)',
				'help' => 'Символы, добавляемые к каждой таблице в базе данных, используемой CMS. Полезно, когда в одной базе данный MySQL имеются таблицы не только CMS. Префикс может быть пустым.',
				'disabled' => true,
			),
			'db_charset' => array(
				'type' => 'text',
				'name' => 'Кодировка базы данных',
				'help' => 'DIAFAN.CMS работает с базой данных в кодировке UTF8. Изменить параметр можно в случае индивидуальной настройки системы.',
				'disabled' => true,
			),
			'hr2' => 'hr',
			'userfiles' => array(
				'type' => 'text',
				'name' => 'Папка для хранения пользовательских файлов.',
				'help' => 'Имя папки, где будут храниться все загружаемые файлы для контента сайта.  По умолчанию все пользовательские файлы хранятся в папке *userfls*. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'admin_folder' => array(
				'type' => 'text',
				'name' => 'Папка административной части',
				'help' => 'Адрес административной части сайта. Например, *http://site.ru/admin/* или *http://site.ru/manager/*. Изменение параметра означает изменение URL-адреса панели администрирования. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
				'maxlength' => 20,
			),
			'hr22' => 'hr',
			'mobile_version' => array(
				'type' => 'checkbox',
				'name' => 'Использовать отдельный шаблон мобильной версии (при наличии)',
				'help' => 'Если отмечено, то CMS будет автоматически определять устройство, с которого зашли на сайт и если это мобильное устройство, то автоматически будет загружаться дополнительный шаблон дизайна themes/m/site.php.',
			),
			'mobile_path' => array(
				'type' => 'text',
				'name' => 'Имя мобильной версии в URL-адресе',
				'help' => 'Название, используемое в URL-адресе, в качестве адреса мобильной версии. Допустимо использование латиницы в нижнем регистре, а также символов тире и нижнего подчеркивания. Например, *http://site.ru/m/* или *http://site.ru/mobile/*. Изменение параметра означает изменение URL-адреса мобильной версии. При изменении параметра следует скорректировать содержание файла robots.txt по необходимости. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
				'default' => 'm',
				'maxlength' => 20,
				'depend' => 'mobile_version',
			),
			'mobile_subdomain' => array(
				'type' => 'checkbox',
				'name' => 'Использовать имя мобильной версии в качестве поддомена',
				'help' => 'Если отмечено, то название мобильной версии будет использоваться в качестве поддомена. Например, *http://m.site.ru/* или *http://mobile.site.ru/*. Изменение параметра означает изменение URL-адреса мобильной версии. Возможно Вам потребуется скорректировать файл robots.txt. ВАЖНО: требуется внесение соответствующих "CNAME" или "A" записей в dns-зону домена, а также изменение настроек веб-сервера. Например, для Apache параметр "ServerAlias", для NGINX параметр "server_name". Прежде, чем изменять параметр настройки, убедитесь, что имя мобильной версии не совпадает ни с одной из частей доменного имени. Например, для URL-адреса *http://site.ru/* в качестве имени мобильной версии нельзя использовать: *site* и *ru*. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
				'default' => false,
				'depend' => 'mobile_version',
			),
			'hr23' => 'hr',
			'no_x_frame' => array(
				'type' => 'checkbox',
				'name' => 'Запретить загружать сайт во frame',
				'help' => 'Если не отмечено, то сайт можно будет вставлять во frame. Повышается опасность clickjacking-атак на сайт.',
			),
			'hr3' => 'hr',
			'route_method' => array(
				'type' => 'select',
				'name' => 'Вариант генерации ЧПУ',
				'help' => 'Cпособ предобразования названия при автоматическом генерировании ЧПУ. ',
				'select' => array(
					1 => 'транслит',
					2 => 'перевод на английский',
					3 => 'русская кириллица',
				),
			),
			'route_translit_from' => array(
				'type' => 'textarea',
				'name' => 'Способ преобразования',
				'help' => 'Массив кириллических символов и соответствующих им латинских символов. Символы разделены пробелом. Параметр появляется, если в качестве варианта генерации ЧПУ выбран «транслит».',
			),
			'route_translit_to' => array(
				'type' => 'textarea',
				'hide' => true,
			),
			'route_translate_yandex_key' => array(
				'type' => 'text',
				'name' => 'API-ключ сервиса Яндекс Переводчик<br><a href="https://tech.yandex.ru/keys/get/?service=trnsl" target="_blank">Получить</a>',
			),
			'route_end' => array(
				'type' => 'text',
				'name' => 'ЧПУ оканчивается на',
				'help' => 'Можно использовать слеш или иное окончание. Например, если установить *.php*, все адреса страниц сайта будут формироваться как *http://site.ru/news.php* Для *.html* – *http://site.ru/news.html*. По умолчанию слеш и *http://site.ru/news/*.',
			),
			'route_auto_module' => array(
				'type' => 'checkbox',
				'name' => 'Генерировать ЧПУ для модулей автоматически',
				'help' => 'Формирование ЧПУ для модулей (новостей, категорий новостей, товаров, статей и пр.) в автоматическом режиме из названий. Если галка отключена, ЧПУ отдельного товара будет генерироваться как *http://site.ru/shop/cat1/show5/*. Если галка стоит, то при сохранении ЧПУ сгенерируется автоматически из названия категорий и имени элементов, т.е. *http://site.ru/shop/telefony/nokia8800/*.',
			),
			'hr4' => 'hr',
			'ftp_host' => array(
				'type' => 'text',
				'name' => 'FTP-хост',
				'help' => 'Адрес FTP-сервера, для подключения к хостингу. Используется для доступа к файлам сайта, если не хватает прав доступа. В том числе может быть использовано для автообновления. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'ftp_login' => array(
				'type' => 'text',
				'name' => 'FTP-логин',
				'help' => 'Имя ftp-пользователя, для подключения хостингу. Используется для доступа к файлам сайта, если не хватает прав доступа. В том числе может быть использовано для автообновления. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'ftp_password' => array(
				'type' => 'password',
				'name' => 'FTP-пароль',
				'help' => 'Пароль ftp-пользователя, для подключения хостингу. Используется для доступа к файлам сайта, если не хватает прав доступа. В том числе может быть использовано для автообновления. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'ftp_dir' => array(
				'type' => 'text',
				'name' => 'Относительный путь до сайта',
				'help' => 'Нужен, если указанный FTP-пользователь после авторизации попадает не в корень сайта, а неколькими уровнями выше. Тогда нужно указать путь к корню сайта. Например, */www/site.ru/*, узнайте на хостинге. Используется для доступа к файлам сайта, если не хватает прав доступа. В том числе может быть использовано для автообновления. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'hr5' => 'hr',
			'cache_memcached' => array(
				'type' => 'checkbox',
				'name' => 'Кэширование Memcached',
				'help' => 'Подключает Memcached-кэширование. По умолчанию используется файловое кэширование. Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!',
			),
			'cache_memcached_host' => array(
				'type' => 'text',
				'name' => 'Xост сервера Memcached',
				'help' => 'Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!',
				'depend' => 'cache_memcached',
			),
			'cache_memcached_port' => array(
				'type' => 'numtext',
				'name' => 'Порт сервера Memcached',
				'help' => 'Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!',
				'depend' => 'cache_memcached',
			),
			'hr6' => 'hr',
			'timezone' => array(
				'type' => 'text',
				'name' => 'Таймзона',
				'help' => 'Часовой пояс, [список часовых поясов](http://www.php.net/manual/en/timezones.php). По умолчанию: Europe/Moscow',
			),
			'hr7' => 'hr',
			'source_js' => array(
				'type' => 'select',
				'name' => 'Источник загрузки JS-библиотек',
				'help' => 'Вариант источника загрузки библиотек JavaScript. Влияет на скорость загрузки страниц сайта. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
				'select' => array(
					1 => 'Google CDN',
					2 => 'Yandex CDN',
					3 => 'Microsoft CDN',
					4 => 'CDNJS CDN',
					5 => 'jQuery CDN',
					6 => 'Hosting',
				),
			),
		),
		'mod_developer_tab' => array (
			'mod_developer' => array(
				'type' => 'checkbox',
				'name' => 'Включить режим разработки',
				'help' => 'Если отметить, в подвале всех страниц сайта будет выводиться консоль, содержащая все уведомления сервера с замечаниями и PHP-ошибками. Режим разработки также отключает сжатие CSS и JS файлов. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'mod_developer_admin' => array(
				'type' => 'checkbox',
				'name' => 'Показывать ошибки только администратору',
			),
			'mod_developer_tech' => array(
				'type' => 'checkbox',
				'name' => 'Перевести сайт в режим обслуживания',
				'help' => 'Если отметить, сайт будет доступен только авторизованному администратору. Все остальные посетители сайта будут видеть только страницу themes/503.php – «Сайт в разработке, временно недоступен». (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'mod_developer_cache' => array(
				'type' => 'checkbox',
				'name' => 'Отключить кэширование',
				'help' => 'Данный параметр разработчику необходимо обязательно вкючать при доработке скриптов и обязательно отключать в штатном режиме работы сайта. Постоянно отключенное кэширование может замедлить работу системы! (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'mod_developer_delete_cache' => array(
				'type' => 'checkbox',
				'name' => 'Сбросить кэш',
				'help' => 'Если отметить, внутренний кэш сайта будет удален. Галка при этом не останется отмечена. Рекомендуется сбрасывать кеш, после внесения изменений в скрипты. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'cache_extreme' => array(
				'type' => 'checkbox',
				'name' => '<a href="http://www.diafan.ru/highload/">Экстремальное кеширование</a>',
				'help' => 'Внимание! Возможно ограничение функционала! Используйте только после ознакомления с назначением данного параметра.',
			),
			'mod_developer_minify' => array(
				'type' => 'checkbox',
				'name' => 'Включить сжатие HTML-контента',
				'help' => 'Если отметить, сгенерированная HTML-страница будет сжиматься перед отправкой в веб-браузер клиента.',
			),
			'mod_developer_profiling' => array(
				'type' => 'checkbox',
				'name' => 'Включить профилирование SQL-запросов',
				'help' => 'Если отметить, в подвале всех страниц сайта будет выводиться консоль, содержащая список всех использованных системой SQL-запросов и время их выполнения. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'mod_developer_profiler' => array(
				'type' => 'checkbox',
				'name' => 'Включить профилирование PHP-скриптов',
				'help' => 'Если отметить, в подвале всех страниц сайта будет выводиться консоль, содержащая список всех профилированных системой PHP-скриптов и время их выполнения. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
			),
			'mod_developer_post' => array(
				'type' => 'checkbox',
				'name' => 'Включить профилирование POST-запроса',
				'help' => 'Если отметить, в подвале всех страниц сайта будет выводиться консоль, содержащая профилирование POST-запрос. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
				'depend' => 'mod_developer_profiler|mod_developer_profiling',
			),
		),
		'info_tab' => array (
			'info_block' => array(
				'type' => 'function',
				'name' => 'Информационное поле',
				'no_save' => true,
				'hide' => true,
			),
		),
	);

	/**
	 * @var array названия табов
	 */
	public $tabs_name = array(
		'base' => 'Основные',
		'mod_developer_tab' =>'Режим разработки',
		'info_tab' =>'Информация о сайте'
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'only_edit', // модуль состоит только из формы редактирования
		'tab_card', // использование вкладок
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		foreach ($this->diafan->_languages->all as $language)
		{
			$base = $this->variables['base'];
			$this->variables['base'] = array();
			$this->variables['base']['title_'.$language["id"]] = array(
				'type' => 'text',
				'name' => $this->diafan->_('Название сайта').($language["shortname"] ? ' ('.$language["shortname"].')' : ''),
				'help' => $this->diafan->_('Название сайта используется при автоматической генерации заголовков title для всех страниц сайта, а также для писем-уведомлений и пр.')
			);
			foreach ($base as $k => $v)
			{
				$this->variables['base'][$k] = $v;
			}
		}

		// поддержка старой версии config.php
		if(! defined('MOBILE_PATH')) define('MOBILE_PATH', 'm');
		if(! defined('MOBILE_SUBDOMAIN')) define('MOBILE_SUBDOMAIN', false);
		if(! defined('SOURCE_JS')) define('SOURCE_JS', 1);

		// запрещаем редактирование значения полей mobile_path, mobile_subdomain и source_js в демо-версии
		if(defined('IS_DEMO') && IS_DEMO)
		{
			$this->diafan->variable('mobile_path', 'disabled', true);
			$this->diafan->variable('mobile_subdomain', 'disabled', true);
			$this->diafan->variable('source_js', 'disabled', true);
		}
	}

	/**
	 * Выводит форму редактирования параметров сайта
	 * @return void
	 */
	public function edit()
	{
		if (file_exists(ABSOLUTE_PATH.'config.php') &&  ! is_writable(ABSOLUTE_PATH.'config.php'))
		{
			echo '<div class="error">'.$this->diafan->_('Установите права на запись (777) для файла конфигурации config.php').'</div>';
		}
		parent::__call('edit', array());
	}

	/**
	 * Якорь для ЧПУ
	 * @return void
	 */
	public function edit_variable_hr3()
	{
		echo '<h2><a name="url"></a>
					</h2>';
	}

	/**
	 * Редактирование поля "Информационное поле"
	 * @return void
	 */
	public function edit_variable_info_block()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			echo '<div class="error">'.$this->diafan->_('не доступно в демонстрационном режиме').'</div>';
			return;
		}

		$content = '';

		try
		{
			$marker = array(
				'good'      => '<i class="fa fa-check-circle" style="color: #acd373"></i>',
				'poor'      => '<i class="fa fa-times-circle" style="color: #ed1c24"></i>',
				'attention' => '<i class="fa fa-exclamation-triangle" style="color: #ff4a0b"></i>',
				'comment' => '<i class="fa fa-comment-o" style="color: #049DE0"></i>'
			);

			$php_version_min = 50200;               // PHP 5.2
			$php_version = 70000;                   // PHP 7.0
			$post_max_size = 8 * 1024 * 1024;       // TO_DO: дефолтные настройки PHP = 8M
			$max_input_vars = 1000;                 // TO_DO: дефолтные настройки PHP = 1000
			$upload_max_filesize = 2 * 1024 * 1024; // TO_DO: дефолтные настройки PHP = 2M
			$memory_limit = 128 * 1024 * 1024;      // TO_DO: дефолтные настройки PHP = 128M
			$max_execution_time = 600;              // TO_DO: дефолтные настройки PHP = 30

			if(! $phpinfo = $this->phpinfo_array())
			{
				$phpinfo = array();
			}

			$content .= '
				<h2>'.$this->diafan->_('Основные настройки').'</h2>';

			$content .= '
					<div class="infofield">'.$this->diafan->_('Система управления сайом').'</div>';
			$update_count = ! empty($_SESSION["update_count"]["value"]) ? (int) $_SESSION["update_count"]["value"] : 0;
			$update_message = $this->diafan->_('Доступно обновление: для установки необходимо перейти в %sраздел "Обновление CMS"%s.', '<a class="link" href="'.BASE_PATH_HREF.'update/">', '</a>');
			$content .= '
					<div>'.$marker[$update_count ? 'attention' : 'good'].' '.$this->diafan->_('Версия %sDIAFAN.CMS%s: %s', '<a class="link" href="http'.(IS_HTTPS ? "s" : '').'://www.diafan.ru/" target="_blank">', '</a>', '<b>'.Custom::version_core().'</b>').($update_count ? '<br>'.$update_message : '').'</div>';

			$content .= '
					<div class="infofield">'.$this->diafan->_('Системные папки и файлы').'</div>';
			$folders = array((defined('USERFILES') && USERFILES ? USERFILES : 'userfls'), 'cache', 'tmp', 'custom', 'return');
			foreach($folders as $f)
			{
				if(! File::is_writable($f))
				{
					$content .= '
					<div>'.$marker["poor"].' '.$this->diafan->_('Необходимо установить права 777 на папку %s', ' <b>/'.$f.'/</b>').'</div>';
				}
				else
				{
					$content .= '
					<div>'.$marker["good"].' '.$this->diafan->_('Папка %s', ' <b>/'.$f.'/</b>').'</div>';
				}
			}
			$files = array('install.php');
			foreach($files as $f)
			{
				if(file_exists(ABSOLUTE_PATH.$f))
				{
					$content .= '
					<div>'.$marker["poor"].' '.$this->diafan->_('Внимание! Файл %s не был удален. Удалите его прежде, чем продолжить.', ' <b>'.$f.'</b>').'</div>';
				}
				else
				{
					$content .= '';
				}
			}
			$files = array('config.php', 'index.html');
			foreach($files as $f)
			{
				if(! File::is_writable($f))
				{
					$content .= '
					<div>'.$marker["poor"].' '.$this->diafan->_('Необходимо установить права 777 на файл %s', ' <b>'.$f.'</b>').'</div>';
				}
				else
				{
					$content .= '
					<div>'.$marker["good"].' '.$this->diafan->_('Файл %s', ' <b>'.$f.'</b>').'</div>';
				}
			}

			$content .= '
					<div class="infofield">'.$this->diafan->_('Конфигурация хостинг').'</div>';
			if(isset($phpinfo["phpinfo"]["System"]))
			{
				$content .= '
					<div>'.$marker['comment'].' '.$this->diafan->_('Операционная система: %s', '<b>'.$phpinfo["phpinfo"]["System"].'</b>').'</div>';
			}
			$content .= '
					<div>'.$marker['comment'].' '.$this->diafan->_('Временная зона: %s', '<b>'.date_default_timezone_get().'</b>').'</div>';
			$content .= '
					<div>'.$marker['comment'].' '.$this->diafan->_('Дата: %s', '<strong id="date">'.date('d.m.Y', time()).'</strong>').'</div>';
			$content .= '
					<div>'.$marker['comment'].' '.$this->diafan->_('Время: %s', '<strong id="time">'.date('H:i:s', time()).'</strong>').'</div>';
			if(isset($phpinfo["phpinfo"]["Server API"]))
			{
				$content .= '
					<div>'.$marker['comment'].' '.$this->diafan->_('Веб-сервер: %s', '<b>'.$phpinfo["phpinfo"]["Server API"].'</b>').'</div>';
			}
			$content .= '
					<div>'.$marker[($this->diafan->version_php() < $php_version_min ? 'poor' : 'good')].' '.$this->diafan->_('Интерпретатор PHP: %s', '<b>'.phpversion().'</b>').'</div>';
			if ($this->diafan->version_php() < $php_version_min) // if (phpversion() < '5.2')
			{
				$content .= '
					<div>'.$marker["attention"].' '.$this->diafan->_('Необходима версия PHP выше %s%s%s, обратитесь к администратору сервера.', '<b>', $this->diafan->version_php(2), '</b>').'</div>';
			}
			// TO_DO: временно деактивирован код
			// if ($this->diafan->version_php() < $php_version)
			// {
			// 	$content .= '
			// 	<div>'.$marker["comment"].' '.$this->diafan->_('Рекомендованная версия PHP выше %s%s.%s%s, обратитесь к администратору сервера.', '<b>', (int) ($php_version / 10000), (($php_version % 10000) / 100), '</b>').'</div>';
			// }
			if(! function_exists('mysql_connect') && ! function_exists('mysqli_connect'))
			{
				$content .= '
					<div>'.$marker["poor"].' '.$this->diafan->_('Необходима поддержка %sMySQL%s', '<b>', '</b>').'</div>';
			}
			else
			{
				$content .= '
					<div>'.$marker["good"].' '.$this->diafan->_('Поддержка %sMySQL%s', '<b>', '</b>').'</div>';
			}
			$fp = @fsockopen('diafan.ru', 80);
			if(! $fp)
			{
				$content .= '
					<div>'.$marker["poor"].' '.$this->diafan->_('Необходима поддержка %sсокетов%s', '<b>', '</b>').'</div>';
			}
			else
			{
				$content .= '
					<div>'.$marker["good"].' '.$this->diafan->_('Поддержка %sсокетов%s', '<b>', '</b>').'</div>';
			}
			if(class_exists('ZipArchive'))
			{
				$content .= '
					<div>'.$marker["good"].' '.$this->diafan->_('Поддержка сжатия данных в %sZIP-архив%s', '<b>', '</b>').'</div>';
			}
			if($phpinfo)
			{
				if(! isset($phpinfo["curl"]))
				{
					$content .= '
					<div>'.$marker["poor"].' '.$this->diafan->_('Необходима поддержка %scURL%s', '<b>', '</b>').'</div>';
				}
				else
				{
					$content .= '
					<div>'.$marker["good"].' '.$this->diafan->_('Поддержка %scURL%s', '<b>', '</b>').(! empty($phpinfo["curl"]["Protocols"]) ? ': '.$phpinfo["curl"]["Protocols"] : '').'</div>';
				}
			}
			if(function_exists('ftp_ssl_connect'))
			{
				$content .= '
					<div>'.$marker["good"].' '.$this->diafan->_('Поддержка %sOpenSSL%s', '<b>', '</b>').'</div>';
			}
			$internal_encoding = mb_internal_encoding();
			$i_e = trim(str_replace('-', '', strtolower($internal_encoding)));
			// TO_DO: временно деактивирован код
			// if($i_e != 'utf8')
			// {
			// 	$content .= '
			// 		<div>'.$marker['attention'].' '.$this->diafan->_('Внутренняя кодировка: %s (рекомендуется: %s)', '<b>'.$internal_encoding.'</b>', '<b>'.'UTF-8'.'</b>').'</div>';
			// }
			// else
			// {
			// 	$content .= '
			// 		<div>'.$marker['good'].' '.$this->diafan->_('Внутренняя кодировка: %s', '<b>'.$internal_encoding.'</b>').'</div>';
			// }
			$content .= '
				<div>'.$marker['comment'].' '.$this->diafan->_('Внутренняя кодировка: %s', '<b>'.$internal_encoding.'</b>').'</div>';

			$protocol = $protocol_ssl = $protocol_tls = array();
			if($phpinfo)
			{
				$content .= '
					<div class="infofield">'.$this->diafan->_('Поддержка протоколов').'</div>';
				if(isset($phpinfo["phpinfo"]["Registered PHP Streams"]))
				{
					$protocol = array_merge($protocol, explode(', ', $phpinfo["phpinfo"]["Registered PHP Streams"]));
				}
				if(isset($phpinfo["phpinfo"]["Registered Stream Socket Transports"]))
				{
					$protocol = array_merge($protocol, explode(', ', $phpinfo["phpinfo"]["Registered Stream Socket Transports"]));
				}
				if(isset($phpinfo["Core"]["SMTP"]))
				{
					$protocol = array_merge($protocol, array('smtp'));
				}
				$protocol = array_unique($protocol);
				foreach($protocol as $key => $value)
				{
					if(false !== strpos($value, 'ssl')) $protocol_ssl[] = $value;
					if(false !== strpos($value, 'tls')) $protocol_tls[] = $value;
				}
				$content .= '
					<div>'.$marker[(! in_array('http', $protocol) ? 'comment' : 'good')].' '.$this->diafan->_('HTTP').' '.(! in_array('http', $protocol) ? $this->diafan->_('не поддерживается') : '').'</div>';
				$content .= '
					<div>'.$marker[(! in_array('https', $protocol) ? 'poor' : 'good')].' '.$this->diafan->_('HTTPS').' '.(! in_array('https', $protocol) ? $this->diafan->_('не поддерживается') : '').'</div>';
				$content .= '
					<div>'.$marker[(! in_array('smtp', $protocol) ? 'comment' : 'good')].' '.$this->diafan->_('SMTP').' '.(! in_array('smtp', $protocol) ? $this->diafan->_('не поддерживается') : '').'</div>';

				$content .= '
					<div>'.$marker[(empty($protocol_ssl) ? 'comment' : 'good')].' '.$this->diafan->_('SSL').(empty($protocol_ssl) ? ' '.$this->diafan->_('не поддерживается') : ': '.implode(", ", $protocol_ssl)).'</div>';
				$content .= '
					<div>'.$marker[(empty($protocol_tls) ? 'comment' : 'good')].' '.$this->diafan->_('TLS').(empty($protocol_tls) ? ' '.$this->diafan->_('не поддерживается') : ': '.implode(", ", $protocol_tls)).'</div>';
			}

			$content .= '
				<h2>'.$this->diafan->_('Конфигурация PHP').'</h2>';
			$content .= '
				<div class="infofield">'.$this->diafan->_('Доступные модули PHP').'</div>';
			$loaded_extensions = get_loaded_extensions(true);
			if(! empty($loaded_extensions))
			{
				$loaded_extensions = array_merge(get_loaded_extensions(), $loaded_extensions);
				$loaded_extensions = array_unique($loaded_extensions);
			}
			else $loaded_extensions = get_loaded_extensions();
			$key = 'loaded_extensions'; $help = implode(", ", $loaded_extensions);
			$content .= '
			<div class="helper">
				<input type="checkbox" id="'.$key.'_helper'.'" class="checkbox hide"/>
				<label for="'.$key.'_helper'.'" class="btn btn_blue btn_small btn_helper">'.$this->diafan->_('Посмотреть').'</label>
				<div>'.$help.'</div>
			</div>';

			$ini = array(); $disable_functions = array();
			$ini_all = function_exists('ini_get_all') ? ini_get_all(null, false) : false;
			if($ini_all)
			{
				$content .= '
				<div class="infofield">'.$this->diafan->_('Настройки PHP').'</div>';
				foreach ($ini_all as $key => $value)
				{
					if(empty($value)) continue;
					switch($key)
					{
						case 'disable_functions':
							$disable_functions = explode(',', ini_get('disable_functions'));
							foreach($disable_functions as $k => $val)
							{
								$val = trim($val);
								if(empty($val)) unset($disable_functions[$k]);
								else $disable_functions[$k] = $val;
							}
							break;

						case 'post_max_size':
						case 'max_input_vars':
						case 'upload_max_filesize':
						case 'max_file_uploads':
						case 'memory_limit':
						case 'max_execution_time':
							break;

						default:
							$ini[] = $key.'='.$value;
							break;
					}
				}
				$ini = ! empty($ini) ? $ini : $ini[] = $this->diafan->_('отсутствуют');
				$key = 'ini'; $help = implode("<br>", $ini);
				$content .= '
				<div class="helper">
					<input type="checkbox" id="'.$key.'_helper'.'" class="checkbox hide"/>
					<label for="'.$key.'_helper'.'" class="btn btn_blue btn_small btn_helper">'.$this->diafan->_('Посмотреть').'</label>
					<div>'.$help.'</div>
				</div>';
			}
			else
			{
				$ini[] = $this->diafan->_('Информация недоступна.');
				$disable_functions[] = $this->diafan->_('Информация недоступна.');
			}

			$content .= '
				<h2>'.$this->diafan->_('Содержание .htaccess').'</h2>';
			if(! $htaccess = file_get_contents(ABSOLUTE_PATH.'.htaccess'))
			{
				$htaccess = $this->diafan->_('отсутствует.');
			}
			$key = 'htaccess'; $help = '<pre>'.htmlspecialchars($htaccess).'</pre>';
			$content .= '
			<div class="helper">
				<input type="checkbox" id="'.$key.'_helper'.'" class="checkbox hide"/>
				<label for="'.$key.'_helper'.'" class="btn btn_blue btn_small btn_helper">'.$this->diafan->_('Посмотреть').'</label>
				<div>'.$help.'</div>
			</div>';

			if(function_exists('mysql_connect') || function_exists('mysqli_connect'))
			{
				$content .= '
					<h2>'.$this->diafan->_('Режим работы SQL').'</h2>';
				$sql_mode = DB::mode();
				$key = 'sql_mode'; $help = implode(", ", $sql_mode);
				$content .= '
				<div class="helper">
					<input type="checkbox" id="'.$key.'_helper'.'" class="checkbox hide"/>
					<label for="'.$key.'_helper'.'" class="btn btn_blue btn_small btn_helper">'.$this->diafan->_('Посмотреть').'</label>
					<div>'.$help.'</div>
				</div>';
			}

			$content .= '
				<h2>'.$this->diafan->_('Ограничения хостинга').' <i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Для изменения лимитов необходимо обратится к администратору хостинга.').'"></i></h2>';
			$content .= '
				<div>'.$marker["good"].' '.$this->diafan->_('Максимальный размер передаваемых данных: %s', '<b>'.$this->diafan->convert($this->convert(ini_get('post_max_size'))).'</b>').'</div>';
			if($post_max_size > $this->convert(ini_get('post_max_size')))
			{
				$content .= '
				<div>'.$marker["comment"].' '.$this->diafan->_('Рекомендованный размер передаваемых данных: %s', '<b>'.$this->diafan->convert($post_max_size).'</b>').'</div>';
			}
			$content .= '
				<div>'.$marker["good"].' '.$this->diafan->_('Максимальный количество передаваемых значений: %s', '<b>'.ini_get('max_input_vars').'</b>').'</div>';
			if ($max_input_vars > (int) ini_get('max_input_vars'))
			{
				$content .= '
				<div>'.$marker["comment"].' '.$this->diafan->_('Рекомендованное количество передаваемых значений: %s', '<b>'.$max_input_vars.'</b>').'</div>';
			}
			$content .= '
				<div>'.$marker["good"].' '.$this->diafan->_('Максимальный размер загружаемого файла: %s', '<b>'.$this->diafan->convert($this->convert(ini_get('upload_max_filesize'))).'</b>').'</div>';
			if($upload_max_filesize > $this->convert(ini_get('upload_max_filesize')))
			{
				$content .= '
				<div>'.$marker["comment"].' '.$this->diafan->_('Рекомендованный размер загружаемого файла: %s', '<b>'.$this->diafan->convert($upload_max_filesize).'</b>').'</div>';
			}
			$content .= '
				<div>'.$marker[(1 > (int) ini_get('max_file_uploads') ? 'poor' : 'good')].' '.$this->diafan->_('Максимальное количество загружаемых файлов: %s', '<b>'.ini_get('max_file_uploads').'</b>').'</div>';
			if (1 > (int) ini_get('max_file_uploads'))
			{
				$content .= '
				<div>'.$marker["attention"].' '.$this->diafan->_('Необходимо разрешить загрузку хотябы %sодного%s файла.', '<b>', '</b>').'</div>';
			}
			$content .= '
				<div>'.$marker["good"].' '.$this->diafan->_('Максимальный размер выделяемой памяти: %s', '<b>'.$this->diafan->convert($this->convert(ini_get('memory_limit'))).'</b>').'</div>';
			if($memory_limit > $this->convert(ini_get('memory_limit')))
			{
				$content .= '
				<div>'.$marker["comment"].' '.$this->diafan->_('Рекомендованный размер выделяемой памяти: %s', '<b>'.$this->diafan->convert($memory_limit).'</b>').'</div>';
			}
			$content .= '
				<div>'.$marker["good"].' '.$this->diafan->_('Максимальное время на выполнение скрипта: %s %sсек.%s', '<b>'.ini_get('max_execution_time').'</b>', '<b>', '</b>').'</div>';
			if ($max_execution_time > (int) ini_get('max_execution_time'))
			{
				$content .= '
				<div>'.$marker["comment"].' '.$this->diafan->_('Рекомендованное время на выполнение скрипта от %s %sсек.%s', '<b>'.$max_execution_time.'</b>', '<b>', '</b>').'</div>';
			}
			if(! class_exists('ZipArchive'))
			{
				$content .= '
				<p>'.$marker["comment"].' '.$this->diafan->_('Cжатие данных в ZIP-архив не поддерживается, так как на сервере не установлено расширение для распоковки ZIP-архивов.').'</p>';
			}
			if(! function_exists('ftp_ssl_connect'))
			{
				$content .= '
				<p>'.$marker["comment"].' '.$this->diafan->_('Использование SMTP не поддерживается, так как PHP был собран без поддержкой OpenSSL').'</p>';
			}

			// TO_DO: временно деактивирован код
			// if($this->convert(ini_get('post_max_size')) != $this->convert(ini_get('upload_max_filesize')))
			// {
			// 	$content .= '
			// 	<p>'.$marker["attention"].' '.$this->diafan->_('%sОбратите внимание%s. Общий размер загружаемых файлов должен соответствовать <b>наименьшему</b> пределу: передаваемых данных %s или загружаемого файла %s.', '<b>', '</b>', '<b>'.$this->diafan->convert($this->convert(ini_get('post_max_size'))).'</b>', '<b>'.$this->diafan->convert($this->convert(ini_get('upload_max_filesize'))).'</b>').'</p>';
			// }

			if($ini_all)
			{
				$content .= '
				<div class="infofield">'.$this->diafan->_('Отключенные функции PHP').'</div>';
				$key = 'disable_functions'; $help = implode(", ", $disable_functions);
				$content .= '
				<div class="helper">
					<input type="checkbox" id="'.$key.'_helper'.'" class="checkbox hide"/>
					<label for="'.$key.'_helper'.'" class="btn btn_blue btn_small btn_helper">'.$this->diafan->_('Посмотреть').'</label>
					<div>'.$help.'</div>
				</div>';
			}

			$content .= '
				<p>'.$marker["comment"].' '.$this->diafan->_('Для изменения настроек / увеличения лимитов используйте %s или %s.', 'php.ini', '.htaccess').'</p>';

			$content .= '
				<h2>'.$this->diafan->_('Используемые ресурсы').' <i class="tooltip fa fa-refresh" title="'.$this->diafan->_('Пересчитать.').'" id="size_refresh"></i></h2>';
			if(function_exists('disk_total_space') && function_exists('disk_free_space'))
			{
				$disc_space = disk_total_space(ABSOLUTE_PATH);
				$disc_free = disk_free_space(ABSOLUTE_PATH);
				if($disc_space && $disc_free)
				{
					$content .= '
				<div>'.$this->diafan->_('Дисковое пространство: %s (свободно %s %%)', '<b>'.$this->diafan->convert($disc_space).'</b>', '<b>'.round(($disc_free*100/$disc_space), 2).'</b>').'</div>
					';
				}
			}
			$content .= '
				<div>'.$this->diafan->_('Общий размер файлов: %s... .%s %s', '<strong id="files_size">', '</strong>', '<img src="'.BASE_PATH.'adm/img/loading.gif"  class="hide">').'</div>
				<div>'.$this->diafan->_('из них пользовательских: %s... .%s %s', '<strong id="user_files_size">', '</strong>', '<img src="'.BASE_PATH.'adm/img/loading.gif"  class="hide">').'</div>
				<div>'.$this->diafan->_('Общий размер базы данных: %s... .%s %s', '<strong id="db_size">', '</strong>', '<img src="'.BASE_PATH.'adm/img/loading.gif" class="hide">').'</div>';
		}
		catch (Exception $e)
		{
			$content = '<div class="error">'.$this->diafan->_('Эта функция не доступна из-за ограничений на хостинге.').'</div>';
		}

		echo $content;
	}

	/**
	 * Конвертирует количество килобайт, мегабайт, гигабайт в байты
	 *
	 * @param string $size размер
	 * @return integer
	 */
	private function convert($size)
	{
		if(! $size)
		{
			return 0;
		}
		$result = (int) $size;
		if((string) $size == (string) $result)
		{
			return $result;
		}
		$measure = trim(str_replace((string) $result, '', $size));
		if(strlen($measure) != 1)
		{
			return $result;
		}
		switch ($measure) {
		// TO_DO: в некоторых настройках PHP допускаются помимо числовых значений
		// следующие регистрозависимые сокращения: 'K', 'M', 'G'
			case 'K':
				$multiplier = 1024;
				break;
			case 'M':
				$multiplier = 1024 * 1024;
				break;

			case 'G':
				$multiplier = 1024 * 1024 * 1024;
				break;

			default:
				$multiplier = 1;
				break;
		}
		return $result * $multiplier;
	}

	/**
	 * Возвращает содержание phpinfo в виде массива
	 *
	 * @return array
	 */
	private function phpinfo_array()
	{
		if(! function_exists('phpinfo'))
		{
			return false;
		}
		// TO_DO: PHP only supports $this inside anonymous functions since 5.4
		$php_version_min = 50400; // PHP 5.4
		if ($this->diafan->version_php() < $php_version_min)
		{
			return false;
		}

		$entitiesToUtf8 = function($input)
		{
      return preg_replace_callback(
				"/(&#[0-9]+;)/",
				function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); },
				$input
			);
    };
    $plainText = function($input) use ($entitiesToUtf8)
		{
      return trim(html_entity_decode($entitiesToUtf8(strip_tags($input))));
    };
    $titlePlainText = function($input) use ($plainText)
		{
      return '# '.$plainText($input);
    };

    ob_start();
    $result = phpinfo(-1);
		$phpinfo_content = ob_get_clean();

		if($result == false)
		{
			return false;
		}

		$phpinfo = array('phpinfo' => array());

    // Strip everything after the <h1>Configuration</h1> tag (other h1's)
    if(! preg_match('#(.*<h1[^>]*>\s*Configuration.*)<h1#s', $phpinfo_content, $matches))
		{
      return array();
    }

    $input = $matches[1];
    $matches = array();

    if(preg_match_all(
      '#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|'.
      '(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
      $input,
      $matches,
      PREG_SET_ORDER
    ))
		{
      foreach ($matches as $match)
			{
        $fn = strpos($match[0], '<th') === false ? $plainText : $titlePlainText;
        if(strlen($match[1]))
				{
          $phpinfo[$match[1]] = array();
        }
				elseif(isset($match[3]))
				{
          $keys1 = array_keys($phpinfo);
          $phpinfo[end($keys1)][$fn($match[2])] = isset($match[4]) ? array($fn($match[3]), $fn($match[4])) : $fn($match[3]);
        }
				else
				{
          $keys1 = array_keys($phpinfo);
          $phpinfo[end($keys1)][] = $fn($match[2]);
        }
      }
    }
    return $phpinfo;
	}

	/**
	 * Задает значения полей для формы
	 *
	 * @return array
	 */
	public function get_values()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			$values = array(
				'db_prefix' => ! empty($_SESSION["CONFIG_DB_PREFIX"]) ? $_SESSION["CONFIG_DB_PREFIX"] : 'diafan_',
				'db_url' => ! empty($_SESSION["CONFIG_DB_URL"]) ? $_SESSION["CONFIG_DB_URL"] : 'mysqli://user:pass@localhost/dbname',
				'db_charset' => ! empty($_SESSION["CONFIG_DB_CHARSET"]) ? $_SESSION["CONFIG_DB_CHARSET"] : 'utf8',
				'userfiles' => ! empty($_SESSION["CONFIG_USERFILES"]) ? $_SESSION["CONFIG_USERFILES"] : 'userfls',
				'admin_folder' => ! empty($_SESSION["CONFIG_ADMIN_FOLDER"]) ? $_SESSION["CONFIG_ADMIN_FOLDER"] : 'admin',
				'no_x_frame' =>  ! empty($_SESSION["CONFIG_NO_X_FRAME"]) ? true : false,
				'mod_developer' => ! empty($_SESSION["CONFIG_MOD_DEVELOPER"]) ? true : false,
				'mod_developer_tech' => ! empty($_SESSION["CONFIG_MOD_DEVELOPER_TECH"]) ? true : false,
				'mod_developer_minify' => ! empty($_SESSION["CONFIG_MOD_DEVELOPER_MINIFY"]) ? true : false,
				'mod_developer_profiling' => ! empty($_SESSION["CONFIG_MOD_DEVELOPER_PROFILING"]) ? true : false,
				'mod_developer_profiler' => ! empty($_SESSION["CONFIG_MOD_DEVELOPER_PROFILER"]) ? true : false,
				'mod_developer_post' => ! empty($_SESSION["CONFIG_MOD_DEVELOPER_POST"]) ? true : false,
				'mod_developer_cache' => ! empty($_SESSION["CONFIG_MOD_DEVELOPER_CACHE"]) ? true : false,
				'cache_extreme' => ! empty($_SESSION["CONFIG_CACHE_EXTREME"]) ? true : false,
				'ftp_host' => ! empty($_SESSION["CONFIG_FTP_HOST"]) ? $_SESSION["CONFIG_FTP_HOST"] : '',
				'ftp_login' => ! empty($_SESSION["CONFIG_FTP_LOGIN"]) ? $_SESSION["CONFIG_FTP_LOGIN"] : '',
				'ftp_password' => ! empty($_SESSION["CONFIG_FTP_PASSWORD"]) ? $_SESSION["CONFIG_FTP_PASSWORD"] : '',
				'ftp_dir' => ! empty($_SESSION["CONFIG_FTP_DIR"]) ? $_SESSION["CONFIG_FTP_DIR"] : '',
				'mobile_path' => ! empty($_SESSION["CONFIG_MOBILE_PATH"]) ? $_SESSION["CONFIG_MOBILE_PATH"] : 'm',
				'mobile_subdomain' => ! empty($_SESSION["CONFIG_MOBILE_SUBDOMAIN"]) ? true : false,
				'source_js' => ! empty($_SESSION["CONFIG_SOURCE_JS"]) ? $_SESSION["CONFIG_SOURCE_JS"] : 1,
			);
		}
		else
		{
			$values = array(
				'db_prefix' => DB_PREFIX,
				'db_url' => DB_URL,
				'db_charset' => DB_CHARSET,
				'userfiles' => USERFILES,
				'admin_folder' => ADMIN_FOLDER,
				'no_x_frame' => NO_X_FRAME,
				'mod_developer' => MOD_DEVELOPER,
				'mod_developer_admin' => defined('MOD_DEVELOPER_ADMIN') ? MOD_DEVELOPER_ADMIN : false,
				'mod_developer_tech' => MOD_DEVELOPER_TECH,
				'mod_developer_minify' => defined('MOD_DEVELOPER_MINIFY') ? MOD_DEVELOPER_MINIFY : false,
				'mod_developer_profiling' => defined('MOD_DEVELOPER_PROFILING') ? MOD_DEVELOPER_PROFILING : false,
				'mod_developer_profiler' => defined('MOD_DEVELOPER_PROFILER') ? MOD_DEVELOPER_PROFILER : false,
				'mod_developer_post' => defined('MOD_DEVELOPER_POST') ? MOD_DEVELOPER_POST : false,
				'mod_developer_cache' => MOD_DEVELOPER_CACHE,
				'cache_extreme' => defined('CACHE_EXTREME') ? CACHE_EXTREME : false,
				'ftp_host' => FTP_HOST,
				'ftp_login' => FTP_LOGIN,
				'ftp_password' => FTP_PASSWORD,
				'ftp_dir' => FTP_DIR,
				'mobile_path' => defined('MOBILE_PATH') ? MOBILE_PATH : 'm',
				'mobile_subdomain' => defined('MOBILE_SUBDOMAIN') ? MOBILE_SUBDOMAIN : false,
				'source_js' => defined('SOURCE_JS') ? SOURCE_JS : 1,
			);
		}
		$url = parse_url($values['db_url']);

		$translit_array = explode('````', DB::query_result("SELECT value FROM {config} WHERE module_name='route' AND name='translit_array' LIMIT 1"), 2);
		$array = array(
			'db_host'                    => urldecode($url['host']).(! empty($url['port']) ? ':'.$url['port'] : ''),
			'db_user'                    => urldecode($url['user']),
			'db_pass'                    => isset($url['pass']) ? urldecode($url['pass']) : '',
			'db_name'                    => substr(urldecode($url['path']), 1),
			'db_prefix'                  => $values['db_prefix'],
			'db_charset'                 => $values['db_charset'],
			'userfiles'                  => $values['userfiles'],
			'admin_folder'               => $values['admin_folder'],
			'mobile_version'             => MOBILE_VERSION,
			'mobile_path'                => $values['mobile_path'],
			'mobile_subdomain'           => $values['mobile_subdomain'],
			'source_js'                  => $values['source_js'],
			'no_x_frame'                 => $values['no_x_frame'],
			'mod_developer'              => $values['mod_developer'],
			'mod_developer_admin'        => $values['mod_developer_admin'],
			'mod_developer_tech'         => $values['mod_developer_tech'],
			'mod_developer_minify'       => $values['mod_developer_minify'],
			'mod_developer_profiling'    => $values['mod_developer_profiling'],
			'mod_developer_profiler'     => $values['mod_developer_profiler'],
			'mod_developer_post'         => $values['mod_developer_post'],
			'mod_developer_cache'        => $values['mod_developer_cache'],
			'cache_extreme'              => $values['cache_extreme'],
			'mod_developer_delete_cache' => false,

			'route_method'               => DB::query_result("SELECT value FROM {config} WHERE module_name='route' AND name='method' LIMIT 1"),
			'route_translit_from'        => $translit_array[0],
			'route_translit_to'          => ! empty($translit_array[1]) ? $translit_array[1] : '',
			'route_end'                  => ROUTE_END,
			'route_auto_module'          => ROUTE_AUTO_MODULE,
			'route_translate_yandex_key' => DB::query_result("SELECT value FROM {config} WHERE module_name='route' AND name='translate_yandex_key' LIMIT 1"),

			'ftp_host'                   => FTP_HOST,
			'ftp_login'                  => FTP_LOGIN,
			'ftp_password'               => FTP_PASSWORD,
			'ftp_dir'                    => FTP_DIR,

			'cache_memcached'            => CACHE_MEMCACHED,
			'cache_memcached_host'       => CACHE_MEMCACHED_HOST,
			'cache_memcached_port'       => CACHE_MEMCACHED_PORT,

			'timezone'                   => defined('TIMEZONE') ? TIMEZONE : '',
		);

		foreach ($this->diafan->_languages->all as $language)
		{
			$array['title_'.$language["id"]] = (defined('TIT'.$language["id"]) ? constant('TIT'.$language["id"]) : '');
		}

		return $array;

	}

	/**
	 * Проверка параметров подключения к Memcached
	 *
	 * @return void
	 */
	public function validate_variable_cache_memcached()
	{
		if(! empty($_POST["cache_memcached"]))
		{
			if(! class_exists('Memcached'))
			{
				$this->diafan->set_error("cache_memcached", "Не установлен модуль Memcached для PHP.");
			}
			elseif(empty($_POST["cache_memcached_host"]) || empty($_POST["cache_memcached_port"]))
			{
				$this->diafan->set_error("cache_memcached", "Укажите хост и порт сервера Memcached.");
			}
			else
			{
				Custom::inc('includes/cache.php');
				Custom::inc('includes/cache/cache.memcached.php');
				if(! Cache_memcached::check($_POST["cache_memcached_host"], $_POST["cache_memcached_port"]))
				{
					$this->diafan->set_error("cache_memcached", "Не верные параметры подключения.");
				}
			}
		}
	}

	/**
	 * Проверка параметров подключения по FTP
	 *
	 * @return void
	 */
	public function validate_variable_ftp()
	{
		if(! empty($_POST["ftp_host"]))
		{
			if(! extension_loaded('ftp'))
			{
				$this->diafan->set_error("ftp_host", "Не установлено PHP-расширение для работы с FTP.");
			}
			if(empty($_POST["ftp_login"]))
			{
				$this->diafan->set_error("ftp_login", "Укажите имя пользователя для подключения по FTP.");
			}
			if(empty($_POST["ftp_password"]))
			{
				$this->diafan->set_error("ftp_password", "Укажите пароль для подключения по FTP.");
			}
			if(! empty($_POST["ftp_login"]) && ! empty($_POST["ftp_password"]))
			{
				$host = $_POST["ftp_host"];
				$port = null;
				if(strpos($host, ':') !== false)
				{
					list($host, $port) = explode(':', $_POST["ftp_host"], 2);
				}
				if(! $conn_id = ftp_connect($host, $port))
				{
					$this->diafan->set_error("ftp_host", "Ошибка подключения по FTP. Хост не найден.");
				}
				elseif(! ftp_login($conn_id, $_POST["ftp_login"], $_POST["ftp_password"]))
				{
					ftp_close($conn_id);
					$this->diafan->set_error("ftp_host", 'Ошибка подключения по FTP. Указаны неверные данные для подлкючения.');
				}
				else
				{
					ftp_pasv($conn_id, true);
					if (! ftp_chdir($conn_id, $_POST["ftp_dir"]))
					{
						$this->diafan->set_error("ftp_dir", 'Неправильно задан относительный путь.');
					}
					ftp_close($conn_id);
				}
			}
		}
	}

	/**
	 * Валидация имени папки
	 *
	 * @return void
	 */
	public function validate_variable_admin_folder()
	{
		if(strpos($_POST["admin_folder"], '/') !== false)
		{
			$this->diafan->set_error("admin_folder", "Символ / не доступстим в названии папки");
		}
	}

	/**
	 * Проверка имени мобильной версии в url-адресе
	 *
	 * @return void
	 */
	public function validate_variable_mobile_path()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			if(empty($_POST['mobile_path']) || $_POST['mobile_path'] != 'm')
			{
				$this->diafan->set_error("mobile_path", "Изменение имени мобильной версии в URL-адресе в демо-версии не доступно.");
			}
		}
		elseif(! empty($_POST["mobile_version"]))
		{
			if(empty($_POST['mobile_path']) || preg_match('/[^a-z0-9-_]+/', $_POST['mobile_path']))
			{
				$this->diafan->set_error("mobile_path", "Укажите корректное имя мобильной версии в URL-адресе.");
			}
			elseif(! empty($_POST['mobile_subdomain']))
			{
				$rew = explode('.', MAIN_DOMAIN);
				if(false !== array_search($_POST['mobile_path'], $rew))
				{
					$this->diafan->set_error("mobile_path", "Имя мобильной версии в URL-адресе не должно совпадать ни с одной из частей доменного имени.");
				}
				else
				{
					$url = 'http'.(IS_HTTPS ? "s" : '').'://'.$_POST['mobile_path'].'.'.MAIN_DOMAIN;
					$answer = $this->diafan->get_http_status($url);
					if (Core::HTTP_OK != $answer && Core::HTTP_SERVICE_UNAVAILABLE != $answer)
					{
						$this->diafan->set_error("mobile_path", "Имя мобильной версии в URL-адресе не поддерживается хостингом.");
						$this->diafan->set_error("mobile_subdomain", 'HTTP status code: '.$answer);
					}
				}
			}
		}
	}

	/**
	 * Проверка источника загрузки JS-библиотек
	 *
	 * @return void
	 */
	public function validate_variable_source_js()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			if(empty($_POST['source_js']) || $_POST['source_js'] != 1)
			{
				$this->diafan->set_error("source_js", "Изменение источника загрузки JS-библиотек в демо-версии не доступно.");
			}
		}
	}

	/**
	 * Проверка использования имени мобильной версии в качестве поддомена
	 *
	 * @return void
	 */
	public function validate_variable_mobile_subdomain()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			if(! empty($_POST['mobile_subdomain']))
			{
				$this->diafan->set_error("mobile_subdomain", "Использования имени мобильной версии в качестве поддомена в демо-версии не доступно.");
			}
		}
	}

	/**
	 * Сохраняет файл конфигурации
	 *
	 * @return boolean
	 */
	public function save()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return false;
		}

		//проверка прав на сохранение
		if (! $this->diafan->_users->roles('edit', 'config'))
		{
			$this->diafan->redirect(URL);
			return false;
		}

		$dir_url_path = '';

		if (getenv('REQUEST_URI') != "/".ADMIN_FOLDER."/config/save1/")
		{
			$dir_url_path = str_replace("/".ADMIN_FOLDER."/config/save1/", "", getenv('REQUEST_URI'));
		}

		$admin_folder = substr($this->diafan->filter($_POST, "string", "admin_folder", ADMIN_FOLDER, 1), 0, 20);

		$mobile_path = substr(preg_replace('/[^a-z0-9-_]+/', '', $this->diafan->filter($_POST, "string", "mobile_path", MOBILE_PATH, 1)), 0, 20);
		if(! empty($_POST['mobile_subdomain']))
		{
			$rew = explode('.', MAIN_DOMAIN);
			$mobile_path = false === array_search($mobile_path, $rew) ? $mobile_path : '';
		}
		// запрещаем запись некорректного значения для MOBILE_PATH
		$mobile_path = ! empty($mobile_path) ? $mobile_path : 'm';

		$new_values = array(
				'DB_URL' => str_replace('"', '\\"', DB_URL),
				'DB_PREFIX' => DB_PREFIX,
				'USERFILES' => $this->diafan->filter($_POST, "string", "userfiles", USERFILES),
				'ADMIN_FOLDER' => $admin_folder,
				'MOBILE_VERSION' => (! empty($_POST["mobile_version"]) ? true : false),
				'MOBILE_PATH' => $mobile_path,
				'MOBILE_SUBDOMAIN' => (! empty($_POST["mobile_subdomain"]) ? true : false),
				'SOURCE_JS' => (! empty($_POST["source_js"]) ? $this->diafan->filter($_POST, "integer", "source_js", 1) : 1),
				'NO_X_FRAME' => (! empty($_POST["no_x_frame"]) ? true : false),
				'MOD_DEVELOPER' => (! empty($_POST["mod_developer"]) ? true : false),
				'MOD_DEVELOPER_ADMIN' => (! empty($_POST["mod_developer_admin"]) ? true : false),
				'MOD_DEVELOPER_TECH' => (! empty($_POST["mod_developer_tech"]) ? true : false),
				'MOD_DEVELOPER_CACHE' => (! empty($_POST["mod_developer_cache"]) ? true : false),
				'CACHE_EXTREME' => (! empty($_POST["cache_extreme"]) ? true : false),
				'MOD_DEVELOPER_MINIFY' => (! empty($_POST["mod_developer_minify"]) ? true : false),
				'MOD_DEVELOPER_PROFILING' => (! empty($_POST["mod_developer_profiling"]) ? true : false),
				'MOD_DEVELOPER_PROFILER' => (! empty($_POST["mod_developer_profiler"]) ? true : false),
				'MOD_DEVELOPER_POST' => (! empty($_POST["mod_developer_post"]) ? true : false),
				'FTP_HOST' => $this->diafan->filter($_POST, "string", "ftp_host", FTP_HOST),
				'FTP_DIR' => $this->diafan->filter($_POST, "string", "ftp_dir", FTP_DIR),
				'FTP_LOGIN' => $this->diafan->filter($_POST, "string", "ftp_login", FTP_LOGIN),
				'FTP_PASSWORD' => $this->diafan->filter($_POST, "string", "ftp_password", FTP_PASSWORD),
				'CACHE_MEMCACHED' => (class_exists('Memcached') && ! empty($_POST["cache_memcached"]) ? true : false),
				'CACHE_MEMCACHED_HOST' => $this->diafan->filter($_POST, "string", "cache_memcached_host"),
				'CACHE_MEMCACHED_PORT' => $this->diafan->filter($_POST, "string", "cache_memcached_port"),
				'TIMEZONE' => $this->diafan->filter($_POST, "string", "timezone"),
				'ROUTE_END' => $this->diafan->filter($_POST, "string", "route_end"),
				'ROUTE_AUTO_MODULE' => (! empty($_POST["route_auto_module"]) ? true : false),
				'CUSTOM' => implode(',', Custom::names()),
			);
		foreach ($this->diafan->_languages->all as $language)
		{
			$new_values['TIT'.$language["id"]] = $this->diafan->filter($_POST, "string", "title_".$language["id"]);
		}
		$route_method = DB::query_fetch_array("SELECT id, value FROM {config} WHERE module_name='route' AND name='method' LIMIT 1");
		if(! $route_method)
		{
			DB::query("INSERT INTO {config} (module_name, name, value) VALUES ('route', 'method', '%d')", $_POST["route_method"]);
		}
		elseif($route_method["value"] != $_POST["route_method"])
		{
			DB::query("UPDATE {config} SET value='%d' WHERE module_name='route' AND name='method'", $_POST["route_method"]);
		}

		$route_translit_array = DB::query_fetch_array("SELECT id, value FROM {config} WHERE module_name='route' AND name='translit_array' LIMIT 1");
		if(! $route_translit_array)
		{
			DB::query("INSERT INTO {config} (module_name, name, value) VALUES ('route', 'translit_array', '%h')", $_POST["route_translit_from"]."````".$_POST["route_translit_to"]);
		}
		elseif($route_translit_array["value"] != $_POST["route_translit_from"]."````".$_POST["route_translit_to"])
		{
			DB::query("UPDATE {config} SET value='%h' WHERE module_name='route' AND name='translit_array'", $_POST["route_translit_from"]."````".$_POST["route_translit_to"]);
		}

		$route_translate_yandex_key = DB::query_fetch_array("SELECT id, value FROM {config} WHERE module_name='route' AND name='translate_yandex_key' LIMIT 1");
		if(! $route_translate_yandex_key)
		{
			DB::query("INSERT INTO {config} (module_name, name, value) VALUES ('route', 'translate_yandex_key', '%h')", $_POST["route_translate_yandex_key"]);
		}
		elseif($route_translate_yandex_key["value"] != $_POST["route_translate_yandex_key"])
		{
			DB::query("UPDATE {config} SET value='%h' WHERE module_name='route' AND name='translate_yandex_key'", $_POST["route_translate_yandex_key"]);
		}

		Custom::inc('includes/config.php');
		Config::save($new_values, $this->diafan->_languages->all);

		if (! empty($_POST["mod_developer_delete_cache"]) || ROUTE_END != $this->diafan->filter($_POST, "string", "route_end"))
		{
			$this->diafan->_cache->delete("", array());
		}
		if ($admin_folder == ADMIN_FOLDER)
		{
			$this->diafan->redirect(URL.'success1/');
		}
		else
		{
			$this->diafan->redirect('http'.(IS_HTTPS ? "s" : '').'://'.BASE_URL.'/'.$admin_folder.'/config/success1/');
		}
		return true;
	}
}
