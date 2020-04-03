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
 * Postman_admin_action
 */
class Postman_admin_action extends Action_admin
{

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
				case 'send':
				case 'group_send':
					$this->group_option();
					break;
			}
		}
	}

	/**
	 * Групповая операция "Отправить уведомления"
	 *
	 * @return void
	 */
	private function group_option()
	{
		$ids = array();
		if(! empty($_POST["ids"]))
		{
			foreach ($_POST["ids"] as $id)
			{
				$id = $this->diafan->_db_ex->filter_uid($id);
				if($id)
				{
					$ids[] = $id;
				}
			}
		}
		elseif(! empty($_POST["id"]))
		{
			$ids = array($this->diafan->_db_ex->filter_uid($_POST["id"]));
		}
		if(! empty($ids))
		{
			switch ($_POST["action"])
			{
				case 'send':
				case 'group_send':
					$this->group_send($ids);
					break;
			}
		}
	}

	/**
	 * Групповая отправка уведомлений или отправка уведомления кнопкой управления
	 *
	 * @param array $ids идентификаторы
	 * @return void
	 */
	public function group_send($ids)
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->result["redirect"] = URL;
			return;
		}

		//проверка прав пользователя на редактирование модуля
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["redirect"] = URL;
			return;
		}

		if(! empty($ids))
		{
			foreach($ids as $id)
			{
				$id = $this->diafan->_db_ex->converter_id('{postman}', $id);
				if(false === $id)
				{
					continue;
				}
				$this->diafan->_postman->message_send($id);
			}
		}

		$this->result["redirect"] = URL.$this->diafan->get_nav;
	}
}
