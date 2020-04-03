<?php
/**
 * Подключение модуля «Посещаемость» для работы с счетчиком статистических данных
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if ( ! defined('DIAFAN'))
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
 * Visitors_inc_counter
 */
class Visitors_inc_counter extends Diafan
{
	/**
     * @var string версия счетчика модуля «Посещаемость»
     */
	const VISITORS_VERSION = "1.0.0.0";

	/**
     * @var string URL-адрес счетчика модуля «Посещаемость»
     */
	const URL = 'visitors/counter/';

	/**
     * @var integer интервал с момента последней активности, в течении которого пользователь не считается уникальным
     */
	const ACTIVE_INTERVAL = 86400; // значение в секундах - 1 сутки

	/**
	 * @var integer задержка, при которой допускается, что посетитель, запросивший контент страницы, активен на сайте
	 */
	const DELAY_ACTIVITY_USER = 900; // значение в секундах - 15 минут

	/**
	 * @var integer задержка, при которой допускается, что посетитель, запросивший контент страницы, активен на сайте
	 */
	const DELAY_ACTIVITY_BOT = 30; // значение в секундах - 30 секунд

	/**
	 * @var boolean интервал динамического формирования статистики трафика
	 */
	const TRAFFIC_LIFETIME = 86400; // значение в секундах - 1 день

	/**
	 * @var integer максимальное количество попыток валидации пользовательского агента
	 */
	const MAX_VALID_COUNT = 3;

	/**
	 * @var array идентификаторы персональных скидок текущего пользователя
	 */
	private $bots = array(
		// Yandex
		'YandexBot', 'YandexAccessibilityBot', 'YandexMobileBot', 'YandexDirectDyn', 'YandexScreenshotBot',
		'YandexImages', 'YandexVideo', 'YandexVideoParser', 'YandexMedia', 'YandexBlogs', 'YandexFavicons',
		'YandexWebmaster', 'YandexPagechecker', 'YandexImageResizer', 'YandexAdNet', 'YandexDirect',
		'YaDirectFetcher', 'YandexCalendar', 'YandexSitelinks', 'YandexMetrika', 'YandexNews',
		'YandexNewslinks', 'YandexCatalog', 'YandexAntivirus', 'YandexMarket', 'YandexVertis',
		'YandexForDomain', 'YandexSpravBot', 'YandexSearchShop', 'YandexMedianaBot', 'YandexOntoDB',
		'YandexOntoDBAPI', 'YandexTurbo', 'YandexVerticals', 'YandexSomething', 'Yandex',

		// Google
		'Googlebot-Image', 'Googlebot', 'Mediapartners-Google', 'AdsBot-Google', 'APIs-Google',
		'AdsBot-Google-Mobile', 'AdsBot-Google-Mobile', 'Googlebot-News', 'Googlebot-Video',
		'AdsBot-Google-Mobile-Apps', 'Google-Sitemaps', 'appEngine-Google', 'feedfetcher-Google',

		// Other
		'mail.ru', 'Mail.RU_Bot', 'bingbot', 'bing.com', 'rambler', 'aport', 'Nigma.ru', 'Accoona',
		'ia_archiver', 'ask.com', 'Ask Jeeves', 'OmniExplorer_Bot', 'W3C_Validator', 'WebAlta',
		'YahooFeedSeeker', 'Yahoo', 'Ezooms', 'Tourlentabot', 'MJ12bot', 'AhrefsBot', 'SearchBot',
		'SiteStatus', 'Baiduspider', 'Statsbot', 'SISTRIX', 'AcoonBot', 'findlinks', 'proximic',
		'OpenindexSpider', 'statdom.ru', 'Exabot', 'Spider', 'SeznamBot', 'oBot', 'C-T bot', 'Updownerbot',
		'Snoopy', 'heritrix', 'Yeti', 'DomainVader', 'DCPbot', 'PaperLiBot', 'StackRambler', 'msnbot',
		'msnbot-media', 'msnbot-news', 'turtle', 'omsktele', 'yetibot', 'picsearch', 'sape.bot',
		'sape_context', 'gigabot', 'snapbot', 'alexa.com', 'megadownload.net', 'askpeter.info', 'igde.ru',
		'qwartabot', 'yanga.co.uk', 'scoutjet', 'similarpages', 'oozbot', 'shrinktheweb.com', 'aboutusbot',
		'followsite.com', 'dataparksearch', 'liveinternet.ru', 'xml-sitemaps.com', 'agama', 'metadatalabs.com',
		'h1.hrn.ru', 'googlealert.com', 'seo-rus.com', 'yaDirectBot', 'yandeG', 'Copyscape.com','domaintools.com',
		'dotnetdotcom',
    );

	/**
	 * Инициирует счетчик статистических данных
	 *
	 * @param boolean $only_valid  инициализация только валидации пользовательского агента
	 * @return void
	 */
	public function init($only_valid = false)
	{
		if(! $this->is_enable())
		{
			return false;
		}

		// метка счетчика статистических данных
		$visitors_version = self::VISITORS_VERSION.'_'.$this->diafan->configmodules('counter_timeedit_installed', 'visitors');
		// основные параметры сессии
		$is_new = ! isset($_SESSION["visitors"]);
		if(! isset($_SESSION["visitors"]["v"]) || $_SESSION["visitors"]["v"] != $visitors_version)
		{
			$_SESSION["visitors"] = array("v" => $visitors_version);
			$is_new = true;
		}

		// маркер инициализация только валидации пользовательского агента
		$_SESSION["visitors"]["only_valid"] = $only_valid;

		// валидация пользовательского агента
		$this->validator();

		// параметры сессии
		if($is_new || empty($_SESSION["visitors"]["SID"])) $_SESSION["visitors"]["SID"] = $this->diafan->_session->id;
		if($_SESSION["visitors"]["SID"] != $this->diafan->_session->id)
		{
			$OLD_SID = $_SESSION["visitors"]["SID"];
			$_SESSION["visitors"]["SID"] = $this->diafan->_session->id;
		}
		else $OLD_SID = false;
		$_SESSION["visitors"]["timestamp"] = DB::query_result(
			"SELECT timestamp FROM {sessions} WHERE session_id='%s' AND user_agent='%s' LIMIT 1",
			$_SESSION["visitors"]["SID"], getenv('HTTP_USER_AGENT')
		);
		if(! $_SESSION["visitors"]["timestamp"]) $_SESSION["visitors"]["timestamp"] = time();
		if(empty($_SESSION["visitors"]["create"])) $_SESSION["visitors"]["create"] = $_SESSION["visitors"]["timestamp"];
		if(! empty($_SESSION["visitors"]["timeedit"])) $last_timeedit = $_SESSION["visitors"]["timeedit"];
		else $last_timeedit = 0;
		$_SESSION["visitors"]["timeedit"] = time();

		$url = 'http'.(IS_HTTPS ? "s" : '').'://'.getenv("HTTP_HOST").getenv('REQUEST_URI');
		if(! $query = parse_url($url, PHP_URL_QUERY)) $query = '';
		if(! $fragment = parse_url($url, PHP_URL_FRAGMENT)) $fragment = '';
		$query = (! empty($query) ? '?'.$query : '').(! empty($fragment) ? '#'.$fragment : '');
		if(! $referer = parse_url(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : getenv("HTTP_REFERER"))) $referer = array();

		if(defined('IS_ADMIN') && IS_ADMIN)
		{
			if(! isset($_SESSION["visitors"]["admin"]["is_new"])) $_SESSION["visitors"]["admin"]["is_new"] = true;
			else $_SESSION["visitors"]["admin"]["is_new"] = false;
			if(! empty($_SESSION["visitors"]["admin"]["timeedit"])) $_SESSION["visitors"]["admin"]["last_timeedit"] = $_SESSION["visitors"]["admin"]["timeedit"];
			else $_SESSION["visitors"]["admin"]["last_timeedit"] = 0;
			$_SESSION["visitors"]["admin"]["timeedit"] = time();
		}
		else
		{
			if(! isset($_SESSION["visitors"]["site"]["is_new"])) $_SESSION["visitors"]["site"]["is_new"] = true;
			else $_SESSION["visitors"]["site"]["is_new"] = false;
			if(! empty($_SESSION["visitors"]["site"]["timeedit"])) $_SESSION["visitors"]["site"]["last_timeedit"] = $_SESSION["visitors"]["site"]["timeedit"];
			else $_SESSION["visitors"]["site"]["last_timeedit"] = 0;
			$_SESSION["visitors"]["site"]["timeedit"] = time();
		}

		// дополнительные параметры сессии
		$params = $_SESSION["visitors"];
		$params["OLD_SID"] = $OLD_SID;
		$params["hostname"] = getenv("HTTP_CLIENT_IP") ?: getenv("HTTP_X_FORWARDED_FOR") ?: getenv("HTTP_X_REAL_IP") ?: getenv("REMOTE_ADDR");
		$params["last_timeedit"] = $last_timeedit;
		$params["is_new"] = $is_new;

		$params["referer_scheme"] = (! empty($referer["scheme"]) && $referer["scheme"] == 'https' ? 1 : 0);
		$params["referer_domain"] = (! empty($referer["host"]) ? $referer["host"] : '');
		$params["referer_rewrite"] = (! empty($referer["path"]) ? $referer["path"] : '');
		$params["referer_query"] = (! empty($referer["query"]) ? '?'.$referer["query"] : '').(! empty($referer["fragment"]) ? '#'.$referer["fragment"] : '');
		$params["scheme"] = (IS_HTTPS ? 1 : 0);
		$params["domain"] = getenv("HTTP_HOST");
		$params["rewrite"] = $_GET["rewrite"];
		$params["query"] = $query;

		$params["user_agent"] = getenv('HTTP_USER_AGENT');
		$params["user_id"] = $this->diafan->_users->id;
		$params["role_id"] = $this->diafan->_users->role_id;

		$params["is_mobile_url"] = (defined('IS_MOBILE') && IS_MOBILE);
		Custom::inc('plugins/mobile_detect.php');
		$detect = new Mobile_Detect;
		$params["is_mobile"] = $detect->isMobile();

		$params["is_admin"] = (defined('IS_ADMIN') && IS_ADMIN);
		if(! $params["is_admin"])
		{
			if($row = $this->diafan->_route->search($_GET["rewrite"]))
			{
				$params["module_name"] = $row["module_name"]; // $this->diafan->_site->module;
				$params["site_id"] = $this->diafan->_site->id;
				$params["element_id"] = $row["element_id"];
				$params["element_type"] = $row["element_type"];
				//$url = BASE_PATH.$this->diafan->_route->link($params["site_id"], $params["element_id"], $params["module_name"], $params["element_type"]);
			}
			else
			{
				$params["module_name"] = $params["site_id"] = $params["element_id"] = $params["element_type"] = '';
			}
		}
		else
		{
			$params["module_name"] = $this->diafan->_admin->module;
			$params["site_id"] = $site_id = $this->diafan->_route->site;
			$params["element_id"] = $this->diafan->id ?: '';
			$params["element_type"] = $this->diafan->element_type();
		}

		// идентификатор для ведения статистических данных
		$UID = $this->diafan->configmodules('counter_uid', 'visitors');
		if(empty($UID))
		{
			$UID = $this->diafan->uid();
			$this->diafan->configmodules('counter_uid', 'visitors', 0, 0, $UID);
		}
		$params["UID"] = $UID;

		// сохранение значений счетчика статистических данных
		// $result = $this->set($params);
		$result = $this->diafan->fast_request(BASE_PATH.self::URL, $params, false, false, REQUEST_POST);

		// блокировка ботов
		if(empty($params["valid"]) && ! empty($params["valid_completed"]))
		{
			if(! empty($params["search_bot"]))
			{
				// блокировка поисковых ботов
				if($this->diafan->configmodules('counter_block_access_search_bots', 'visitors'))
				{
					Custom::inc('includes/403.php');
				}
			}
			else
			{
				// блокировка спам-ботов
				if($this->diafan->configmodules('counter_block_access_bots', 'visitors'))
				{
					Custom::inc('includes/403.php');
				}
			}
		}

		return $result;
	}

