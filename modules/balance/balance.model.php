<?php
/**
 * Модель модуля «Баланс пользователя»
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

class Balance_model extends Model
{
	/**
	 * Выводит форму на страницу пополнения баланса
	 * 
	 * @return void
	 */
	public function form()
	{
		$this->result['balance'] = array(
			'summ'     => $this->diafan->_balance->get(),
			'currency' => $this->diafan->configmodules("currency", "balance")
		);
		$this->result['form_tag'] = 'balance';
		$this->form_errors($this->result, $this->result['form_tag'], array('summ'));

		$this->result["payments"] = $this->diafan->_payment->get_all("AND payment<>'balance'");
		$this->result["view"] = 'form';
	}

	/**
	 * Генерирует данные для второго шага при пополнении баланса: оплата
	 * 
	 * @return void
	 */
	public function payment()
	{
		$this->result = $this->diafan->_payment->get_pay($this->diafan->_users->id, 'balance');				
		$this->result["view"] = "payment";		
	}

	/**
	 * Генерирует данные для третьего шага в оформлении заказа: результат оплаты
	 * 
	 * @return void
	 */
	public function result()
	{
		if ($this->diafan->_route->step == 3)
		{
			$name = "payment_success_text";
		}
		else
		{
			$name = "payment_fail_text";
		}
		$this->result["text"] = $this->diafan->configmodules($name, "balance");

		$this->result["view"] = "result";
	}
}