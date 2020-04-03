<?php
/**
 * Модель модуля «Регистрация»
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
 * Registration_model
 */
class Registration_model extends Model
{
	/**
	 * Генерирует данные для формы регистрации
	 * 
	 * @return void
	 */
	public function form()
	{
		$this->result["action"] = '';
		$fields = array('', 'name', 'fio', 'password', 'password2', 'mail', 'phone', 'avatar', 'captcha');
		$this->result["user_id"] = $this->diafan->_users->id;
		$this->result["url"] = '';
		$this->result["use_name"] = ! $this->diafan->configmodules("mail_as_login", "users");
		$this->result["use_avatar"] = $this->diafan->configmodules("avatar", "users");
		if ($this->result["use_avatar"])
		{
			$this->result["avatar_width"] = $this->diafan->configmodules("avatar_width", "users");
			$this->result["avatar_height"] = $this->diafan->configmodules("avatar_height", "users");
		}

		if (! $this->diafan->configmodules("act", "users"))
		{
			$this->result["url"] = $this->result["action"].'?action=success';
		}
		$where_param_role_rel = $this->get_where_param_role_rel();
		$where = "show_in_form_no_auth='1'".$where_param_role_rel;
		$this->result["rows_param"] = $this->get_params(array("module" => "users", "where" => $where));

		$param_types_array = array();
		foreach ($this->result["rows_param"] as &$row)
		{
			$fields[] = 'p'.$row["id"];
			$param_types_array[$row["id"]] = $row["type"];
			$row["text"] = $this->diafan->_tpl->htmleditor($row["text"]);
		}
		$this->form_errors($this->result, "registration", $fields);
		$this->result["captcha"] = '';
		if ($this->diafan->_captcha->configmodules("users"))
		{
			$this->result["captcha"] = $this->diafan->_captcha->get("registration", $this->result["error_captcha"]);
		}

		$this->result['use_subscription'] =
			in_array("subscription", $this->diafan->installed_modules)
			&& $this->diafan->configmodules('subscribe_in_registration', 'subscription');

		$this->result["view"] = 'form';
	}