	/**
	 * Возвращает статус метрики
	 *
	 * @return boolean
	 */
	public function is_enable()
	{
		$url = strtok(getenv('REQUEST_URI'), '?');
		if($url == '/'.self::URL) return false;
		if(! $this->diafan->configmodules('counter_enable', 'visitors')) return false;
		if(defined('CACHE_EXTREME') && CACHE_EXTREME && ! $this->diafan->configmodules('counter_cache_extreme', 'visitors')) return false;
		if(empty($this->diafan->_session->id)) return false;

		return true;
	}

	/**
	 * Cохранение значений счетчика статистических данных
	 *
	 * @param array $params параметры сессии
	 * @return boolean
	 */
	public function set($params)
	{
		// чистим устаревшие данные
		$this->gc();

		if(empty($params))
		{
			return false;
		}
		// параметры сессии
		$SESS = array(
			"UID" => $this->diafan->filter($params, "string", "UID", ""),
			"SID" => $this->diafan->filter($params, "string", "SID", ""),
			"OLD_SID" => $this->diafan->filter($params, "string", "OLD_SID", ""),
			"hostname" => $this->diafan->filter($params, "string", "hostname", ""),

			"scheme" => $this->diafan->filter($params, "integer", "scheme", 0),
			"domain" => $this->diafan->filter($params, "string", "domain", ""),
			"rewrite" => $this->diafan->filter($params, "string", "rewrite", ""),
			"query" => $this->diafan->filter($params, "string", "query", ""),
			"referer_scheme" => $this->diafan->filter($params, "integer", "referer_scheme", 0),
			"referer_domain" => $this->diafan->filter($params, "string", "referer_domain", ""),
			"referer_rewrite" => $this->diafan->filter($params, "string", "referer_rewrite", ""),
			"referer_query" => $this->diafan->filter($params, "string", "referer_query", ""),

			"create" => $this->diafan->filter($params, "integer", "create", 0),
			"timestamp" => $this->diafan->filter($params, "integer", "timestamp", 0),
			"timeedit" => $this->diafan->filter($params, "integer", "timeedit", 0),
			"last_timeedit" => $this->diafan->filter($params, "integer", "last_timeedit", 0),
			"is_new" => ! empty($params["is_new"]),
			"user_agent" => $this->diafan->filter($params, "string", "user_agent", ""),
			"user_id" => $this->diafan->filter($params, "integer", "user_id", 0),
			"role_id" => $this->diafan->filter($params, "integer", "role_id", 0),
			"is_mobile_url" => ! empty($params["is_mobile_url"]),
			"is_mobile" => ! empty($params["is_mobile"]),
			"valid" => ! empty($params["valid"]),
			"search_bot" => $this->diafan->filter($params, "string", "search_bot", ""),

			"is_admin" => ! empty($params["is_admin"]),
			"module_name" => $this->diafan->filter($params, "string", "module_name", ""),
			"site_id" => $this->diafan->filter($params, "integer", "site_id", 0),
			"element_id" => $this->diafan->filter($params, "integer", "element_id", 0),
			"element_type" => $this->diafan->filter($params, "string", "element_type", "element"),

			"only_valid" => ! empty($params["only_valid"]),
			"valid_completed" => ! empty($params["valid_completed"]),
			"no_script" => ! empty($params["no_script"]),
		);
		$SESS["element_type"] = $this->diafan->_route->check_element_type($SESS["element_type"], true) ? $SESS["element_type"] : "element";
		$SESS["site"] = array("is_new" => false, "timeedit" => 0, "last_timeedit" => 0);
		if(! empty($params["site"]))
		{
			$SESS["site"]["is_new"] = ! empty($params["site"]["is_new"]);
			$SESS["site"]["timeedit"] = $this->diafan->filter($params["site"], "integer", "timeedit", 0);
			$SESS["site"]["last_timeedit"] = $this->diafan->filter($params["site"], "integer", "last_timeedit", 0);
		}
		$SESS["admin"] = array("is_new" => false, "timeedit" => 0, "last_timeedit" => 0);
		if(! empty($params["admin"]))
		{
			$SESS["admin"]["is_new"] = ! empty($params["admin"]["is_new"]);
			$SESS["admin"]["timeedit"] = $this->diafan->filter($params["admin"], "integer", "timeedit", 0);
			$SESS["admin"]["last_timeedit"] = $this->diafan->filter($params["admin"], "integer", "last_timeedit", 0);
		}

		if(empty($SESS["SID"]))
		{
			return false;
		}
		// идентификатор для ведение статистических данных
		$UID = $this->diafan->configmodules('counter_uid', 'visitors');
		if(empty($UID))
		{
			$UID = $this->diafan->uid();
			$this->diafan->configmodules('counter_uid', 'visitors', 0, 0, $UID);
		}
		if(empty($SESS["UID"]) || $SESS["UID"] != $UID)
		{
			return false;
		}

		// сведения о посетителе
		$visitors_session_id = DB::query_result(
			"SELECT id FROM {visitors_session} WHERE session_id='%s' AND user_agent='%s' LIMIT 1",
			$SESS["SID"], $SESS["user_agent"]
		);
		if(! $visitors_session_id && ! empty($SESS["OLD_SID"]))
		{
			$visitors_session_id = DB::query_result(
				"SELECT id FROM {visitors_session} WHERE session_id='%s' AND user_agent='%s' LIMIT 1",
				$SESS["OLD_SID"], $SESS["user_agent"]
			);
		}
		$need_update = false;
		if($visitors_session_id)
		{
			$row = $this->diafan->_db_ex->get('{visitors_session}', $visitors_session_id);
			$this->diafan->_db_ex->update('{visitors_session}', $visitors_session_id,
				array(
					"session_id='%h'", "user_id=%d", "role_id=%d", "hostname='%s'", "user_agent='%s'", "`create`='%d'", "timestamp='%d'", "timeedit=%d",
					"`status`='%d'", "search_bot='%h'"
				),
				array(
					$SESS["SID"], $SESS["user_id"], $SESS["role_id"], $SESS["hostname"], $SESS["user_agent"], $SESS["create"], $SESS["timestamp"], $SESS["timeedit"],
					(! empty($SESS["valid"]) ? 1 : 0), $SESS["search_bot"]
				)
			);
			if($row && (!! $row["status"] != !! $SESS["valid"]))
			{
				// маркер необходимости обновления значений valid
				$need_update = true;
			}
		}
		else
		{
			$visitors_session_id = $this->diafan->_db_ex->add_new('{visitors_session}',
				array(
					"session_id", "user_id", "role_id", "hostname", "user_agent", "`create`", "timestamp", "timeedit",
					"`status`", "search_bot"
				),
				array(
					"'%h'", "%d", "%d", "'%s'", "'%s'", "%d", "%d", "%d",
					"'%d'", "'%h'"
				),
				array(
					$SESS["SID"], $SESS["user_id"], $SESS["role_id"], $SESS["hostname"], $SESS["user_agent"], $SESS["create"], $SESS["timestamp"], $SESS["timeedit"],
					(! empty($SESS["valid"]) ? 1 : 0), $SESS["search_bot"]
				)
			);
		}

		// сведения о локации посетителя
		if(! $SESS["only_valid"] && $visitors_session_id)
		{
			$row = DB::query_fetch_array(
				"SELECT * FROM {visitors_url} WHERE visitors_session_id='%h' ORDER BY timeedit DESC, master_id DESC, slave_id DESC LIMIT 1",
				$visitors_session_id
			);
			if(! $row || ($row["status"] != ($SESS["valid"] ? 1 : 0))
			|| $row["rewrite"] != $SESS["rewrite"] || $row["user_id"] != $SESS["user_id"] || $row["role_id"] != $SESS["role_id"]
			|| $row["is_mobile"] != $SESS["is_mobile"] || $row["is_mobile_url"] != $SESS["is_mobile_url"]
			)
			{
				$rewrite = $SESS["rewrite"] ?: "/";
				$referer_domain = $SESS["referer_domain"] == $this->diafan->domain() ? '/' : $SESS["referer_domain"];

				$active_interval = $this->diafan->filter($this->diafan->configmodules('counter_active_interval', 'visitors'), "integer");
				$active_interval = ! empty($active_interval) ? ($active_interval * 60) : self::ACTIVE_INTERVAL; // по умолчанию 1 сутки
				$visits = 0;
				/*if($SESS["is_new"])
				{
					$visits = 1;
				}
				else
				{
					$SESS["last_timeedit"] += $active_interval;
					if($SESS["last_timeedit"] < $SESS["timeedit"])
					{
						$visits = 2;
					}
				}*/
				if($SESS["is_admin"]) $key = 'admin';
				else $key = 'site';
				if($SESS[$key]["is_new"])
				{
					$visits = 1;
				}
				else
				{
					$SESS[$key]["last_timeedit"] += $active_interval;
					if($SESS[$key]["last_timeedit"] < $SESS[$key]["timeedit"])
					{
						$visits = 2;
					}
				}

				// сохранем локацию посетителя
				$this->diafan->_db_ex->add_new('{visitors_url}',
					array(
						"visitors_session_id",
						"referer_scheme", "referer_domain", "referer_rewrite", "referer_query",
						"scheme", "rewrite", "query",
						"user_id", "role_id",
						"is_admin", "module_name", "site_id", "element_id", "element_type",
						"is_mobile", "is_mobile_url", "visits", "timeedit",
						"hostname",
						"`status`", "search_bot"
					),
					array(
						"'%h'",
						"'%d'", "'%h'", "'%h'", "'%h'",
						"'%d'", "'%h'", "'%h'",
						"%d", "%d",
						"'%d'", "'%h'", "%d", "%d", "'%h'",
						"'%d'", "'%d'", "'%d'", "'%d'",
						"'%s'",
						"'%d'", "'%h'"
					),
					array(
						$visitors_session_id,
						(! $SESS["referer_scheme"] ? 0 : 1), $referer_domain, $SESS["referer_rewrite"], $SESS["referer_query"],
						(! $SESS["scheme"] ? 0 : 1), $rewrite, $SESS["query"],
						$SESS["user_id"], $SESS["role_id"],
						(! $SESS["is_admin"] ? 0 : 1), $SESS["module_name"], $SESS["site_id"], $SESS["element_id"], $SESS["element_type"],
						(! $SESS["is_mobile"] ? 0 : 1), (! $SESS["is_mobile_url"] ? 0 : 1), ($visits < 1 || $visits > 2 ? 0 : $visits), time(),
						$SESS["hostname"],
						(! empty($SESS["valid"]) ? 1 : 0), $SESS["search_bot"]
					)
				);
			}
			elseif(! empty($row["id"]))
			{
				$this->diafan->_db_ex->update('{visitors_url}', $row["id"],
					array(
						"timeedit=%d",
						"`status`='%d'", "search_bot='%h'"
					),
					array(
						time(),
						(! empty($SESS["valid"]) ? 1 : 0), $SESS["search_bot"]
					)
				);
			}
		}

		// сведения о валидации пользовательского агента в локации посетителя
		if($visitors_session_id && $need_update)
		{
			// пересчет статистических данных
			$this->reset_traffic($visitors_session_id, $SESS["valid"]);
			$this->reset_traffic_source($visitors_session_id, $SESS["valid"]);
			$this->reset_traffic_pages($visitors_session_id, $SESS["valid"]);
			$this->reset_traffic_names_search_bot($visitors_session_id, $SESS["valid"]);
			// смена статуса
			DB::query("UPDATE {visitors_url} SET `status`='%d' WHERE visitors_session_id='%h' AND `status`<>'%d'", (! empty($SESS["valid"]) ? 1 : 0), $visitors_session_id, (! empty($SESS["valid"]) ? 1 : 0));
		}

		return true;
	}

