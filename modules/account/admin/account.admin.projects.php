<?php
/**
 * Редактирование модуля
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
 * Account_admin_projects
 */
class Account_admin_projects extends Frame_admin
{
  /**
	 * @var object вспомогательный объект модуля
	 */
	private $account = null;

  /**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
    Custom::inc('modules/account/admin/account.admin.inc.php');
		$this->account = new Account_admin_inc($this->diafan);
	}

  /**
	 * Выводит содержание "Персональная страница"
   *
	 * @return void
	 */
	public function show()
	{
    if(! $this->account->is_auth())
    {
      $this->diafan->redirect(BASE_PATH.ADMIN_FOLDER.'/'.$this->diafan->_admin->module.'/');
    }
    $this->diafan->redirect('http://pro.user.diafan.ru/');
	}
}
