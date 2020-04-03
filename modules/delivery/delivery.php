<?php
/**
 * Контроллер
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

class Delivery extends Controller
{
	/**
	 * Обработка POST-запросов
	 */
	public function action()
	{
		if (! empty($_POST["action"]) && ! empty($_POST['backend']))
		{
			$backend = preg_replace('/[^a-z0-9\_]+/', '', $_POST['backend']);
			$action = preg_replace('/[^a-z0-9\_]+/', '', $_POST["action"]);
			$path = 'modules/delivery/backend/'.$backend.'/delivery.'.$backend.'.action.php';

			if (Custom::exists($path))
			{
				Custom::inc($path);
				$name_class_action = 'Delivery_'.$backend.'_action';
				$class = new $name_class_action($this->diafan);
				if (is_callable(array($class, $action)))
				{
					call_user_func_array (array(&$class, $action), array());
					$this->action->result = $class->result;
				}
			}
		}
	}
}