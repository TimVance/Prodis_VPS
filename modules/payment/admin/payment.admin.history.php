<?php
/**
 * История платежей
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
 * Payment_admin_history
 */
class Payment_admin_history extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'payment_history';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'id' => array(
				'type' => 'function',
				'name' => 'Номер платежа',
				'no_save' => true,
			),
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата и время платежа',
				'no_save' => true,
				'disabled' => true,
			),
			'summ' => array(
				'type' => 'floattext',
				'name' => 'Сумма',
				'no_save' => true,
				'disabled' => true,
			),
			'element_id' => array(
				'type' => 'function',
				'name' => 'Объект',
				'no_save' => true,
			),
			'status' => array(
				'type' => 'select',
				'name' => 'Статус',
				'help' => 'При смене статуса на «Оплачено» произойдет списание товаров или зачисление средств на баланс.',
				"select" => array(
					"request_pay" => 'запрос платежа',
					"pay"         => 'оплачено',
				),
			),
			'payment_id' => array(
				'type' => 'select',
				'name' => 'Метод оплаты',
				'select_db' => array(
					'table' => 'payment',
					"name" => "[name]",
					"where" => "trash='0'",
					"order" => 'sort ASC',
				),
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'created' => array(
			'name' => 'Дата и время',
			'type' => 'datetime',
			'sql' => true,
			'no_important' => true,
		),
		'name' => array(
			'name' => 'Название',
			'variable' => 'id',
			'text' => 'Платеж № %d',
		),
		'status' => array(
			'type' => 'select',
			'name' => 'Статус',
			'sql' => true,
			'no_important' => true,
		),
		'summ' => array(
			'name' => 'Сумма',
			'sql' => true,
		),
		'element_id' => array(
			'type' => 'select',
			'name' => 'Статус',
			'sql' => true,
			'no_important' => true,
		),
		'payment_id' => array(
			'type' => 'select',
			'name' => 'Метод оплаты',
			'sql' => true,
			'no_important' => true,
		),
		'module_name' => array(
			'type' => 'none',
			'sql' => true,
			'no_important' => true,
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),				
		'actions' => array(
			'del' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'created' => array(
			'name' => 'Период',
			'type' => 'datetime_interval',
		),
		'summ' => array(
			'name' => 'Искать по сумме',
			'type' => 'numtext_interval',
		),
	);

	/**
	 * Выводит список заказов
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Выводит элемента
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_element_id($row, $var)
	{
		if(! isset($this->cache["prepare"]["users"]))
		{
			$user_ids = array();
			foreach($this->diafan->rows as $r)
			{
				if($r["module_name"] == 'balance' && $r["element_id"] && ! in_array($r["element_id"], $user_ids))
				{
					$user_ids[] = $r["element_id"];
				}
			}
			if($user_ids)
			{
				$this->cache["prepare"]["users"] = DB::query_fetch_key_value(
					"SELECT id, CONCAT(fio, ' (', name, ')') as fio FROM {users} WHERE id IN (%s) AND trash='0'",
					implode(",", $user_ids),
					"id", "fio"
				);
			}
		}
		$text = '<div class="no_important">';
		switch($row["module_name"])
		{
			case 'balance':
				if(! empty($this->cache["prepare"]["users"][$row["element_id"]]))
				{
					$text .= '<a href="'.BASE_PATH_HREF.'users/edit'.$row["element_id"].'/">'.$this->cache["prepare"]["users"][$row["element_id"]].'</a>';
				}
				break;

			case 'cart':
				$text .= '<a href="'.BASE_PATH_HREF.'order/edit'.$row["element_id"].'/">'.$this->diafan->_('Заказ №%s', $row["element_id"]).'</a>';
				break;
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит сумму платежа
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_summ($row)
	{
		$text = '<div class="sum">';
		if($row["summ"])
		{
			$text .= $row["summ"].' '.$this->diafan->configmodules("currency", "balance");
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит платежную систему
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_payment_id($row)
	{
		if(! isset($this->cache["prepare"]["payments"]))
		{
			$this->cache["prepare"]["payments"] = DB::query_fetch_key_value(
				"SELECT id, [name] FROM {payment}",
				"id", "name"
			);
		}
		$text = '<div class="no_important">';
		if($row["payment_id"] && ! empty($this->cache["prepare"]["payments"][$row["payment_id"]]))
		{
			$text .= $this->cache["prepare"]["payments"][$row["payment_id"]];
		}
		$text .= '</div>';
		return $text;
	}
	
	/**
	 * Редактирование поля "Объект"
	 * @return void
	 */
	public function edit_variable_element_id()
	{
		echo '
		<div class="unit">
			<b>
				'.$this->diafan->variable_name().':
			</b>';
		switch($this->diafan->values("module_name"))
		{
			case 'balance':
				echo '<a href="'.BASE_PATH_HREF.'users/edit'.$this->diafan->value.'/">'
				.DB::query_result("SELECT CONCAT(fio, ' (', name, ')') FROM {users} WHERE id=%d AND trash='0'", $this->diafan->value)
				.'</a>';
				break;

			case 'cart':
				echo '<a href="'.BASE_PATH_HREF.'order/edit'.$this->diafan->value.'/">'.$this->diafan->_('Заказ №%s', $this->diafan->value).'</a>';
				break;
		}
		echo '
			'.$this->diafan->help().'
		</div>';
	}
	
	/**
	 * Сохранение поля "Статус"
	 * @return void
	 */
	public function save_variable_status()
	{
		if($this->diafan->values('status') == 'request_pay' && $_POST["status"] == 'pay')
		{
			$pay = DB::query_fetch_array("SELECT * FROM {payment_history} WHERE id=%d", $this->diafan->id);
			$this->diafan->_payment->success($pay, 'pay');
		}
		else
		{
			$this->diafan->set_query("status='%h'");
			$this->diafan->set_value($_POST["status"]);
		}
	}
}