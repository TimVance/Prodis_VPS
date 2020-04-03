<?php
/**
 * Обрабатывает полученные данные из формы
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

class Balance_action extends Action 
{
	/**
	 * Пополнение баланса
	 * 
	 * @return void
	 */
	public function recharge()
	{
		if(empty($_POST["summ"]))
		{
			$this->result["errors"]["summ"] = $this->diafan->_('Введите сумму.');
			return;
		}
		$pay_id = $this->diafan->_payment->add_pay($this->diafan->_users->id, 'balance', $_POST["payment_id"], $_POST["summ"]);

		$payment = $this->diafan->_payment->get($_POST["payment_id"]);		
		if($payment["payment"])
		{			
			$this->result["redirect"] = BASE_PATH_HREF.str_replace('ROUTE_END', '', $this->diafan->_route->link($this->diafan->_site->id, 0, "balance", 'element', false)).'/step2'.ROUTE_END;
		}
		else
		{
			$this->result["data"] = array(
				'form' => $this->diafan->configmodules('mes', 'balance'),
			);
		}

		$this->send_mails($pay_id, $payment);
		$this->send_sms();

		$this->result["result"] = "success";	
	}

	/**
	 * Отправляет письма администратору сайта и пользователю, пополнившему баланс
	 *
	 * @param integer $pay_id номер платежа
	 * @param array $payment платежная система
	 * @return void
	 */
	private function send_mails($pay_id, $payment)
	{
		$this->model->form();
		$this->model->result["hide_form"] = true;		
		$payment_name = '';

		if($payment)
		{
			$payment_name = $payment["name"];
			if($payment["payment"] == 'non_cash')
			{
				$code = DB::query_result("SELECT code FROM {payment_history} WHERE id=%d", $pay_id);

				$payment_name .= ', <a href="'.BASE_PATH.'payment/get/non_cash/ul/'.$pay_id.'/'.$code.'/">'.$this->diafan->_('Счет для юридических лиц', false).'</a>,
				<a href="'.BASE_PATH.'payment/get/non_cash/fl/'.$pay_id.'/'.$code.'/">'.$this->diafan->_('Квитанция для физических лиц', false).'</a>';
			}
		}
	
		//send mail admin
		$subject = str_replace(array('%title', '%url', '%id'),
				   array(TITLE, BASE_URL, $pay_id),
				   $this->diafan->configmodules('subject_admin', 'balance')
				  );

		$message = str_replace(
			array('%title', '%url', '%fio', '%payment', '%id'),
			array(
				TITLE,
				BASE_URL,
				$this->diafan->_users->fio,
				$payment_name,
				$pay_id
			),
			$this->diafan->configmodules('message_admin', 'balance'));

		$this->diafan->_postman->message_add_mail(
				$this->diafan->configmodules("emailconfadmin", 'balance') ? $this->diafan->configmodules("email_admin", 'balance') : EMAIL_CONFIG,
				$subject,
				$message,
				$this->diafan->configmodules("emailconf", 'balance') ? $this->diafan->configmodules("email", 'balance') : EMAIL_CONFIG
			);

		//send mail user
		if ($this->diafan->_users->mail)
		{
			return;
		}		
		
		$subject = str_replace(
				array('%title', '%url', '%id'),
				array(TITLE, BASE_URL, $pay_id),
				$this->diafan->configmodules('subject', 'balance')
			);

		$message = str_replace(
				array('%title', '%url', '%fio', '%payment', '%id'),
				array(
					TITLE,
					BASE_URL,
					$this->diafan->_users->fio,
					$payment_name,
					$pay_id
				),
				$this->diafan->configmodules('message', 'balance')
			);

		$this->diafan->_postman->message_add_mail(
			$this->diafan->_users->mail,
			$subject,
			$message,
			$this->diafan->configmodules("emailconf", 'balance') ? $this->diafan->configmodules("email", 'balance') : EMAIL_CONFIG
		);
	}

	/**
	 * Отправляет администратору SMS-уведомление
	 * 
	 * @return void
	 */
	private function send_sms()
	{
		if (! $this->diafan->configmodules("sendsmsadmin", 'balance'))
			return;
			
		$message = $this->diafan->configmodules("sms_message_admin", 'balance');

		$to = $this->diafan->configmodules("sms_admin", 'balance');

		$this->diafan->_postman->message_add_sms($message, $to);
	}
}