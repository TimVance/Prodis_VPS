<?php
/**
 * Подключение модуля к административной части других модулей
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
 * Users_admin_inc
 */
class Users_admin_inc extends Diafan
{
	/**
	 * Блокирует/разблокирует пользователей
	 * 
	 * @param string $table таблица
	 * @param array $element_ids номера элементов
	 * @param integer $act блокировать/разблокировать
	 * @return void
	 */
	public function act($table, $element_ids, $act)
	{
		// если блокирует/разблокирует скидки не через форму, пересчитывает цены
		if ($table == "users" && $act)
		{
			//send mail user
			$subject = str_replace(
				array('%title', '%url'),
				array(TITLE, BASE_URL),
				$this->diafan->configmodules('subject_act', "users")
			);

			$rows = DB::query_fetch_all("SELECT * FROM {users} WHERE id IN (%s)", implode(',', $element_ids));
			foreach ($rows as $row)
			{
				if($this->diafan->configmodules("mail_as_login", "users"))
				{
					$login = $this->diafan->filter($row["mail"]);
				}
				else
				{
					$login = $this->diafan->filter($row["name"]);
				}
		
				$message = str_replace(
						array('%login', '%title', '%url', '%fio', '%email'), array(
							$login,
							TITLE,
							BASE_URL,
							$row["fio"],
							$row["mail"],
						), $this->diafan->configmodules('message_act', "users")
				);
				if ($message && $subject)
				{
					$this->diafan->_postman->message_add_mail(
						$row["mail"],
						$subject,
						$message,
						$this->diafan->configmodules("emailconf", "users") ? $this->diafan->configmodules("email", "users") : EMAIL_CONFIG
					);
				}
			}
		}
	}
}