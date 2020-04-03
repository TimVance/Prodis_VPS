<?php
/**
 * Подключение модуля «Баланс пользователя»
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

class Balance_inc extends Model
{
	/**
	 * Возврат текущего баланса пользователя
	 * 
	 * @param integer $user_id ID пользователя. По умолчанию текущий пользователь
	 * @return float
	 */
	public function get($user_id = false)
	{
		if(! $user_id)
		{
			$user_id = $this->diafan->_users->id;
		}
		if(! isset($this->cache["balance"][$user_id]))
		{
			$this->cache["balance"][$user_id] = DB::query_fetch_array("SELECT * FROM {balance} WHERE user_id=%d", $user_id);
		}
		$row = $this->cache["balance"][$user_id];

		if(! empty($row["summ"]))
		{
			return $row["summ"];
		}				
		return 0;
	}

	/**
	 * Возврат информаци о плательщике
	 * 
	 * @param integer $user_id ID пользователя
	 * @return array
	 */
	public function details($user_id)
	{
		$result["email"] = $this->diafan->_users->mail;
		$result["phone"] = $this->diafan->_users->phone;
		return $result;
	}

	/**
	 * Изменение текущего баланса пользователя
	 * 
	 * @param integer $user_id ID пользователя. По умолчанию текущий пользователь
	 * @param float $summ новая сумма
	 * @param string $type тип операции: *summ* – изменение всей суммы на балансе, *plus* – добавление, *minus* – вычитание
	 * @return void
	 */
	public function set($user_id, $summ, $type = 'summ')
	{
		if(! $user_id)
		{
			$user_id = $this->diafan->_users->id;
		}
		if(! isset($this->cache["balance"][$user_id]))
		{
			$this->cache["balance"][$user_id] = DB::query_fetch_array("SELECT * FROM {balance} WHERE user_id=%d", $user_id);
		}
		$row = $this->cache["balance"][$user_id];
		
		$balance_summ = 0;
		if($row["summ"])
		{
			$balance_summ = $row["summ"];
		}
		switch($type)
		{
			case 'summ':
				$balance_summ = $summ;
				break;

			case 'plus':
				$balance_summ += $summ;
				break;

			case 'minus':
				$balance_summ -= $summ;
				break;
		}
		if($row)
		{
			DB::query("UPDATE {balance} SET summ=%f WHERE id=%d", $balance_summ, $row["id"]);
		}
		else
		{
			DB::query("INSERT INTO {balance} (`user_id`, `summ`) VALUES (%d, %f)", $user_id, $balance_summ);
		}
	}

	/**
	 * Добавление средств на баланс пользователя
	 * 
	 * @param array $pay данные платежа
	 * @return void
	 */
	public function pay($pay)
	{
		$this->diafan->_balance->set($pay["element_id"], $pay["summ"], 'plus');
	}
}