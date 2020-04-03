<?php
/**
 * Подключение модуля
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
 * Users_inc
 */
class Users_inc extends Model
{
	/**
	 * @var integer номер текущего пользователя
	 */
	public $id = 0;

	/**
	 * @var integer роль текущего пользователя
	 */
	public $role_id = 0;

	/**
	 * string данные, хранящиеся в сессии
	 */
	public $session;

	/**
	 * @var boolean пользователь проверен по идентификационному хэшу
	 */
	public $checked;

	/**
	 * @var string идентификационный хэш
	 */
	private $hash;

	/**
	 * @var boolean пользователь является администратором с доступом ко всем модулям
	 */
	public $admin;

	/**
	 * @var string ошибка авторизации
	 */
	public $errauth;

	/**
	 * @var array характеристики пользователя
	 */
	public $fields = array('name', 'fio', 'mail', 'phone', 'created', 'role_id', 'lang_id', 'htmleditor', 'admin_nastr', 'start_admin', 'useradmin', 'copy_files', 'config');

	/**
	 * @var array характеристики текущего пользователя
	 */
	private $user;

	/**
	 * @var array права доступа администратора
	 */
	private $roles = array();

	/**
	 * @var array права доступа для модулей
	 */
	private $module_roles = array();

	/**
	 * Доступ к свойствам текущего пользователя
	 *
	 * @return void
	 */
	public function __get($value)
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			if($this->user->name == 'demo')
			{
				$this->user = DB::query_fetch_object("SELECT * FROM {users} WHERE id=1");
			}
		}
		if(in_array($value, $this->fields))
		{
			return ! empty($this->user->$value) ? $this->user->$value : '';
		}
	}

	/**
	 * Определяет текущего пользователя
	 *
	 * @param object $user данные о текущем пользователе
	 * @return void
	 */
	public function set($user)
	{
		$this->user = $user;
		if(! empty($user->session))
		{
			$this->session = $user->session;
		}
		$this->id = $user->id;
		$this->role_id = $user->role_id;

		if(defined('IS_DEMO') && IS_DEMO)
		{
			$this->checked = true;
		}
		else
		{
			if(! empty($_REQUEST["check_hash_user"]) && $this->id)
			{
				if ($id = DB::query_result("SELECT id FROM {sessions_hash} WHERE user_id=%d AND created>%d AND hash='%h' LIMIT 1", $this->id, time() - 7200, $_REQUEST["check_hash_user"]))
				{
					Dev::register_shutdown_function(array($this, 'delete_session_hash'), $id);
					$this->checked = true;
				}
			}
			else
			{
				$this->checked = false;
			}
		}

		$rows = DB::query_fetch_all("SELECT rewrite, perm, type FROM {users_role_perm} WHERE role_id = %d", $this->role_id);
		foreach ($rows as $row)
		{
			switch($row['type'])
			{
				case 'admin':
					$this->roles[$row['rewrite']] = explode(',', $row['perm']);
					break;

				case 'site':
					$this->module_roles[$row['rewrite']] = explode(',', $row['perm']);
					break;
			}

			if(! empty($this->roles['all']) && $this->roles['all'][0] == 'all')
			{
				$this->admin = true;
			}
		}
		if($this->admin)
		{
			$_COOKIE['dev'] = true;
		}
		else
		{
			unset($_COOKIE['dev']);
		}
	}

	/**
	 * Удаляет уникальный хэш сессии
	 *
	 * @param integer $id номер хэша
	 * @return void
	 */
	public function delete_session_hash($id)
	{
		//если вышла ошибка, то хэш не удаляем, чтобы можно было обновить страницу
		if (! Dev::$is_error)
		{
			DB::query("DELETE FROM {sessions_hash} WHERE id=%d", $id);
		}
	}

	/**
	 * Генерируем идентификационный пользовательский хэш
	 *
	 * @return string
	 */
	public function get_hash()
	{
		if($this->hash)
		{
			return $this->hash;
		}
		if ($this->id)
		{
			$pass = DB::query_result("SELECT password FROM {users} WHERE id=%d LIMIT 1", $this->id);
			$this->hash = md5(substr($pass, mt_rand(0, 32), mt_rand(0, 32)).mt_rand(23, 567).substr($pass, mt_rand(0, 32), mt_rand(0, 32)));

			DB::query("INSERT INTO {sessions_hash} (user_id, created, hash) VALUES (%d, %d, '%h')", $this->id, time(), $this->hash);
			DB::query("DELETE FROM {sessions_hash} WHERE created<%d", time() - 7200);
			return $this->hash;
		}
		return '';
	}

	/**
	 * Очищает информацию о текущем пользователе
	 *
	 * @return boolean true
	 */
	public function logout()
	{
		$this->diafan->_session->destroy();
		$lang = '';
		if($_GET["rewrite"]  != "logout")
		{
			$lang = str_replace('logout', '', $_GET["rewrite"]);
		}
		$this->diafan->redirect(BASE_PATH.$lang);
		return true;
	}

	/**
	 * Проверяет авторизован ли пользователь
	 *
	 * @param array $form_values массив с данными для авторизации - логин и пароль
	 * @return array
	 */
	public function auth($form_values)
	{
		if (! $form_values['name'] || ! $form_values['pass'])
		{
			$result["result"] = false;
			$result["error_code"] = 'wrong_login_or_pass';
			if($this->diafan->configmodules("mail_as_login", "users"))
			{
				$result["error"] = $this->diafan->_('Неверный e-mail или пароль.', false);
			}
			else
			{
				$result["error"] = $this->diafan->_('Неверный логин или пароль.', false);
			}
			return $result;
		}
		$name = ($this->diafan->configmodules("mail_as_login", "users") ? "mail" : "name");

		$admin_role_ids = array();
		if(! IS_ADMIN)
		{
			// роли пользователей, имеющих доступ к администрированию
			$admin_role_ids = DB::query_fetch_value("SELECT DISTINCT(role_id) FROM {users_role_perm} WHERE type='admin'", "role_id");
		}

		if (DB::query_result("SELECT id FROM {users} WHERE trash='0' AND act='0' AND LOWER(".$name.")=LOWER('%s') LIMIT 1", trim($form_values['name'])))
		{
			$result["result"] = false;
			$result["error_code"] = 'blocked';
			$result["error"] = $this->diafan->_('Логин не активирован или заблокирован.', false);
			return $result;
		}

		if ($this->_log())
		{
			$result["result"] = false;
			$result["error_code"] = 'blocked_30_min';
			$result["error"] = $this->diafan->_('Вы превысили количество попыток, поэтому будете заблокированы на 30 минут', false);
			return $result;
		}

		$result_sql = DB::query("SELECT * FROM {users} u WHERE trash='0' AND act='1' AND LOWER(".$name.")=LOWER('%s') AND password='%s'", trim($form_values['name']), encrypt(trim($form_values['pass'])));

		if (DB::num_rows($result_sql))
		{
			$user = DB::fetch_object($result_sql);
			DB::free_result($result_sql);

			if(in_array($user->role_id, $admin_role_ids))
			{
				$result["result"] = false;
				$result["error_code"] = 'admin_role';
				$result["error"] = $this->diafan->_('Вы вводите правильный логин, но админстратор войти на сайт может только по секретному адресу панели управления.', false);
				return $result;
			}

			$this->set($user);

			$rew = '';
			if(! empty($form_values["is_admin"]))
			{
				$rewrite = ADMIN_FOLDER.'/';
			}
			else
			{
				$rewrite = (! empty($_REQUEST["rewrite"]) ? $_REQUEST["rewrite"].(ROUTE_END == '/' ? "/" : '') : '');
			}
			if ($this->lang_id)
			{
				foreach ($this->diafan->_languages->all as $language)
				{
					if($this->diafan->_languages->site != $language["id"])
					{
						$rewrite = preg_replace('/^'.preg_quote($language["shortname"], '/').'(\/)*/', '', $rewrite);
					}
					if($language["id"] == $this->lang_id)
					{
						$rew = (! $language["base_site"]) ? $language["shortname"].'/' : '';
					}
				}
			}
			$result["result"] = true;
			$result["redirect"] = BASE_PATH.$rew.$rewrite;
			return $result;
		}
		else
		{
			$this->update_log();
			$result["result"] = false;
			$result["error_code"] = 'wrong_login_or_pass';
			$result["error"] = $this->diafan->_('Неверный логин или пароль.', false);
			return $result;
		}
	}

	/**
	 * Проверяет авторизован ли пользователь
	 *
	 * @return boolean
	 */
	public function auth_loginza()
	{
		$profile = file_get_contents('http'.(IS_HTTPS ? "s" : '').'://loginza.ru/api/authinfo'
			.'?token='.$_POST['token']
			.'&id='.$this->diafan->configmodules('loginza_widget_id', 'users')
			.'&sig='.md5($_POST['token'].$this->diafan->configmodules('loginza_skey', 'users'))
		);
		$profile = json_decode($profile);

		// проверка на ошибки
		if (! is_object($profile) || !empty($profile->error_message) || !empty($profile->error_type)) {
			return;
		}
		if($this->diafan->_users->id)
		{
			DB::query("UPDATE {users} SET identity='%h' WHERE id=%d", $profile->identity, $this->diafan->_users->id);
			return;
		}
		if($profile->identity)
		{
			$result = DB::query("SELECT * FROM {users} u WHERE trash='0' AND act='1' AND identity='%h'", $profile->identity);
		}
		if(! DB::num_rows($result) && ! empty($profile->identities) && is_array($profile->identities))
		{
			foreach ($profile->identities as $i)
			{
				if($i)
				{
					$result = DB::query("SELECT * FROM {users} u WHERE trash='0' AND act='1' AND identity='%h'", $i);
					if (DB::num_rows($result))
					{
						break;
					}
				}
			}
		}

		if (! DB::num_rows($result))
		{
			if ($profile->name->full_name)
			{
				$fio = $profile->name->full_name;
			}
			elseif ($profile->name->first_name || $profile->name->last_name)
			{
				$fio = trim($profile->name->first_name.' '.$profile->name->last_name);
			}
			if(! empty($profile->nickname))
			{
				$name = $profile->nickname;
			}
			elseif($profile->email)
			{
				list($name, ) = explode('@', $profile->email);
			}
			else
			{
				$name = rand(1, 9999);
			}
			while(DB::query_result("SELECT id FROM {users} WHERE trash='0' AND name='%h'", $name))
			{
				$name .= rand(1, 9999);
			}
			if (!empty($profile->photo))
			{
				try
				{
					$this->create_avatar($name, $profile->photo);
				}
				catch(Exception $e)
				{}
			}
			$role_id = DB::query_result("SELECT id FROM {users_role} WHERE registration='1' AND trash='0' LIMIT 1");
			DB::query("INSERT INTO {users} (name, fio, identity, mail, act, role_id, created) VALUES ('%h', '%h', '%h', '%h', '1', %d, %d)", $name, $fio, $profile->identity, $profile->email, $role_id, time());
			$result = DB::query("SELECT * FROM {users} u WHERE trash='0' AND act='1' AND identity='%h'", $profile->identity);
		}
		$user = DB::fetch_object($result);
		DB::free_result($result);
		$this->set($user);
	}

	/**
	 * Проверяет авторизован ли пользователь
	 *
	 * @return mixed(string|integer)
	 */
	public function auth_api($name, $pass)
	{
		if(empty($name))
		{
			return "no_name";
		}
		if(empty($pass))
		{
			return "no_pass";
		}
		$field = ($this->diafan->configmodules("mail_as_login", "users") ? "mail" : "name");
		$admin_role_ids = array();
		if(! IS_ADMIN)
		{
			// роли пользователей, имеющих доступ к администрированию
			$admin_role_ids = DB::query_fetch_value("SELECT DISTINCT(role_id) FROM {users_role_perm} WHERE type='admin'", "role_id");
		}
		if(DB::query_result("SELECT id FROM {users} WHERE trash='0' AND act='0' AND LOWER(".$field.")=LOWER('%s') LIMIT 1", trim($name)))
		{
			return "blocked";
		}
		if ($this->_log())
		{
			return "blocked_30_min";
		}
		$result_sql = DB::query("SELECT * FROM {users} u WHERE trash='0' AND act='1' AND LOWER(".$field.")=LOWER('%s') AND password='%s'", trim($name), encrypt(trim($pass)));
		if (DB::num_rows($result_sql))
		{
			$user = DB::fetch_object($result_sql);
			DB::free_result($result_sql);
			if(in_array($user->role_id, $admin_role_ids))
			{
				// Введен верный проль, пользователь является администратором
			}
			return (int) $user->id;
		}
		$this->update_log();
		return "wrong_login_or_pass";
	}

	/**
	 * Загружает аватар
	 *
	 * @param string $name логин пользователь
	 * @param string $file файл аватара
	 * @return void
	 */
	public function create_avatar($name, $file)
	{
		if (! $this->diafan->configmodules("avatar", "users"))
		{
			return;
		}
		if ($file)
		{
			$tmp_name = 'avatar'.rand(0, 99999);
			File::copy_file($file, 'tmp/'.$tmp_name);
			$tmp_name = 'tmp/'.$tmp_name;
			try
			{
				list($width, $height) = getimagesize(ABSOLUTE_PATH.$tmp_name);
				if (! $width || ! $height)
				{
					throw new Exception($this->diafan->_('Некорректный файл.'));
				}
				if ($width < $this->diafan->configmodules("avatar_width", "users") || $height < $this->diafan->configmodules("avatar_height", "users"))
				{
					throw new Exception($this->diafan->_('Размер изображения должен быть не меньше %spx X %spx.', false, $this->diafan->configmodules("avatar_width", "users"), $this->diafan->configmodules("avatar_height", "users")));
				}
				Custom::inc('includes/image.php');
				if (! Image::resize(ABSOLUTE_PATH.$tmp_name, $this->diafan->configmodules("avatar_width", "users"), $this->diafan->configmodules("avatar_height", "users"), $this->diafan->configmodules("avatar_quality", "users"), true, true))
				{
					throw new Exception($this->diafan->_('Файл не загружен.'));
				}
				$dst_img  = imageCreateTrueColor($this->diafan->configmodules("avatar_width", "users"), $this->diafan->configmodules("avatar_height", "users"));
				$original = @imageCreateFromString(file_get_contents(ABSOLUTE_PATH.$tmp_name));
				if(! imageCopy($dst_img, $original, 0, 0, 0, 0, $this->diafan->configmodules("avatar_width", "users"), $this->diafan->configmodules("avatar_height", "users")))
				{
					throw new Exception($this->diafan->_('Файл не загружен.'));
				}
				File::create_dir(USERFILES.'/avatar');
				if(! imagePNG($dst_img, ABSOLUTE_PATH.USERFILES.'/avatar/'.$name.'.png'))
				{
					throw new Exception($this->diafan->_('Файл не загружен.'));
				}
				File::delete_file($tmp_name);
			}
			catch(Exception $e)
			{
				File::delete_file($tmp_name);
				throw new Exception($e->getMessage());
			}
		}
		return true;
	}

	/**
	 * Проверяет есть ли права у пользователя на действие для модуля
	 *
	 * @param string $action действие
	 * @param string $module_name модуль
	 * @param array $roles права пользователя
	 * @param string $type часть сайта административная/пользовательская
	 * @return boolean
	 */
	public function roles($action, $module_name = '', $roles = array(), $type = 'admin')
	{
		if (! $roles)
		{
			$roles = $type == 'admin' ? $this->roles : $this->module_roles;
			if (empty($roles))
			{
				return false;
			}
		}
		if(! $module_name)
		{
			if(IS_ADMIN)
			{
				$module_name = $this->diafan->_admin->module;
			}
			else
			{
				$module_name = $this->diafan->_site->module;
			}
		}

		if(! empty($roles['all']))
		{
			return true;
		}

		if(! empty($roles[$module_name]))
		{
			if ($roles[$module_name][0] == 'all' || in_array($action, $roles[$module_name]))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Проверяет лог авторизации для блокировки попытки подбора паролей
	 *
	 * @return boolean
	 */
	private function _log()
	{
		DB::query("DELETE FROM {log} WHERE created<%d", time());
		$ip = getenv('REMOTE_ADDR');
		if($row = DB::query_fetch_array("SELECT `count` FROM {log} WHERE ip='%h' LIMIT 1", $ip))
		{
			if ($row['count'] > 4)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Обновляет лог авторизации для блокировки попытки подбора паролей
	 *
	 * @return void
	 */
	private function update_log()
	{
		$ip = getenv('REMOTE_ADDR');
		$date = time() + 1800;
		$result = DB::query("SELECT `count` FROM {log} WHERE ip='%h'", $ip);
		if (DB::num_rows($result) > 0)
		{
			DB::query("UPDATE {log} SET `count`=`count`+1, created=%d WHERE ip='%h'", $date, $ip);
		}
		else
		{
			DB::query("INSERT INTO {log} (ip, created, info) VALUES ('%s', '%d', '%s')", $ip, $date, getenv('HTTP_USER_AGENT'));
		}
		DB::free_result($result);
	}
}
