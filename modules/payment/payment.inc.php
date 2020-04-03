<?php
/**
 * Модель модуля «Методы оплаты»
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

class Payment_inc extends Diafan
{
	public $code;

	/**
	 * Получает список платежных систем
	 * @param string $where условие для SQL-запроса
	 * @return array
	 */
	public function get_all($where = '')
	{
		$rows = DB::query_fetch_all("SELECT id, [name], [text], payment FROM {payment} WHERE [act]='1' AND trash='0' ".$where." ORDER BY sort ASC");
		return $rows;
	}

	/**
	 * Возвращает название метода оплаты и его тип по ID
	 * 
	 * @param integer $id ид метода оплаты
	 * @return array
	 */
	public function get($id)
	{		
		return DB::query_fetch_array("SELECT [name], payment FROM {payment} WHERE id=%d LIMIT 1", $id);
	}

	/**
	 * Добавление записи о новом платеже в историю платежей, возвращает ID записи
	 * 
	 * @param integer $element_id номер элемента, для которого будет совершен платеж
	 * @param string $module_name модуль, осуществляющий запрос
	 * @param integer $payment_id ID способа оплаты
	 * @param float $summ сумма платежа
	 * @return integer
	 */
	public function add_pay($element_id, $module_name, $payment_id, $summ)
	{
		$this->code = md5(mt_rand(0, 999999999));
		return DB::query("INSERT INTO {payment_history} (created, status, element_id, payment_id, summ, module_name, code) VALUES (%d, 'request_pay', %d, '%d', %f, '%s', '%s')", time(), $element_id, $payment_id, $summ, $module_name, $this->code);
	}

	/**
	 * Добавление или обновление записи о платеже в историю платежей, возвращает ID записи
	 * 
	 * @param integer $element_id номер элемента, для которого будет совершен платеж
	 * @param string $module_name модуль, осуществляющий запрос
	 * @param integer $payment_id ID способа оплаты
	 * @param float $summ сумма платежа
	 * @return integer
	 */
	public function update_pay($element_id, $module_name, $payment_id, $summ)
	{
		$id = DB::query_result("SELECT id FROM {payment_history} WHERE element_id=%d AND module_name='%s'", $element_id, $module_name);
		if($id)
		{
			DB::query("UPDATE {payment_history} SET payment_id=%d, summ=%f WHERE id=%d", $payment_id, $summ, $id);
			return $id;
		}
		else
		{
			$id = $this->add_pay($element_id, $module_name, $payment_id, $summ);
		}
	}

	/**
	 * Генерирует данные для второго шага в оформлении заказа: оплата
	 * 
	 * @param string $element_id ID заказа
	 * @param string $module_name модуль, осуществляющий запрос
	 * @param string $code код доступа
	 * @return array
	 */
	public function get_pay($element_id, $module_name, $code = '')
	{
		$pay = DB::query_fetch_array("SELECT * FROM {payment_history} WHERE element_id=%d AND module_name='%s'".($code ? " AND code='%s'" : '')." ORDER BY id DESC LIMIT 1", $element_id, $module_name, $code);
		if(! $pay)
		{
			Custom::inc('includes/404.php');
		}
		
		$payment = DB::query_fetch_array("SELECT payment, [text], params FROM {payment} WHERE id=%d LIMIT 1", $pay['payment_id']);
		if(! $payment)
		{
			Custom::inc('includes/404.php');
		}
		$module_name_config = $module_name;
		if($module_name == 'cart')
		{
			$module_name_config = 'shop';
		}

		if($payment["payment"])
		{
			$params = unserialize($payment["params"]);

			if($pay['module_name'] == 'cart')
			{
				$pay["details"] = $this->diafan->_order->details($pay['element_id']);
			}	
			elseif($pay["module_name"] == 'balance')
			{
				$pay["details"] = $this->diafan->_balance->details($pay['element_id']);
			}

			$pay["text"] = str_replace(
				array('%id', '%summ'),
				array($element_id, $pay["summ"]),
				$this->diafan->configmodules('mes', $module_name_config)
			);
			if($pay["text"])
			{
				$pay["text"] .= '<br><br>'.$payment["text"];
			}
			$pay["desc"] = str_replace(
				'%id',
				$element_id,
				$this->diafan->configmodules("desc_payment", $module_name_config)
			);

			Custom::inc('modules/payment/backend/'.$payment["payment"].'/payment.'.$payment["payment"].'.model.php');
			$class = 'Payment_'.$payment["payment"].'_model';		
			$payment_class = new $class($this->diafan);

			$result = $payment_class->get($params, $pay);
			$result["payment"] = $payment["payment"];	
		}
		else
		{
			$result['message'] = str_replace(
				array('%id', '%summ'),
				array($element_id, $pay["summ"]),
				$this->diafan->configmodules('mes', $module_name_config)
			);
			if($payment["text"])
			{
				$result['message'] .= '<br><br>'.$payment["text"];
			}
		}

		return $result;
	}

	/**
	 * Проверяет наличие платежа, используется в конкретном методе оплаты
	 * 
	 * @param integer $id номер платежа
	 * @param string $payment платежная система
	 * @return array
	 */
	public function check_pay($id, $payment)
	{
		$pay = DB::query_fetch_array("SELECT * FROM {payment_history} WHERE id=%d LIMIT 1", $id);
		if (! $pay)
		{
			Custom::inc('includes/404.php');
		}
		if($pay["status"] == 'pay')
		{
			$this->success($pay, 'redirect');
		}
	
		$pay["payment"] = DB::query_fetch_array("SELECT * FROM {payment} WHERE id=%d AND payment='%s' LIMIT 1", $pay["payment_id"], $payment);
		if(! $pay["payment"])
		{
			Custom::inc('includes/404.php');
		}
		$pay["params"] = unserialize($pay["payment"]["params"]);
		return $pay;
	}

	/**
	 * Действия при успешной оплате
	 * 
	 * @param array $pay данные платежа
	 * @param string $type тип операции: all - все действия, pay - оплата, redirect - редирект на страницу платежа
	 * @return void
	 */
	public function success($pay, $type = 'all')
	{
		if($type == 'all' || $type == 'pay')
		{
			if($pay['module_name'] == 'cart')
			{
				$this->diafan->_order->pay($pay['element_id']);
			}	
			elseif($pay["module_name"] == 'balance')
			{
				$this->diafan->_balance->pay($pay);
			}
			DB::query("UPDATE {payment_history} SET status='pay', created=%d WHERE id=%d", time(), $pay["id"]);
		}

		if($type == 'all' || $type == 'redirect')
		{
			$order_rew = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='site' AND trash='0' AND element_type='element' AND element_id IN (SELECT id FROM {site} WHERE module_name='%s' AND [act]='1' AND trash='0')", $pay['module_name']);	
			$this->diafan->redirect('http'.(IS_HTTPS ? "s" : '').'://'.getenv("HTTP_HOST").'/'.(REVATIVE_PATH ? REVATIVE_PATH.'/' : '').$order_rew.'/step3/');
		}
	}

	/**
	 * Действия при неудачной оплате
	 * 
	 * @param array $pay данные платежа
	 * @return void
	 */
	public function fail($pay)
	{
		$order_rew = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='site' AND trash='0' AND element_type='element' AND element_id IN (SELECT id FROM {site} WHERE module_name='%s' AND [act]='1' AND trash='0')", $pay['module_name']);
		$this->diafan->redirect('http'.(IS_HTTPS ? "s" : '').'://'.getenv("HTTP_HOST").'/'.(REVATIVE_PATH ? REVATIVE_PATH.'/' : '').$order_rew.'/step4/');
	}
}