	/**
	 * Чистит мусор - удаляет сессии старше $lifetime
	 *
	 * @return void
	 */
	public function gc()
	{
		$timemarker = mktime(23, 59, 0, date("m"), date("d"), date("Y")); // кешируем на сутки
		if($timemarker != $this->diafan->configmodules('counter_gc_timemarker', 'visitors'))
		{
			$this->diafan->configmodules('counter_gc_timemarker', 'visitors', 0, 0, $timemarker);

			// формируем статистику количества визитов, просмотров и уникальных посетителей
			$this->set_traffic();
			// формируем статистику количества по источникам трафика (визиты)
			$this->set_traffic_source();
			// формируем статистику по посещаемым страницам сайта (визиты)
			$this->set_traffic_pages();
			// формируем статистику по поисковым ботам (визиты)
			$this->set_traffic_names_search_bot();

			// чистим мусор
			//$lifetime = 1209600; // 2 weeks
			$lifetime = 259200; // 3 days
			DB::query("DELETE FROM {visitors_session} WHERE timeedit<%d", time() - $lifetime);
			DB::query("DELETE FROM {visitors_url} WHERE timeedit<%d", time() - $lifetime);
		}
		return true;
	}

	/**
	 * Определяет поискового робота на основе заголовка HTTP_USER_AGENT
	 *
	 * @param string $params заголовок HTTP_USER_AGENT
	 * @return mixed(boolean/string)
	 */
	private function is_Bot($user_agent)
	{
		if(empty($user_agent))
		{
			return false;
		}
		foreach($this->bots as $bot)
		{
			if(stripos($user_agent, $bot) !== false)
			{
				return $bot;
			}
		}
		return false;
	}

