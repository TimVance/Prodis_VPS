<?php
/**
 * Подключение модуля «Посещаемость» для работы с API Google Analytics
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
 * Visitors_inc_google
 */
class Visitors_inc_google extends Diafan
{
	const API_URI_AUTHORIZE = "https://accounts.google.com/o/oauth2/auth";
	const API_URI_TOKEN = "https://www.googleapis.com/oauth2/v3/token";
	const API_URI_STAT = "https://www.googleapis.com/analytics/v3/data/ga";

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
		self::$MAIL_LOGIN = $this->diafan->configmodules('google_mail_login', 'visitors');
		self::$CLIENT_ID = $this->diafan->configmodules('google_client_id', 'visitors');
		self::$CLIENT_PASSWORD = $this->diafan->configmodules('google_client_password', 'visitors');
		self::$COUNTER_ID = $this->diafan->configmodules('google_counter_id', 'visitors');
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
			if($token = $this->diafan->configmodules('google_token', 'visitors'))
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
			$params = http_build_query(
				array(
					"response_type" => "code",
					'redirect_uri'  => "http".(IS_HTTPS ? "s" : '')."://".MAIN_DOMAIN,
					"client_id"     => self::$CLIENT_ID,
					'scope'         => 'https://www.googleapis.com/auth/analytics',
					"login_hint"    => self::$MAIL_LOGIN,
					"access_type"   => 'offline',
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
			'client_secret' => self::$CLIENT_PASSWORD,
			'redirect_uri'  => "http".(IS_HTTPS ? "s" : '')."://".MAIN_DOMAIN,
			'scope'         => ''
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
		$this->diafan->configmodules('google_token', 'visitors', 0, 0, $answer["access_token"]);
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
			'ids'         => 'ga:'.self::$COUNTER_ID,
			'oauth_token' => $token,
			'metrics'     => 'ga:users,ga:pageviews,ga:newUsers',
			'dimensions'  => 'ga:date',
			'start-date'  => '30daysAgo',
			'end-date'    => 'today',
			'sort'        => 'ga:date',
		), false, false, ( REQUEST_GET | REQUEST_ANSWER ));
		if(empty($answer))
		{
			return false;
		}
		$answer = json_decode($answer, true);
		if(empty($answer["rows"]))
		{
			return false;
		}
		$data = $answer["rows"];
		if(empty($data) || ! is_array($data))
		{
			return false;
		}

