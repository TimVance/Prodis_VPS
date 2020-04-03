<?php
/**
 * Каркас для обработки API-запросов модуля
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
 * Api
 *
 * Абстрактный класс для API
 */
abstract class Api extends Diafan
{
	/**
   * @var integer версия API
   */
	const API_VERSION = 1;

	/**
   * @var string success
   */
	const SUCCESS = "success";

	/**
   * @var string error
   */
	const ERROR = "error";

	/**
	 * @var integer время начала работы API
	 */
	private $time;

	/**
	 * @var string полученный электронный ключ при обращении
	 */
	public $token;

	/**
	 * @var integer номер текущего электронного ключа
	 */
	public $token_id = 0;

	/**
	 * @var string имя текущего модуля
	 */
	public $module;

	/**
	 * @var string имя текущего метора
	 */
	public $method;

	/**
	 * @var boolean требуется верификация
	 */
	public $verify = false;

	/**
	 * @var object текущий пользователь
	 */
	public $user = null;

	/**
	 * @var object удаленный хост
	 */
	public $remote = null;

	/**
	 * @var object пагинация
	 */
	public $paginator = null;

	/**
	 * @var array полученный после обработки данных результат
	 */
	public $result;

	/**
	 * @var array массив ошибок
	 */
	public $errors = array(
		"error" => "ошибка",
		"method_unknown" => "Метод запроса не определен.",
		"wrong_param" => "Некорректные входные параметры.",
		"access_denied" => "Доступ запрещен.",
		"only_https" => "Необходимо использовать протокол HTTPS.",
		"wrong_token" => "Неверный электронный ключ.",
		"busy" => "Сервер временно не отвечает на запросы.",
	);

	/**
	 * @var boolean отдавать ответ только запросам AJAX
	 */
	public $ajax = false;

	/**
	 * @var boolean при недопустимых запросах отдавать 404
	 */
	public $page_404 = false;

	/**
	 * @var boolean ответ API только по протоколу HTTPS
	 */
	public $only_https = false;