	/**
	 * Инициирует валидацию пользовательского агента
	 *
	 * @return void
	 */
	private function validator()
	{
		if(! isset($_SESSION["visitors"]["valid"]) && ($search_bot = $this->is_Bot(getenv('HTTP_USER_AGENT'))))
		{// определяем поискового бота
			$_SESSION["visitors"]["valid"] = false;
			$_SESSION["visitors"]["search_bot"] = $search_bot;
			// маркер завершения валидации пользовательского агента
			$_SESSION["visitors"]["valid_completed"] = true;
		}

		// принудительная валидация, если пользователь авторизован
		if($this->diafan->_users->id)
		{
			$_SESSION["visitors"]["valid"] = true;
			// маркер завершения валидации пользовательского агента
			$_SESSION["visitors"]["valid_completed"] = true;
		}

		if(! isset($_SESSION["visitors"]["valid"]))
		{// валидация пользовательского агента
			$_SESSION["visitors"]["valid"] = false;
			// маркер начала валидации пользовательского агента
			$_SESSION["visitors"]["valid_completed"] = false;

			$_SESSION["visitors"]["VALID_NAME"] = $this->diafan->uid();
			$_SESSION["visitors"]["VALID_VALUE"] = $this->diafan->uid(true);
			$expires = 60*60*24*1; // 1 сутки
			$path = '/';
			$domain = $this->diafan->_session->HTTP_HOST();
			if(MOBILE_VERSION && defined('MOBILE_SUBDOMAIN') && MOBILE_SUBDOMAIN)
			{
				$domain = '.' . $domain;
			}

			$no_script = '<noscript><div><img src="'.BASE_PATH.self::URL.'?watch=no_script&'.$_SESSION["visitors"]["VALID_NAME"].'='.$_SESSION["visitors"]["VALID_VALUE"].'" style="position:absolute; left:-9999px;" alt="" /></div></noscript>';

			if($this->diafan->configmodules('counter_defer', 'visitors'))
			{
				if(defined('IS_ADMIN') && IS_ADMIN)
				{
					$this->diafan->_admin->js_code[__CLASS__] = '
<script language="javascript" type="text/javascript" '.$_SESSION["visitors"]["VALID_NAME"].'="'.$_SESSION["visitors"]["VALID_VALUE"].'">
	$(function() {
		function run_after_ready() {
			if( window.$.cookie && window.$.ajax) {
				$.cookie("'.$_SESSION["visitors"]["VALID_NAME"].'", "'.$_SESSION["visitors"]["VALID_VALUE"].'", {expires:'.$expires.', path:"'.$path.'", domain:"'.$domain.'"});

				$.ajax({
					url : window.location.href,
					type : "POST",
					data : {
						module: "visitors",
						action: "valid",
						ajax: true,
						check_hash_user: "'.$this->diafan->_users->get_hash().'"
					},
					success:(function (result) {
						try {
							var response = $.parseJSON(result);
						} catch(err){
							return false;
						}
						if(response.redirect) {
							window.location.href = prepare(response.redirect);
						}
					})
				});

				var elem = $("script['.$_SESSION["visitors"]["VALID_NAME"].'=\"'.$_SESSION["visitors"]["VALID_VALUE"].'\"]");
				if(elem.length)
				{
					elem.remove();
				}
			}
			else
			{
				window.setTimeout( run_after_ready, 50 );
			}
		}
		run_after_ready();
	});
</script>'.$no_script;
				}
				else
				{
					$this->diafan->_site->js_code[__CLASS__] = '
<script language="javascript" type="text/javascript" '.$_SESSION["visitors"]["VALID_NAME"].'="'.$_SESSION["visitors"]["VALID_VALUE"].'">
	function visitors_inc_counter_validator()
	{
		function getCookie(name)
		{
			var matches = document.cookie.match(new RegExp(
				"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, "\\$1") + "=([^;]*)"
			));
			return matches ? decodeURIComponent(matches[1]) : undefined;
		}
		function setCookie(name, value, options)
		{
			options = options || {};
			var expires = options.expires;

			if(typeof expires == "number" && expires)
			{
				var d = new Date();
				d.setTime(d.getTime() + expires * 1000);
				expires = options.expires = d;
			}
			if(expires && expires.toUTCString)
			{
				options.expires = expires.toUTCString();
			}

			value = encodeURIComponent(value);
			var updatedCookie = name + "=" + value;

			for(var propName in options)
			{
				updatedCookie += "; " + propName;
				var propValue = options[propName];
				if (propValue !== true)
				{
					updatedCookie += "=" + propValue;
				}
			}

			document.cookie = updatedCookie;
		}
		function deleteCookie(name)
		{
			setCookie(name, "", {
				expires: -1
			});
		}
		if(navigator.cookieEnabled)
		{
			setCookie("'.$_SESSION["visitors"]["VALID_NAME"].'", "'.$_SESSION["visitors"]["VALID_VALUE"].'", {expires:'.$expires.', path:"'.$path.'", domain:"'.$domain.'"});
		}


		var data = {
			module: "visitors",
			action: "valid",
			ajax: true,
			check_hash_user: "'.$this->diafan->_users->get_hash().'"
		};

		var boundary = String(Math.random()).slice(2);
		var boundaryMiddle = "--" + boundary + "\r\n";
		var boundaryLast = "--" + boundary + "--\r\n"
		var body = ["\r\n"];
		for (var key in data) {
			body.push("Content-Disposition: form-data; name=\"" + key + "\"\r\n\r\n" + data[key] + "\r\n");
		}
		body = body.join(boundaryMiddle) + boundaryLast;

		var xhr = new XMLHttpRequest();
		xhr.open("POST", window.location.href, true);
		xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary=" + boundary);
		xhr.onreadystatechange = function() {
			if (this.readyState != 4) return;
			// по окончании запроса доступны: status, statusText, responseText, responseXML (при content-type: text/xml)
			//console.log( this.responseText );

			if (this.status != 200)
			{
				// обработать ошибку
				//console.log("ошибка: " + (this.status ? this.statusText : "запрос не удался") );
				return;
			  }
		}
		xhr.send(body);


		var elems = document.getElementsByTagName("script");
		for( var i = 0, elem; elem = elems[ i++ ]; ) {
			if ( elem.getAttribute("'.$_SESSION["visitors"]["VALID_NAME"].'") == "'.$_SESSION["visitors"]["VALID_VALUE"].'" )
			{
				elem.remove();
			}
		}
	}
	visitors_inc_counter_validator();
</script>'.$no_script;
				}
			}
			else
			{
				$_SESSION["visitors"]["GET"] = $_GET;
				$_SESSION["visitors"]["POST"] = $_POST;
				$_SESSION["visitors"]["HTTP_REFERER"] = getenv("HTTP_REFERER");

				header('HTTP/1.0 200 OK');
				header('Content-Type: text/html; charset=utf-8');
				header("Cache-Control: no-cache");

				echo '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>'.$this->diafan->_("Валидация пользовательского агента", false).'</title>
	<noscript><meta http-equiv="Refresh" content="0"></noscript>
</head>
<body>
	<script language="javascript" type="text/javascript">
		function getCookie(name)
		{
			var matches = document.cookie.match(new RegExp(
				"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, "\\$1") + "=([^;]*)"
			));
			return matches ? decodeURIComponent(matches[1]) : undefined;
		}
		function setCookie(name, value, options)
		{
			options = options || {};
			var expires = options.expires;

			if(typeof expires == "number" && expires)
			{
				var d = new Date();
				d.setTime(d.getTime() + expires * 1000);
				expires = options.expires = d;
			}
			if(expires && expires.toUTCString)
			{
				options.expires = expires.toUTCString();
			}

			value = encodeURIComponent(value);
			var updatedCookie = name + "=" + value;

			for(var propName in options)
			{
				updatedCookie += "; " + propName;
				var propValue = options[propName];
				if (propValue !== true)
				{
					updatedCookie += "=" + propValue;
				}
			}

			document.cookie = updatedCookie;
		}
		function deleteCookie(name)
		{
			setCookie(name, "", {
				expires: -1
			});
		}
		if(navigator.cookieEnabled)
		{
			setCookie("'.$_SESSION["visitors"]["VALID_NAME"].'", "'.$_SESSION["visitors"]["VALID_VALUE"].'", {expires:'.$expires.', path:"'.$path.'", domain:"'.$domain.'"});
		}
		location.reload(true);
		var elem = document.getElementsByTagName("script")[0];
		elem.remove();
	</script>'.$no_script.'
</body>
</html>';
				exit;
			}
		}
		else
		{
			// восстановление после валидации пользовательского агента
			if(isset($_SESSION["visitors"]["GET"])) { $_GET = $_SESSION["visitors"]["GET"]; unset($_SESSION["visitors"]["GET"]); }
			if(isset($_SESSION["visitors"]["POST"])) { $_POST = $_SESSION["visitors"]["POST"]; unset($_SESSION["visitors"]["POST"]); }
			if(isset($_SESSION["visitors"]["HTTP_REFERER"])) { $_SERVER["HTTP_REFERER"] = $_SESSION["visitors"]["HTTP_REFERER"]; unset($_SESSION["visitors"]["HTTP_REFERER"]); }

			// проверка пользовательского агента
			if(isset($_SESSION["visitors"]["valid_completed"]) && ! $_SESSION["visitors"]["valid_completed"])
			{
				if(isset($_GET["watch"]) && $_GET["watch"] == "no_script")
				{ // JavaScript is disable
					if(! isset($_SESSION["visitors"]["no_script"]))
					{
						if(isset($_GET[$_SESSION["visitors"]["VALID_NAME"]])
						&& $_GET[$_SESSION["visitors"]["VALID_NAME"]] == $_SESSION["visitors"]["VALID_VALUE"])
						{
							// маркер блокировки исполнения JavaScript на стороне пользовательского агента
							$_SESSION["visitors"]["no_script"] = true;
							$_SESSION["visitors"]["valid"] = false;

							// маркер завершения валидации пользовательского агента
							$_SESSION["visitors"]["valid_completed"] = true;
						}
					}
				}
				else
				{ // JavaScript is enable
					if(isset($_COOKIE[$_SESSION["visitors"]["VALID_NAME"]]))
					{ // JavaScript is not blocked
						if(! $_SESSION["visitors"]["valid"] && $_COOKIE[$_SESSION["visitors"]["VALID_NAME"]] == $_SESSION["visitors"]["VALID_VALUE"])
						{
							$_SESSION["visitors"]["valid"] = true;
						}
						else
						{
							$_SESSION["visitors"]["valid"] = false;
						}
						$expires = 60*60*24*1; // 1 сутки
						$path = '/'; $domain = $this->diafan->_session->HTTP_HOST(); if(MOBILE_VERSION && defined('MOBILE_SUBDOMAIN') && MOBILE_SUBDOMAIN) { $domain = '.' . $domain; }
						setcookie($_SESSION["visitors"]["VALID_NAME"], $_SESSION["visitors"]["VALID_VALUE"], (time() - $expires), $path, $domain);

						// маркер завершения валидации пользовательского агента
						$_SESSION["visitors"]["valid_completed"] = true;
					}
					elseif($_SESSION["visitors"]["only_valid"])
					{// JavaScript is not blocked (т.е. ответ пришел через AJAX, но валидация не пройдена на стороне пользовательского агента)
						$_SESSION["visitors"]["valid"] = false;
						// маркер завершения валидации пользовательского агента
						$_SESSION["visitors"]["valid_completed"] = true;
					}
					else
					{ // JavaScript is blocked (т.е. ответ пришел не через AJAX, а после перехода на новую страницу и валидация не пройдена на стороне пользовательского агента)
						$_SESSION["visitors"]["valid"] = false;
						if(! isset($_SESSION["visitors"]["valid_count"])) $_SESSION["visitors"]["valid_count"] = 1;
						else $_SESSION["visitors"]["valid_count"] = $_SESSION["visitors"]["valid_count"] + 1;
						if(! $this->diafan->configmodules('counter_defer', 'visitors') || $_SESSION["visitors"]["valid_count"] >= self::MAX_VALID_COUNT)
						{
							// маркер завершения валидации пользовательского агента
							$_SESSION["visitors"]["valid_completed"] = true;
						}
					}
				}
			}
			if(isset($_SESSION["visitors"]["valid_completed"]) && $_SESSION["visitors"]["valid_completed"])
			{
				if(isset($_SESSION["visitors"]["VALID_NAME"])) unset($_SESSION["visitors"]["VALID_NAME"]);
				if(isset($_SESSION["visitors"]["VALID_VALUE"])) unset($_SESSION["visitors"]["VALID_VALUE"]);
			}
		}
	}

