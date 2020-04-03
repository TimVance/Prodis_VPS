<?php
/**
 * Подключение модуля к административной части других модулей
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
 * Account_admin_inc
 */
class Account_admin_inc extends Diafan
{
	/**
   * @var string доменное имя для API
   */
	public $api_domain = 'user.diafan.ru';

	/**
   * @var integer тип авторизации по умолчанию
   */
	public $auth_type = 1;

	/**
	 * @var string путь до временной директории относительно корня сайта
	 */
	public $dir_path = 'tmp/account';

	/**
	 * @var string электронный ключ
	 */
	public $token;

	/**
	 * @var string допустимая версия API
	 */
	public $v = '1';

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
		$this->token = $this->diafan->configmodules("token", "account");
		File::create_dir($this->dir_path, true);
		$this->diafan->_client->set_valid_version($this->api_domain, $this->v);
	}

	/**
	 * Проверяет авторизацию API
	 *
	 * @param integer $token электронный ключ
	 * @return boolean
	 */
	public function is_auth($token = null)
	{
		$token = ! is_null($token) ? $token : $this->token;
		if(! $token)
		{
			return false;
		}
		if(isset($this->cache["token"][$token]))
		{
			return !! $this->cache["token"][$token];
		}
		$answer = $this->diafan->_client->token($this->api_domain, $token);
		$this->cache["token"][$token] = !! $answer;
		if(! $this->cache["token"][$token])
		{
			return false;
		}
		$this->cache["token"][$token] = (! empty($answer["enable"]) && $answer["enable"] == 'on');
		return !! $this->cache["token"][$token];
	}

	/**
	 * Возвращает URI API
	 *
	 * @param string $module имя модуля
	 * @param string $method имя метода
	 * @param integer $page номер страницы
	 * @param string $urlpage шаблон части ссылки, отвечающей за передачу номера страницы
	 * @return array
	 */
	public function uri($module, $method, $page = false, $urlpage = 'page%d/')
	{
		return $this->diafan->_client->uri($this->api_domain, $module, $method, $page, $urlpage);
	}
}

/**
 * Account_admin_inc_exception
 *
 * Исключение для подключений модуля к административной части других модулей
 */
class Account_admin_inc_exception extends Exception{}
