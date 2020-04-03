<?php
/**
 * Сохранение параметров сайта
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
 * Config
 */
class Config
{
	/**
	 * Сохраняет параметры сайта
	 *
	 * @param array $new_values новые значения параметров сайта
	 * @param array $languages языки сайта
	 * @param string $save_demo сохранить файл конфигурации для демо-версии
	 * @return void
	 */
	public static function save($new_values, $languages, $save_demo = false)
	{
		$fields = array(
			'DB_URL',
			'DB_PREFIX',
			'DB_CHARSET',
			'USERFILES',
			'VERSION_CMS',
			'ADMIN_FOLDER',
			'MOBILE_VERSION',
			'MOBILE_PATH',
			'MOBILE_SUBDOMAIN',
			'SOURCE_JS',
			'IS_DEMO',
			'MOD_DEVELOPER',
			'MOD_DEVELOPER_ADMIN',
			'MOD_DEVELOPER_CACHE',
			'MOD_DEVELOPER_PROFILING',
			'MOD_DEVELOPER_PROFILER',
			'MOD_DEVELOPER_POST',
			'MOD_DEVELOPER_TECH',
			'MOD_DEVELOPER_MINIFY',
			'FTP_HOST',
			'FTP_DIR',
			'FTP_LOGIN',
			'FTP_PASSWORD',
			'CACHE_EXTREME',
			'CACHE_MEMCACHED',
			'CACHE_MEMCACHED_HOST',
			'CACHE_MEMCACHED_PORT',
			'ROUTE_END',
			'ROUTE_AUTO_MODULE',
			'TIMEZONE',
			'LAST_1C_EXPORT',
			'NO_X_FRAME',
			'CUSTOM',
			);
		foreach ($languages as $language)
		{
			$fields[] = 'TIT'.$language["id"];
		}
		foreach ($fields as $field)
		{
			if (! isset($new_values[$field]))
			{
				$new_values[$field] = defined($field) ? constant($field): '';
			}
			if(defined('IS_DEMO') && IS_DEMO)
			{
				$_SESSION["CONFIG_".$field] = $new_values[$field];
			}
			$new_values[$field] = str_replace('\\', '\\\\', $new_values[$field]);
			$new_values[$field] = str_replace("'", "\\'", $new_values[$field]);
		}
		if(defined('IS_DEMO') && IS_DEMO)
		{
			return;
		}
		if($new_values["CUSTOM"])
		{
			$new_c = array();
			$custom = explode(',', $new_values["CUSTOM"]);
			foreach($custom as $c)
			{
				if(trim($c))
				{
					$new_c[] = $c;
				}
			}
			$new_values["CUSTOM"] = implode(',', $new_c);
		}
		if($save_demo)
		{
			$text = self::template_demo($new_values);
		}
		else
		{
			$text = self::template($new_values, $languages);
		}
		File::save_file($text, "config.php");
	}

