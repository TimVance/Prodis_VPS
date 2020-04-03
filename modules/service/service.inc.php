<?php
/**
 * Подключение модуля «Модули и БД»
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

/**
 * Service_inc
 */
class Service_inc extends Diafan
{
  /**
	 * Определяет переменные
	 *
	 * @param string $name название переменной
	 * @return mixed
	 */
	public function __get($name)
	{
		if (! isset($this->cache["var"][$name]))
		{
			switch($name)
			{
				case 'proc_uid':      // маркер процесса
					$this->cache["var"][$name] = isset($_SESSION[__CLASS__][$name]) ? $_SESSION[__CLASS__][$name] : false;
					break;

				case 'busy_proc_uid': // маркер блокировки процесса
          $cache_meta = array("name" => $name, "prefix" => "inc");
          $value = $this->diafan->_cache->get($cache_meta, 'service', CACHE_GLOBAL);
					$this->cache["var"][$name] = $value ?: false;
					break;

				default:
					$this->cache["var"][$name] = null;
					break;
			}
		}
		return $this->cache["var"][$name];
	}

	/**
	 * Сохраняет переменные
	 *
	 * @param string $name название переменной
	 * @param mixed $value значение переменной
	 * @return void
	 */
	public function __set($name, $value)
	{
		switch($name)
		{
			case 'proc_uid':
				if(empty($value))
				{
					if(isset($_SESSION[__CLASS__][$name])) unset($_SESSION[__CLASS__][$name]);
					if(isset($this->cache["var"][$name])) unset($this->cache["var"][$name]);
				}
				else
				{
					$this->cache["var"][$name] = $_SESSION[__CLASS__][$name] = $value;
				}
				break;

			case 'busy_proc_uid':
        $cache_meta = array("name" => $name, "prefix" => "inc");
        $this->diafan->_cache->save($value, $cache_meta, 'service', CACHE_GLOBAL);
				if(empty($value))
        {
          if(isset($this->cache["var"][$name])) unset($this->cache["var"][$name]);
        }
				else
        {
          $this->cache["var"][$name] = $value;
        }
				break;

			default:
				$this->cache["var"][$name] = $value;
				break;
		}
	}

  /**
	 * Получает список всех модулей, поддерживающих импорт/экспорт
	 *
	 * @return void
	 */
	public function modules_express()
	{
    if(! isset($this->cache["modules"]))
    {
      $this->cache["modules"] = array(); $sort = 0;
      if(! isset($this->cache["all_modules"]))
      {
        if(defined('IS_ADMIN') && IS_ADMIN) $this->cache["all_modules"] = $this->diafan->all_modules;
        else $this->cache["all_modules"] = DB::query_fetch_all("SELECT * FROM {modules} ORDER BY id ASC");
      }
  		foreach ($this->cache["all_modules"] as $key => $value)
  		{
  			if(! empty($value["module_name"]) && $value["module_name"] == "core")
  			{
  				continue;
  			}
        if(empty($value["module_name"]) || empty($value["name"]) || $value["module_name"] != $value["name"])
  			{
  				continue;
  			}
        if(! $this->is_express($value["module_name"], 'import') || ! $this->is_express($value["module_name"], 'export'))
        {
          continue;
        }

        $value["sort"] = ++$sort;
  			$this->cache["modules"][] = $value;
  		}

      // сортировка: первый в списки обладает большим приоритетом при ранжировании
      $priority_array = array_reverse(
        array(
          'shop', 'reviews', 'comments', 'news', 'clauses', 'faq', 'forum', 'ab',
          'bs', 'photo', 'votes', 'tags', 'keywords', 'subscription'
        ), false
      ); $sort++;
      foreach($this->cache["modules"] as $key => $value)
      {
        if(! isset($value["name"]) || ! isset($value["sort"])) continue;
        if(FALSE === $index = array_search($value["name"], $priority_array, true)) continue;
        $this->cache["modules"][$key]["sort"] = $sort + $index;
      }
      usort($this->cache["modules"], function($a, $b){
        if(! isset($a["sort"]) || ! isset($b["sort"])) return 0;
        if($a["sort"] == $b["sort"]) return 0;
        return ($a["sort"] > $b["sort"]) ? -1 : 1;
      });
      foreach($this->cache["modules"] as $key => $value)
      {
        if(! isset($value["sort"])) continue;
        unset($this->cache["modules"][$key]["sort"]);
      }
    }
    return $this->cache["modules"];
	}

  /**
	 * Проверяет наличие в модулях файлов, описывающих классы импорта/экспорта
	 *
   * @param string $module_name имя модуля
	 * @param string $extension расширение
	 * @return string
	 */
	private function is_express($module_name, $extension)
	{
		if(! $module_name || ! $extension) return false;

		$e_type = 'express';
		$module_file = 'modules/'.$module_name.'/'.$module_name.($e_type ? '.'.$e_type : '').($extension ? '.'.$extension : '').'.php';
		if(! Custom::exists($module_file)) return false;

		return true;
	}

  /**
	 * Получает список таблиц базы данных
	 *
	 * @param string $field поле, которое должно присутствовать в таблице
	 * @param boolean $no_cache не использовать кэш
	 * @return array
	 */
	public function get_tables($field = false, $no_cache = false)
	{
		if(empty($this->cache["tables"]) || empty($this->cache["tables"][$field]) || $no_cache)
		{
			$this->cache["tables"][$field] = array();
			$url = parse_url(DB_URL);
			$dbname = substr($url['path'], 1);
			$rows = DB::query_fetch_all("SHOW TABLES FROM `".$dbname."`");
			foreach ($rows as $row)
			{
				foreach ($row as $k => $v)
				{
					if(DB_PREFIX && ! preg_match('/^'.preg_quote(DB_PREFIX, '/').'/', $v))
					continue;

					$table = $v;
					if(DB_PREFIX)
					{
						$table = preg_replace('/^'.preg_quote(DB_PREFIX, '/').'/', '', $table);
					}

					if($field && ! DB::query_fetch_value("SHOW COLUMNS FROM {".$table."} FROM `".$dbname."` WHERE Field='%s'", $field, 'Field'))
					continue;

					$this->cache["tables"][$field][$table] = DB::query_fetch_value("SHOW COLUMNS FROM {".$table."} FROM `".$dbname."` WHERE 1=1", 'Field');
					break;
				}
			}
		}
		return $this->cache["tables"][$field];
	}

  /**
	 * Возвращает, если не передано значение,
	 * устанавливает, если передано TRUE,
	 * или снимает, если передано FALSE, блокировку процесса.
	 * При установки/снятия блокировки процесса возвращает TRUE в случае успеха.
	 * Снять блокировку может только установивший ее процесс. Условие игнорируется, если вторым пораметром передано TRUE.
	 *
	 * @return mixed
	 */
	public function busy()
	{
		$args = func_get_args();
		if( empty($args) )
		{
			return ! (! $this->busy_proc_uid || $this->busy_proc_uid == $this->proc_uid);
		}
		if(empty($args[1]) && $this->busy_proc_uid && $this->busy_proc_uid != $this->proc_uid)
		{
			return false;
		}
		$busy = ! empty($args[0]);
		if($busy)
		{
			$uid = $this->diafan->uid();
			$this->busy_proc_uid = $uid;
			$this->proc_uid = $uid;
		}
		else
		{
			$this->busy_proc_uid = false;
			$this->proc_uid = false;
		}
		return true;
	}
}
