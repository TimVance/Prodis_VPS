<?php
/**
 * Список ожиданий
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
 * Shop_admin_waitlist
 */
class Shop_admin_waitlist extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_waitlist';

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
		'mail' => array(
			'type' => 'text',
			'sql' => true,
			'no_important' => true,
		),
		'good_id' => array(
			'name' => 'Товар',
			'sql' => true,
		),
		'user_id' => array(
			'name' => 'Пользователь',
			'sql' => true,
			'no_important' => true,
		),
		'param' => array(
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
			'trash' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'created' => array(
			'name' => 'Искать по дате',
			'type' => 'datetime_interval',
			'links' => true,
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
	 * Выводит товар
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_good_id($row, $var)
	{
		$params = unserialize($row["param"]);
		if(! isset($this->cache["goods"][$row["good_id"]]))
		{
			$good = DB::query_fetch_array("SELECT [name], article FROM {shop} WHERE id=%d LIMIT 1", $row["good_id"]);

			
			$this->cache["goods"][$row["good_id"]]["link"] = BASE_PATH_HREF.'shop/edit'.$row["good_id"].'/';

			$this->cache["goods"][$row["good_id"]]["name"] = $good["name"].($good["article"] ? " ".$good["article"] : '');
		}
		$name = $this->cache["goods"][$row["good_id"]]["name"];
		foreach ($params as $id => $value)
		{
			if (! isset($this->cache["param_names"][$id]))
			{
				$this->cache["param_names"][$id] = DB::query_result("SELECT [name] FROM {shop_param} WHERE id=%d LIMIT 1", $id);
			}
			if (! isset($this->cache["select_names"][$id][$value]))
			{
				$this->cache["select_names"][$id][$value] =
					DB::query_result("SELECT [name] FROM {shop_param_select} WHERE param_id=%d AND id=%d LIMIT 1", $id, $value);
			}
			$name .= ', '.$this->cache["param_names"][$id].': '.$this->cache["select_names"][$id][$value];
		}
		$link = $this->cache["goods"][$row["good_id"]]["link"];

		return '<div class="sum"><a href="'.$link.'">'.$name.'</a></div>';
	}

	/**
	 * Выводит имя пользователя, добавившего заявку
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
		$text = '<div class="sum">';
		if($row["user_id"] && ! empty($this->cache["prepare"]["users"][$row["user_id"]]))
		{
			$text .= '<a href="'.BASE_PATH_HREF.'users/edit'.$row["user_id"].'/">'.$this->cache["prepare"]["users"][$row["user_id"]].'</a>';
		}
		$text .= '</div>';
		return $text;
	}
}