	/**
	 * Получает условие для SQL-запроса: выбор полей с учетом роли пользователя
	 *
	 * @param integer $role_id номер роли пользователя
	 * @return string
	 */
	private function get_where_param_role_rel()
	{
		$param_ids = array();
		$param_role_rels = array();
		$rows = DB::query_fetch_all("SELECT role_id, element_id FROM {users_param_role_rel} WHERE trash='0' AND role_id>0");
		foreach ($rows as $row)
		{
			$param_role_rels[$row["element_id"]][] = $row["role_id"];
		}
		$roles = array();
		$result_roles = DB::query_fetch_all("SELECT id, [name] FROM {users_role} WHERE registration='1' AND trash='0' ORDER BY sort ASC");
		foreach ($result_roles as $row)
		{
			$roles[] = $row["id"];
		}
		if(count($result_roles) > 1)
		{
			$this->result["roles"] = $result_roles;
			$this->result["param_role_rels"] = $param_role_rels;
		}
		foreach ($param_role_rels as $param_id => $rel_roles)
		{
			$in = false;
			foreach ($roles as $role_id)
			{
				if(in_array($role_id, $rel_roles))
				{
					$in = true;
				}
			}
			if(! $in)
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
	 * Активация аккаунта
	 * 
	 * @return void
	 */
	public function act()
	{
		if(empty($_GET["code"]) || empty($_GET["user_id"]) || $this->diafan->configmodules("act", "users") != 1)
		{
			Custom::inc('includes/404.php');
		}
		$actlink = DB::query_fetch_array("SELECT user_id, created FROM {users_actlink} WHERE link='%h' AND user_id=%d AND `count`<4 LIMIT 1", $_GET["code"], $_GET["user_id"]);
		$user = DB::query_fetch_object("SELECT * FROM {users} WHERE id=%d LIMIT 1", $_GET["user_id"]);

		DB::query("UPDATE {users_actlink} SET `count`=`count`+1 WHERE user_id=%d", $_GET["user_id"]);

		if (empty($user->id) || ! $user->act && ! $actlink)
		{
			$this->result["text"] = $this->diafan->_('Извините, вы не можете воспользоваться этой ссылкой.', false);
		}
		else
		{
			if (! $user->act && $actlink["created"] < time())
			{
				$this->result["text"] = $this->diafan->_('Извините, время действия ссылки закончилось.', false);
			}
			elseif($user->act)
			{
				$this->result["text"] = $this->diafan->_('Ваш аккаунт был активирован ранее.', false);
			}
			else
			{
				DB::query("UPDATE {users} SET act='1' WHERE id=%d", $actlink["user_id"]);
				DB::query("DELETE FROM {users_actlink} WHERE link='%h' AND user_id=%d", $_GET["code"], $actlink["user_id"]);
				$this->diafan->_users->set($user);
				$this->result["text"] = $this->diafan->_('Регистрация успешно активирована! Вы авторизованы на сайте.', false);
			}
		}
		$this->result["view"] = 'act';
	}

	/**
	 * Страница успешной регистрации
	 * 
	 * @return void
	 */
	public function success()
	{
		$this->result["text"] = $this->diafan->configmodules('mes', "users");
		$this->result["view"] = 'success';
	}

	/**
	 * Генерирует данные для формы авторизации
	 * 
	 * @return array
	 */
	public function show_login()
	{
		$result["user"] = $this->diafan->_users->id;
		if (! $result["user"])
		{
			$result["registration"] = $this->diafan->_route->module("registration");
			if($result["registration"] !== false)
			{
				$result["registration"] = BASE_PATH_HREF.$result["registration"];
			}
			$this->form_errors($result, "registration_auth", array(''));
			$result["reminding"] = $this->diafan->_route->module("reminding");
			if($result["reminding"] !== false)
			{
				$result["reminding"] = BASE_PATH_HREF.$result["reminding"];
			}
			if(empty($result["error"]) && $this->diafan->_users->errauth)
			{
				$result["error"] = $this->diafan->_users->errauth;
			}
			$result["action"]      = $this->diafan->_site->module == "registration" ? BASE_PATH_HREF : '';
			$result["use_loginza"] = $this->diafan->configmodules("loginza", "users");
		}
		else
		{

			$result["fio"] = $this->diafan->_users->fio;
			$result["usersettings"] = $this->diafan->_route->module("usersettings");
			if($result["usersettings"] !== false)
			{
				$result["usersettings"] = BASE_PATH_HREF.$result["usersettings"];
			}

			$result["userpage"] = $this->diafan->_route->module("userpage");
			if(! empty($result["userpage"]))
			{
				$result["userpage"] = BASE_PATH_HREF.$result["userpage"].'?name='.$this->diafan->_users->name;
			}

			if ($message_site_id = $this->diafan->_route->id_module("messages", 0, false))
			{
				$result['messages']   = BASE_PATH.$this->diafan->_route->link($message_site_id);
				$result['messages_unread'] = DB::query_result("SELECT COUNT(*) FROM {messages} WHERE to_user=%d AND readed='0' LIMIT 1", $this->diafan->_users->id);
				$result['messages_name']   = DB::query_result("SELECT [name] FROM {site} WHERE id=%d", $message_site_id);
			}
			if ($this->diafan->configmodules("avatar", "users"))
			{
				$result["avatar"] = file_exists(ABSOLUTE_PATH.USERFILES.'/avatar/'.$this->diafan->_users->name.'.png');
				if(! $result["avatar"] && $this->diafan->configmodules("avatar_none", "users"))
				{
					$result["avatar_none"] = BASE_PATH.USERFILES.'/avatar_none.png';
				}
				$result["avatar_width"]  = $this->diafan->configmodules("avatar_width", "users");
				$result["avatar_height"] = $this->diafan->configmodules("avatar_height", "users");
				$result["name"]          = $this->diafan->_users->name;
			}
		}
		return $result;
	}
}