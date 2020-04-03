<?php
/**
 * Подключение модуля «Список пожеланий»
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
if ( ! defined('DIAFAN'))
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

class Wishlist_inc extends Diafan
{
	/*
	 * @var array информация, записанная в список пожеланий
	 */
	private $wishlist = 'no_check';

	/**
	 * Конструктор класса
	 * 
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		$this->diafan = &$diafan;
		$this->init();
	}

	/**
	 * Возвращает информацию из списка пожеланий
	 *
	 * @param integer $id идентификатор товара
	 * @param mixed $param характеристики товара, учитываемые в заказе
	 * @param mixed $additional_cost сопутствующие услуги
	 * @param string $name_info тип информации (count - количество, is_file - это товар-файл)
	 * @return mixed
	 */
	public function get($id = 0, $param = false, $additional_cost= false, $name_info = '')
	{
		if(! $id)
		{
			return $this->wishlist;
		}
		if(empty($this->wishlist[$id]))
		{
			return false;
		}
		if($param === false)
		{
			if($name_info == "count")
			{
				$count = 0;
				foreach ($this->wishlist[$id] as $rows)
				{
					foreach($rows as $row)
					{
						$count += $row["count"];
					}
				}
				return $count;
			}
			return $this->wishlist[$id];
		}

		if(is_array($param))
		{
			asort($param);
			$param = serialize($param);
		}

		if(empty($this->wishlist[$id][$param][$additional_cost]))
		{
			return false;
		}
		if(! $name_info)
		{
			return $this->wishlist[$id][$param][$additional_cost];
		}
		if(empty($this->wishlist[$id][$param][$additional_cost][$name_info]))
		{
			return false;
		}
		return $this->wishlist[$id][$param][$additional_cost][$name_info];
	}

	/**
	 * Возвращает количество товаров в списке пожеланий
	 * 
	 * @return integer
	 */
	public function get_count()
	{
	    if($this->diafan->_users->id)
		{
			$count = DB::query_result("SELECT SUM(count) FROM {shop_wishlist} WHERE user_id='%s' AND trash='0'", $this->diafan->_users->id);
			return (empty($count) ? 0 : $count);
		}
		else
	    {
	    	$count = DB::query_result("SELECT SUM(count) FROM {shop_wishlist} WHERE session_id='%s' AND trash='0'", session_id());
	    	return (empty($count) ? 0 : $count);
	    }
	}

	/**
	 * Записывает данные в список пожеланий
	 * 
	 * @param mixed $value данные
	 * @param integer $id номер товра
	 * @param mixed $param характеристики товара, учитываемые в заказе
	 * @param mixed $additional_cost сопутствующие услуги
	 * @param string $name_info тип информации (count - количество, is_file - это товар-файл)
	 * @return void
	 */
	public function set($value = array(), $id = 0, $param = false, $additional_cost = false, $name_info = '')
	{
		if(! $id)
		{
			$this->wishlist = $value;
			return;
		}

		if($param === false)
		{
			if($value)
			{
				$this->wishlist[$id] = $value;
			}
			else
			{
				unset($this->wishlist[$id]);
			}
			return;
		}

		if(is_array($param))
		{
			$params = $param;
			asort($param);
			$param = serialize($param);
		}
		else
		{
			$params = unserialize($param);
		}

		$price = $this->diafan->_shop->price_get($id, $params);
		if (! $price && ! $this->diafan->configmodules('buy_empty_price', "shop"))
		{
			return $this->diafan->_('Товара с заданными параметрами не существует.');
		}

		if(! $name_info)
		{
			if(! $value)
			{
				unset($this->wishlist[$id][$param][$additional_cost]);
				if(! $this->wishlist[$id][$param])
				{
					unset($this->wishlist[$id][$param]);
				}
				if(! $this->wishlist[$id])
				{
					unset($this->wishlist[$id]);
				}
				return;
			}
			else
			{
				$this->wishlist[$id][$param][$additional_cost]["is_file"] = $value["is_file"] ? true : false;
				$name_info = "count";
				$value = $value["count"];
			}
		}
		if($name_info == "count")
		{
			$value = preg_replace('/[^0-9\.]+/', '', $value);
			if($value <= 0)
			{
				unset($this->wishlist[$id][$param][$additional_cost]);
				if(! $this->wishlist[$id][$param])
				{
					unset($this->wishlist[$id][$param]);
				}
				if(! $this->wishlist[$id])
				{
					unset($this->wishlist[$id]);
				}
				return;
			}
			//товар-файл => можно купить только 1 товар
			if(! empty($this->wishlist[$id][$param][$additional_cost]["is_file"]) && $value > 1)
			{
				$value = 1;
			}
		}
		$this->wishlist[$id][$param][$additional_cost][$name_info] = $value;
	}

	/**
	 * Записывает информацию в хранилище
	 * 
	 * @return void
	 */
	public function write()
	{
		$old_wishlist = array();
		$rows = DB::query_fetch_all("SELECT * FROM {shop_wishlist} WHERE ".($this->diafan->_users->id ? "user_id=".$this->diafan->_users->id : "session_id='".$this->diafan->_session->id."'")." AND trash='0'");
		foreach ($rows as $row)
		{
			$old_wishlist[$row["good_id"]][$row["param"]][$row["additional_cost"]] = $row;
		}
		foreach ($this->wishlist as $id => $rows)
		{
			foreach ($rows as $param => $rs)
			{
				if(! $rs)
					continue;
				foreach ($rs as $additional_cost => $row)
				{
					if(! empty($old_wishlist[$id][$param][$additional_cost]))
					{
						if($row["count"] != $old_wishlist[$id][$param][$additional_cost]["count"])
						{
							DB::query("UPDATE {shop_wishlist} SET created=%d, count=%f WHERE id=%d", time(), $row["count"], $old_wishlist[$id][$param][$additional_cost]["id"]);
						}
						unset($old_wishlist[$id][$param][$additional_cost]);
					}
					else
					{
						DB::query("INSERT INTO {shop_wishlist} (good_id, created, count, param, additional_cost, is_file, ".($this->diafan->_users->id ? "user_id" : "session_id").") VALUES (%d, %d, %f, '%s', '%s', '%d', ".($this->diafan->_users->id ? $this->diafan->_users->id : "'".$this->diafan->_session->id."'").")", $id, time(), $row["count"], $param, $additional_cost, (! empty($row["is_file"]) ? 1 : 0));
					}
				}
			}
		}
		foreach ($old_wishlist as $id => $rows)
		{
			foreach ($rows as $rs)
			{
				foreach ($rs as $row)
				{
					DB::query("DELETE FROM {shop_wishlist} WHERE id=%d", $row["id"]);
				}
			}
		}
	}

	/**
	 * Инициализация списка пожеланий
	 * 
	 * @return void
	 */
	private function init()
	{
		if($this->wishlist === 'no_check')
		{
			$wishlist = array();
			$rows = DB::query_fetch_all("SELECT * FROM {shop_wishlist} WHERE session_id='%h' AND trash='0'", $this->diafan->_session->id);
			foreach ($rows as $row)
			{
				$wishlist[$row["good_id"]][$row["param"]][$row["additional_cost"]]["count"] = $row["count"];
				$wishlist[$row["good_id"]][$row["param"]][$row["additional_cost"]]["is_file"] = $row["is_file"];
			}
			$this->wishlist = array();
			if($this->diafan->_users->id)
			{
				$rows = DB::query_fetch_all("SELECT * FROM {shop_wishlist} WHERE user_id=%d AND trash='0'", $this->diafan->_users->id);
				foreach ($rows as $row)
				{
					$this->wishlist[$row["good_id"]][$row["param"]][$row["additional_cost"]]["count"] = $row["count"];
					$this->wishlist[$row["good_id"]][$row["param"]][$row["additional_cost"]]["is_file"] = $row["is_file"];
				}
				if($wishlist)
				{
					foreach ($wishlist as $id => $rows)
					{
						foreach ($rows as $param => $rs)
						{
							foreach ($rs as $additional_cost => $row)
							{
								$this->set($row, $id, $param, $additional_cost);
							}
						}
					}
					$this->write();
					//DB::query("DELETE FROM {shop_wishlist} WHERE session_id='%h' AND trash='0'", $this->diafan->_session->id);
				}
			}
			else
			{
				$this->wishlist = $wishlist;
			}
		}
	}
}