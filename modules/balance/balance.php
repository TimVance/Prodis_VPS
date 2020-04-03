<?php
/**
 * Контроллер модуля «Баланс пользователя»
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
 * Balance
 */
class Balance extends Controller
{
	/**
	 * @var array переменные, передаваемые в URL страницы
	 */
	public $rewrite_variable_names = array('step', 'show');

	/**
	 * Инициализация модуля
	 * 
	 * @return void
	 */
	public function init()
	{
		if (($this->diafan->configmodules('security_user', 'shop') && ! $this->diafan->_users->id))
			return false;
		
		if (empty($this->diafan->_route->step))
		{
			$this->model->form();
		}
		// платежная система
		elseif ($this->diafan->_route->step == 2)
		{			
			$this->model->payment();			
		}
		elseif ($this->diafan->_route->step == 3 || $this->diafan->_route->step == 4)
		{			
			$this->model->result();		
		}	
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
				case 'recharge':
					return $this->action->recharge();					
			}
		}
	}
}