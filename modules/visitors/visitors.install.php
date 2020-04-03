<?php
/**
 * Установка модуля
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

class Visitors_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Посещаемость";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "visitors_session",
			"comment" => "Статистика посетителей",
			"fields" => array(
				array(
					"name" => "master_id",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "главный идентификатор в формате UNIXTIME",
				),
				array(
					"name" => "slave_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "вторичный идентификатор в интервале master_id",
				),
				array(
					"name" => "id",
					"type" => "TEXT",
					"comment" => "объединенный идентификатор",
				),
				array(
					"name" => "session_id",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "идентификатор сессии из таблицы {sessions}",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "role_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "  тип пользователя из таблицы {users_role}, для которого установлена скидка",
				),
				array(
					"name" => "hostname",
					"type" => "VARCHAR(128) NOT NULL DEFAULT ''",
					"comment" => "хост",
				),
				array(
					"name" => "user_agent",
					"type" => "TEXT",
					"comment" => "браузер пользователя",
				),
				array(
					"name" => "create",
					"type" => "VARCHAR(10) NOT NULL DEFAULT '0'",
					"comment" => "время создания в формате UNIXTIME",
				),
				array(
					"name" => "timestamp",
					"type" => "VARCHAR(20) NOT NULL DEFAULT '0'",
					"comment" => "время, до которого сессия действует",
				),
				array(
					"name" => "timeedit",
					"type" => "VARCHAR(10) NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
				array(
					"name" => "status",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "статус валидации пользовательского агента: 0 - бот, 1 - посетитель",
				),
				array(
					"name" => "search_bot",
					"type" => "VARCHAR(255) NOT NULL DEFAULT ''",
					"comment" => "имя поискового бота",
				),
			),
			"keys" => array(
				"PRIMARY KEY (`master_id`, `slave_id`)",
			),
		),
		array(
			"name" => "visitors_url",
			"comment" => "Лог посещений пользователей",
			"fields" => array(
				array(
					"name" => "master_id",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "главный идентификатор в формате UNIXTIME",
				),
				array(
					"name" => "slave_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "вторичный идентификатор в интервале master_id",
				),
				array(
					"name" => "id",
					"type" => "TEXT",
					"comment" => "объединенный идентификатор",
				),
				array(
					"name" => "visitors_session_id",
					"type" => "TEXT",
					"comment" => "идентификатор посетителей из таблицы {visitors_session}",
				),
				array(
					"name" => "hostname",
					"type" => "VARCHAR(128) NOT NULL DEFAULT ''",
					"comment" => "хост",
				),
				array(
					"name" => "referer_scheme",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "протокол рефферер: 0 - http, 1 - https",
				),
				array(
					"name" => "referer_domain",
					"type" => "TEXT",
					"comment" => "домен-рефферер",
				),
				array(
					"name" => "referer_rewrite",
					"type" => "TEXT",
					"comment" => "адрес-рефферер",
				),
				array(
					"name" => "referer_query",
					"type" => "TEXT",
					"comment" => "параметры-рефферер",
				),
				array(
					"name" => "scheme",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "протокол: 0 - http, 1 - https",
				),
				array(
					"name" => "rewrite",
					"type" => "TEXT",
					"comment" => "псевдоссылка страницы",
				),
				array(
					"name" => "query",
					"type" => "TEXT",
					"comment" => "параметры адреса",
				),
				array(
					"name" => "is_admin",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "страница относится к административной части сайта: 0 - нет, 1 - да",
				),
				array(
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
				),
				array(
					"name" => "module_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название модуля",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор элемента модуля",
				),
				array(
					"name" => "element_type",
					"type" => "ENUM('element', 'cat', 'brand', 'param') NOT NULL DEFAULT 'element'",
					"comment" => "тип элемента модуля",
				),
				array(
					"name" => "is_mobile",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "использование мобильного устройства: 0 - нет, 1 - да",
				),
				array(
					"name" => "is_mobile_url",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "мобильная версия страницы: 0 - нет, 1 - да",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "role_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "  тип пользователя из таблицы {users_role}, для которого установлена скидка",
				),
				array(
					"name" => "visits",
					"type" => "ENUM( '0', '1' , '2' ) NOT NULL DEFAULT '0'",
					"comment" => "посещение: 0 - нет, 1 - новое, 2 - повторное",
				),
				array(
					"name" => "status",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "статус валидации пользовательского агента: 0 - бот, 1 - посетитель",
				),
				array(
					"name" => "search_bot",
					"type" => "VARCHAR(255) NOT NULL DEFAULT ''",
					"comment" => "имя поискового бота",
				),
				array(
					"name" => "timeedit",
					"type" => "VARCHAR(10) NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
			),
			"keys" => array(
				"PRIMARY KEY (`master_id`, `slave_id`)",
			),
		),
		array(
			"name" => "visitors_stat_traffic",
			"comment" => "Статистика посещений",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "date",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время в формате UNIXTIME",
				),
				array(
					"name" => "visits_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество визитов",
				),
				array(
					"name" => "visits_search_bot_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество визитов от поисковых ботов",
				),
				array(
					"name" => "visits_bot_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество визитов от неопределенных ботов",
				),
				array(
					"name" => "pageviews_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество просмотров",
				),
				array(
					"name" => "pageviews_search_bot_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество просмотров от поисковых ботов",
				),
				array(
					"name" => "pageviews_bot_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество просмотров от неопределенных ботов",
				),
				array(
					"name" => "users_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество уникальных посетители",
				),
				array(
					"name" => "users_search_bot_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество уникальнех поисковых ботов",
				),
				array(
					"name" => "users_bot_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество уникальных неопределенных ботов",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY `date` (`date`)",
			),
		),
		array(
			"name" => "visitors_stat_traffic_source",
			"comment" => "Статистика по источникам трафика",
			"fields" => array(
				array(
					"name" => "master_id",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "главный идентификатор в формате UNIXTIME",
				),
				array(
					"name" => "slave_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "вторичный идентификатор в интервале master_id",
				),
				array(
					"name" => "id",
					"type" => "TEXT",
					"comment" => "объединенный идентификатор",
				),
				array(
					"name" => "referer_domain",
					"type" => "TEXT",
					"comment" => "домен-рефферер",
				),
				array(
					"name" => "visits_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество визитов",
				),
				array(
					"name" => "visits_search_bot_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество визитов от поисковых ботов",
				),
				array(
					"name" => "visits_bot_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество визитов от неопределенных ботов",
				),
				array(
					"name" => "timeedit",
					"type" => "VARCHAR(10) NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
			),
			"keys" => array(
				"PRIMARY KEY (`master_id`, `slave_id`)",
			),
		),
		array(
			"name" => "visitors_stat_traffic_pages",
			"comment" => "Статистика по посещаемым страницам сайта",
			"fields" => array(
				array(
					"name" => "master_id",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "главный идентификатор в формате UNIXTIME",
				),
				array(
					"name" => "slave_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "вторичный идентификатор в интервале master_id",
				),
				array(
					"name" => "id",
					"type" => "TEXT",
					"comment" => "объединенный идентификатор",
				),
				array(
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
				),
				array(
					"name" => "visits_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество визитов",
				),
				array(
					"name" => "visits_search_bot_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество визитов от поисковых ботов",
				),
				array(
					"name" => "visits_bot_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество визитов от неопределенных ботов",
				),
				array(
					"name" => "timeedit",
					"type" => "VARCHAR(10) NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
			),
			"keys" => array(
				"PRIMARY KEY (`master_id`, `slave_id`)",
			),
		),
		array(
			"name" => "visitors_stat_traffic_names_search_bot",
			"comment" => "Статистика по поисковым ботам",
			"fields" => array(
				array(
					"name" => "master_id",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "главный идентификатор в формате UNIXTIME",
				),
				array(
					"name" => "slave_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "вторичный идентификатор в интервале master_id",
				),
				array(
					"name" => "id",
					"type" => "TEXT",
					"comment" => "объединенный идентификатор",
				),
				array(
					"name" => "search_bot",
					"type" => "VARCHAR(255) NOT NULL DEFAULT ''",
					"comment" => "имя поискового бота",
				),
				array(
					"name" => "visits_search_bot_count",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество визитов от поисковых ботов",
				),
				array(
					"name" => "timeedit",
					"type" => "VARCHAR(10) NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
			),
			"keys" => array(
				"PRIMARY KEY (`master_id`, `slave_id`)",
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "visitors",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Посещаемость",
			"rewrite" => "visitors",
			"group_id" => 3,
			"sort" => 41,
			"act" => true,
			"children" => array(
				array(
					"name" => "Сводная статистика",
					"rewrite" => "visitors",
					"act" => true,
				),
				array(
					"name" => "Посетители",
					"rewrite" => "visitors/counter",
					"act" => true,
				),
				array(
					"name" => "Яндекс Метрика",
					"rewrite" => "visitors/yandex",
					"act" => true,
				),
				array(
					"name" => "Google Аналитика",
					"rewrite" => "visitors/google",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "visitors/config",
				),
			),
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(//активация ведение Статистики CMS
			"name" => "counter_enable",
			"value" => "1",
		),
		array(//отложенный режим для идентификации пользовательского агента
			"name" => "counter_defer",
			"value" => "1",
		),
	);

	/**
	 * Выполняет действия при установке модуля после основной установки
	 *
	 * @return void
	 */
	public function action_post()
	{
		$this->diafan->configmodules('counter_timeedit_installed', 'visitors', 0, 0, time());

		//идентификатор для ведение статистических данных
		$UID = $this->diafan->configmodules('counter_uid', 'visitors');
		if(empty($UID))
		{
			$UID = $this->diafan->uid();
			$this->diafan->configmodules('counter_uid', 'visitors', 0, 0, $UID);
		}

		Custom::inc('modules/visitors/inc/visitors.inc.counter.php');
		//интервал (в минутах) с момента последней активности, в течении которого пользователь не считается уникальным
		$active_interval = $this->diafan->configmodules('counter_active_interval', 'visitors');
		if(empty($active_interval))
		{
			$this->diafan->configmodules('counter_active_interval', 'visitors', 0, 0, (round(Visitors_inc_counter::ACTIVE_INTERVAL / 60)));
		}
		//задержка (в секундах), при которой допускается, что посетитель, запросивший контент страницы, активен на сайте
		$delay_activity_user = $this->diafan->configmodules('counter_delay_activity_user', 'visitors');
		if(empty($delay_activity_user))
		{
			$this->diafan->configmodules('counter_delay_activity_user', 'visitors', 0, 0, Visitors_inc_counter::DELAY_ACTIVITY_USER);
		}
		//задержка (в секундах), при которой допускается, что посетитель, запросивший контент страницы, активен на сайте
		$delay_activity_bot = $this->diafan->configmodules('counter_delay_activity_bot', 'visitors');
		if(empty($delay_activity_bot))
		{
			$this->diafan->configmodules('counter_delay_activity_bot', 'visitors', 0, 0, Visitors_inc_counter::DELAY_ACTIVITY_BOT);
		}

		//File::create_dir('tmp/visitors', true);
	}

	/**
	 * @var array SQL-запросы
	 */
	public $sql = array(
		"visitors_stat_traffic" => array(
			array(
				'date' => '0',
				'slave_id' => '1',
				'visits_count' => '40',
				'visits_search_bot_count' => '10',
				'visits_bot_count' => '20',
				'pageviews_count' => '60',
				'pageviews_search_bot_count' => '15',
				'pageviews_bot_count' => '25',
				'users_count' => '20',
				'users_search_bot_count' => '1',
				'users_bot_count' => '5',
			),
		),
		"visitors_stat_traffic_source" => array(
			array(
				'master_id' => '1',
				'slave_id' => '1',
				'id' => '1-1',
				'referer_domain' => 'www.yandex.ru',
				'visits_count' => '10',
				'visits_search_bot_count' => '10',
				'visits_bot_count' => '10',
				'timeedit' => '0',
			),
			array(
				'master_id' => '1',
				'slave_id' => '2',
				'id' => '1-2',
				'referer_domain' => 'www.google.ru',
				'visits_count' => '5',
				'visits_search_bot_count' => '5',
				'visits_bot_count' => '5',
				'timeedit' => '0',
			),
		),
		"visitors_stat_traffic_pages" => array(
			array(
				'master_id' => '1',
				'slave_id' => '1',
				'id' => '1-1',
				'site_id' => '1',
				'visits_count' => '1',
				'visits_search_bot_count' => '1',
				'visits_bot_count' => '1',
				'timeedit' => '0',
			),
		),
		"visitors_stat_traffic_names_search_bot" => array(
			array(
				'master_id' => '1',
				'slave_id' => '1',
				'id' => '1-1',
				'search_bot' => 'YandexBot',
				'visits_search_bot_count' => '10',
				'timeedit' => '0',
			),
			array(
				'master_id' => '1',
				'slave_id' => '2',
				'id' => '1-2',
				'search_bot' => 'Googlebot',
				'visits_search_bot_count' => '5',
				'timeedit' => '0',
			),
		),
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		'visitors_stat_traffic' => array(
			array(
				'date' => '0',
				'slave_id' => '1',
				'visits_count' => '55',
				'visits_search_bot_count' => '50',
				'visits_bot_count' => '60',
				'pageviews_count' => '125',
				'pageviews_search_bot_count' => '85',
				'pageviews_bot_count' => '110',
				'users_count' => '70',
				'users_search_bot_count' => '55',
				'users_bot_count' => '70',
			),
			array(
				'date' => '0',
				'slave_id' => '1',
				'visits_count' => '25',
				'visits_search_bot_count' => '20',
				'visits_bot_count' => '20',
				'pageviews_count' => '65',
				'pageviews_search_bot_count' => '45',
				'pageviews_bot_count' => '60',
				'users_count' => '40',
				'users_search_bot_count' => '25',
				'users_bot_count' => '40',
			),
			array(
				'date' => '0',
				'slave_id' => '1',
				'visits_count' => '25',
				'visits_search_bot_count' => '20',
				'visits_bot_count' => '20',
				'pageviews_count' => '65',
				'pageviews_search_bot_count' => '45',
				'pageviews_bot_count' => '60',
				'users_count' => '40',
				'users_search_bot_count' => '25',
				'users_bot_count' => '40',
			),
			array(
				'date' => '0',
				'slave_id' => '1',
				'visits_count' => '35',
				'visits_search_bot_count' => '30',
				'visits_bot_count' => '40',
				'pageviews_count' => '85',
				'pageviews_search_bot_count' => '65',
				'pageviews_bot_count' => '90',
				'users_count' => '50',
				'users_search_bot_count' => '35',
				'users_bot_count' => '50',
			),
			array(
				'date' => '0',
				'slave_id' => '1',
				'visits_count' => '25',
				'visits_search_bot_count' => '20',
				'visits_bot_count' => '20',
				'pageviews_count' => '65',
				'pageviews_search_bot_count' => '45',
				'pageviews_bot_count' => '60',
				'users_count' => '40',
				'users_search_bot_count' => '25',
				'users_bot_count' => '40',
			),
			array(
				'date' => '0',
				'slave_id' => '1',
				'visits_count' => '4',
				'visits_search_bot_count' => '20',
				'visits_bot_count' => '15',
				'pageviews_count' => '18',
				'pageviews_search_bot_count' => '25',
				'pageviews_bot_count' => '45',
				'users_count' => '10',
				'users_search_bot_count' => '15',
				'users_bot_count' => '30',
			),
			array(
				'date' => '0',
				'slave_id' => '1',
				'visits_count' => '25',
				'visits_search_bot_count' => '20',
				'visits_bot_count' => '20',
				'pageviews_count' => '65',
				'pageviews_search_bot_count' => '45',
				'pageviews_bot_count' => '60',
				'users_count' => '40',
				'users_search_bot_count' => '25',
				'users_bot_count' => '40',
			),
			array(
				'date' => '0',
				'slave_id' => '1',
				'visits_count' => '3',
				'visits_search_bot_count' => '10',
				'visits_bot_count' => '20',
				'pageviews_count' => '13',
				'pageviews_search_bot_count' => '15',
				'pageviews_bot_count' => '80',
				'users_count' => '10',
				'users_search_bot_count' => '5',
				'users_bot_count' => '60',
			),
			array(
				'date' => '0',
				'slave_id' => '1',
				'visits_count' => '0',
				'visits_search_bot_count' => '0',
				'visits_bot_count' => '0',
				'pageviews_count' => '5',
				'pageviews_search_bot_count' => '15',
				'pageviews_bot_count' => '30',
				'users_count' => '5',
				'users_search_bot_count' => '15',
				'users_bot_count' => '30',
			),
		),
		'visitors_stat_traffic_source' => array(
			array(
				'master_id' => '2',
				'slave_id' => '1',
				'id' => '2-1',
				'referer_domain' => 'www.yandex.ru',
				'visits_count' => '60',
				'visits_search_bot_count' => '80',
				'visits_bot_count' => '10',
				'timeedit' => '0',
			),
			array(
				'master_id' => '2',
				'slave_id' => '2',
				'id' => '2-2',
				'referer_domain' => 'www.google.ru',
				'visits_count' => '70',
				'visits_search_bot_count' => '40',
				'visits_bot_count' => '20',
				'timeedit' => '0',
			),
			array(
				'master_id' => '2',
				'slave_id' => '3',
				'id' => '2-3',
				'referer_domain' => 'mail.ru',
				'visits_count' => '30',
				'visits_search_bot_count' => '20',
				'visits_bot_count' => '5',
				'timeedit' => '0',
			),
		),
		'visitors_stat_traffic_pages' => array(
			array(
				'master_id' => '2',
				'slave_id' => '1',
				'id' => '2-1',
				'site_id' => '1',
				'visits_count' => '60',
				'visits_search_bot_count' => '80',
				'visits_bot_count' => '60',
				'timeedit' => '0',
			),
			array(
				'master_id' => '2',
				'slave_id' => '2',
				'id' => '2-2',
				'site_id' => '154',
				'visits_count' => '80',
				'visits_search_bot_count' => '90',
				'visits_bot_count' => '30',
				'timeedit' => '0',
			),
			array(
				'master_id' => '2',
				'slave_id' => '3',
				'id' => '2-3',
				'site_id' => '155',
				'visits_count' => '30',
				'visits_search_bot_count' => '90',
				'visits_bot_count' => '30',
				'timeedit' => '0',
			),
			array(
				'master_id' => '2',
				'slave_id' => '4',
				'id' => '2-4',
				'site_id' => '4',
				'visits_count' => '20',
				'visits_search_bot_count' => '20',
				'visits_bot_count' => '20',
				'timeedit' => '0',
			),
			array(
				'master_id' => '2',
				'slave_id' => '5',
				'id' => '2-5',
				'site_id' => '172',
				'visits_count' => '30',
				'visits_search_bot_count' => '30',
				'visits_bot_count' => '80',
				'timeedit' => '0',
			),
		),
		'visitors_stat_traffic_names_search_bot' => array(
			array(
				'master_id' => '2',
				'slave_id' => '1',
				'id' => '2-1',
				'search_bot' => 'YandexBot',
				'visits_search_bot_count' => '60',
				'timeedit' => '0',
			),
			array(
				'master_id' => '2',
				'slave_id' => '2',
				'id' => '2-2',
				'search_bot' => 'Googlebot',
				'visits_search_bot_count' => '40',
				'timeedit' => '0',
			),
			array(
				'master_id' => '2',
				'slave_id' => '3',
				'id' => '2-3',
				'search_bot' => 'Mail.RU_Bot',
				'visits_search_bot_count' => '20',
				'timeedit' => '0',
			),
		),
	);

	/**
	 * Выполняет действия при установке модуля
	 *
	 * @return void
	 */
	protected function action()
	{
		if(! empty($_POST["example_yes"]))
		{
			if(! empty($this->demo))
			{
				$lifetime = 86400;
				foreach($this->demo as $key => $values)
				{
					if($key == 'visitors_stat_traffic')
					{
						if(! empty($values))
						{
							$today = time() - $lifetime;
							foreach($values as $k => $val)
							{
								if(empty($val) || ! isset($val["date"])) continue;
								$today -= $lifetime;
								$this->demo[$key][$k]["date"] = $today;
							}
						}
					}
					if($key == 'visitors_stat_traffic_pages')
					{
						if(! empty($values))
						{
							$today = time() - $lifetime;
							foreach($values as $k => $val)
							{
								if(empty($val) || ! isset($val["timeedit"])) continue;
								$today -= $lifetime;
								$this->demo[$key][$k]["timeedit"] = $today;
							}
						}
					}
				}
			}
		}

		if(! empty($this->sql))
		{
			$lifetime = 86400;
			foreach($this->sql as $key => $values)
			{
				if($key == 'visitors_stat_traffic')
				{
					if(! empty($values))
					{
						$today = time() - $lifetime;
						foreach($values as $k => $val)
						{
							if(empty($val) || ! isset($val["date"])) continue;
							$today -= $lifetime;
							$this->sql[$key][$k]["date"] = $today;
						}
					}
				}
				if($key == 'visitors_stat_traffic_pages')
				{
					if(! empty($values))
					{
						$today = time() - $lifetime;
						foreach($values as $k => $val)
						{
							if(empty($val) || ! isset($val["timeedit"])) continue;
							$today -= $lifetime;
							$this->sql[$key][$k]["timeedit"] = $today;
						}
					}
				}
			}
		}
	}
}
