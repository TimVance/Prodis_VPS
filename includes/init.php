<?php
/**
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

define('VERSION', 'DIAFAN.CMS version 6.0.10.10');

if (! defined('DIAFAN'))
{
	define('DIAFAN', 1);
	try
	{
		if(! defined('ABSOLUTE_PATH'))
		{
			$path = __FILE__;
			while(! file_exists($path.'/index.php') || ! file_exists($path.'/config.php'))
			{
				$parent = dirname($path);
				if($parent == $path)
				{
					throw new Exception('Absolute path was not found');
				}
				$path = $parent;
			}
			define('ABSOLUTE_PATH', $path.'/');
		}
		include_once(ABSOLUTE_PATH."config.php");
		include_once ABSOLUTE_PATH.'includes/custom.php';
		Custom::inc('includes/database.php');
		if(! $VERSION = Custom::version_core())
		{
			throw new Exception('Empty record');
		}
	}
	catch (Exception $e)
	{
		$VERSION = '';
	}
	$VERSION = $VERSION && defined('VERSION_CMS') && VERSION_CMS != $VERSION && VERSION != 'DIAFAN.CMS version '.$VERSION ? "DIAFAN.CMS " . $VERSION : VERSION;
	echo $VERSION; exit;
}

/**
 * Init
 *
 * Основной класс системы
 */
class Init extends Core
{
	/**
	 * @var object текущий объект
	 */
	public $diafan;

	/**
	 * @var object исполняемый модуль
	 */
	public $module;

	/**
	 * @var string модуль текущего исполняемого файла
	 */
	public $current_module;

	/**
	 * @var string текущий шаблонный тег
	 */
	public $current_insert_tag;

	/**
	 * @var object локальный объект отложенной загрузки
	 */
	private $defer;

	/**
	 * @var object локальный объект загрузки дополнительных элементов страницы
	 */
	private $more;

