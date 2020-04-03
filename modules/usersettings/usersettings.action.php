<?php
/**
 * Обработка запроса при изменении данных о пользователе
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

class Usersettings_action extends Action
{
	/**
	* @var string модуль
	*/
	protected $module = 'users';

	/*
	 * Редактирует данные пользователя
	 * 
	 * @return void
	 */
	public function edit()
	{
		if ($this->diafan->_site->module != 'usersettings')
			return;

		$this->check_user();

		if ($this->result())
			return;

		if (! $this->diafan->_users->checked)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		$this->result["hash"] = $this->diafan->_users->get_hash();

		$this->check_fields();
		
		$role_id = 0;
		if(! empty($_POST["role_id"]))
		{
			$role_id = DB::query_result("SELECT id FROM {users_role} WHERE registration='1' AND trash='0' AND id=%d", $_POST["role_id"]);
		}
		if(! $role_id)
		{
			$role_id = $this->diafan->_users->role_id;
		}
		$where_param_role_rel = $this->get_where_param_role_rel($role_id);
		$params = $this->model->get_params(array("module" => "users", "where" => "show_in_form_auth='1'".$where_param_role_rel));
		$this->empty_required_field(array("params" => $params));

		if($this->diafan->_route->id_module('cart'))
		{
			$order_params = $this->model->get_params(array("module" => "shop", "table" => "shop_order", "where" => "show_in_form_register='1'"));
			//$this->empty_required_field(array("params" => $order_params, "prefix" => "dop_"));
		}

		if ($this->result())
			return;

		if(! empty($_POST["lang_id"]) && !DB::query_result("SELECT id FROM {languages} WHERE id=%d LIMIT 1", $_POST["lang_id"]))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}

		$other_params = $this->model->get_params(array("module" => "users", "where" => "show_in_form_auth='0'".$where_param_role_rel));
		$no_empty_param_ids = array();
		foreach($other_params as $other_param)
		{
			$no_empty_param_ids[] = $other_param["id"];
		}
		$this->update_values(array("id" => $this->diafan->_users->id, "table" => "users", "params" => $params, "no_empty_param_ids" => $no_empty_param_ids));

		if ($this->result())
			return;

		if(! empty($order_params))
		{
			$this->update_values(array("id" => $this->diafan->_users->id, "table" => "shop_order", "params" => $order_params, "prefix" => "dop_", "rel" => "user"));
		}

		if ($this->result())
			return;

		if($this->diafan->configmodules("mail_as_login", "users"))
		{
			if($this->diafan->_users->name)
			{
				$_POST["name"] = $this->diafan->_users->name;
			}
			else
			{
				list($_POST["name"],) = explode('@', $_POST["mail"]);
				while(DB::query_result("SELECT name FROM {users} WHERE name='%h' AND trash='0'",  $_POST["name"]))
				{
					$_POST["name"] = $_POST["name"].mt_rand(1, 99999);
				}
			}
		}

		if($_POST["phone"])
		{
			$phone = preg_replace('/[^0-9]+/', '', $_POST["phone"]);
		}

		DB::query("UPDATE {users} SET name='%h', mail='%h', phone='%h', lang_id=%d, fio='%h', role_id=%d WHERE id=%d",
			  $_POST["name"], $_POST["mail"], (! empty($_POST["phone"]) ? $phone : ''), ! empty($_POST["lang_id"]) ? $_POST["lang_id"] : 0,
			  $_POST["fio"], $role_id, $this->diafan->_users->id
		);

		if(in_array('subscription', $this->diafan->installed_modules))
		{
			if(! $this->diafan->_route->id_module('subscription') && $this->diafan->configmodules('subscribe_in_registration', 'subscription'))
			{
				DB::query("UPDATE {subscription_emails} SET act='%d' WHERE mail='%s'", (empty($_POST['subscribe']) ? 0 : 1), $this->diafan->_users->mail);
			}
			// при смене e-mail, меняем его в списке рассылки
			if($this->diafan->_users->mail != $_POST["mail"])
			{
				DB::query("UPDATE {subscription_emails} SET mail='%s' WHERE mail='%s'", $_POST['mail'], $this->diafan->_users->mail);
			}
			if($_POST["phone"])
			{
				$phone = preg_replace('/[^0-9]+/', '', $_POST["phone"]);				
				if($this->diafan->_users->phone != $phone)
				{
					DB::query("UPDATE {subscription_phones} SET phone='%s' WHERE phone='%s'", $phone, $this->diafan->_users->phone);
				}						
			}
		}

		if ($_POST["password"])
		{
			DB::query("UPDATE {users} SET password='%h' WHERE id=%d", encrypt($_POST["password"]), $this->diafan->_users->id);
		}

		$this->upload_avatar();

		$this->result["errors"][0] = $this->diafan->_('Изменения сохранены.', false);
	}

	/*
	 * Удаляет аватар пользователя
	 * 
	 * @return void
	 */
	public function delete_avatar()
	{
		if ($this->diafan->_site->module != 'usersettings')
			return;

		$this->check_user();

		if ($this->result())
				return;

		if ($this->diafan->configmodules("avatar", "users"))
		{
			File::delete_file(USERFILES.'/avatar/'.$this->diafan->_users->name.'.png');
		}
	}

	/**
	 * Загружает изображение
	 *
	 * @return void
	 */
	public function upload_image()
	{
		if ($this->diafan->_site->module != 'usersettings')
			return;

		$this->check_user();

		if ($this->result())
				return;

		$prefix = '';
		if(! empty($_POST["images_prefix"]))
		{
			$prefix = $this->diafan->filter($_POST, "string", "images_prefix");
		}
		if(empty($_POST["images_param_id"]))
		{
			return;
		}

		if (! $this->diafan->_users->checked)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		$this->result["hash"] = $this->diafan->_users->get_hash();

		$param_id = $this->diafan->filter($_POST, "int", "images_param_id");
		if (! empty($_FILES[$prefix.'images'.$param_id]) && $_FILES[$prefix.'images'.$param_id]['tmp_name'] != '' && $_FILES[$prefix.'images'.$param_id]['name'] != '')
		{
			try
			{
				$this->diafan->_images->upload($this->diafan->_users->id, "users", 'element', 0, $_FILES[$prefix.'images'.$param_id]['tmp_name'], $this->diafan->translit($_FILES[$prefix.'images'.$param_id]['name']), false, $param_id);
			}
			catch(Exception $e)
			{
				Dev::$exception_field = $prefix.'p'.$param_id;
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}
			$images = $this->diafan->_images->get('large', $this->diafan->_users->id, "users", 'element', 0, '', $param_id);
			$this->result["data"] = $this->diafan->_tpl->get('images', "usersettings", $images);
		}
		$this->result["result"] = 'success';
	}

	/**
	 * Удаляет изображение
	 *
	 * @return void
	 */
	public function delete_image()
	{
		if ($this->diafan->_site->module != 'usersettings')
			return;

		$this->check_user();

		if ($this->result())
				return;

		if(empty($_POST["id"]))
		{
			return;
		}
		$row = DB::query_fetch_array("SELECT * FROM {images} WHERE module_name='users' AND id=%d AND element_id=%d", $_POST["id"], $this->diafan->_users->id);
		if(! $row)
		{
			return;
		}

		if (! $this->diafan->_users->checked)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		$this->result["hash"] = $this->diafan->_users->get_hash();
		$this->result["result"] = 'success';

		$this->diafan->_images->delete_row($row);
	}

	/**
	 * Валидация введенных данных
	 * 
	 * @return void
	 */
	private function check_fields()
	{
		Custom::inc('includes/validate.php');
		if (! $this->diafan->configmodules("mail_as_login", "users") &&  $_POST["name"] != $this->diafan->_users->name)
		{
			$mes = Validate::login($_POST["name"]);
			if ($mes)
			{
				$this->result["errors"]["name"] = $this->diafan->_($mes);
			}
		}
		$mes = Validate::mail($_POST["mail"]);
		if ($mes)
		{
			$this->result["errors"]["mail"] = $this->diafan->_($mes);
		}
		if ($_POST["mail"] != $this->diafan->_users->mail)
		{
			$mes = Validate::mail_user($_POST["mail"]);
			if ($mes)
			{
				$this->result["errors"]["mail"] = $this->diafan->_($mes);
			}
		}
		if (! empty($_POST["phone"]))
		{
			$mes = Validate::phone($_POST["phone"]);
			if ($mes)
			{
				$this->result["errors"]["phone"] = $this->diafan->_($mes);
			}
		}
		if ($_POST["password"])
		{
			$mes = Validate::password($_POST["password"]);
			if ($mes)
			{
				$this->result["errors"]["password"] = $this->diafan->_($mes);
			}
			elseif ($_POST["password"] != $_POST["password2"])
			{
				$this->result["errors"]["password"] = $this->diafan->_('Пароли не совпадают', false);
			}
		}

		if (empty($_POST["fio"]))
		{
			$this->result["errors"]["fio"] = $this->diafan->_('Заполните поле ФИО или название компании', false);
		}
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
			$result["name"]          = $this->diafan->_users->name;
			$result["fio"]           = $this->diafan->_users->fio;
			$result["avatar_width"]  = $this->diafan->configmodules("avatar_width", "users");
			$result["avatar_height"] = $this->diafan->configmodules("avatar_height", "users");

			$this->result["data"] = array('.usersettings_avatar' => $this->diafan->_tpl->get('avatar', 'usersettings', $result));
		}
	}
}
