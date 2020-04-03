<?php
/**
 * @package    DIAFAN.CMS
 *
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

/**
 * DB_EX
 *
 * Database extension
 */
class DB_EX extends Diafan
{
	const SEPARATOR = '-'; private function _prefix_tables($R130D64A4AD653C91E0FD80DE8FEADC3A){if(defined('IS_DEMO') && IS_DEMO && strpos($R130D64A4AD653C91E0FD80DE8FEADC3A, 'CREATE TABLE') === false){$R130D64A4AD653C91E0FD80DE8FEADC3A = str_replace('{sessions}', DB_PREFIX_DEMO.'sessions', $R130D64A4AD653C91E0FD80DE8FEADC3A);}return strtr($R130D64A4AD653C91E0FD80DE8FEADC3A, array('{'=>'`'.(defined('DB_PREFIX') ? DB_PREFIX : DB_PREFIX_DEMO), '}'=>'`'));}
	
	/**
	 * Получает первичные ключи таблицы
	 *
	 * @param string $table_name данные для подключения к базе данных
	 * @return array
	 */
	private function primary_keys($table_name)
	{
		$table_name = $this->_prefix_tables($table_name);
		if(! isset($this->cache["PRIMARY_KEYS"]) || ! isset($this->cache["PRIMARY_KEYS"][$table_name]))
		{
			$this->cache["PRIMARY_KEYS"][$table_name] = DB::query_fetch_value("SHOW KEYS FROM ".$table_name." WHERE Key_name = 'PRIMARY'", "Column_name");
		}
		return $this->cache["PRIMARY_KEYS"][$table_name];
	}

