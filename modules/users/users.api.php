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

class Users_api extends Api
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
	 * Инициализация API модуля
	 *
	 * @return void
	 */
	public function init()
	{
		switch($this->method)
		{
			case 'info':
				$this->info();
				break;

			default:
				$this->set_error("method_unknown");
				break;
		}
	}

	/**
	 * Возвращает информацию о текущем пользователе
	 *
	 * @return void
	 */
	private function info()
	{
		if(! $this->is_auth() || ! $this->user->id)
		{
			$this->set_error("wrong_token");
		}
		if($this->result())
		{
			return;
		}

		$this->result["result"] = array(
			"name" => $this->user->name,
			"fio" => $this->user->fio,
			"mail" => $this->user->mail,
			"avatar" => $this->user->avatar,
			"created" => $this->user->created,
		);
	}
}