	/**
	 * Сохраняет статистику количества визитов, просмотров и уникальных посетителей
	 *
	 * @return void
	 */
	public function set_traffic()
	{
		if(isset($this->cache["prepare"][__METHOD__]))
		{
			return $this->cache["prepare"][__METHOD__] = true;
		}

		$lifetime = self::TRAFFIC_LIFETIME; // 1 day
		$start_time = DB::query_result("SELECT MAX(`date`) AS `date` FROM {visitors_stat_traffic} WHERE `date`<%d LIMIT 1", time() - $lifetime);
		$start_time = $start_time ?: 0;
		$rows = array();

		//визиты
		$rows["visits"] = DB::query_fetch_key(
			"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE status = '%d' AND visits = '%d' AND `master_id`>=%d AND is_admin='0'"
			." GROUP BY `date` ORDER BY `date` ASC",
			1, 2, $start_time, "date"
		);
		$rows["visits_search_bot"] = DB::query_fetch_key(
			"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE status = '%d' AND visits = '%d' AND `master_id`>=%d AND search_bot <> '' AND is_admin='0'"
			." GROUP BY `date` ORDER BY `date` ASC",
			0, 2, $start_time, "date"
		);
		$rows["visits_bot"] = DB::query_fetch_key(
			"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE status = '%d' AND visits = '%d' AND `master_id`>=%d AND search_bot = '' AND is_admin='0'"
			." GROUP BY `date` ORDER BY `date` ASC",
			0, 2, $start_time, "date"
		);
		//просмотры
		$rows["pageviews"] = DB::query_fetch_key(
			"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE status = '%d' AND `master_id`>=%d AND is_admin='0'"
			." GROUP BY `date` ORDER BY `date` ASC",
			1, $start_time, "date"
		);
		$rows["pageviews_search_bot"] = DB::query_fetch_key(
			"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE status = '%d' AND `master_id`>=%d AND search_bot <> '' AND is_admin='0'"
			." GROUP BY `date` ORDER BY `date` ASC",
			0, $start_time, "date"
		);
		$rows["pageviews_bot"] = DB::query_fetch_key(
			"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE status = '%d' AND `master_id`>=%d  AND search_bot = '' AND is_admin='0'"
			." GROUP BY `date` ORDER BY `date` ASC",
			0, $start_time, "date"
		);
		//уникальные посетители
		$rows["users"] = DB::query_fetch_key(
			"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE status = '%d' AND visits = '%d' AND `master_id`>=%d AND is_admin='0'"
			." GROUP BY `date` ORDER BY `date` ASC",
			1, 1, $start_time, "date"
		);
		$rows["users_search_bot"] = DB::query_fetch_key(
			"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE status = '%d' AND visits = '%d' AND `master_id`>=%d AND search_bot <> '' AND is_admin='0'"
			." GROUP BY `date` ORDER BY `date` ASC",
			0, 1, $start_time, "date"
		);
		$rows["users_bot"] = DB::query_fetch_key(
			"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE status = '%d' AND visits = '%d' AND `master_id`>=%d AND search_bot = '' AND is_admin='0'"
			." GROUP BY `date` ORDER BY `date` ASC",
			0, 1, $start_time, "date"
		);

		foreach($rows as $key => $values)
		{
			$field = $key.'_count';
			foreach($values as $val)
			{
				if($val["count"] <= 0) continue;
				$val["date"] = strtotime($val["date"]);
				if($id = DB::query_result("SELECT id FROM {visitors_stat_traffic} WHERE `date`=%d LIMIT 1", $val["date"]))
				{
					DB::query("UPDATE {visitors_stat_traffic} SET `%h`=%d WHERE id=%d LIMIT 1", $field, $val["count"], $id);
				}
				else
				{
					DB::query("INSERT INTO {visitors_stat_traffic} (`date`, `%h`) VALUES('%d', '%d')", $field, $val["date"], $val["count"]);
				}
			}
		}

		return $this->cache["prepare"][__METHOD__] = true;
	}

	/**
	 * Пересчет статистики количества визитов, просмотров и уникальных посетителей
	 *
	 * @param string $visitors_session_id идентификатор сессии
	 * @param boolean $status статус валидации пользовательского агента
	 * @return void
	 */
	private function reset_traffic($visitors_session_id, $status)
	{
		$lifetime = self::TRAFFIC_LIFETIME; // 1 day
		$start_time = DB::query_result("SELECT MAX(`date`) AS `date` FROM {visitors_stat_traffic} WHERE `date`<%d LIMIT 1", time() - $lifetime);
		$start_time = $start_time ?: 0;
		if($start_time == 0)
		{
			return;
		}
		$rows = $fields = array();

		//визиты
		if($status != 1)
		{
			$rows["visits"] = DB::query_fetch_key(
				"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
				." WHERE visitors_session_id='%h' AND status = '%d' AND visits = '%d' AND `master_id`<%d AND is_admin='0'"
				." GROUP BY `date` ORDER BY `date` ASC",
				$visitors_session_id, 1, 2, $start_time, "date"
			);
			$fields["visits"] = "visits_bot";
		}
		if($status != 0)
		{
			$rows["visits_bot"] = DB::query_fetch_key(
				"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
				." WHERE visitors_session_id='%h' AND status = '%d' AND visits = '%d' AND `master_id`<%d AND search_bot = '' AND is_admin='0'"
				." GROUP BY `date` ORDER BY `date` ASC",
				$visitors_session_id, 0, 2, $start_time, "date"
			);
			$fields["visits_bot"] = "visits";
		}
		//просмотры
		if($status != 1)
		{
			$rows["pageviews"] = DB::query_fetch_key(
				"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
				." WHERE visitors_session_id='%h' AND status = '%d' AND `master_id`<%d AND is_admin='0'"
				." GROUP BY `date` ORDER BY `date` ASC",
				$visitors_session_id, 1, $start_time, "date"
			);
			$fields["pageviews"] = "pageviews_bot";
		}
		if($status != 0)
		{
			$rows["pageviews_bot"] = DB::query_fetch_key(
				"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
				." WHERE visitors_session_id='%h' AND status = '%d' AND `master_id`<%d  AND search_bot = '' AND is_admin='0'"
				." GROUP BY `date` ORDER BY `date` ASC",
				$visitors_session_id, 0, $start_time, "date"
			);
			$fields["pageviews_bot"] = "pageviews";
		}
		//уникальные посетители
		if($status != 1)
		{
			$rows["users"] = DB::query_fetch_key(
				"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
				." WHERE visitors_session_id='%h' AND status = '%d' AND visits = '%d' AND `master_id`<%d AND is_admin='0'"
				." GROUP BY `date` ORDER BY `date` ASC",
				$visitors_session_id, 1, 1, $start_time, "date"
			);
			$fields["users"] = "users_bot";
		}
		if($status != 0)
		{
			$rows["users_bot"] = DB::query_fetch_key(
				"SELECT DATE_FORMAT(FROM_UNIXTIME(`master_id`), '%Y-%m-%%d') AS `date`, COUNT(*) AS `count` FROM {visitors_url}"
				." WHERE visitors_session_id='%h' AND status = '%d' AND visits = '%d' AND `master_id`<%d AND search_bot = '' AND is_admin='0'"
				." GROUP BY `date` ORDER BY `date` ASC",
				$visitors_session_id, 0, 1, $start_time, "date"
			);
			$fields["users_bot"] = "users";
		}

		foreach($rows as $key => $values)
		{
			$field = $key.'_count';
			foreach($values as $val)
			{
				if($val["count"] <= 0) continue;
				if(empty($fields[$key])) continue;
				$val["date"] = strtotime($val["date"]);
				if($id = DB::query_result("SELECT id FROM {visitors_stat_traffic} WHERE `date`=%d LIMIT 1", $val["date"]))
				{
					DB::query("UPDATE {visitors_stat_traffic} SET"
						." `%h`=`%h`-%d,"
						." `%h`=`%h`+%d"
						." WHERE id=%d LIMIT 1",
						$field, $field, $val["count"],
						$fields[$key].'_count', $fields[$key].'_count', $val["count"],
						$id
					);
				}
			}
		}

		return true;
	}

