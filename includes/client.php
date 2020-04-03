<?php
/**
 * Клиент для API-запросов модуля
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

class Client extends Diafan
{
	/**
   * @var string success
   */
	const SUCCESS = "success";

	/**
	 * @var object объект пагинации
	 */
	public $paginator = null;

	/**
	 * @var array массив текущих ошибок
	 */
	public $errors = array();

	/**
	 * @var boolean выдавать исключения PHP
	 */
	public $throw_exception = false;

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
		$this->paginator = new stdClass();
	}

	/**
	 * Устанавливает валидные версии API для доменов
	 *
	 * @param string $domain доменное имя
	 * @param string $v версия API
	 * @return void
	 */
	public function set_valid_version($domain, $v = false)
	{
		if(! is_string($domain)) return;
		$v = is_string($v) ? $v : false;
		if(! isset($this->cache["valid_version"])) $this->cache["valid_version"] = array();
		$this->cache["valid_version"][$domain] = $v;
	}

	/**
	 * Предварительная обработка полученного ответа
	 *
	 * @param string $json данные в формат JSON
	 * @param string $v допустимая версия API
	 * @return array
	 */
	protected function prepare_request($json, $v = false)
	{
		if(empty($json))
		{
			return false;
		}
		Custom::inc('plugins/json.php');
		$json = from_json($json, true);
		if(! is_array($json) || empty($json["request"]))
		{
			return false;
		}
		$v = is_string($v) ? $v : false;
		if($v && (! isset($json["v"]) || $v != $json["v"]))
		{
			if($this->throw_exception)
			{
				throw new Client_exception(
					'Ответ API (версия '.$json["v"].') не совпадает с ожидаемым ответом (версия '.$v.').'."\n"
					.' Возможно требуется обновить CMS до актуальной версии.');
			}

			$this->errors = array(
				"v" => $this->diafan->_(
					'Ответ API (версия %s) не совпадает с ожидаемым ответом (версия %s).'
					.'%sВозможно требуется %sобновить CMS%s до актуального состояния.',
					'<b>'.$json["v"].'</b>', '<b>'.$v.'</b>', '<br>', '<a class="link" href="'.BASE_PATH_HREF.'update/">', '</a>'
				),
			);
			return false;
		}
		if(isset($json["v"])) unset($json["v"]);
		$this->errors = ! empty($json["errors"]) ? $json["errors"] : array();
		$this->errors = ! is_array($this->errors) ? array($this->errors) : $this->errors;
		if($json["request"] != self::SUCCESS)
		{
			return false;
		}
		unset($json["request"]);
		if(isset($json["method"])) unset($json["method"]);
		if(isset($json["paginator"]) && is_array($json["paginator"]))
		{
			foreach($json["paginator"] as $key => $value)
			{
				$this->paginator->$key = $value;
			}
			unset($json["paginator"]);
		}
		$json = ! empty($json) ? $json : true;
		return $json;
	}

	/**
	 * Возвращает URI API
	 *
	 * @param string $domen имя домена
	 * @param string $module имя модуля
	 * @param string $method имя метода
	 * @param integer $page номер страницы
	 * @param string $urlpage шаблон части ссылки, отвечающей за передачу номера страницы
	 * @return array
	 */
	public function uri($domain, $module, $method, $page = false, $urlpage = 'page%d/')
	{
		return "http".(IS_HTTPS ? "s" : "")."://".$domain."/"."api"."/".$module."/".$method."/".($page && $urlpage ? sprintf($urlpage, $page).'/' : '');
	}

	/**
	 * Возвращает позицию заголовка
	 *
	 * @param array $headers заголовки
	 * @param string $key ключ
	 * @param string $value значение
	 * @param boolean $strict строгое соответствие
	 * @return integer
	 */
	protected function header_pos($headers, $key = false, $value = false, $strict = FALSE)
	{
		if(! $headers || ! $key && ! $value) return false;
		$headers = is_string($headers) ? array($headers) : $headers;
		if(! is_array($headers)) return false;
		if(! $strict)
		{
			$key = $key ? trim(strtolower($key)) : $key;
			$value = $value ? trim(strtolower($value)) : $value;
		}
		$pos = false;
		foreach ($headers as $p => $val)
		{
			if(! $val) continue;
			$val = explode(":", $val);
			$count = count($val);
			$k = $count > 1 ? array_shift($val) : false;
			$val = implode(":", $val);
			if(! $strict)
			{
				$k = $k ? trim(strtolower($k)) : $k;
				$val = $val ? trim(strtolower($val)) : $val;
			}
			if($key && $key != $k) continue;
			if($value && $value != $val) continue;
			$pos = $p;
			break;
		}
		return $pos;
	}

	/**
	 * Возвращает уникальный тик
	 *
	 * @return string
	 */
	public function tick()
	{
		// TO_DO: генерация псевдослучайной соли для CRYPT_BLOWFISH hash type
		$salt = substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand()))), 0, 22);
		return base64_encode(crypt($this->diafan->domain(), $salt));
	}

	/**
	 * Задает неопределенным атрибутам значение по умолчанию
	 *
	 * @param array $attributes массив определенных атрибутов
	 * @return array
	 */
	public function get_attributes(&$attributes)
	{
		$a = func_get_args();
		for ($i = 1; $i < count($a); $i++)
		{
			if (is_array($a[$i]))
			{
				$name = $a[$i][0];
				$value = $a[$i][1];
			}
			else
			{
				$name = $a[$i];
				$value = '';
			}
			if (empty($attributes[$name]))
			{
				$attributes[$name] = $value;
			}
		}
		return $attributes;
	}

	/**
	 * Возвращает ответ API
	 *
	 * @param string $url адрес URI
	 * @param string $token электронный ключ
	 * @param array $param параметры запроса
	 * @param array $header передаваемые заголовки
	 * @param integer $flag флаг или комбинация флагов запроса
	 * @return array
	 */
	public function request($url, $token = false, $param = false, $header = false, $flag = 0)
	{
		$this->errors = array();
		$param = is_array($param) ? $param : false;
		$header = is_array($header) ? array_values($header) : (is_string($header) ? array($header) : array());
		$token = ($token && is_string($token) ? $token : false);
		if($token && FALSE === $this->header_pos($header, 'Authorization'))
		{
			$header[] = 'Authorization: OAuth '.$token;
		}
		// if($token && FALSE === $this->header_pos($header, 'Host')) { $header[] = 'Host: ' . $this->diafan->domain(); }
		if(FALSE === $this->header_pos($header, 'Tick')) { $header[] = 'Tick: UID '.$this->tick(); }

		$options = ( REQUEST_POST | REQUEST_ANSWER | REQUEST_AJAX);
		if($flag & CLIENT_DOWNLOAD) $options = ($options | REQUEST_DOWNLOAD);
		if($flag & CLIENT_DEBUG) $options = ($options | REQUEST_DEBUG);
		$answer = $this->diafan->fast_request(
			$url, $param, $header, false, $options
		);
		if(($flag & CLIENT_DEBUG) || ($flag & CLIENT_DOWNLOAD))
		{
			return $answer;
		}
		if($flag & CLIENT_NO_PREPARE)
		{
			return $answer;
		}
		$v = false;
		if($domain = parse_url($url, PHP_URL_HOST))
		{
			$v = isset($this->cache["valid_version"][$domain]) ? $this->cache["valid_version"][$domain] : false;
		}
		if(FALSE === $answer = $this->prepare_request($answer, $v))
		{
			return false;
		}
		return $answer;
	}

	/**
	 * Возвращает электронный ключ
	 *
	 * @param string $domain доменное имя
	 * @param string $login имя учтной записи
	 * @param string $password пароль учетной записи
	 * @param integer $flag флаг или комбинация флагов запроса
	 * @return string
	 */
	public function auth($domain, $login, $password, $flag = 0)
	{
		$flag &= ~ CLIENT_DOWNLOAD;
		$flag &= ~ CLIENT_LOCAL_REVOKE;
		$answer = $this->request(
			$this->uri($domain, "registration", "auth_code"),
			false,
			array(
				"name" => $login,
				"pass" => $password,
			),
			false, $flag
		);
		if($flag & CLIENT_DEBUG)
		{
			return $answer;
		}
		if($flag & CLIENT_NO_PREPARE)
		{
			return $answer;
		}
		if(FALSE === $answer)
		{
			return false;
		}
		if(empty($answer["token"]) || ! is_string($answer["token"]))
		{
			return false;
		}
		return $answer["token"];
	}

	/**
	 * Возвращает информацию об электронном ключе
	 *
	 * @param string $domain доменное имя
	 * @param string $token электронный ключ
	 * @param integer $flag флаг или комбинация флагов запроса
	 * @return array
	 */
	public function token($domain, $token, $flag = 0)
	{
		$flag &= ~ CLIENT_DOWNLOAD;
		$flag &= ~ CLIENT_LOCAL_REVOKE;
		$answer = $this->request(
			$this->uri($domain, "registration", "auth_code_info"), $token, false, false, $flag
		);
		if($flag & CLIENT_DEBUG)
		{
			return $answer;
		}
		if($flag & CLIENT_NO_PREPARE)
		{
			return $answer;
		}
		if(FALSE === $answer)
		{
			return false;
		}
		return $answer;
	}

	/**
	 * Отзывает электронный ключ
	 *
	 * @param string $domain доменное имя
	 * @param string $token электронный ключ
	 * @param integer $flag флаг или комбинация флагов запроса
	 * @return array
	 */
	public function revoke($domain, $token, $flag = 0)
	{
		$flag &= ~ CLIENT_DOWNLOAD;
		if($flag & CLIENT_LOCAL_REVOKE)
		{
			$this->diafan->configmodules("token", "account", 0, 0, '');
			return true;
		}
		$answer = $this->request(
			$this->uri($domain, "registration", "auth_code_revoke"), $token, false, false, $flag
		);
		if($flag & CLIENT_DEBUG)
		{
			return $answer;
		}
		if($flag & CLIENT_NO_PREPARE)
		{
			return $answer;
		}
		if(FALSE === $answer)
		{
			return false;
		}
		return true;
	}
}

/**
 * Client_exception
 *
 * Исключение для клиента API-запросов
 */
class Client_exception extends Exception{}

/**
 * Client_const
 *
 * Исключение для работы с файлами
 */
// Флаг request: возвращает заголовки запроса и ответа
if(! defined('CLIENT_DEBUG')) define('CLIENT_DEBUG', 1 << 0);               // 0001
// Флаг request: возвращает результат в виде массива для скачивания контента
if(! defined('CLIENT_DOWNLOAD')) define('CLIENT_DOWNLOAD', 1 << 1);         // 0010
// Флаг request: определяет необходимость предварительной обработки ответа
if(! defined('CLIENT_NO_PREPARE')) define('CLIENT_NO_PREPARE', 1 << 2);     // 0100
// Флаг request: только локальный отзыв электронного ключа
if(! defined('CLIENT_LOCAL_REVOKE')) define('CLIENT_LOCAL_REVOKE', 1 << 3); // 1000