	/**
	 * Подключает модель
	 *
	 * @return object|null
	 */
	public function __get($name)
	{
		if($name == 'model' || $name == 'inc')
		{
			$module = $this->diafan->current_module;
			if(! isset($this->cache[$name.'_'.$module]))
			{
				if(Custom::exists('modules/'.$module.'/'.$module.'.'.$name.'.php'))
				{
					Custom::inc('modules/'.$module.'/'.$module.'.'.$name.'.php');
					$class = ucfirst($module).'_'.$name;
					$this->cache[$name.'_'.$module] = new $class($this->diafan, $module);
				}
			}
			return $this->cache[$name.'_'.$module];
		}
		return NULL;
	}

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
		$this->time = microtime(true);
		Custom::inc('includes/header.php');
		$this->paginator = new Api_paginator();
		$this->user = new stdClass();
		$this->user->id = 0;
		$this->remote = new stdClass();
		$this->remote->addr = getenv("HTTP_CLIENT_IP") ?: getenv("HTTP_X_FORWARDED_FOR") ?: getenv("HTTP_X_FORWARDED") ?: getenv("HTTP_FORWARDED_FOR") ?: getenv("HTTP_FORWARDED") ?: getenv("HTTP_X_REAL_IP") ?: getenv("REMOTE_ADDR");
		$this->remote->host = getenv("REMOTE_HOST") ?: gethostbyaddr($this->remote->addr);
		require_once(ABSOLUTE_PATH.'plugins/idna.php');
		$IDN = new idna_convert(array('idn_version' => '2008'));
		$domain = $IDN->decode($this->remote->host);
		$domain = $domain ? $domain : $this->remote->host;
		$this->remote->hostname = $domain;
		$this->remote->agent = getenv("HTTP_USER_AGENT");
		$domain = false; $ref_domain = false;
		if($this->remote->referer = getenv("HTTP_REFERER")) $ref_domain = parse_url($this->remote->referer, PHP_URL_HOST);
		$this->remote->hostname = $ref_domain ?: $this->remote->host;
		$domain = $IDN->decode($this->remote->hostname);
		$this->remote->hostname = $domain ?: $this->remote->hostname;
		$domain = $ref_domain ?: $this->remote->host;
		$this->remote->host_equals = false;
		$ips = $this->gethostbyname($domain);
		$this->remote->host_equals = (! empty($ips) && ! empty($ips["ip"]) && in_array($this->remote->addr, $ips["ip"]));

	}

	/**
	 * Получает массив IPv4 и IPv6 -адресов, соответствующие переданному имени хоста
   *
	 * @param string $host имя хоста
	 * @return array
	 */
	public function gethostbyname($host)
  {
    if(! isset($this->cache["hostbyname"][$host]))
    {
      $this->cache["hostbyname"][$host] = array(
				"ip" => array(),
				"ipv4" => array(),
				"ipv6" => array(),
      );
      if($dns = dns_get_record($host, DNS_ANY))
      {
        if(! empty($dns) && is_array($dns))
        {
          foreach($dns as $record)
          {
            if(empty($record["type"])) continue;
            if($record["type"] == "A" && ! empty($record["ip"]))
						{
							$this->cache["hostbyname"][$host]["ip"][] = $this->cache["hostbyname"][$host]["ipv4"][] = $record["ip"];
						}
            if($record["type"] == "AAAA" && ! empty($record["ipv6"]))
						{
							$this->cache["hostbyname"][$host]["ip"][] = $this->cache["hostbyname"][$host]["ipv6"][] = $record["ipv6"];
						}
          }
        }
      }
    }
    return $this->cache["hostbyname"][$host];
  }

	/**
	 * Гнерирует исключение с выводом времени работы скрипта
	 *
	 * @return void
	 */
	protected function debug_time()
	{
		throw new Api_exception("Время выполнения скрипта: " . (microtime(true) - $this->debug_time));
	}

	/**
	 * Определяет свойства класса
	 *
	 * @param string $module имя модуля
	 * @param string $method имя метода
	 * @return void
	 */
	public function prepare($module, $method)
	{
		$this->module = preg_replace('/[^a-z0-9_]+/', '', $module);
		$this->method = preg_replace('/[^a-z0-9_]+/', '', $method);
		$this->variables();
		$this->auth();
		if($this->verify && ! $this->is_verify()) $this->clear();
	}

	/**
	 * Определение свойств класса
	 *
	 * @return void
	 */
	public function variables(){}

	/**
	 * Авторизация или получение токена
	 *
	 * @param integer $user_id номер пользователя
	 * @return string
	 */
	public function auth($user_id = false)
	{
		if($user_id)
		{
			if(! $u_id = DB::query_result("SELECT id FROM {users} WHERE trash='0' AND id=%d LIMIT 1", $user_id))
			{
				return false;
			}
			$row = DB::query_fetch_array(
				"SELECT * FROM {users_token} WHERE user_id=%d AND element_type='api' GROUP BY id ORDER BY date_finish DESC, id DESC LIMIT 1",
				$u_id
			);
			if(! empty($row["id"]))
			{
				DB::query("DELETE FROM {users_token} WHERE user_id=%d AND element_type='api' AND id<>%d", $u_id, $row["id"]);
			}
			else DB::query("DELETE FROM {users_token} WHERE user_id=%d AND element_type='api'", $u_id);
			if($u_id == $this->user->id)
			{
				$this->clear();
			}
			$token = ! empty($row["token"]) ? $row["token"] : $this->token();
			$time = time();
			$date_start = $time;
			$date_finish = $date_start + (180 * 24 * 60 * 60); // 180 дней
			if(! empty($row["id"]))
			{
				DB::query("UPDATE {users_token} SET user_id=%d, token='%h', element_type='%h', created=%d, date_start=%d, date_finish=%d WHERE id=%d", $u_id, $token, 'api', $time, $date_start, $date_finish, $row["id"]);
				$token_id = $row["id"];
			}
			else
			{
				$token_id = DB::query("INSERT INTO {users_token} (user_id, token, element_type, created, date_start, date_finish) VALUES (%d, '%h', '%h', %d, %d, %d)", $u_id, $token, 'api', $time, $date_start, $date_finish);
			}
			if(! $token = DB::query_result("SELECT token FROM {users_token} WHERE id=%d LIMIT 1", $token_id))
			{
				return false;
			}
			$this->user->id = $u_id;
			$this->token_id = $token_id;
			$this->token = $token;
			$this->user();
			return $this->token;
		}

		if($this->is_auth())
		{
			return false;
		}
		$this->clear();
		if(! $value = Header::value('Authorization', 'OAuth '))
		{
			return false;
		}
		$this->token = $this->diafan->filter($value, "string");
		if(! $this->token)
		{
			$this->clear();
			return false;
		}
		$time = time();
		if(! $row = DB::query_fetch_array("SELECT u.id, u.role_id, t.token, t.id AS token_id FROM {users} AS u"
			." INNER JOIN {users_token} AS t ON u.id = t.user_id"
			." WHERE u.act='1' AND u.trash='0' AND u.created<=%d"
			." AND t.created<=%d AND t.date_start<=%d AND (t.date_finish=0 OR t.date_finish>=%d)"
			." AND t.token<>'' AND t.token='%h'"
			." AND t.element_type='api'"
			." GROUP BY u.id ORDER by t.id DESC, t.created DESC, u.created DESC"
			." LIMIT 1", $time, $time, $time, $time, $this->token))
		{
			$this->clear();
			return false;
		}
		$this->user->id = $row["id"];
		$this->token_id = $row["token_id"];
		$this->token = $row["token"];
		$this->user();
		return $this->token;
	}

	/**
	 * Проверяет авторизацию текущего пользователя
	 *
	 * @return boolean
	 */
	public function is_auth()
	{
		if(! $this->token || ! $this->token_id || ! $this->user->id)
		{
			return false;
		}
		return true;
	}

	/**
	 * Верификация
	 *
	 * @return boolean
	 */
	public function is_verify()
	{
		if(isset($this->cache["tick_verify"]))
		{
			return $this->cache["tick_verify"];
		}
		if(! $value = Header::value('Tick', 'UID '))
		{
			return $this->cache["tick_verify"] = false;
		}
		if(! $this->remote->host_equals)
		{
			return $this->cache["tick_verify"] = false;
		}
		$hashed = $this->diafan->filter($value, "string");
		if(! $hashed || ! $hashed = base64_decode($hashed)) return $this->cache["tick_verify"] = false;
		return $this->cache["tick_verify"] = hash_equals($hashed, crypt($this->remote->hostname, $hashed));
	}

	/**
	 * Сброс авторизации
	 *
	 * @return void
	 */
	private function clear()
	{
		$this->token = null;
		$this->token_id = 0;
		$this->user = new stdClass();
		$this->user->id = 0;
	}

	/**
	 * Отдает уникальный электронный ключ
	 *
	 * @return mixed(string|object)
	 */
	private function token()
	{
		// TO_DO: генерация псевдослучайной соли для CRYPT_BLOWFISH hash type
		$salt = substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand()))), 0, 22);
		$token = md5(base64_encode(crypt($this->diafan->domain().$this->diafan->uid(true), $salt)));
		if(DB::query_result("SELECT id FROM {users_token} WHERE element_type='api' AND token='%h' LIMIT 1", $token))
		{
			$token = $this->token();
		}
		return $token;
	}

	/**
	 * Отзывает электронный ключ у текущего пользователя
	 *
	 * @return boolean
	 */
	public function revoke()
	{
		if(! $this->is_auth() || ! $this->user->id)
		{
			return false;
		}
		DB::query("DELETE FROM {users_token} WHERE user_id=%d AND element_type='api'", $this->user->id);
		$this->clear();
		return true;
	}

	/**
	 * Возвращает данные о текущем электронном ключе
	 *
	 * @return array
	 */
	public function token_info()
	{
		if(! $value = Header::value('Authorization', 'OAuth '))
		{
			return false;
		}
		$this->token = $this->diafan->filter($value, "string");
		if(! $this->token)
		{
			return false;
		}
		$time = time();
		if(! $row = DB::query_fetch_array("SELECT"
			." u.id AS user_id, u.role_id AS user_role_id, u.act AS user_act, u.trash AS user_trash, u.created AS user_created"
			.", t.token, t.id AS token_id, t.created AS token_created, t.date_start AS token_date_start, t.date_finish AS token_date_finish"
			." FROM {users} AS u"
			." INNER JOIN {users_token} AS t ON u.id = t.user_id"
			." WHERE t.token<>'' AND t.token='%h' AND t.element_type='api'"
			." GROUP BY u.id ORDER by t.id DESC, t.created DESC, u.created DESC"
			." LIMIT 1", $this->token))
		{
			return false;
		}
		$row["date"] = $time;
		return $row;
	}

	/**
	 * Обновляет информацию о текущем пользователе
	 *
	 * @return void
	 */
	private function user()
	{
		if(! $this->is_auth() || ! $this->user->id)
		{
			return;
		}
		$result_sql = DB::query("SELECT * FROM {users} WHERE id=%d", $this->user->id);
		if(! DB::num_rows($result_sql))
		{
			return;
		}
		$this->user = DB::fetch_object($result_sql);
		DB::free_result($result_sql);
		$this->user->avatar = file_exists(ABSOLUTE_PATH.'userfiles/avatar/'.$this->user->name.'.png')
				? BASE_PATH_HREF.USERFILES.'/avatar/'.$this->user->name.'.png'
				: BASE_PATH_HREF.Custom::path('img/avatar.jpg');
	}

	/**
	 * Является ли запрос AJAX
	 *
	 * @return boolean
	 */
	private function is_ajax()
	{
		if(! empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest'
			// для IE
			|| ! empty($_POST["action"]))
		{
			return true;
		}
		return false;
	}

	/**
	 * Отправляет ответ
	 *
	 * @return void
	 */
	public function end()
	{
		if($this->only_https && ! IS_HTTPS)
		{
			$this->result = array();
			$this->set_error("only_https");
		}
		$this->result = is_array($this->result) ? $this->result : array('result' => $this->result);
		$params = array("errors", "result");
		$s = false;
		foreach ($params as $v)
		{
			if(isset($this->result[$v]))
			{
				$s = true;
				break;
			}
		}
		if($s && (! $this->ajax || $this->is_ajax()))
		{
			if(! isset($this->result["result"]) || is_bool($this->result["result"]))
			{
				if(isset($this->result["result"]) && ! $this->result["result"] && empty($this->result["errors"]))
				{
					$this->set_error("error");
				}
				$this->result["result"] = array();
			}
			if(! is_array($this->result["result"]))
			{
				$this->result["result"] = array("result" => $this->result["result"]);
			}
			if($this->paginator->nen > 0 && empty($this->result["result"]["paginator"]))
			{
				$variable = $this->paginator->variable;
				$this->result["result"]["paginator"] = array(
					"nen" => $this->paginator->nen,
					"nastr" => $this->paginator->nastr,
					"polog" => $this->paginator->polog,
					"offset" => $this->paginator->offset,
					"first_page" => $this->paginator->first_page,
					"prev_page" => $this->paginator->prev_page,
					"cur_page" => $this->paginator->cur_page,
					"next_page" => $this->paginator->next_page,
					"last_page" => $this->paginator->last_page,
					"variable" => $variable,
					"urlpage" => $this->paginator->urlpage,
					"page" => $this->paginator->$variable,
				);
			}
			if(! empty($this->result["errors"]))
			{
				if(! empty($this->result["result"]["errors"]))
				{
					$this->result["result"]["errors"] = array_merge($this->result["result"]["errors"], $this->result["errors"]);
				}
				else $this->result["result"]["errors"] = $this->result["errors"];
				unset($this->result["errors"]);
			}
			if(! isset($this->result["result"]["v"]))
			{
				$this->result["result"]["v"] = self::API_VERSION;
			}
			if(! isset($this->result["result"]["method"]) && $this->method)
			{
				$this->result["result"]["method"] = $this->method;
			}
			if(! isset($this->result["result"]["request"]))
			{
				if(! isset($this->result["result"]["errors"])) $this->result["result"]["request"] = self::SUCCESS;
				else $this->result["result"]["request"] = self::ERROR;
			}
			$this->result = $this->result["result"];
			echo $this->to_json($this->result);
			exit;
		}
		else $this->result = array();
		if(! $this->page_404)
		{
			$this->set_error("error");
			if(! empty($this->result["errors"])) $this->result["result"] = $this->result["errors"];
			else $this->result["result"] = array(
				"v" => self::API_VERSION,
				"method" => $this->method ?: "unknown",
				"request" => self::ERROR,
				"errors" => array("error" => "error"),
			);
			$this->result = $this->result["result"];
			echo $this->to_json($this->result);
			exit;
		}
		include(ABSOLUTE_PATH.Custom::path('includes/404.php'));
		exit;
	}

	/**
	 * Запоминает найденную ошибку
	 *
	 * @return void
	 */
	public function set_error($key, $value = false)
	{
		if(! $value && isset($this->errors[$key]))
		{
			$value = $this->errors[$key];
		}
		if($value)
		{
			$this->result = is_array($this->result) ? $this->result : array('result' => $this->result);
			// TO_DO: $this->result["errors"][$key] = $this->diafan->_($value, false);
			$args = func_get_args();
			unset($args[0]);
			unset($args[1]);
			if(! defined('IS_ADMIN') || ! IS_ADMIN)
			{
				$this->result["errors"][$key] = $this->diafan->_languages->get($value, $this->diafan->current_module, false, $args);
			}
			else
			{
				$this->result["errors"][$key] = $this->diafan->_languages->get($value, $this->diafan->_admin->module, false, $args);
			}
		}
		else $this->result["errors"][$key] = $key;
	}

	/**
	 * Проверяет сформирован ли ответ
	 *
	 * @return boolean
	 */
	public function result()
	{
		if(is_array($this->result) && (! empty($this->result["result"]) || ! empty($this->result["errors"])))
		{
			return true;
		}
		return false;
	}

	/**
	 * Преобразует массив в формат JSON
	 *
	 * @param array $data исходный массив
	 * @return string
	 */
	private function to_json($data)
	{
		header('Content-Type: application/json; charset=utf-8');
		$php_version_min = 50400; // PHP 5.4
		if($this->diafan->version_php() < $php_version_min)
		{
			// TO_DO: кириллица в ответе JSON - JSON_UNESCAPED_UNICODE
			$json = preg_replace_callback(
				"/\\\\u([a-f0-9]{4})/",
				function($matches) {
					return iconv('UCS-4LE','UTF-8',pack('V', hexdec('U' . $matches[0])));
				},
				json_encode($data)
			);
			$json = str_replace('&', '&amp;', $json);
			$json = str_replace(array('<', '>'), array('&lt;', '&gt;'), $json);
		}
		else $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
		return $json;
	}
}

