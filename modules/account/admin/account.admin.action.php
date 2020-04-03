<?php
/**
 * Обработка POST-запросов в административной части модуля
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
 * Account_admin_action
 */
class Account_admin_action extends Action_admin
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
	 * Вызывает обработку Ajax-запросов
	 *
	 * @return void
	 */
	public function init()
	{
		if (! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'warn_read':
					$this->warn_read();
					break;

				case 'close':
					$this->close();
					break;
			}
		}
	}

	/**
	 * Отметка о прочтении сообщения "Предупреждение"
	 *
	 * @return void
	 */
	private function warn_read()
	{
		$this->diafan->get_attributes($_POST, 'checked');
		$this->result["result"] = 'success';

		if(! $this->account->is_auth())
    {
      return;
    }
    $url = $this->account->uri('support', 'warn_read');
		$param = array(
			'checked' => !empty($_POST["checked"]) ? '1' : '',
		);
    $result = $this->diafan->_client->request($url, $this->account->token, $param);

		return;
	}

	/**
	 * Закрывается тикет
	 *
	 * @return void
	 */
	private function close()
	{
		$this->diafan->get_attributes($_POST, 'id');
		$this->result["result"] = 'success';

		if(! $this->account->is_auth())
    {
      return;
    }
    $url = $this->account->uri('support', 'close');
		$param = array(
			'id' => $this->diafan->filter($_POST, "integer", "id"),
		);
		$result = $this->diafan->_client->request($url, $this->account->token, $param);

		return;
	}
}
