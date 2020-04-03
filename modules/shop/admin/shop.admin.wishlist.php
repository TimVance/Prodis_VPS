<?php
/**
 * Список желаний в административной части
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
 * Shop_admin_wishlist
 */
class Shop_admin_wishlist extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_wishlist';

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
		'count' => array(
			'name' => 'Кол-во',
			'type' => 'numtext',
			'sql' => true,
			'no_important' => true,
		),
		'good_id' => array(
			'name' => 'Товар',
			'sql' => true,
		),
		'additional_cost' => array(
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
		if(! isset($this->cache["goods"]))
		{
			foreach($this->diafan->rows as $r)
			{
				$good_ids[] = $r["good_id"];
				if($r["additional_cost"])
				{
					$additional_cost[] = $r["additional_cost"];
				}
			}
			$this->cache["goods"] = DB::query_fetch_key("SELECT id, [name], article FROM {shop} WHERE id IN (%s)", implode(",", $good_ids), "id");
			if(! empty($additional_cost))
			{
				$this->cache["additional_cost"] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_additional_cost} WHERE id IN (%s)", implode(",", $additional_cost), "id", "name");
			}
		}
		$name = '';
		if(! empty($this->cache["goods"][$row["good_id"]]))
		{
			$good = $this->cache["goods"][$row["good_id"]];
			$name = $good["name"].($good["article"] ? " ".$good["article"] : '');
		}
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
		if($row["additional_cost"])
		{
			$additional_cost = explode(",", $row["additional_cost"]);
			foreach($additional_cost as $a)
			{
				if(! empty($this->cache["additional_cost"][$a]))
				{
					$name .= ', '.$this->cache["additional_cost"][$a];
				}
			}
		}

		return '<div class="name"><a href="'.BASE_PATH_HREF.'shop/edit'.$row["good_id"].'/">'.$name.'</a></div>';
	}

	/**
	 * Выводит имя пользователя, отложившего заказ
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
		$text = '<div class="no_important">';
		if($row["user_id"] && ! empty($this->cache["prepare"]["users"][$row["user_id"]]))
		{
			$text .= '<a href="'.BASE_PATH_HREF.'users/edit'.$row["user_id"].'/">'.$this->cache["prepare"]["users"][$row["user_id"]].'</a>';
		}
		$text .= '</div><div></div>';
		return $text;
	}
}