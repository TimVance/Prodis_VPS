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

class Account_api extends Api
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
		$this->verify = false;

		$this->errors["wrong_param_method"] = "Неверно заданы параметры запроса.";
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
		// if(! $this->is_auth() || ! $this->user->id || ! $this->is_verify())
		// {
		// 	$this->set_error("wrong_token");
		// }
		// if($this->result())
		// {
		// 	return;
		// }

		$rewrite_array = array(); $count = 0;
		if(! empty($_GET["rewrite"]))
		{
			$rewrite_array = explode("/", $_GET["rewrite"]);
			$count = count($rewrite_array);
		}

		if($count)
		{
			switch($rewrite_array[0])
			{
				case 'core':
					$this->result["result"] = array('core' => Custom::version_core());
					break;

				case 'author':
					$this->result["result"] = array('author' => 'DIAFAN.CMS');
					break;

				case 'version':
					$this->result["result"] = array('version' => vsprintf('DIAFAN.CMS%s', ' '.Custom::version_core()));
					break;

				case 'link':
					$this->result["result"] = array('link' => 'http://www.diafan.ru/');
					break;

				default:
					$this->set_error("wrong_param_method");
					break;
			}
			if($this->result())
			{
				return;
			}
		}
		else $this->result = true;
	}
}