/**
 * Api_exception
 *
 * Исключение для обработки API-запросов
 */
class Api_exception extends Exception{}

/**
 * Api_paginator
 *
 * Набор функция для пагинации API
 */
class Api_paginator
{
	/**
	 * @var array массив переменных в URI
	 */
	public $rewrite_variables = array('page');

	/**
	 * @var string переменная в URI участвующая в текущей пагинации
	 */
	public $variable = 'page';

	/**
	 * @var integer порядковый номер элемента, с которого начинается вывод элементов
	 */
	public $offset = 1;

	/**
	 * @var integer индекс элемента, с которого начинается вывод элементов
	 */
	public $polog = 0;

	/**
	 * @var integer количество элементов в списке
	 */
	public $nen;

	/**
	 * @var integer количество элементов, показанных на странице
	 */
	public $nastr = 0;

	/**
	 * @var integer номер первой страницы списка элементов
	 */
	public $first_page = 1;

	/**
	 * @var integer номер предыдущей страницы списка элементов
	 */
	public $prev_page = 1;

	/**
	 * @var integer номер текущей страницы списка элементов
	 */
	public $cur_page = 1;

	/**
	 * @var integer номер текущей страницы списка элементов
	 */
	public $next_page = 1;

	/**
	 * @var integer номер последней страницы списка элементов
	 */
	public $last_page = 1;

