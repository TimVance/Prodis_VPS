<?php
/**
 * Макрос для групповой операции: Изменение статуса
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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
 * Order_admin_group_status
 */
class Order_admin_group_status extends Diafan
{
	/**
	 * Возвращает настройки
	 *
	 * @param string $value последнее выбранное групповое действие
	 * @return array|false
	 */
	public function show($value)
	{
		$config = array(
			'name' => 'Изменить статус',
		);

		$config['html'] = '<select name="group_status_id">';
		$rows = DB::query_fetch_all("SELECT * FROM {shop_order_status} WHERE trash='0' ORDER BY sort ASC");
		foreach($rows as $row)
		{
			$config['html'] .= '<option value="'.$row["id"].'">'.$row["name"._LANG].'</option>';
			
		}
		$config['html'] .= '</select>';

		return $config;
	}

	/**
	 * Изменение статуса
	 *
	 * @return void
	 */
	public function action()
	{
		if(empty($_POST["ids"]) || empty($_POST["group_status_id"]))
			return;

		$ids = $this->diafan->filter($_POST["ids"], "integer");
		
		$status = DB::query_fetch_array("SELECT * FROM {shop_order_status} WHERE id=%d LIMIT 1", $_POST["group_status_id"]);
		if(! $status)
			return;

		$rows = DB::query_fetch_all("SELECT * FROM {shop_order} WHERE id IN (%s)", implode(",", $ids));
		foreach($rows as $row)
		{
			$this->diafan->_order->set_status($row, $status);
		}
	}
}