	/**
	 * Получает статистику количества визитов, просмотров и уникальных посетителей
	 *
	 * @return array
	 */
	public function traffic()
	{
		if(isset($this->cache["prepare"][__METHOD__]))
		{
			return $this->cache["prepare"][__METHOD__];
		}

		$result = array(
			'data' => array(),
			'min'  => array(
				'visits'    => 0,
				'pageviews' => 0,
				'users'     => 0
			),
			'max'  => array(
				'visits'    => 0,
				'pageviews' => 0,
				'users'     => 0
			)
		);

		$lifetime = 1209600; // 2 weeks
		$day = 86400; // 1 day
		$end_time = time();
		$start_time = $end_time - $lifetime;

		//визиты
		$rows = DB::query_fetch_key(
			"SELECT *, DATE_FORMAT(FROM_UNIXTIME(`date`), '%Y-%m-%%d') AS `day` FROM {visitors_stat_traffic}"
			." WHERE `date`>=%d"
			." GROUP BY `date` ORDER BY `date` ASC",
			1, 2, $start_time, "day"
		);

		$values = array('visits' => array(), 'pageviews' => array(), 'users' => array());
		for ($d = $start_time; $d <= $end_time; $d = $d + $day)
		{
		    $key = date("Y-m-d", $d);
			if(empty($rows[$key]))
			{
				$result["data"][$key] = array(
					'categories'           => $key,
					'visits'               => 0,
					'visits_search_bot'    => 0,
					'visits_bot'           => 0,
					'pageviews'            => 0,
					'pageviews_search_bot' => 0,
					'pageviews_bot'        => 0,
					'users'                => 0,
					'users_search_bot'     => 0,
					'users_bot'            => 0
				);
			}
			else
			{
				$result["data"][$key] = array(
					'categories'           => $key,
					'visits'               => (isset($rows[$key]["visits_count"]) ? $rows[$key]["visits_count"] : 0),
					'visits_search_bot'    => (isset($rows[$key]["visits_search_bot_count"]) ? $rows[$key]["visits_search_bot_count"] : 0),
					'visits_bot'           => (isset($rows[$key]["visits_bot_count"]) ? $rows[$key]["visits_bot_count"] : 0),
					'pageviews'            => (isset($rows[$key]["pageviews_count"]) ? $rows[$key]["pageviews_count"] : 0),
					'pageviews_search_bot' => (isset($rows[$key]["pageviews_search_bot_count"]) ? $rows[$key]["pageviews_search_bot_count"] : 0),
					'pageviews_bot'        => (isset($rows[$key]["pageviews_bot_count"]) ? $rows[$key]["pageviews_bot_count"] : 0),
					'users'                => (isset($rows[$key]["users_count"]) ? $rows[$key]["users_count"] : 0),
					'users_search_bot'     => (isset($rows[$key]["users_search_bot_count"]) ? $rows[$key]["users_search_bot_count"] : 0),
					'users_bot'            => (isset($rows[$key]["users_bot_count"]) ? $rows[$key]["users_bot_count"] : 0)
				);
				if($result["data"][$key]['visits'] != 0) $values['visits'][] = $result["data"][$key]['visits'];
				if($result["data"][$key]['visits_search_bot'] != 0) $values['visits_search_bot'][] = $result["data"][$key]['visits_search_bot'];
				if($result["data"][$key]['visits_bot'] != 0) $values['visits_bot'][] = $result["data"][$key]['visits_bot'];
				if($result["data"][$key]['pageviews'] != 0) $values['pageviews'][] = $result["data"][$key]['pageviews'];
				if($result["data"][$key]['pageviews_search_bot'] != 0) $values['pageviews_search_bot'][] = $result["data"][$key]['pageviews_search_bot'];
				if($result["data"][$key]['pageviews_bot'] != 0) $values['pageviews_bot'][] = $result["data"][$key]['pageviews_bot'];
				if($result["data"][$key]['users'] != 0) $values['users'][] = $result["data"][$key]['users'];
				if($result["data"][$key]['users_search_bot'] != 0) $values['users_search_bot'][] = $result["data"][$key]['users_search_bot'];
				if($result["data"][$key]['users_bot'] != 0) $values['users_bot'][] = $result["data"][$key]['users_bot'];
			}
		}
		$result['min'] = array(
			'visits'               => (! empty($values['visits']) ? min($values['visits']) : 0),
			'visits_search_bot'    => (! empty($values['visits_search_bot']) ? min($values['visits_search_bot']) : 0),
			'visits_bot'           => (! empty($values['visits_bot']) ? min($values['visits_bot']) : 0),
			'pageviews'            => (! empty($values['pageviews']) ? min($values['pageviews']) : 0),
			'pageviews_search_bot' => (! empty($values['pageviews_search_bot']) ? min($values['pageviews_search_bot']) : 0),
			'pageviews_bot'        => (! empty($values['pageviews_bot']) ? min($values['pageviews_bot']) : 0),
			'users'                => (! empty($values['users']) ? min($values['users']) : 0),
			'users_search_bot'     => (! empty($values['users_search_bot']) ? min($values['users_search_bot']) : 0),
			'users_bot'            => (! empty($values['users_bot']) ? min($values['users_bot']) : 0)
		);
		$result['max'] = array(
			'visits'               => (! empty($values['visits']) ? max($values['visits']) : 0),
			'visits_search_bot'    => (! empty($values['visits_search_bot']) ? max($values['visits_search_bot']) : 0),
			'visits_bot'           => (! empty($values['visits_bot']) ? max($values['visits_bot']) : 0),
			'pageviews'            => (! empty($values['pageviews']) ? max($values['pageviews']) : 0),
			'pageviews_search_bot' => (! empty($values['pageviews_search_bot']) ? max($values['pageviews_search_bot']) : 0),
			'pageviews_bot'        => (! empty($values['pageviews_bot']) ? max($values['pageviews_bot']) : 0),
			'users'                => (! empty($values['users']) ? max($values['users']) : 0),
			'users_search_bot'     => (! empty($values['users_search_bot']) ? max($values['users_search_bot']) : 0),
			'users_bot'            => (! empty($values['users_bot']) ? max($values['users_bot']) : 0)
		);

		return $this->cache["prepare"][__METHOD__] = $result;
	}

	/**
	 * Сохраняет статистику количества по источникам трафика (визиты)
	 *
	 * @return void
	 */
	public function set_traffic_source()
	{
		if(isset($this->cache["prepare"][__METHOD__]))
		{
			return $this->cache["prepare"][__METHOD__] = true;
		}

		if(! $start_time = DB::query_result("SELECT MAX(`timeedit`) AS `timeedit` FROM {visitors_stat_traffic_source} WHERE 1=1 LIMIT 1"))
		{
			$start_time = 0;
		}
		if(! $end_time = DB::query_result("SELECT MAX(`master_id`) AS `timeedit` FROM {visitors_url} WHERE 1=1 LIMIT 1"))
		{
			$end_time = 0;
		}
		if($start_time >= $end_time)
		{
			return;
		}
		$rows_visitors = DB::query_fetch_key(
			"SELECT referer_domain, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE `status`='1' AND is_admin='0' AND timeedit>%d AND timeedit<=%d"
			." GROUP BY referer_domain ORDER BY `count` DESC",
			$start_time, $end_time, "referer_domain"
		);
		$rows_search_bot = DB::query_fetch_key(
			"SELECT referer_domain, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE `status`='0' AND search_bot<>'' AND is_admin='0' AND timeedit>%d AND timeedit<=%d"
			." GROUP BY referer_domain ORDER BY `count` DESC",
			$start_time, $end_time, "referer_domain"
		);
		$rows_bot = DB::query_fetch_key(
			"SELECT referer_domain, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE `status`='0' AND search_bot='' AND is_admin='0' AND timeedit>%d AND timeedit<=%d"
			." GROUP BY referer_domain ORDER BY `count` DESC",
			$start_time, $end_time, "referer_domain"
		);
		$rows = array();
		foreach ($rows_visitors as $key => $value) $rows[$key]["visitors"] = $value["count"];
		foreach ($rows_search_bot as $key => $value) $rows[$key]["search_bot"] = $value["count"];
		foreach ($rows_bot as $key => $value) $rows[$key]["bot"] = $value["count"];
		foreach ($rows as $key => $value)
		{
			if(! isset($rows[$key]["visitors"])) $rows[$key]["visitors"] = 0;
			if(! isset($rows[$key]["search_bot"])) $rows[$key]["search_bot"] = 0;
			if(! isset($rows[$key]["bot"])) $rows[$key]["bot"] = 0;
		}
		foreach ($rows as $key => $value)
		{
			if($id = DB::query_result("SELECT id FROM {visitors_stat_traffic_source} WHERE referer_domain='%h' LIMIT 1", $key))
			{
				$this->diafan->_db_ex->update('{visitors_stat_traffic_source}', $id,
					array(
						"visits_count=visits_count+%d", "visits_search_bot_count=visits_search_bot_count+%d", "visits_bot_count=visits_bot_count+%d", "timeedit=%d"
					),
					array(
						$value["visitors"], $value["search_bot"], $value["bot"], $end_time
					)
				);
			}
			else
			{
				$visitors_session_id = $this->diafan->_db_ex->add_new('{visitors_stat_traffic_source}',
					array(
						"referer_domain", "visits_count", "visits_search_bot_count", "visits_bot_count", "timeedit"
					),
					array(
						"'%h'", "%d", "%d", "%d", "%d"
					),
					array(
						$key, $value["visitors"], $value["search_bot"], $value["bot"], $end_time
					)
				);
			}
		}

		return $this->cache["prepare"][__METHOD__] = true;
	}