	/**
	 * @var string шаблон части ссылки, отвечающей за передачу номера страницы
	 */
	public $urlpage = 'page%d/';

	/**
	 * Подключает модель
	 *
	 * @return object|null
	 */
	public function __get($name)
	{
		$variable_names = $this->rewrite_variables;
		if (in_array($name, $variable_names))
		{
			if(! isset($this->cache["vars"][$name]))
			{
				$this->cache["vars"][$name] = '';
			}
			return $this->cache["vars"][$name];
		}
		return NULL;
	}

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->init();
	}

	/**
	 * Определяет свойства класса
	 *
	 * @return void
	 */
	private function init()
	{
		if($_GET["rewrite"])
		{
			$rewrite_array = explode("/", preg_replace('/'.preg_quote(ROUTE_END, '/').'$/', '', $_GET["rewrite"]));
			foreach($this->rewrite_variables as $key => $name)
			{
				$this->$name = 0;
			}
			foreach($rewrite_array as $key => $ra)
			{
				foreach($this->rewrite_variables as $name)
				{
					if (preg_match('/^'.$name.'([0-9]+)$/', $ra, $result))
					{
						$this->$name = (int) $result[1];
						unset($rewrite_array[$key]);
					}
				}
			}
		}
	}

	/**
	 * Рассчитывает параметры постраничной навигации
	 *
	 * @param integer $name имя переменной передаваемой в URI
	 * @return void
	 */
	 public function prepare($name)
 	{
 		$this->init();

 		if(! $this->nen || $this->nen <= 0
 		|| ! in_array($name, $this->rewrite_variables))
 		{
 			$this->nen = 0;
 			$this->nastr = 0;
 			$this->polog = 0;
 			$this->offset = 1;
 			$this->first_page = 1;
 			$this->prev_page = 1;
 			$this->cur_page = 1;
 			$this->next_page = 1;
 			$this->last_page = 1;
 			$this->variable = 'page';
			$this->urlpage = $this->variable.'%d';
 		}
 		else
 		{
 			if(! $this->nastr || $this->nastr <= 0)
 			{
 				$this->nastr = 10;
 			}
 			$this->last_page = ceil($this->nen / $this->nastr);
 			if($this->$name <= 0) $this->$name = 1;

 			$this->first_page = 1;
 			$this->cur_page = $this->$name;
 			$this->cur_page = $this->cur_page < $this->first_page ? $this->first_page : $this->cur_page > $this->last_page ? $this->last_page : $this->cur_page;
 			$this->prev_page = $this->cur_page - 1;
 			$this->prev_page = $this->prev_page < $this->first_page ? $this->first_page : $this->prev_page > $this->last_page ? $this->last_page : $this->prev_page;
 			$this->next_page = $this->cur_page + 1;
 			$this->next_page = $this->next_page < $this->first_page ? $this->first_page : $this->next_page > $this->last_page ? $this->last_page : $this->next_page;

 			$this->polog = (($this->nastr * $this->$name) - $this->nastr);
 			$this->polog = $this->polog >= 0 ? $this->polog : 0;

 			$this->variable = $name;
			$this->urlpage = $this->variable.'%d';
 			$this->offset = $this->polog + 1;

 			$this->result["paginator"] = array(
 				"nen" => $this->nen,
 				"nastr" => $this->nastr,
 				"polog" => $this->polog,
 				"offset" => $this->offset,
 				"first_page" => $this->first_page,
 				"prev_page" => $this->prev_page,
 				"cur_page" => $this->cur_page,
 				"next_page" => $this->next_page,
 				"last_page" => $this->last_page,
 				"variable" => $name,
				"urlpage" => $this->urlpage,
 				"page" => $this->$name,
 			);
 		}
 	}
}

/**
 * Api_paginator_exception
 *
 * Исключение для пагинации API-запросов
 */
class Api_paginator_exception extends Exception{}
