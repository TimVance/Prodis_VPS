<?php
/**
 * Подключение модуля «Уведомления» для работы с сообщениями
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if ( ! defined('DIAFAN'))
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
 * Postman_inc_message
 */
class Postman_inc_message extends Diafan
{
	/**
	 * Добавляет письмо в список почтовых отправлений
	 *
	 * @param string|array $recipient получатель/получатели
	 * @param string $subject тема письма
	 * @param string $body содержание письма
	 * @param string $from адрес отправителя
	 * @return string
	 */
	public function add_mail($recipient, $subject, $body, $from = '')
	{
		if(! $id = $this->diafan->_postman->db_add($recipient, $subject, $body, $from, 'mail', true))
		{
			return false;
		}

		if($this->diafan->configmodules('auto_send', 'postman'))
		{
			if(! $this->diafan->configmodules('mail_defer', 'postman'))
			{
				$this->send($id);
			}
			else
			{
				$this->diafan->_postman->defer_init();
			}
		}

		return $id;
	}

	/**
	 * Добавляет SMS в список почтовых отправлений
	 *
	 * @param string $text текст SMS
	 * @param string $to номер получателя
	 * @return mixed string
	 */
	public function add_sms($text, $to)
	{
		$recipient = $to;
		$subject = '';
		$body = $text;
		$from = $this->diafan->configmodules("sms_provider", 'postman');

		if(! $id = $this->diafan->_postman->db_add($recipient, $subject, $body, $from, 'sms', true))
		{
			return false;
		}

		if($this->diafan->configmodules('auto_send', 'postman'))
		{
			if(! $this->diafan->configmodules('sms_defer', 'postman'))
			{
				$this->send($id);
			}
			else
			{
				$this->diafan->_postman->defer_init();
			}
		}

		return $id;
	}

	/**
	 * Отправляет уведомление
	 *
	 * @param mixed(array|string) $id идентификатор уведомления
	 * @return boolean
	 */
	public function send($id)
	{
		if(! $row = $this->diafan->_db_ex->get('{postman}', $id))
		{
			return false;
		}

		$status = false;
		$row["error"] = $row["trace"] = '';
		$this->diafan->_db_ex->update('{postman}', $row["id"], array("timesent='%d'", "status='%h'", "error='%s'", "trace='%s'"), array(time(), (! $status ? '2' : '1'), $row["error"], $row["trace"]));

		switch ($row["type"])
		{
			case 'mail':
				try {
					if(empty($row["recipient"]))
					{
						throw new Exception('Ошибка: для отправки уведомления необходимо указать адрес получателя.');
					}
					$status = $this->send_mail($row["recipient"], $row["subject"], $row["body"], $row["from"], $row["error"], $row["trace"]);
				} catch (Exception $e) {
					$row["error"] = $e->getMessage();
					$row["trace"] = '';
					$status = false;
				}
				break;

			case 'sms':
				try {
					if(empty($row["recipient"]))
					{
						throw new Exception('Ошибка: для отправки уведомления необходимо указать адрес получателя.');
					}
					if(! $this->diafan->configmodules("sms", 'postman'))
					{
						throw new Exception('Ошибка: для отправки уведомления необходимо настроить SMS-уведомления.');
					}
					$from = $this->diafan->configmodules("sms_provider", 'postman');
					$this->diafan->_db_ex->update('{postman}', $row["id"], array("`from`='%h'"), array($from));
					$status = $this->send_sms($row["body"], $row["recipient"], $row["error"], $row["trace"]);
				} catch (Exception $e) {
					$row["error"] = $e->getMessage();
					$row["trace"] = '';
					$status = false;
				}
				break;

			default:
				return false;
				break;
		}

		if(! $this->diafan->configmodules('del_after_send', 'postman') || ! $status)
		{
			$this->diafan->_db_ex->update('{postman}', $row["id"], array("timesent='%d'", "status='%h'", "error='%s'", "trace='%s'"), array(time(), (! $status ? '2' : '1'), $row["error"], $row["trace"]));
		}
		else
		{
			$this->diafan->_db_ex->delete('{postman}', $id);
		}

		return true;
	}

