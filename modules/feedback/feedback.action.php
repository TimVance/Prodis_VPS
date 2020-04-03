<?php
/**
 * Обработка POST-запроса
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

class Feedback_action extends Action
{	
	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function add()
	{
		$this->check_site_id();

		if ($this->result())
			return;

		if ($this->diafan->_captcha->configmodules('feedback', $this->site_id))
		{
			$this->check_captcha();
		}
		$params = $this->model->get_params(array("module" => "feedback", "where" => "site_id=".$this->site_id));
		$this->empty_required_field(array("params" => $params));

		if ($this->result())
			return;

		$save = DB::query("INSERT INTO {feedback} (created, site_id, lang_id, url, user_id) VALUES (%d, %d, %d, '%h', %d)", time(), $this->site_id, _LANG, getenv('HTTP_REFERER'), $this->diafan->_users->id);

		if(! empty($_POST["tmpcode"]))
		{
			DB::query("UPDATE {images} SET element_id=%d, tmpcode='' WHERE module_name='feedback' AND element_id=0 AND tmpcode='%s'", $save, $_POST["tmpcode"]);
		}

		$this->insert_values(array("id" => $save, "table" => "feedback", "params" => $params));

		if ($this->result())
			return;

		$this->send_mail();
		$this->send_sms();

		$mes = $this->diafan->configmodules('add_message', 'feedback', $this->site_id, _LANG);
		$this->result["errors"][0] = $mes ? $mes : ' ';
		$this->result["result"] = 'success';
		$this->result["data"] = array("form" => false);
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
		if(empty($_POST["images_param_id"]))
		{
			return;
		}
		$param_id = $this->diafan->filter($_POST, "int", "images_param_id");

		$this->result["result"] = 'success';
		if (! empty($_FILES['images'.$param_id]) && $_FILES['images'.$param_id]['tmp_name'] != '' && $_FILES['images'.$param_id]['name'] != '')
		{
			try
			{
				$this->diafan->_images->upload(0, "feedback", 'element', 0, $_FILES['images'.$param_id]['tmp_name'], $this->diafan->translit($_FILES['images'.$param_id]['name']), false, $param_id, $_POST["tmpcode"]);
			}
			catch(Exception $e)
			{
				Dev::$exception_field = 'p'.$param_id;
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}
			$images = $this->diafan->_images->get('large', 0, "feedback", 'element', 0, '', $param_id, 0, '', $_POST["tmpcode"]);
			$this->result["data"] = $this->diafan->_tpl->get('images', "feedback", $images);
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
		$row = DB::query_fetch_array("SELECT * FROM {images} WHERE module_name='feedback' AND id=%d AND tmpcode='%s'", $_POST["id"], $_POST["tmpcode"]);
		if(! $row)
		{
			return;
		}
		$this->diafan->_images->delete_row($row);
	}

	/**
	 * Уведомление администратора по e-mail
	 * 
	 * @return void
	 */
	private function send_mail()
	{
		if (! $this->diafan->configmodules("sendmailadmin", 'feedback', $this->site_id))
			return;

		$subject = str_replace(
			array('%title', '%url'),
			array(TITLE, BASE_URL),
			$this->diafan->configmodules("subject_admin", 'feedback', $this->site_id)
		);

		$message = str_replace(
			array('%title', '%url', '%message'),
			array(
				TITLE,
				BASE_URL,
				$this->message_admin_param
			),
			$this->diafan->configmodules("message_admin", 'feedback', $this->site_id)
		);

		$to   = $this->diafan->configmodules("emailconfadmin", 'feedback', $this->site_id)
		        ? $this->diafan->configmodules("email_admin", 'feedback', $this->site_id)
		        : EMAIL_CONFIG;
		$from = $this->diafan->configmodules("emailconf", 'feedback', $this->site_id)
		        ? $this->diafan->configmodules("email", 'feedback', $this->site_id)
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
		if (! $this->diafan->configmodules("sendsmsadmin", 'feedback', $this->site_id))
			return;

		$message = $this->diafan->configmodules("sms_message_admin", 'feedback', $this->site_id);

		$to   = $this->diafan->configmodules("sms_admin", 'feedback', $this->site_id);

		$this->diafan->_postman->message_add_sms($message, $to);
	}
}