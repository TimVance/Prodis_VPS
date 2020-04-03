<?php
/**
 * @package    DIAFAN.CMS
 *
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

class Init_admin extends Core
{
	/**
	 * @var string название подключаемой функции
	 */
	public $action;

	/**
	 * @var array модуль текущего исполняемого файла
	 */
	public $current_module;

	/**
	 * var array локальный кэш файла
	 */
	public $cache;

	/**
	 * @var Frame_admin каркас
	 */
	public $_frame;

	private $destruct = false;

	/**
	 * Конструктор класса. Определяет свойства класса
	 *
	 * @return void
	 */
	public function __construct()
	{
		// явный вызов деструктора в конце работы скрипта
		Dev::register_shutdown_function(array($this, 'destruct'));

		Custom::inc('includes/database.php');

		Custom::inc('includes/diafan.php');

		Custom::inc('includes/file.php');

		Custom::inc('plugins/encoding.php');
	}

	/**
	 * Инициализирует генерирование страницы
	 *
	 * @return void
	 */
	public function init()
	{
		define('MAIN_DOMAIN', $this->domain(true));
		if(MAIN_DOMAIN != $this->domain())
		{
			header('Location: http'.(IS_HTTPS ? "s" : '').'://'. MAIN_DOMAIN .getenv("REQUEST_URI"), true, 302);
			exit;
		}

		Custom::inc('includes/session.php');
		$this->_session = new Session($this);
		$this->_session->prepare();

		Dev::set_profiler();

		$this->_session->init();

		if(! defined('EMAIL_CONFIG'))
		{
			define('EMAIL_CONFIG', ($this->configmodules("email", 'postman') ?: ''));
		}

		$this->magic_quote_off();

		define('BASE_PATH', "http".(IS_HTTPS ? "s" : '')."://".getenv("HTTP_HOST")."/".( REVATIVE_PATH ? REVATIVE_PATH.'/' : '' ) );
		define('BASE_URL', $this->domain().( REVATIVE_PATH ? '/'.REVATIVE_PATH : '' ));
		define('MAIN_PATH', "http".(IS_HTTPS ? "s" : '')."://".MAIN_DOMAIN."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : ''));
		if(defined('MOBILE_VERSION') && MOBILE_VERSION)
		{
			define('MOBILE_PATH_HREF', "http".(IS_HTTPS ? "s" : '')."://".(MOBILE_SUBDOMAIN ? MOBILE_PATH.'.' : '').MAIN_DOMAIN.( REVATIVE_PATH ? '/'.REVATIVE_PATH : '' ).(! MOBILE_SUBDOMAIN ? '/'.MOBILE_PATH : ''));
		}

		if(defined('IS_DEMO') && IS_DEMO)
		{
			$this->user();
			$this->languages();
		}
		else
		{
			$this->languages();
			$this->user();
		}

		$this->prepare_rewrite();

		$this->_postman->defer_init();

		$this->_admin->set();

		$this->headers();
		$this->module();

		Dev::get_profiler();
	}

	/**
	 * Подключает вспомогательные модули, если они не подключены
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		if (! isset($this->cache["var"][$name]))
		{
			switch($name)
			{
				case 'installed_modules':
				case 'title_modules':
				case 'all_modules':
					$this->cache["var"]["installed_modules"] = array();
					$this->cache["var"]["title_modules"] = array();
					$this->cache["var"]["all_modules"] = DB::query_fetch_all("SELECT * FROM {modules} ORDER BY id ASC");
					foreach($this->cache["var"]["all_modules"] as $m)
					{
						$this->cache["var"]["title_modules"][$m["name"]] = $m["title"];
						$this->cache["var"]["installed_modules"][] = $m["name"];
					}
					break;

				case 'admin_pages':
					$this->cache["var"]["admin_pages"] = DB::query_fetch_key_array("SELECT id, name, rewrite, group_id, parent_id, `add`, add_name, icon_name FROM {admin} WHERE act='1' ORDER BY sort ASC", "parent_id");
					break;

				case '_cache':
					Custom::inc('includes/cache.php');
					$this->cache["var"][$name] = new Cache;
					break;

				case '_route':
					Custom::inc('includes/route.php');
					$this->cache["var"][$name] = new Route($this);
					break;

				case '_db_ex':
					Custom::inc('includes/database_extension.php');
					$this->cache["var"][$name] = new DB_EX($this);
					break;

				case '_tpl':
					Custom::inc('includes/template.php');
					$this->cache["var"][$name] = new Template($this);
					break;

				case '_parser_theme':
					Custom::inc('includes/parser_theme.php');
					$this->cache["var"][$name] = new Parser_theme($this);
					break;

				case '_client':
					Custom::inc('includes/client.php');
					$this->cache["var"][$name] = new Client($this);
					break;

				default:
					// подключаем обработчик запросов модуля
					if (substr($name, 0, 1) == '_')
					{
						$module = substr($name, 1);
						if (Custom::exists('modules/'.$module.'/'.$module.'.inc.php'))
						{
							Custom::inc('includes/model.php');
							Custom::inc('modules/'.$module.'/'.$module.'.inc.php');
							$class = ucfirst($module).'_inc';
							$this->cache["var"][$name] = new $class( $this );
						}
					}
					else
					{
						return $this->_frame->$name;
					}
					break;
			}
		}
		return $this->cache["var"][$name];
	}

	/**
	 * Вызывает методы, определенные в файлах действий
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		if(! $this->_frame)
		{
			return false;
		}
		return call_user_func_array(array(&$this->_frame, $name), $arguments);
	}

	/**
	 * Деструктор класса
	 *
	 * @return void
	 */
	public function destruct()
	{
		DB::close();
	}

	/**
	 * Записывает данные о языках сайта
	 *
	 * @return void
	 */
	private function languages()
	{
		unset($_lang);

		if ($_GET["rewrite"])
		{
			$rew = explode('/', $_GET["rewrite"], 2);
			foreach ($this->_languages->all as $row)
			{
				if ($rew[0] == $row["shortname"] && ! $row["base_admin"])
				{
					$_GET["rewrite"] = preg_replace('/^'.$rew[0].'(\/)*/', '', $_GET["rewrite"]);
					define('_LANG', $row["id"]);
					if(! $row["base_site"])
					{
						define('_SHORTNAME', $row["shortname"].'/' );
					}
					else
					{
						define('_SHORTNAME', '' );
					}
					define('TITLE', ( defined('TIT'.$row["id"]) ? constant('TIT'.$row["id"]) : '' ) );
					define('BASE_PATH_HREF', "http".(IS_HTTPS ? "s" : '')."://".getenv("HTTP_HOST")."/".( REVATIVE_PATH ? REVATIVE_PATH.'/' : '' ).ADMIN_FOLDER.'/'.$row["shortname"].'/' );
					break;
				}
			}
		}
		if (! defined('_LANG'))
		{
			foreach ($this->_languages->all as $row)
			{
				if ($row["base_admin"])
				{
					define('_LANG', $row["id"]);
					if(! $row["base_site"])
					{
						define('_SHORTNAME', $row["shortname"].'/' );
					}
					else
					{
						define('_SHORTNAME', '' );
					}
					define('TITLE', ( defined('TIT'.$row["id"]) ? constant('TIT'.$row["id"]) : '' ) );
					define('BASE_PATH_HREF', "http".(IS_HTTPS ? "s" : '')."://".getenv("HTTP_HOST")."/".( REVATIVE_PATH ? REVATIVE_PATH.'/' : '' ).ADMIN_FOLDER.'/' );
					break;
				}
			}
		}
	}

	/**
	 * Инициирует авторизацию или выход пользователя из системы
	 *
	 * @return void
	 */
	private function user()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			if(! empty($_POST["create"]))
			{
				$_POST["name"] = "demo";
				$_POST["pass"] = "demo";
				$result = $this->_users->auth($_POST);
				$this->redirect($result["redirect"]);
			}
			elseif (strstr($_GET["rewrite"], "logout"))
			{
				Custom::inc('includes/demo.php');
				$demo = new Demo($this);
				$demo->clear($this->_session->id);
				$this->_users->logout();
			}

			if(! $this->_users->id)
			{
				include_once(ABSOLUTE_PATH.'adm/themes/demoauth.php');
				exit;
			}
			Custom::inc('includes/demo.php');
			$demo = new Demo($this);
			$demo->init();
		}
		else
		{
			// поддержка старой версии
			if (! empty($_POST['action']) && $_POST['action'] == 'auth')
			{
				$result = $this->_users->auth($_POST);
				if($result["result"])
				{
					unset($_SESSION["lang_id"]);
					foreach ($this->_languages->all as $row)
					{
						if ($row["base_admin"] && $row["base_admin"] != _LANG)
						{
							$_SESSION["lang_id"] = _LANG;
						}
					}
					$this->redirect('http'.(IS_HTTPS ? "s" : '').'://'.getenv("HTTP_HOST").getenv('REQUEST_URI'));
				}
				else
				{
					$this->_users->errauth = $result["error"];
				}
			}
			elseif (strpos($_GET["rewrite"], "logout") !== false)
			{
				$this->_users->logout();
			}
		}
	}

	/**
	 * Подготавливает запрос для идентификации страницы в таблице {site} по rewrite или по id,
	 * удаляет из строки запроса $_GET[rewrite] переданные переменные
	 *
	 * @return void
	 */
	private function prepare_rewrite()
	{
		if ($_GET["rewrite"])
		{
			$rewrite_array = explode("/", $_GET["rewrite"]);

			foreach ($rewrite_array as $key => $ra)
			{

				foreach ($this->_route->variable_names_admin as $name)
				{
					if (preg_match('/'.$name.'([0-9]+)/', $ra, $result))
					{
						$this->_route->$name = $result[1];
						unset( $rewrite_array[$key] );
					}
				}
			}
			$this->_admin->rewrite = implode("/", $rewrite_array);
		}
		if (! $this->_admin->rewrite)
		{
			if($this->_users->start_admin && $this->_users->roles('init', $this->_users->start_admin))
			{
				$this->_admin->rewrite = $this->_users->start_admin;
			}
			elseif ($this->_users->roles('init', 'dashboard') || !$this->_users->id)
			{
				$this->_admin->rewrite = 'dashboard';
			}
			else
			{
				$rows = DB::query_fetch_all("SELECT id, rewrite FROM {admin} WHERE act='1' ORDER BY id DESC");
				foreach ($rows as $row)
				{
					if ($this->_users->roles('init', $row["rewrite"]))
					{
						$this->_admin->rewrite = $row["rewrite"];
						break;
					}
				}
			}
		}
	}

	/**
	 * Отдает браузеру заголовки страницы
	 *
	 * @return void
	 */
	private function headers()
	{
		if(! $this->_users->id && ! IS_DEMO)
		{
			header('HTTP/1.0 404 Not Found');
		}
		else
		{
			header("Expires: ".date("r"));
			header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Pragma: no-cache");
			header("Cache-Control: post-check=0, pre-check=0", false);
		}
		header('Content-Type: text/html; charset=utf-8');
	}

	/**
	 * Подключает модуль
	 *
	 * @return void
	 */
	private function module()
	{
		if (! $this->_users->roles('init', $this->_admin->rewrite) && $this->_users->id)
		{
			include ABSOLUTE_PATH.'includes/404.php';
		}

		define('URL', $this->get_admin_url());

		Custom::inc("adm/includes/frame.php");

		if (strpos($this->_admin->rewrite, '/') !== false)
		{
			$rew = explode('/', $this->_admin->rewrite);
			foreach ($rew as $k => $v)
			{
				$v = preg_replace('/[^A-Za-z0-9\-\_]+/', '', $v);
				if ($k == 0)
				{
					$rewrite = $v;
					$module = $v.'.admin';
				}
				else
				{
					$module .= '.'.$v;
				}
			}
		}
		else
		{
			$rewrite = preg_replace('/[^A-Za-z0-9\-\_]+/', '', $this->_admin->rewrite);
			$module = $rewrite.'.admin';
		}
		if(in_array($rewrite, $this->installed_modules) && Custom::exists('modules/'.$rewrite.'/admin/'.$module.'.php') && $this->_users->id)
		{
			if(! empty($_GET["parent"]))
			{
				$this->_route->parent = preg_replace('/[^0-9]+/', '', $_GET["parent"]);
			}
			Custom::inc('modules/'.$rewrite.'/admin/'.$module.'.php');
			$this->_admin->module = $rewrite;
			$this->_admin->title_module = (! empty($this->title_modules[$rewrite]) ? $this->title_modules[$rewrite] : '');
		}
		$name_class_module = str_replace('.', '_', ucfirst($module));
		$name_func_module = 'inc_file_'.$rewrite;

		if (function_exists($name_func_module))
		{
			$name_class_module = $name_func_module($this);
		}

		if (in_array($name_class_module, get_declared_classes()))
		{
			$this->_frame = new $name_class_module( $this );
		}
		else
		{
			$this->_frame = new Frame_admin( $this );
		}

		if ($this->_route->parent && $this->_frame->variable_list("plus") && ! DB::query_result("SELECT id FROM {".$this->_frame->table."} WHERE id=%d LIMIT 1", $this->_route->parent))
		{
			if (! DB::query_result("SELECT id FROM {".$this->_frame->table."_category} WHERE id=%d LIMIT 1", $this->_route->parent))
			{
				$this->redirect($this->get_admin_url('parent'));
				exit;
			}
		}

		if ($this->_route->site && $this->_frame->config("element_site") && ! DB::query_result("SELECT id FROM {site} WHERE id=%d LIMIT 1", $this->_route->site))
		{
			$this->redirect(BASE_PATH_HREF.$this->_admin->rewrite.'/');
			exit;
		}

		$this->_frame->init();
	}

	/**
	 * Возвращает текущий адрес страницы без указанных в аргументах переменных, передаваемых в URL
	 *
	 * @return string
	 */
	public function get_admin_url()
	{
		$args = func_get_args();
		return BASE_PATH_HREF
		.($this->_admin->rewrite ? $this->_admin->rewrite."/" : "")
		.($this->_route->page && ! in_array('page', $args) ? "page".$this->_route->page."/" : "")
		.($this->_route->parent && ! in_array('parent', $args) ? "parent".$this->_route->parent."/" : "")
		.($this->_route->cat && ! in_array('cat', $args) ? "cat".$this->_route->cat."/" : "")
		.($this->_route->site && ! in_array('site', $args) ? "site".$this->_route->site."/" : "");
	}

	/**
	 * Отдает значение перевода строки
	 *
	 * @param string $name текст для перевода
	 * @return string
	 */
	public function _($name)
	{
		$args = func_get_args();
		unset($args[0]);
		return $this->_languages->get($name, $this->_admin->module, false, $args);
	}
}
