<?php
/**
 * Контроллер модуля «Настройки аккаунта»
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
 * Usersettings
 */
class Usersettings extends Controller
{
	/**
	 * Инициализация модуля
	 * 
	 * @return void
	 */
	public function init()
	{
		if(! $this->diafan->_users->id)
		{
			Custom::inc('includes/403.php');
		}
		$this->result = $this->model->form();
	}

	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function action()
	{
		if(! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'edit':
					return $this->action->edit();
	
				case 'delete_avatar':
					return $this->action->delete_avatar();
	
				case 'upload_image':
					return $this->action->upload_image();
	
				case 'delete_image':
					return $this->action->delete_image();
			}
		}
	}	
}