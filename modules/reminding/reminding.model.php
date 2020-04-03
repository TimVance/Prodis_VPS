<?php
/**
 * Модель модуля «Восстановление пароля»
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
 * Reminding_model
 */
class Reminding_model extends Model
{
	/**
	 * Генерирует данные для формы запроса ссылки на форму восстановления пароля
	 * 
	 * @return void
	 */
	public function form_mail()
	{
		if ($this->diafan->_users->id)
		{
			$this->diafan->redirect(BASE_PATH_HREF);
		}
		$this->form_errors($this->result, "reminding", array('', 'mail', 'captcha'));
		$this->result["captcha"] = '';
		if ($this->diafan->_captcha->configmodules("users"))
		{
			$this->result["captcha"] = $this->diafan->_captcha->get("reminding", $this->result["error_captcha"]);
		}
		$this->result["action"] = ! empty($_GET["diafan"]) ? '<input type="hidden" name="diafan" value="true">' : '';
		$this->result["view"] = 'form_mail';
	}

	/**
	 * Генерирует данные для формы смены пароля
	 * 
	 * @return void
	 */
	public function form_change_password()
	{
		if ($this->diafan->_users->id || empty($_GET["user_id"]) || empty($_GET["code"]))
		{
			$this->diafan->redirect(BASE_PATH_HREF);
		}
		$actlink = DB::query_fetch_array("SELECT user_id, created, link FROM {users_actlink} WHERE link='%h' AND user_id=%d AND `count`<4 LIMIT 1", $_GET["code"], $_GET["user_id"]);
		$user = DB::query_fetch_array("SELECT id, act FROM {users} WHERE id=%d LIMIT 1", $_GET["user_id"]);
		if (! $actlink || ! $user)
		{
			$this->result["result"] = "incorrect";
		}
		elseif($user["id"] && ! $user["act"])
		{
			$this->result["result"] = "block";
		}
		elseif ($actlink["created"] < time())
		{
			$this->result["result"] = "old";
		}
		else
		{
			$this->result["result"] = "success";
			$this->result["user_id"] = $actlink["user_id"];
			$this->result["code"] = $actlink["link"];
			$this->form_errors($this->result, "reminding", array('', 'password'));
		}
		DB::query("UPDATE {users_actlink} SET `count`=`count`+1 WHERE user_id=%d", $_GET["user_id"]);
		$this->result["view"] = 'form_change_password';
	}

	/**
	 * Страница успешной смены пароля
	 * 
	 * @return void
	 */
	public function success()
	{
		if (! $this->diafan->_users->id)
		{
			Custom::inc('includes/404.php');
		}
		$this->result["view"] = 'success';
	}
}