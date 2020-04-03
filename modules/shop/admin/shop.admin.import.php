<?php
/**
 * Импорт
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
 * Shop_admin_import
 */
class Shop_admin_import extends Diafan
{
	/**
	 * @var array конфигурация текущего импорта
	 */
	private $import;

	/**
	 * @var array характеристики товара в магазине
	 */
	private $params;

	/**
	 * @var array поля "показывать в меню"
	 */
	private $menus;

	/**
	 * @var array поля, заданные для текущего импорта
	 */
	private $fields;

	private $fields_iterator;

	/**
	 * @var string данные о текущем элементе импорта
	 */
	private $data_string;

	/**
	 * @var integer номер текущей строки
	 */
	private $data_string_number = 0;

	/**
	 * @var array данные о текущем элементе импорта
	 */
	private $data;

	/**
	 * @var integer номер текущего элемента импорта
	 */
	private $id;

	/**
	 * @var boolean текущий элемент найден в системе и будет обнвлен
	 */
	private $update;

	/**
	 * @var array старые данные об импортируемом элементе
	 */
	private $oldrow;

	/**
	 * @var resource ссылка на файл импорта
	 */
	private $handle;

	/**
	 * @var array ошибки импорта
	 */
	private $errors;

	/**
	 * @var integer текущее значение поля для сортировки
	 */
	private $sort;

	/**
	 * Загружает файл импорта
	 * @return void
	 */
	public function upload()
	{
		if (isset($_FILES["file"]) && is_array($_FILES["file"]) && $_FILES["file"]['name'] != '')
		{
			File::upload_file($_FILES["file"]['tmp_name'], 'tmp/shopimport');
			$this->sort = count(explode("\n", file_get_contents(ABSOLUTE_PATH.'/tmp/shopimport')));
			echo '<meta http-equiv="Refresh" content="0; url='.URL.'?ftell=0&amp;upload=true&amp;sort='.$this->sort.'">';
			exit;
		}
		elseif(! file_exists(ABSOLUTE_PATH.'/tmp/shopimport'))
		{
			$this->diafan->redirect(URL);
			exit;
		}
		$this->sort = $this->diafan->filter($_GET, "int", "sort");

		// устанавливает настройки импорта
		$this->init_config();

		$result = $this->import();
		if($this->errors)
		{
			if($result == 'next')
			{
				echo '<p><a href="'.URL.'?ftell='.ftell($this->handle).'&amp;upload=true&amp;data_string_number='.$this->data_string_number.'&amp;sort='.$this->sort.'" class="btn">'.$this->diafan->_('Продолжить импорт').'</a></p>';
			}
			echo '<div class="error"><p>'.implode("</p><p>", $this->errors).'</p></div>';
			return;
		}
		switch($result)
		{
			case 'success':
				$this->diafan->redirect_js(URL.'success1/');
				exit;
				break;

			case 'next':
				echo '
				Imported: '.$this->data_string_number.'
				<meta http-equiv="Refresh" content="0; url='.URL.'?ftell='.ftell($this->handle).'&amp;upload=true&amp;data_string_number='.$this->data_string_number.'&amp;sort='.$this->sort.'">';
				exit;
				break;

			case 'empty':
				$this->diafan->redirect_js(URL);
				exit;
				break;
		}
	}

	/**
	 * Устанавливает настройки импорта
	 *
	 * @return void
	 */
	private function init_config()
	{
		$this->import = DB::query_fetch_array("SELECT * FROM {shop_import_category} WHERE id=%d LIMIT 1", $this->diafan->_route->cat);
		$this->import["table"] = 'shop'.($this->import["type"] != 'good' ? "_".$this->import["type"] : "");
		$this->import["end_string"] = htmlspecialchars_decode($this->import["end_string"]);
		if(! $this->import["count_part"])
		{
			$this->import["count_part"] = 20;
		}
		if(! $this->import['delimiter'])
		{
			$this->import["delimiter"] = ";";
		}
		if(! $this->import['sub_delimiter'])
		{
			$this->import["sub_delimiter"] = "|";
		}
		if($this->import["cat_id"])
		{
			$this->import["cat_ids"] = $this->diafan->get_children($this->import["cat_id"], "shop_category");
			$this->import["cat_ids"][] = $this->import["cat_id"];
		}

		$k = 0;
		$this->fields = array();
		$this->fields_iterator = array();
		$this->params = array();
		$this->menus = array();

		//получаем типы полей учавствующих в импорте
		$rows = DB::query_fetch_all("SELECT type, name, required, params FROM {shop_import} WHERE trash='0' AND cat_id=%d ORDER BY sort ASC", $this->diafan->_route->cat);
		foreach ($rows as $row)
		{
			$k++;
			if ($row["type"] == "param")
			{
				$this->params[$k-1] = array(
						'name' => $row["name"],
						'required' => $row["required"],
					);
				$params = unserialize($row["params"]);
				$this->params[$k-1]["id"] = $params["id"];
				$this->params[$k-1]["select_type"] = $params["select_type"];
				$this->params[$k-1]["directory"] = $params["directory"];
				$p = DB::query_fetch_array("SELECT type, config FROM {shop_param} WHERE id=%d LIMIT 1", $params["id"]);
				$this->params[$k-1]["type"] = $p["type"];
				$this->params[$k-1]["config"] = unserialize($p["config"]);
				$this->params[$k-1]["config"]["param_id"] = $params["id"];
				$this->params[$k-1]["values"] = array();
				if ($this->params[$k-1]["type"] == 'select' || $this->params[$k-1]["type"] == 'multiple')
				{
					$this->params[$k-1]["values"] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param_select} WHERE param_id=%d", $params["id"], "name", "id");
				}
				continue;
			}
			if($row["type"] == 'menu')
			{
				$params = unserialize($row["params"]);
				$this->menus[$k-1] = array(
						'name' => $row["name"],
						'required' => $row["required"],
						'id' => $params["id"],
					);
				continue;
			}
			$new_field = array(
				'i' => $k - 1,
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
				$this->fields[$row["type"]][$k - 1] = $new_field;
			}
			else
			{
				$this->fields[$row["type"]] = $new_field;
			}
		}
		$this->cache["count_fields"] = count($rows);
		switch($this->import["type"])
		{
			case 'good':
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
	}

	/**
	 * Импорт
	 *
	 * @return string $result
	 */
	private function import()
	{
		if (empty($this->fields))
		{
			return 'empty';
		}

		// первая итерация импорта
		if (empty($_GET['ftell']))
		{
			// подготовка базы данных
			$this->prepare();
		}

		Custom::inc("includes/image.php");

		$this->handle = fopen(ABSOLUTE_PATH.'tmp/shopimport', "r");
		if (isset($_GET['ftell']))
		{
			fseek($this->handle, $_GET['ftell']);
		}
		$this->data_string_number = $this->diafan->filter($_GET, "int", 'data_string_number');
		$i = 1;
		$cache = array();
		$is_end = true;
		$header = false;
		// построчное считывание и анализ строк из файла
		while (($data_string = fgets($this->handle)) !== false)
		{
			if ($this->import["encoding"] == 'cp1251')
			{
				$data_string = utf::to_utf($data_string);
			}
			if(! $is_end)
			{
				$this->data_string .= $data_string;
			}
			else
			{
				$this->data_string = $data_string;
			}
			if (! $this->data_string_number && $this->import["header"] && ! $header)
			{
				$header = true;
				$this->data_string = '';
				continue;
			}

			if(! $is_end = $this->prepare_data())
			{
				continue;
			}

			$this->data_string_number++;
			if($this->cache["bag_string"])
			{
				continue;
			}

			$this->id = 0;

			if ($this->is_field("id"))
			{
				switch($this->field("id", "param_type"))
				{
					case "site":
						$type_id = 'id';
						break;

					case "article":
						$type_id = 'article';
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
					$this->update_row();
				}
				else
				{
					$this->update = false;
					$this->insert_row();
				}
			}
			else
			{
				$this->insert_row();
			}

			$this->set_images();

			$this->set_access();

			$this->set_category_rel();

			if ($this->import["type"] == 'good')
			{
				$this->set_price_count();

				$this->set_params();

				$this->set_rels();
			}

			$this->set_rewrite();

			$this->set_redirect();

			$this->set_map();

			$this->set_menu();

			if ($i == $this->import["count_part"])
			{
				return 'next';
			}

			$i++;
		}
		fclose($this->handle);
		File::delete_file('tmp/shopimport');

		$this->finish_update_sort();

		$this->finish_rels();

		$this->finish_delete();

		$this->finish_parent();

		$this->finish_access();

		$this->finish_menu();

		$this->diafan->_cache->delete("", "shop");
		return 'success';
	}

	/**
	 * Подготовка базы данных
	 *
	 * @return void
	 */
	private function prepare()
	{
		// включаем режим обновления
		DB::query("UPDATE {".$this->import["table"]."} SET `import`='0' WHERE `import`='1'"
			  .($this->import["type"] == 'good' && $this->import["cat_id"] ? " AND cat_id=".$this->import["cat_id"] : ''));

		// удаляет неописанные в файле импорта записи
		$this->prepare_delete();

		// подготовка к импорту поля "Родитель"
		$this->prepare_parent();

		// подготовка к импорту поля "Связанные товары"
		$this->prepare_rels();
	}

	/**
	 * Удаление записей в БД, если в импорте НЕ участвуют идентификаторы элементов
	 *
	 * @return void
	 */
	private function prepare_delete()
	{
		if(! $this->import["delete_items"])
			return;

		// удалим в конце все не помеченные import='1'
		if($this->is_field('id'))
			return;

		$this->delete();
	}

	/**
	 * Подготавливает к импорту поля "Родитель"
	 *
	 * @return void
	 */
	public function prepare_parent()
	{
		if ($this->import["type"] != 'category')
			return;

		if (! $this->is_field("parent"))
			return;

		if($this->field("parent", "param_type") == 'site')
			return;

		DB::query("ALTER TABLE {shop_category} ADD `import_parent_id` VARCHAR(100) NOT NULL AFTER `import_id`");
	}

	/**
	 * Подготавливает к импорту поля "Связанные товары"
	 *
	 * @return void
	 */
	public function prepare_rels()
	{
		if ($this->import["type"] != 'good')
			return;

		if (! $this->is_field("id") || ! $this->is_field("rel_goods"))
			return;

		if($this->field("rel_goods", "param_type") == 'site')
			return;

		DB::query("ALTER TABLE {shop_rel} ADD `rel_element_id_temp` VARCHAR(100) NOT NULL DEFAULT ''");
	}

