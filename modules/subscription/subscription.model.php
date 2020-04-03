<?php
/**
 * Модель
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
 * Subscription_model
 */
class Subscription_model extends Model
{
	/**
	 * Форма подписки на рассылки
	 * 
	 * @param boolean $insert_form форма выводится с помощью шаблонного тега
	 * @return void
	 */
	public function form($insert_form = false)
	{
		$fields = array('', 'mail', 'captcha');
		$this->result['form_tag'] = 'subscription'.md5($insert_form);
		$this->form_errors($this->result, $this->result['form_tag'], $fields);
		$this->result["captcha"] = '';
		if ($this->diafan->_captcha->configmodules("subscription"))
		{
			$this->result["captcha"] = $this->diafan->_captcha->get($this->result['form_tag'], $this->result["error_captcha"]);
		}
		$this->result["view"] = 'form';
		return $this->result;
	}

	/**
	 * Отписывает e-mail от рассылки
	 * 
	 * @return void
	 */
	public function edit()
	{
		$row = DB::query_fetch_array("SELECT * FROM {subscription_emails} WHERE mail='%s' AND trash='0' LIMIT 1", $_GET['mail']);
		$this->result['mail'] = $_GET["mail"];
		if($row["id"])
		{
			$this->result = $row;
			if($_GET["code"] != $row['code'])
			{
				$this->result["view"] = "error";
				return;
			}
			if($row["act"])
			{
				$this->result['link'] = BASE_PATH_HREF.$this->diafan->_route->module("subscription").'?action=del&mail='.urlencode($row['mail']).'&code='.urlencode($row["code"]);
			}
		}
		else
		{
			$row["act"] = 0;
			$row["code"] = '';
		}

		if($this->diafan->configmodules("cat", "subscription"))
		{
			$this->result["cats"] = array();
			$this->result["cats_unrel"] = array();
			if($row["id"])
			{
				$this->result["cats_unrel"] = DB::query_fetch_value("SELECT cat_id FROM {subscription_emails_cat_unrel} WHERE element_id=%d AND trash='0'", $row['id'], "cat_id");
			}
			$array = array();
			$this->parent_id_subscription(0, 0, $array);
		}
		$this->result["view"] = 'edit';
	}
    
	/**
	 * Отписывает e-mail от рассылки
	 * 
	 * @return void
	 */
	public function del()
	{
		if(empty($_GET["code"]) || empty($_GET["mail"]))
		{
			Custom::inc('includes/404.php');
		}

		$row = DB::query_fetch_array("SELECT * FROM {subscription_emails} WHERE mail='%s' AND trash='0' LIMIT 1", $_GET['mail']);
		if(! $row["id"])
		{
			$this->diafan->redirect(BASE_PATH_HREF.$this->diafan->_route->module("subscription")."?mail=".urlencode($_GET['mail']));
		}

		if($_GET["code"] != $row['code'])
		{
			$this->result["view"] = "error";
			return;
		}
		if($row["act"])
		{
			DB::query("UPDATE {subscription_emails} SET act='0' WHERE id=%d LIMIT 1", $row['id']);
			DB::query("DELETE FROM {subscription_emails_cat_unrel} WHERE element_id=%d", $row['id']);
		}
		$this->diafan->redirect(BASE_PATH_HREF.$this->diafan->_route->module("subscription")."?mail=".urlencode($row["mail"])."&code=".urlencode($row["code"]));
	}
	
	
	/**
	 * Формирует список рассылок
	 * @return array
	 */
	private function parent_id_subscription($parent_id, $level, &$array)
	{
		$rows = DB::query_fetch_all("SELECT [name], id FROM {subscription_category} WHERE parent_id=%d AND trash='0' ORDER BY sort ASC", $parent_id);
		foreach ($rows as $row)
		{
			$row["level"] = $level;
			$this->result["cats"][] = $row;
			if (in_array($row["id"], $array))
			{
				return $array;
			}
			$array[] = $row["id"];
			$this->parent_id_subscription($row["id"], $level+1, $array);
		}
	}
}