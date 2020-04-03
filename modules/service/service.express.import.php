<?php
/**
 * Импорт данных
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
 * Service_express_import
 */
class Service_express_import extends Diafan
{
	/**
	 * @var string имя файла лога ошибок импорта
	 */
	const FILE_ERRORS_LOG = 'errors.log.csv';

	/**
	 * @var string имя таблицы базы данных без префикса, содержащей импортируемые данные
	 */
	const TABLE_NAME = 'service_express_import_elements';

	/**
	 * @var string префикс полей таблицы базы данных, содержащих импортируемые данные
	 */
	const COLUMN_NAME = 'col_';

	/**
	 * @var integer минимальное количество в цикле
	 */
	const MIN_NASTR = 1000;

	/**
	 * @var integer идентификатор описания импорта/экспорта
	 */
	protected $cat_id;

	/**
	 * @var array конфигурация текущего импорта
	 */
	protected $import;

	/**
	 * @var array характеристики элементов
	 */
	protected $params;

	/**
	 * @var array поля "показывать в меню"
	 */
	protected $menus;

	/**
	 * @var array поля, заданные для текущего импорта
	 */
	protected $fields;

	protected $fields_iterator;

	/**
	 * @var integer номер текущей строки
	 */
	protected $data_string_number = 0;

	/**
	 * @var array записи текущего импорта
	 */
	protected $rows;

	/**
	 * @var array исходная запись текущего импорта
	 */
	protected $row;

	/**
	 * @var array данные о текущем элементе импорта
	 */
	protected $data;

	/**
	 * @var integer номер текущего элемента импорта
	 */
	protected $id;

	/**
	 * @var boolean текущий элемент найден в системе и будет обновлен
	 */
	protected $update;

	/**
	 * @var array старые данные об импортируемом элементе
	 */
	protected $oldrow;

	/**
	 * @var array ошибки импорта/экспорта
	 */
	protected $errors;

	/**
	 * @var integer текущее значение поля для сортировки
	 */
	protected $sort;

	/**
	 * @var boolean первая итерация текущего импорта
	 */
	protected $first_iteration = false;

	/**
	 * @var string путь до временной директории относительно корня сайта
	 */
	protected $dir_path = 'tmp/express';

	/**
	 * @var string|array метка кэша
	 */
	protected $cache_meta;

