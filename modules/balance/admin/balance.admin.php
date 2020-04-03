<?php
/**
 * Редактирование баланса пользователей
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
 * Balance_admin
 */
class Balance_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'balance';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'user_id' => array(
				'type' => 'function',
				'name' => 'Имя пользователя',
			),
			'summ' => array(
				'type' => 'text',
				'name' => 'Средства на балансе',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'summ' => array(
			'name' => 'Сумма',
			'sql' => true,
		),
		'user_id' => array(
			'name' => 'Пользователь',
			'sql' => true,
		),
		'actions' => array(
			'del' => true,
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить');
	}

	/**
	 * Выводит список методов оплаты
	 * @return void
	 */
	public function show()
	{
		if(! in_array('payment', $this->diafan->installed_modules))
		{
			echo '<div class="error">'.$this->diafan->_('Для корректной работы модуля необходимо установить модуль «Оплата».').'</div>';
		}
		else
		{
			$this->diafan->list_row();
		}
	}

	/**
	 * Выводит имя пользователя в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_user_id($row, $var)
	{
		if(! isset($this->cache["prepare"]["users"]))
		{
			$user_ids = array();
			foreach($this->diafan->rows as $r)
			{
				if($r["user_id"] && ! in_array($r["user_id"], $user_ids))
				{
					$user_ids[] = $r["user_id"];
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
		$text = '<div class="name">';
		if($row["user_id"] && ! empty($this->cache["prepare"]["users"][$row["user_id"]]))
		{
			$text .= '<a href="'.BASE_PATH_HREF.'users/edit'.$row["user_id"].'/">'.$this->cache["prepare"]["users"][$row["user_id"]].'</a>';
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит сумму на балансе в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_summ($row, $var)
	{
		$text = '<div><a href="';
		$text .= $this->diafan->get_base_link($row);
		$text .= '" title="'.$this->diafan->_('Редактировать').' ('.$row["id"].')">';
		if($row["summ"])
		{
			$text .= $row["summ"].' '.$this->diafan->configmodules("currency", "balance");
		}
		else
		{
			$text .= $this->diafan->_('Редактировать');
		}
		$text .= '</a></div>';
		return $text;
	}	

	/**
	 * Валидация поля "Пользователь"
	 * 
	 * @return void
	 */
	public function validate_variable_user_id()
	{
		if(empty($_POST["user_id"]))
		{
			$this->diafan->set_error("user_id", 'Пользователь обязательно должен быть задан.');
		}
		elseif(DB::query_result("SELECT id FROM {balance} WHERE user_id=%d AND id<>%d", $_POST["user_id"], $this->diafan->id))
		{
			$this->diafan->set_error("user_id", 'Для выбранного пользователя уже есть запись с балансом.');
		}
	}
}