	/**
	 * Шаблон файла конфигурации
	 *
	 * @param array $languages языки сайта
	 * @param array $new_values новые значения параметров сайта
	 * @return string
	 */
	static private function template($new_values, $languages)
	{
		$text = '<?php
/**
 * Файл конфигурации
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined(\'DIAFAN\'))
{
	include dirname(__FILE__).\'/includes/404.php\';
}

//папка, в которой лежит сайт. Для корня домена оставьте пустым
define("REVATIVE_PATH", \''.REVATIVE_PATH.'\');
';
		foreach ($languages as $language)
		{
			$text .= '
//название сайта, добавляется к тегу title в конце через дефис
define("TIT'.$language["id"].'", \''.$new_values["TIT".$language["id"]].'\');
';
		}
		$text.='
//параметры подключения к БД
define("DB_URL", \''.$new_values["DB_URL"].'\');

//префикс таблиц сайта в БД
define("DB_PREFIX", \''.$new_values["DB_PREFIX"].'\');

//кодировка БД
define("DB_CHARSET", \''.$new_values["DB_CHARSET"].'\');

//название папки с визуальным редактором
define("USERFILES", \''.$new_values["USERFILES"].'\');

//версия DIAFAN.CMS
define("VERSION_CMS", "6.0");

//ЧПУ папки панели администрирования
define("ADMIN_FOLDER", \''.str_replace("/", "", $new_values["ADMIN_FOLDER"]).'\');

//мобильная версия true/false (да/нет)
define("MOBILE_VERSION", '.($new_values["MOBILE_VERSION"] ? 'true' : 'false').');

//имя мобильной версии в url-адресе
define("MOBILE_PATH", \''.preg_replace('/[^a-z0-9-_]+/', '', $new_values["MOBILE_PATH"]).'\');

//использовать имя мобильной версии в качестве поддомена true/false (да/нет)
define("MOBILE_SUBDOMAIN", '.($new_values["MOBILE_SUBDOMAIN"] ? 'true' : 'false').');

//источник загрузки JS-библиотек: 1 - Google CDN, 2 - Yandex CDN, 3 - Microsoft CDN, 4 - CDNJS CDN, 5 - jQuery CDN, 6 - Hosting
define("SOURCE_JS", '.( (int) $new_values["SOURCE_JS"] ).');

//demo-версия true/false (да/нет)
define("IS_DEMO", false);

//включить режим разработки, когда на сайт выводятся все возможные ошибки true/false (да/нет)
define("MOD_DEVELOPER", '.($new_values["MOD_DEVELOPER"] ? 'true' : 'false').');

//показывать ошибки только администратору true/false (да/нет)
define("MOD_DEVELOPER_ADMIN", '.($new_values["MOD_DEVELOPER_ADMIN"] ? 'true' : 'false').');

//включить режим технического обслуживания сайта, сайт станет недоступен для пользователей (шаблон оформления сообщения в themes/503.php) true/false (да/нет)
define("MOD_DEVELOPER_TECH", '.($new_values["MOD_DEVELOPER_TECH"] ? 'true' : 'false').');

//включить режим сжатия HTML-контента true/false (да/нет)
define("MOD_DEVELOPER_MINIFY", '.($new_values["MOD_DEVELOPER_MINIFY"] ? 'true' : 'false').');

//отключить кеширование true/false (да/нет)
define("MOD_DEVELOPER_CACHE", '.($new_values["MOD_DEVELOPER_CACHE"] ? 'true' : 'false').');

//выводить запросы к БД на сайте true/false (да/нет)
define("MOD_DEVELOPER_PROFILING", '.($new_values["MOD_DEVELOPER_PROFILING"] ? 'true' : 'false').');

//выводить профилирование PHP-скриптов на сайте true/false (да/нет)
define("MOD_DEVELOPER_PROFILER", '.($new_values["MOD_DEVELOPER_PROFILER"] ? 'true' : 'false').');

//выводить профилирование POST-запроса на сайте true/false (да/нет)
define("MOD_DEVELOPER_POST", '.($new_values["MOD_DEVELOPER_POST"] ? 'true' : 'false').');

//адрес FTP текущего сайта
define("FTP_HOST", \''.$new_values["FTP_HOST"].'\');

//путь к DIAFAN.CMS, после входа ftp-пользователя, например, www/site.ru/docs/
define("FTP_DIR", \''.$new_values["FTP_DIR"].'\');

//имя FTP-пользователя
define("FTP_LOGIN", \''.$new_values["FTP_LOGIN"].'\');

//пароль FTP-пользователя
define("FTP_PASSWORD", \''.$new_values["FTP_PASSWORD"].'\');

//экстремальное кэширование
define("CACHE_EXTREME", '.($new_values["CACHE_EXTREME"] ? 'true' : 'false').');

//использовать Memcached сервер для кэширования
define("CACHE_MEMCACHED", '.($new_values["CACHE_MEMCACHED"] ? 'true' : 'false').');

//хост сервера Memcached
define("CACHE_MEMCACHED_HOST", \''.$new_values["CACHE_MEMCACHED_HOST"].'\');

//порт сервера Memcached
define("CACHE_MEMCACHED_PORT", \''.$new_values["CACHE_MEMCACHED_PORT"].'\');

//часовой пояс сайта, в формате http://www.php.net/manual/en/timezones.php
define("TIMEZONE", \''.($new_values["TIMEZONE"] ? $new_values["TIMEZONE"] : 'Europe/Moscow').'\');

//конец строки ЧПУ, по умолчанию "/". Можно ввести ".htm"
define("ROUTE_END", \''.$new_values["ROUTE_END"].'\');

//использовать автоматическое формирование ЧПУ для модулей true/false (да/нет)
define("ROUTE_AUTO_MODULE", '.($new_values["ROUTE_AUTO_MODULE"] ? 'true' : 'false').');

//дата последнего экспорта заказов в систему 1С:Предприятие
define("LAST_1C_EXPORT", \''.$new_values["LAST_1C_EXPORT"].'\');

// разрешить вставлять во frame
define("NO_X_FRAME", '.($new_values["NO_X_FRAME"] ? 'true' : 'false').');

// примененные темы
define("CUSTOM", \''.$new_values["CUSTOM"].'\');
';
		return $text;
	}

	/**
	 * Шаблон файла конфигурации для демо-версии
	 *
	 * @param array $new_values новые значения параметров сайта
	 * @return string
	 */
	static private function template_demo($new_values)
	{
		$text = '<?php
/**
 * Файл конфигурации
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined(\'DIAFAN\'))
{
	include dirname(__FILE__).\'/includes/404.php\';
}

//папка, в которой лежит сайт. Для корня домена оставьте пустым
define("REVATIVE_PATH", \''.REVATIVE_PATH.'\');

//параметры подключения к БД
define("DB_URL", \''.$new_values["DB_URL"].'\');

//префикс таблиц сайта в БД
define("DB_PREFIX_DEMO", \''.$new_values["DB_PREFIX"].'\');

//кодировка БД
define("DB_CHARSET", \''.$new_values["DB_CHARSET"].'\');

//версия DIAFAN.CMS
define("VERSION_CMS", "6.0");

//ЧПУ папки панели администрирования
define("ADMIN_FOLDER", \''.str_replace("/", "", $new_values["ADMIN_FOLDER"]).'\');

//мобильная версия true/false (да/нет)
define("MOBILE_VERSION", false);

//имя мобильной версии в url-адресе
define("MOBILE_PATH", \'m\');

//использовать имя мобильной версии в качестве поддомена true/false (да/нет)
define("MOBILE_SUBDOMAIN", false);

//источник загрузки JS-библиотек: 1 - Google CDN, 2 - Yandex CDN, 3 - Microsoft CDN, 4 - CDNJS CDN, 5 - jQuery CDN, 6 - Hosting
define("SOURCE_JS", 1);

//demo-версия true/false (да/нет)
define("IS_DEMO", true);

//экстремальное кэширование
define("CACHE_EXTREME", false);

//включить режим разработки, когда на сайт выводятся все возможные ошибки true/false (да/нет)
define("MOD_DEVELOPER", false);

//включить режим технического обслуживания сайта, сайт станет недоступен для пользователей (шаблон оформления сообщения в themes/503.php) true/false (да/нет)
define("MOD_DEVELOPER_TECH", false);

//включить режим сжатия HTML-контента true/false (да/нет)
define("MOD_DEVELOPER_MINIFY", false);

//отключить кеширование true/false (да/нет)
define("MOD_DEVELOPER_CACHE", false);

//выводить запросы к БД на сайте true/false (да/нет)
define("MOD_DEVELOPER_PROFILING", false);

//выводить профилирование PHP-скриптов на сайте true/false (да/нет)
define("MOD_DEVELOPER_PROFILER", false);

//выводить профилирование POST-запроса на сайте true/false (да/нет)
define("MOD_DEVELOPER_POST", false);


//адрес ftp текущего сайта
define("FTP_HOST", "");

//путь к DIAFAN.CMS, после входа ftp-пользователя, например, www/site.ru/docs/
define("FTP_DIR", "");

//имя ftp-пользователя
define("FTP_LOGIN", "");

//пароль ftp-пользователя
define("FTP_PASSWORD", "");

// разрешить вставлять во frame
define("NO_X_FRAME", false);
';
		return $text;
	}
}