	/**
	 * @var mixed данные, сохраняемые в кэше
	 */
	protected $cache_data = false;

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
		$this->cache_meta = array("name" => "data", "prefix" => "express_import");
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
				case 'last_cat_id':     // идентификатор описания импорта/экспорта
				case 'file_errors_log': // маркер ошибки импорта
					$cache_meta = array("name" => $name, "prefix" => "express_import");
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
			case 'last_cat_id':
			case 'file_errors_log':
				$cache_meta = array("name" => $name, "prefix" => "express_import");
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
	 * Инициирует импорт
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
		|| ! $this->diafan->_users->roles("init", $this->import["module_name"], array(), 'admin'))
		{
			return false; //Custom::inc('includes/404.php');
		}

		// устанавливаем блокировку процесса
		if($this->diafan->_service->busy())
		{
			return 'busy';
		}
		else $this->diafan->_service->busy(true);

		// иинициируем импорт
		$result = $this->import();

		// снимаем блокировку процесса
		$this->diafan->_service->busy(false);

		// сохраняем лог ошибок
		if(! empty($this->errors))
		{
			$text = '';
			foreach ($this->errors as $list)
			{
				$text .= $this->diafan->putcsv($list);
			}
			if($text) $this->save_log($text);
		}

		// возвращаем ответ
		return $result;
	}

	/**
	 * Устанавливает настройки импорта
	 *
	 * @return boolean
	 */
	protected function init_config()
	{
		if(! $this->import = DB::query_fetch_array("SELECT * FROM {%s_category} WHERE trash='0' AND id=%d LIMIT 1", 'service_express_fields', $this->cat_id))
		{
			return false;
		}
		if(! $modules = $this->diafan->_service->modules_express())
		{
			return false;
		}
		else $modules = $this->diafan->array_column($modules, "name");
		if(! $this->import["module_name"] || ! in_array($this->import["module_name"], $modules))
		{
			return false;
		}
		$this->import["table"] = $this->import["module_name"].($this->import["type"] != 'element' ? "_".$this->import["type"] : "");
		if(! $this->import["count_part"])
		{
			$this->import["count_part"] = 20;
		}
		if(! $this->import['sub_delimiter'])
		{
			$this->import["sub_delimiter"] = "|";
		}
		if($this->import["cat_id"])
		{
			$this->import["cat_ids"] = $this->diafan->get_children($this->import["cat_id"], $this->import["module_name"]."_category");
			$this->import["cat_ids"][] = $this->import["cat_id"];
		}

		$this->fields = array();
		$this->fields_iterator = array();
		$this->params = array();
		$this->menus = array();

		//получаем типы полей учавствующих в импорте
		// TO_DO: принципиально важна единообразная очередность полей для таблицы {service_express_fields} - ORDER BY sort ASC, id ASC
		if(! $rows = DB::query_fetch_all("SELECT type, name, required, params FROM {%s} WHERE trash='0' AND cat_id=%d ORDER BY sort ASC, id ASC", "service_express_fields", $this->cat_id))
		{
			return false;
		}
		foreach ($rows as $k => $row)
		{
			if($this->config_params($rows, $k, $row))
			{
				continue;
			}
			if($row["type"] == 'menu')
			{
				$params = unserialize($row["params"]);
				$this->menus[$k] = array(
						'name' => $row["name"],
						'required' => $row["required"],
						'id' => $params["id"],
					);
				continue;
			}
			$new_field = array(
				'i' => $k,
				'name' => $row["name"],
				'required' => $row["required"],
			);
			$params = unserialize($row["params"]);
			if($params)
			{
				foreach ($params as $key => $value)
				{
					$new_field['param_'.$key] = $value;
				}
			}

			if(array_key_exists($row['type'], $this->fields))
			{
				if(array_key_exists('i',  $this->fields[$row["type"]]))
				{
					$this->fields_iterator[$row['type']] = $this->fields[$row["type"]]["i"];
					$this->fields[$row["type"]] = array($this->fields[$row["type"]]["i"] => $this->fields[$row["type"]]);
				}
				$this->fields[$row["type"]][$k] = $new_field;
			}
			else
			{
				$this->fields[$row["type"]] = $new_field;
			}
		}

		$this->cache["count_fields"] = count($rows);
		switch($this->import["type"])
		{
			case 'element':
				$this->import["element_type"] = 'element';
				break;

			case 'category':
				$this->import["element_type"] = 'cat';
				break;

			default:
				$this->import["element_type"] = $this->import["type"];
				break;
		}
		Custom::inc("includes/validate.php");

		File::create_dir($this->dir_path, true);

		return true;
	}

	/**
	 * Устанавливает параметры полей учавствующих в импорте
	 *
	 * @param array $rows массив полей учавствующих в импорте
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
	 * Импорт
	 *
	 * @return string ('success'|'next'|'empty')
	 */
	protected function import()
	{
		// проверяем наличие полей описания импорта
		if (empty($this->fields)) return 'empty';
		// определяем текущие колонки без учета служебных в таблице импорта
		$tables = $this->diafan->_service->get_tables(false, true);
		if(empty($tables[self::TABLE_NAME])) return 'empty';
		$fields = array();
		foreach ($tables[self::TABLE_NAME] as $name)
		{
			if(! preg_match('/'. self::COLUMN_NAME .'([0-9]+)/', $name, $result)) continue;
			$fields[] = $name;
		}
		if(empty($fields)) return 'empty';

		// определяем начало импорта
		$this->first_iteration = !!! DB::query_result("SELECT id FROM {".self::TABLE_NAME."} WHERE trash='1' LIMIT 1");
		// читаем строки импорта
		$polog = 0; $nastr = $this->import["count_part"];
		$this->rows = DB::query_fetch_key("SELECT id, %s FROM {".self::TABLE_NAME."} WHERE 1=1 AND trash='0' GROUP BY id LIMIT %d, %d", implode(",", $fields), $polog, $nastr, "id");
		// реакция на первую итерацию импорта и пустой импорт
		if($this->first_iteration && empty($this->rows)) return 'empty';
		// подготавливаем массив значений
		foreach ($this->rows as $id => $data)
		{
			unset($data["id"]);
			$this->rows[$id] = array_values($data);
		}
		// определяем значение для сортировки
		$this->sort = DB::query_result("SELECT COUNT(*) FROM {".self::TABLE_NAME."} WHERE trash='0'");

		// первая итерация импорта
		if ($this->first_iteration) $this->prepare(); // подготовка базы данных

		//кеширование
		$this->cache_data = $this->diafan->_cache->get($this->cache_meta, 'service', CACHE_GLOBAL);
		if(empty($this->cache_data)) $this->cache_data = array();

		Custom::inc("includes/image.php");

		if(! empty($this->rows))
		{
			// построчное считывание и анализ строк из файла
			$this->prepare_rows();
			foreach ($this->rows as $this->data_string_number => $this->row)
			{
				$this->data = $this->row;
				$this->id = 0;

				// первая строчка, если является заголовком (названием полей)
				if(($this->import["header"] && $this->first_iteration && $this->data_string_number == 1)
				// чтение записей импорта
				|| (! $this->prepare_data() || $this->cache["bag_string"]))
				{
					// пропускаем
				}
				else
				{
					// импортируем текущие записи импорта
					$this->import_row();
				}

				// помечаем прочитанные строки импорта
				DB::query("UPDATE {".self::TABLE_NAME."} SET trash='1', element_id=%d WHERE id=%d AND trash='0'", $this->id, $this->data_string_number);
			}
		}

		// проверяем наличие необработанных записей импорта
		if(DB::query_result("SELECT id FROM {".self::TABLE_NAME."} WHERE trash='0' LIMIT 1"))
		{
			//кеширование
			$this->diafan->_cache->save($this->cache_data, $this->cache_meta, 'service', CACHE_GLOBAL);
			return 'next';
		}
		else
		{
			// необработанных записей импорта не осталось
			// проверяем была ли обработка записей импорта в данной итерации
			if(! empty($this->rows))
			{
				//кеширование
				$this->diafan->_cache->save($this->cache_data, $this->cache_meta, 'service', CACHE_GLOBAL);
				return 'next';
			}
		}

		// выполняем операции после импорта
		if(! $this->finish())
		{
			//кеширование
			$this->diafan->_cache->save($this->cache_data, $this->cache_meta, 'service', CACHE_GLOBAL);
			return 'next';
		}

		// удаляем кэш модуля
		$this->diafan->_cache->delete("", $this->import["module_name"]);

		// удаляем кэш импорта
		$this->diafan->_cache->delete($this->cache_meta, 'service');

		// очищаем таблицу экспорта
		DB::query("DROP TABLE IF EXISTS ".DB_PREFIX.self::TABLE_NAME);

		return 'success';
	}

	/**
	 * Возвращает состояние записи: обрабатывался ли ранее идентификатор записи в таблице модуля
	 *
	 * @param integer $element_id идентификатор записи из таблицы модуля
	 * @return boolean
	 */
	protected function is_ready($element_id)
	{
		if(empty($this->cache["is_ready"][$element_id]))
		{
			$this->cache["is_ready"][$element_id] = DB::query_result("SELECT id FROM {".self::TABLE_NAME."} WHERE element_id=%d AND trash='1' LIMIT 1", $element_id);
		}
		return !! $this->cache["is_ready"][$element_id];
	}

	/**
	 * Подготовка базы данных
	 *
	 * @return void
	 */
	protected function prepare()
	{
		// удаляем предыдущий лог
		$this->delete_log();

		// включаем режим обновления
		DB::query("UPDATE {".$this->import["table"]."} SET `import`='0' WHERE `import`='1'"
			  .($this->import["type"] == 'element' && $this->import["cat_id"] ? " AND cat_id=".$this->import["cat_id"] : ''));

		// удаляет неописанные в файле импорта записи
		$this->prepare_delete();

		// подготовка к импорту поля "Родитель"
		$this->prepare_parent();

		// запоминаем идентификатор описания импорта
		$this->last_cat_id = $this->cat_id;

		// удаляем кэш импорта
		$this->diafan->_cache->delete($this->cache_meta, 'service');
	}

	/**
	 * Удаление записей в БД, если в импорте НЕ участвуют идентификаторы элементов
	 *
	 * @return void
	 */
	protected function prepare_delete()
	{
		if(! $this->import["delete_items"])
			return;

		// удалим в конце все не помеченные import='1'
		if($this->is_field('id'))
			return;

		$this->delete();
	}

	/**
	 * Подготавливает к импорту поле "Родитель"
	 *
	 * @return void
	 */
	protected function prepare_parent()
	{
		if(! in_array($this->import["type"], array('category')))
			return;

		if (! $this->is_field("parent"))
			return;

		if($this->field("parent", "param_type") == 'site')
			return;

		$tables = $this->diafan->_service->get_tables();
		if(! empty($tables[$this->import["module_name"]."_category"]) && in_array("import_parent_id", $tables[$this->import["module_name"]."_category"]))
		{
			DB::query("ALTER TABLE {%s_category} DROP `import_parent_id`", $this->import["module_name"]);
		}
		DB::query("ALTER TABLE {%s_category} ADD `import_parent_id` VARCHAR(100) NOT NULL AFTER `import_id`", $this->import["module_name"]);
	}

	/**
	 * Подготавливает поле о текущем элементе
	 *
	 * @return void
	 */
	protected function insert_field_data($type)
	{
		$value = $this->field_value($type);
		if(! $value)
		{
			if($this->field($type, 'required'))
			{
				$this->error_validate($type, 'значение не задано');
			}
			return;
		}

		// подготовка полей, содержащих несколько значений
		if (in_array($type, array("cats")))
		{
			// замена переносов строк, табуляции на разделитель
			$value = str_replace(array("\r\n", "\r", "\n", "\t"), $this->import["sub_delimiter"], $value);
			$value = str_replace($this->import["sub_delimiter"].$this->import["sub_delimiter"], $this->import["sub_delimiter"], $value);

			$d = explode($this->import["sub_delimiter"], $value);
			$value = array();
			foreach ($d as $i => $v)
			{
				$v = trim($v);
				if(! $v)
					continue;

				$value[$i] = $v;
			}

			// подготовка поля, содержащего последовательность значений
			if($this->field($type, 'param_sequence_delimitor'))
			{
				foreach($value as $key => $dummy)
				{
					$d = explode($this->field($type, 'param_sequence_delimitor'), $value[$key]);
					$val = array();
					foreach ($d as $i => $v)
					{
						$v = trim($v);
						if(! $v)
							continue;

						$val[$i] = $v;
					}
					$count = count($val);
					if($count > 1)
					{
						if($this->field($type, 'param_type') == 'name')
						{
							$value[$key] = $val;
						}
						else
						{
							$value[$key] = end($val);
						}
					}
					elseif($count == 1)
					{
						$value[$key] = reset($val);
					}
					else
					{
						unset($value[$key]);
					}
				}
			}
		}
		// валидация
		switch($type)
		{
			case 'id':
			case 'parent':
				if($this->field($type, 'param_type') == 'site')
				{
					if(preg_match('/[^0-9]+/', $value))
					{
						$this->error_validate($type, 'значение должно быть числом');
						$value = preg_replace('/[^0-9]+/', '', $value);
						if($type == 'id')
						{
							$this->cache["bag_string"] = true;
						}
					}
					elseif($value > 4294967295)
					{
						$this->error_validate($type, 'значение не может быть больше 4294967295');
						$value = 0;
						if($type == 'id')
						{
							$this->cache["bag_string"] = true;
						}
					}
				}
				break;
			case 'cats':
				if($this->field($type, 'param_type') == 'site')
				{
					$new_value = array();
					foreach ($value as $v)
					{
						if(preg_match('/[^0-9]+/', $v))
						{
							$this->error_validate($type, 'значение должно быть числом');
							$v = preg_replace('/[^0-9]+/', '', $v);
						}
						if($v)
						{
							$new_value[] = $v;
						}
					}
					$value = $new_value;
				}
				break;
			case 'name':
				$new_value = strip_tags($value);
				if($value !=  $new_value)
				{
					$this->error_validate($type, 'HTML-теги не допустимы');
					$value = $new_value;
				}
				break;

			case 'act':
				if($value === '1' || $value === 1 || $value === 'true' || $value === 'TRUE' || $value === true)
				{
					$value = 1;
				}
				elseif($value === '0' || $value === 0 || $value === 'false' || $value === 'FALSE' || $value === false)
				{
					$value = 0;
				}
				else
				{
					$this->error_validate($type, 'допустимы только следующие значения 1, 0, true, false');
					$value = 0;
				}
				break;
		}
		$this->field_value($type, $value);
	}

	/**
	 * Подготавливает данные о текущем элементе
	 *
	 * @return boolean
	 */
	protected function prepare_data()
	{
		$this->cache["bag_string"] = false;

		if(count($this->data) != $this->cache["count_fields"])
		{
			$this->error_validate('', 'формат данных не соответствует описанию файла');
			$this->cache["bag_string"] = true;
		}

		foreach ($this->data as $key => $value)
		{
			$this->data[$key] = trim($value);
		}

		foreach ($this->fields as $type => $k)
		{
			if($this->is_field_multiple($type))
			{
				foreach(array_keys($this->fields[$type]) as $i)
				{
					$this->fields_iterator[$type] = $i;
					$this->insert_field_data($type);
				}
			}
			else
			{
				$this->insert_field_data($type);
			}
		}
		return true;
	}

	/**
	 * Подготовка к построчному считыванию и анализу строк из файла
	 *
	 * @return void
	 */
	protected function prepare_rows()
	{

	}

	/**
	 * Импорт текущей записи
	 *
	 * @return void
	 */
	protected function import_row()
	{
		if ($this->is_field("id"))
		{
			switch($this->field("id", "param_type"))
			{
				case "site":
					$type_id = 'id';
					break;

				default:
					$type_id = 'import_id';
					break;
			}
			$this->oldrow = DB::query_fetch_array(
					"SELECT * FROM {".$this->import["table"]."} WHERE ".$type_id."='%s'"
					." AND trash='0' AND site_id=%d"
					.($this->import["type"] != 'category' && $this->import["cat_id"] ? " AND cat_id IN (".implode(",", $this->import["cat_ids"]).")" : '')
					." LIMIT 1",
					$this->field_value("id"), $this->import["site_id"]
				);
			if($this->oldrow)
			{
				$this->id = $this->oldrow["id"];
				$this->update = true;
				if(! $this->import["add_new_items"])
				{
					$this->update_row();
				}
				else return;
			}
			else
			{
				$this->update = false;
				$this->insert_row();
			}
		}
		else
		{
			$this->update = false;
			$this->insert_row();
		}

		$this->set_category_rel();

		$this->set_menu();
	}

	/**
	 * Завершающие операции импорта
	 *
	 * @return boolean
	 */
	protected function finish()
	{
		if(! isset($this->cache_data["finish"]))
		{
			$this->cache_data["finish"] = array(
				"finish_delete" => array(
					"title" => $this->diafan->_('Удаление старых записей'),
					"result" => false,
				),
				"finish_parent" => array(
					"title" => $this->diafan->_('Обработка временных данных поля Родитель'),
					"result" => false,
				),
				"finish_menu" => array(
					"title" => $this->diafan->_('Отображение элементов в меню'),
					"result" => false,
				),
			);
		}
		if(! $this->finish_delete())
		{
			return false;
		}
		if(! $this->finish_parent())
		{
			return false;
		}
		if(! $this->finish_menu())
		{
			return false;
		}

		// завершены последние операции импорта
		return true;
	}

	/**
	 * Добавляет ошибку в лог
	 *
	 * @param string $type тип поля
	 * @param string $error ошибка
	 * @param string $name имя поля, на котором произошла ошибка
	 * @param boolean $lang текст ошибки нужно переводить
	 * @return void
	 */
	protected function error_validate($type, $error, $name = false, $lang = true)
	{
		$name = $name === false ? $this->field($type, 'name') : $name;
		if($lang)
		{
			$error = $this->diafan->_($error, false);
		}
		$title = $name.(! empty($name) && ! empty($type) ? ', ' : '').$type;
		$message = array(
			($this->diafan->_('Ошибка в строке', false) . ' ' . $this->data_string_number),
			($title . (! empty($title) && ! empty($error) ? ': ' : '') . $error)
		);
		$this->errors[] = array_merge($message, $this->row);
	}

	/**
	 * Добавляет доступ к массиву this->fields
	 *
	 * @param string $type тип поля
	 * @return mixed
	 */
	protected function get_fields($type)
	{
		if(! $this->is_field($type))
		{
			return array();
		}

		if(! $this->is_field_multiple($type))
		{
			return $this->fields[$type];
		}

		return $this->fields[$type][$this->fields_iterator[$type]];
	}

	/**
	 * Определяет задано ли в импорте поле с указанным типом
	 *
	 * @param string $type тип поля
	 * @return boolean
	 */
	protected function is_field($type)
	{
		return array_key_exists($type, $this->fields);
	}

	protected function is_field_multiple($type)
	{
		return ! array_key_exists('i', $this->fields[$type]);
	}

	/**
	 * Возвращает значение поля с указанным типом или задает новое значение
	 *
	 * @param string $type тип поля
	 * @param mixed $value новое значение
	 * @return mixed
	 */
	protected function field_value($type, $value = false)
	{
		$fields = $this->get_fields($type);

		if(! isset($fields["i"]))
		{
			return '';
		}
		if($value !== false)
		{
			$this->data[$fields["i"]] = $value;
			$this->fields_iterator[$type] = $fields["i"];
		}
		else
		{
			return isset($this->data[$fields["i"]]) ? $this->data[$fields["i"]] : '';
		}
	}

	/**
	 * Возвращает данные о поле по типу
	 *
	 * @param string $type тип поля
	 * @param string $name название получаемых данных
	 * @return mixed
	 */
	protected function field($type, $name)
	{
		$fields = $this->get_fields($type);

		if(isset($fields[$name]))
		{
			return $fields[$name];
		}

		return false;
	}

	/**
	 * Добавление записи в БД, если в импорте участвуют идентификаторы элементов
	 *
	 * @return void
	 */
	protected function insert_row()
	{
		$this->id = 0;
		if($this->is_field("id") && $this->field("id", "param_type") == 'site')
		{
			$row_empty = DB::query_fetch_array("SELECT * FROM {".$this->import["table"]."} WHERE id=%d LIMIT 1", $this->field_value("id"));
		}
		$fields = array("import", "site_id", "timeedit");
		$mask = array("'%d'", "%d", "%d");
		$values = array('1', $this->import["site_id"], time());
		if($this->is_field("id") && $this->field("id", "param_type") == 'site' && ! $row_empty)
		{
			$fields[] = "id";
			$mask[] = "%d";
			$values[] = $this->field_value("id");
			$this->id = $this->field_value("id");
		}
		if($this->is_field("id") && ! $this->field("id", "param_type"))
		{
			$fields[] = "import_id";
			$mask[] = "'%s'";
			$values[] = $this->field_value("id");
		}
		if($this->is_field("act"))
		{
			$fields[] = "[act]";
			$mask[] = "'%d'";
			$values[] = ($this->field_value("act") ? 1 : 0);
		}
		elseif($this->import["act_items"])
		{
			$fields[] = "[act]";
			$mask[] = "'%d'";
			$values[] = 1;
		}
		if($this->is_field("name"))
		{
			$fields[] = "[name]";
			$mask[] = "'%s'";
			$values[] = $this->field_value("name");
		}
		if($this->import["type"] == 'category')
		{
			if(! $this->diafan->configmodules("cat", $this->import["module_name"], $this->import["site_id"]))
			{
				$this->error_validate('', 'Импорт категорий невозможен, так как в настройках модуля отключен данный параметр.');
			}
			else
			{
				if($this->is_field("parent"))
				{
					if($this->field("parent", "param_type") == 'site')
					{
						$fields[] = "parent_id";
						$mask[] = "%d";
					}
					else
					{
						$fields[] = "import_parent_id";
						$mask[] = "'%s'";
					}
					$values[] = $this->field_value("parent");
				}
				elseif($this->import["cat_id"])
				{
					$fields[] = "parent_id";
					$mask[] = "%d";
					$values[] = $this->import["cat_id"];
				}
			}
		}
		if($this->import["type"] == 'element')
		{
			if($this->is_field("cats") || $this->import["cat_id"])
			{
				if(! $this->diafan->configmodules("cat", $this->import["module_name"], $this->import["site_id"]))
				{
					$this->error_validate(($this->is_field("cats") ? 'cats' : ''), 'Импорт категорий невозможен, так как в настройках модуля отключен данный параметр.');
				}
				else
				{
					$fields[] = "cat_id";
					$mask[] = "%d";
					$values[] = $this->set_category();
				}
			}
		}
		DB::query("INSERT INTO {".$this->import["table"]."} (".implode(",", $fields).") VALUES (".implode(",", $mask).")", $values);

		if(! $this->id)
		{
			$this->id = DB::insert_id();
		}

		if($this->is_field("id") && $this->field("id", "param_type") == 'site' && $row_empty)
		{
			if($row_empty["trash"])
			{
				$this->error_validate('id', $this->diafan->_('запись с идентификатором %d перемещена в корзину, новая запись добавлена с новым идентификатом %d', $this->field_value("id"), $this->id), false, false);
			}
			elseif($row_empty["site_id"] != $this->import["site_id"])
			{
				$this->error_validate('id', $this->diafan->_('запись с идентификатором %d находится в другом разделе сайта, новая запись добавлена с новым идентификатом %d', $this->field_value("id"), $this->id), false, false);
			}
			elseif($this->import["type"] != 'category' && $this->import["cat_id"] && $row["cat_id"] != $this->import["cat_id"])
			{
				$this->error_validate('id', $this->diafan->_('запись с идентификатором %d находится в другой категории, новая запись добавлена с новым идентификатом %d', $this->field_value("id"), $this->id), false, false);
			}
			else
			{
				$this->error_validate('id', $this->diafan->_('новая запись добавлена с новым идентификатом %d', $this->id), false, false);
			}
		}
	}

	/**
	 * Обновляем записи в БД для существующего элемента
	 *
	 * @return void
	 */
	protected function update_row()
	{
		$query = "UPDATE {".$this->import["table"]."} SET"
		." import='1',"
		." site_id=%d,"
		."timeedit=%d";
		$values = array($this->import["site_id"], time());
		if($this->is_field("act"))
		{
			$query .= ", [act]='%d'";
			$values[] = ($this->field_value("act") ? 1 : 0);
		}
		elseif($this->import["act_items"])
		{
			$query .= ", [act]='%d'";
			$values[] = 1;
		}
		if($this->is_field("name"))
		{
			$query .= ", [name]='%s'";
			$values[] = $this->field_value("name");
		}
		if($this->import["type"] == 'category')
		{
			if(! $this->diafan->configmodules("cat", $this->import["module_name"], $this->import["site_id"]))
			{
				$this->error_validate('', 'Импорт категорий невозможен, так как в настройках модуля отключен данный параметр.');
			}
			else
			{
				if($this->is_field("parent"))
				{
					if($this->field("parent", "param_type") == 'site')
					{
						$query .= ", parent_id=%d";
					}
					else
					{
						$query .= ", import_parent_id='%h'";
					}
					$values[] = $this->field_value("parent");
				}
				elseif($this->import["cat_id"])
				{
					$query .= ", parent_id=%d";
					$values[] = $this->import["cat_id"];
				}
			}
		}
		if($this->import["type"] == 'element')
		{
			if($this->is_field("cats") || $this->import["cat_id"])
			{
				if(! $this->diafan->configmodules("cat", $this->import["module_name"], $this->import["site_id"]))
				{
					$this->error_validate(($this->is_field("cats") ? 'cats' : ''), 'Импорт категорий невозможен, так как в настройках модуля отключен данный параметр.');
				}
				else
				{
					$query .= ", cat_id=%d";
					$values[] = $this->set_category();
				}
			}
		}
		$query .= " WHERE id=%d";
		$values[] = $this->id;
		DB::query($query, $values);
	}

	/**
	 * Обработка поля "Категории"
	 *
	 * @param integer $index указатель на индек массива значений поля
	 * @return integer
	 */
	protected function set_category($index = false)
	{
		if($this->import["type"] != 'element')
		{
			return 0;
		}
		if(! $this->field_value("cats"))
		{
			return $this->import["cat_id"] ?: 0;
		}

		if(! isset($this->cache["cats"]))
		{
			switch($this->field("cats", "param_type"))
			{
				case 'name':
					$this->cache["cats"] =
						DB::query_fetch_key_value("SELECT id, [name] FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "name", "id");
					break;

				case 'site':
					$this->cache["cats"] =
						DB::query_fetch_key_value("SELECT id FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "id", "id");
					break;

				default:
					$this->cache["cats"] =
						DB::query_fetch_key_value("SELECT id, import_id FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "import_id", "id");
					break;
			}
		}

		// брабатываем значения, кроме типа name
		if($this->field("cats", "param_type") != 'name')
		{
			foreach($this->field_value("cats") as $key => $cat)
			{
				if($index !== false && $index != $key) continue;
				$cat = is_array($cat) ? end($cat) : $cat;
				if(empty($this->cache["cats"][$cat]))
				{
					$this->error_validate('cats', $this->diafan->_('категория %s не найдена', $cat), false, false);
					if($index !== false) return 0;
					continue;
				}
				return $this->cache["cats"][$cat];
			}
			if(empty($this->oldrow["cat_id"]) && $this->import["cat_id"])
			{
				return $this->import["cat_id"];
			}
			return 0;
		}

		// брабатываем значения только с типом name
		if(! isset($this->cache["parents"]))
		{
			switch($this->field("parent", "param_type"))
			{
				case 'name':
					$this->cache["parents"] =
						DB::query_fetch_key_array("SELECT id, parent_id, [name] FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "name");
					break;

				case 'site':
					$this->cache["parents"] =
						DB::query_fetch_key_array("SELECT id, parent_id FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "id");
					break;

				default:
					$this->cache["parents"] =
						DB::query_fetch_key_array("SELECT id, parent_id, import_id FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "import_id");
					break;
			}
		}
		$parent_ids = array();
		if($this->is_field("parent") && $this->field_value("parent"))
		{
			$cat = $this->field_value("parent");
			if(empty($this->cache["parents"][$cat]))
			{
				if($this->field("parent", "param_type") == 'name')
				{
					$parent_id = $this->add_cat($cat);
					$this->cache["parents"][$cat] = array();
					$this->cache["parents"][$cat][] = DB::query_fetch_array("SELECT id, parent_id, [name] FROM {%s_category} WHERE trash='0' AND site_id=%d AND id=%d LIMIT 1", $this->import["module_name"], $this->import["site_id"], $parent_id);
					if(! isset($this->cache["cats"][$cat]))
					{
						$this->cache["cats"][$cat] = $parent_id;
					}
					if(isset($this->cache["category"]))
					{
						unset($this->cache["category"]);
					}
					$parent_ids = array($parent_id);
				}
				else
				{
					$this->error_validate('cats', $this->diafan->_('родительская категория %s не найдена', $cat), false, false);
				}
			}
			else
			{
				$parent_ids = $this->diafan->array_column($this->cache["parents"][$cat], 'id');
			}
		}
		if(! isset($this->cache["category"]))
		{
			switch($this->field("cats", "param_type"))
			{
				case 'name':
					$this->cache["category"] =
						DB::query_fetch_key_array("SELECT id, parent_id, [name] FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "name");
					break;

				case 'site':
					$this->cache["category"] =
						DB::query_fetch_key_array("SELECT id, parent_id FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "id");
					break;

				default:
					$this->cache["category"] =
						DB::query_fetch_key_array("SELECT id, parent_id, import_id FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "import_id");
					break;
			}
		}
		foreach($this->field_value("cats") as $key => $cat)
		{
			if($index !== false && $index != $key) continue;

			// значение определено, как последовательность записей
			if(is_array($cat))
			{
				// определяем оптимальную последовательность категорий
				$sequence = array();
				if($sequences = $this->sequences_transform($this->get_sequences($cat, false, $parent_ids)))
				{
					$count = count($sequence);
					foreach($sequences as $val)
					{
						$cnt = count($val);
						if($cnt <= $count) continue;
						$sequence = $val;
						$count = $cnt;
					}
				}

				// определяем категории
				$cat_id = false;
				if($sequence) reset($sequence);
				foreach($cat as $k => $val)
				{
					if($sequence && (key($sequence) !== null) && ($current = current($sequence)))
					{
						next($sequence); $cat_id = $current;
						continue;
					}
					if($cat_id !== false)
					{
						$parent_ids = array($cat_id);
					}
					if(empty($this->cache["category"][$val]))
					{
						$parent_id = false;
						if($parent_ids)
						{
							$parent_id = reset($parent_ids);
						}
						$cat_id = $this->add_cat($val, $parent_id);
						$this->cache["category"][$val] = array();
						$this->cache["category"][$val][] = DB::query_fetch_array("SELECT id, parent_id, [name] FROM {%s_category} WHERE trash='0' AND site_id=%d AND id=%d LIMIT 1", $this->import["module_name"], $this->import["site_id"], $cat_id);
						if(! isset($this->cache["cats"][$val]))
						{
							$this->cache["cats"][$val] = $cat_id;
						}
						if(isset($this->cache["parents"]))
						{
							unset($this->cache["parents"]);
						}
						continue;
					}
					if(! $parent_ids)
					{
						$cat_ids = $this->diafan->array_column($this->cache["category"][$val], 'id');
						$cat_id = reset($cat_ids);
						continue;
					}
					foreach($this->cache["category"][$val] as $c)
					{
						if(in_array($c["parent_id"], $parent_ids))
						{
							$cat_id = $c["id"];
							continue 2;
						}
					}
					$parent_id = reset($parent_ids);
					$cat_id = $this->add_cat($val, $parent_id);
					$this->cache["category"][$val] = array();
					$this->cache["category"][$val][] = DB::query_fetch_array("SELECT id, parent_id, [name] FROM {%s_category} WHERE trash='0' AND site_id=%d AND id=%d LIMIT 1", $this->import["module_name"], $this->import["site_id"], $cat_id);
					if(! isset($this->cache["cats"][$val]))
					{
						$this->cache["cats"][$val] = $cat_id;
					}
					if(isset($this->cache["parents"]))
					{
						unset($this->cache["parents"]);
					}
					continue;
				}
				return $cat_id;
			}

			// значение определено, как не последовательность записей
			if(empty($this->cache["category"][$cat]))
			{
				$parent_id = false;
				if($parent_ids)
				{
					$parent_id = reset($parent_ids);
				}
				$cat_id = $this->add_cat($cat, $parent_id);
				$this->cache["category"][$cat] = array();
				$this->cache["category"][$cat][] = DB::query_fetch_array("SELECT id, parent_id, [name] FROM {%s_category} WHERE trash='0' AND site_id=%d AND id=%d LIMIT 1", $this->import["module_name"], $this->import["site_id"], $cat_id);
				if(! isset($this->cache["cats"][$cat]))
				{
					$this->cache["cats"][$cat] = $cat_id;
				}
				if(isset($this->cache["parents"]))
				{
					unset($this->cache["parents"]);
				}
				return $cat_id;
			}
			if(! $parent_ids)
			{
				$cat_ids = $this->diafan->array_column($this->cache["category"][$cat], 'id');
				$cat_id = reset($cat_ids);
				return $cat_id;
			}
			foreach($this->cache["category"][$cat] as $c)
			{
				if(in_array($c["parent_id"], $parent_ids))
				{
					return $c["id"];
				}
			}
			$parent_id = reset($parent_ids);
			$cat_id = $this->add_cat($cat, $parent_id);
			$this->cache["category"][$cat] = array();
			$this->cache["category"][$cat][] = DB::query_fetch_array("SELECT id, parent_id, [name] FROM {%s_category} WHERE trash='0' AND site_id=%d AND id=%d LIMIT 1", $this->import["module_name"], $this->import["site_id"], $cat_id);
			if(! isset($this->cache["cats"][$cat]))
			{
				$this->cache["cats"][$cat] = $cat_id;
			}
			if(isset($this->cache["parents"]))
			{
				unset($this->cache["parents"]);
			}
			return $cat_id;
		}
		if(empty($this->oldrow["cat_id"]) && $this->import["cat_id"])
		{
			return $this->import["cat_id"];
		}
		return 0;
	}

	/**
	 * Возвращает последовательность номеров категорий в виде многоуровнего массива,
	 * где ключами массива являются номера категорий
	 *
	 * @param array $cat последовательность категорий
	 * @param mixed $key текущий индекс в последовательности категорий
	 * @param integer $parent_ids массив номеров родительских категорий
	 * @return mixed (array|false)
	 */
	protected function get_sequences($cat, $key = false, $parent_ids = array())
	{
		$result = false;
		$cat = is_array($cat) ? $cat : array($cat);
		if($key === false)
		{
			reset($cat);
			$key = key($cat);
		}
		elseif(is_null($key) || ! array_key_exists($key, $cat))
		{
			return $result;
		}
		reset($cat);
		while(key($cat) !== $key && key($cat) !== null) next($cat);
		next($cat); $next_key = key($cat); reset($cat);

		if(! isset($this->cache["category"]))
		{
			switch($this->field("cats", "param_type"))
			{
				case 'name':
					$this->cache["category"] =
						DB::query_fetch_key_array("SELECT id, parent_id, [name] FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "name");
					break;

				case 'site':
					$this->cache["category"] =
						DB::query_fetch_key_array("SELECT id, parent_id FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "id");
					break;

				default:
					$this->cache["category"] =
						DB::query_fetch_key_array("SELECT id, parent_id, import_id FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "import_id");
					break;
			}
		}

		$val = $cat[$key];
		if(empty($this->cache["category"][$val]))
		{
			return $result;
		}

		$result = array();
		if(! $parent_ids)
		{
			$cat_ids = $this->diafan->array_column($this->cache["category"][$val], 'id');
			foreach($cat_ids as $cat_id)
			{
				$result[$cat_id] = $this->get_sequences($cat, $next_key, array($cat_id));
			}
		}
		else
		{
			foreach($this->cache["category"][$val] as $c)
			{
				if(in_array($c["parent_id"], $parent_ids))
				{
					$cat_id = $c["id"];
					$result[$cat_id] = $this->get_sequences($cat, $next_key, array($cat_id));
				}
			}
		}
		return $result ?: false;
	}

	/**
	 * Трансформация последовательности номеров категорий из многоуровнего массива в одноуровневый массив,
	 * где значениями ключей массива являются номера категорий
	 *
	 * @param array $sequences последовательность категорий в виде многоуровнего массива
	 * @return mixed (array|false)
	 */
	protected function sequences_transform($sequences)
	{
		if(! is_array($sequences))
		{
			return false;
		}
		$result = array();
		foreach($sequences as $key => $value)
		{
			if(is_array($value))
			{
				$rslt = $this->sequences_transform($value);
				if(is_array($rslt))
				{
					foreach($rslt as $k => $val)
					{
						if(empty($val)) continue;
						$result[] = array_merge(array($key), $val);
					}
				}
				else $result[] = array($key);
			}
			else $result[] = array($key);
		}
		return $result ?: false;
	}

	/**
	 * Обработка поля "Дополнительные категории"
	 *
	 * @return void
	 */
	protected function set_category_rel()
	{
		if(! $table_cats_rel = $this->get_table_cat_rel())
		{
			return;
		}
		if(! $this->diafan->configmodules("cat", $this->import["module_name"], $this->import["site_id"]))
		{
			return;
		}
		$this->cache["current_cat"] = 0;
		$this->cache["current_cats"] = array();
		if (! $this->is_field("cats"))
		{
			if(empty($this->oldrow["cat_id"]))
			{
				if($this->import["cat_id"])
				{
					$this->cache["current_cats"] = array($this->import["cat_id"]);
					$this->cache["current_cat"] = $this->import["cat_id"];
					DB::query("INSERT INTO {".$table_cats_rel."} (element_id, cat_id) VALUES (%d, %d)", $this->id, $this->import["cat_id"]);
				}
			}
			else
			{
				$this->cache["current_cat"] = $this->oldrow["cat_id"];
				$rows = DB::query_fetch_all("SELECT * FROM {".$table_cats_rel."} WHERE element_id=%d", $this->id);
				foreach ($rows as $row)
				{
					$this->cache["current_cats"][] = $row["cat_id"];
				}
			}
		}
		else
		{
			if(! isset($this->cache["cats"]))
			{
				switch($this->field("cats", "param_type"))
				{
					case 'name':
						$this->cache["cats"] =
							DB::query_fetch_key_value("SELECT id, [name] FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "name", "id");
						break;

					case 'site':
						$this->cache["cats"] =
							DB::query_fetch_key_value("SELECT id FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "id", "id");
						break;

					default:
						$this->cache["cats"] =
							DB::query_fetch_key_value("SELECT id, import_id FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "import_id", "id");
						break;
				}
			}

			if ($this->is_field("id"))
			{
				DB::query("DELETE FROM {".$table_cats_rel."} WHERE element_id=%d", $this->id);
			}
			$this->cache["current_cat"] = 0;
			if($this->field_value("cats"))
			{
				$cat_ids = array();
				foreach($this->field_value("cats") as $key => $cat)
				{
					$cat_id = false;
					if($this->import["type"] == 'element' && $this->field("cats", "param_type") == 'name')
					{
						$cat_id = $this->set_category($key);
						if($cat && ! $cat_id)
						{
							if(is_array($cat))
							{
								$this->error_validate('cats', $this->diafan->_('последовательность категорий %s не найдена', implode($this->field("cats", 'param_sequence_delimitor'), $cat)), false, false);
							}
							else $this->error_validate('cats', $this->diafan->_('категория %s не найдена', $cat), false, false);
							continue;
						}
					}
					else
					{
						$cat = is_array($cat) ? end($cat) : $cat;
						if(empty($this->cache["cats"][$cat]))
						{
							$this->error_validate('cats', $this->diafan->_('категория %s не найдена', $cat), false, false);
							continue;
						}
						$cat_id = $this->cache["cats"][$cat];
					}
					if(empty($this->cache["current_cat"]))
					{
						$this->cache["current_cat"] = $cat_id;
					}
					if(in_array($cat_id, $cat_ids))
					{
						continue;
					}
					$cat_ids[] = $cat_id;
					DB::query("INSERT INTO {".$table_cats_rel."} (element_id, cat_id) VALUES (%d, %d)", $this->id, $cat_id);
					$this->cache["current_cats"][] = $cat_id;
				}
			}
		}
	}

	/**
	 * Возвращает имя таблицы базы данных без префикса для обработки поля "Дополнительные категории"
	 *
	 * @return string
	 */
	protected function get_table_cat_rel()
	{
		switch($this->import["type"])
		{
			case 'element':
				$table_cats_rel = $this->import["module_name"].'_category_rel';
				break;

			default:
				$table_cats_rel = false;
				break;
		}
		return $table_cats_rel;
	}

	/**
	 * Обработка поля "Меню"
	 *
	 * @return void
	 */
	protected function set_menu()
	{
		if(! in_array("menu", $this->diafan->installed_modules))
			return;

		foreach($this->menus as $k => $param)
		{
			if ( ! $param["id"])
				continue;

			$value = isset($this->data[$k]) ? $this->data[$k] : '';
			if(! $value)
			{
				if($param['required'])
				{
					$this->error_validate('menu', 'значение не задано', $param["name"]);
				}
				continue;
			}

			if($this->is_field("id"))
			{
				$this->diafan->_menu->delete($this->id, $this->import["module_name"], $this->import["element_type"], $param["id"]);
			}

			if($value)
			{
				DB::query(
					"INSERT INTO {menu} ([name], module_name, element_id, element_type,"
					." cat_id, sort, [act]) VALUES ('%s', '%s', %d, '%s', %d, %d, '%d')",
					$this->field_value('name'),
					$this->import["module_name"],
					$this->id,
					$this->import["element_type"],
					$param["id"],
					$this->field_value('sort') ? $this->field_value('sort') : $this->id,
					($this->is_field('act') ? ($this->field_value('act') ? 1 : 0) : ($this->import["act_items"] ? 1 : 0))
				);
			}
		}
	}

	/**
	 * Добавляет категорию
	 *
	 * @param string $name название категории
	 * @param integer $parent_id номер родительской категории
	 * @return integer
	 */
	private function add_cat($name, $parent_id = false)
	{
		$parent_id = ($this->import["type"] == 'element' && ($parent_id !== false || $this->import["cat_id"]) ? ($parent_id !== false ? $parent_id : $this->import["cat_id"]) : $this->import["cat_id"]);
		if(! isset($this->cache["cats_names"]))
		{
			$this->cache["cats_names"] = DB::query_fetch_key_array("SELECT id, [name], parent_id FROM {%s_category} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "name");
		}
		if(! empty($this->cache["cats_names"][$name]))
		{
			$save_key = false;
			foreach($this->cache["cats_names"][$name] as $key => $value)
			{
				if($value["parent_id"] == $parent_id)
				{
					return $value["id"];
				}
				if($save_key === false
				&& isset($this->cache_data["add_cat_ids"])
				&& in_array($value["id"], $this->cache_data["add_cat_ids"]))
				{
					$save_key = $key;
				}
			}
			if($save_key !== false && ! empty($this->cache["cats_names"][$name][$save_key]["id"]))
			{
				$id = $this->cache["cats_names"][$name][$save_key]["id"];
				// если найдена категория по её названию и родительская категория несовпала,
				// но сама категория создана в рамках текущего импорта,
				// то изменяем родительскую категорию у найденной категорий
				$p_id = DB::query_result("SELECT parent_id FROM {%s_category} WHERE id=%d", $this->import["module_name"], $id);
				if($p_id != $parent_id)
				{
					DB::query("UPDATE {%s_category} SET parent_id=%d WHERE id=%d", $this->import["module_name"], $parent_id, $id);
					DB::query("UPDATE {%s_category} SET `count_children` = `count_children` + 1 WHERE id=%d", $this->import["module_name"], $parent_id);
					DB::query("DELETE FROM {%s_category_parents} WHERE element_id=%d AND parent_id=%d", $this->import["module_name"], $id, $p_id);
					DB::query("INSERT INTO {%s_category_parents} (element_id, parent_id) VALUES (%d, %d)", $this->import["module_name"], $id, $parent_id);
					$this->cache["cats_names"][$name][$save_key]["id"]["parent_id"] = $parent_id;
				}
				return $id;
			}
		}
		if(! isset($this->cache["cats_names"][$name]))
		{
			$this->cache["cats_names"][$name] = array();
		}

		$id = DB::query("INSERT INTO {%s_category} ([name], site_id, import) VALUES ('%s', %d, '%d')", $this->import["module_name"], $name, $this->import["site_id"], 1);
		DB::query("UPDATE {%s_category} SET sort=%d WHERE id=%d", $this->import["module_name"], $id, $id);
		if($this->import["act_items"])
		{
			DB::query("UPDATE {%s_category} SET [act]='%d' WHERE id=%d", $this->import["module_name"], 1, $id);
		}
		$this->cache["cats_names"][$name][] = DB::query_fetch_array("SELECT id, [name], parent_id FROM {%s_category} WHERE trash='0' AND site_id=%d AND id=%d LIMIT 1", $this->import["module_name"], $this->import["site_id"], $id);

		if($this->import["type"] == 'element' && $parent_id)
		{
			DB::query("UPDATE {%s_category} SET parent_id=%d WHERE id=%d", $this->import["module_name"], $parent_id, $id);
			DB::query("UPDATE {%s_category} SET `count_children` = `count_children` + 1 WHERE id=%d", $this->import["module_name"], $parent_id);
			DB::query("INSERT INTO {%s_category_parents} (element_id, parent_id) VALUES (%d, %d)", $this->import["module_name"], $id, $parent_id);
			$this->import["cat_ids"][] = $id;
		}

		// Сохранение псевдоссылки
		$rewrite = '';
		$text = $name;
		$element_id = $id;
		$module_name = $this->import["module_name"];
		$element_type = "cat";
		$site_id = $this->import["site_id"];
		$cat_id = 0;
		//$parent_id = $parent_id;
		$add_parents = true;
		$change_children = true;
		$this->diafan->_route->save($rewrite, $text, $element_id, $module_name, $element_type, $site_id, $cat_id, $parent_id, $add_parents, $change_children);

		// Сохранение пункта меню для автоматически созданных категорий при импорте элементов
		if($this->import["menu_cat_id"] && in_array("menu", $this->diafan->installed_modules) && $this->import["element_type"] == 'element')
		{
			$element_type = 'cat';
			$menu_cat_id = (int) $this->import["menu_cat_id"];
			if($id)
			{
				$this->diafan->_menu->delete($id, $this->import["module_name"], $element_type, $menu_cat_id);
			}

			if($menu_cat_id)
			{
				DB::query(
					"INSERT INTO {menu} ([name], module_name, element_id, element_type,"
					." cat_id, sort, [act]) VALUES ('%s', '%s', %d, '%s', %d, %d, '%d')",
					$name,
					$this->import["module_name"],
					$id,
					$element_type,
					$menu_cat_id,
					$id,
					($this->import["act_items"] ? 1 : 0)
				);
			}

			// кэш импорта
			if(! isset($this->cache_data["add_cat_ids"])) $this->cache_data["add_cat_ids"] = array();
			$this->cache_data["add_cat_ids"][] = $id;
		}

		return $id;
	}

	/**
 	 * Удаление старых записей в БД, если в импорте участвуют идентификаторы элементов
 	 *
 	 * @return void
 	 */
	protected function finish_delete()
	{
		if(! empty($this->cache_data["finish"]["finish_delete"]["result"]))
		{
			return true;
		}

		if(! $this->import["delete_items"])
		{
			$this->cache_data["finish"]["finish_delete"]["result"] = true;
			return false;
		}

		if(! $this->is_field('id'))
		{
			$this->cache_data["finish"]["finish_delete"]["result"] = true;
			return false;
		}

		$this->delete(0);

		$this->cache_data["finish"]["finish_delete"]["result"] = true;
		return false;
	}

	/**
	 * Обработка временных данных поля "Родитель"
	 *
	 * @return void
	 */
	protected function finish_parent()
	{
		if(! empty($this->cache_data["finish"]["finish_parent"]["result"]))
		{
			return true;
		}

		if(! $this->diafan->configmodules("cat", $this->import["module_name"], $this->import["site_id"]))
		{
			$this->cache_data["finish"]["finish_parent"]["result"] = true;
			return false;
		}

		if($this->import["type"] != 'category')
		{
			$this->cache_data["finish"]["finish_parent"]["result"] = true;
			return false;
		}

		if(! $this->is_field("parent") && ! $this->import["cat_id"])
		{
			$this->cache_data["finish"]["finish_parent"]["result"] = true;
			return false;
		}

		// если задано поле "Родитель" у категорий
		$rows = DB::query_fetch_all("SELECT id".(! $this->field("parent", "param_type") && ! $this->import["cat_id"] || $this->field("parent", "param_type") == "name" ? ", import_parent_id" : '').", parent_id FROM {%s_category} WHERE `import`='1'", $this->import["module_name"]);
		foreach ($rows as $row)
		{
			if($row["parent_id"])
			{
				// удаляем всех старых родителей
				DB::query("DELETE FROM {%s_category_parents} WHERE element_id=%d", $this->import["module_name"], $row["id"]);
			}

			if ((! $this->field("parent", "param_type") && ! $this->import["cat_id"] || $this->field("parent", "param_type") == "name") && $row["import_parent_id"])
			{
				if ( ! isset($this->cache["cats"][$row["import_parent_id"]]))
				{
					$this->cache["cats"][$row["import_parent_id"]] =
							DB::query_result("SELECT id FROM {%s_category} WHERE "
							.(! $this->field("parent", "param_type") && ! $this->import["cat_id"] ? "import_id='%h'" : "[name]='%s'")
							." AND trash='0' LIMIT 1", $this->import["module_name"], $row["import_parent_id"]);
				}
				$row["parent_id"] = $this->cache["cats"][$row["import_parent_id"]];
				DB::query("UPDATE {%s_category} SET parent_id=%d WHERE id=%d", $this->import["module_name"], $row["parent_id"], $row["id"]);
			}
			if($row["parent_id"])
			{
				$parent_id = $row["parent_id"];
				$parents = array();
				while ($parent_id > 0 && ! in_array($parent_id, $parents))
				{
					$parents[] = $parent_id;
					DB::query("INSERT INTO {%s_category_parents} (`element_id`, `parent_id`) VALUES (%d, %d)", $this->import["module_name"], $row["id"], $parent_id);
					$parent_id = DB::query_result("SELECT parent_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->import["module_name"], $parent_id);
				}
			}
		}
		// пересчитываем количество детей у всех категорий
		$rows = DB::query_fetch_all("SELECT id FROM {%s_category}", $this->import["module_name"]);
		foreach ($rows as $row)
		{
			$count = DB::query_result("SELECT COUNT(*) FROM  {%s_category_parents} WHERE parent_id=%d", $this->import["module_name"], $row["id"]);
			DB::query("UPDATE {%s_category} SET count_children=%d WHERE id=%d", $this->import["module_name"], $count, $row["id"]);
		}

		if(! $this->field("parent", "param_type") && ! $this->import["cat_id"] || $this->field("parent", "param_type") == "name")
		{
			DB::query("UPDATE {%s_category} SET parent_id=0 WHERE import_parent_id='' AND `import`='1'", $this->import["module_name"]);
			DB::query("ALTER TABLE {%s_category} DROP `import_parent_id`", $this->import["module_name"]);
		}

		$this->cache_data["finish"]["finish_parent"]["result"] = true;
		return false;
	}

	/**
	 * Отображение элементов в меню
	 *
	 * @return void
	 */
	protected function finish_menu()
	{
		if(! empty($this->cache_data["finish"]["finish_menu"]["result"]))
		{
			return true;
		}

		if($this->menus)
		{
			foreach ($this->menus as $param)
			{
				$this->finish_items_menu(
					$param["id"],
					$this->import["table"],
					$this->import["type"],
					$this->import["element_type"],
					true
				);
			}
		}

		if($this->import["menu_cat_id"] && $this->import["element_type"] == 'element'
		&& ! empty($this->cache_data["add_cat_ids"]))
		{ // пункты меню для автоматически созданных категорий при импорте элементов
			foreach ($this->cache_data["add_cat_ids"] as $menu_cat_id)
			{// иметация импорта категорий
				$this->finish_items_menu(
					$menu_cat_id,
					$this->import["module_name"].'_'.'category',
					'category',
					'cat',
					false
				);
			}
		}

		$this->cache_data["finish"]["finish_menu"]["result"] = true;
		return false;
	}

	/**
	 * Отображение элементов меню
	 *
	 * @param integer $menu_cat_id
	 * @param string $table
	 * @param string $type
	 * @param string $element_type
	 * @param boolean $import
	 * @return void
	 */
	protected function finish_items_menu($menu_cat_id, $table, $type, $element_type, $import = true)
	{
		$rows = DB::query_fetch_all(
				"SELECT s.*, m.id AS menu_id FROM {menu} AS m"
				." INNER JOIN {".$table."} AS s"
				." ON m.element_id=s.id AND m.element_type='%s'".($import ? " AND s.import='1'" : "")
				." WHERE m.module_name='%s' AND m.cat_id=%d AND m.trash='0'"
				.($type == 'category' ? ' ORDER BY s.count_children DESC' : ''),
				$element_type, $this->import["module_name"], $menu_cat_id
			);
		foreach ($rows as $row)
		{
			if($type == 'category')
			{
				$menu_parent = 0;
				if($row["parent_id"])
				{
					$parents = $this->diafan->get_parents($row["id"], $this->import["module_name"].'_category');
					$menu_parent = DB::query_result(
							"SELECT m.id FROM {menu} AS m"
							." INNER JOIN {%s_category} AS s"
							." ON m.element_id=s.id AND m.element_type='cat' AND s.trash='0'"
							." WHERE s.id IN (".implode(',', $parents).") AND m.cat_id=%d AND m.trash='0'"
							." ORDER BY s.count_children ASC LIMIT 1",
							$this->import["module_name"], $menu_cat_id);
				}
				if(! $menu_parent)
				{
					$menu_parent = DB::query_result("SELECT id FROM {menu} WHERE module_name='site' AND element_id=%d AND element_type='element' AND cat_id=%d AND trash='0'", $this->import["site_id"], $menu_cat_id);
				}

				DB::query("UPDATE {menu} SET parent_id=%d, access='%d' WHERE id=%d", $menu_parent, $row["access"], $row["menu_id"]);
				if(! $menu_parent)
					continue;

				$menu_parents = $this->diafan->get_parents($menu_parent, 'menu');
				$menu_parents[] = $menu_parent;
				foreach ($menu_parents as $m)
				{
					DB::query("INSERT INTO {menu_parents} (parent_id, element_id) VALUES (%d, %d)", $m, $row["menu_id"]);
				}
			}
			else
			{
				$menu_parent = 0;
				if($type == 'element' && $row["cat_id"])
				{
					$parents = $this->diafan->get_parents($row["cat_id"], $this->import["module_name"].'_category');
					$parents[] = $row["cat_id"];
					$menu_parent = DB::query_result(
							"SELECT m.id FROM {menu} AS m"
							." INNER JOIN {%s_category} AS s"
							." ON m.element_id=s.id AND m.element_type='cat' AND s.trash='0'"
							." WHERE s.id IN (".implode(',', $parents).") AND m.cat_id=%d AND m.trash='0'"
							." ORDER BY s.count_children ASC LIMIT 1",
							$this->import["module_name"], $menu_cat_id);
				}
				if(! $menu_parent)
				{
					$menu_parent = DB::query_result("SELECT id FROM {menu} WHERE module_name='site' AND element_id=%d AND element_type='element' AND cat_id=%d AND trash='0'", $this->import["site_id"], $menu_cat_id);
				}

				DB::query("UPDATE {menu} SET parent_id=%d, access='%d' WHERE id=%d", $menu_parent, (! empty($row["access"]) ? $row["access"] : ''), $row["menu_id"]);
				if(! $menu_parent)
					continue;

				$menu_parents = $this->diafan->get_parents($menu_parent, 'menu');
				$menu_parents[] = $menu_parent;
				foreach ($menu_parents as $m)
				{
					DB::query("INSERT INTO {menu_parents} (parent_id, element_id) VALUES (%d, %d)", $m, $row["menu_id"]);
				}
			}
		}
		// пересчитываем количество детей у всех пунктов меню
		$rows = DB::query_fetch_all("SELECT id FROM {menu} WHERE cat_id=%d", $menu_cat_id);
		foreach ($rows as $row)
		{
			$count = DB::query_result("SELECT COUNT(*) FROM  {menu_parents} WHERE parent_id=%d", $row["id"]);
			DB::query("UPDATE {menu} SET count_children=%d WHERE id=%d", $count, $row["id"]);
		}
	}

	/**
	 * Удаление записей в БД
	 *
	 * @param mixed $import (0|1|false)
	 * @return void
	 */
	protected function delete($import = false)
	{
		switch($this->import["type"])
		{
			case 'element':
				$this->import["element_type"] = 'element';
				break;

			case 'category':
				$this->import["element_type"] = 'cat';
				break;

			default:
				$this->import["element_type"] = $this->import["type"];
				break;
		}
		$this->import["table"] = $this->import["module_name"].($this->import["type"] != 'element' ? "_".$this->import["type"] : "");
		$where =  '';
		if($import !== false)
		{
			$where = " AND `import`='".$import."'";
		}
		if($this->import["type"] == 'element' && $this->import["cat_id"])
		{
			$where .= " AND cat_id=".$this->import["cat_id"];
		}
		$ids = DB::query_fetch_value("SELECT id FROM {".$this->import["table"]."} WHERE site_id=%d".$where, $this->import["site_id"], "id");
		if(! $ids)
		{
			return;
		}
		DB::query("DELETE FROM {".$this->import["table"]."} WHERE id IN(%s)", implode(",", $ids));
		switch($this->import["type"])
		{
			case 'element':
				DB::query("DELETE FROM {%s_category_rel} WHERE element_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				break;

			case 'category':
				DB::query("DELETE FROM {%s_category_parents} WHERE element_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_category_rel} WHERE cat_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				break;
		}
		if(in_array("munu", $this->diafan->installed_modules)) $this->diafan->_menu->delete($ids, $this->import["module_name"], $this->import["element_type"]);
		$this->diafan->_route->delete($ids, $this->import["module_name"], $this->import["element_type"]);
		DB::query("DELETE FROM {redirect} WHERE module_name='%s' AND element_id IN(%s) AND element_type='%s'", $this->import["module_name"], implode(",", $ids), $this->import["element_type"]);

		// удаляем автозаполнение
		if($import !== false && $this->import["type"] == 'element')
		{
			$where = " AND `import`='".$import."'";
			$ids = DB::query_fetch_value("SELECT id FROM {%s_category} WHERE site_id=%d".$where, $this->import["module_name"], $this->import["site_id"], "id");
			if($ids)
			{
				DB::query("DELETE FROM {%s_category} WHERE id IN(%s)", $this->import["module_name"], implode(",", $ids));
			}
		}
	}

	/**
	 * Удаляет импортированные элементы с сайта
	 *
	 * @param integer $cat_id идентификатор описания импорта/экспорта
	 * @return void
	 */
	public function remove($cat_id)
	{
		$this->cat_id = $cat_id;
		if(! $this->init_config())
		{
			return false;
		}
		if(! $modules = $this->diafan->_service->modules_express())
		{
			return false;
		}
		else $modules = $this->diafan->array_column($modules, "name");
		if(! $this->import["module_name"] || ! in_array($this->import["module_name"], $modules))
		{
			return false;
		}

		// проверяем права доступа
		if(! $this->diafan->_users->roles("init", "service/express", array(), 'admin')
		|| ! $this->diafan->_users->roles("init", $this->import["module_name"], array(), 'admin'))
		{
			return false;
		}

		// устанавливаем блокировку процесса
		if($this->diafan->_service->busy())
		{
			return false;
		}
		else $this->diafan->_service->busy(true);

		$this->delete(1);
		$this->diafan->_cache->delete("", $this->import["module_name"]);

		// снимаем блокировку процесса
		$this->diafan->_service->busy(false);
		return true; //$this->diafan->redirect_js(URL.'error3/');
	}

	/**
	 * Публикует / скрывает результаты импорта
	 *
	 * @param integer $cat_id идентификатор описания импорта/экспорта
	 * @param boolean $act активность элемента на сайте
	 * @return void
	 */
	public function act($cat_id, $act)
	{
		$this->cat_id = $cat_id;
		if(! $this->init_config())
		{
			return false;
		}
		if(! $modules = $this->diafan->_service->modules_express())
		{
			return false;
		}
		else $modules = $this->diafan->array_column($modules, "name");
		if(! $this->import["module_name"] || ! in_array($this->import["module_name"], $modules))
		{
			return false;
		}

		// проверяем права доступа
		if(! $this->diafan->_users->roles("init", "service/express", array(), 'admin')
		|| ! $this->diafan->_users->roles("init", $this->import["module_name"], array(), 'admin'))
		{
			return false;
		}

		// устанавливаем блокировку процесса
		if($this->diafan->_service->busy())
		{
			return false;
		}
		else $this->diafan->_service->busy(true);

		$this->import["table"] = $this->import["module_name"].($this->import["type"] != 'element' ? "_".$this->import["type"] : "");
		switch($this->import["type"])
		{
			case 'element':
				$this->import["element_type"] = 'element';
				break;

			case 'category':
				$this->import["element_type"] = 'cat';
				break;

			default:
				$this->import["element_type"] = $this->import["type"];
				break;
		}

		DB::query("UPDATE {".$this->import["table"]."} SET [act]='%d' WHERE site_id=%d AND import='1'"
			.($this->import["type"] == 'element' && $this->import["cat_id"] ? " AND cat_id=".$this->import["cat_id"] : ''),
			$act ? 1 : 0, $this->import["site_id"]);

		// индексирует / удаляет индекс для карты сайта
		if(in_array("map", $this->diafan->installed_modules))
		{
			if($act)
			{
				$polog = 0; $nastr = $this->import["count_part"]; $nastr = $nastr < self::MIN_NASTR ? self::MIN_NASTR : $nastr;
				while ($rows = DB::query_range_fetch_all("SELECT * FROM {".$this->import["table"]."} WHERE site_id=%d AND import='1'"
				.($this->import["type"] == 'element' && $this->import["cat_id"] ? " AND cat_id=".$this->import["cat_id"] : ''),
				$this->import["site_id"], $polog, $nastr))
				{
					$polog += $nastr;
					foreach($rows as $i => &$row)
					{
						if(! empty($row["map_no_show"]))
						{
							unset($rows[$i]);
							continue;
						}
						$row["module_name"] = $this->import["module_name"];
						$row["element_type"] = $this->import["element_type"];
					}
					$this->diafan->_map->index_elements($rows);
				}
			}
			else
			{
				$polog = 0; $nastr = $this->import["count_part"]; $nastr = $nastr < self::MIN_NASTR ? self::MIN_NASTR : $nastr;
				while ($rows = DB::query_range_fetch_all("SELECT id FROM {".$this->import["table"]."} WHERE site_id=%d AND import='1'"
				.($this->import["type"] == 'element' && $this->import["cat_id"] ? " AND cat_id=".$this->import["cat_id"] : ''), $this->import["site_id"], $polog, $nastr))
				{
					$polog += $nastr;
					if($ids = $this->diafan->array_column($rows, "id"))
					{
						$this->diafan->_map->delete($ids, $this->import["module_name"], $this->import["element_type"]);
					}
				}
			}
		}

		if($this->import["type"] != 'element')
		{
			DB::query("UPDATE {menu} SET [act]='%d' WHERE module_name='%s' AND element_type='%s' AND element_id IN (SELECT id FROM {".$this->import["table"]."} WHERE site_id=%d AND `import`='1')", $act ? 1 : 0, $this->import["module_name"], $this->import["element_type"], $this->import["site_id"]);
		}

		// публикует / скрывает результаты импорта автозаполнения
		if($this->import["type"] == 'element')
		{
			$where = " AND `import`='1'";

			$ids = DB::query_fetch_value("SELECT id FROM {%s_category} WHERE site_id=%d".$where, $this->import["module_name"], $this->import["site_id"], "id");
			if($ids)
			{
				DB::query("UPDATE {%s_category} SET [act]='%d' WHERE id IN(%s)", $this->import["module_name"], $act ? 1 : 0, implode(",", $ids));
			}
		}

		$this->diafan->_cache->delete("", $this->import["module_name"]);

		// снимаем блокировку процесса
		$this->diafan->_service->busy(false);
		return true; //$this->diafan->redirect_js(URL.'success1/');
	}

	/**
	 * Определяет наличие результатов импорта
	 *
	 * @param integer $cat_id идентификатор описания импорта/экспорта
	 * @return binary
	 */
	public function detect($cat_id)
	{
		// Флаг импорта: ошибка
		if(! defined('IMPORT_ERROR')) define('IMPORT_ERROR', 1 << 0);                      // 0001
		// Флаг импорта: отказ в доступе
		if(! defined('IMPORT_ACCESS_DENIED')) define('IMPORT_ACCESS_DENIED', 1 << 1);      // 0010
		// Флаг импорта: наличие записей, неотображаемых на сайте
		if(! defined('IMPORT_ACTIVE')) define('IMPORT_ACTIVE', 1 << 2);                    // 0100
		// Флаг импорта: наличие записей, отображаемых на сайте
		if(! defined('IMPORT_DEACTIVE')) define('IMPORT_DEACTIVE', 1 << 3);                // 1000
		// Флаг импорта: наличие записей, неотображаемых и отображаемых на сайте
		if(! defined('IMPORT_ALL')) define('IMPORT_ALL', IMPORT_ACTIVE | IMPORT_DEACTIVE); // 1100

		$this->cat_id = $cat_id;
		if(! $this->init_config())
		{
			return IMPORT_ERROR;
		}
		if(! $modules = $this->diafan->_service->modules_express())
		{
			return IMPORT_ERROR;
		}
		else $modules = $this->diafan->array_column($modules, "name");
		if(! $this->import["module_name"] || ! in_array($this->import["module_name"], $modules))
		{
			return IMPORT_ERROR;
		}

		// проверяем права доступа
		if(! $this->diafan->_users->roles("init", "service/express", array(), 'admin')
		|| ! $this->diafan->_users->roles("init", $this->import["module_name"], array(), 'admin'))
		{
			return IMPORT_ACCESS_DENIED;
		}

		$this->import["table"] = $this->import["module_name"].($this->import["type"] != 'element' ? "_".$this->import["type"] : "");
		switch($this->import["type"])
		{
			case 'element':
				$this->import["element_type"] = 'element';
				break;

			case 'category':
				$this->import["element_type"] = 'cat';
				break;

			default:
				$this->import["element_type"] = $this->import["type"];
				break;
		}

		$where = '';
		if($this->import["type"] == 'element' && $this->import["cat_id"])
		{
			$where .= " AND cat_id=".$this->import["cat_id"];
		}

		$act_import = DB::query_result("SELECT id FROM {".$this->import["table"]."} WHERE import='1' AND [act]='0' AND site_id=%d ".$where." LIMIT 1", $this->import["site_id"]);
		$deact_import = DB::query_result("SELECT id FROM {".$this->import["table"]."} WHERE import='1' AND [act]='1' AND site_id=%d ".$where." LIMIT 1", $this->import["site_id"]);

		$result = 0; // (0 << 0);
		if($act_import) $result = $result | IMPORT_ACTIVE;
		if($deact_import) $result = $result | IMPORT_DEACTIVE;

		return $result;
	}

	/**
	 * Возвращает идентификатор описания последнего импорта
	 *
	 * @return integer
	 */
	public function last_cat_id()
	{
		return (int) $this->last_cat_id;
	}

	/**
	 * Сохраняет лог
	 *
	 * @param string $content содержание файла
	 * @param string $file_path путь до файла относительно корня сайта
	 * @param string $encoding кодировка содержания
	 * @return integer
	 */
	protected function save_log($content, $file_path = false, $encoding = 'cp1251')
	{
		$file_path = $file_path ?: $this->dir_path.'/'.self::FILE_ERRORS_LOG;
		if($encoding == 'cp1251')
		{
			$content = utf::to_windows1251($content);
		}
		$exists = file_exists(ABSOLUTE_PATH.$file_path);
		$file_errors_log = $this->file_errors_log;
		if($file_errors_log && is_array($file_errors_log))
		{
			$file_errors_log[] = $file_path;
			$this->file_errors_log = $file_errors_log;
		}
		else $this->file_errors_log = array($file_path);
		// file_put_contents(ABSOLUTE_PATH.$file_path, ($exists ? PHP_EOL . $content : $content), FILE_APPEND);
		return File::save_file(($exists ? PHP_EOL . $content : $content), $file_path, true);
	}

	/**
	 * Удаляет лог
	 *
	 * @param string $file_path путь до файла относительно корня сайта
	 * @return void
	 */
	protected function delete_log($file_path = false)
	{
		$this->file_errors_log = false;
		$file_path = $file_path ?: $this->dir_path.'/'.self::FILE_ERRORS_LOG;
		if(file_exists(ABSOLUTE_PATH.$file_path) && is_file(ABSOLUTE_PATH.$file_path))
		{
			unlink(ABSOLUTE_PATH.$file_path);
		}
	}

	/**
	 * Возвращает массив имен лог-файлов относительно корня сайта
	 *
	 * @return array
	 */
	public function get_log()
	{
		return $this->file_errors_log;
	}

	/**
	 * Скачивание лог-файлов
	 *
	 * @return array
	 */
	public function download_log()
	{
		if(! $this->diafan->_users->roles("init", "service/express", array(), 'admin'))
		{
			exit; //Custom::inc('includes/404.php');
		}
		$file_errors_log = $this->file_errors_log;
		if(! $file_errors_log || ! is_array($file_errors_log)) exit;
		$files = array(); $files_size = 0;
		foreach($file_errors_log as $file)
		{
			if(! file_exists(ABSOLUTE_PATH.$file) || ! is_readable(ABSOLUTE_PATH.$file)) continue;
			$files[] = $file;
			$files_size += filesize(ABSOLUTE_PATH.$file);
		}
		if(empty($files)) exit;

		$is_zip = false; $filename = '';
		if(class_exists('ZipArchive'))
		{
			$filename = 'tmp/'.md5($this->diafan->uid()).'.zip';
			$zip = new ZipArchive;
			if ($zip->open(ABSOLUTE_PATH.$filename, ZipArchive::CREATE) === true)
			{
				foreach($files as $file)
				{
					$zip->addFile(ABSOLUTE_PATH.$file, basename(ABSOLUTE_PATH.$file));
				}
				$zip->close();
				$is_zip = true;
				$files_size = filesize(ABSOLUTE_PATH.$filename);
			}
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
		header('Content-Disposition: attachment; filename=' . self::FILE_ERRORS_LOG.($is_zip ? '.zip' : ''));
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Accept-Ranges: bytes');
		header('Content-Length: ' . $files_size);
		header('Connection: close');
		if($is_zip) { readfile(ABSOLUTE_PATH.$filename); }
		else { foreach($files as $filename) { echo file_get_contents(ABSOLUTE_PATH.$filename)/* . PHP_EOL*/; } }
		exit;
	}

	/**
	 * Возвращает максимальную позицию в импорте
	 *
	 * @return integer
	 */
	public function get_max()
	{
		if(! isset($this->cache["max"]))
		{
			$this->cache["max"] = (int) DB::query_result("SELECT COUNT(*) FROM {".self::TABLE_NAME."} WHERE 1=1");
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
		return (int) DB::query_result("SELECT COUNT(*) FROM {".self::TABLE_NAME."} WHERE trash='1'");
	}

	/**
	 * Возвращает информацию о завершении импорта
	 *
	 * @return array
	 */
	public function get_finish()
	{
		$result = array();

		//кеширование
		if($this->cache_data = $this->diafan->_cache->get($this->cache_meta, 'service', CACHE_GLOBAL))
		{
			if(! empty($this->cache_data) && ! empty($this->cache_data["finish"]))
			{
				$result = $this->cache_data["finish"];
			}
		}

		return $result;
	}
}