	/**
	 * Пересчет статистики количества по источникам трафика (визиты)
	 *
	 * @param string $visitors_session_id идентификатор сессии
	 * @param boolean $status статус валидации пользовательского агента
	 * @return void
	 */
	private function reset_traffic_source($visitors_session_id, $status)
	{
		if(! $end_time = DB::query_result("SELECT MAX(`timeedit`) AS `timeedit` FROM {visitors_stat_traffic_pages} WHERE 1=1 LIMIT 1"))
		{
			return;
		}
		$rows = DB::query_fetch_key(
			"SELECT referer_domain, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE visitors_session_id='%h', `status`<>'%d' AND is_admin='0' AND timeedit<=%d"
			." GROUP BY referer_domain ORDER BY `count` DESC",
			$visitors_session_id, (! empty($status) ? 1 : 0), $end_time, "referer_domain"
		);
		foreach ($rows as $key => $value)
		{
			if($id = DB::query_result("SELECT id FROM {visitors_stat_traffic_pages} WHERE referer_domain='%h' LIMIT 1", $key))
			{
				$this->diafan->_db_ex->update('{visitors_stat_traffic_pages}', $id,
					array(
						($status ? "visits_count=visits_count+%d" : "visits_count=visits_count-%d"),
						($status ? "visits_bot_count=visits_bot_count-%d" : "visits_bot_count=visits_bot_count+%d")
					),
					array(
						$value["count"], $value["count"]
					)
				);
			}
		}

		return true;
	}

	/**
	 * Получает статистику по источникам трафика (визиты)
	 *
	 * @return array
	 */
	public function traffic_source()
	{
		if(isset($this->cache["prepare"][__METHOD__]))
		{
			return $this->cache["prepare"][__METHOD__];
		}

		$row = DB::query_fetch_key(
			"SELECT referer_domain, visits_count AS `count` FROM {visitors_stat_traffic_source}"
			." WHERE visits_count>0"
			." GROUP BY referer_domain ORDER BY `count` DESC LIMIT 10",
			"referer_domain"
		);
		$data = array();
		foreach($row as $key => $val) $data[$key] = $val["count"];
		/*if(! empty($data))
		{
			array_multisort($data, SORT_DESC, SORT_NUMERIC);
			$data = array_slice($data, 0, 9, true);
		}*/

		// приобразовываем данные для визуализации
		$result = array(
			'data' => array(),
			'min'  => (! empty($data) ? min($data) : 0),
			'max'  => (! empty($data) ? max($data) : 0)
		);
		foreach($data as $key => $val)
		{
			$result["data"][$key]["categories"] = $key;
			$result["data"][$key]["value"] = $val;
		}

		return $this->cache["prepare"][__METHOD__] = $result;
	}

	/**
	 * Сохраняет статистику по посещаемым страницам сайта (визиты)
	 *
	 * @return void
	 */
	public function set_traffic_pages()
	{
		if(isset($this->cache["prepare"][__METHOD__]))
		{
			return $this->cache["prepare"][__METHOD__] = true;
		}

		if(! $start_time = DB::query_result("SELECT MAX(`timeedit`) AS `timeedit` FROM {visitors_stat_traffic_pages} WHERE 1=1 LIMIT 1"))
		{
			$start_time = 0;
		}
		if(! $end_time = DB::query_result("SELECT MAX(`master_id`) AS `timeedit` FROM {visitors_url} WHERE 1=1 LIMIT 1"))
		{
			$end_time = 0;
		}
		if($start_time >= $end_time)
		{
			return;
		}
		$rows_visitors = DB::query_fetch_key(
			"SELECT site_id, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE `status`='1' AND is_admin='0' AND timeedit>%d AND timeedit<=%d"
			." GROUP BY site_id ORDER BY `count` DESC",
			$start_time, $end_time, "site_id"
		);
		$rows_search_bot = DB::query_fetch_key(
			"SELECT site_id, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE `status`='0' AND search_bot<>'' AND is_admin='0' AND timeedit>%d AND timeedit<=%d"
			." GROUP BY site_id ORDER BY `count` DESC",
			$start_time, $end_time, "site_id"
		);
		$rows_bot = DB::query_fetch_key(
			"SELECT site_id, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE `status`='0' AND search_bot='' AND is_admin='0' AND timeedit>%d AND timeedit<=%d"
			." GROUP BY site_id ORDER BY `count` DESC",
			$start_time, $end_time, "site_id"
		);
		$rows = array();
		foreach ($rows_visitors as $key => $value) $rows[$key]["visitors"] = $value["count"];
		foreach ($rows_search_bot as $key => $value) $rows[$key]["search_bot"] = $value["count"];
		foreach ($rows_bot as $key => $value) $rows[$key]["bot"] = $value["count"];
		foreach ($rows as $key => $value)
		{
			if(! isset($rows[$key]["visitors"])) $rows[$key]["visitors"] = 0;
			if(! isset($rows[$key]["search_bot"])) $rows[$key]["search_bot"] = 0;
			if(! isset($rows[$key]["bot"])) $rows[$key]["bot"] = 0;
		}
		foreach ($rows as $key => $value)
		{
			if($id = DB::query_result("SELECT id FROM {visitors_stat_traffic_pages} WHERE site_id=%d LIMIT 1", $key))
			{
				$this->diafan->_db_ex->update('{visitors_stat_traffic_pages}', $id,
					array(
						"visits_count=visits_count+%d", "visits_search_bot_count=visits_search_bot_count+%d", "visits_bot_count=visits_bot_count+%d", "timeedit=%d"
					),
					array(
						$value["visitors"], $value["search_bot"], $value["bot"], $end_time
					)
				);
			}
			else
			{
				$visitors_session_id = $this->diafan->_db_ex->add_new('{visitors_stat_traffic_pages}',
					array(
						"site_id", "visits_count", "visits_search_bot_count", "visits_bot_count", "timeedit"
					),
					array(
						"%d", "%d", "%d", "%d", "%d"
					),
					array(
						$key, $value["visitors"], $value["search_bot"], $value["bot"], $end_time
					)
				);
			}
		}

		return $this->cache["prepare"][__METHOD__] = true;
	}

	/**
	 * Пересчет статистики по посещаемым страницам сайта (визиты)
	 *
	 * @param string $visitors_session_id идентификатор сессии
	 * @param boolean $status статус валидации пользовательского агента
	 * @return void
	 */
	private function reset_traffic_pages($visitors_session_id, $status)
	{
		if(! $end_time = DB::query_result("SELECT MAX(`timeedit`) AS `timeedit` FROM {visitors_stat_traffic_pages} WHERE 1=1 LIMIT 1"))
		{
			return;
		}
		$rows = DB::query_fetch_key(
			"SELECT site_id, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE visitors_session_id='%h', `status`<>'%d' AND is_admin='0' AND timeedit<=%d"
			." GROUP BY site_id ORDER BY `count` DESC",
			$visitors_session_id, (! empty($status) ? 1 : 0), $end_time, "site_id"
		);
		foreach ($rows as $key => $value)
		{
			if($id = DB::query_result("SELECT id FROM {visitors_stat_traffic_pages} WHERE site_id=%d LIMIT 1", $key))
			{
				$this->diafan->_db_ex->update('{visitors_stat_traffic_pages}', $id,
					array(
						($status ? "visits_count=visits_count+%d" : "visits_count=visits_count-%d"),
						($status ? "visits_bot_count=visits_bot_count-%d" : "visits_bot_count=visits_bot_count+%d")
					),
					array(
						$value["count"], $value["count"]
					)
				);
			}
		}

		return true;
	}

