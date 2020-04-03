<?php
/**
 * Обработка запроса при отправке данных из формы восстановления пароля
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

class Reminding_action extends Action
{
	/**
	 * Отправляет письмо пользователю со ссылкой на форму изменения пароля
	 * 
	 * @return void
	 */
	public function mail()
	{
		if ($this->diafan->_captcha->configmodules('users'))
		{
			$this->check_captcha();
		}
		$this->check_fields_mail();

		if ($this->result())
			return;

		$this->check_log();

		if ($this->result())
			return;

		if (! $row = DB::query_fetch_array("SELECT id, name, fio, mail, act FROM {users} WHERE mail='%h' AND trash='0' LIMIT 1", $_POST["mail"]))
		{
			if(! $row)
			{
				$this->result["errors"][0] = $this->diafan->_('Извините, вы ошиблись. Проверьте вводимые данные и попробуйте еще раз.', false);
				return;
			}
		}

		// если аккаунт не активирован и нет активации по ссылке, то отдаем ошибку
		if (! $row["act"])
		{
			$this->result["errors"][0] = $this->diafan->_('Пользователь заблокирован.', false);
			return;
		}

		$actcode = $this->gen_code();
		DB::query("INSERT INTO {users_actlink} (link, user_id, created) VALUES ('%s', %d, %d)", $actcode, $row["id"], time() + 86400);
		$actlink = BASE_PATH_HREF.$this->diafan->_site->rewrite.($_GET["rewrite"] == 'admin_reminding' ? '/' : ROUTE_END).'?action=change_password&user_id='.$row["id"].'&code='.$actcode;

		//send mail user
		$subject = str_replace(
				array('%title', '%url'),
				array(TITLE, BASE_URL),
				$this->diafan->configmodules('subject_reminding', "users")
			);

		$message = str_replace(
			array('%title', '%url', '%fio', '%actlink'),
			array(
				TITLE,
				BASE_URL,
				$row["fio"],
				$actlink
			),
			$this->diafan->configmodules('message_reminding', "users")
		);

		$this->diafan->_postman->message_add_mail(
				$row["mail"],
				$subject,
				$message,
				$this->diafan->configmodules("emailconf", "users") ? $this->diafan->configmodules("email", "users") : EMAIL_CONFIG
			);

		$mes = $this->diafan->configmodules('mes_reminding', 'users');
		$this->result["data"] = array(
			'form' => false,
			'.reminding_result' => $mes
		);
	}

	/**
	 * Генерирует код
	 * 
	 * @return string
	 */
	private function gen_code()
	{
		$leight = mt_rand(10, 255);

		$x = '';

		$str = "absmlfpwmcuskf;etwnxsh3435208sqwertyuiopasdfghjklzxcvbnm123456789";

		for($i = 0; $i < $leight; $i++)
		{
			$x .= substr($str, mt_rand(0, strlen($str) - 1), 1);
		}
		return $x;
	}

	/**
	 * Проверяет заполнены ли поля для запроса ссылки
	 * 
	 * @return void
	 */
	private function check_fields_mail()
	{
		if (! $_POST["mail"])
		{
			$this->result["errors"]["mail"] = $this->diafan->_('Введите электронный ящик', false);
		}
	}

	/**
	 * Проверяет попытку подбора логина
	 * 
	 * @return void
	 */
	private function check_log()
	{
		DB::query("DELETE FROM {log} WHERE created<%d", time());

		if (getenv('HTTP_X_FORWARDED_FOR'))
		{
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		else
		{
			$ip = getenv('REMOTE_ADDR');
		}
		$date   = time() + 1800;
		$row = DB::query_fetch_array("SELECT `count` FROM {log} WHERE ip='%h'", $ip); 
		if ($row)
		{
			if ($row['count'] > 5)
			{
				$this->result["errors"][0] = $this->diafan->_('Вы превысили количество попыток, поэтому будете заблокированы на 30 минут.', false);
			}
			else
			{
				DB::query("UPDATE {log} SET count=count+1, created=%d WHERE ip='%s'", $date, $ip);
			}
		}
		else
		{
			$info = getenv('HTTP_USER_AGENT');
			DB::query('INSERT INTO {log} (ip, created, info) VALUES ("'.$ip.'", "'.$date.'", "'.$info.'")');
		}
	}

	/**
	 * Меняет пароль
	 * 
	 * @return void
	 */
	public function change_password()
	{
		$this->check_fields_change_password();

		if ($this->result())
			return;

		$this->check_log();

		if ($this->result())
			return;

		$actlink = DB::query_fetch_array("SELECT id, user_id, created FROM {users_actlink} WHERE link='%h' AND user_id=%d AND `count`<5 LIMIT 1", $_POST["code"], $_POST["user_id"]);
		DB::query("DELETE FROM {users_actlink} WHERE user_id=%d", $actlink["user_id"]);

		$user = DB::query_fetch_array("SELECT id, name, fio, mail, act FROM {users} WHERE id=%d LIMIT 1", $_POST["user_id"]);
		if (! $actlink || ! $user)
		{
		    $this->result["errors"][0] = $this->diafan->_('Извините, вы не можете воспользоваться этой ссылкой.', false);
			return;
		}
		elseif($user["id"] && ! $user["act"])
		{
		    $this->result["errors"][0] = $this->diafan->_('Пользователь заблокирован.', false);
			return;
		}
		elseif ($actlink["created"] < time())
		{
		    $this->result["errors"][0] = $this->diafan->_('Извините, время действия ссылки закончилось.', false);
			return;
		}
		
		DB::query("UPDATE {users} SET password='%s' WHERE id=%d", encrypt($_POST["password"]), $user["id"]);

		//send mail user
		$subject = str_replace(
				array('%title', '%url'),
				array(TITLE, BASE_URL),
				$this->diafan->configmodules('subject_reminding_new_pass', "users")
			);

		if($this->diafan->configmodules("mail_as_login", "users"))
		{
			$login = $user["mail"];
		}
		else
		{
			$login = $user["name"];
		}
		$message = str_replace(
			array('%login', '%title', '%url', '%fio', '%password'),
			array(
				$login,
				TITLE,
				BASE_URL,
				$user["fio"],
				$this->diafan->filter($_POST, "string", "password")
			),
			$this->diafan->configmodules('message_reminding_new_pass', "users")
		);

		$this->diafan->_postman->message_add_mail(
				$user["mail"],
				$subject,
				$message,
				$this->diafan->configmodules("emailconf", "users") ? $this->diafan->configmodules("email", "users") : EMAIL_CONFIG
			);
		$this->diafan->_users->id = $user["id"];
		$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->current_link().'?action=success';
	}

	/**
	 * Проверяет заполнены ли поля для смены пароля
	 * 
	 * @return void
	 */
	private function check_fields_change_password()
	{
		Custom::inc('includes/validate.php');
		$mes = Validate::password($_POST["password"]);
		if ($mes)
		{
		    $this->result["errors"]["password"] = $this->diafan->_($mes);
		}
		elseif ($_POST["password"] != $_POST["password2"])
		{
		    $this->result["errors"]["password"] = $this->diafan->_('Пароли не совпадают.', false);
		}
		if(empty($_POST["code"]))
		{
		    $this->result["errors"][0] = 'ERROR_1';
		}
		if(empty($_POST["user_id"]))
		{
		    $this->result["errors"][0] = 'ERROR_2';
		}
	}
}