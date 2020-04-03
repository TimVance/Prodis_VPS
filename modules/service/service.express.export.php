<?php
/**
 * Экспорт данных
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
 * Service_express_export
 */
class Service_express_export extends Diafan
{
	/**
	 * @var string префикс имени файла экспорта
	 */
	const EXPORT_PREFIX_FILE_NAME = 'export';

	/**
	 * @var string расширение файла экспорта
	 */
	const EXPORT_FILE_EXTENTION = 'csv';

	/**
	 * @var array конфигурация текущего экспорта
	 */
	protected $export;

	/**
	 * @var array название полей списка
	 */
	protected $select_values;

	/**
	 * @var array поля, заданные для текущего экспорта
	 */
	protected $fields;

	/**
	 * @var array записи текущего экспорта
	 */
	protected $rows;

	/**
	 * @var array данные о текущем элементе экспорта
	 */
	protected $data;

	/**
 	 * @var string разделитель поля в содержании CSV-экспорта (только один символ)
 	 */
 	protected $csv_delimiter = ';';

	/**
 	 * @var string символ ограничителя поля в содержании CSV-экспорта (только один символ)
 	 */
 	protected $csv_enclosure = '"';

	/**
 	 * @var string экранирующий символ в содержании CSV-экспорта (только один символ)
 	 */
 	protected $csv_escape = '\\';

	/**
 	 * @var string кодировка содержания CSV-экспорта
 	 */
 	protected $csv_encoding = 'cp1251';

	/**
	 * @var string путь до временной директории относительно корня сайта
	 */
	protected $dir_path = 'tmp/express';

