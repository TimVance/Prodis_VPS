<?php
/**
 * Модель модуля «Настройки аккаунта»
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
 * Usersettings_model
 */
class Usersettings_model extends Model
{
	/**
	 * Генерирует данные для формы регистрации
	 * 
	 * @return void
	 */
	public function form()
	{
		$this->result["fio"] = $this->diafan->_users->fio;
		$this->result["hash"] = $this->diafan->_users->get_hash();
		$this->result["mail"] = $this->diafan->_users->mail;
		$this->result["name"] = $this->diafan->_users->name;
		$this->result["phone"] = $this->diafan->_users->phone;
		$this->result["use_name"] = ! $this->diafan->configmodules("mail_as_login", "users");

		if (count($this->diafan->_languages->all) > 1)
		{
			foreach ($this->diafan->_languages->all as $language)
			{
				$this->result["languages"][] = array(
					"value"    => $language["id"],
					"selected" => ($language["id"] == $this->diafan->_users->lang_id ? ' selected' : ''),
					"name"     => $language["name"]
				);
			}
		}

		$fields = array('', 'name', 'fio', 'phone', 'password', 'password2', 'mail', 'captcha', 'avatar');
		$this->get_shop_order_param($fields);

		$this->result["action"] = BASE_PATH_HREF.$this->diafan->_route->current_link();
		$this->result["user_id"] = $this->diafan->_users->id;
		$this->result["url"] = '';
		$this->result["use_avatar"] = $this->diafan->configmodules("avatar", "users");
		if ($this->result["use_avatar"])
		{
			$this->result["avatar"] = file_exists(ABSOLUTE_PATH.USERFILES.'/avatar/'.$this->diafan->_users->name.'.png');
			$this->result["avatar_width"] = $this->diafan->configmodules("avatar_width", "users");
			$this->result["avatar_height"] = $this->diafan->configmodules("avatar_height", "users");
		}

		if (! $this->diafan->configmodules("act", "users"))
		{
			$this->result["url"] = $this->result["action"].'?action=success';
		}
		$where_param_role_rel = $this->get_where_param_role_rel();
		$where = "show_in_form_auth='1'".$where_param_role_rel;
		$this->result["rows_param"] = $this->get_params(array("module" => "users", "where" => $where));

		$param_types_array = array();
		foreach ($this->result["rows_param"] as &$row)
		{
			$fields[] = 'p'.$row["id"];
			$param_types_array[$row["id"]] = $row["type"];
			if($row["type"] == 'attachments' && ! $row["attachments_access_admin"])
			{
				$this->result['attachments'][$row["id"]] = $this->diafan->_attachments->get($this->diafan->_users->id, 'users', $row["id"]);
			}
			if($row["type"] == 'images')
			{
				$this->result['images'][$row["id"]] = $this->diafan->_images->get('large', $this->diafan->_users->id, 'users', 'element', 0, '', $row["id"]);
			}
			$row["text"] = $this->diafan->_tpl->htmleditor($row["text"]);
		}
		$this->form_errors($this->result, "usersettings", $fields);

		$this->result["captcha"] = '';
		if ($this->diafan->_captcha->configmodules("users") && ! $this->diafan->_users->id)
		{
			$this->result["captcha"] = $this->diafan->_captcha->get("usersettings", $this->result["error_captcha"]);
		}

		$user_param = array();
		$rows = DB::query_fetch_all("SELECT value, param_id FROM {users_param_element} WHERE trash='0' AND element_id=%d", $this->diafan->_users->id);
		foreach ($rows as &$row)
		{
			if(empty($param_types_array[$row["param_id"]]))
				continue;

			switch ($param_types_array[$row["param_id"]])
			{
				case 'multiple':
					$user_param[$row["param_id"]][] = $row["value"];
					break;

				case 'date':
					$user_param[$row["param_id"]] = $this->diafan->formate_from_date($row["value"]);
					break;

				case 'datetime':
					$user_param[$row["param_id"]] = $this->diafan->formate_from_datetime($row["value"]);
					break;

				default:
					$user_param[$row["param_id"]] = $row["value"];
			}
		}
		foreach ($this->result["rows_param"] as &$row)
		{
			$row["value"] = '';
			if(! empty($user_param[$row["id"]]))
			{
				$row["value"] = $user_param[$row["id"]];
			}
		}

		if($this->diafan->_route->id_module('subscription'))
		{
			$this->result['link_subscription'] = BASE_PATH_HREF.$this->diafan->_route->module("subscription").'?action=edit&mail='.$this->diafan->_users->mail;
			if($code = DB::query_result("SELECT code FROM {subscription_emails} WHERE mail='%s' AND trash='0'", $this->diafan->_users->mail))
			{
				$this->result['link_subscription'] .= '&code='.$code;
			}
		}
		else
		{
			if(in_array("subscription", $this->diafan->installed_modules))
			{
				$this->result['use_subscription'] = $this->diafan->configmodules('subscribe_in_usersettings', 'subscription');
				$this->result["is_subscribe"] = DB::query_result("SELECT id FROM {subscription_emails} WHERE mail='%s' AND trash='0' AND act='1' LIMIT 1", $this->diafan->_users->mail);
			}
		}
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
		$result_roles = DB::query_fetch_all("SELECT id, [name] FROM {users_role} WHERE registration='1' AND trash='0' ORDER BY sort ASC");
		$rows = DB::query_fetch_all("SELECT role_id, element_id FROM {users_param_role_rel} WHERE trash='0' AND role_id>0");
		foreach ($rows as $row)
		{
			$param_role_rels[$row["element_id"]][] = $row["role_id"];
		}
		$roles = array($this->diafan->_users->role_id);
		foreach($result_roles as $r)
		{
			$roles[] = $r["id"];
		}
		if(count($result_roles) > 1)
		{
			$this->result["roles"] = $result_roles;
			$this->result["param_role_rels"] = $param_role_rels;
		}
		$this->result["role_id"] = $this->diafan->_users->role_id;
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
	 * Формирует поля формы "Оформление заказа", доступные для внешнего редактирования
	 *
	 * @param array $fields поля для редактирования
	 * @return void
	 */
	private function get_shop_order_param(&$fields)
	{
		if(! $this->diafan->_route->id_module('cart'))
			return false;

		if (empty($this->diafan->_users->id))
			return false;

		$values = DB::query_fetch_key_value("SELECT value, param_id FROM {shop_order_param_user} WHERE trash='0' AND user_id=%d", $this->diafan->_users->id, "param_id", "value");

		$this->result["dop_rows_param"] = $this->get_params(array("module" => "shop", "table" => "shop_order", "where" => "show_in_form_register='1'"));
		foreach ($this->result["dop_rows_param"] as $i => $row)
		{
			if($row["type"] == "attachments" || $row["type"] == "images")
			{
				unset($this->result["dop_rows_param"][$i]);
				continue;
			}
			$row["value"] = '';
			if(! empty($values[$row["id"]]))
			{
				$row["value"] = $values[$row["id"]];
			}
			$row["required"] = false;
			$this->result["dop_rows_param"][$i] = $row;
			$fields[] = 'dop_p'.$row["id"];
		}
	}
}