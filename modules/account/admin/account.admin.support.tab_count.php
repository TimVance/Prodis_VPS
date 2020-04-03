<?php
/**
 * Количество непрочитанных уведомлений службы поддержки для меню административной панели
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
 * Account_admin_support_tab_count
 */
class Account_admin_support_tab_count extends Diafan
{
	/**
	 * @var object вспомогательный объект модуля
	 */
	private $account = null;

	/**
	 * @var integer метка времени
	 */
	static private $count = 0;

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
	 * Возвращает количество непрочитанных уведомлений службы поддержки для меню административной панели
	 *
	 * @return integer
	 */
	public function count()
	{
		if(self::$count)
		{
			return self::$count;
		}

		self::$count = 0;

		if(! $this->account->is_auth())
    {
      return self::$count;
    }
    $url = $this->account->uri('support', 'count');
		if(! $result = $this->diafan->_client->request($url, $this->account->token))
    {
      return self::$count;
    }
    $this->diafan->_client->get_attributes($result, 'count');

		self::$count += $this->diafan->filter($result, 'integer', 'count');

		return self::$count;
	}
}