	/**
	 * Отправляет письмо
	 *
	 * @param string|array $recipient получатель/получатели
	 * @param string $subject тема письма
	 * @param string $body содержание письма
	 * @param string $from адрес отправителя
	 * @param string $error_output вывод ошибки
	 * @param string $trace_output вывод трассировки
	 * @return boolean
	 */
	private function send_mail($recipient, $subject, $body, $from = '', &$error_output = '', &$trace_output = '')
	{
		Custom::inc('plugins/class.phpmailer.php');

		$mail = new PHPMailer();

		if($this->diafan->configmodules("smtp_mail", 'postman')
		&& $this->diafan->configmodules("smtp_host", 'postman')
		&& $this->diafan->configmodules("smtp_login", 'postman')
		&& $this->diafan->configmodules("smtp_password", 'postman'))
		{
			$mail->isSMTP(); // telling the class to use SMTP
			$mail->Host       = $this->diafan->configmodules("smtp_host", 'postman');     // SMTP server
			$mail->SMTPDebug = 1;
			// $mail->SMTPDebug  = MOD_DEVELOPER ? 1 : 0; // enables SMTP debug information (for testing)
			// 								                           // 1 = errors and messages
			// 								                           // 2 = messages only
			$mail->SMTPAuth   = true;          // enable SMTP authentication
			if ($this->diafan->configmodules("smtp_port", 'postman'))
			{
				$mail->Port   = $this->diafan->configmodules("smtp_port", 'postman');       // set the SMTP port for the GMAIL server
			}
			$mail->Username   = $this->diafan->configmodules("smtp_login", 'postman');    // SMTP account username
			$mail->Password   = $this->diafan->configmodules("smtp_password", 'postman'); // SMTP account password

			// TO_DO: Don't mix up these modes; ssl on port 587 or tls on port 465 will not work.
			// TO_DO: PHPMailer 5.2.10 introduced opportunistic TLS - if it sees that the server is advertising TLS encryption (after you have connected to the server), it enables encryption automatically, even if you have not set SMTPSecure. This might cause issues if the server is advertising TLS with an invalid certificate, but you can turn it off with $mail->SMTPAutoTLS = false;.
			$mail->SMTPAutoTLS = false;

			// TO_DO: Failing that, you can allow insecure connections via the SMTPOptions property introduced in PHPMailer 5.2.10 (it's possible to do this by subclassing the SMTP class in earlier versions), though this is not recommended as it defeats much of the point of using a secure transport at all:
			$mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);
		}

		$mail->setFrom($from ? $from : $this->diafan->configmodules("email", 'postman'), TITLE);
		$mail->Subject = $subject;
		$mail->msgHTML($body);

		if (is_array($recipient))
		{
			foreach ($recipient as $to)
			{
				$mail->addAddress($to);
			}
		}
		elseif (strpos($recipient, ',') !== false)
		{
			$recipients = explode(',', $recipient);
			foreach ($recipients as $r)
			{
				$mail->addAddress(trim($r));
			}
		}
		else
		{
			$mail->addAddress($recipient);
		}

		ob_start();
		$mailssend = $mail->send();
		$trace_output = ob_get_contents();
		ob_end_clean();
		$error_output = $mail->ErrorInfo;
		return $mailssend;
	}

	/**
	 * Отправляет SMS
	 *
	 * @param string $text текст SMS
	 * @param string $to номер получателя
	 * @param string $error_output вывод ошибки
	 * @param string $trace_output вывод трассировки
	 * @return boolean
	 */
	private function send_sms($text, $to, &$error_output = '', &$trace_output = '')
	{
		if(! $this->diafan->configmodules("sms", 'postman'))
		{
			$error_output = "ERROR: SMS isn't enabled";
			return false;
		}
		$backend = $this->diafan->configmodules("sms_provider", 'postman');
		if(! $backend
		|| ! Custom::exists('modules/postman/backend/'.$backend.'/postman.'.$backend.'.sms.php'))
		{
			$error_output = "ERROR: no service provider defined";
			return false;
		}
		$to = preg_replace('/[^0-9]+/', '', $to);
		Custom::inc('includes/validate.php');
		if($error = Validate::phone($to))
		{
			$error_output = "ERROR: ".$error;
			return false;
		}
		$text = urlencode(str_replace("\n", "%0D", substr($text, 0, 800)));
		$backend = $this->diafan->configmodules("sms_provider", 'postman');
		if(! Custom::exists('modules/postman/backend/'.$backend.'/postman.'.$backend.'.sms.php'))
		{
			$error_output = "ERROR: unidentified service provider";
			return false;
		}
		Custom::inc('modules/postman/backend/'.$backend.'/postman.'.$backend.'.sms.php');
		
		$name_class = 'Postman_'.$backend.'_sms';
		$class = new $name_class($this->diafan);
		if (! is_callable(array(&$class, "send")))
		{
			$error_output = "ERROR: unidentified service provider";
			return false;
		}
		return $class->send($text, $to, $error_output, $trace_output);
	}
}

/**
 * Postman_message_exception
 *
 * Исключение для почтовых отправлений
 */
class Postman_message_exception extends Exception{}
