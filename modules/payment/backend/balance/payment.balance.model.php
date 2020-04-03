<?php
/**
 * Действия при выборе оплаты балансом
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

class Payment_balance_model extends Diafan
{
	/**
     * Списание денег с баланса пользователя
     * 
     * @param array $params настройки платежной системы
     * @param array $pay данные о платеже
     * @return void|array
     */
	public function get($params, $pay)
	{
		if($pay["status"] == 'pay')
		{
			$this->diafan->_payment->success($pay, 'redirect');
		}

		$balance = $this->diafan->_balance->get();
		if($pay['summ'] > $balance) // недостаточно средств
		{
			$result["text"] = $this->diafan->_('Ошибка операции! Недостаточно средств на счете.');
			return $result;
		}

		// вычитание суммы из баланса
		$this->diafan->_balance->set(0, $pay['summ'], 'minus');

		$this->diafan->_payment->success($pay);
	}
}