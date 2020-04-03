<?php
/**
 * Обработка запроса при отправке сообщения из формы подписки на рассылку
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

class Subscription_action extends Action
{
	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function init()
	{
		if(empty($_POST['action']))
			return;

		if($_POST['action'] == 'add')
		{
			if ($this->diafan->_captcha->configmodules('subscription'))
			{
				$this->check_captcha();
			}
		}

		if(empty($_POST['mail']))
		{
			$this->result["errors"]['mail'] = $this->diafan->_('Укажите e-mail.');
			return;
		}

		if ($this->result())
			return;

		$row = DB::query_fetch_array("SELECT * FROM {subscription_emails} WHERE mail='%s' AND trash='0' LIMIT 1", $_POST['mail']);
		if($row["id"] && ($_POST['action'] != 'add' && $row['code'] != $_POST['code']))
		{
		    return FALSE;
		}

		if(! $row["id"])
		{
			$this->check_fields();

			if ($this->result())
				return;

			$row['code'] = md5(rand(0, 9999999));
			$row['mail'] = $this->diafan->filter($_POST, "string", "mail");
			
			$row['id'] = DB::query("INSERT INTO {subscription_emails} (mail, act, created, code) VALUES ('%s', '1', %d, '%s')", $row['mail'], time(), $row['code']);

			$this->send_mail($row["mail"], $row["code"]);
		}
		elseif(! $row["act"])
		{
			DB::query("UPDATE {subscription_emails} SET act='1' WHERE id=%d", $row["id"]);
			$this->send_mail($row["mail"], $row["code"]);
		}

		if($this->diafan->configmodules("cat", "subscription") && $_POST['action'] != 'add')
		{
			DB::query("DELETE FROM {subscription_emails_cat_unrel} WHERE element_id=%d", $row['id']);
			$rows_cat = DB::query_fetch_all("SELECT id FROM {subscription_category} WHERE trash='0' ORDER BY sort ASC");
			foreach ($rows_cat as $row_cat)
			{
				if(empty($_POST['cat_ids']) || ! in_array($row_cat['id'], $_POST['cat_ids']))
				{
				    DB::query("INSERT INTO {subscription_emails_cat_unrel} (element_id, cat_id) VALUES (%d, %d)", $row['id'], $row_cat['id']);
				}
			}
			if(empty($_POST['cat_ids']))
			{
				DB::query("UPDATE {subscription_emails} SET act='0' WHERE id=%d", $row["id"]);
			}
		}
		// запоминаем e-mail в сессию, чтобы использовать в других модулях
		$_SESSION["user_mail"] = $row['mail'];
		// записываем e-mail в корзину, если модуль используется на сайте
		if(in_array('cart', $this->diafan->installed_modules))
		{
			$this->diafan->_cart->edit(array("mail" => $row['mail']));
		}
		if($_POST['action'] == 'add')
		{
			$mes = $this->diafan->configmodules('add_mail', 'subscription');	
			$this->result["errors"][0] = $mes ? $mes : ' ';
			$this->result["result"] = 'success';
		}
		else
		{
			$this->result["errors"][0] = $this->diafan->_('Изменения сохранены.', false);
		}
	}

	/**
	 * Валидация введенных данных
	 * 
	 * @return void
	 */
	private function check_fields()
	{
		Custom::inc('includes/validate.php');

		$mes = Validate::mail($_POST['mail']);
		if ($mes)
		{
			$this->result["errors"]["mail"] = $this->diafan->_($mes);
		}
	}

	/**
	 * Уведомление пользователя по e-mail
	 * 
	 * @return void
	 */
	private function send_mail($mail, $code)
	{
		$url_subscription = BASE_PATH_HREF.$this->diafan->_route->module("subscription");
		$link    = $url_subscription.'?mail='.$mail.'&code='.$code;
		$actlink = $url_subscription.'?action=del&mail='.$mail.'&code='.$code;
	    
		$subject = str_replace(
			array('%title', '%url'),
			array(TITLE, BASE_URL),
			$this->diafan->configmodules("subject_user", 'subscription')
		);

		$message = str_replace(
			array('%title', '%url', '%link', '%actlink'),
			array(
				TITLE,
				BASE_URL,
				$link,
				$actlink				
			),
			$this->diafan->configmodules("message_user", 'subscription')
		);

		$to   = $mail;
		$from = $this->diafan->configmodules("emailconf", 'subscription')
		        ? $this->diafan->configmodules("email", 'subscription')
		        : EMAIL_CONFIG;

		$this->diafan->_postman->message_add_mail($to, $subject, $message, $from);
	}
}