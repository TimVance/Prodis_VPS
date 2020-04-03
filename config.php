<?php
/**
 * Файл конфигурации
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	include dirname(__FILE__).'/includes/404.php';
}

//папка, в которой лежит сайт. Для корня домена оставьте пустым
define("REVATIVE_PATH", '');

//название сайта, добавляется к тегу title в конце через дефис
define("TIT1", 'Vend');

//параметры подключения к БД
define("DB_URL", 'mysqli://admin_vend:7ERdqT3X@localhost/admin_vend');

//префикс таблиц сайта в БД
define("DB_PREFIX", 'vdbb_');

//кодировка БД
define("DB_CHARSET", 'utf8');

//название папки с визуальным редактором
define("USERFILES", 'userfls');

//версия DIAFAN.CMS
define("VERSION_CMS", "6.0");

//ЧПУ папки панели администрирования
define("ADMIN_FOLDER", 'admin');

//мобильная версия true/false (да/нет)
define("MOBILE_VERSION", false);

//имя мобильной версии в url-адресе
define("MOBILE_PATH", 'm');

//использовать имя мобильной версии в качестве поддомена true/false (да/нет)
define("MOBILE_SUBDOMAIN", false);

//источник загрузки JS-библиотек: 1 - Google CDN, 2 - Yandex CDN, 3 - Microsoft CDN, 4 - CDNJS CDN, 5 - jQuery CDN, 6 - Hosting
define("SOURCE_JS", 1);

//demo-версия true/false (да/нет)
define("IS_DEMO", false);

//включить режим разработки, когда на сайт выводятся все возможные ошибки true/false (да/нет)
define("MOD_DEVELOPER", true);

//показывать ошибки только администратору true/false (да/нет)
define("MOD_DEVELOPER_ADMIN", true);

//включить режим технического обслуживания сайта, сайт станет недоступен для пользователей (шаблон оформления сообщения в themes/503.php) true/false (да/нет)
define("MOD_DEVELOPER_TECH", false);

//включить режим сжатия HTML-контента true/false (да/нет)
define("MOD_DEVELOPER_MINIFY", false);

//отключить кеширование true/false (да/нет)
define("MOD_DEVELOPER_CACHE", true);

//выводить запросы к БД на сайте true/false (да/нет)
define("MOD_DEVELOPER_PROFILING", false);

//выводить профилирование PHP-скриптов на сайте true/false (да/нет)
define("MOD_DEVELOPER_PROFILER", false);

//выводить профилирование POST-запроса на сайте true/false (да/нет)
define("MOD_DEVELOPER_POST", false);

//адрес FTP текущего сайта
define("FTP_HOST", '');

//путь к DIAFAN.CMS, после входа ftp-пользователя, например, www/site.ru/docs/
define("FTP_DIR", '');

//имя FTP-пользователя
define("FTP_LOGIN", '');

//пароль FTP-пользователя
define("FTP_PASSWORD", '');

//экстремальное кэширование
define("CACHE_EXTREME", false);

//использовать Memcached сервер для кэширования
define("CACHE_MEMCACHED", false);

//хост сервера Memcached
define("CACHE_MEMCACHED_HOST", '');

//порт сервера Memcached
define("CACHE_MEMCACHED_PORT", '');

//часовой пояс сайта, в формате http://www.php.net/manual/en/timezones.php
define("TIMEZONE", 'Europe/Moscow');

//конец строки ЧПУ, по умолчанию "/". Можно ввести ".htm"
define("ROUTE_END", '/');

//использовать автоматическое формирование ЧПУ для модулей true/false (да/нет)
define("ROUTE_AUTO_MODULE", true);

//дата последнего экспорта заказов в систему 1С:Предприятие
define("LAST_1C_EXPORT", '');

// разрешить вставлять во frame
define("NO_X_FRAME", false);

// примененные темы
define("CUSTOM", 'custom28_10_2019_18_12');
