<?php
/**
 * Обработка запроса при регистрации пользователя
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

class Registration_action extends Action
{
	/*
	 * Добавляет нового пользователя
	 * 
	 * @return void
	 */
	public function add()
	{
		if ($this->diafan->_captcha->configmodules('users'))
		{
			$this->check_captcha();
		}

		$role_id = $this->get_role_id();
		$where_param_role_rel = $this->get_where_param_role_rel($role_id);

		$params = $this->model->get_params(array("module" => "users", "where" => "show_in_form_no_auth='1'".$where_param_role_rel));

		$this->check_fields();
		$this->empty_required_field(array("params" => $params));

		if ($this->result())
			return;

		if($this->diafan->configmodules("mail_as_login", "users"))
		{
			list($_POST["name"],) = explode('@', $_POST["mail"]);
			while(DB::query_result("SELECT name FROM {users} WHERE name='%h' AND trash='0'",  $_POST["name"]))
			{
				$_POST["name"] = $_POST["name"].mt_rand(1, 99999);
			}
		}

		if($_POST["phone"])
		{
			$phone = preg_replace('/[^0-9]+/', '', $_POST["phone"]);			
		}

		$save_id = DB::query("INSERT INTO {users} (name, password, mail, phone, created, lang_id, fio, act, role_id)"
			. " VALUES ('%h', '%h', '%h', '%h', %d, %d, '%h', '%d', %d)",
			$_POST["name"], encrypt($_POST["password"]), $_POST["mail"], (! empty($_POST["phone"]) ? $phone : ''), time(),
			_LANG, $_POST["fio"], $this->diafan->configmodules("act", "users") ? 0 : 1,
			$role_id
		);

		$this->insert_values(array("id" => $save_id, "table" => "users", "params" => $params));

		if(! empty($_POST["tmpcode"]))
		{
			DB::query("UPDATE {images} SET element_id=%d, tmpcode='' WHERE module_name='users' AND element_id=0 AND tmpcode='%s'", $save_id, $_POST["tmpcode"]);
		}

		if ($this->result())
			return;
		
		if(in_array('subscription', $this->diafan->installed_modules))
		{
			if(! empty($_POST['subscribe']) || ! $this->diafan->configmodules('subscribe_in_registration', 'subscription'))
			{
				$email_id = DB::query_result("SELECT id FROM {subscription_emails} WHERE mail='%s' LIMIT 1", $_POST['mail']);
				if($email_id)
				{
					DB::query("UPDATE {subscription_emails} SET act='1', trash='0' WHERE id=%d LIMIT 1", $email_id);
				}
				else
				{
					$code = md5(rand(111, 99999));
					DB::query("INSERT INTO {subscription_emails} (created, mail, name, code, act) VALUES (%d, '%s', '%h', '%s', '1')", time(), $_POST['mail'], $_POST["fio"], $code);
				}
			}
			if($_POST["phone"])
			{
				$phone = preg_replace('/[^0-9]+/', '', $_POST["phone"]);
				if(! DB::query_result("SELECT id FROM {subscription_phones} WHERE phone='%s' AND trash='0'", $phone))
				{
					DB::query("INSERT INTO {subscription_phones} (phone, created, name, act) VALUES ('%s', %d, '%h', '1')", $phone, time(), $_POST["fio"]);
				}
			}
		}

		$this->send_mails($save_id);

		$this->upload_avatar();

		if (! $this->diafan->configmodules("act", "users"))
		{
			$this->diafan->_users->id = $save_id;
			if ($_POST["url"])
			{
				$this->result["redirect"] = $_POST["url"];
				return;
			}
		}
		$this->result["data"] = array(".registration_message" => $this->diafan->configmodules('mes', "users"));
		
		if($this->diafan->configmodules("hide_register_form", "users"))
		{
			$this->result["data"]["form"] = false;
		}

		$this->result["result"] = 'success';
	}

	/*
	 * Авторизация
	 * 
	 * @return void
	 */
	public function auth()
	{
		$result = $this->diafan->_users->auth($_POST);
		if($result["result"])
		{
			$this->result["result"] = 'success';
			$this->result["redirect"] = $result["redirect"];
		}
		else
		{
			$this->result["errors"][0] = $result["error"];
		}
	}

	/**
	 * Валидация данных "на лету"
	 * 
	 * @return void
	 */
	public function fast_validate()
	{
		Custom::inc('includes/validate.php');
		switch($_POST["name"])
		{
			case "name":
				if(! $this->diafan->configmodules("mail_as_login", "users") && $mes = Validate::login($_POST["value"]))
				{
					$this->result["data"] = $this->diafan->_($mes, false);
				}
				break;

			case "mail":
				if(! $mes = Validate::mail($_POST["value"]))
				{
					$mes = Validate::mail_user($_POST["value"]);
				}
				if($mes)
				{
					$this->result["data"] = $this->diafan->_($mes, false);
				}
				break;

			case "password":
				if($mes = Validate::password($_POST["value"], true))
				{
					$this->result["data"] = $this->diafan->_($mes, false);
				}
				break;

			case "password2":
				if($_POST["value"] != $_POST["value2"])
				{
					$this->result["data"] = $this->diafan->_('Пароли не совпадают', false);
				}
				break;

			case "fio":
				if (! $_POST["value"])
				{
					$this->result["data"] = $this->diafan->_('Заполните поле ФИО или название компании', false);
				}
				break;
		}
		$this->result["result"] = 'success';
	}

	/**
	 * Загружает изображение
	 *
	 * @return void
	 */
	public function upload_image()
	{
		if(empty($_POST["tmpcode"]))
		{
			return;
		}
		$prefix = '';
		if(! empty($_POST["images_prefix"]))
		{
			$prefix = $this->diafan->filter($_POST, "string", "images_prefix");
		}
		if(empty($_POST["images_param_id"]))
		{
			return;
		}
		$param_id = $this->diafan->filter($_POST, "int", "images_param_id");
		$this->result["result"] = 'success';
		if (! empty($_FILES[$prefix.'images'.$param_id]) && $_FILES[$prefix.'images'.$param_id]['tmp_name'] != '' && $_FILES[$prefix.'images'.$param_id]['name'] != '')
		{
			try
			{
				$this->diafan->_images->upload(0, "users", 'element', 0, $_FILES[$prefix.'images'.$param_id]['tmp_name'], $this->diafan->translit($_FILES[$prefix.'images'.$param_id]['name']), false, $param_id, $_POST["tmpcode"]);
			}
			catch(Exception $e)
			{
				Dev::$exception_field = $prefix.'p'.$param_id;
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}
			$images = $this->diafan->_images->get('large', 0, "users", 'element', 0, '', $param_id, 0, '', $_POST["tmpcode"]);
			$this->result["data"] = $this->diafan->_tpl->get('images', "users", $images);
		}
	}

	/**
	 * Удаляет изображение
	 *
	 * @return void
	 */
	public function delete_image()
	{
		if(empty($_POST["id"]))
		{
			return;
		}
		if(empty($_POST["tmpcode"]))
		{
			return;
		}
		$row = DB::query_fetch_array("SELECT * FROM {images} WHERE module_name='users' AND id=%d AND tmpcode='%s'", $_POST["id"], $_POST["tmpcode"]);
		if(! $row)
		{
			return;
		}
		$this->diafan->_images->delete_row($row);
	}

	/**
	 * Проверяет валидность введенных при регистрации данных
	 * 
	 * @return void
	 */
	private function check_fields()
	{
		Custom::inc('includes/validate.php');
		if(! $this->diafan->configmodules("mail_as_login", "users"))
		{
			$mes = Validate::login($_POST["name"]);
			if ($mes)
			{
				$this->result["errors"]["name"] = $this->diafan->_($mes, false);
			}
		}
		$mes = Validate::mail($_POST["mail"]);
		if ($mes)
		{
			$this->result["errors"]["mail"] = $this->diafan->_($mes, false);
		}
		else
		{
			$mes = Validate::mail_user($_POST["mail"]);
			if ($mes)
			{
				$this->result["errors"]["mail"] = $this->diafan->_($mes, false);
			}
		}
		if (! empty($_POST["phone"]))
		{
			$mes = Validate::phone($_POST["phone"]);
			if ($mes)
			{
				$this->result["errors"]["phone"] = $this->diafan->_($mes, false);
			}
		}
		$mes = Validate::password($_POST["password"]);
		if ($mes)
		{
			$this->result["errors"]["password"] = $this->diafan->_($mes, false);
		}
		elseif ($_POST["password"] != $_POST["password2"])
		{
			$this->result["errors"]["password2"] = $this->diafan->_('Пароли не совпадают', false);
		}

		if (!$_POST["fio"])
		{
			$this->result["errors"]["fio"] = $this->diafan->_('Заполните поле ФИО или название компании', false);
		}
	}

	/**
	 * Проверяет валидность заполнения роли пользователя, определяет роль для нового пользователя
	 * 
	 * @return boolean
	 */
	private function get_role_id()
	{
		$roles = DB::query_fetch_value("SELECT id FROM {users_role} WHERE registration='1' AND trash='0'", "id");
		if(! count($roles))
		{
			return 0;
		}
		if(count($roles) == 1)
		{
			return $roles[0];
		}

		if (empty($_POST["role_id"]) || !in_array($_POST["role_id"], $roles))
		{
			$this->result["errors"]["role_id"] = 'ERROR_ROLE_ID';
		}

		return $_POST["role_id"];
	}

	/**
	 * Получает условие для SQL-запроса: выбор полей с учетом роли пользователя
	 *
	 * @param integer $role_id номер роли пользователя
	 * @return string
	 */
	private function get_where_param_role_rel($role_id)
	{
		$param_ids = array();
		$param_role_rels = array();
		$rows = DB::query_fetch_all("SELECT role_id, element_id FROM {users_param_role_rel} WHERE trash='0' AND role_id>0");
		foreach ($rows as $row)
		{
			$param_role_rels[$row["element_id"]][] = $row["role_id"];
		}
		foreach ($param_role_rels as $param_id => $roles)
		{
			if(! in_array($role_id, $roles))
			{
				$param_ids[] = $param_id;
			}
		}
		if($param_ids)
		{
			return " AND id NOT IN (".implode(",", $param_ids).")";
		}
		return '';
	}

	/**
	 * Отправляет письмо новому пользователю и администратору сайта
	 * 
	 * @return void
	 */
	private function send_mails($save)
	{
		if($this->diafan->configmodules("mail_as_login", "users"))
		{
			$login = $this->diafan->filter($_POST, "string", "mail");
		}
		else
		{
			$login = $this->diafan->filter($_POST, "string", "name");
		}
		if ($this->diafan->configmodules("sendmailadmin", "users"))
		{
			$subject = str_replace(
					array('%title', '%url'), array(TITLE, BASE_URL), $this->diafan->configmodules('subject_admin', "users")
			);
			$message = str_replace(
					array('%login', '%title', '%url', '%fio', '%email', '%params'), array(
						$login,
						TITLE,
						BASE_URL,
						$this->diafan->filter($_POST, "string", "fio"),
						$this->diafan->filter($_POST, "string", "mail"),
						$this->message_admin_param,
					), $this->diafan->configmodules('message_admin', "users")
			);

			if ($message && $subject)
			{
				$this->diafan->_postman->message_add_mail(
						$this->diafan->configmodules("emailconfadmin", "users") ? $this->diafan->configmodules("email_admin", "users") : EMAIL_CONFIG,
						$subject,
						$message,
						$this->diafan->configmodules("emailconf", "users") ? $this->diafan->configmodules("email", "users") : EMAIL_CONFIG
				);
			}
		}

		//send mail user
		$subject = str_replace(
			array('%title', '%url'),
			array(TITLE, BASE_URL),
			$this->diafan->configmodules('subject', "users")
		);

		$actlink = '';
		if ($this->diafan->configmodules("act", "users") == 1)
		{
			$actcode = md5(rand(111, 99999));
			DB::query("INSERT INTO {users_actlink} (link, user_id, created) VALUES ('%s', %d, %d)", $actcode, $save, time() + 86400);
			$actlink = BASE_PATH_HREF.$this->diafan->_route->link($this->diafan->_site->id).'?action=act&user_id='.$save.'&code='.$actcode;
		}

		$message = str_replace(
				array('%login', '%title', '%url', '%fio', '%email', '%password', '%params', '%actlink'), array(
					$login,
					TITLE,
					BASE_URL,
					$this->diafan->filter($_POST, "string", "fio"),
					$this->diafan->filter($_POST, "string", "mail"),
					$this->diafan->filter($_POST, "string", "password"),
					$this->message_param,
					$actlink
				), $this->diafan->configmodules('message', "users")
		);
		if ($message && $subject)
		{
			$this->diafan->_postman->message_add_mail(
					$_POST["mail"],
					$subject,
					$message,
					$this->diafan->configmodules("emailconf", "users") ? $this->diafan->configmodules("email", "users") : EMAIL_CONFIG
			);
		}
	}

	/**
	 * Загружает аватар
	 * 
	 * @return void
	 */
	private function upload_avatar()
	{
		if (isset($_FILES["avatar"]) && is_array($_FILES["avatar"]) && $_FILES["avatar"]['name'] != '')
		{
			try
			{
				$this->diafan->_users->create_avatar($_POST["name"], $_FILES["avatar"]['tmp_name']);
			}
			catch(Exception $e)
			{
				Dev::$exception_field = "avatar";
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}
		}
	}

}