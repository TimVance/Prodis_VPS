<?php
/**
 * Макрос для групповой операции: клонирование
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
 * Shop_admin_group_clone
 */
class Shop_admin_group_clone extends Diafan
{
	/**
	 * Возвращает настройки
	 *
	 * @param string $value последнее выбранное групповое действие
	 * @return array|false
	 */
	public function show($value)
	{
		return array(
			'name' => 'Клонировать',
		);
	}

	/**
	 * Клонирует товары
	 *
	 * @return void
	 */
	public function action()
	{
		foreach ($_POST["ids"] as $id)
		{
			$id = intval($id);
			if(! $id)
				continue;

			$row = DB::query_fetch_array("SELECT * FROM {shop} WHERE id=%d LIMIT 1", $id);

			foreach ($row as $k => $v)
			{
				if ($k == 'name'.$this->diafan->_languages->site)
				{
					$v = $this->diafan->_('КОПИЯ').' '.$v;
				}
				$row[$k] = "'".str_replace("'", "\\'", $v)."'";
			}
			unset($row['id']);
			unset($row['counter_buy']);

			$n_id = DB::query('INSERT INTO {shop} ('.implode(',', array_keys($row)).') VALUES ('.implode(',', $row).')');

			$site_id = $row['site_id'];

			$rows = DB::query_fetch_all("SELECT cat_id, trash FROM {shop_category_rel} WHERE element_id='%d'", $id);
			foreach ($rows as $row)
			{
				DB::query("INSERT INTO {shop_category_rel} (element_id, cat_id, trash) VALUES (%d, %d, '%s')", $n_id, $row['cat_id'], $row['trash']);
			}

			$prices = array();
			$rows = DB::query_fetch_all("SELECT * FROM {shop_price} WHERE good_id=%d AND trash='0'", $id);
			foreach ($rows as $row)
			{
				$row['good_id'] = $n_id;
				$row_param = array();
				foreach ($row as $k => $v)
				{
					if($k != "id")
					{
						$row_param[$k] = "'".str_replace("'", "\\'", $v)."'";
					}
				}
				$price_id = DB::query('INSERT INTO {shop_price} ('.implode(',', array_keys($row_param)).') VALUES ('.implode(',', $row_param).')');
				if($row["id"] == $row["price_id"])
				{
					$prices[$row["price_id"]] = $price_id;

					$rows_param = DB::query_fetch_all("SELECT param_id, param_value FROM {shop_price_param} WHERE price_id=%d", $row["price_id"]);
					foreach ($rows_param as $row_param)
					{
						DB::query("INSERT INTO {shop_price_param} (price_id, param_id, param_value) VALUES (%d, %d, %d)", $price_id, $row_param["param_id"], $row_param["param_value"]);
					}
				}
			}
			foreach ($prices as $old => $new)
			{
				DB::query("UPDATE {shop_price} SET price_id=%d WHERE price_id=%d AND good_id=%d", $new, $old, $n_id);
			}

			$rows = DB::query_fetch_all("SELECT * FROM {images} WHERE element_id=%d AND element_type='element' AND module_name='shop' AND trash='0'", $id);
			foreach ($rows as $row)
			{
				$n = array();
				$m = array();
				$vs = array();
				foreach($row as $k => $v)
				{
					if($k == 'id')
						continue;

					$n[] = $k;
					switch($k)
					{
						case 'element_id':
							$m[] = "%d";
							$vs[] = $n_id;
							break;

						case 'tmpcode':
							$m[] = "''";
							break;

						case 'image_id':
							if($v)
							{
								$vs[] = $v;
							}
							else
							{
								$vs[] = $row["id"];
							}
							$m[] = "%d";
							break;

						case 'created':
							$m[] = "%d";
							$vs[] = time();
							break;

						default:
							$m[] = "'%s'";
							$vs[] = $v;
					}
				}
				$img_id = DB::query("INSERT INTO {images} (".implode(",", $n).") VALUES (".implode(",", $m).")", $vs);
				foreach ($prices as $old => $new)
				{
					$iid = DB::query_result("SELECT id FROM {shop_price_image_rel} WHERE price_id=%d AND image_id=%d", $old, $row['id']);
					if($iid)
					{
						DB::query("INSERT INTO {shop_price_image_rel} (`price_id`, `image_id`, `trash`) VALUES(%d, %d, '0')", $new, $img_id);
					}
				}
			}

			$rows = DB::query_fetch_all("SELECT * FROM {shop_param_element} WHERE element_id='%d' AND trash='0'", $id);
			foreach ($rows as $row)
			{
				unset($row["id"]);
				$row['element_id'] = $n_id;
				foreach ($row as $k => &$v)
				{
					$v = "'".str_replace("'", "\\'", $v)."'";
				}
				DB::query('INSERT INTO {shop_param_element} ('.implode(',', array_keys($row)).') VALUES ('.implode(',', $row).')');
			}
		}
	}
}