	/**
	 * Валидация идентификатора таблицы
	 *
	 * @param string $table_name данные для подключения к базе данных
	 * @param array $id идентификатор таблицы
	 * @param boolean $isset проверка наличия записи в тавблице уведомлений
	 * @return boolean
	 */
	private function valid_id($table_name, $id, $isset = false)
	{
		if(! is_array($id) || ! $primary_keys = $this->primary_keys($table_name))
		{
			return false;
		}
		$where = ' WHERE 1=1'; $keys = array();
		foreach($primary_keys as $primary_key)
		{
			if(! empty($primary_key) && ! empty($id[$primary_key]))
			{
				$where .= ' AND '.$primary_key.'=%d';
				$keys[$primary_key] = $id[$primary_key];
				continue;
			}
			return false;
		}
		if($isset)
		{
			if(! $row = DB::query_fetch_array("SELECT * FROM ".$table_name.$where." LIMIT 1", $keys))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Преобразует идентификатор записи таблицы
	 *
	 * @param string $table_name данные для подключения к базе данных
	 * @param mixed(array|string) $id идентификатор записи таблицы
	 * @return mixed(string|array)
	 */
	public function converter_id($table_name, $id)
	{
		if(is_array($id))
		{
			if(! $this->valid_id($table_name, $id))
			return false;

			$primary_keys = $this->primary_keys($table_name);
			foreach($primary_keys as $primary_key)
			{
				if(! empty($primary_key) && ! empty($id[$primary_key]))
				{
					$keys[$primary_key] = $id[$primary_key];
					continue;
				}
				return false;
			}
			return implode(self::SEPARATOR, $keys);
		}
		elseif(is_string($id))
		{
			$id = explode(self::SEPARATOR, $id);
			$primary_keys = $this->primary_keys($table_name);
			if(count($id) != count($primary_keys))
			return false;

			foreach($primary_keys as $primary_key)
			{
				$id_value = array_shift($id);
				if(! empty($primary_key) && ! empty($id_value))
				{
					$keys[$primary_key] = $id_value;
					continue;
				}
				return false;
			}

			if(! $this->valid_id($table_name, $keys))
			return false;

			return $keys;
		}

		return false;
	}

	/**
	 * Добавляет новую запись таблицы
	 *
	 * @param string $table_name данные для подключения к базе данных
	 * @param array $fields масив названий полей
	 * @param array $masks массив масок для значений
	 * @param array $values массив значений полей
	 * @param mixed(array|string) $id если определен, то принудительно задает идентификатор таблицы
	 * @param boolean $dev профилирование запроса
	 * @return string
	 */
	public function add_new($table_name, $fields = array(), $masks = array(), $values = array(), $id = false, $dev = false)
	{
		if($id)
		{
			if(is_string($id))
			{
				if(! $id = $this->converter_id($table_name, $id))
				{
					$id = false;
				}
			}
			elseif(is_array($id))
			{
				if(! $this->valid_id($table_name, $id))
				{
					$id = false;
				}
			}
			else
			{
				$id = false;
			}
			if($id)
			{
				$this->delete($table_name, $id);
			}
		}
		if($id)
		{
			DB::query(
				($dev ? "DEV " : "")."INSERT INTO ".$table_name." (`master_id`, `slave_id`, `id`".(! empty($fields) ? ', ' . implode(', ', $fields) : '').") VALUES ('%d', '%d', '%h'".(! empty($masks) ? ', ' . implode(', ', $masks) : '').");",
				array_merge(array($id["master_id"], $id["slave_id"], $id["master_id"]. self::SEPARATOR .$id["slave_id"]), $values)
			);
		}
		else
		{
			$id = array(
				'master_id' => time(),
				'slave_id' => 0,
			);
			$tb_name = preg_replace("/[^a-zA-Z0-9_-]/", '', trim(htmlspecialchars(strip_tags($table_name))));
			DB::query(
				($dev ? "DEV " : "")."SET @".$tb_name."_master_id=0, @".$tb_name."_slave_id=0;"
			);
			DB::query(
				($dev ? "DEV " : "")."INSERT INTO ".$table_name." (`master_id`, `slave_id`, `id`".(! empty($fields) ? ', ' . implode(', ', $fields) : '').") VALUES (@".$tb_name."_master_id:='%d', @".$tb_name."_slave_id:=((SELECT IFNULL(MAX(e.slave_id), 0) FROM ".$table_name." AS e WHERE e.master_id='%d') + 1), (CONCAT(@".$tb_name."_master_id,'". self::SEPARATOR ."',@".$tb_name."_slave_id))".(! empty($masks) ? ', ' . implode(', ', $masks) : '').");",
				array_merge(array($id["master_id"], $id["master_id"]), $values)
			);
			$id = DB::query_fetch_array(
				($dev ? "DEV " : "")."SELECT @".$tb_name."_master_id AS master_id, @".$tb_name."_slave_id AS slave_id LIMIT 1;"
			);
		}

		return $this->converter_id($table_name, $id);
	}

	/**
	 * Обновляет запись таблицы
	 *
	 * @param string $table_name данные для подключения к базе данных
	 * @param mixed(array|string) $id идентификатор таблицы
	 * @param array $fields масив названий полей
	 * @param array $values массив значений полей
	 * @param boolean $dev профилирование запроса
	 * @return string
	 */
	public function update($table_name, $id, $fields = array(), $values = array(), $dev = false)
	{
		if(is_string($id))
		{
			if(! $id = $this->converter_id($table_name, $id))
			{
				return false;
			}
		}
		elseif(is_array($id))
		{
			if(! $this->valid_id($table_name, $id))
			{
				return false;
			}
		}
		else
		{
			return false;
		}
		if(! $primary_keys = $this->primary_keys($table_name))
		{
			return false;
		}
		$where = ' WHERE 1=1'; $keys = array();
		foreach($primary_keys as $primary_key)
		{
			if(! empty($primary_key) && ! empty($id[$primary_key]))
			{
				$where .= ' AND '.$primary_key.'=%d';
				$keys[$primary_key] = $id[$primary_key];
				continue;
			}
			return false;
		}

		if(empty($fields))
		{
			return false;
		}

		$set = " SET ".implode(', ', $fields);
		$values = array_merge($values, $keys);
		DB::query(($dev ? "DEV " : "")."UPDATE ".$table_name. $set ." WHERE master_id='%d' AND slave_id='%d'", $values);

		return true;
	}

	/**
	 * Получает запись таблицы
	 *
	 * @param string $table_name данные для подключения к базе данных
	 * @param mixed(array|string) $id идентификатор таблицы
	 * @param boolean $dev профилирование запроса
	 * @return array
	 */
	public function get($table_name, $id, $dev = false)
	{
		if(is_string($id))
		{
			if(! $id = $this->converter_id($table_name, $id))
			{
				return false;
			}
		}
		elseif(is_array($id))
		{
			if(! $this->valid_id($table_name, $id))
			{
				return false;
			}
		}
		else
		{
			return false;
		}
		if(! $primary_keys = $this->primary_keys($table_name))
		{
			return false;
		}
		$where = ' WHERE 1=1'; $keys = array();
		foreach($primary_keys as $primary_key)
		{
			if(! empty($primary_key) && ! empty($id[$primary_key]))
			{
				$where .= ' AND '.$primary_key.'=%d';
				$keys[$primary_key] = $id[$primary_key];
				continue;
			}
			return false;
		}
		return DB::query_fetch_array(($dev ? "DEV " : "")."SELECT * FROM ".$table_name.$where." LIMIT 1", $keys);
	}

	/**
	 * Удаляет запись таблицы
	 *
	 * @param string $table_name данные для подключения к базе данных
	 * @param mixed(array|string) $ids идентификатор таблицы
	 * @param boolean $dev профилирование запроса
	 * @return boolean
	 */
	public function delete($table_name, $ids, $dev = false)
	{
		$del = array();
		if(is_string($ids))
		{
			$ids = $this->converter_id($table_name, $ids);
			if(false === $ids)
			{
				return false;
			}
			$del[] = $this->converter_id($table_name, $ids);
		}
		elseif(is_array($ids))
		{
			if(! $this->valid_id($table_name, $ids))
			{
				foreach($ids as $id)
				{
					if(is_string($id))
					{
						$id = $this->converter_id($table_name, $id);
						if(false === $id)
						{
							continue;
						}
						$del[] = $this->converter_id($table_name, $id);
					}
					elseif(is_array($id))
					{
						if(! $this->valid_id($table_name, $id))
						{
							continue;
						}
						$del[] = $this->converter_id($table_name, $id);
					}
					else
					{
						continue;
					}
				}
			}
			else
			{
				$del[] = $this->converter_id($table_name, $ids);
			}
		}
		else
		{
			return false;
		}
		if(empty($del))
		{
			return false;
		}

		$where = ' WHERE id IN ('.substr(str_repeat(",'%s'", count($del)), 1).')';

		DB::query(($dev ? "DEV " : "")."DELETE FROM ".$table_name.$where, $del);
		return true;
	}

	/**
	 * Приводит значение переменной к типу, соответстветствующему маске идентификатора таблицы
	 *
	 * @param string $id идентификатор таблицы
	 * @return string
	 */
	public function filter_uid($id)
	{
		return preg_replace("/[^0-9".self::SEPARATOR."]/", '', $id);
	}
}

/**
 * DB_EX_exception
 *
 * Исключение для работы с таблицей
 */
class DB_EX_exception extends Exception{}
