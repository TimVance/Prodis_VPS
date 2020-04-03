<?php
/**
 * Подключение модуля
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
 * Search_inc
 */
class Search_inc extends Diafan
{
	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var integer максимальное количество ссылок, индексируемых за один проход скрипта
	 */
	private $max_insert = 500;

	/**
	 * @var integer
	 */
	private $current_insert = 0;

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
				case 'langs':
					$this->cache["var"][$name] = array();
					foreach ($this->diafan->_languages->all as $row)
					{
						$this->cache["var"][$name][] = $row["id"];
					}
					break;

				case 'searchwords':
					Custom::inc('includes/searchwords.php');
					$this->cache["var"][$name] = new Searchwords();
					$this->cache["var"][$name]->max_length = $this->diafan->configmodules("max_length", "search");
					break;

				// страница сайта, проиндексированная за предыдущий проход скрипта
				case 'last_site_id':
					$this->cache["var"][$name] = $this->diafan->configmodules($this->type."_current_index_site", "search", 0);
					break;

				// таблица, проиндексированная за предыдущий проход скрипта
				case 'last_table':
					$this->cache["var"][$name] = $this->diafan->configmodules($this->type."_current_index_module_table", "search");
					break;

				default:
					// настройки модуля
					if(strpos($name, 'module_') === 0)
					{
						$module_name = str_replace('module_', '', $name);
						if (! Custom::exists('modules/'.$module_name.'/'.$module_name.'.search.php'))
						{
							$this->cache["var"][$name] = false;
						}
						else
						{
							Custom::inc('modules/'.$module_name.'/'.$module_name.'.search.php');
							$class = ucfirst($module_name).'_search_config';
							$obj = new $class();
							$this->cache["var"][$name] = $obj->config;
						}
					}
					else
					{
						$this->cache["var"][$name] = null;
					}
					break;
			}
		}
		return $this->cache["var"][$name];
	}

	/**
	 * Индексирует весь сайт
	 *
	 * @return void
	 */
	public function index_all()
	{
		$this->type = 'all';

		if(! $this->last_site_id)
		{
			DB::query("TRUNCATE TABLE {search_index}");
			DB::query("TRUNCATE TABLE {search_results}");
			DB::query("TRUNCATE TABLE {search_keywords}");
		}
		$config = $this->check_module_config('site', 'site');

		$rows = DB::query_fetch_all("SELECT * FROM {site} WHERE trash='0'".($this->last_site_id ? " AND id>=%d" : "")." ORDER BY id ASC LIMIT ".$this->max_insert, $this->last_site_id);

		foreach ($rows as $row)
		{
			if($this->last_site_id != $row["id"])
			{
				$this->index_item($row, $config, 'site');
				$this->check_max($row["id"], 'site', 0);
			}
			$this->index_site_module($row, false);
		}
		$this->diafan->configmodules($this->type."_current_index_module_table", "search", 0, false, '');
		$this->diafan->configmodules($this->type."_current_index_module_element", "search", 0, false, '');
		$this->diafan->configmodules($this->type."_current_index_site", "search", 0, false, '');
		$this->diafan->configmodules("full_index", "search", 0, false, true);
	}

	/**
	 * Индексирует модуль
	 *
	 * @param string $module_name название модуля
	 * @return void
	 */
	public function index_module($module_name)
	{
		$this->type = 'module_'.$module_name;
		$module_config = $this->check_module_config($module_name);

		if(empty($module_config))
		{
			return;
		}

		if(! $this->last_site_id)
		{
			$this->delete_module($module_name);
		}

		$config = $this->check_module_config('site', 'site');

		if($module_name == 'site')
		{
			$rows = DB::query_fetch_all("SELECT * FROM {site} WHERE trash='0'".($this->last_site_id ? " AND id>=%d" : "")." ORDER BY id ASC LIMIT ".$this->max_insert, $this->last_site_id);
		}
		else
		{
			$rows = DB::query_fetch_all("SELECT * FROM {site} WHERE trash='0' AND module_name='%s'".($this->last_site_id ? " AND id>=%d" : "")." ORDER BY id ASC LIMIT ".$this->max_insert, $module_name, $this->last_site_id);
		}

		foreach ($rows as $row)
		{
			if($this->last_site_id != $row["id"])
			{
				$this->index_item($row, $config, 'site');
				$this->check_max($row["id"], 'site', 0);
			}
			if($module_name != 'site')
			{
				$this->index_site_module($row, false, count($rows));
			}
		}
		$this->diafan->configmodules($this->type."_current_index_module_table", "search", 0, false, '');
		$this->diafan->configmodules($this->type."_current_index_module_element", "search", 0, false, '');
		$this->diafan->configmodules($this->type."_current_index_site", "search", 0, false, '');
		$this->diafan->configmodules($this->type."_index", "search", 0, false, '');
	}

	/**
	 * Индексирует страницы модуля, прикрепленного к странице сайта
	 *
	 * @param array $site данные о странице сайта
	 * @param boolean $out функция используется как внешняя
	 * @param integer $count_sites количество страниц, к которым прикреплен модуль
	 * @return void
	 */
	public function index_site_module($site, $out = true, $count_sites = 0)
	{
		if(! $site["module_name"])
		{
			return;
		}
		$module_config = $this->check_module_config($site["module_name"]);

		if(empty($module_config))
		{
			return;
		}
		if($out)
		{
			$this->type = 'module_site_'.$site["id"];
		}

		foreach($module_config as $table_name => $config)
		{
			$where = '';
			// если на прошлой итерации индексирования (до обновления страницы)
			// индексировалась текущая страница и задана какая-то таблица
			if($this->last_site_id == $site["id"] && $this->last_table)
			{
				// ищем текущую таблицу
				if($this->last_table == $table_name)
				{
					$current_last_table = true;
					$where .= " AND id>".$this->diafan->configmodules($this->type."_current_index_module_element", "search");
				}
				// если еще не найдена текущая таблица, то таблицу пропускаем, так как она уже проиндексирована
				if(empty($current_last_table))
				{
					continue;
				}
			}
			if($count_sites)
			{
				$where .= " AND site_id=".$site["id"];
			}
			$rows = DB::query_fetch_all("SELECT * FROM {%s} WHERE trash='0'".$where." ORDER BY id ASC LIMIT ".$this->max_insert, $table_name);

			if($out && $rows)
			{
				$element_ids = array();
				foreach($rows as $row)
				{
					$element_ids[] = $row["id"];
				}
				$this->delete($element_ids, $table_name);
			}

			foreach ($rows as $row)
			{
				$this->index_item($row, $config, $table_name, $site["id"]);
			}
		}
		if($out)
		{
			$this->diafan->configmodules($this->type."_current_index_module_table", "search", 0, false, '');
			$this->diafan->configmodules($this->type."_current_index_module_element", "search", 0, false, '');
			$this->diafan->configmodules($this->type."_current_index_site", "search", 0, false, '');
			$this->diafan->configmodules($this->type."_index", "search", 0, false, '');
		}
	}

	/**
	 * Индексирует группу элементов
	 *
	 * @param array $rows массив данных об индексируемых элементах
	 * @param strign $table_name таблица элементов
	 * @return void
	 */
	public function index_elements($rows, $table_name)
	{
		if(! $config = $this->check_module_config('', $table_name))
			return;

		foreach ($rows as $row)
		{
			$ids[] = $row["id"];
		}

		if(! empty($ids))
		{
			$this->delete($ids, $table_name);
			foreach ($rows as $row)
			{
				$this->index_item($row, $config, $table_name);
			}
		}
	}

	/**
	 * Индексирует один элемент
	 *
	 * @param array $row данные об индексируемом элементе
	 * @param strign $table_name таблица элемента
	 * @return void
	 */
	public function index_element($row, $table_name)
	{
		if(! $config = $this->check_module_config('', $table_name))
			return;

		$this->delete($row["id"], $table_name);

		$this->index_item($row, $config, $table_name);
	}

	/**
	 * Удаляет один или несколько элементов
	 *
	 * @param integer|array $element_ids номер одного или нескольких элементов
	 * @param string $table_name таблица элемента
	 * @return void
	 */
	public function delete($element_ids, $table_name)
	{
		if(is_array($element_ids))
		{
			$where = " IN (%s)";
			$value = preg_replace('/[^0-9,]+/', '', implode(",", $element_ids));
		}
		else
		{
			$where = "=%d";
			$value = $element_ids;
		}
		$ids = DB::query_fetch_value("SELECT id FROM {search_results} WHERE table_name='%s' AND element_id".$where, $table_name, $value, "id");

		if(empty($ids))
			return;

		$keys = DB::query_fetch_value("SELECT DISTINCT i.keyword_id FROM {search_index} AS i"
			." INNER JOIN {search_index} AS i2 ON i.keyword_id=i2.keyword_id"
			." WHERE i.result_id IN (%s) GROUP BY i.keyword_id HAVING count(i2.keyword_id)<2", implode(",", $ids),
			"keyword_id");

		DB::query("DELETE FROM {search_results} WHERE id IN (%s)", implode(",", $ids));
		DB::query("DELETE FROM {search_index} WHERE result_id IN (%s)", implode(",", $ids));
		if($keys)
		{
			$query = "";
			$k = 0;
			foreach ($keys as $key)
			{
					if(! $query)
					{
						$query = "DELETE FROM {search_keywords} WHERE id IN (".$key;
					}
					else
					{
						$query .= ", ".$key;
					}
					$k++;
					if($k == 100)
					{
						DB::query($query.")");
						$query = '';
						$k = 0;
					}
			}
			if($query)
			{
				DB::query($query.")");
			}
		}
	}

	/**
	 * Удаляет весь индекс модуля
	 *
	 * @param string $module_name название модуля
	 * @return void
	 */
	public function delete_module($module_name)
	{
		$site_ids = DB::query_fetch_value("SELECT id FROM {site} WHERE module_name='%s'", $module_name, "id");
		DB::query("DELETE FROM {search_index} WHERE result_id IN (SELECT id FROM {search_results} WHERE table_name LIKE '%s%%'".($site_ids ? " OR table_name='site' AND element_id IN (".implode(",", $site_ids).")" : '').")", $module_name);
		DB::query("DELETE FROM {search_results} WHERE table_name LIKE '%s%%'".($site_ids ? " OR table_name='site' AND element_id IN (".implode(",", $site_ids).")" : ''), $module_name);
	}

	/**
	 * Удаляет индекс модулей, прикрепленных к страницам сайта
	 *
	 * @param array $site_ids идентификаторы страниц сайта
	 * @return void
	 */
	public function delete_sites($site_ids)
	{
		$sites = DB::query_fetch_all("SELECT id, module_name FROM {site} WHERE id IN (%s)", implode(",", $site_ids));
		foreach($sites as $site)
		{
			if($site["module_name"])
			{
				$module_config = $this->check_module_config($site["module_name"]);

				if(empty($module_config))
				{
					return;
				}

				foreach($module_config as $table_name => $config)
				{
					$ids = DB::query_fetch_value("SELECT id FROM {%s} WHERE trash='0' AND site_id=%d", $table_name, $site["id"], "id");

					if ($ids)
					{
						$this->delete($ids, $table_name);
					}
				}
			}
		}
	}

	/**
	 * Проверяет есть ли настройки поиска для модуля и таблицы модуля
	 *
	 * @param string $module_name модуль
	 * @param string $table_name таблица
	 * @return array|boolean false
	 */
	public function check_module_config($module_name, $table_name = '')
	{
		if(! $module_name)
		{
			list($module_name, ) = explode('_', $table_name);
		}
		$module_config_name = 'module_'.$module_name;
		$module_config = $this->$module_config_name;
		if(! $table_name)
		{
			return $module_config;
		}
		else
		{
			if(isset($module_config[$table_name]))
			{
				return $module_config[$table_name];
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Индексирует страницы модуля, прикрепленного к странице сайта
	 *
	 * @param array $row
	 * @return void
	 */
	private function index_item($row, $config, $table_name, $site_id = 0)
	{
		$this->current_insert++;

		if(! empty($row["search_no_show"]))
			return;

		if (isset($row["act"]) && ! $row["act"])
			return;

		// дополнительные характеристики
		if(in_array('param', $config["fields"]) && ! isset($row["param"]) && (! defined('_LANG') || ! isset($row["param"._LANG])))
		{
			$param = $this->get_param($row["id"], $table_name);
		}

		foreach ($this->langs as $l)
		{
			if (isset($row["act".$l]) && ! $row["act".$l])
				continue;

			$module_rating = 0;
			if(! empty($config["rating"]))
			{
				$module_rating = $config["rating"];
			}

			$values_for_index = array();
			foreach($config["fields"] as $field)
			{
				if($field == 'param')
				{
					if(isset($param))
					{
						$values_for_index[] = $param[$l];
					}
				}
				else
				{
					$values_for_index[] = (array_key_exists($field.$l,$row) ? $row[$field.$l] : $row[$field]);
				}
			}
			$access = ! empty($row["access"]) ? 1 : 0;
			$date_start = ! empty($row["date_start"]) ? $row["date_start"] : 0;
			$date_finish = ! empty($row["date_finish"]) ? $row["date_finish"] : 0;
			$this->insert($row["id"], $table_name, $values_for_index, $module_rating, $l, $access, $date_start, $date_finish);
		}
		if($site_id)
		{
			$this->check_max($site_id, $table_name, $row["id"]);
		}
	}

	/**
	 * Получение значения дополнительных характеристик для элемента
	 *
	 * @param integer $id номер элемента
	 * @param string $table_name таблица
	 * @return string
	 */
	private function get_param($id, $table_name)
	{
		if(! isset($this->cache["param"][$table_name]))
		{
			$this->cache["param"][$table_name] = DB::query_fetch_key("SELECT * FROM {%s_param} WHERE trash='0'", $table_name, "id");
			$this->cache["param_select"][$table_name] = DB::query_fetch_key("SELECT * FROM {%s_param_select} WHERE trash='0'", $table_name, "id");
			foreach($this->cache["param_select"][$table_name] as $param_select_id => $row)
			{
				if(! empty($this->cache["param"][$table_name][$row["param_id"]]) && $this->cache["param"][$table_name][$row["param_id"]]["type"] == "checkbox")
				{
					$this->cache["param_checkbox"][$row["param_id"]][$row["value"]] = $row;
				}
			}
		}
		foreach($this->langs as $l)
		{
			$result[$l] = '';
		}
		$rows = DB::query_fetch_all("SELECT * FROM {%s_param_element} WHERE element_id=%d", $table_name, $id);
		foreach($rows as $row)
		{
			if(empty($this->cache["param"][$table_name]))
			{
				return;
			}
			if(empty($this->cache["param"][$table_name][$row["param_id"]]))
			{
				continue;
			}
			$param = $this->cache["param"][$table_name][$row["param_id"]];
			foreach($this->langs as $l)
			{
				$value = ' '.$param["name".$l];
				$v = $row["value".$this->diafan->_languages->site];
				switch($param["type"])
				{
					case 'select':
					case 'multiple':
						$value .= ' '.(! empty($this->cache["param_select"][$table_name][$v]) ? $this->cache["param_select"][$table_name][$v]["name".$l] : '');
						break;

					case 'checkbox':
						if($v)
						{
							$value .= ' '.(! empty($this->cache["param_checkbox"][$row["param_id"]][1]) ? $this->cache["param_checkbox"][$row["param_id"]][1]["name".$l] : '');
						}
						elseif(! empty($this->cache["param_checkbox"][$row["param_id"]][0]))
						{
							$value .= ' '.$this->cache["param_checkbox"][$row["param_id"]][0]["name".$l];
						}
						else
						{
							$value = '';
						}
						break;

					case 'text':
					case 'textarea':
					case 'editor':
						$value .= ' '.$row["value".$l];
						break;

					default:
						$value .= ' '.$v;
				}
				$result[$l] .= $value;
			}
		}
		return $result;
	}

	/**
	 * Добавляет в базу данных элемент
	 *
	 * @param integer $element_id идентификатор элемента
	 * @param string $table_name таблица
	 * @param array $values_for_index значения для индекса
	 * @param integer $module_rating рейтинг модуля
	 * @param integer $lang_id номер языка
	 * @param integer $access доступ ограничен группой пользователей
	 * @param integer $date_start дата начала показа
	 * @param integer $date_finish дата окончания показа
	 * @return void
	 */
	private function insert($element_id, $table_name, $values_for_index, $module_rating, $lang_id, $access, $date_start, $date_finish)
	{
		$field_rating = array();
		$words = array();
		foreach($values_for_index as $i => $value_for_index)
		{
			$index_words = $this->searchwords->prepare($value_for_index);
			if(! empty($index_words))
			{
				foreach($index_words as $w)
				{
					if($w && ! isset($field_rating[$w]))
					{
						$field_rating[$w] = $i;
						$words[] = $w;
					}
				}
			}
		}

		if(empty($words))
		{
			return;
		}

		$result_id = DB::query("INSERT INTO {search_results} (element_id, table_name, rating, lang_id, access, date_start, date_finish)"
			." VALUES(%d, '%s', %d, %d, '%d', %d, %d)", $element_id, $table_name, $module_rating, $lang_id, $access, $date_start, $date_finish
		);

		$keywords_already_exists = DB::query_fetch_key_value("SELECT id, keyword FROM {search_keywords} WHERE keyword IN ('".implode("', '", $words)."')", "id", "keyword");

		$new_words = array();
		foreach($words as $word)
		{
			if(! in_array($word, $keywords_already_exists))
			{
				$new_words[] = $word;
			}
		}
		if($new_words)
		{
			$query = "";
			$k = 0;
			foreach ($new_words as $word)
			{
					if(! $query)
					{
						$query = "INSERT INTO {search_keywords} (keyword) VALUES ('".$word."')";
					}
					else
					{
						$query .= ", ('".$word."')";
					}
					$k++;
					if($k == 100)
					{
						DB::query($query);
						$query = '';
						$k = 0;
					}
			}
			if($query)
			{
				DB::query($query);
			}
			$new_keywords_already_exists = DB::query_fetch_key_value("SELECT id, keyword FROM {search_keywords}"
				." WHERE keyword IN ('".implode("', '", $new_words)."')", "id", "keyword");
			foreach($new_keywords_already_exists as $keyword_id => $keyword)
			{
				$keywords_already_exists[$keyword_id] = $keyword;
			}
		}
		$query = "";
		$k = 0;
		foreach ($keywords_already_exists as $keyword_id => $keyword)
		{
			$r = ! empty($field_rating[$keyword]) ? $field_rating[$keyword] : 0;
			if(! $query)
			{
				$query = "INSERT INTO {search_index} (keyword_id, result_id, rating) VALUES (".$keyword_id.", ".$result_id.", ".$r.")";
			}
			else
			{
				$query .= ", (".$keyword_id.", ".$result_id.", ".$r.")";
			}
			$k++;
			if($k == 100)
			{
				DB::query($query);
				$query = '';
				$k = 0;
			}
		}
		if($query)
		{
			DB::query($query);
		}
	}

	/**
	 * Проверяет достижение максимума интексируемых ссылок за один проход скрипта
	 *
	 * @param integer $site_id текущая страница сайта
	 * @param string $table_name текущая таблица
	 * @param integer $element_id номер текущего проиндексированного элемента
	 * @return void
	 */
	private function check_max($site_id, $table_name, $element_id)
	{
		if($this->current_insert + 1 > $this->max_insert)
		{
			$this->diafan->configmodules($this->type."_current_index_module_table", "search", 0, false, $table_name);
			$this->diafan->configmodules($this->type."_current_index_module_element", "search", 0, false, $element_id);
			$this->diafan->configmodules($this->type."_current_index_site", "search", 0, false, $site_id);

			echo '<meta http-equiv="Refresh" content="0; url="http'.(IS_HTTPS ? "s" : '').'://'.getenv("HTTP_HOST").getenv("REQUEST_URI").'">';

			exit;
		}
	}
}
