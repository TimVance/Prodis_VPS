<?php
/**
 * Обрабатывает полученные данные из формы
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

class Registration_api extends Api
{
	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
	}

	/**
	 * Определение свойств класса
	 *
	 * @return void
	 */
	public function variables()
	{
		$this->errors["no_name"] = "Не задан ключ name.";
		$this->errors["no_pass"] = "Не задан ключ pass.";
		$this->errors["blocked"] = "Логин не активирован или заблокирован.";
		$this->errors["blocked_30_min"] = "Вы превысили количество попыток, поэтому будете заблокированы на 30 минут.";
		$this->errors["wrong_login_or_pass"] = "Неверный логин или пароль.";
		$this->errors["wrong_user"] = "Пользователь не определен.";
	}

	/**
	 * Инициализация API модуля
	 *
	 * @return void
	 */
	public function init()
	{
		switch($this->method)
		{
			case 'auth_code':
				$this->auth_code();
				break;

			case 'auth_code_revoke':
				$this->auth_code_revoke();
				break;

			case 'auth_code_info':
				$this->auth_code_info();
				break;

			default:
				$this->set_error("method_unknown");
				break;
		}
	}

	/**
	 * Возвращает новый электронный ключ текущему пользователю
	 *
	 * @return void
	 */
	public function auth_code()
	{
		if(empty($_POST["name"]))
		{
			$this->set_error("no_name");
		}
		if(empty($_POST["pass"]))
		{
			$this->set_error("no_pass");
		}
		if($this->result())
		{
			return;
		}

		$user_id = $this->diafan->_users->auth_api($_POST["name"], $_POST["pass"]);
		if(is_string($user_id))
		{
			$this->set_error($user_id);
		}
		elseif(! is_integer($user_id) || ! $token = $this->auth($user_id))
		{
			$this->set_error("wrong_user");
		}
		if($this->result())
		{
			return;
		}

		$this->result["result"]["token"] = $token;
	}

	/**
	 * Отзывает электронный ключ у текущего пользователя
	 *
	 * @return void
	 */
	public function auth_code_revoke()
	{
		if(! $this->is_auth() || ! $this->revoke())
		{
			$this->set_error("wrong_token");
		}
		if($this->result())
		{
			return;
		}
		$this->result["result"] = true;
	}

	/**
	 * Возвращает информацию об электронном ключе текущего пользователя
	 *
	 * @return void
	 */
	public function auth_code_info()
	{
		$token = $this->token_info();
		if(! $token || ! is_array($token))
		{
			$this->set_error("wrong_token");
			$this->result["result"] = array("token" => $this->token);
		}
		if($this->result())
		{
			return;
		}
		$this->result["result"] = array(
			"date" => $token["date"],
			"token" => $this->token,
			"date_start" => $token["token_date_start"],
			"date_finish" => $token["token_date_finish"],
			"enable" => ($this->is_auth() ? "on" : "off"),
		);
	}
}
