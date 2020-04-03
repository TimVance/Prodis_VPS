<?php
/**
 * Контроллер модуля «Восстановление пароля»
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
 * Reminding
 */
class Reminding extends Controller
{
	/**
	 * Инициализация модуля
	 * 
	 * @return void
	 */
	public function init()
	{
		if(! empty($_GET["action"]))
		{
			switch($_GET["action"])
			{
				case "change_password":
					$this->model->form_change_password();
					break;

				case "success":
					$this->model->success();
					break;

				default:
					Custom::inc('includes/404.php');
			}
		}
		else
		{
			$this->model->form_mail();
		}
	}

	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function action()
	{
		if ($this->diafan->_site->module != 'reminding' || $this->diafan->_users->id)
		{
			return;
		}

		if(! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'mail':
					return $this->action->mail();
	
				case 'change_password':
					return $this->action->change_password();
			}
		}
	}
}