	private function insert_field_data($type)
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
			if (in_array($type, array("cats", "rel_goods", "images", "access", "yandex", "google", "price", "count")))
			{
				$d = explode($this->import["sub_delimiter"], $value);
				$value = array();
				foreach ($d as $i => $v)
				{
					$v = trim($v);
					if(! $v)
						continue;

					$value[$i] = $v;
				}
			}
			// валидация
			switch($type)
			{
				case 'id':
				case 'parent':
				case 'brand':
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
				case 'rel_goods':
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
				case 'keywords':
				case 'descr':
				case 'title_meta':
				case 'canonical':
				case 'measure_unit':
					$new_value = strip_tags($value);
					if($value !=  $new_value)
					{
						$this->error_validate($type, 'HTML-теги не допустимы');
						$value = $new_value;
					}
					break;
				case 'article':
					$new_value = strip_tags($value);
					if($value !=  $new_value)
					{
						$this->error_validate($type, 'HTML-теги не допустимы');
						$value = $new_value;
					}
					if(utf::strlen($value) > 30)
					{
						$this->error_validate($type, 'значение поля должно быть не более 30 символов');
					}
					break;
				case 'show_yandex':
				case 'show_google':
				case 'no_buy':
				case 'act':
				case 'hit':
				case 'new':
				case 'action':
				case 'is_file':
				case 'map_no_show':
				case 'hit':
				case 'new':
				case 'action':
				case 'is_file':
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
				case 'sort':
					if(preg_match('/[^0-9]+/', $value))
					{
						$this->error_validate($type, 'значение должно быть числом');
						$value = preg_replace('/[^0-9]+/', '', $value);
					}
					break;
				case 'admin_id':
					if(preg_match('/[^0-9]+/', $value))
					{
						$this->error_validate($type, 'значение должно быть числом');
						$value = preg_replace('/[^0-9]+/', '', $value);
					}
					if($value)
					{
						if(! isset($this->cache["admin_id"][$value]))
						{
							$this->cache["admin_id"][$value] = DB::query_result("SELECT id FROM {users} WHERE id=%d AND trash='0' LIMIT 1", $value);
						}
						if(! $this->cache["admin_id"][$value])
						{
							$this->error_validate($type, 'пользователя не существует');
							$value = 0;
						}
					}
					break;
				case 'theme':
					if(! Custom::exists('themes/'.$value))
					{
						$this->error_validate($type, $this->diafan->_('файл %s не существует', ABSOLUTE_PATH.'themes/'.$value), false, false);
						$value = '';
					}
					break;
				case 'view':
				case 'view_rows':
				case 'view_element':
					if(! Custom::exists('modules/shop/views/shop.view.'.$value.'.php'))
					{
						$this->error_validate($type, $this->diafan->_('файл %s не существует', ABSOLUTE_PATH.'modules/shop/views/shop.view.'.$value.'.php'), false, false);
						$value = '';
					}
					break;
				case 'date_start':
				case 'date_finish':
					if($error = Validate::datetime($value))
					{
						$this->error_validate($type, $error);
						$value = 0;
					}
					else
					{
						$value = $this->diafan->unixdate($value);
						if($this->field($type, 'param_date_start') > $value)
						{
							$this->error_validate($type, $this->diafan->_('значение не должно быть меньше %s', date('d.m.Y H:i', $this->field($type, 'param_date_start'))), false, false);
							$value = 0;
						}
						elseif($this->field($type, 'param_date_finish') < $value)
						{
							$this->error_validate($type, $this->diafan->_('значение не должно быть больше %s', date('d.m.Y H:i', $this->field($type, 'param_date_finish'))), false, false);
							$value = 0;
						}
					}
					break;
				case 'access':
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
							if(! isset($this->cache["roles"][$v]))
							{
								$this->cache["roles"][$v] = DB::query_result("SELECT id FROM {users_role} WHERE id=%d AND trash='0' LIMIT 1", $v);
							}
							if(! $this->cache["roles"][$v])
							{
								$this->error_validate($type, 'роли пользователя не существует');
								$v = 0;
							}
						}
						if($v)
						{
							$new_value[] = $v;
						}
					}
					$value = $new_value;
					break;
				case 'yandex':
				case 'google':
					$value = implode("\n", $value);
					break;
				case 'priority':
					if(preg_match('/[^0-9\.\,]+/', $value))
					{
						$this->error_validate($type, 'значение должно быть дискретным числом');
						$value = preg_replace('/[^0-9\.\,]+/', '', $value);
					}
					$value = (float)str_replace(',', '.', $value);
					if($value < 0 || $value > 1)
					{
						$this->error_validate($type, 'значение должно быть в диапазоне от 0 до 1');
						$value = 0;
					}
					break;
				case 'changefreq':
					if(! $value)
					{
						$value = 'monthly';
					}
					if(! in_array($value, array('monthly', 'always', 'hourly', 'daily', 'weekly', 'yearly', 'never')))
					{
						$this->error_validate($type, 'поле должно иметь одно из значений: monthly, always, hourly, daily, weekly, yearly, never');
						$value = 'monthly';
					}
					break;
				case 'weight':
				case 'length':
				case 'width':
				case 'height':
					if(preg_match('/[^0-9\.\,]+/', $value))
					{
						$this->error_validate($type, 'значение должно быть дискретным числом');
						$value = preg_replace('/[^0-9\.\,]+/', '', $value);
					}
					$value = (float)str_replace(',', '.', $value);
					break;
			}
			$this->field_value($type, $value);
	}

	/**
	 * Подготавливает данные о текущем элементе
	 *
	 * @return void
	 */
	private function prepare_data()
	{
		$this->cache["bag_string"] = false;

		if($this->import["end_string"])
		{
			$len = strlen($this->import["end_string"]);
			$data_string = trim($this->data_string);
			$lendata = strlen($data_string);
			if(substr($this->data_string, $lendata - $len, $len) != $this->import["end_string"])
			{
				return false;
			}
			else
			{
				$this->data_string = substr($this->data_string, 0, $lendata - $len);
			}
		}
		$this->data = $this->diafan->getcsv($this->data_string, $this->import["delimiter"]);
		if(! $this->data)
		{
			return false;
		}

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
	 * Добавляет ошибку в лог
	 *
	 * @param string $type тип поля
	 * @param string $error ошибка
	 * @param string $name имя поля, на котором произошла ошибка
	 * @param boolean $lang текст ошибки нужно переводить
	 * @return void
	 */
	private function error_validate($type, $error, $name = false, $lang = true)
	{
		$name = $name === false ? $this->field($type, 'name') : $name;
		if($lang)
		{
			$error = $this->diafan->_($error);
		}
		$this->errors[] = $this->diafan->_('Ошибка в строке').' '.$this->data_string_number.': '.htmlentities($this->data_string).'<br><b>'.$name.', '.$type.': '.$error.'</b>';
	}

	/**
	 * Добавляет доступ к массиву this->fields
	 *
	 * @param string $type тип поля
	 * @return mixed
	 */
	private function get_fields($type)
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
	private function is_field($type)
	{
		return array_key_exists($type, $this->fields);
	}

	private function is_field_multiple($type)
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
	private function field_value($type, $value = false)
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
	private function field($type, $name)
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
	public function insert_row()
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
		if($this->is_field("name"))
		{
			$fields[] = "[name]";
			$mask[] = "'%s'";
			$values[] = $this->field_value("name");
		}
		if($this->is_field("keywords"))
		{
			$fields[] = "[keywords]";
			$mask[] = "'%s'";
			$values[] = $this->field_value("keywords");
		}
		if($this->is_field("descr"))
		{
			$fields[] = "[descr]";
			$mask[] = "'%s'";
			$values[] = $this->field_value("descr");
		}
		if($this->is_field("title_meta"))
		{
			$fields[] = "[title_meta]";
			$mask[] = "'%s'";
			$values[] = $this->field_value("title_meta");
		}
		if($this->is_field("anons"))
		{
			$fields[] = "[anons]";
			$mask[] = "'%s'";
			$values[] = $this->field_value("anons");
		}
		if($this->is_field("text"))
		{
			$fields[] = "[text]";
			$mask[] = "'%s'";
			$values[] = $this->field_value("text");
		}
		if($this->is_field("map_no_show"))
		{
			$fields[] = "map_no_show";
			$mask[] = "'%d'";
			$values[] = ($this->field_value("map_no_show") ? 1 : 0);
		}
		if($this->is_field("changefreq"))
		{
			$fields[] = "changefreq";
			$mask[] = "'%h'";
			$values[] = $this->field_value("changefreq");
		}
		if($this->is_field("priority"))
		{
			$fields[] = "priority";
			$mask[] = "%f";
			$values[] = $this->field_value("priority");
		}
		if($this->is_field("canonical"))
		{
			$fields[] = "[canonical]";
			$mask[] = "'%h'";
			$values[] = $this->field_value("canonical");
		}
		if($this->is_field("measure_unit"))
		{
			$fields[] = "[measure_unit]";
			$mask[] = "'%h'";
			$values[] = $this->field_value("measure_unit");
		}
		if($this->is_field("sort"))
		{
			$fields[] = "sort";
			$mask[] = "%d";
			$values[] = $this->field_value("sort");
		}
		else
		{
			if($this->import["type"] == 'good')
			{
				$fields[] = "sort";
				$mask[] = "%d";
				$values[] = $this->sort;
				$this->sort--;
			}
		}
		if($this->is_field("theme"))
		{
			$fields[] = "theme";
			$mask[] = "'%h'";
			$values[] = $this->field_value("theme");
		}
		if($this->is_field("view"))
		{
			$fields[] = "view";
			$mask[] = "'%h'";
			$values[] = $this->field_value("view");
		}
		if($this->is_field("admin_id"))
		{
			$fields[] = "admin_id";
			$mask[] = "%d";
			$values[] = $this->field_value("admin_id");
		}
		if($this->is_field("access"))
		{
			$fields[]= "access";
			$mask[] = "'%d'";
			$values[] = ($this->field_value("access") ? 1 : 0);
		}
		if($this->is_field("show_yandex"))
		{
			$fields[] = "show_yandex";
			$mask[] = "'%d'";
			$values[] = ($this->field_value("show_yandex") ? 1 : 0);
		}
		if($this->is_field("show_google"))
		{
			$fields[] = "show_google";
			$mask[] = "'%d'";
			$values[] = ($this->field_value("show_google") ? 1 : 0);
		}
		if($this->import["type"] == 'category')
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
			if($this->is_field("view_rows"))
			{
				$fields[] = "view_rows";
				$mask[] = "'%h'";
				$values[] = $this->field_value("view_rows");
			}
			if($this->is_field("view_element"))
			{
				$fields[] = "view_element";
				$mask[] = "'%h'";
				$values[] = $this->field_value("view_element");
			}
		}
		if($this->import["type"] == 'good')
		{
			if($this->is_field("id") && $this->field("id", "param_type") == 'article')
			{
				$fields[] = "article";
				$mask[] = "'%h'";
				$values[] = $this->field_value("id");
			}
			elseif($this->is_field("article"))
			{
				$fields[] = "article";
				$mask[] = "'%h'";
				$values[] = $this->field_value("article");
			}
			if($this->is_field("brand"))
			{
				$fields[] = "brand_id";
				$mask[] = "%d";
				switch($this->field("brand", "param_type"))
				{
					case 'site':
						$values[] = $this->field_value("brand");
						break;

					case 'name':
						if(! isset($this->cache["brands"]))
						{
							$this->cache["brands"] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_brand} WHERE trash='0' AND site_id=%d", $this->import["site_id"], "name", "id");
						}
						$values[] = (! empty($this->cache["brands"][$this->field_value("brand")]) ? $this->cache["brands"][$this->field_value("brand")] : '');
						break;

					default:
						if(! isset($this->cache["brands"]))
						{
							$this->cache["brands"] = DB::query_fetch_key_value("SELECT id, import_id FROM {shop_brand} WHERE trash='0' AND site_id=%d", $this->import["site_id"], "import_id", "id");
						}
						$values[] = (! empty($this->cache["brands"][$this->field_value("brand")]) ? $this->cache["brands"][$this->field_value("brand")] : '');
						break;
				}
			}
			if($this->is_field("date_start"))
			{
				$fields[] = "date_start";
				$mask[] = "%d";
				$values[] = $this->field_value("date_start");
			}
			if($this->is_field("date_finish"))
			{
				$fields[] = "date_finish";
				$mask[] = "%d";
				$values[] = $this->field_value("date_finish");
			}
			if($this->is_field("no_buy"))
			{
				$fields[] = "no_buy";
				$mask[] = "'%d'";
				$values[] = ($this->field_value("no_buy") ? 1 : 0);
			}
			if($this->is_field("hit"))
			{
				$fields[] = "hit";
				$mask[] = "'%d'";
				$values[] = ($this->field_value("hit") ? 1 : 0);
			}
			if($this->is_field("new"))
			{
				$fields[] = "new";
				$mask[] = "'%d'";
				$values[] = ($this->field_value("new") ? 1 : 0);
			}
			if($this->is_field("action"))
			{
				$fields[] = "action";
				$mask[] = "'%d'";
				$values[] = ($this->field_value("action") ? 1 : 0);
			}
			if($this->is_field("is_file"))
			{
				$fields[] = "is_file";
				$mask[] = "'%d'";
				$values[] = ($this->field_value("is_file") ? 1 : 0);
			}
			if($this->is_field("yandex"))
			{
				$fields[] = "yandex";
				$mask[] = "'%h'";
				$values[] = $this->field_value("yandex");
			}
			if($this->is_field("google"))
			{
				$fields[] = "google";
				$mask[] = "'%h'";
				$values[] = $this->field_value("google");
			}
			if($this->is_field("weight"))
			{
				$fields[] = "weight";
				$mask[] = "%f";
				$values[] = $this->field_value("weight");
			}
			if($this->is_field("length"))
			{
				$fields[] = "length";
				$mask[] = "%f";
				$values[] = $this->field_value("length");
			}
			if($this->is_field("width"))
			{
				$fields[] = "width";
				$mask[] = "%f";
				$values[] = $this->field_value("width");
			}
			if($this->is_field("height"))
			{
				$fields[] = "height";
				$mask[] = "%f";
				$values[] = $this->field_value("height");
			}
			if($this->is_field("cats"))
			{
				$fields[] = "cat_id";
				$mask[] = "%d";
				$values[] = $this->set_category();
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
	public function update_row()
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
		if($this->is_field("name"))
		{
			$query .= ", [name]='%s'";
			$values[] = $this->field_value("name");
		}
		if($this->is_field("keywords"))
		{
			$query .= ", [keywords]='%s'";
			$values[] = $this->field_value("keywords");
		}
		if($this->is_field("descr"))
		{
			$query .= ", [descr]='%s'";
			$values[] = $this->field_value("descr");
		}
		if($this->is_field("title_meta"))
		{
			$query .= ", [title_meta]='%s'";
			$values[] = $this->field_value("title_meta");
		}
		if($this->is_field("anons"))
		{
			$query .= ", [anons]='%s'";
			$values[] = $this->field_value("anons");
		}
		if($this->is_field("text"))
		{
			$query .= ", [text]='%s'";
			$values[] = $this->field_value("text");
		}
		if($this->is_field("map_no_show"))
		{
			$query .= ", map_no_show='%d'";
			$values[] = ($this->field_value("map_no_show") ? 1 : 0);
		}
		if($this->is_field("changefreq"))
		{
			$query .= ", changefreq='%h'";
			$values[] = $this->field_value("changefreq");
		}
		if($this->is_field("priority"))
		{
			$query .= ", priority=%f";
			$values[] = $this->field_value("priority");
		}
		if($this->is_field("canonical"))
		{
			$query .= ", [canonical]='%h'";
			$values[] = $this->field_value("canonical");
		}
		if($this->is_field("measure_unit"))
		{
			$query .= ", [measure_unit]='%h'";
			$values[] = $this->field_value("measure_unit");
		}
		if($this->is_field("sort"))
		{
			$query .= ", sort=%d";
			$values[] = $this->field_value("sort");
		}
		if($this->is_field("theme"))
		{
			$query .= ", theme='%h'";
			$values[] = $this->field_value("theme");
		}
		if($this->is_field("view"))
		{
			$query .= ", view='%h'";
			$values[] = $this->field_value("view");
		}
		if($this->is_field("admin_id"))
		{
			$query .= ", admin_id=%d";
			$values[] = $this->field_value("admin_id");
		}
		if($this->is_field("access"))
		{
			$query .= ", access='%d'";
			$values[] = ($this->field_value("access") ? 1 : 0);
		}
		if($this->is_field("show_yandex"))
		{
			$query .= ", show_yandex='%d'";
			$values[] = ($this->field_value("show_yandex") ? 1 : 0);
		}
		if($this->is_field("show_google"))
		{
			$query .= ", show_google='%d'";
			$values[] = ($this->field_value("show_google") ? 1 : 0);
		}
		if($this->import["type"] == 'category')
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
			if($this->is_field("view_rows"))
			{
				$query .= ", view_rows='%h'";
				$values[] = $this->field_value("view_rows");
			}
			if($this->is_field("view_element"))
			{
				$query .= ", view_element='%h'";
				$values[] = $this->field_value("view_element");
			}
		}
		if($this->import["type"] == 'good')
		{
			if($this->is_field("id") && $this->field("id", "param_type") == 'article')
			{
				$query .= ", article='%h'";
				$values[] = $this->field_value("id");
			}
			elseif($this->is_field("article"))
			{
				$query .= ", article='%h'";
				$values[] = $this->field_value("article");
			}
			if($this->is_field("brand"))
			{
				$query .= ", brand_id=%d";
				switch($this->field("brand", "param_type"))
				{
					case 'site':
						$values[] = $this->field_value("brand");
						break;

					case 'name':
						if(! isset($this->cache["brands"]))
						{
							$this->cache["brands"] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_brand} WHERE trash='0' AND site_id=%d", $this->import["site_id"], "name", "id");
						}
						$values[] = (! empty($this->cache["brands"][$this->field_value("brand")]) ? $this->cache["brands"][$this->field_value("brand")] : '');
						break;

					default:
						if(! isset($this->cache["brands"]))
						{
							$this->cache["brands"] = DB::query_fetch_key_value("SELECT id, import_id FROM {shop_brand} WHERE trash='0' AND site_id=%d", $this->import["site_id"], "import_id", "id");
						}
						$values[] = (! empty($this->cache["brands"][$this->field_value("brand")]) ? $this->cache["brands"][$this->field_value("brand")] : '');
						break;
				}
			}
			if($this->is_field("date_start"))
			{
				$query .= ", date_start=%d";
				$values[] = $this->field_value("date_start");
			}
			if($this->is_field("date_finish"))
			{
				$query .= ", date_finish=%d";
				$values[] = $this->field_value("date_finish");
			}
			if($this->is_field("no_buy"))
			{
				$query .= ", no_buy='%d'";
				$values[] = ($this->field_value("no_buy") ? 1 : 0);
				if(empty($this->oldrow["no_buy"]) && ! $this->field_value("no_buy"))
				{
					$this->send_mail_waitlist();
				}
			}
			if($this->is_field("hit"))
			{
				$query .= ", hit='%d'";
				$values[] = ($this->field_value("hit") ? 1 : 0);
			}
			if($this->is_field("new"))
			{
				$query .= ", new='%d'";
				$values[] = ($this->field_value("new") ? 1 : 0);
			}
			if($this->is_field("action"))
			{
				$query .= ", action='%d'";
				$values[] = ($this->field_value("action") ? 1 : 0);
			}
			if($this->is_field("is_file"))
			{
				$query .= ", is_file='%d'";
				$values[] = ($this->field_value("is_file") ? 1 : 0);
			}
			if($this->is_field("yandex"))
			{
				$query .= ", yandex='%h'";
				$values[] = $this->field_value("yandex");
			}
			if($this->is_field("google"))
			{
				$query .= ", google='%h'";
				$values[] = $this->field_value("google");
			}
			if($this->is_field("weight"))
			{
				$query .= ", weight=%f";
				$values[] = $this->field_value("weight");
			}
			if($this->is_field("length"))
			{
				$query .= ", length=%f";
				$values[] = $this->field_value("length");
			}
			if($this->is_field("width"))
			{
				$query .= ", width=%f";
				$values[] = $this->field_value("width");
			}
			if($this->is_field("height"))
			{
				$query .= ", height=%f";
				$values[] = $this->field_value("height");
			}
			if($this->is_field("cats"))
			{
				$query .= ", cat_id=%d";
				$values[] = $this->set_category();
			}
		}
		$query .= " WHERE id=%d";
		$values[] = $this->id;
		DB::query($query, $values);
	}

	/**
	 * Обработка поля "Доступ"
	 *
	 * @return void
	 */
	private function set_access()
	{
		if(! $this->is_field("access"))
			return;

		DB::query("DELETE FROM {access} WHERE element_id=%d AND module_name='shop' AND element_type='%s'", $this->id, $this->import["element_type"]);
		$value = $this->field_value("access");
		if(! $value)
			return;
		foreach ($value as $role_id)
		{
			DB::query("INSERT INTO {access} (module_name, element_id, element_type, role_id) VALUES ('shop', %d, '%s', %d)", $this->id, $this->import["element_type"], $role_id);
		}
	}

	/**
	 * Обработка поля "Псевдоссылка"
	 *
	 * @return void
	 */
	private function set_rewrite()
	{
		if(! $this->is_field("rewrite") && $this->update)
			return;

		$value = $this->field_value("rewrite");

		// ЧПУ
		if($this->field_value("rewrite") || ROUTE_AUTO_MODULE)
		{
			$parent_id = 0;
			if($this->import["type"] == 'category' && $this->is_field("parent") && $this->field("parent", "param_type") == 'site')
			{
				$parent_id = $this->field_value("parent");
			}
			$this->diafan->_route->save($value, $this->field_value("name"), $this->id, 'shop', $this->import["element_type"], $this->import["site_id"], (! empty($this->cache["current_cat"]) ? $this->cache["current_cat"] : 0), $parent_id);
		}
	}

	/**
	 * Обработка поля "Редирект"
	 *
	 * @return void
	 */
	private function set_redirect()
	{
		if(! $this->is_field("redirect"))
			return;

		$redirect = $this->field_value("redirect");
		if($this->field('redirect', 'param_second_delimitor'))
		{
			$r = explode($this->field('redirect', 'param_second_delimitor'), $redirect);
			$redirect = $r[0];
			if(! empty($r[1]))
			{
				$code = $r[1];
			}
		}
		if(empty($code))
		{
			$code = 301;
		}

		if(! $this->field_value("redirect") && $this->update)
		{
			DB::query("DELETE FROM {redirect} WHERE module_name='shop' AND element_id=%d AND element_type='%s'", $this->id, $this->import["element_type"]);
		}
		if($this->field_value("redirect"))
		{
			if($this->update && $id = DB::query_result("SELECT id FROM {redirect} WHERE module_name='shop' AND element_id=%d AND element_type='%s'", $this->id, $this->import["element_type"]))
			{
				DB::query("UPDATE {redirect} SET redirect='%s', code=%d WHERE id=%d", $redirect, $code, $id);
			}
			else
			{
				DB::query("INSERT INTO {redirect} (redirect, code, module_name, element_id, element_type)"
					." VALUES ('%s', %d, 'shop', %d, '%s')",
					$redirect, $code, $this->id, $this->import["element_type"]);
			}

		}
	}

	/**
	 * Обработка поля "Ссылка на карте сайта"
	 *
	 * @return void
	 */
	private function set_map()
	{
		// ссылка на карте сайта
		if(! in_array("map", $this->diafan->installed_modules))
			return;

		// проверяется заполнение поля "Не показывать на карте сайта"
		$hide_map = false;
		if($this->is_field("map_no_show"))
		{
			if($this->field_value("map_no_show"))
			{
				$hide_map = true;
			}
		}
		elseif($this->update)
		{
			if(! empty($this->oldrow["map_no_show"]))
			{
				$hide_map = true;
			}
		}
		if(! $hide_map)
		{
			$shop_row = array(
				"module_name" => 'shop',
				"id"          => $this->id,
				"site_id"     => $this->import["site_id"],
			);
			if($this->import["type"] == 'good')
			{
				$shop_row["cat_id"] = $this->cache["current_cat"];
			}
			$shop_row["element_type"] = $this->import["element_type"];

			$hide_map = true;
			// вычисляется на каких языковых зеркалах товар/категория активны
			foreach($this->diafan->_languages->all as $l)
			{
				$shop_row["act".$l["id"]] = false;
				if($l["id"] == _LANG && $this->is_field("act"))
				{
					if($this->field_value("act"))
					{
						$shop_row["act".$l["id"]] = true;
						$hide_map = false;
					}
				}
				elseif(! empty($this->oldrow["act".$l["id"]]))
				{
					$shop_row["act".$l["id"]] = true;
					$hide_map = false;
				}
			}
			if($this->is_field("date_start") && $this->field_value("date_start"))
			{
				$shop_row["date_start"] = $this->field_value("date_start");
			}
			if($this->is_field("date_finish") && $this->field_value("date_finish"))
			{
				$shop_row["date_finish"] = $this->field_value("date_finish");
			}
			if(! $hide_map)
			{
				$this->diafan->_map->index_element($shop_row);
			}
		}
		if($hide_map && $this->update)
		{
			$this->diafan->_map->delete($this->id, 'shop', $this->import["element_type"]);
		}
	}

	/**
	 * Обработка поля "Категории"
	 *
	 * @return integer
	 */
	private function set_category()
	{
		if($this->import["type"] != 'good')
		{
			return 0;
		}
		if($this->field_value("cats"))
		{
			if(! isset($this->cache["cats"]))
			{
				switch($this->field("cats", "param_type"))
				{
					case 'name':
						$this->cache["cats"] =
							DB::query_fetch_key_value("SELECT id, [name] FROM {shop_category} WHERE trash='0' AND site_id=%d", $this->import["site_id"], "name", "id");
						break;

					case 'site':
						$this->cache["cats"] =
							DB::query_fetch_key_value("SELECT id FROM {shop_category} WHERE trash='0' AND site_id=%d", $this->import["site_id"], "id", "id");
						break;

					default:
						$this->cache["cats"] =
							DB::query_fetch_key_value("SELECT id, import_id FROM {shop_category} WHERE trash='0' AND site_id=%d", $this->import["site_id"], "import_id", "id");
						break;
				}
			}
			foreach ($this->field_value("cats") as $cat)
			{
				if(empty($this->cache["cats"][$cat]))
				{
					$this->error_validate('cats', $this->diafan->_('категория %s не найдена', $cat), false, false);
					continue;
				}
				return $this->cache["cats"][$cat];
			}
		}
		else
		{
			if(empty($this->oldrow["cat_id"]) && $this->import["cat_id"])
			{
				return $this->import["cat_id"];
			}
		}
	}

	/**
	 * Обработка поля "Дополнительные категории"
	 *
	 * @return void
	 */
	private function set_category_rel()
	{
		switch($this->import["type"])
		{
			case 'good':
				$table_cats_rel = 'shop_category_rel';
				break;

			case 'brand':
				$table_cats_rel = 'shop_brand_category_rel';
				break;

			default:
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
							DB::query_fetch_key_value("SELECT id, [name] FROM {shop_category} WHERE trash='0' AND site_id=%d", $this->import["site_id"], "name", "id");
						break;

					case 'site':
						$this->cache["cats"] =
							DB::query_fetch_key_value("SELECT id FROM {shop_category} WHERE trash='0' AND site_id=%d", $this->import["site_id"], "id", "id");
						break;

					default:
						$this->cache["cats"] =
							DB::query_fetch_key_value("SELECT id, import_id FROM {shop_category} WHERE trash='0' AND site_id=%d", $this->import["site_id"], "import_id", "id");
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
				foreach ($this->field_value("cats") as $cat)
				{
					if(empty($this->cache["cats"][$cat]))
					{
						$this->error_validate('cats', $this->diafan->_('категория %s не найдена', $cat), false, false);
						continue;
					}
					if(empty($this->cache["current_cat"]))
					{
						$this->cache["current_cat"] = $this->cache["cats"][$cat];
					}
					DB::query("INSERT INTO {".$table_cats_rel."} (element_id, cat_id) VALUES (%d, %d)", $this->id, $this->cache["cats"][$cat]);
					$this->cache["current_cats"][] = $this->cache["cats"][$cat];
				}
			}
		}
		if(! $this->cache["current_cats"] && $this->import["type"] == 'brand')
		{
			DB::query("INSERT INTO {".$table_cats_rel."} (element_id) VALUES (%d)", $this->id);
		}
	}

	/**
	 * Обработка полей "Цена" и "Количество"
	 *
	 * @return void
	 */
	private function set_price_count()
	{
		if (! $this->is_field("price") && ! $this->is_field("count"))
			return;

		if($this->is_field("count"))
		{
			$count_value = $this->set_count();
		}
		if ($this->is_field("price"))
		{
			$price_ids = array();
			$prices = array();
			if($this->is_field("id"))
			{
				$prices = $this->diafan->_shop->price_get_base($this->id);
			}
			$price_value = $this->set_price();
			foreach ($price_value as $row)
			{
				if(empty($row["count"]))
				{
					$row["count"] = 0;
					if(! empty($count_value))
					{
						foreach ($count_value as $c)
						{
							if($c["params"] == $row["params"])
							{
								$row["count"] = $c["count"];
							}
						}
					}
				}
				$update = false;
				foreach($prices as $price)
				{
					if($price["param"] == $row["params"] && $row["currency"] == $price["currency_id"])
					{
						$update = true;
						$price_ids[] = $price["price_id"];
						if($row["old_price"] != $price["old_price"] || $row["price"] != $price["price"] || $row["count"] != $price["count_goods"])
						{
							DB::query("UPDATE {shop_price} SET price=%f, old_price=%f, count_goods=%f WHERE id=%d", $row["price"], $row["old_price"], $row["count"], $price["id"]);
						}
						if($row["image_id"] !== false)
						{
							$image_rel = DB::query_fetch_array("SELECT * FROM {shop_price_image_rel} WHERE price_id=%d", $price["id"]);
							if($image_rel)
							{
								if(! $row["image_id"])
								{
									DB::query("DELETE FROM {shop_price_image_rel} WHERE id=%d", $image_rel["id"]);
								}
								elseif($image_rel["image_id"] != $row["image_id"])
								{
									DB::query("UPDATE {shop_price_image_rel} SET image_id=%d WHERE id=%d", $row["image_id"], $image_rel["id"]);
								}
							}
							elseif($row["image_id"])
							{
								DB::query("INSERT INTO {shop_price_image_rel} (price_id, image_id) VALUES (%d, %d)", $price["id"], $row["image_id"]);
							}
						}
					}
				}
				if(! $update)
				{
					$price_ids[] = $this->diafan->_shop->price_insert($this->id, $row["price"], $row["old_price"], $row["count"], $row["params"], $row["currency"], '', $row["image_id"]);
				}
				if($row["count"])
				{
					$this->send_mail_waitlist($row["params"]);
				}
			}
			if($this->is_field("id") && $prices)
			{
				$del_price_ids = array();
				foreach($prices as $price)
				{
					if(! in_array($price["price_id"], $price_ids))
					{
						$del_price_ids[] = $price["price_id"];
					}
				}
				if($del_price_ids)
				{
					DB::query("DELETE FROM {shop_price_param} WHERE price_id IN (%s)", implode(",", $del_price_ids));
					DB::query("DELETE FROM {shop_price_image_rel} WHERE price_id IN (%s)", implode(",", $del_price_ids));
					DB::query("DELETE FROM {shop_price} WHERE price_id IN (%s)", implode(",", $del_price_ids));
				}
			}
		}
		else
		{
			foreach ($count_value as $row)
			{
				$price = $this->diafan->_shop->price_get($this->id, $row["params"], false);
				if(! empty($price["price_id"]))
				{
					DB::query("UPDATE {shop_price} SET count_goods=%f WHERE price_id=%d", $row["count"], $price["price_id"]);
				}
				else
				{
					$this->diafan->_shop->price_insert($this->id, 0, 0, $row["count"], $row["params"], 0);
				}
				if($row["count"] && empty($price["count_goods"]))
				{
					$this->send_mail_waitlist($row["params"]);
				}
			}
		}
		$this->diafan->_shop->price_calc($this->id);
	}

	/**
	 * Отправляет уведомления о поступлении товара
	 *
	 * @param array $params дополнительные характеристики, влияющие на цену
	 * @return void
	 */
	private function send_mail_waitlist($params = array())
	{
		if(empty($this->oldrow))
		{
			return;
		}
		$row = $this->oldrow;
		if($this->is_field("no_buy"))
		{
			$row["no_buy"] = ($this->field_value("no_buy") ? 1 : 0);
		}
		if($this->is_field("name"))
		{
			$row["name"._LANG] = $this->field_value("name");
		}
		$this->diafan->_shop->price_send_mail_waitlist($this->id, $params, $row);
	}

	/**
	 * Подготавливает значение поля "Цена"
	 *
	 * @return array
	 */
	private function set_price()
	{
		if(! $this->field_value("price"))
			return array();

		$new_values = array();
		if(! isset($this->cache["multiple_params"]))
		{
			$this->cache["multiple_params"] = array();
			$rows = DB::query_fetch_all("SELECT id, [name] FROM {shop_param} WHERE type='multiple' AND required='1' AND trash='0'");
			foreach ($rows as $row)
			{
				$rows_v = DB::query_fetch_all("SELECT id, [name] FROM {shop_param_select} WHERE param_id=%d", $row["id"]);
				foreach ($rows_v as $row_v)
				{
					$row["values"][$row_v["id"]] = $row_v["name"];
				}
				$rows_cat = DB::query_fetch_all("SELECT cat_id FROM {shop_param_category_rel} WHERE element_id=%d", $row["id"]);
				foreach ($rows_cat as $row_cat)
				{
					$row["cats"][] = $row_cat["cat_id"];
				}
				$this->cache["multiple_params"][$row["id"]] = $row;
			}
		}

		$param_delimitor = $this->field('price', 'param_delimitor') ? $this->field('price', 'param_delimitor') : '&';
		$param_select_type = $this->field('price', 'param_select_type');
		foreach ($this->field_value("price") as $v)
		{
			$new_v = array();
			$v = explode($param_delimitor, $v);
			if($error = Validate::floattext($v[0]))
			{
				$this->error_validate('price', $error);
				continue;
			}
			$i = 1;
			$new_v["count"] = 0;
			if($this->field('price', 'param_count'))
			{
				$new_v["count"] = $v[$i];
				if(preg_match('/[^0-9\.\,]+/', $new_v["count"]))
				{
					$this->error_validate('price', 'количество должно быть числом');
					$new_v["count"] = preg_replace('/[^0-9\.\,]+/', '', $new_v["count"]);
				}
				$new_v["count"] = (float)$new_v["count"];
				unset($v[$i]);
				$i++;
			}
			$new_v["old_price"] = 0;
			if($this->field('price', 'param_old_price'))
			{
				$new_v["old_price"] = str_replace(',', '.', $v[$i]);
				unset($v[$i]);
				$i++;
			}
			$new_v["currency"] = 0;
			if($this->field('price', 'param_currency'))
			{
				$currency = $v[$i];
				if($currency)
				{
					if(! isset($this->cache["currency"]))
					{
						$this->cache["currency"] = array();
						$rows = DB::query_fetch_all("SELECT id, name FROM {shop_currency} WHERE trash='0'");
						foreach ($rows as $row)
						{
							if($this->field('price', 'param_select_currency') == 'value')
							{
								$this->cache["currency"][$row["name"]] = $row["id"];
							}
							else
							{
								$this->cache["currency"][$row["id"]] = $row["id"];
							}
						}
					}
					if(empty($this->cache["currency"][$currency]))
					{
						$this->error_validate('price', 'некорректное значение валюты');
						continue;
					}
					else
					{
						$new_v["currency"] = $this->cache["currency"][$currency];
					}
				}
				unset($v[$i]);
			}
			$new_v["image_id"] = false;
			if($this->field('price', 'param_image'))
			{
				$new_v["image_id"] = null;
				$image = $v[$i];
				if($image)
				{
					if($this->field_value("images") && ! empty($this->cache["images"][$image]))
					{
						$new_v["image_id"] = $this->cache["images"][$image];
					}
					else
					{
						$new_v["image_id"] = DB::query_result("SELECT * FROM {images} WHERE element_id=%d AND module_name='shop' AND element_type='%s' AND name='%s'", $this->id, $this->import["element_type"], $image);
						if(! $new_v["image_id"])
						{
							$new_v["image_id"] = null;
							$this->error_validate('price', 'некорректное значение прикрепленного к цене изображения');
							continue;
						}
					}
				}
				unset($v[$i]);
			}
			$new_v["price"] = str_replace(',', '.', $v[0]);
			unset($v[0]);
			$new_params = array();
			foreach ($v as $i => $p)
			{
				if(! $p)
					continue;

				list($param_id, $param_value) = explode('=', $p);
				if(empty($param_id))
				{
					$this->error_validate('price', 'некорректное значение параметра, влияющего на цену');
					continue;
				}
				if(empty($param_value))
				{
					$param_value = 0;
				}

				$new_params[$param_id] = $param_value;
			}
			$multiple_params = array();
			foreach ($this->cache["multiple_params"] as $id => $param)
			{
				$in_cats = true;
				if(! in_array(0, $param["cats"]) && $this->cache["current_cats"])
				{
					$in_cats = false;
					foreach ($this->cache["current_cats"] as $cat)
					{
						if(in_array($cat, $param["cats"]))
						{
							$in_cats = true;
							break;
						}
					}
				}
				if($in_cats)
				{
					$multiple_params[] = $id;
				}
				if($param_select_type == 'value')
				{
					$id = $param["name"];
				}
				if($in_cats && ! in_array($id, array_keys($new_params)))
				{
					$new_params[$id] = 0;
				}
			}
			foreach ($new_params as $id => $value)
			{
				$new_id = 0;
				foreach ($this->cache["multiple_params"] as $param)
				{
					if($param_select_type == 'value')
					{
						$param_id = $param["name"];
					}
					else
					{
						$param_id = $param["id"];
					}
					if($param_id == $id)
					{
						$new_id = $param["id"];
						if($value)
						{
							$new_value = 0;
							foreach ($param["values"] as $v_k => $v_v)
							{
								if($param_select_type == 'value')
								{
									$param_value = $v_v;
								}
								else
								{
									$param_value = $v_k;
								}
								if($param_value == $value)
								{
									$new_value = $v_k;
									break;
								}
							}
							if(! $new_value)
							{
								$this->error_validate('price', 'не верно задано значение параметра, влияющего на цену');
								continue;
							}
							$value = $new_value;
						}
						break;
					}
				}
				if(! $new_id)
				{
					$this->error_validate('price', 'не верно задан параметр, влияющий на цену');
					continue;
				}
				$id = $new_id;
				if(! in_array($id, $multiple_params))
				{
					if($value)
					{
						$this->error_validate('price', 'параметр, влияющий на цену не может быть применен к товару');
					}
					continue;
				}
				$new_params[$id] = $value;
			}
			$new_v["params"] = $new_params;
			$new_values[] = $new_v;
		}
		return $new_values;
	}

	/**
	 * Подготавливает значение поля "Количество"
	 *
	 * @return array
	 */
	private function set_count()
	{
		$new_values = array();
		if(! isset($this->cache["multiple_params"]))
		{
			$this->cache["multiple_params"] = array();
			$rows = DB::query_fetch_all("SELECT id, [name] FROM {shop_param} WHERE type='multiple' AND required='1' AND trash='0'");
			foreach ($rows as $row)
			{
				$rows_v = DB::query_fetch_all("SELECT id, [name] FROM {shop_param_select} WHERE param_id=%d", $row["id"]);
				foreach ($rows_v as $row_v)
				{
					$row["values"][$row_v["id"]] = $row_v["name"];
				}
				$rows_cat = DB::query_fetch_all("SELECT cat_id FROM {shop_param_category_rel} WHERE element_id=%d", $row["id"]);
				foreach ($rows_cat as $row_cat)
				{
					$row["cats"][] = $row_cat["cat_id"];
				}
				$this->cache["multiple_params"][$row["id"]] = $row;
			}
		}

		$param_delimitor = $this->field('count', 'param_delimitor') ? $this->field('count', 'param_delimitor') : '&';
		$param_select_type = $this->field('count', 'param_select_type');
		$count_value = $this->field_value("count");
		if(! is_array($count_value))
		{
			$count_value = array($count_value);
		}
		foreach ($count_value as $v)
		{
			$new_v = array();
			$v = explode($param_delimitor, $v);
			if($error = Validate::floattext($v[0]))
			{
				$this->error_validate('count', $error);
				continue;
			}
			$new_v["count"] = (float)$v[0];
			unset($v[0]);
			$new_params = array();
			foreach ($v as $i => $p)
			{
				if(! $p)
					continue;

				list($param_id, $param_value) = explode('=', $p);
				if(empty($param_id))
				{
					$this->error_validate('count', 'некорректное значение параметра, влияющего на цену');
					continue;
				}
				if(empty($param_value))
				{
					$param_value = 0;
				}

				$new_params[$param_id] = $param_value;
			}
			$multiple_params = array();
			foreach ($this->cache["multiple_params"] as $id => $param)
			{
				$in_cats = true;
				if(! in_array(0, $param["cats"]) && $this->cache["current_cats"])
				{
					$in_cats = false;
					foreach ($this->cache["current_cats"] as $cat)
					{
						if(in_array($cat, $param["cats"]))
						{
							$in_cats = true;
							break;
						}
					}
				}
				if($in_cats)
				{
					$multiple_params[] = $id;
				}
				if($param_select_type == 'value')
				{
					$id = $param["name"];
				}
				if($in_cats && ! in_array($id, array_keys($new_params)))
				{
					$new_params[$id] = 0;
				}
			}
			foreach ($new_params as $id => $value)
			{
				$new_id = 0;
				foreach ($this->cache["multiple_params"] as $param)
				{
					if($param_select_type == 'value')
					{
						$param_id = $param["name"];
					}
					else
					{
						$param_id = $param["id"];
					}
					if($param_id == $id)
					{
						$new_id = $param["id"];
						if($value)
						{
							$new_value = 0;
							foreach ($param["values"] as $v_k => $v_v)
							{
								if($param_select_type == 'value')
								{
									$param_value = $v_v;
								}
								else
								{
									$param_value = $v_k;
								}
								if($param_value == $value)
								{
									$new_value = $v_k;
									break;
								}
							}
							if(! $new_value)
							{
								$this->error_validate('count', 'не верно задано значение параметра, влияющего на цену');
								continue;
							}
							$value = $new_value;
						}
						break;
					}
				}
				if(! $new_id)
				{
					$this->error_validate('count', 'не верно задан параметр, влияющий на цену');
					continue;
				}
				$id = $new_id;
				if(! in_array($id, $multiple_params))
				{
					$this->error_validate('count', 'параметр, влияющий на цену не может быть применен к товару');
					continue;
				}
				$new_params[$id] = $value;
			}
			$new_v["params"] = $new_params;
			$new_values[] = $new_v;
		}
		return $new_values;
	}

	/**
	 * Обработка поля "Связанные товары"
	 *
	 * @return void
	 */
	private function set_rels()
	{
		if ($this->import["type"] != 'good')
			return;

		if(! $this->is_field("id") || ! $this->is_field("rel_goods"))
			return;

		DB::query("DELETE FROM {shop_rel} WHERE element_id=%d", $this->id);

		if (! $this->field_value("rel_goods"))
			return;

		if($this->field("rel_goods", "param_type") == 'site')
		{
			foreach ($this->field_value("rel_goods") as $relation)
			{
				DB::query("INSERT INTO {shop_rel} (element_id, rel_element_id) VALUES (%d, %d)", $this->id, $relation);
			}
			return;
		}

		foreach ($this->field_value("rel_goods") as $relation)
		{
			DB::query("INSERT INTO {shop_rel} (element_id, rel_element_id_temp) VALUES (%d, '%s')", $this->id, $relation);
		}
	}

	/**
	 * Обработка поля "Меню"
	 *
	 * @return void
	 */
	private function set_menu()
	{
		foreach ($this->menus as $k => $param)
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

			if ($this->is_field("id"))
			{
				$this->diafan->_menu->delete($this->id, "shop", $this->import["element_type"], $param["id"]);
			}

			if ($value)
			{
				DB::query(
					"INSERT INTO {menu} ([name], module_name, element_id, element_type,"
					." cat_id, sort, [act]) VALUES ('%s', 'shop', %d, '%s', %d, %d, '%d')",
					$this->field_value('name'),
					$this->id,
					$this->import["element_type"],
					$param["id"],
					$this->field_value('sort') ? $this->field_value('sort') : $this->id,
					$this->field_value('act') ? 1 : 0
				);
			}
		}
	}

	/**
	 * Прикрепление характеристик к товару
	 *
	 * @return void
	 */
	private function set_params()
	{
		foreach ($this->params as $k => $param)
		{
			if ( ! $param["id"])
				continue;

			if ($this->is_field("id"))
			{
				DB::query("DELETE FROM {shop_param_element} WHERE param_id=%d AND element_id=%d", $param["id"], $this->id);
			}

			$value = isset($this->data[$k]) ? $this->data[$k] : '';
			if(empty($value))
			{
				if($param['required'])
				{
					$this->error_validate('param', 'значение не задано', $param["name"]);
				}
				continue;
			}

			switch ($param["type"])
			{
				case 'multiple':
					$new_value =  array();
					$d = explode($this->import["sub_delimiter"], $value);
					foreach ($d as $v)
					{
						$v = trim($v);
						if($v)
						{
							if(isset($param["values"][$v]) && $param["select_type"] == 'value')
							{
								$new_value[] = $param["values"][$v];
							}
							elseif(in_array($v, $param["values"]) && $param["select_type"] == 'key')
							{
								$new_value[] = $v;
							}
							else
							{
								$this->error_validate('param', $this->diafan->_('"%s" нет в списке значений', $v), $k, false);
							}
						}
					}
					$value = $new_value;
					break;

				case 'select':
					if($value)
					{
						if(isset($param["values"][$value]) && $param["select_type"] == 'value')
						{
							$value = $param["values"][$value];
						}
						elseif(! in_array($value, $param["values"]) && $param["select_type"] == 'key')
						{
							$this->error_validate('param', $this->diafan->_('"%s" нет в списке значений', $value), $param["name"], false);
							$value = '';
						}
					}
					break;

				case 'date':
					if($error = Validate::date($value))
					{
						$this->error_validate('param', $error, $param["name"]);
						$value = '';
					}
					else
					{
						$value = $this->diafan->formate_in_date($value);
					}
					break;

				case 'datetime':
					if($error = Validate::datetime($value))
					{
						$this->error_validate('param', $error, $param["name"]);
						$value = '';
					}
					else
					{
						$value = $this->diafan->formate_in_datetime($value);
					}
					break;

				case 'numtext':
					if(preg_match('/[^0-9,\.]+/', $value))
					{
						$this->error_validate('param', 'значение должно быть числом', $param["name"]);
						$value = preg_replace('/[^0-9,\.]+/', '', $value);
					}
					$value = str_replace(',', '.', $value);
					break;

				case 'title':
					$value = '';
					break;

				case 'checkbox':
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
						$this->error_validate('param', 'допустимы только следующие значения 1, 0, true, false', $param["name"]);
						$value = 0;
					}
					break;

				case 'attachments':
					if(empty($param["directory"]))
					{
						$this->error_validate('param', $this->diafan->_('Невозможно загрузить файлы %s, так как в настройках не указана папка, где они храняться.', $value), $param["name"], false);
						return;
					}

					$this->diafan->_attachments->delete($this->id, 'shop', 0, $param["id"]);

					$new_value =  array();
					$d = explode($this->import["sub_delimiter"], $value);
					foreach ($d as $v)
					{
						$v = trim($v);
						if($v)
						{
							$new_value[] = $v;
						}
					}
					$value = $new_value;

					if($as = $this->get_attachments_data($value, $param["directory"]))
					{
						foreach ($as as $a)
						{
							try
							{
								$this->diafan->_attachments->upload($a, 'shop', $this->id, false, $param["config"]);
							}
							catch(Exception $e)
							{
								File::delete_file($a['tmp_name']);
								$this->error_validate('param', $a['address'].': '.$e->getMessage(), $param["name"], false);
							}
						}
					}
					$value = '';
					break;

				case 'images':
					if(empty($param["directory"]))
					{
						$this->error_validate('param', $this->diafan->_('Невозможно загрузить изображения %s, так как в настройках не указана папка, где они храняться.', $value), $param["name"], false);
						return;
					}

					$this->diafan->_images->delete($this->id, 'shop', $this->import["element_type"], $param["id"]);

					$new_value =  array();
					$d = explode($this->import["sub_delimiter"], $value);
					foreach ($d as $v)
					{
						$v = trim($v);
						if($v)
						{
							$new_value[] = $v;
						}
					}
					$value = $new_value;

					if($images = $this->get_images_data($value, $param["directory"], $param["name"]))
					{
						foreach ($images as $image)
						{
							try
							{
								$this->diafan->_images->upload($this->id, 'shop', $this->import["element_type"], $this->import['site_id'], $image['address'], $image['name'], false, $param["id"]);
							}
							catch(Exception $e)
							{
								//$this->error_validate('param', $image['address'].': '.$e->getMessage(), $param["name"], false);
							}
						}
					}
					$value = '';
					break;
			}

			if(empty($value))
			{
				if($param['required'])
				{
					$this->error_validate('param', 'значение не задано', $param["name"]);
				}
				continue;
			}
			$value_name = in_array($param["type"], array('text', 'textarea', 'editor')) ? "[value]" : "value".$this->diafan->_languages->site;
			if (is_array($value))
			{
				foreach ($value as $v)
				{
					DB::query("INSERT INTO {shop_param_element} (".$value_name.", param_id, element_id) VALUES ('%s', %d, %d)", $v, $param["id"], $this->id);
				}
			}
			else
			{
				DB::query("INSERT INTO {shop_param_element} (".$value_name.", param_id, element_id) VALUES ('%s', %d, %d)", $value, $param["id"], $this->id);
			}
		}
	}

	/**
	 * Загружает все изображения товара
	 *
	 * @return void
	 */
	public function set_images()
	{
		$this->cache["images"] = array();

		if ( ! $this->is_field("images"))
			return;

		$this->diafan->_images->delete($this->id, 'shop', $this->import["element_type"], 0);

		if($this->is_field_multiple("images"))
		{
			foreach(array_keys($this->fields["images"]) as $i)
			{
				$this->fields_iterator['images'] = $i;
				$this->upload_images();
			}
		}
		else
		{
			$this->upload_images();
		}
	}

	private function upload_images()
	{
		if (! $this->field_value("images"))
			return;

		if(! $images = $this->get_images_data($this->field_value("images"), $this->field("images", 'param_directory')))
			return;

		foreach ($images as $image)
		{
			try
			{
				$this->diafan->_images->upload($this->id, 'shop', $this->import["element_type"], $this->import['site_id'], $image['address'], $image['name']);
				$this->cache["images"][$image['value']] = $GLOBALS["image_id"];
			}
			catch(Exception $e)
			{
				//$this->error_validate('images', $image['address'].': '.$e->getMessage(), false, false);
			}
			if(! empty($image["alt"]) || ! empty($image["title"]))
			{
				DB::query("UPDATE {images} SET [alt]='%h', [title]='%h' WHERE id=%d", $image["alt"], $image["title"], $GLOBALS["image_id"]);
			}
		}
	}

	/**
	 * Получение данных об изображениях, доступных для загрузки
	 *
	 * @return array
	 */
	public function get_images_data($value, $dir, $name = false)
	{
		$directory = trim(preg_replace('/(^\/)|(\/$)/', '', $dir)).'/';
		$images = array();
		foreach ($value as $image_address)
		{
			$temp = array('alt' => '', 'title' => '');
			if($name === false && $this->field('images', 'param_second_delimitor'))
			{
				$r = explode($this->field('images', 'param_second_delimitor'), $image_address);
				$image_address = $r[0];
				if(! empty($r[1]))
				{
					$temp["alt"] = $r[1];
				}
				if(! empty($r[2]))
				{
					$temp["title"] = $r[2];
				}
			}
			if(preg_match('/^https?:\/\//', $image_address))
			{
				$temp['address'] = $image_address;
			}
			elseif(preg_match('/^https?:\/\//', $directory))
			{
				$temp['address'] = $directory.$image_address;
			}
			else
			{
				if ( ! file_exists(ABSOLUTE_PATH.$directory.$image_address))
				{
					if($name !== false)
					{
						$this->error_validate('param', $this->diafan->_('Файл %s не найден', ABSOLUTE_PATH.$directory.$image_address), $name);
					}
					else
					{
						$this->error_validate('images', $this->diafan->_('Файл %s не найден', ABSOLUTE_PATH.$directory.$image_address), false, false);
					}
					continue;
				}
				$temp['address'] = ABSOLUTE_PATH.$directory.$image_address;
			}
			$temp['name'] = $this->field_value("name") ? preg_replace('/[^A-Za-z0-9-_]+/', '', strtolower($this->diafan->translit(substr($this->field_value("name"), 0, 50)))) : $this->id;
			$temp["value"] = $image_address;

			$images[] = $temp;
		}

		return $images;
	}

	/**
	 * Получение данных об файлах, доступных для загрузки
	 *
	 * @return array
	 */
	public function get_attachments_data($value, $dir)
	{
		$directory = trim(preg_replace('/(^\/)|(\.)|(\/$)/', '', $dir)).'/';
		$as = array();
		foreach ($value as $i => $address)
		{
			$tmp_name = 'tmp/shopimportattachs'.$i;
			if(preg_match('/^https?:\/\//', $address))
			{
				File::copy_file($address, $tmp_name);
			}
			else
			{
				if (! file_exists(ABSOLUTE_PATH.$directory.$address))
				{
					$this->error_validate('param', $this->diafan->_('Файл %s не найден', ABSOLUTE_PATH.$directory.$address));
					continue;
				}
				File::copy_file(ABSOLUTE_PATH.$directory.$address, $tmp_name);
			}
			$ar = explode('/', $address);
			$a["name"] = array_pop($ar);
			$a["type"] = '';
			$a['address'] = $address;
			$a['tmp_name'] = $tmp_name;
			$as[] = $a;
		}

		return $as;
	}


	/**
	 * Обновление сортировки
	 *
	 * @return void
	 */
	public function finish_update_sort()
	{
		DB::query("UPDATE {".$this->import["table"]."} SET sort=id WHERE sort=0");
	}

	/**
	 * Удаление старых записей в БД, если в импорте участвуют идентификаторы элементов
	 *
	 * @return void
	 */
	public function finish_delete()
	{
		if (! $this->import["delete_items"])
			return;

		if (! $this->is_field('id'))
			return;

		$this->delete(0);
	}

	/**
	 * Обработка временных данных поля "Родитель"
	 *
	 * @return void
	 */
	public function finish_parent()
	{
		if ($this->import["type"] != 'category')
			return;

		if (! $this->is_field("parent"))
			return;

		// если задано поле "Родитель" у категорий
		$rows = DB::query_fetch_all("SELECT id".(! $this->field("parent", "param_type") || $this->field("parent", "param_type") == "name" ? ", import_parent_id" : '').", parent_id FROM {shop_category} WHERE `import`='1'");
		foreach ($rows as $row)
		{
			if($row["parent_id"])
			{
				// удаляем всех старых родителей
				DB::query("DELETE FROM {shop_category_parents} WHERE element_id=%d", $row["id"]);
			}

			if ((! $this->field("parent", "param_type") || $this->field("parent", "param_type") == "name") && $row["import_parent_id"])
			{
				if ( ! isset($this->cache["cats"][$row["import_parent_id"]]))
				{
					$this->cache["cats"][$row["import_parent_id"]] =
							DB::query_result("SELECT id FROM {shop_category} WHERE "
							.(! $this->field("parent", "param_type") ? "import_id='%h'" : "[name]='%s'")
							." AND trash='0' LIMIT 1", $row["import_parent_id"]);
				}
				$row["parent_id"] = $this->cache["cats"][$row["import_parent_id"]];
				DB::query("UPDATE {shop_category} SET parent_id=%d WHERE id=%d", $row["parent_id"], $row["id"]);
			}
			if($row["parent_id"])
			{
				$parent_id = $row["parent_id"];
				$parents = array();
				while ($parent_id > 0 && ! in_array($parent_id, $parents))
				{
					$parents[] = $parent_id;
					DB::query("INSERT INTO {shop_category_parents} (`element_id`, `parent_id`) VALUES (%d, %d)", $row["id"], $parent_id);
					$parent_id = DB::query_result("SELECT parent_id FROM {shop_category} WHERE id=%d LIMIT 1", $parent_id);
				}
			}
		}
		// пересчитываем количество детей у всех категорий
		$rows = DB::query_fetch_all("SELECT id FROM {shop_category}");
		foreach ($rows as $row)
		{
			$count = DB::query_result("SELECT COUNT(*) FROM  {shop_category_parents} WHERE parent_id=%d", $row["id"]);
			DB::query("UPDATE {shop_category} SET count_children=%d WHERE id=%d", $count, $row["id"]);
		}

		if(! $this->field("parent", "param_type") || $this->field("parent", "param_type") == "name")
		{
			DB::query("UPDATE {shop_category} SET parent_id=0 WHERE import_parent_id='' AND `import`='1'");
			DB::query("ALTER TABLE {shop_category} DROP `import_parent_id`");
		}
	}

	/**
	 * Обработка временных данных поля "Доступ"
	 *
	 * @return void
	 */
	public function finish_access()
	{
		if ($this->import["type"] == 'brand')
			return;

		if ($this->import["type"] == 'good')
		{
			// для импортированных товаров проверяет доступ к категориям, если ограничен, то органичевает доступ к товару
			$rows = DB::query_fetch_all("SELECT cat_id, id, access FROM {shop} WHERE `import`='1' AND site_id=%d", $this->import["site_id"]);
			foreach ($rows as $row)
			{
				if(! $row["cat_id"])
					continue;

				if(! isset($this->cache["access_cat"][$row["cat_id"]]))
				{
					$this->cache["access_cat"][$row["cat_id"]] = array();
					$access = DB::query_result("SELECT access FROM {shop_category} WHERE id=%d LIMIT 1", $row["cat_id"]);
					if($access)
					{
						$rows_a = DB::query_fetch_all("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='shop' AND element_type='cat'", $row["cat_id"]);
						foreach ($rows_a as $row_a)
						{
							$this->cache["access_cat"][$row["cat_id"]][] = $row_a["role_id"];
						}
					}
				}
				if($this->cache["access_cat"][$row["cat_id"]])
				{
					$access = array();
					if($row["access"])
					{
						$rows_a = DB::query("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='shop' AND element_type='element'", $row["id"]);
						foreach ($rows_a as $row_a)
						{
							$access[] = $row_a["role_id"];
						}
					}
					else
					{
						DB::query("UPDATE {shop} SET access='1' WHERE id=%d", $row["id"]);
					}
					foreach ($this->cache["access_cat"][$row["cat_id"]] as $role_id)
					{
						if(! in_array($role_id, $access))
						{
							DB::query("INSERT INTO {access} (module_name, element_id, element_type, role_id) VALUES ('shop', %d, 'element', %d)", $row["id"], $role_id);
							$access[] = $role_id;
						}
					}
				}
			}
		}

		if ($this->import["type"] == 'category')
		{
			$rows = DB::query_fetch_all("SELECT id, access, parent_id FROM {shop_category} WHERE `import`='1' AND site_id=%d ORDER BY count_children DESC", $this->import["site_id"]);
			foreach ($rows as $row)
			{
				// для импортированных категорий проверяет доступ к родителю, если ограничен, то органичевает доступ к категории
				if($row["parent_id"])
				{
					$this->get_access($row["parent_id"]);
					if($this->cache["access_cat"][$row["parent_id"]])
					{
						$this->get_access($row["id"], $row["access"]);
						foreach ($this->cache["access_cat"][$row["parent_id"]] as $role_id)
						{
							if(! in_array($role_id, $this->cache["access_cat"][$row["id"]]))
							{
								DB::query("INSERT INTO {access} (module_name, element_id, element_type, role_id) VALUES ('shop', %d, 'cat', %d)", $row["id"], $role_id);
								$this->cache["access_cat"][$row["id"]][] = $role_id;
								$row["access"] = '1';
							}
						}
						if(! $row["access"] && $this->cache["access_cat"][$row["id"]])
						{
							DB::query("UPDATE {shop_category} SET access='1' WHERE id=%d", $row["id"]);
						}
					}
				}

				if(! $row["access"] && (! isset($this->cache["access_cat"][$row["id"]]) || ! $this->cache["access_cat"][$row["id"]]))
					continue;

				$this->get_access($row["id"]);

				// ограничевает доступ к вложенным категориям
				$children = $this->diafan->get_children($row["id"], 'shop_category');
				if($children)
				{
					$rows_ch = DB::query_fetch_all("SELECT id, access FROM {shop_category} WHERE id IN (".implode(",", $children).")");
					foreach ($rows_ch as $row_ch)
					{
						$this->get_access($row_ch["id"], $row_ch["access"]);
						foreach ($this->cache["access_cat"][$row["id"]] as $role_id)
						{
							if(! in_array($role_id, $this->cache["access_cat"][$row_ch["id"]]))
							{
								DB::query("INSERT INTO {access} (module_name, element_id, element_type, role_id) VALUES ('shop', %d, 'cat', %d)", $row_ch["id"], $role_id);
								$this->cache["access_cat"][$row_ch["id"]][] = $role_id;
							}
						}
						if(! $row_ch["access"] && $this->cache["access_cat"][$row_ch["id"]])
						{
							DB::query("UPDATE {shop_category} SET access='1' WHERE id=%d", $row_ch["id"]);
						}
					}
				}

				// ограничивает доступ к вложенным товарам
				$rows_ch = DB::query_fetch_all("SELECT id, access FROM {shop} WHERE cat_id=%d", $row["id"]);
				foreach ($rows_ch as $row_ch)
				{
					$access = array();
					if($row_ch["access"])
					{
						$rows_a = DB::query_fetch_all("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='shop' AND element_type='cat'", $row_ch["id"]);
						foreach ($rows_a as $row_a)
						{
							$access[] = $row_a["role_id"];
						}
					}
					foreach ($this->cache["access_cat"][$row["id"]] as $role_id)
					{
						if(! in_array($role_id, $access))
						{
							DB::query("INSERT INTO {access} (module_name, element_id, element_type, role_id) VALUES ('shop', %d, 'element, %d)", $row_ch["id"], $role_id);
						}
					}
					if(! $row_ch["access"] && $this->cache["access_cat"][$row["id"]])
					{
						DB::query("UPDATE {shop} SET access='1' WHERE id=%d", $row_ch["id"]);
					}
				}
			}
			if(DB::query_result("SELECT id FROM {access} WHERE module_name='shop' AND element_type='cat' LIMIT 1"))
			{
				$this->diafan->configmodules('where_access_cat', 'shop', 0, 0, 1);
			}
			else
			{
				$this->diafan->configmodules('where_access_cat', 'shop', 0, 0, 0);
			}
		}
		if(DB::query_result("SELECT id FROM {access} WHERE module_name='shop' AND element_type='element' LIMIT 1"))
		{
			$this->diafan->configmodules('where_access_element', 'shop', 0, 0, 1);
			$this->diafan->configmodules('where_access', 'all', 0, 0, true);
		}
		else
		{
			$this->diafan->configmodules('where_access_element', 'shop', 0, 0, 0);
			if(! DB::query_result("SELECT id FROM {config} WHERE module_name<>'all' AND value='1' AND name LIKE 'where_access%' LIMIT 1"))
			{
				$this->diafan->configmodules('where_access', 'all', 0, 0, 0);
			}
			else
			{
				$this->diafan->configmodules('where_access', 'all', 0, 0, true);
			}
		}
	}

	/**
	 * Формирует массив прав доступа к категории
	 *
	 * @param integer $id номер категории
	 * @param mixed $access общий доступ ограничен/не ограничен
	 * @return void
	 */
	private function get_access($id, $access = 'check')
	{
		if(! isset($this->cache["access_cat"][$id]))
		{
			$this->cache["access_cat"][$id] = array();
			if($access === 'check')
			{
				$access = DB::query("SELECT access FROM {shop_category} WHERE id=%d", $id);
			}
			if($access)
			{
				$rows = DB::query_fetch_all("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='shop' AND element_type='cat'", $id);
				foreach ($rows as $row)
				{
					$this->cache["access_cat"][$id][] = $row["role_id"];
				}
			}
		}
	}

	/**
	 * Обработка временных данных поля "Связанные товары"
	 *
	 * @param array $this->import конфигурация импорта
	 * @param array $this->fields массив типов полей, используемых в импорте
	 * @return void
	 */
	public function finish_rels()
	{
		if ($this->import["type"] != 'good')
			return;

		if(! $this->is_field("id") || ! $this->is_field("rel_goods"))
			return;

		if($this->field("rel_goods", "param_type") == 'site')
			return;

		$type = $this->field("rel_goods", "param_type") == 'article' ? 'article' : 'import_id';

		$rows = DB::query_fetch_all("SELECT id, ".$type." as aid FROM {shop} WHERE `import`='1' AND site_id=%d".($this->import["type"] == 'good' && $this->import["cat_id"] ? " AND cat_id=".$this->import["cat_id"] : ''), $this->import["site_id"]);
		foreach ($rows as $row)
		{
			DB::query("UPDATE {shop_rel} SET rel_element_id=%d WHERE rel_element_id_temp='%s'", $row["id"], $row["aid"]);
		}
		DB::query("DELETE FROM {shop_rel} WHERE rel_element_id=element_id");

		DB::query("ALTER TABLE {shop_rel} DROP `rel_element_id_temp`");
	}

	/**
	 * Отображение элементов в меню
	 *
	 * @return void
	 */
	public function finish_menu()
	{
		if (! $this->menus)
			return;

		foreach ($this->menus as $param)
		{
			$rows = DB::query_fetch_all(
					"SELECT s.*, m.id AS menu_id FROM {menu} AS m"
					." INNER JOIN {".$this->import["table"]."} AS s"
					." ON s.import='1' AND m.element_id=s.id AND m.element_type='%s'"
					." WHERE  m.module_name='shop' AND m.cat_id=%d AND m.trash='0'"
					.($this->import["type"] == 'category' ? ' ORDER BY s.count_children DESC' : ''),
					$this->import["element_type"], $param["id"]
				);
			foreach ($rows as $row)
			{
				if($this->import["type"] == 'category')
				{
					$menu_parent = 0;
					if($row["parent_id"])
					{
						$parents = $this->diafan->get_parents($row["id"], 'shop_category');
						$menu_parent = DB::query_result(
								"SELECT m.id FROM {menu} AS m"
								." INNER JOIN {shop_category} AS s"
								." ON m.element_id=s.id AND m.element_type='cat' AND s.trash='0'"
								." WHERE s.id IN (".implode(',', $parents).") AND m.cat_id=%d AND m.trash='0'"
								." ORDER BY s.count_children ASC LIMIT 1",
								$param["id"]);
					}
					if(! $menu_parent)
					{
						$menu_parent = DB::query_result("SELECT id FROM {menu} WHERE module_name='site' AND element_id=%d AND element_type='element' AND cat_id=%d AND trash='0'", $this->import["site_id"], $param["id"]);
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
					if($this->import["type"] == 'good' && $row["cat_id"])
					{
						$parents = $this->diafan->get_parents($row["cat_id"], 'shop_category');
						$parents[] = $row["cat_id"];
						$menu_parent = DB::query_result(
								"SELECT m.id FROM {menu} AS m"
								." INNER JOIN {shop_category} AS s"
								." ON m.element_id=s.id AND m.element_type='cat' AND s.trash='0'"
								." WHERE s.id IN (".implode(',', $parents).") AND m.cat_id=%d AND m.trash='0'"
								." ORDER BY s.count_children ASC LIMIT 1",
								$param["id"]);
					}
					if(! $menu_parent)
					{
						$menu_parent = DB::query_result("SELECT id FROM {menu} WHERE module_name='site' AND element_id=%d AND element_type='element' AND cat_id=%d AND trash='0'", $this->import["site_id"], $param["id"]);
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
			$rows = DB::query_fetch_all("SELECT id FROM {menu} WHERE cat_id=%d", $param["id"]);
			foreach ($rows as $row)
			{
				$count = DB::query_result("SELECT COUNT(*) FROM  {menu_parents} WHERE parent_id=%d", $row["id"]);
				DB::query("UPDATE {menu} SET count_children=%d WHERE id=%d", $count, $row["id"]);
			}
		}
	}


	/**
	 * Удаление записей в БД
	 *
	 * @param mixed $import import=0, import=1, false
	 * @return void
	 */
	private function delete($import = false)
	{
		switch($this->import["type"])
		{
			case 'good':
				$this->import["element_type"] = 'element';
				break;

			case 'category':
				$this->import["element_type"] = 'cat';
				break;

			default:
				$this->import["element_type"] = $this->import["type"];
				break;
		}
		$this->import["table"] = 'shop'.($this->import["type"] != 'good' ? "_".$this->import["type"] : "");
		$where =  '';
		if($import !== false)
		{
			$where = " AND `import`='".$import."'";
		}
		if($this->import["type"] == 'good' && $this->import["cat_id"])
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
			case 'good':
				DB::query("DELETE FROM {shop_category_rel} WHERE element_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {shop_rel} WHERE element_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {shop_cart_goods} WHERE good_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {shop_wishlist} WHERE good_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {shop_waitlist} WHERE good_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {shop_rel} WHERE rel_element_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {shop_price_param} WHERE price_id IN (SELECT price_id FROM {shop_price} WHERE good_id IN(%s))", implode(",", $ids));
				DB::query("DELETE FROM {shop_price} WHERE good_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {shop_param_element} WHERE element_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {shop_discount_object} WHERE good_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {access} WHERE element_id IN(%s) AND module_name='shop' AND element_type='element'", implode(",", $ids));

				$this->diafan->_tags->delete($ids, "shop");
				$this->diafan->_comments->delete($ids, "shop");
				$this->diafan->_rating->delete($ids, "shop");
				$this->diafan->_attachments->delete($ids, "shop");
				break;

			case 'category':
				DB::query("DELETE FROM {shop_category_parents} WHERE element_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {shop_brand_category_rel} WHERE cat_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {shop_param_category_rel} WHERE cat_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {shop_category_rel} WHERE cat_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {shop_discount_object} WHERE cat_id IN(%s)", implode(",", $ids));
				DB::query("DELETE FROM {access} WHERE element_id IN(%s) AND module_name='shop' AND element_type='cat'", implode(",", $ids));

				$this->diafan->_comments->delete($ids, "shop", "cat");
				$this->diafan->_rating->delete($ids, "shop", "cat");
				break;

			case 'brand':
				DB::query("DELETE FROM {shop_brand_category_rel} WHERE element_id IN(%s)", implode(",", $ids));
				break;
		}
		$this->diafan->_menu->delete($ids, "shop", $this->import["element_type"]);
		$this->diafan->_map->delete($ids, "shop", $this->import["element_type"]);
		$this->diafan->_images->delete($ids, "shop", $this->import["element_type"]);
		$this->diafan->_route->delete($ids, "shop", $this->import["element_type"]);
	}
	/**
	 * Удаляет импортированные элементы с сайта
	 *
	 * @return void
	 */
	public function remove()
	{
		$this->import = DB::query_fetch_array("SELECT * FROM {shop_import_category} WHERE id=%d LIMIT 1", $this->diafan->_route->cat);

		$this->delete(1);
		$this->diafan->_cache->delete("", "shop");

		$this->diafan->redirect_js(URL.'error3/');
	}

	/**
	 * Публикует / скрывает результаты импорта
	 *
	 * @param boolean $act активность элемента на сайте
	 * @return void
	 */
	public function act($act)
	{
		$this->import = DB::query_fetch_array("SELECT * FROM {shop_import_category} WHERE id=%d LIMIT 1", $this->diafan->_route->cat);
		$this->import["table"] = 'shop'.($this->import["type"] != 'good' ? "_".$this->import["type"] : "");
		switch($this->import["type"])
		{
			case 'good':
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
			.($this->import["type"] == 'good' && $this->import["cat_id"] ? " AND cat_id=".$this->import["cat_id"] : ''),
			$act ? 1 : 0, $this->import["site_id"]);

		// индексирует / удаляет индекс для карты сайта
		if(in_array("map", $this->diafan->installed_modules))
		{
			if($act)
			{
				$rows = DB::query_fetch_all("SELECT * FROM {".$this->import["table"]."} WHERE site_id=%d AND import='1'"
				.($this->import["type"] == 'good' && $this->import["cat_id"] ? " AND cat_id=".$this->import["cat_id"] : ''),
				$this->import["site_id"], "id");
				foreach($rows as $i => &$row)
				{
					if(! empty($row["map_no_show"]))
					{
						unset($rows[$i]);
						continue;
					}
					$row["module_name"] = "shop";
					$row["element_type"] = $this->import["element_type"];
				}
				$this->diafan->_map->index_elements($rows);
			}
			else
			{
				$ids = DB::query_fetch_value("SELECT id FROM {".$this->import["table"]."} WHERE site_id=%d AND import='1'"
				.($this->import["type"] == 'good' && $this->import["cat_id"] ? " AND cat_id=".$this->import["cat_id"] : ''), $this->import["site_id"], "id");
				$this->diafan->_map->delete($ids, 'shop', $this->import["element_type"]);
			}
		}

		if($this->import["type"] != 'good')
		{
			DB::query("UPDATE {menu} SET [act]='%d' WHERE module_name='shop' AND element_type='%s' AND element_id IN (SELECT id FROM {".$this->import["table"]."} WHERE site_id=%d AND `import`='1')", $act ? 1 : 0, $this->import["element_type"], $this->import["site_id"]);
		}
		$this->diafan->_cache->delete("", "shop");

		$this->diafan->redirect_js(URL.'success1/');
	}
}