	/**
	 * @var string имя файла экспорта
	 */
	protected $export_file_name = '';

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);

		$this->export_file_name = preg_replace('/[^a-z_ ]+/', '', str_replace(array(' ', '-'), '_', substr(strtolower($this->diafan->translit(TIT1)), 0, 50)));
	}

	/**
	 * Деструктор класса
	 *
	 * @return void
	 */
	public function __destruct()
	{

  }

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
				case 'pos':         // текущая позиция экспорта
				case 'file_export': // путь к файлу экспорта относительно корня сайта
				case 'module_name': // имя модуля участвующего в экспорте
					$cache_meta = array("name" => $name, "prefix" => "express_export");
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
			case 'pos':
			case 'file_export':
			case 'module_name':
				$cache_meta = array("name" => $name, "prefix" => "express_export");
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
	 * Инициирует экспорт
	 *
	 * @param integer $cat_id идентификатор описания импорта/экспорта
	 * @return mixed (false|'success'|'next'|'empty'|'busy')
	 */
	public function init($cat_id)
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			return false;
		}

		$this->cat_id = $cat_id;

		// устанавливает настройки импорта/экспорта
		if(! $this->init_config())
		{
			return false;
		}

		// проверяем права доступа
		if(! $this->diafan->_users->roles("init", "service/express", array(), 'admin')
		|| ! $this->diafan->_users->roles("init", $this->export["module_name"], array(), 'admin'))
		{
			return false; //Custom::inc('includes/404.php');
		}

		// устанавливаем блокировку процесса
		if($this->diafan->_service->busy())
		{
			return 'busy';
		}
		else $this->diafan->_service->busy(true);

		// иинициируем экспорт
		$result = $this->export();

		// снимаем блокировку процесса
		$this->diafan->_service->busy(false);

		// возвращаем ответ
		return $result;
	}

	/**
	 * Устанавливает настройки экспорта
	 *
	 * @return boolean
	 */
	protected function init_config()
	{
		if(! $this->export = DB::query_fetch_array("SELECT * FROM {%s_category} WHERE trash='0' AND id=%d LIMIT 1", 'service_express_fields', $this->cat_id))
		{
			return false;
		}
		if(! $modules = $this->diafan->_service->modules_express())
		{
			return false;
		}
		else $modules = $this->diafan->array_column($modules, "name");
		if(! $this->export["module_name"] || ! in_array($this->export["module_name"], $modules))
		{
			return false;
		}
		$site_ids = DB::query_fetch_value("SELECT id FROM {site} WHERE module_name='%s'", $this->export["module_name"], "id");
		if(! empty($site_ids) && ! in_array($this->export["site_id"], $site_ids))
		{ // не найдена страница модуля
			$site_id = reset($site_ids);
			// изменяем на первую существующую страницу модуля
			DB::query("UPDATE {%s_category} SET site_id=%d WHERE id=%d LIMIT 1", 'service_express_fields', $site_id, $this->cat_id);
			$this->export["site_id"] = $site_id;
		}
		$this->export["table"] = $this->export["module_name"].($this->export["type"] != 'element' ? "_".$this->export["type"] : "");
		if(! $this->export["count_part"])
		{
			$this->export["count_part"] = 20;
		}
		if(! $this->export['sub_delimiter'])
		{
			$this->export["sub_delimiter"] = "|";
		}
		if($this->export["cat_id"])
		{
			$this->export["cat_ids"] = $this->diafan->get_children($this->export["cat_id"], $this->export["module_name"]."_category");
			$this->export["cat_ids"][] = $this->export["cat_id"];
		}
		else $this->export["cat_ids"] = array();

		$this->export['delimiter'] = $this->csv_delimiter ?: ";";
		$this->export['enclosure'] = $this->csv_enclosure ?: '"';
		$this->export['encoding'] = $this->csv_encoding;

		$this->fields = array();
		$this->select_values = array();

		//получаем типы полей учавствующих в импорте
		// TO_DO: принципиально важна единообразная очередность полей для таблицы {service_express_fields} - ORDER BY sort ASC, id ASC
		if(! $this->fields = DB::query_fetch_all("SELECT type, name, required, params FROM {%s} WHERE trash='0' AND cat_id=%d ORDER BY sort ASC, id ASC", "service_express_fields", $this->cat_id))
		{
			return false;
		}
		foreach($this->fields as $k => $row)
		{
			$this->fields[$k]["params"] = $row["params"] = unserialize($row["params"]);
			if($this->config_params($this->fields, $k, $row))
			{
				continue;
			}
		}
		$this->select_values();
		switch($this->export["type"])
		{
			case 'element':
				$this->export["element_type"] = 'element';
				break;

			case 'category':
				$this->export["element_type"] = 'cat';
				break;

			default:
				$this->export["element_type"] = $this->export["type"];
				break;
		}

		File::create_dir($this->dir_path, true);

		return true;
	}

	/**
	 * Устанавливает параметры полей учавствующих в экспорте
	 *
	 * @param array $rows массив полей учавствующих в экспорте
	 * @param integer $k текущий индекс в массиве полей
	 * @param array $row массив значений текущего поля
	 * @return boolean
	 */
	protected function config_params($rows, $k, $row)
	{
		// TO_DO: При возврате значение TRUE, текущая итерация цикла будет принудительно прервана.
		return false;
	}

	/**
	 * Устанавливает название полей списка
	 *
	 * @return void
	 */
	protected function select_values()
	{

	}

	/**
	 * Экспорт
	 *
	 * @return string (false|'success'|'next'|'empty')
	 */
	protected function export()
	{
		if(! $count = $this->get_max())
		{
			return 'empty';
		}
		$this->pos ? $this->pos : 0;

		// определяем результат прошлого экспорта
		if(($this->pos >= $count) || (! $this->pos))
		{
			if(file_exists(ABSOLUTE_PATH.$this->file_export) && is_file(ABSOLUTE_PATH.$this->file_export))
			{
				unlink(ABSOLUTE_PATH.$this->file_export);
			}
			$this->file_export = false;
			$this->module_name = false;
			$this->pos = 0;
		}
		// определяем начало экспорта - первая итерация экспорта
		if(! $this->file_export || ! file_exists(ABSOLUTE_PATH.$this->file_export))
		{
			$this->file_export = $this->dir_path.'/'.self::EXPORT_PREFIX_FILE_NAME.'.'.$this->export["module_name"].'.'.$this->export_file_name.'.'. $this->diafan->uid().'.'.self::EXPORT_FILE_EXTENTION;
			$this->module_name = $this->export["module_name"];
			$this->pos = 0;
		}

		// первая итерация экспорта
		if (! $this->pos) $this->prepare(); // подготовка базы данных

		if($this->pos < $count)
		{
			$list = array();
			if($this->export["header"] && ! $this->pos)
			{
				$array = array();
				foreach($this->fields as $field)
				{
					$array[] = $field["name"];
				}
				$list[] = $this->diafan->putcsv($array, $this->export['delimiter'], $this->export['enclosure']);
			}

			$this->rows = DB::query_range_fetch_all("SELECT * FROM {".$this->export["table"]."} WHERE site_id=%d"
				.($this->export["type"] == 'element' && $this->export["cat_ids"] ? " AND cat_id IN (".implode(',', $this->export["cat_ids"]).")" : '')
				." AND trash='0'", $this->export["site_id"], $this->pos, $this->export["count_part"]);
			// построчное считывание и анализ строк из базы данных
			$this->prepare_rows();
			foreach($this->rows as $row)
			{
				$this->pos = $this->pos + 1;
				$this->data = &$row;
				if(! $array = $this->export_row())
				{
					continue;
				}
				$list[] = $this->diafan->putcsv($array, $this->export['delimiter'], $this->export['enclosure']);
			}
			if(! empty($list))
			{
				if(false === $this->save_export(implode(PHP_EOL, $list), $this->file_export, $this->export['encoding']))
				{
					return false;
				}
			}
		}
		if($this->pos < $count) return 'next';
		$this->pos = false;

		$this->finish();

		return 'success';
	}

	/**
	 * Подготовка базы данных
	 *
	 * @return void
	 */
	protected function prepare()
	{

	}

	/**
	 * Подготовка к построчному считыванию и анализу строк из базы данных
	 *
	 * @return void
	 */
	protected function prepare_rows()
	{

	}

	/**
	 * Вывод строки экспорта
	 *
	 * @return array
	 */
	protected function export_row()
	{
		$list = array();
		if(empty($this->data) || ! is_array($this->data))
		{
			return $list;
		}
		foreach ($this->fields as $k => $field)
		{
			switch($field["type"])
			{
				case 'id':
					switch($field["params"]["type"])
					{
						case 'site':
							$list[] = $this->data["id"];
							break;

						default:
							$list[] = $this->data["import_id"];
							break;
					}
					break;

				case 'parent':
					$value = '';
					if($this->export["type"] == 'category')
					{
						switch($field["params"]["type"])
						{
							case 'site':
								$value = $this->data["parent_id"];
								break;

							case 'name':
								if($this->data["parent_id"])
								{
									$value = DB::query_result("SELECT [name] FROM {%s_category} WHERE id=%d LIMIT 1", $this->export["module_name"], $this->data["parent_id"]);
								}
								break;

							default:
								if($this->data["parent_id"])
								{
									$value = DB::query_result("SELECT import_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->export["module_name"], $this->data["parent_id"]);
								}
								break;
						}
					}
					elseif($this->export["type"] == 'element')
					{
						$cats = DB::query_fetch_key_value("SELECT cat_id as cat FROM {%s_category_rel} WHERE element_id=%d AND trash='0'", $this->export["module_name"], $this->data["id"], "cat", "cat");
						if(isset($this->data["cat_id"]) && ! empty($cats[$this->data["cat_id"]]))
						{
							$cat_id = $cats[$this->data["cat_id"]];
						}
						else
						{
							$cat_id = ! empty($cats) ? array_shift($cats) : 0;
						}
						unset($cats);
						if($cat_id)
						{
							if(! $parent_id = DB::query_result("SELECT parent_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->export["module_name"], $cat_id))
							{
								$parent_id = DB::query_result("SELECT parent_id FROM {%s_category_parents} WHERE element_id=%d AND trash='0' LIMIT 1", $this->export["module_name"], $cat_id);
							}
							if($parent_id)
							{
								switch($field["params"]["type"])
								{
									case 'site':
										$value = $parent_id;
										break;

									case 'name':
										$value = DB::query_result("SELECT [name] FROM {%s_category} WHERE id=%d LIMIT 1", $this->export["module_name"], $parent_id);
										break;

									default:
										$value = DB::query_result("SELECT import_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->export["module_name"], $parent_id);
										break;
								}
							}
						}
					}
					$list[] = $value;
					break;

				case 'name':
					$list[] = $this->data[$field["type"]._LANG];
					break;

				case 'cats':
					if($this->export["type"] == 'element')
					{
						$table_cat_rel = $this->export["module_name"].'_category_rel';
					}
					else
					{
						$table_cat_rel = $this->export["module_name"].'_'.$this->export["type"].'_category_rel';
					}
					switch($field["params"]["type"])
					{
						case 'site':
							$cats = DB::query_fetch_key_value("SELECT cat_id as cat FROM {".$table_cat_rel."} WHERE element_id=%d AND trash='0'", $this->data["id"], "cat", "cat");
							break;

						case 'name':
							$cats = DB::query_fetch_key_value("SELECT s.[name], s.id FROM {".$table_cat_rel."} AS r INNER JOIN {%s_category} AS s ON s.id=r.cat_id WHERE r.element_id=%d AND r.trash='0'", $this->export["module_name"], $this->data["id"], "id", "name");
							break;

						default:
							$cats = DB::query_fetch_key_value("SELECT s.import_id, s.id FROM {".$table_cat_rel."} AS r INNER JOIN {%s_category} AS s ON s.id=r.cat_id WHERE r.element_id=%d AND r.trash='0'", $this->export["module_name"], $this->data["id"], "id", "import_id");
							break;
					}
					$sequence = ($field["params"]["type"] == 'name' && ! empty($field["params"]["sequence_delimitor"]));
					if(! isset($this->cache["cats"]) && $sequence)
					{
						$this->cache["parents"] =
							DB::query_fetch_key("SELECT id, [name], parent_id FROM {%s_category} WHERE trash='0'", $this->export["module_name"], $this->import["site_id"], "id");
					}
					if($sequence)
					{
						foreach($cats as $i => $dummy)
						{
							$ii = $i;
							while(! empty($this->cache["parents"][$ii]))
							{
								if($ii != $i)
								{
									$cats[$i] = $this->cache["parents"][$ii]["name"] . $field["params"]["sequence_delimitor"] . $cats[$i];
								}
								$ii = $this->cache["parents"][$ii]["parent_id"];
							}
						}
					}
					$value = '';
					if(isset($this->data["cat_id"]) && ! empty($cats[$this->data["cat_id"]]))
					{
						$value = $cats[$this->data["cat_id"]];
						unset($cats[$this->data["cat_id"]]);
						if($cats)
						{
							$value .= $this->export["sub_delimiter"];
						}
					}
					$value .= implode($this->export["sub_delimiter"], $cats);
					$list[] = $value;
					break;

				case 'menu':
					if($field["params"]["id"])
					{
						$in_menu = DB::query_result("SELECT id FROM {menu} WHERE cat_id=%d AND module_name='%s' AND element_id=%d AND element_type='%s' trash='0' AND [act]='1' LIMIT 1", $field["params"]["id"], $this->export["module_name"], $this->data["id"], $this->export["element_type"]);
						$list[] = $in_menu ? '1' : '0';
					}
					break;

				case 'empty':
					$list[] = '';
					break;
			}
		}
		return $list;
	}

	/**
	 * Сохраняет экспорта
	 *
	 * @param string $content содержание файла
	 * @param string $file_path путь до файла относительно корня сайта
	 * @param string $encoding кодировка содержания
	 * @return integer
	 */
	protected function save_export($content, $file_path = false, $encoding = 'cp1251')
	{
		$file_path = $file_path ?: $this->file_export;
		if(! $file_path)
		{
			$file_path = $this->dir_path.'/'.self::EXPORT_PREFIX_FILE_NAME.($this->module_name ? '.'.$this->module_name : '').'.'.$this->export_file_name.'.'.$this->diafan->uid().'.'.self::EXPORT_FILE_EXTENTION;
			$this->file_export = $file_path;
		}
		if($encoding == 'cp1251')
		{
			$content = utf::to_windows1251($content);
		}
		// file_put_contents(ABSOLUTE_PATH.$file_path, $content, FILE_APPEND);
		return File::save_file($content, $file_path, true);
	}

	/**
	 * Завершающие операции экспорта
	 *
	 * @return void
	 */
	protected function finish()
	{

	}

	/**
	 * Записывает разделитель поля в содержании CSV-экспорта
	 *
	 * @param string $value разделитель поля (только один символ)
	 * @return void
	 */
	public function set_delimiter($value)
	{
		if(empty($value))
		{
			return false;
		}

		$this->csv_delimiter = $value;
		return true;
	}

	/**
	 * Записывает символ ограничителя поля в содержании CSV-экспорта
	 *
	 * @param string $value символ ограничителя поля (только один символ)
	 * @return void
	 */
	public function set_enclosure($value)
	{
		if(empty($value))
		{
			return false;
		}

		$this->csv_enclosure = $value;
		return true;
	}

	/**
	 * Записывает кодировка содержания CSV-экспорта
	 *
	 * @param string $value кодировка содержания
	 * @return void
	 */
	public function set_encoding($value)
	{
		if(empty($value))
		{
			return false;
		}

		$this->csv_encoding = $value;
		return true;
	}

	/**
	 * Скачивание файлов экспорта
	 *
	 * @param boolean $zip сжимать файл экспорта
	 * @return void
	 */
	public function download($zip = false)
	{
		if($this->pos)
		{
			return false; //Custom::inc('includes/404.php');
		}
		if(! ($modules = $this->diafan->_service->modules_express()) || ! $this->module_name
		|| ! $this->file_export || ! file_exists(ABSOLUTE_PATH.$this->file_export) || ! is_readable(ABSOLUTE_PATH.$this->file_export))
		{
			return false; //Custom::inc('includes/404.php');
		}
		$modules = $this->diafan->array_column($modules, "module_name");
		if(! in_array($this->module_name, $modules))
		{
			return false; //Custom::inc('includes/404.php');
		}

		// проверяем права доступа
		if(! $this->diafan->_users->roles("init", "service/express", array(), 'admin')
		|| ! $this->diafan->_users->roles("init", $this->module_name, array(), 'admin'))
		{
			return false; //Custom::inc('includes/404.php');
		}

		$is_zip = false; $filename = $this->file_export; $files_size = filesize(ABSOLUTE_PATH.$filename);
		if(class_exists('ZipArchive') && $zip)
		{
			$filename = 'tmp/'.md5($this->diafan->uid()).'.zip';
			$obj_zip = new ZipArchive;
			if ($obj_zip->open(ABSOLUTE_PATH.$filename, ZipArchive::CREATE) === true)
			{
				$obj_zip->addFile(ABSOLUTE_PATH.$this->file_export, basename(ABSOLUTE_PATH.$this->file_export));
				$obj_zip->close();
				$is_zip = true;
				$files_size = filesize(ABSOLUTE_PATH.$filename);
				unlink(ABSOLUTE_PATH.$this->file_export);
			}
			unset($obj_zip);
		}

		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Cache-Control: max-age=86400');
		if($is_zip)
		{
			header("Content-type: application/zip");
		}
		else
		{
			header('Content-Type: application/octet-stream');//header("Content-type: text/plain");
		}
		header('Content-Disposition: attachment; filename=' . self::EXPORT_PREFIX_FILE_NAME.'.'.$this->module_name.'.'.$this->export_file_name.'.'.($is_zip ? 'zip' : self::EXPORT_FILE_EXTENTION));
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Accept-Ranges: bytes');
		header('Content-Length: ' . $files_size);
		header('Connection: close');
		if($is_zip) { readfile(ABSOLUTE_PATH.$filename); }
		else { echo file_get_contents(ABSOLUTE_PATH.$filename); }
		unlink(ABSOLUTE_PATH.$filename);
		exit;
	}

	/**
	 * Возвращает максимальную позицию в импорте
	 *
	 * @param integer $cat_id идентификатор описания импорта/экспорта
	 * @return integer
	 */
	public function get_max($cat_id = false)
	{
		if(! isset($this->cache["max"]))
		{
			$no_cache = false;
			if(! $this->export)
			{
				$no_cache = true;

				if(! $cat_id)
				{
					return false;
				}
				$this->cat_id = $cat_id;

				// устанавливает настройки импорта/экспорта
				if(! $this->init_config())
				{
					return false;
				}
			}

			$count = (int) DB::query_result("SELECT COUNT(*) FROM {".$this->export["table"]."} WHERE site_id=%d"
				.($this->export["type"] == 'element' && $this->export["cat_ids"] ? " AND cat_id IN (".implode(',', $this->export["cat_ids"]).")" : '')
				." AND trash='0'", $this->export["site_id"]);

			if($no_cache)
			{
				return $count;
			}
			$this->cache["max"] = $count;
		}
		return $this->cache["max"];
	}

	/**
	 * Возвращает текущую позицию в обработке импорте
	 *
	 * @return integer
	 */
	public function get_pos()
	{
		return (int) $this->pos ?: 0;
	}
}
