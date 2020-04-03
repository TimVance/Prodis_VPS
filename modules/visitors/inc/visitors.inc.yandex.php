<?php
/**
 * Подключение модуля «Посещаемость» для работы с API Яндекс.Метрики
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
 * Visitors_inc_yandex
 */
class Visitors_inc_yandex extends Diafan
{
	const API_URI_CLIENT = "https://oauth.yandex.ru/client/";
	const API_URI_AUTHORIZE = "https://oauth.yandex.ru/authorize";
	const API_URI_TOKEN = "https://oauth.yandex.ru/token";
	const API_URI_STAT = "https://api-metrika.yandex.ru/stat/v1/data/";

	/**
	 * @var string E-mail для API Яндекс.Метрики
	 */
	static private $MAIL_LOGIN = '';

	/**
	 * @var string идентификатор зарегистрированного приложения
	 */
	static private $CLIENT_ID = '';

	/**
	 * @var string пароль зарегистрированного приложения
	 */
	static private $CLIENT_PASSWORD = '';

	/**
	 * @var string номер счетчика Яндекс.Метрика, информацию о котором необходимо получить
	 */
	static private $COUNTER_ID = '';

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
		self::$MAIL_LOGIN = $this->diafan->configmodules('yandex_mail_login', 'visitors');
		self::$CLIENT_ID = $this->diafan->configmodules('yandex_client_id', 'visitors');
		self::$CLIENT_PASSWORD = $this->diafan->configmodules('yandex_client_password', 'visitors');
		self::$COUNTER_ID = $this->diafan->configmodules('yandex_counter_id', 'visitors');
	}

	/**
	 * Получает OAuth token
	 *
	 * @param boolean $force_confirm признак того, что необходимо обязательно запросить новый OAuth token
	 * @return string
	 */
	public function token($force_confirm = false)
	{
		$token = false;
		if(! $force_confirm)
		{
			if($token = $this->diafan->configmodules('yandex_token', 'visitors'))
			{
				return $token;
			}
		}

		if(! isset($_GET["code"]) && isset($_SESSION["visitors"]["api"]["code"]))
		{
			$_GET["code"] = $_SESSION["visitors"]["api"]["code"];
			unset($_SESSION["visitors"]["api"]["code"]);
		}
		if(! isset($_GET["code"]))
		{
			// проверка прав для зарегистрированного приложения
			$answer = $this->diafan->fast_request(self::API_URI_CLIENT.self::$CLIENT_ID.'/info', false, false, false, ( REQUEST_GET | REQUEST_ANSWER ));
			$answer = json_decode($answer, true);
			if(! isset($answer["scope"]) || ! in_array("metrika:read", $answer["scope"]))
			{
				// нет прав на чтение статистики
				return false;
			}
			$params = http_build_query(
				array(
					"response_type" => "code",
					"client_id"     => self::$CLIENT_ID,
					"login_hint"    => self::$MAIL_LOGIN,
					"state"         => http_build_query(array('rewrite' => getenv('REQUEST_URI')))
				)
			);
			// направляем клиента на подтверждение прав зарегистрированного приложения
			$this->diafan->redirect(self::API_URI_AUTHORIZE."?".$params, 302);
		}
		//делаем запрос для получения токена
		$answer = $this->diafan->fast_request(self::API_URI_TOKEN, array(
			'grant_type'    => 'authorization_code',
			'code'          => $_GET["code"],
			'client_id'     => self::$CLIENT_ID,
			'client_secret' => self::$CLIENT_PASSWORD
		), false, false, ( REQUEST_POST | REQUEST_ANSWER ));
		if(empty($answer))
		{
			return false;
		}
		$answer = json_decode($answer, true);
		if(empty($answer["access_token"]))
		{
			return false;
		}
		$this->diafan->configmodules('yandex_token', 'visitors', 0, 0, $answer["access_token"]);
		return $answer["access_token"];
	}

	/**
	 * Получает статистику количества визитов, просмотров и уникальных посетителей
	 *
	 * @param boolean $force_confirm признак того, что необходимо обязательно запросить новый OAuth token
	 * @return array
	 */
	public function traffic($force_confirm = false)
	{
		if(! $token = $this->token($force_confirm))
		{
			return false;
		}

		$answer = $this->diafan->fast_request(self::API_URI_STAT, array(
			'ids'         => self::$COUNTER_ID,
			'metrics'     => 'ym:s:visits,ym:s:pageviews,ym:s:users',
			'dimensions'  => 'ym:s:date',
			'date1'       => '30daysAgo',
			'date2'       => 'today',
			'sort'        => 'ym:s:date',
		), array(
			'GET /management/v1/counters HTTP/1.1',
			'Host: api-metrika.yandex.net',
			'Authorization: OAuth '.$token,
			'Content-Type: application/x-yametrika+json',
			'Content-Length: 0',
		), false, ( REQUEST_GET | REQUEST_ANSWER ));
		if(empty($answer))
		{
			return false;
		}
		$answer = json_decode($answer, true);
		if(empty($answer["data"]))
		{
			return false;
		}
		$data = $answer["data"];
		if(empty($data) || ! is_array($data))
		{
			return false;
		}

		// приобразовываем данные для линейного графика
		$result = array(
			'data' => array(),
			'min'  => array(
				'visits'    => (isset ($answer["min"][0]) ? $answer["min"][0] : 0),
				'pageviews' => (isset ($answer["min"][1]) ? $answer["min"][1] : 0),
				'users'     => (isset ($answer["min"][2]) ? $answer["min"][2] : 0)
			),
			'max'  => array(
				'visits'    => (isset ($answer["max"][0]) ? $answer["max"][0] : 0),
				'pageviews' => (isset ($answer["max"][1]) ? $answer["max"][1] : 0),
				'users'     => (isset ($answer["max"][2]) ? $answer["max"][2] : 0)
			)
		);
		foreach($data as $item) {
			$result["data"][] = array(
				'categories' => (isset($item['dimensions'][0]['name']) ? $item['dimensions'][0]['name'] : ''),
				'visits'     => (isset($item['metrics'][0]) ? $item['metrics'][0] : 0),
				'pageviews'  => (isset($item['metrics'][1]) ? $item['metrics'][1] : 0),
				'users'      => (isset($item['metrics'][2]) ? $item['metrics'][2] : 0)
			);
		}

		return $result;
	}

	/**
	 * Получает статистику по источникам трафика (визиты)
	 *
	 * @param boolean $force_confirm признак того, что необходимо обязательно запросить новый OAuth token
	 * @return array
	 */
	public function traffic_source($force_confirm = false)
	{
		if(! $token = $this->token($force_confirm))
		{
			return false;
		}

		$answer = $this->diafan->fast_request(self::API_URI_STAT, array(
			'ids'         => self::$COUNTER_ID,
			'metrics'     => 'ym:s:visits',
			'dimensions'  => 'ym:s:<attribution>TrafficSource',
			//'filters'     => 'ym:s:visits>10',
			'date1'       => '30daysAgo',
			'date2'       => 'today',
		), array(
			'GET /management/v1/counters HTTP/1.1',
			'Host: api-metrika.yandex.net',
			'Authorization: OAuth '.$token,
			'Content-Type: application/x-yametrika+json',
			'Content-Length: 0',
		), false, ( REQUEST_GET | REQUEST_ANSWER ));
		if(empty($answer))
		{
			return false;
		}
		$answer = json_decode($answer, true);
		if(empty($answer["data"]))
		{
			return false;
		}
		$data = $answer["data"];
		if(empty($data) || ! is_array($data))
		{
			return false;
		}

		// приобразовываем данные для визуализации
		$result = array(
			'data' => array(),
			'min'  => (isset ($answer["min"][0]) ? $answer["min"][0] : 0),
			'max'  => (isset ($answer["max"][0]) ? $answer["max"][0] : 0)
		);
		foreach($data as $item) {
			if(empty($item["dimensions"]) || empty($item["metrics"]))
				continue;
			foreach($item["dimensions"] as $key => $dimension)
			{
				if(! isset($dimension["id"]) || ! isset($item["metrics"][$key]))
					continue;
				$result["data"][$dimension["id"]]["categories"] = isset($dimension["name"]) ? $dimension["name"] : $dimension["id"];
				$result["data"][$dimension["id"]]["value"] = $item["metrics"][$key];
			}
		}

		return $result;
	}

	/**
	 * Получает статистику по отказам (доля визитов, в рамках которых состоялся лишь один просмотр страницы, продолжавшийся менее 15 секунд)
	 *
	 * @param boolean $force_confirm признак того, что необходимо обязательно запросить новый OAuth token
	 * @return array
	 */
	public function bounce_rate($force_confirm = false)
	{
		if(! $token = $this->token($force_confirm))
		{
			return false;
		}

		$answer = $this->diafan->fast_request(self::API_URI_STAT, array(
			'ids'         => self::$COUNTER_ID,
			'metrics'     => 'ym:s:bounceRate',
			'dimensions'  => 'ym:s:date',
			'date1'       => '30daysAgo',
			'date2'       => 'today',
			'sort'        => 'ym:s:date',
		), array(
			'GET /management/v1/counters HTTP/1.1',
			'Host: api-metrika.yandex.net',
			'Authorization: OAuth '.$token,
			'Content-Type: application/x-yametrika+json',
			'Content-Length: 0',
		), false, ( REQUEST_GET | REQUEST_ANSWER ));
		if(empty($answer))
		{
			return false;
		}
		$answer = json_decode($answer, true);
		if(empty($answer["data"]))
		{
			return false;
		}
		$data = $answer["data"];
		if(empty($data) || ! is_array($data))
		{
			return false;
		}

		// приобразовываем данные для линейного графика
		$result = array(
			'data' => array(),
			'min'  => (isset ($answer["min"][0]) ? $answer["min"][0] : 0),
			'max'  => (isset ($answer["max"][0]) ? $answer["max"][0] : 0)
		);
		foreach($data as $item) {
			$result["data"][] = array(
				'categories' => (isset($item['dimensions'][0]['name']) ? $item['dimensions'][0]['name'] : ''),
				'value'     => (isset($item['metrics'][0]) ? $item['metrics'][0] : 0)
			);
		}

		return $result;
	}

	/**
	 * Получает статистику по времени на сайте (средняя продолжительность визита в минутах и секундах.)
	 *
	 * @param boolean $force_confirm признак того, что необходимо обязательно запросить новый OAuth token
	 * @return array
	 */
	public function duration($force_confirm = false)
	{
		if(! $token = $this->token($force_confirm))
		{
			return false;
		}

		$answer = $this->diafan->fast_request(self::API_URI_STAT, array(
			'ids'         => self::$COUNTER_ID,
			'metrics'     => 'ym:s:avgVisitDurationSeconds',
			'dimensions'  => 'ym:s:date',
			'date1'       => '30daysAgo',
			'date2'       => 'today',
			'sort'        => 'ym:s:date',
		), array(
			'GET /management/v1/counters HTTP/1.1',
			'Host: api-metrika.yandex.net',
			'Authorization: OAuth '.$token,
			'Content-Type: application/x-yametrika+json',
			'Content-Length: 0',
		), false, ( REQUEST_GET | REQUEST_ANSWER ));
		if(empty($answer))
		{
			return false;
		}
		$answer = json_decode($answer, true);
		if(empty($answer["data"]))
		{
			return false;
		}
		$data = $answer["data"];
		if(empty($data) || ! is_array($data))
		{
			return false;
		}

		// приобразовываем данные для линейного графика
		$result = array(
			'data' => array(),
			'min'  => (isset ($answer["min"][0]) ? $answer["min"][0] : 0),
			'max'  => (isset ($answer["max"][0]) ? $answer["max"][0] : 0)
		);
		foreach($data as $item) {
			$result["data"][] = array(
				'categories' => (isset($item['dimensions'][0]['name']) ? $item['dimensions'][0]['name'] : ''),
				'value'     => (isset($item['metrics'][0]) ? $item['metrics'][0] : 0)
			);
		}

		return $result;
	}
}

/**
 * Visitors_yandex_exception
 *
 * Исключение для работы с API Яндекс.Метрики
 */
class Visitors_yandex_exception extends Exception{}