	/**
	 * Получает статистику по посещаемым страницам сайта (визиты)
	 *
	 * @return array
	 */
	public function traffic_pages()
	{
		if(isset($this->cache["prepare"][__METHOD__]))
		{
			return $this->cache["prepare"][__METHOD__];
		}

		$total = DB::query_result(
			"SELECT SUM(visits_count) AS `count` FROM {visitors_stat_traffic_pages}"
			." WHERE visits_count>0"
			." ORDER BY `count` DESC LIMIT 1"
		);
		$row = DB::query_fetch_key(
			"SELECT site_id, visits_count AS `count` FROM {visitors_stat_traffic_pages}"
			." WHERE visits_count>0"
			." GROUP BY site_id ORDER BY `count` DESC LIMIT 10",
			"site_id"
		);
		$data = array();
		foreach($row as $key => $val) $data[$key] = $val["count"];
		/*if(! empty($data))
		{
			//array_multisort($data, SORT_DESC, SORT_NUMERIC);
			if(count($data) >= 10)
			{
				$data = array_slice($data, 0, 9, true);
			}
		}*/

		// приобразовываем данные для визуализации
		$result = array(
			'data' => array(),
			'min'  => (! empty($data) ? min($data) : 0),
			'max'  => (! empty($data) ? max($data) : 0),
			'all'  => $total,
			'other'=> (! empty($data) ? $total - array_sum($data) : 0),
		);
		foreach($data as $key => $val)
		{
			if(! $name = DB::query_result("SELECT [name] AS `count` FROM {site} WHERE id=%d LIMIT 1", $key))
			{
				$name = 'unknown';
			}
			$result["data"][$key]["categories"] = $name;
			$result["data"][$key]["value"] = $val;
		}

		return $this->cache["prepare"][__METHOD__] = $result;
	}

	/**
	 * Получает статистику по посещаемым поисковыми ботами страницам сайта (визиты)
	 *
	 * @return array
	 */
	public function traffic_pages_search_bot()
	{
		if(isset($this->cache["prepare"][__METHOD__]))
		{
			return $this->cache["prepare"][__METHOD__];
		}

		$total = DB::query_result(
			"SELECT SUM(visits_search_bot_count) AS `count` FROM {visitors_stat_traffic_pages}"
			." WHERE visits_search_bot_count>0"
			." ORDER BY `count` DESC LIMIT 1"
		);
		$row = DB::query_fetch_key(
			"SELECT site_id, visits_search_bot_count AS `count` FROM {visitors_stat_traffic_pages}"
			." WHERE visits_search_bot_count>0"
			." GROUP BY site_id ORDER BY `count` DESC LIMIT 10",
			"site_id"
		);
		$data = array();
		foreach($row as $key => $val) $data[$key] = $val["count"];
		/*if(! empty($data))
		{
			//array_multisort($data, SORT_DESC, SORT_NUMERIC);
			if(count($data) >= 10)
			{
				$data = array_slice($data, 0, 9, true);
			}
		}*/

		// приобразовываем данные для визуализации
		$result = array(
			'data' => array(),
			'min'  => (! empty($data) ? min($data) : 0),
			'max'  => (! empty($data) ? max($data) : 0),
			'all'  => $total,
			'other'=> (! empty($data) ? $total - array_sum($data) : 0),
		);
		foreach($data as $key => $val)
		{
			if(! $name = DB::query_result("SELECT [name] AS `count` FROM {site} WHERE id=%d LIMIT 1", $key))
			{
				$name = 'unknown';
			}
			$result["data"][$key]["categories"] = $name;
			$result["data"][$key]["value"] = $val;
		}

		return $this->cache["prepare"][__METHOD__] = $result;
	}

	/**
	 * Получает статистику по посещаемым ботами страницам сайта (визиты)
	 *
	 * @return array
	 */
	public function traffic_pages_bot()
	{
		if(isset($this->cache["prepare"][__METHOD__]))
		{
			return $this->cache["prepare"][__METHOD__];
		}

		$total = DB::query_result(
			"SELECT SUM(visits_bot_count) AS `count` FROM {visitors_stat_traffic_pages}"
			." WHERE visits_bot_count>0"
			." ORDER BY `count` DESC LIMIT 1"
		);
		$row = DB::query_fetch_key(
			"SELECT site_id, visits_bot_count AS `count` FROM {visitors_stat_traffic_pages}"
			." WHERE visits_bot_count>0"
			." GROUP BY site_id ORDER BY `count` DESC LIMIT 10",
			"site_id"
		);
		$data = array();
		foreach($row as $key => $val) $data[$key] = $val["count"];
		/*if(! empty($data))
		{
			//array_multisort($data, SORT_DESC, SORT_NUMERIC);
			if(count($data) >= 10)
			{
				$data = array_slice($data, 0, 9, true);
			}
		}*/

		// приобразовываем данные для визуализации
		$result = array(
			'data' => array(),
			'min'  => (! empty($data) ? min($data) : 0),
			'max'  => (! empty($data) ? max($data) : 0),
			'all'  => $total,
			'other'=> (! empty($data) ? $total - array_sum($data) : 0),
		);
		foreach($data as $key => $val)
		{
			if(! $name = DB::query_result("SELECT [name] AS `count` FROM {site} WHERE id=%d LIMIT 1", $key))
			{
				$name = 'unknown';
			}
			$result["data"][$key]["categories"] = $name;
			$result["data"][$key]["value"] = $val;
		}

		return $this->cache["prepare"][__METHOD__] = $result;
	}

	/**
	 * Сохраняет статистику по поисковым ботам (визиты)
	 *
	 * @return void
	 */
	public function set_traffic_names_search_bot()
	{
		if(isset($this->cache["prepare"][__METHOD__]))
		{
			return $this->cache["prepare"][__METHOD__] = true;
		}

		if(! $start_time = DB::query_result("SELECT MAX(`timeedit`) AS `timeedit` FROM {visitors_stat_traffic_names_search_bot} WHERE 1=1 LIMIT 1"))
		{
			$start_time = 0;
		}
		if(! $end_time = DB::query_result("SELECT MAX(`master_id`) AS `timeedit` FROM {visitors_url} WHERE 1=1 LIMIT 1"))
		{
			$end_time = 0;
		}
		if($start_time >= $end_time)
		{
			return;
		}
		$rows_search_bot = DB::query_fetch_key(
			"SELECT search_bot, COUNT(*) AS `count` FROM {visitors_url}"
			." WHERE `status`='0' AND search_bot<>'' AND is_admin='0' AND timeedit>%d AND timeedit<=%d"
			." GROUP BY search_bot ORDER BY `count` DESC",
			$start_time, $end_time, "search_bot"
		);
		$rows = array();
		foreach ($rows_search_bot as $key => $value) $rows[$key]["search_bot"] = $value["count"];
		foreach ($rows as $key => $value)
		{
			if($id = DB::query_result("SELECT id FROM {visitors_stat_traffic_names_search_bot} WHERE search_bot='%h' LIMIT 1", $key))
			{
				$this->diafan->_db_ex->update('{visitors_stat_traffic_names_search_bot}', $id,
					array(
						"visits_search_bot_count=visits_search_bot_count+%d", "timeedit=%d"
					),
					array(
						$value["search_bot"], $end_time
					)
				);
			}
			else
			{
				$visitors_session_id = $this->diafan->_db_ex->add_new('{visitors_stat_traffic_names_search_bot}',
					array(
						"search_bot", "visits_search_bot_count", "timeedit"
					),
					array(
						"'%h'", "%d", "%d"
					),
					array(
						$key, $value["search_bot"], $end_time
					)
				);
			}
		}

		return $this->cache["prepare"][__METHOD__] = true;
	}

	/**
	 * Пересчет статистики по поисковым ботам (визиты)
	 *
	 * @param string $visitors_session_id идентификатор сессии
	 * @param boolean $status статус валидации пользовательского агента
	 * @return void
	 */
	private function reset_traffic_names_search_bot($visitors_session_id, $status)
	{
		// TO_DO: не требуется пересчет, так как поисковые боты определяются на основе пользовательского агента
		return;
	}

	/**
	 * Получает статистику по поисковым ботам (визиты)
	 *
	 * @return array
	 */
	public function traffic_names_search_bot()
	{
		if(isset($this->cache["prepare"][__METHOD__]))
		{
			return $this->cache["prepare"][__METHOD__];
		}

		$total = DB::query_result(
			"SELECT SUM(visits_search_bot_count) AS `count` FROM {visitors_stat_traffic_names_search_bot}"
			." WHERE visits_search_bot_count>0"
			." ORDER BY `count` DESC LIMIT 1"
		);
		$row = DB::query_fetch_key(
			"SELECT search_bot, visits_search_bot_count AS `count` FROM {visitors_stat_traffic_names_search_bot}"
			." WHERE visits_search_bot_count>0"
			." GROUP BY search_bot ORDER BY `count` DESC LIMIT 10",
			"search_bot"
		);
		$data = array();
		foreach($row as $key => $val) $data[$key] = $val["count"];
		/*if(! empty($data))
		{
			//array_multisort($data, SORT_DESC, SORT_NUMERIC);
			if(count($data) >= 10)
			{
				$data = array_slice($data, 0, 9, true);
			}
		}*/

		// приобразовываем данные для визуализации
		$result = array(
			'data' => array(),
			'min'  => (! empty($data) ? min($data) : 0),
			'max'  => (! empty($data) ? max($data) : 0),
			'all'  => $total,
			'other'=> (! empty($data) ? $total - array_sum($data) : 0),
		);
		foreach($data as $key => $val)
		{
			$result["data"][$key]["categories"] = $key;
			$result["data"][$key]["value"] = $val;
		}

		return $this->cache["prepare"][__METHOD__] = $result;
	}
}

/**
 * Visitors_counter_exception
 *
 * Исключение для счетчика статистических данных
 */
class Visitors_counter_exception extends Exception{}