	/**
	 * var array локальный кэш файла
	 */
	private $cache;

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
	}

	/**
	 * Инициализирует генерирование страницы
	 *
	 * @return void
	 */
	public function start()
	{
		Custom::inc('plugins/encoding.php');

		Custom::inc('includes/controller.php');

		Custom::inc('includes/model.php');

		Custom::inc('includes/action.php');

		Custom::inc('includes/api.php');
		Dev::set_profiler();

		define('MAIN_DOMAIN', $this->domain(true));
		Custom::inc('includes/session.php');
		$this->_session = new Session($this);
		$this->_session->init();

		if(! defined('IS_DEMO') || ! IS_DEMO)
		{
			$this->get_redirect();
		}

		if(defined('IS_DEMO') && IS_DEMO)
		{
			$this->user();
			$this->get_redirect();
		}

		if(! defined('EMAIL_CONFIG'))
		{
			define('EMAIL_CONFIG', ($this->configmodules("email", 'postman') ?: ''));
		}

		$this->magic_quote_off();
		if(! defined('IS_DEMO') || ! IS_DEMO)
		{
			$this->user();
		}

		define('MAIN_PATH', "http".(IS_HTTPS ? "s" : '')."://".MAIN_DOMAIN."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : ''));
		$this->languages();


		$this->include_other_file();

		$this->prepare_rewrite();

		$this->module();

		$this->_postman->defer_init();

		if (MOD_DEVELOPER_TECH && ! $this->_users->roles('init', $this->_site->module ? $this->_site->module : 'site'))
		{
			if(!  $this->_site->module || ! $this->_users->roles('init', 'site')
			   || ! DB::query_result("SELECT id FROM {modules} WHERE name='%h' AND site='1' AND admin='0' LIMIT 1", $this->_site->module))
			{
				Custom::inc('includes/503.php');
			}
		}
		$this->headers();
		$this->_parser_theme->show_theme();

		$this->finish_rewrite();

		Dev::get_profiler();
	}

	/**
	 * Деструктор класса
	 *
	 * @return void
	 */
	public function destruct()
	{
		DB::close();
		$this->_cache->close();
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
					$this->cache["var"][$name] = DB::query_fetch_value("SELECT name FROM {modules} WHERE site='1'", "name");
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
						if((! defined('IS_INSTALL') || ! IS_INSTALL)
						   && ! in_array($module, array('users', 'languages'))
						   && ! in_array($module, $this->installed_modules))
						{
							$this->cache["var"][$name] = new Empty_inc();
						}
						else
						{
							Custom::inc('includes/model.php');
							if (Custom::exists('modules/'.$module.'/'.$module.'.inc.php'))
							{
								Custom::inc('modules/'.$module.'/'.$module.'.inc.php');
								$class = ucfirst($module).'_inc';
								$this->cache["var"][$name] = new $class($this, $module);
							}
						}
					}
					else
					{
						$this->cache["var"][$name] = null;
					}
					break;
			}
		}
		return $this->cache["var"][$name];
	}

	/**
	 * Выполняет 301 редирект
	 *
	 * @return void
	 */
	private function get_redirect()
	{
		$url = rawurldecode(getenv('REQUEST_URI'));
		$url = preg_replace('/^\/'.(REVATIVE_PATH ? preg_quote(REVATIVE_PATH, '/').'\/' : '').'/', '', $url);
		if($row = DB::query_fetch_array("SELECT * FROM {redirect} WHERE redirect='%s' LIMIT 1", $url))
		{
			if(! $row["code"])
			{
				$row["code"] = 301;
			}
			header("Cache-Control: no-cache");
			$site_id = 0;
			$this->redirect(BASE_PATH.$this->_route->link($site_id, $row["element_id"], $row["module_name"], $row["element_type"]), $row["code"]);
		}
		if(preg_match('/^\/\/(.*)$/', getenv('REQUEST_URI'), $m))
		{
			$this->redirect('http'.(IS_HTTPS ? "s" : '').'://'.getenv("HTTP_HOST").'/'.$m[1]);
			exit;
		}
		if(preg_match('/^\/(.*)\/\/(.*)$/', getenv('REQUEST_URI'), $m))
		{
			$this->redirect('http'.(IS_HTTPS ? "s" : '').'://'.getenv("HTTP_HOST").'/'.$m[1].'/'.$m[2]);
			exit;
		}
	}

	/**
	 * Подключает файлы для решения нестандартых задач (вывод капчи, вывод прикрепленных файлов...)
	 *
	 * @return void
	 */
	private function include_other_file()
	{
		if (! empty($_GET["rewrite"]))
		{
			$rewrite_array = explode("/", $_GET["rewrite"]);
			$count = count($rewrite_array);

			// API
			if($count > 1 && $rewrite_array[0] == 'api' && ! in_array($rewrite_array[0], $this->installed_modules))
			{
				$module = preg_replace('/[^a-z0-9_]+/', '', $rewrite_array[1]);
				$installed_modules = $this->installed_modules;
				if(! in_array('account', $installed_modules)) $installed_modules[] = 'account';
				if(in_array($module, $installed_modules) && Custom::exists('modules/'.$module.'/'.$module.'.php')
				&& Custom::exists('modules/'.$module.'/'.$module.'.api'.'.php'))
				{
					$method = false;
					if($count > 2)
					{
						$method = preg_replace('/[^a-z0-9_]+/', '', $rewrite_array[2]);
						$method = ! empty($method) ? $method : false;
					}
					unset($rewrite_array[0]);
					unset($rewrite_array[1]);
					$count -= 2;
					if($method)
					{
						unset($rewrite_array[2]);
						$count--;
					}
					if($count > 0) $_GET["rewrite"] = implode('/', $rewrite_array);
					else $_GET["rewrite"] = '';
					$this->diafan = $this;
					Custom::inc('modules/'.$module.'/'.$module.'.php');
					$this->current_module = $module;
					$name_class_module = ucfirst($module);
					$this->module = new $name_class_module($this);
					$this->module->api->prepare($module, $method);
					if(! $this->module->api->only_https || $this->module->api->only_https && IS_HTTPS)
					{
						$this->module->api();
					}
					$this->module->api->end();
				}
			}

			// other file
			if($count > 1 && ! in_array($rewrite_array[1], array('model', 'view', 'admin', 'inc', 'action', 'api'))
			&& in_array($rewrite_array[0], $this->installed_modules))
			{
				$path = false;
				if(Custom::exists('modules/'.$rewrite_array[0].'/'.$rewrite_array[0].'.'.(implode('.', array_slice($rewrite_array, 1))).'.php'))
				{ // TO_DO: for example service.express.export.php
					$path = 'modules/'.$rewrite_array[0].'/'.$rewrite_array[0].'.'.(implode('.', array_slice($rewrite_array, 1))).'.php';
					$rewrite_array = array();
				}
				elseif(Custom::exists('modules/'.$rewrite_array[0].'/'.$rewrite_array[0].'.'.$rewrite_array[1].'.php'))
				{ // TO_DO: for example shop.export.php
					$path = 'modules/'.$rewrite_array[0].'/'.$rewrite_array[0].'.'.$rewrite_array[1].'.php';
					unset($rewrite_array[0]);
					unset($rewrite_array[1]);
				}
				if($path)
				{
					$this->diafan = $this;
					$_GET["rewrite"] = implode('/', $rewrite_array);
					include_once(Custom::path($path));
				}
			}
		}

		if (strpos($_GET["rewrite"], 'sitemap.xml') !== false)
		{
			$this->diafan = $this;
		    include_once(Custom::path('modules/map/map.sitemap.php'));
		}

		if (! ROUTE_END && ! empty($_GET["rewrite"]) && $_GET["rewrite"] != 'admin_reminding'
			&& preg_match('/^\/'.(REVATIVE_PATH ? REVATIVE_PATH.'\/' : '').'(.*)\/$/', $_SERVER["REQUEST_URI"], $m))
		{
			if(! is_dir(ABSOLUTE_PATH.$m[1]))
			{
				header('Location: http'.(IS_HTTPS ? "s" : '').'://'.$_SERVER["HTTP_HOST"]."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : '').$m[1], true, 301);
				exit;
			}
		}

		if (ROUTE_END && ! empty($_GET["rewrite"]) && $_GET["rewrite"] != 'admin_reminding' && ! preg_match('/'.preg_quote(ROUTE_END, '/').'$/', $_SERVER["REQUEST_URI"]))
		{
			$get = $_GET;
			unset($get["rewrite"]);
			if(empty($get))
			{
				header('Location: '.preg_replace('/\/$/', '', $_SERVER["REQUEST_URI"]).ROUTE_END, true, 301);
				exit;
			}
		}
	}

	/**
	 * Подготавливает запрос для идентифицикации страницы в таблице {site} по rewrite или по id,
	 * удаляет из строки запроса $_GET[rewrite] переданные переменные
	 *
	 * @return void
	 */
	private function prepare_rewrite()
	{
		if($_GET["rewrite"] == 'admin_reminding')
		{
			$this->_site->id     = 1;
			$this->_site->module  = 'reminding';
			$this->_site->rewrite = $_GET["rewrite"];
			return true;
		}
		if(in_array('visitors', $this->installed_modules))
		{
			$this->_visitors->prepare_rewrite();
		}
		$arguments_in_url = false;
		if ($this->rewrite($_GET["rewrite"], $arguments_in_url))
		{
			return true;
		}
		if ($_GET["rewrite"])
		{
			$rewrite_array = explode("/", preg_replace('/'.preg_quote(ROUTE_END, '/').'$/', '', $_GET["rewrite"]));
			foreach ($this->_route->variable_names_site as $name)
			{
				$this->_route->$name = 0;
			}

			foreach ($rewrite_array as $key => $ra)
			{
				foreach ($this->_route->variable_names_site as $name)
				{
					if (preg_match('/^'.$name.'([0-9]+)$/', $ra, $result))
					{
						$this->_route->$name = $result[1];
						unset($rewrite_array[$key]);
						$arguments_in_url = true;
					}
				}
			}

			$_GET["rewrite"] = implode("/", $rewrite_array);
			if (! $this->rewrite($_GET["rewrite"], $arguments_in_url))
			{
				if(! $redirect = $this->_route->search($_GET["rewrite"], $arguments_in_url, false))
				{
					$redirect = $this->_route->search($_GET["rewrite"], $arguments_in_url, false, true);
				}
				if(! empty($redirect["rewrite"]))
				{
					$redirect = preg_replace('/^'.preg_quote('/'.$_GET["rewrite"], '/').'/', $redirect["rewrite"], $_SERVER["REQUEST_URI"], 1);
				}
				else $redirect = false;
				$get = $_GET;
				unset($get["rewrite"]);
				if(empty($get) && $redirect && $redirect != $_GET["rewrite"])
				{
					$this->redirect(BASE_PATH_HREF.$redirect.ROUTE_END, 301);
				}

				include(ABSOLUTE_PATH.Custom::path('includes/404.php'));
			}
		}
	}

	/**
	 * Получаем страницу по псевдоссылке
	 *
	 * @param string $rewrite текущая псевдоссылка
	 * @param boolean $arguments_in_url в URL переданы аргументы
	 * @return boolean
	 */
	public function rewrite($rewrite, $arguments_in_url)
	{
		if($row = $this->_route->search($rewrite, $arguments_in_url))
		{
			$row["site_id"] = 0;
			if($row["module_name"] == 'site')
			{
				$row["site_id"] = $row["element_id"];
				$row["element_type"] = '';
			}
			else
			{
				switch($row["element_type"])
				{
					case 'param':
						break;

					case 'cat':
						$table = $row["module_name"].'_category';
						break;

					case 'element':
						$table = $row["module_name"];
						$row["element_type"] = 'show';
						break;

					default:
						$table = $row["module_name"].'_'.$row["element_type"];
						break;
				}
				if(isset($table))
				{
					$e = DB::query_fetch_array("SELECT * FROM {%s} WHERE id=%d", $table, $row["element_id"]);
					if(! empty($e))
					{
						if(isset($e["site_id"]))
						{
							if(! $e["site_id"])
							{
								Custom::inc('includes/404.php');
							}
							$row["site_id"] = $e["site_id"];
						}
					}
				}
			}
			$name = $row["element_type"];
			if($name)
			{
				if($this->_route->$name)
				{
					Custom::inc('includes/404.php');
				}
				else
				{
					$this->_route->$name = $row["element_id"];
				}
			}
			$this->_site->id      = $row["site_id"];
			$this->_site->module  = $row["module_name"];
			$this->_site->rewrite = $row["rewrite"];
			if (! $this->_site->rewrite)
			{
				$this->_site->id = empty($_GET["url"]) ? 1 : intval($_GET["url"]);
			}
			$this->_site->set();
			$element_types = array('param', 'show', 'cat', 'brand');
			foreach($element_types as $element_type)
			{
				if($name != $element_type && $this->_route->$element_type)
				{
					// в одной строке не может быть несколько элементов
					if($name && $element_type != 'brand')
					{
						Custom::inc('includes/404.php');
					}
					// если элемент задан в URL, но есть ЧПУ для этого элемента,
					// то делаем редирект, чтобы избавиться от дублей
					if($element_type != 'brand' && ! $name)
					{
						$redirect = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='%s' AND element_type='%s' AND element_id=%d", $this->_site->module, ($element_type == 'show' ? 'element' : $element_type), $this->_route->$element_type);
					}
				}
			}
			if(! empty($redirect))
			{
				$this->redirect(BASE_PATH_HREF.$redirect.ROUTE_END, 301);
			}
			return true;
		}
		return false;
	}

	/**
	 * Проверка переданных переменых в запросе $_GET[rewrite]
	 *
	 * @return void
	 */
	private function finish_rewrite()
	{
		Custom::inc('modules/paginator/paginator.inc.php');
		$rewrite_variable_names = Paginator_inc::$variables;
		foreach($rewrite_variable_names as $key => $name)
		{
			if(in_array($name, $this->_route->rewrite_variable_names))
			{
				continue;
			}
			unset($rewrite_variable_names[$key]);
		}
		$rewrite_variable_names = array_merge($this->module->rewrite_variable_names, $rewrite_variable_names);
		$rewrite_variable_names = array_unique($rewrite_variable_names);
		foreach($this->_route->rewrite_variable_names as $name)
		{
			if(in_array($name, $rewrite_variable_names))
			{
				continue;
			}
			if($this->_route->$name || $this->_route->$name === '0')
			{
				if($level = ob_get_level())
				{
					for ($i=0; $i < $level; $i++)
					{
						ob_end_clean();
					}
				}
				Custom::inc('includes/404.php');
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
		if($this->_site->rewrite == 'admin_reminding')
		{
			header('HTTP/1.0 404 Not Found');
		}
		// проверяем, отослал ли браузер заголовок If-Modified-Since
		elseif (getenv('HTTP_IF_MODIFIED_SINCE') && $this->_site->timeedit && $this->_site->timeedit <= strtotime(getenv('HTTP_IF_MODIFIED_SINCE')))
		{
			header("HTTP/1.1 304 Not Modified");
			header("Last-Modified: ".gmdate("D, d M Y H:i:s", $this->_site->timeedit)." GMT");
			exit;
		}
		header("Last-Modified: ".(! $this->_site->timeedit ? gmdate("D, d M Y H:i:s") : gmdate("D, d M Y H:i:s", $this->_site->timeedit))." GMT");
		//header("Expires: ".gmdate("D, d M Y H:i:s", $this->_site->timeedit + 300));
		header("Cache-control: private, no-cache, no-store");
		//header("Cache-Control: max-age=30, must-revalidate");
		if(NO_X_FRAME)
		{
			header("X-Frame-Options: SAMEORIGIN");
		}
		header('Content-Type: text/html; charset=utf-8');
	}

	/**
	 * Подключает модуль, прикрепленный к странице
	 *
	 * @return void
	 */
	private function module()
	{
		// подключаем обработчик запросов отложенной загрузки
		if (isset($_POST["defer"]))
		{
			$this->defer = new Defer_action($this);
			$this->defer->action();
			$this->defer->end();
		}

		// подключаем обработчик запросов модуля
		if (! empty($_POST["module"]))
		{
			$module = preg_replace('/[^a-z0-9_]+/', '', $_POST["module"]);
			Custom::inc('modules/'.$module.'/'.$module.'.php');
			$this->current_module = $module;
			$name_class_module = ucfirst($module);
			$this->module = new $name_class_module($this);

			// подключаем обработчик запросов загрузки дополнительных элементов страницы
			if(! empty($_POST["more"]) && isset($_POST["mode"]) && $_POST["mode"] == 'model')
			{
				$this->more = new More_action($this);
				$this->more->action();
				$this->more->end();
			}

			$this->module->action();
			$this->module->action->end();
		}

		if(in_array('visitors', $this->installed_modules))
		{
			$this->_visitors->counter_init();
		}

		if ($this->_site->module && Custom::exists('modules/'.$this->_site->module.'/'.$this->_site->module.'.php'))
		{
			Custom::inc('modules/'.$this->_site->module.'/'.$this->_site->module.'.php');
			$this->current_module = $this->_site->module;
			$name_class_module = ucfirst($this->_site->module);
			$this->module = new $name_class_module($this);
			$this->module->init();
			$this->module->get_global_variables();
			$this->current_module = '';
			foreach ($this->_route->variable_names_site as $name)
			{
				if ($this->_route->$name && ! in_array($name, $this->module->rewrite_variable_names) && ! in_array($name, $this->_route->rewrite_variable_names))
				{
					Custom::inc('includes/404.php');
				}
			}
		}
		else
		{
			foreach ($this->_route->variable_names_site as $name)
			{
				if ($this->_route->$name && ! in_array($name, $this->_route->rewrite_variable_names))
				{
					Custom::inc('includes/404.php');
				}
			}
			$this->module = new Controller($this);
		}
	}

	/**
	 * Определяет является ли текущая версия мобильной
	 *
	 * @return void
	 */
	private function mobile_version()
	{
		$is_mobile = false;
		if(MOBILE_VERSION)
		{
			$domain = $this->domain();
			$rew = MOBILE_SUBDOMAIN ? explode('.', $domain, 2) : explode('/', $_GET["rewrite"], 2);
			if($rew[0] == MOBILE_PATH)
			{
				if(MOBILE_SUBDOMAIN)
				{
					$domain = (! empty($rew[1]) ? $rew[1] : $domain);
				}
				else
				{
					$_GET["rewrite"] = (! empty($rew[1]) ? $rew[1] : '');
				}
				$is_mobile = true;
			}
			if(! empty($_GET["mobile"]) && $_GET["mobile"] == 'no')
			{
				$_SESSION["mobile_no"] = true;
			}
			if(! $is_mobile && empty($_SESSION["mobile_no"]))
			{
				Custom::inc('plugins/mobile_detect.php');
				$detect = new Mobile_Detect;
				if($detect->isMobile())
				{
					$lang = '';
					foreach ($this->_languages->all as $language)
					{
						if ($language["id"] == _LANG && ! $language["base_site"])
						{
							$lang = $language["shortname"];
						}
					}
					$base_path = MOBILE_SUBDOMAIN ? "http".(IS_HTTPS ? "s" : '')."://". MOBILE_PATH.'.' .$domain."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : '') : BASE_PATH;
					$mobile_path = MOBILE_SUBDOMAIN ? '' : MOBILE_PATH.'/';
					$this->redirect($base_path.($lang ? $lang.'/' : '').$mobile_path.preg_replace('/^\/'.(REVATIVE_PATH ? preg_quote(REVATIVE_PATH, '/').'\/' : '').($lang ? preg_quote($lang, '/').'(\/)*' : '').'/', '', getenv('REQUEST_URI')), 301);
				}
			}
		}
		define('IS_MOBILE', $is_mobile);
	}

	/**
	 * Записывает данные о языках сайта
	 *
	 * @return void
	 */
	private function languages()
	{
		if ($_GET["rewrite"] || defined('_LANG') && _LANG)
		{
			$rew = explode('/', $_GET["rewrite"], 2);
			foreach ($this->_languages->all as $language)
			{
				if (! defined('_LANG') && $rew[0] == $language["shortname"] || defined('_LANG') && $language["id"] == _LANG)
				{
					$_GET["rewrite"] = preg_replace('/^'.preg_quote($rew[0], '/').'(\/)*/', '', $_GET["rewrite"]);
					if (! defined('_LANG'))
					{
						define('_LANG', $language["id"]);
					}
					if($language["base_site"])
					{
						Custom::inc('includes/404.php');
					}
					$this->mobile_version();
					define('MAIN_PATH_HREF', MAIN_PATH.(! $language["base_site"] ? $language["shortname"].'/' : ''));
					define('MAIN_URL', MAIN_DOMAIN.(REVATIVE_PATH ? '/'.REVATIVE_PATH : '').(! $language["base_site"] ? '/'.$language["shortname"] : ''));
					if(defined('MOBILE_VERSION') && MOBILE_VERSION)
					{
						define('MOBILE_PATH_HREF', (MOBILE_SUBDOMAIN ? "http".(IS_HTTPS ? "s" : '')."://". MOBILE_PATH.'.' .MAIN_DOMAIN."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : '') : BASE_PATH).(! $language["base_site"] ? $language["shortname"].'/' : '').(! MOBILE_SUBDOMAIN ? MOBILE_PATH.'/' : ''));
					}
					else define('MOBILE_PATH_HREF', MAIN_PATH_HREF);
					define('BASE_PATH_HREF', (IS_MOBILE && MOBILE_SUBDOMAIN ? "http".(IS_HTTPS ? "s" : '')."://". MOBILE_PATH.'.' .MAIN_DOMAIN."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : '') : BASE_PATH).(! $language["base_site"] ? $language["shortname"].'/' : '').(IS_MOBILE && ! MOBILE_SUBDOMAIN ? MOBILE_PATH.'/' : ''));
					define('BASE_URL', (IS_MOBILE && MOBILE_SUBDOMAIN ? MOBILE_PATH.'.' : '').MAIN_DOMAIN.(REVATIVE_PATH ? '/'.REVATIVE_PATH : '').(! $language["base_site"] ? '/'.$language["shortname"] : '').(IS_MOBILE && ! MOBILE_SUBDOMAIN ? '/'.MOBILE_PATH : ''));
					if (defined('TIT'.$language["id"]))
					{
						define('TITLE', constant('TIT'.$language["id"]));
					}
					else
					{
						define('TITLE', '');
					}
					break;
				}
			}
		}

		if (! defined('_LANG'))
		{
			foreach ($this->_languages->all as $row)
			{
				if ($row["base_site"])
				{
					define('_LANG', $row["id"]);
					$this->mobile_version();
					define('TITLE', ( defined('TIT'.$row["id"]) ? constant('TIT'.$row["id"]) : '' ) );
					define('BASE_PATH_HREF', (IS_MOBILE && MOBILE_SUBDOMAIN ? "http".(IS_HTTPS ? "s" : '')."://". MOBILE_PATH.'.' .MAIN_DOMAIN."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : '') : BASE_PATH).(IS_MOBILE && ! MOBILE_SUBDOMAIN ? MOBILE_PATH.'/' : ''));
					define('BASE_URL', (IS_MOBILE && MOBILE_SUBDOMAIN ? MOBILE_PATH.'.' : '').MAIN_DOMAIN.(REVATIVE_PATH ? '/'.REVATIVE_PATH : '').(IS_MOBILE && ! MOBILE_SUBDOMAIN ? '/'.MOBILE_PATH : ''));
					define('MAIN_PATH_HREF', MAIN_PATH);
					define('MAIN_URL', MAIN_DOMAIN.(REVATIVE_PATH ? '/'.REVATIVE_PATH : ''));
					if(defined('MOBILE_VERSION') && MOBILE_VERSION)
					{
						define('MOBILE_PATH_HREF', (MOBILE_SUBDOMAIN ? "http".(IS_HTTPS ? "s" : '')."://". MOBILE_PATH.'.' .MAIN_DOMAIN."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : '') : BASE_PATH).(! MOBILE_SUBDOMAIN ? MOBILE_PATH.'/' : ''));
					}
					else define('MOBILE_PATH_HREF', MAIN_PATH_HREF);
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
			elseif (! empty($_POST['token']))
			{
				$this->_users->auth_loginza();
			}
			elseif (strstr($_GET["rewrite"], "logout"))
			{
				Custom::inc('includes/demo.php');
				$demo = new Demo($this);
				$demo->clear($this->_session->id);
				$this->_users->logout();
				return;
			}
			if(! $this->_users->id)
			{
				require_once(ABSOLUTE_PATH.'plugins/idna.php');
				$IDN = new idna_convert(array('idn_version' => '2008'));
				$domain = $IDN->decode(getenv("HTTP_HOST"));
				define('BASE_URL', ($domain ? $domain : getenv("HTTP_HOST")).(REVATIVE_PATH ? '/'.REVATIVE_PATH : ''));

				Custom::inc('adm/themes/demoauth.php');
				exit;
			}
			Custom::inc('includes/demo.php');
			$demo = new Demo($this);
			$demo->init();
		}
		else
		{
			// поддержка старой версии
			if (! empty($_POST['action']) && $_POST['action'] == 'auth' && empty($_POST["module"]))
			{
				if (! defined('_LANG'))
				{
					foreach ($this->_languages->all as $row)
					{
						if ($row["base_site"])
						{
							define('_LANG', $row["id"]);
						}
					}
				}
				$result = $this->_users->auth($_POST);
				if($result["result"])
				{
					$this->redirect($result["redirect"]);
				}
				else
				{
					$this->_users->errauth = $result["error"];
				}
			}
			elseif (! empty($_POST['token']))
			{
				$this->_users->auth_loginza();
			}
			elseif (strpos($_GET["rewrite"], "logout") !== false)
			{
				$this->_users->logout();
			}
		}
	}

	/**
	 * Отдает значение перевода строки
	 *
	 * @param string $name текст для перевода
	 * @param boolean $useradmin выдавать форму для редактирования
	 * @return string
	 */
	public function _($name, $useradmin = true)
	{
		$args = func_get_args();
		unset($args[0]);
		if(isset($args[1]))
		{
			unset($args[1]);
		}
		if(! empty($_POST["ajax"]))
		{
			$useradmin = false;
		}
		return $this->_languages->get($name, $this->current_module, $useradmin, $args);
	}
}

class Empty_inc
{
	public function __call($name, $args)
	{
		return false;
	}

	public function __get($name)
	{
		return false;
	}
}
