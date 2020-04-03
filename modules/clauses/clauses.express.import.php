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
 * Clauses_express_import
 */
class Clauses_express_import extends Service_express_import
{
	/**
	 * Инициирует импорт
	 *
	 * @param integer $cat_id идентификатор описания импорта/экспорта
	 * @return mixed (false|'success'|'next'|'empty')
	 */
	public function init($cat_id)
	{
		return parent::init($cat_id);
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
		if ($row["type"] == "param")
		{
			$this->params[$k] = array(
					'name' => $row["name"],
					'required' => $row["required"],
				);
			$params = unserialize($row["params"]);
			$this->params[$k]["id"] = $params["id"];
			$this->params[$k]["directory"] = $params["directory"];
			return true;
		}
		return false;
	}

	/**
	 * Подготовка базы данных
	 *
	 * @return void
	 */
	protected function prepare()
	{
		parent::prepare();

		// подготовка к импорту поля "Связанные элементы"
		$this->prepare_rels();
	}

	/**
	 * Подготавливает к импорту поле "Связанные элементы"
	 *
	 * @return void
	 */
	private function prepare_rels()
	{
		if ($this->import["type"] != 'element')
			return;

		if (! $this->is_field("id") || ! $this->is_field("rel_clauses"))
			return;

		if($this->field("rel_clauses", "param_type") == 'site')
			return;

		$tables = $this->diafan->_service->get_tables();
		if(! empty($tables[$this->import["module_name"]."_rel"]) && in_array("rel_element_id_temp", $tables[$this->import["module_name"]."_rel"]))
		{
			DB::query("ALTER TABLE {%s_rel} DROP `rel_element_id_temp`", $this->import["module_name"]);
		}
		DB::query("ALTER TABLE {%s_rel} ADD `rel_element_id_temp` VARCHAR(100) NOT NULL DEFAULT ''", $this->import["module_name"]);
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
		if (in_array($type, array("cats", "rel_clauses", "images", "access")))
		{
			if (in_array($type, array("images")))
			{// замена переносов строк, табуляций, а также недопустимых символы в адресе изображения на разделитель
				$d = explode($this->import["sub_delimiter"], $value);
				$val = array();
				foreach ($d as $i => $v)
				{
					$v = trim($v);
					if(! $v) continue;
					$val[$i] = $v;
				}
				foreach ($val as $i => $v)
				{
					$temp = array('image_address' => '', 'alt' => '', 'title' => '');
					if($this->field('images', 'param_second_delimitor'))
					{
						$r = explode($this->field('images', 'param_second_delimitor'), $v);
						$temp["image_address"] = $r[0];
						if(! empty($r[1])) $temp["alt"] = $r[1];
						if(! empty($r[2])) $temp["title"] = $r[2];
					}
					else $temp["image_address"] = $v;
					$val[$i] = $temp;
				}
				$value = array();
				foreach ($val as $i => $v)
				{
					// замена переносов строк, табуляции, пробела на разделитель
					$v["image_address"] = str_replace(array("\r\n", "\r", "\n", "\t", " "), $this->import["sub_delimiter"], $v["image_address"]);
					$v["image_address"] = str_replace($this->import["sub_delimiter"].$this->import["sub_delimiter"], $this->import["sub_delimiter"], $v["image_address"]);
					$v["image_address"] = explode($this->import["sub_delimiter"], $v["image_address"]);
					$add_new = false;
					foreach ($v["image_address"] as $image_address)
					{
						if(empty($image_address)) continue;
						$value[] = $image_address; $add_new = true;
					}
					if($add_new && $this->field('images', 'param_second_delimitor')
					&& (! empty($v["alt"]) || ! empty($v["title"])))
					{
						$last_key = key(array_slice($value, -1, 1, TRUE));
						$value[$last_key] .= $this->field('images', 'param_second_delimitor').$v["alt"]
							.$this->field('images', 'param_second_delimitor').$v["title"];
					}
				}
			}
			else
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

				if (in_array($type, array("cats")))
				{
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
			case 'rel_clauses':
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
				$new_value = strip_tags($value);
				if($value !=  $new_value)
				{
					$this->error_validate($type, 'HTML-теги не допустимы');
					$value = $new_value;
				}
				break;
			case 'act':
			case 'map_no_show':
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
				if(! Custom::exists('modules/'.$this->import["module_name"].'/views/'.$this->import["module_name"].'.view.'.$value.'.php'))
				{
					$this->error_validate($type, $this->diafan->_('файл %s не существует', ABSOLUTE_PATH.'modules/'.$this->import["module_name"].'/views/'.$this->import["module_name"].'.view.'.$value.'.php'), false, false);
					$value = '';
				}
				break;
			case 'created':
				if($error = Validate::datetime($value))
				{
					$this->error_validate($type, $error);
					$value = 0;
				}
				else
				{
					$value = $this->diafan->unixdate($value);
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
		return parent::prepare_data();
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
			$this->insert_row();
		}

		$this->set_images();
		$this->set_access();
		$this->set_category_rel();

		if ($this->import["type"] == 'element')
		{
			$this->set_rels();
		}

		$this->set_rewrite();
		$this->set_redirect();
		$this->set_map();
		$this->set_menu();
	}

	/**
	 * Завершающие операции импорта
	 *
	 * @return void
	 */
	protected function finish()
	{
		if(! isset($this->cache_data["finish"]))
		{
			$this->cache_data["finish"] = array(
				"finish_update_sort" => array(
					"title" => $this->diafan->_('Обновление сортировки'),
					"result" => false,
				),
				"finish_rels" => array(
					"title" => $this->diafan->_('Обработка временных данных поля Связанные элементы'),
					"result" => false,
				),
				"finish_delete" => array(
					"title" => $this->diafan->_('Удаление старых записей'),
					"result" => false,
				),
				"finish_parent" => array(
					"title" => $this->diafan->_('Обработка временных данных поля Родитель'),
					"result" => false,
				),
				"finish_access" => array(
					"title" => $this->diafan->_('Обработка временных данных поля Доступ'),
					"result" => false,
				),
				"finish_menu" => array(
					"title" => $this->diafan->_('Отображение элементов в меню'),
					"result" => false,
				),
				"finish_images" => array(
					"title" => $this->diafan->_('Удаление неактуальных изображений'),
					"result" => false,
				),
			);
		}
		if(! $this->finish_update_sort())
		{
			return false;
		}
		if(! $this->finish_rels())
		{
			return false;
		}
		if(! $this->finish_delete())
		{
			return false;
		}
		if(! $this->finish_parent())
		{
			return false;
		}
		if(! $this->finish_access())
		{
			return false;
		}
		if(! $this->finish_menu())
		{
			return false;
		}
		if(! $this->finish_images())
		{
			return false;
		}

		// завершены последние операции импорта
		return true;
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
		if($this->is_field("sort"))
		{
			$fields[] = "sort";
			$mask[] = "%d";
			$values[] = $this->field_value("sort");
		}
		else
		{
			if($this->import["type"] == 'element')
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
		}
		if($this->import["type"] == 'element')
		{
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
			if($this->is_field("created"))
			{
				$fields[] = "created";
				$mask[] = "%d";
				$values[] = $this->field_value("created");
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
		if($this->import["type"] == 'category')
		{
			if(! $this->diafan->configmodules("cat", $this->import["module_name"], $this->import["site_id"]))
			{
				$this->error_validate(($this->is_field("cats") ? 'cats' : ''), 'Импорт категорий невозможен, так как в настройках модуля отключен данный параметр.');
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
		}
		if($this->import["type"] == 'element')
		{
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
			if($this->is_field("created"))
			{
				$query .= ", created=%d";
				$values[] = $this->field_value("created");
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

		DB::query("DELETE FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='%s'", $this->id, $this->import["module_name"], $this->import["element_type"]);
		$value = $this->field_value("access");
		if(! $value)
			return;
		foreach ($value as $role_id)
		{
			DB::query("INSERT INTO {access} (module_name, element_id, element_type, role_id) VALUES ('%s', %d, '%s', %d)", $this->import["module_name"], $this->id, $this->import["element_type"], $role_id);
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
			$this->diafan->_route->save($value, $this->field_value("name"), $this->id, $this->import["module_name"], $this->import["element_type"], $this->import["site_id"], (! empty($this->cache["current_cat"]) ? $this->cache["current_cat"] : 0), $parent_id);
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
			DB::query("DELETE FROM {redirect} WHERE module_name='%s' AND element_id=%d AND element_type='%s'", $this->import["module_name"], $this->id, $this->import["element_type"]);
		}
		if($this->field_value("redirect"))
		{
			if($this->update && $id = DB::query_result("SELECT id FROM {redirect} WHERE module_name='%s' AND element_id=%d AND element_type='%s'", $this->import["module_name"], $this->id, $this->import["element_type"]))
			{
				DB::query("UPDATE {redirect} SET redirect='%s', code=%d WHERE id=%d", $redirect, $code, $id);
			}
			else
			{
				DB::query("INSERT INTO {redirect} (redirect, code, module_name, element_id, element_type)"
					." VALUES ('%s', %d, '%s', %d, '%s')",
					$redirect, $code, $this->import["module_name"], $this->id, $this->import["element_type"]);
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
			$element_row = array(
				"module_name" => $this->import["module_name"],
				"id"          => $this->id,
				"site_id"     => $this->import["site_id"],
			);
			if($this->import["type"] == 'element')
			{
				$element_row["cat_id"] = $this->cache["current_cat"];
			}
			$element_row["element_type"] = $this->import["element_type"];

			$hide_map = true;
			// вычисляется на каких языковых зеркалах статья/категория активны
			foreach($this->diafan->_languages->all as $l)
			{
				$element_row["act".$l["id"]] = false;
				if($l["id"] == _LANG && ($this->is_field("act") || $this->import["act_items"]))
				{
					if($this->field_value("act") || $this->import["act_items"])
					{
						$element_row["act".$l["id"]] = true;
						$hide_map = false;
					}
				}
				elseif(! empty($this->oldrow["act".$l["id"]]))
				{
					$element_row["act".$l["id"]] = true;
					$hide_map = false;
				}
			}
			if($this->is_field("date_start") && $this->field_value("date_start"))
			{
				$element_row["date_start"] = $this->field_value("date_start");
			}
			if($this->is_field("date_finish") && $this->field_value("date_finish"))
			{
				$element_row["date_finish"] = $this->field_value("date_finish");
			}
			if(! $hide_map)
			{
				$this->diafan->_map->index_element($element_row);
			}
		}
		if($hide_map && $this->update)
		{
			$this->diafan->_map->delete($this->id, $this->import["module_name"], $this->import["element_type"]);
		}
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

		parent::set_category_rel();
	}

	/**
	 * Возвращает имя таблицы базы данных без префикса для обработки поля "Дополнительные категории"
	 *
	 * @return string
	 */
	protected function get_table_cat_rel()
	{
		return parent::get_table_cat_rel();
	}

	/**
	 * Обработка поля "Связанные статьи"
	 *
	 * @return void
	 */
	private function set_rels()
	{
		if ($this->import["type"] != 'element')
			return;

		if(! $this->is_field("id") || ! $this->is_field("rel_clauses"))
			return;

		DB::query("DELETE FROM {%s_rel} WHERE element_id=%d", $this->import["module_name"], $this->id);

		if (! $this->field_value("rel_clauses"))
			return;

		if($this->field("rel_clauses", "param_type") == 'site')
		{
			foreach ($this->field_value("rel_clauses") as $relation)
			{
				DB::query("INSERT INTO {%s_rel} (element_id, rel_element_id) VALUES (%d, %d)", $this->import["module_name"], $this->id, $relation);
			}
			return;
		}

		foreach ($this->field_value("rel_clauses") as $relation)
		{
			DB::query("INSERT INTO {%s_rel} (element_id, rel_element_id_temp) VALUES (%d, '%s')", $this->import["module_name"], $this->id, $relation);
		}
	}

	/**
	 * Загружает все изображения статьи
	 *
	 * @return void
	 */
	private function set_images()
	{
		$this->cache["images"] = array();

		if(! $this->is_field("images"))
			return;

		$this->diafan->_images->delete($this->id, $this->import["module_name"], $this->import["element_type"], 0, false);

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
		if(! $this->field_value("images"))
			return;

		if(! $images = $this->get_images_data($this->field_value("images"), $this->field("images", 'param_directory')))
			return;

		foreach ($images as $image)
		{
			try
			{
				$this->diafan->_images->upload($this->id, $this->import["module_name"], $this->import["element_type"], $this->import['site_id'], $image['address'], $image['name']);
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
	private function get_images_data($value, $dir, $name = false)
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
	 * Обновление сортировки
	 *
	 * @return void
	 */
	private function finish_update_sort()
	{
		if(! empty($this->cache_data["finish"]["finish_update_sort"]["result"]))
		{
			return true;
		}

		DB::query("UPDATE {".$this->import["table"]."} SET sort=id WHERE sort=0");

		$this->cache_data["finish"]["finish_update_sort"]["result"] = true;
		return false;
	}

	/**
	 * Обработка временных данных поля "Доступ"
	 *
	 * @return void
	 */
	private function finish_access()
	{
		if(! empty($this->cache_data["finish"]["finish_access"]["result"]))
		{
			return true;
		}

		if ($this->import["type"] == 'brand')
		{
			$this->cache_data["finish"]["finish_access"]["result"] = true;
			return false;
		}

		if ($this->import["type"] == 'element')
		{
			// для импортированных товаров проверяет доступ к категориям, если ограничен, то органичевает доступ к статье
			$rows = DB::query_fetch_all("SELECT cat_id, id, access FROM {%s} WHERE `import`='1' AND site_id=%d", $this->import["module_name"], $this->import["site_id"]);
			foreach ($rows as $row)
			{
				if(! $row["cat_id"])
					continue;

				if(! isset($this->cache["access_cat"][$row["cat_id"]]))
				{
					$this->cache["access_cat"][$row["cat_id"]] = array();
					$access = DB::query_result("SELECT access FROM {%s_category} WHERE id=%d LIMIT 1", $this->import["module_name"], $row["cat_id"]);
					if($access)
					{
						$rows_a = DB::query_fetch_all("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='cat'", $row["cat_id"], $this->import["module_name"]);
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
						$rows_a = DB::query("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='element'", $row["id"], $this->import["module_name"]);
						foreach ($rows_a as $row_a)
						{
							$access[] = $row_a["role_id"];
						}
					}
					else
					{
						DB::query("UPDATE {%s} SET access='1' WHERE id=%d", $this->import["module_name"], $row["id"]);
					}
					foreach ($this->cache["access_cat"][$row["cat_id"]] as $role_id)
					{
						if(! in_array($role_id, $access))
						{
							DB::query("INSERT INTO {access} (module_name, element_id, element_type, role_id) VALUES ('%s', %d, 'element', %d)",
							$this->import["module_name"],
							$row["id"], $role_id);
							$access[] = $role_id;
						}
					}
				}
			}
		}

		if ($this->import["type"] == 'category')
		{
			$rows = DB::query_fetch_all("SELECT id, access, parent_id FROM {%s_category} WHERE `import`='1' AND site_id=%d ORDER BY count_children DESC", $this->import["module_name"], $this->import["site_id"]);
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
								DB::query("INSERT INTO {access} (module_name, element_id, element_type, role_id) VALUES ('%s', %d, 'cat', %d)",
								$this->import["module_name"],
								$row["id"], $role_id);
								$this->cache["access_cat"][$row["id"]][] = $role_id;
								$row["access"] = '1';
							}
						}
						if(! $row["access"] && $this->cache["access_cat"][$row["id"]])
						{
							DB::query("UPDATE {%s_category} SET access='1' WHERE id=%d", $this->import["module_name"], $row["id"]);
						}
					}
				}

				if(! $row["access"] && (! isset($this->cache["access_cat"][$row["id"]]) || ! $this->cache["access_cat"][$row["id"]]))
					continue;

				$this->get_access($row["id"]);

				// ограничевает доступ к вложенным категориям
				$children = $this->diafan->get_children($row["id"], $this->import["module_name"].'_category');
				if($children)
				{
					$rows_ch = DB::query_fetch_all("SELECT id, access FROM {%s_category} WHERE id IN (".implode(",", $children).")", $this->import["module_name"]);
					foreach ($rows_ch as $row_ch)
					{
						$this->get_access($row_ch["id"], $row_ch["access"]);
						foreach ($this->cache["access_cat"][$row["id"]] as $role_id)
						{
							if(! in_array($role_id, $this->cache["access_cat"][$row_ch["id"]]))
							{
								DB::query("INSERT INTO {access} (module_name, element_id, element_type, role_id) VALUES ('%s', %d, 'cat', %d)",
								$this->import["module_name"],
								$row_ch["id"], $role_id);
								$this->cache["access_cat"][$row_ch["id"]][] = $role_id;
							}
						}
						if(! $row_ch["access"] && $this->cache["access_cat"][$row_ch["id"]])
						{
							DB::query("UPDATE {%s_category} SET access='1' WHERE id=%d", $this->import["module_name"], $row_ch["id"]);
						}
					}
				}

				// ограничивает доступ к вложенным статьям
				$rows_ch = DB::query_fetch_all("SELECT id, access FROM {%s} WHERE cat_id=%d", $this->import["module_name"], $row["id"]);
				foreach ($rows_ch as $row_ch)
				{
					$access = array();
					if($row_ch["access"])
					{
						$rows_a = DB::query_fetch_all("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='cat'", $row_ch["id"], $this->import["module_name"]);
						foreach ($rows_a as $row_a)
						{
							$access[] = $row_a["role_id"];
						}
					}
					foreach ($this->cache["access_cat"][$row["id"]] as $role_id)
					{
						if(! in_array($role_id, $access))
						{
							DB::query("INSERT INTO {access} (module_name, element_id, element_type, role_id) VALUES ('%s', %d, 'element, %d)",
							$this->import["module_name"],
							$row_ch["id"], $role_id);
						}
					}
					if(! $row_ch["access"] && $this->cache["access_cat"][$row["id"]])
					{
						DB::query("UPDATE {%s} SET access='1' WHERE id=%d", $this->import["module_name"], $row_ch["id"]);
					}
				}
			}
			if(DB::query_result("SELECT id FROM {access} WHERE module_name='%s' AND element_type='cat' LIMIT 1", $this->import["module_name"]))
			{
				$this->diafan->configmodules('where_access_cat', $this->import["module_name"], 0, 0, 1);
			}
			else
			{
				$this->diafan->configmodules('where_access_cat', $this->import["module_name"], 0, 0, 0);
			}
		}
		if(DB::query_result("SELECT id FROM {access} WHERE module_name='%s' AND element_type='element' LIMIT 1", $this->import["module_name"]))
		{
			$this->diafan->configmodules('where_access_element', $this->import["module_name"], 0, 0, 1);
			$this->diafan->configmodules('where_access', 'all', 0, 0, true);
		}
		else
		{
			$this->diafan->configmodules('where_access_element', $this->import["module_name"], 0, 0, 0);
			if(! DB::query_result("SELECT id FROM {config} WHERE module_name<>'all' AND value='1' AND name LIKE 'where_access%' LIMIT 1"))
			{
				$this->diafan->configmodules('where_access', 'all', 0, 0, 0);
			}
			else
			{
				$this->diafan->configmodules('where_access', 'all', 0, 0, true);
			}
		}

		$this->cache_data["finish"]["finish_access"]["result"] = true;
		return false;
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
				$access = DB::query("SELECT access FROM {%s_category} WHERE id=%d", $this->import["module_name"], $id);
			}
			if($access)
			{
				$rows = DB::query_fetch_all("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='cat'", $id, $this->import["module_name"]);
				foreach ($rows as $row)
				{
					$this->cache["access_cat"][$id][] = $row["role_id"];
				}
			}
		}
	}

	/**
	 * Обработка временных данных поля "Связанные статьи"
	 *
	 * @param array $this->import конфигурация импорта
	 * @param array $this->fields массив типов полей, используемых в импорте
	 * @return void
	 */
	private function finish_rels()
 	{
 		if(! empty($this->cache_data["finish"]["finish_rels"]["result"]))
 		{
 			return true;
 		}

 		if ($this->import["type"] != 'element')
 		{
 			$this->cache_data["finish"]["finish_rels"]["result"] = true;
 			return false;
 		}

 		if(! $this->is_field("id") || ! $this->is_field("rel_goods"))
 		{
 			$this->cache_data["finish"]["finish_rels"]["result"] = true;
 			return false;
 		}

 		if($this->field("rel_goods", "param_type") == 'site')
 		{
 			$this->cache_data["finish"]["finish_rels"]["result"] = true;
 			return false;
 		}

 		$type = $this->field("rel_goods", "param_type") == 'article' ? 'article' : 'import_id';

 		$rows = DB::query_fetch_all("SELECT id, ".$type." as aid FROM {%s} WHERE `import`='1' AND site_id=%d".($this->import["type"] == 'element' && $this->import["cat_id"] ? " AND cat_id=".$this->import["cat_id"] : ''), $this->import["module_name"], $this->import["site_id"]);
 		foreach ($rows as $row)
 		{
 			DB::query("UPDATE {%s_rel} SET rel_element_id=%d WHERE rel_element_id_temp='%s'", $this->import["module_name"], $row["id"], $row["aid"]);
 		}
 		DB::query("DELETE FROM {%s_rel} WHERE rel_element_id=element_id", $this->import["module_name"]);

 		DB::query("ALTER TABLE {%s_rel} DROP `rel_element_id_temp`", $this->import["module_name"]);

 		$this->cache_data["finish"]["finish_rels"]["result"] = true;
 		return false;
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
			if($import !== false)
			{
				$this->diafan->_images->clear($this->import["module_name"]);
			}
			return;
		}
		DB::query("DELETE FROM {".$this->import["table"]."} WHERE id IN(%s)", implode(",", $ids));
		switch($this->import["type"])
		{
			case 'element':
				DB::query("DELETE FROM {%s_category_rel} WHERE element_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_rel} WHERE element_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_rel} WHERE rel_element_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {access} WHERE element_id IN(%s) AND module_name='%s' AND element_type='element'", implode(",", $ids), $this->import["module_name"]);

				if(in_array('tags', $this->diafan->installed_modules)) $this->diafan->_tags->delete($ids, $this->import["module_name"]);
				if(in_array('comments', $this->diafan->installed_modules)) $this->diafan->_comments->delete($ids, $this->import["module_name"]);
				if(in_array('rating', $this->diafan->installed_modules)) $this->diafan->_rating->delete($ids, $this->import["module_name"]);
				if(in_array('attachments', $this->diafan->installed_modules)) $this->diafan->_attachments->delete($ids, $this->import["module_name"]);
				break;

			case 'category':
				DB::query("DELETE FROM {%s_category_parents} WHERE element_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_category_rel} WHERE cat_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_discount_object} WHERE cat_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {access} WHERE element_id IN(%s) AND module_name='%s' AND element_type='cat'", implode(",", $ids), $this->import["module_name"]);

				if(in_array('comments', $this->diafan->installed_modules)) $this->diafan->_comments->delete($ids, $this->import["module_name"], "cat");
				if(in_array('rating', $this->diafan->installed_modules)) $this->diafan->_rating->delete($ids, $this->import["module_name"], "cat");
				break;
		}
		if(in_array('menu', $this->diafan->installed_modules)) $this->diafan->_menu->delete($ids, $this->import["module_name"], $this->import["element_type"]);
		if(in_array('map', $this->diafan->installed_modules)) $this->diafan->_map->delete($ids, $this->import["module_name"], $this->import["element_type"]);
		$this->diafan->_images->delete($ids, $this->import["module_name"], $this->import["element_type"], false, ($import !== false ? true : false));
		if($import !== false)
		{
			$this->diafan->_images->clear($this->import["module_name"]);
		}
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
	 * Очистка изображений, ранее маркерованных, как удаленные
	 *
	 * @return void
	 */
	protected function finish_images()
 	{
 		if(! empty($this->cache_data["finish"]["finish_menu"]["result"]))
 		{
 			return true;
 		}

 		$this->diafan->_images->clear($this->import["module_name"]);

 		$this->cache_data["finish"]["finish_menu"]["result"] = true;
 		return false;
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
		if(! parent::act($cat_id, $act))
		{
			return false;
		}

		$this->diafan->_cache->delete("", $this->import["module_name"]);
		return true;
	}
}
