<?php
/**
 * Обработка запроса при отправки сообщения из формы
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

class Faq_action extends Action
{
	/**
	 * Обрабатывает запрос на добавление вопроса
	 * 
	 * @return void
	 */
	public function init()
	{
		if ($this->diafan->configmodules('only_user', 'faq', $_POST["site_id"]))
		{
			$this->check_user();

			if ($this->result())
				return;
		}

		$this->check_site_id();

		if ($this->result())
			return;

		if (! empty($_POST["cat_id"]) && ! DB::query_result("SELECT id FROM {faq_category} WHERE id=%d AND trash='0' AND [act]='1' LIMIT 1", $_POST["cat_id"]))
		{
			return;
		}

		if ($this->diafan->_captcha->configmodules('faq', $this->site_id))
		{
			$this->check_captcha();
		}
		$this->check_fields();

		if ($this->result())
			return;

		if($this->error_insert())
			return;

		$save = DB::query("INSERT INTO {faq} (created, mail, [anons], [name], cat_id, site_id, user_id) VALUES (%d, '%h', '%h', '%h', %d, %d, %d)",
			time(), $_POST["email"], $_POST["question"], $_POST["name"], $_POST["cat_id"], $this->site_id, $this->diafan->_users->id);

		$err = '';
		$this->result["files"] = '';
		$config = array('site_id' => $this->site_id, 'type' => 'configmodules');
		try
		{
			$result_upload = $this->diafan->_attachments->save($save, "faq", $config);
		}
		catch(Exception $e)
		{
			DB::query("DELETE FROM {faq} WHERE id=%d", $save);
			$this->diafan->_attachments->delete($save, 'faq');

			unset($this->result["altnames"]);

			Dev::$exception_field = 'attachments';
			Dev::$exception_result = $this->result;
			throw new Exception($e->getMessage());
		}
		
		if($result_upload)
		{
			$attachs = $this->diafan->_attachments->get($save, "faq");
			foreach ($attachs as $a)
			{
				if ($a["is_image"])
				{
					$this->result["files"] .= ' <a href="'.$a["link"].'">'.$a["name"].'</a> <a href="'.$a["link"].'"><img src="'.$a["link_preview"].'"></a>';
				}
				else
				{
					$this->result["files"] .= ' <a href="'.$a["link"].'">'.$a["name"].'</a>';
				}
			}
			if($this->diafan->configmodules("attachments_access_admin", 'faq', $this->site_id))
			{
				$this->result["files"] .= '<br>'.$this->diafan->_('Для просмотра файлов авторизуйтесь на сайте как администратор.', false);
			}
		}

		$this->send_mail();
		$this->send_sms();
		unset($this->result["files"]);

		$mes = $this->diafan->configmodules('add_message', 'faq', $this->site_id);
		$this->result["errors"][0] = $mes ? $mes : ' ';
		$this->result["result"] = 'success';
		$this->result["data"] = array("form" => false);
	}

	/**
	 * Валидация введенных данных
	 * 
	 * @return void
	 */
	private function check_fields()
	{
		if (empty($_POST['name']))
		{
			$this->result["errors"]["name"] = $this->diafan->_('Пожалуйста, введите имя.', false);
		}
		if (empty($_POST['question']))
		{
			$this->result["errors"]["question"] = $this->diafan->_('Пожалуйста, введите вопрос.', false);
		}
		if (! empty($_POST['email']))
		{
			Custom::inc('includes/validate.php');
			$mes = Validate::mail($_POST['email']);
			if ($mes)
			{
				$this->result["errors"]["email"] = $this->diafan->_($mes, false);
			}
		}
	}

	/**
	 * Проверяет на попытку отправить сообщение повторно
	 * 
	 * @return boolean
	 */
	private function error_insert()
	{
		$mes = '';
		$num = DB::query_result("SELECT COUNT(id) FROM {faq} where mail='%h' AND anons".$this->diafan->_languages->site."='%h'", $_POST["email"], $_POST["question"]);
		if ($num > 0)
		{ 
			$mes = $this->diafan->configmodules('error_insert_message', 'faq', $this->site_id);
			$this->result["errors"][0] = $mes ? $mes : ' ';
		}
		return $this->result();
	}

	/**
	 * Уведомляет администратора по e-mail
	 * 
	 * @return void
	 */
	private function send_mail()
	{
		if (! $this->diafan->configmodules("sendmailadmin", 'faq', $this->site_id))
			return;
			
		$subject = str_replace(
			array('%title', '%url'),
			array(TITLE, BASE_URL),
			$this->diafan->configmodules("subject_admin", 'faq', $this->site_id)
		);
			
		$message = str_replace(
			array('%name', '%title', '%url', '%question', '%email', '%files'),
			array(
				$this->diafan->filter($_POST, "string", "name"),
				TITLE,
				BASE_URL,
				$this->diafan->filter($_POST, "string", "question"),
				$this->diafan->filter($_POST, "string", "email"),
				($this->result["files"] ? $this->result["files"] : '-')
			),
			$this->diafan->configmodules("message_admin", 'faq', $this->site_id)
		);

		$to   = $this->diafan->configmodules("emailconfadmin", 'faq', $this->site_id)
		        ? $this->diafan->configmodules("email_admin", 'faq', $this->site_id)
		        : EMAIL_CONFIG;
		$from = $this->diafan->configmodules("emailconf", 'faq', $this->site_id)
		        ? $this->diafan->configmodules("email", 'faq', $this->site_id)
		        : EMAIL_CONFIG;

		$this->diafan->_postman->message_add_mail($to, $subject, $message, $from);
	}

	/**
	 * Отправляет администратору SMS-уведомление
	 * 
	 * @return void
	 */
	private function send_sms()
	{
		if (! $this->diafan->configmodules("sendsmsadmin", 'faq', $this->site_id))
			return;
			
		$message = $this->diafan->configmodules("sms_message_admin", 'faq', $this->site_id);

		$to = $this->diafan->configmodules("sms_admin", 'faq', $this->site_id);

		$this->diafan->_postman->message_add_sms($message, $to);
	}
}