		// приобразовываем данные для линейного графика
		$result = array();
		$values = array('visits' => array(), 'pageviews' => array(), 'users' => array());
		foreach($data as $key => $item) {
			$result["data"][$key] = array(
				'categories' => (isset($item[0]) ? date('Y-m-d', strtotime($item[0])) : ''),
				'visits'     => (isset($item[1]) ? $item[1] : 0),
				'pageviews'  => (isset($item[2]) ? $item[2] : 0),
				'users'      => (isset($item[3]) ? $item[3] : 0),
			);
			if($result["data"][$key]['visits'] != 0) $values['visits'][] = $result["data"][$key]['visits'];
			if($result["data"][$key]['pageviews'] != 0)  $values['pageviews'][] = $result["data"][$key]['pageviews'];
			if($result["data"][$key]['users'] != 0)  $values['users'][] = $result["data"][$key]['users'];
		}
		$result['min'] = array(
			'visits'    => (! empty($values['visits']) ? min($values['visits']) : 0),
			'pageviews' => (! empty($values['pageviews']) ? min($values['pageviews']) : 0),
			'users'     => (! empty($values['users']) ? min($values['users']) : 0)
		);
		$result['max'] = array(
			'visits'    => (! empty($values['visits']) ? max($values['visits']) : 0),
			'pageviews' => (! empty($values['pageviews']) ? max($values['pageviews']) : 0),
			'users'     => (! empty($values['users']) ? max($values['users']) : 0)
		);

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
			'ids'         => 'ga:'.self::$COUNTER_ID,
			'oauth_token' => $token,
			'metrics'     => 'ga:users',
			'dimensions'  => 'ga:source',
			//'filters'     => 'ga:users>10',
			'start-date'       => '30daysAgo',
			'end-date'       => 'today',
		), false, false, ( REQUEST_GET | REQUEST_ANSWER ));
		if(empty($answer))
		{
			return false;
		}
		$answer = json_decode($answer, true);
		if(empty($answer["rows"]))
		{
			return false;
		}
		$data = $answer["rows"];
		if(empty($data) || ! is_array($data))
		{
			return false;
		}

		// приобразовываем данные для линейного графика
		$result = $values = array();
		foreach($data as $key => $item) {
			$result["data"][$key] = array(
				'categories' => (isset($item[0]) ? $item[0] : ''),
				'value'      => (isset($item[1]) ? $item[1] : 0)
			);
			if($result["data"][$key]['value'] != 0) $values[] = $result["data"][$key]['value'];
		}
		$result['min'] = ! empty($values) ? min($values) : 0;
		$result['max'] = ! empty($values) ? max($values) : 0;
		$summ = array_sum($values);
		foreach($data as $key => $item) {
			if(empty($result["data"][$key]["value"])) continue;
			$result["data"][$key]["value"] = ($result["data"][$key]["value"] * 100) / $summ;
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
			'ids'         => 'ga:'.self::$COUNTER_ID,
			'oauth_token' => $token,
			'metrics'     => 'ga:bounceRate',
			'dimensions'  => 'ga:date',
			'start-date'  => '30daysAgo',
			'end-date'    => 'today',
			'sort'        => 'ga:date',
		), false, false, ( REQUEST_GET | REQUEST_ANSWER ));
		if(empty($answer))
		{
			return false;
		}
		$answer = json_decode($answer, true);
		if(empty($answer["rows"]))
		{
			return false;
		}
		$data = $answer["rows"];
		if(empty($data) || ! is_array($data))
		{
			return false;
		}

		// приобразовываем данные для линейного графика
		$result = $values = array();
		foreach($data as $key => $item) {
			$result["data"][$key] = array(
				'categories' => (isset($item[0]) ? date('Y-m-d', strtotime($item[0])) : ''),
				'value'      => (isset($item[1]) ? $item[1] : 0)
			);
			if($result["data"][$key]['value'] != 0) $values[] = $result["data"][$key]['value'];
		}
		$result['min'] = ! empty($values) ? min($values) : 0;
		$result['max'] = ! empty($values) ? max($values) : 0;

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
			'ids'         => 'ga:'.self::$COUNTER_ID,
			'oauth_token' => $token,
			'metrics'     => 'ga:avgSessionDuration',
			'dimensions'  => 'ga:date',
			'start-date'  => '30daysAgo',
			'end-date'    => 'today',
			'sort'        => 'ga:date',
		), false, false, ( REQUEST_GET | REQUEST_ANSWER ));
		if(empty($answer))
		{
			return false;
		}
		$answer = json_decode($answer, true);
		if(empty($answer["rows"]))
		{
			return false;
		}
		$data = $answer["rows"];
		if(empty($data) || ! is_array($data))
		{
			return false;
		}

		// приобразовываем данные для линейного графика
		$result = $values = array();
		foreach($data as $key => $item) {
			$result["data"][$key] = array(
				'categories' => (isset($item[0]) ? date('Y-m-d', strtotime($item[0])) : ''),
				'value'      => (isset($item[1]) ? $item[1] : 0)
			);
			if($result["data"][$key]['value'] != 0) $values[] = $result["data"][$key]['value'];
		}
		$result['min'] = ! empty($values) ? min($values) : 0;
		$result['max'] = ! empty($values) ? max($values) : 0;

		return $result;
	}
}

/**
 * Visitors_google_exception
 *
 * Исключение для работы с API Яндекс.Метрики
 */
class Visitors_google_exception extends Exception{}
