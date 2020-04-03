<?php
/**
 * Demo-версия
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
if(! defined('DIAFAN'))
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
 * Demo
 */
class Demo extends Diafan
{
	/**
	 * @var object
	 */
	private $install;

	/**
	 * @var array модули ядра в порядке установки
	 */
	private $core_tables = array('service', 'admin', 'site', 'config', 'attachments', 'images', 'menu');

	/**
	 * Установка
	 *
	 * @return void
	 */
	function install()
	{
		DB::query("CREATE TABLE {sessions} (
			`user_id` int(11) unsigned NOT NULL DEFAULT '0',
			`session_id` varchar(64) NOT NULL DEFAULT '',
			`hostname` varchar(128) NOT NULL DEFAULT '',
			`user_agent` varchar(255) NOT NULL DEFAULT '',
			`timestamp` int(10) unsigned NOT NULL,
			`session` text NOT NULL,
			PRIMARY KEY (`session_id`),
			KEY `user_id` (`user_id`)
		) CHARSET=utf8;");

		DB::query("CREATE TABLE {users} (
			`id` int(3) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(60) NOT NULL DEFAULT '',
			`password` varchar(32) NOT NULL DEFAULT '',
			`mail` varchar(64) NOT NULL DEFAULT '',
			`created` int(12) unsigned NOT NULL DEFAULT '0',
			`fio` varchar(250) NOT NULL DEFAULT '',
			`role_id` int(3) unsigned NOT NULL DEFAULT '0',
			`act` enum('0','1') NOT NULL DEFAULT '0',
			`trash` enum('0','1') NOT NULL DEFAULT '0',
			`htmleditor` enum('0','1') NOT NULL,
			`subscription` varchar(255) NOT NULL,
			`language` tinyint(2) NOT NULL,
			`background` varchar(255) NOT NULL,
			PRIMARY KEY (`id`),
			KEY `name` (`name`(1))
		) CHARSET=utf8;");

		DB::query("CREATE TABLE {users_demo} (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`session_id` varchar(64) CHARACTER SET utf8 NOT NULL,
			`ip` varchar(62) CHARACTER SET utf8 NOT NULL,
			`timestart` int(10) unsigned NOT NULL,
			`timeend` int(10) unsigned NOT NULL,
			`timenow` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`)
		) CHARSET=utf8;");

		DB::query("CREATE TABLE {users_role} (
			`id` int(3) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(64) NOT NULL DEFAULT '',
			`trash` enum('0','1') NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		) CHARSET=utf8;");

		DB::query("CREATE TABLE {users_role_perm} (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`role_id` tinyint(3) unsigned NOT NULL,
			`perm` text NOT NULL,
			`rewrite` text NOT NULL,
			`type` enum('site','admin') NOT NULL,
			PRIMARY KEY (`id`)
		) CHARSET=utf8;");

		DB::query("INSERT INTO {users} (`id`, `name`, `password`, `mail`, `created`, `fio`, `role_id`, `act`, `trash`, `htmleditor`, `subscription`, `language`, `background`) VALUES
(1, 'demo', 'fb676e6eb353fd0d2224ec2711f9e571', 'mail@diafan.ru', 1301601600, 'Иванов Иван', 4, '1', '0', '1', '', 0, '');");

		DB::query("INSERT INTO {users_role} (`id`, `name`, `trash`) VALUES
(4, 'Администратор', '0');");

		DB::query("INSERT INTO {users_role_perm} (`id`, `role_id`, `perm`, `rewrite`, `type`) VALUES
(1, 4, 'all', 'all', 'admin');");
	}

	/**
	 * Инициализация demo-версии
	 *
	 * @return void
	 */
	function init()
	{
		$session_id = preg_replace('/[0-9]+/', '', $this->diafan->_session->id);
		define("DB_PREFIX", $session_id.DB_PREFIX_DEMO);
		define("USERFILES", 'userfls/'.$session_id);

		if(! $this->diafan->_users->id)
		{
			exit;
		}

		if(! DB::query_result("SELECT id FROM ".DB_PREFIX_DEMO."users_demo WHERE session_id='%s' AND timeend=0 LIMIT 1", $this->diafan->_session->id))
		{
			$this->start();
			$this->clear();
		}
		$this->config();
	}

	/**
	 * Старт demo-сессии
	 *
	 * @return void
	 */
	function start()
	{
		define('IS_INSTALL', true);
		if(DB::query_result("SELECT COUNT(*) FROM ".DB_PREFIX_DEMO."users_demo WHERE timeend=0") > 15)
		{
			$this->clear();
			throw new Exception('В настоящий момент демо-версию DIAFAN.CMS изучают более 15 других пользователей. <a href="/">Обновите страницу</a> минут через 10. Благодарим за понимание!');
		}

		DB::query("INSERT INTO ".DB_PREFIX_DEMO."users_demo (session_id, ip, timestart) VALUES ('%s', '%s', %d)",
				  $this->diafan->_session->id, getenv('REMOTE_ADDR')." ".getenv('HTTP_X_FORWARDED_FOR'), time());

		$_SESSION["install_name"] = 'Бумажный зоопарк';

		Custom::inc("includes/install.php");

		$_SESSION["install_admin_name"] = 'admin';
		$_SESSION["install_admin_pass"] = '123';
		$_SESSION["install_admin_fio"] = 'Админ';
		$_SESSION["install_admin_mail"] = 'test@test.ru';

		$langs = array(1, 2);
		define('_LANG', 1);
		define('_SHORTNAME',  '' );
		define('TITLE', '');
		define('BASE_PATH_HREF', "http".(IS_HTTPS ? "s" : '')."://".getenv("HTTP_HOST")."/".( REVATIVE_PATH ? REVATIVE_PATH.'/' : '' ).ADMIN_FOLDER.'/' );
		$install_modules = $this->core_tables;
		if ($dir = opendir(ABSOLUTE_PATH.'modules'))
		{
			while (($module = readdir($dir)) !== false)
			{
				if ($module != '.' && $module != '..')
				{
					if (file_exists(ABSOLUTE_PATH.'modules/'.$module.'/'.$module.'.install.php'))
					{
						if(! in_array($module, $this->core_tables))
						{
							$install_modules[] = $module;
						}
					}
				}
			}
			closedir($dir);
		}
		foreach($install_modules as $module)
		{
			include_once(ABSOLUTE_PATH.'modules/'.$module.'/'.$module.'.install.php');
			$name = Ucfirst($module).'_install';
			$class[$module] = new $name($this->diafan);
			$class[$module]->langs = $langs;
			$class[$module]->module = $module;
			$class[$module]->install_modules = $install_modules;
		}
		foreach($install_modules as $module)
		{
			$class[$module]->tables();
		}
		foreach($install_modules as $module)
		{
			$class[$module]->start(true);
		}
		foreach($install_modules as $module)
		{
			$class[$module]->action_post();
		}
	}

	/**
	 * Формирует параметры сайта
	 *
	 * @return void
	 */
	function config()
	{
		$fields = array(
			'MOBILE_VERSION',
			'MOBILE_PATH',
			'MOBILE_SUBDOMAIN',
			'SOURCE_JS',
			'CACHE_MEMCACHED',
			'CACHE_MEMCACHED_HOST',
			'CACHE_MEMCACHED_PORT',
			'TIMEZONE',
			'LAST_1C_EXPORT',
			'ROUTE_END',
			'ROUTE_AUTO_MODULE',
		);
		for($i = 1; $i<11; $i++)
		{
			$fields[] = 'TIT'.$i;
		}
		$default = array(
			'DB_URL' => 'mysqli://user:pass@localhost/dbname',
			'DB_PREFIX' => 'diafan_',
			'DB_CHARSET' => 'utf8',
			'USERFILES' => 'userfls',
			'ADMIN_FOLDER' => 'admin',
			'TIMEZONE' => 'Europe/Moscow',
			'LAST_1C_EXPORT' => "1.04.2013 13:21",
			'ROUTE_END' => '/',
			'ROUTE_AUTO_MODULE' => true,
			'TIT1' => 'Демо версия сайта',
			'TIT2' => 'Demo',
			'MOBILE_VERSION' => true,
			'MOBILE_PATH' => 'm',
			'MOBILE_SUBDOMAIN' => false,
			'SOURCE_JS' => 1,
		);
		// определяем дефолтные значения для модуля "Параметры сайта"
		foreach ($default as $k => $v)
		{
			if(! isset($_SESSION["CONFIG_".$v]))
			{
				$_SESSION["CONFIG_".$k] = $v;
			}
		}
		// назначаем параметрам сайта из списка значения, определенные пользователем
		foreach ($fields as $field)
		{
			if(! isset($_SESSION["CONFIG_".$field]))
			{
				$_SESSION["CONFIG_".$field] = '';
			}
			define($field, $_SESSION["CONFIG_".$field]);
		}
	}

	/**
	 * Удаляет демо-данные
	 *
	 * @param string $session_id идентификатор сессии, если не передан, чистит старые сессии
	 * @return void
	 */
	public function clear($session_id = '')
	{
		Custom::inc("includes/install.php");

		$sessions = array();
		if(! $session_id)
		{
			// удаляем старые сессии, не текущие
			DB::query("DELETE FROM ".DB_PREFIX_DEMO."sessions WHERE timestamp<%d AND session_id<>'%s'", time() - 3600, $this->diafan->_session->id);
			// выбираем все активные сессии
			$sessions = DB::query_fetch_value("SELECT session_id FROM ".DB_PREFIX_DEMO."sessions", "session_id");
			// выбираем все созданные на сайте песочницы
			$rows = DB::query_fetch_all("SELECT session_id FROM ".DB_PREFIX_DEMO."users_demo WHERE timeend=0");
		}
		else
		{
			// удаляем текущую сессию
			DB::query("DELETE FROM ".DB_PREFIX_DEMO."sessions WHERE session_id='%s'", $session_id);
			// выбираем текущую песочницу
			$rows = DB::query_fetch_all("SELECT session_id FROM ".DB_PREFIX_DEMO."users_demo WHERE session_id='%h' LIMIT 1", $session_id);
		}

		foreach ($rows as $row)
		{
			if(! in_array($row["session_id"], $sessions))
			{
				DB::query("DELETE FROM ".DB_PREFIX_DEMO."users_demo WHERE session_id='%s'", $row["session_id"]);

				$row["session_id"] = preg_replace('/[0-9]+/', '', $row["session_id"]);
				if(! $row["session_id"]) continue;

				if(! isset($tables))
				{
					Custom::inc('includes/install.php');
					$tables = array();
					$rs = Custom::read_dir("modules");
					foreach($rs as $module)
					{
						if (Custom::exists('modules/'.$module.'/'.$module.'.install.php'))
						{
							Custom::inc('modules/'.$module.'/'.$module.'.install.php');
							$name = ucfirst($module).'_install';
							$class = new $name($this->diafan);
							$tables[$module] = $class->tables;
						}
					}
				}
				foreach ($tables as $module => $arr)
				{
					foreach ($arr as $r)
					{
						DB::query("DROP TABLE ".$row["session_id"].DB_PREFIX_DEMO.$r["name"].";");
					}
				}
				File::delete_dir('userfls/'.$row["session_id"]);
			}
		}
	}
}
