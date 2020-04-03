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
 * Shop_express_import
 */
class Shop_express_import extends Service_express_import
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
			$this->params[$k]["select_type"] = $params["select_type"];
			$this->params[$k]["directory"] = $params["directory"];
			$p = DB::query_fetch_array("SELECT type, config FROM {%s_param} WHERE id=%d LIMIT 1", $this->import["module_name"], $params["id"]);
			$this->params[$k]["type"] = $p["type"];
			$this->params[$k]["config"] = unserialize($p["config"]);
			$this->params[$k]["config"]["param_id"] = $params["id"];
			$this->params[$k]["values"] = array();
			if ($this->params[$k]["type"] == 'select' || $this->params[$k]["type"] == 'multiple')
			{
				$this->params[$k]["values"] = DB::query_fetch_key_value("SELECT id, [name] FROM {%s_param_select} WHERE param_id=%d", $this->import["module_name"], $params["id"], "name", "id");
			}
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

		// подготовка к импорту поля "Цена"
		$this->prepare_price();
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

		if (! $this->is_field("id") || ! $this->is_field("rel_goods"))
			return;

		if($this->field("rel_goods", "param_type") == 'site')
			return;

		$tables = $this->diafan->_service->get_tables();
		if(! empty($tables[$this->import["module_name"]."_rel"]) && in_array("rel_element_id_temp", $tables[$this->import["module_name"]."_rel"]))
		{
			DB::query("ALTER TABLE {%s_rel} DROP `rel_element_id_temp`", $this->import["module_name"]);
		}
		DB::query("ALTER TABLE {%s_rel} ADD `rel_element_id_temp` VARCHAR(100) NOT NULL DEFAULT ''", $this->import["module_name"]);
	}

	/**
	 * Подготавливает к импорту поле "Цена"
	 *
	 * @return void
	 */
	private function prepare_price()
	{
		if ($this->import["type"] != 'element')
			return;

		if (! $this->is_field("id") || ! $this->is_field("price"))
			return;

		$tables = $this->diafan->_service->get_tables();
		if(! empty($tables[$this->import["module_name"]."_price"]) && in_array("import_price_del", $tables[$this->import["module_name"]."_price"]))
		{
			DB::query("ALTER TABLE {%s_price} DROP `import_price_del`", $this->import["module_name"]);
		}
		DB::query("ALTER TABLE {%s_price} ADD `import_price_del` ENUM('0', '1') NOT NULL DEFAULT '0' COMMENT 'запись подлежит удалению: 0 - нет, 1 - да'", $this->import["module_name"]);
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
		if (in_array($type, array("cats", "rel_goods", "images", "access", "yandex", "google", "price", "count")))
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
				if(! Custom::exists('modules/'.$this->import["module_name"].'/views/'.$this->import["module_name"].'.view.'.$value.'.php'))
				{
					$this->error_validate($type, $this->diafan->_('файл %s не существует', ABSOLUTE_PATH.'modules/'.$this->import["module_name"].'/views/'.$this->import["module_name"].'.view.'.$value.'.php'), false, false);
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
			$this->set_params();
			$this->set_price_count();
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
				"finish_price" => array(
					"title" => $this->diafan->_('Обработка временных данных поля Цена'),
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
		if(! $this->finish_price())
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
							$this->cache["brands"] = DB::query_fetch_key_value("SELECT id, [name] FROM {%s_brand} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "name", "id");
						}
						if(! isset($this->cache["brands"][$this->field_value("brand")]))
						{
							$this->cache["brands"][$this->field_value("brand")] = $this->add_brand($this->field_value("brand"));
						}
						$values[] = (! empty($this->cache["brands"][$this->field_value("brand")]) ? $this->cache["brands"][$this->field_value("brand")] : '');
						break;

					default:
						if(! isset($this->cache["brands"]))
						{
							$this->cache["brands"] = DB::query_fetch_key_value("SELECT id, import_id FROM {%s_brand} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "import_id", "id");
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
							$this->cache["brands"] = DB::query_fetch_key_value("SELECT id, [name] FROM {%s_brand} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "name", "id");
						}
						if(! isset($this->cache["brands"][$this->field_value("brand")]))
						{
							$this->cache["brands"][$this->field_value("brand")] = $this->add_brand($this->field_value("brand"));
						}
						$values[] = (! empty($this->cache["brands"][$this->field_value("brand")]) ? $this->cache["brands"][$this->field_value("brand")] : '');
						break;

					default:
						if(! isset($this->cache["brands"]))
						{
							$this->cache["brands"] = DB::query_fetch_key_value("SELECT id, import_id FROM {%s_brand} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "import_id", "id");
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
			// вычисляется на каких языковых зеркалах товар/категория активны
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

		if(! $this->cache["current_cats"] && $this->import["type"] == 'brand')
		{
			DB::query("INSERT INTO {".$table_cats_rel."} (element_id) VALUES (%d)", $this->id);
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

			case 'brand':
				$table_cats_rel = $this->import["module_name"].'_brand_category_rel';
				break;

			default:
				$table_cats_rel = false;
				break;
		}
		return $table_cats_rel;
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
				if(! empty($row["restored"]))
				{
					// если вся структура цены восстановлена из значений характеристик, влияющих на цену
					$this->cache_data["restored"][$this->id] = true;
				}
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
							DB::query("UPDATE {%s_price} SET price=%f, old_price=%f, count_goods=%f WHERE id=%d", $this->import["module_name"], $row["price"], $row["old_price"], $row["count"], $price["id"]);
						}
						if($row["image_id"] !== false)
						{
							$image_rel = DB::query_fetch_array("SELECT * FROM {%s_price_image_rel} WHERE price_id=%d", $this->import["module_name"], $price["id"]);
							if($image_rel)
							{
								if(! $row["image_id"])
								{
									DB::query("DELETE FROM {%s_price_image_rel} WHERE id=%d", $this->import["module_name"], $image_rel["id"]);
								}
								elseif($image_rel["image_id"] != $row["image_id"])
								{
									DB::query("UPDATE {%s_price_image_rel} SET image_id=%d WHERE id=%d", $this->import["module_name"], $row["image_id"], $image_rel["id"]);
								}
							}
							elseif($row["image_id"])
							{
								DB::query("INSERT INTO {%s_price_image_rel} (price_id, image_id) VALUES (%d, %d)", $this->import["module_name"], $price["id"], $row["image_id"]);
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
					else DB::query("UPDATE {%s_price} SET import_price_del='0' WHERE id=%d LIMIT 1", $this->import["module_name"], $price["price_id"]);
				}
				if($this->is_field("id") && ! $this->is_ready($this->id) && $del_price_ids)
				{
					DB::query("UPDATE {%s_price} SET import_price_del='1' WHERE id IN (%s)", $this->import["module_name"], implode(",", $del_price_ids));
				}
			}
		}
		else
		{
			foreach ($count_value as $row)
			{
				if(! empty($row["restored"]))
				{
					// если вся структура цены восстановлена из значений характеристик, влияющих на цену
					$this->cache_data["restored"][$this->id] = true;
				}
				$price = $this->diafan->_shop->price_get($this->id, $row["params"], false);
				if(! empty($price["price_id"]))
				{
					DB::query("UPDATE {%s_price} SET count_goods=%f WHERE price_id=%d", $this->import["module_name"], $row["count"], $price["price_id"]);
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
			$rows = DB::query_fetch_all("SELECT id, [name] FROM {%s_param} WHERE type='multiple' AND required='1' AND site_id IN (%s) AND trash='0'", $this->import["module_name"], implode(",", array(0, $this->import["site_id"])));
			foreach ($rows as $row)
			{
				$rows_v = DB::query_fetch_all("SELECT id, [name] FROM {%s_param_select} WHERE param_id=%d", $this->import["module_name"], $row["id"]);
				foreach ($rows_v as $row_v)
				{
					$row["values"][$row_v["id"]] = $row_v["name"];
				}
				$rows_cat = DB::query_fetch_all("SELECT cat_id FROM {%s_param_category_rel} WHERE element_id=%d", $this->import["module_name"], $row["id"]);
				if(! empty($rows_cat))
				{
					foreach ($rows_cat as $row_cat)
					{
						$row["cats"][] = $row_cat["cat_id"];
					}
				}
				else
				{
					// восстанавливаем связи дополнительных харакеристик товаров и категорий
					DB::query("INSERT INTO {%s_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $this->import["module_name"], $row["id"], 0);
					$row["cats"][] = 0;
				}
				$this->cache["multiple_params"][$row["id"]] = $row;
			}
		}

		$param_delimitor = $this->field('price', 'param_delimitor') ? $this->field('price', 'param_delimitor') : '&';
		$param_select_type = $this->field('price', 'param_select_type');
		$param_multiplier = $this->diafan->filter($this->field('price', 'param_multiplier'), "float");
		$param_multiplier = ! empty($param_multiplier) ? $param_multiplier : 1;
		$price_index = 0;
		foreach ($this->field_value("price") as $v)
		{
			$price_index++;
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
						$rows = DB::query_fetch_all("SELECT id, name FROM {%s_currency} WHERE trash='0'", $this->import["module_name"]);
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
						$new_v["image_id"] = DB::query_result("SELECT * FROM {images} WHERE element_id=%d AND module_name='%s' AND element_type='%s' AND name='%s'", $this->id, $this->import["module_name"], $this->import["element_type"], $image);
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
			if($param_multiplier != 1) $new_v["price"] = $new_v["price"] * $param_multiplier;
			unset($v[0]);
			$v_params = array();
			foreach ($v as $i => $p)
			{
				if(! $p)
					continue;

				$param_id = $param_value = false;
				$p = explode('=', $p); // list($param_id, $param_value) = explode('=', $p);
				if(! empty($p)) $param_id = array_shift($p);
				if(! empty($p)) $param_value = array_shift($p);

				if(empty($param_id))
				{
					$this->error_validate('price', 'некорректное значение параметра, влияющего на цену');
					continue;
				}
				if(empty($param_value))
				{
					$param_value = 0;
				}

				$v_params[$param_id] = $param_value;
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
				if($in_cats && ! in_array($id, array_keys($v_params)))
				{
					$v_params[$id] = 0;
				}
			}
			$new_params = array(); $restored = 0;
			foreach ($v_params as $id => $value)
			{
				$new_id = 0;
				foreach ($this->cache["multiple_params"] as $k => $param)
				{
					if($param_select_type == 'value')
					{
						$param_id = $param["name"];
					}
					else
					{
						$param_id = $param["id"];
					}
					if($param_id == $id && in_array($param["id"], $multiple_params))
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
								if($param_select_type == 'value')
								{
									$new_value = $this->add_param_value($param["id"], $value);
									$this->cache["multiple_params"][$k]["values"][$new_value] = $value;
								}
								else
								{
									$this->error_validate('price', 'не верно задано значение параметра, влияющего на цену');
									continue;
								}
							}
							$value = $new_value;
						}
						break;
					}
				}
				if(! $new_id)
				{
					if($param_select_type == 'value')
					{
						$new_id = $this->add_param($id, 'multiple', true);
						$this->cache["multiple_params"][$new_id] = array(
							"id" => $new_id,
							"name" => $id,
							"values" => array(),
							"cats" => DB::query_fetch_value("SELECT id FROM {%s_param_category_rel} WHERE element_id=%d", $this->import["module_name"], $new_id, "id")
						);
						$new_value = $this->add_param_value($new_id, $value);
						$this->cache["multiple_params"][$new_id]["values"][$new_value] = $value;
						$value = $new_value;
						$multiple_params[] = $new_id;
					}
					else
					{
						$this->error_validate('price', 'не верно задан параметр, влияющий на цену');
						continue;
					}
				}
				$id = $new_id;
				if(! $value)
				{
					// если значения характеристик, влияющих на цену, не заданы в структуре цены,
					// то ищем их в полях, описывающих дополнительные характеристики товара, согласно аорядкового номера цены
					foreach ($this->params as $param)
					{
						if(empty($param["id"]) || $param["id"] != $id || empty($param["value"])) continue;
						if(is_array($param["value"]))
						{
							$p_i = 0;
							foreach ($param["value"] as $v)
							{
								$p_i++;
								if($price_index == $p_i)
								{
									$value = $v;
									break;
								}
							}
						}
						else
						{
							if($price_index == 1)
							{
								$value = $param["value"];
							}
						}
						if(! empty($this->cache["multiple_params"][$id]["name"])
						&& isset($new_params[$this->cache["multiple_params"][$id]["name"]])
						&& isset($this->cache["multiple_params"][$id]["values"][$value])
						&& empty($new_params[$this->cache["multiple_params"][$id]["name"]]))
						{
							$new_params[$this->cache["multiple_params"][$id]["name"]] = $this->cache["multiple_params"][$id]["values"][$value];
						}
						if($value)
						{
							$restored++;
						}
						break;
					}
				}
				if(! in_array($id, $multiple_params))
				{
					if($value)
					{
						$this->error_validate('price', 'параметр, влияющий на цену не может быть применен к товару');
					}
					continue;
				}
				if($value)
				{
					$new_params[$id] = $value;
				}
			}
			if($restored) // $restored > 0 && $restored <= count($new_params)
			{
				$new_v["restored"] = true; // если вся структура цены восстановлена из значений характеристик, влияющих на цену
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
			$rows = DB::query_fetch_all("SELECT id, [name] FROM {%s_param} WHERE type='multiple' AND required='1' AND site_id IN (%s) AND trash='0'", $this->import["module_name"], implode(",", array(0, $this->import["site_id"])));
			foreach ($rows as $row)
			{
				$rows_v = DB::query_fetch_all("SELECT id, [name] FROM {%s_param_select} WHERE param_id=%d", $this->import["module_name"], $row["id"]);
				foreach ($rows_v as $row_v)
				{
					$row["values"][$row_v["id"]] = $row_v["name"];
				}
				$rows_cat = DB::query_fetch_all("SELECT cat_id FROM {%s_param_category_rel} WHERE element_id=%d", $this->import["module_name"], $row["id"]);
				if(! empty($rows_cat))
				{
					foreach ($rows_cat as $row_cat)
					{
						$row["cats"][] = $row_cat["cat_id"];
					}
				}
				else
				{
					// восстанавливаем связи дополнительных харакеристик товаров и категорий
					DB::query("INSERT INTO {%s_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $this->import["module_name"], $row["id"], 0);
					$row["cats"][] = 0;
				}
				$this->cache["multiple_params"][$row["id"]] = $row;
			}
		}

		$param_delimitor = $this->field('count', 'param_delimitor') ? $this->field('count', 'param_delimitor') : '&';
		$param_select_type = $this->field('count', 'param_select_type');
		$count_value = $this->field_value("count");
		$price_index = 0;
		if(! is_array($count_value))
		{
			$count_value = array($count_value);
		}
		foreach ($count_value as $v)
		{
			$price_index++;
			$new_v = array();
			$v = explode($param_delimitor, $v);
			if($error = Validate::floattext($v[0]))
			{
				$this->error_validate('count', $error);
				continue;
			}
			$new_v["count"] = (float)$v[0];
			unset($v[0]);
			$v_params = array();
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

				$v_params[$param_id] = $param_value;
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
				if($in_cats && ! in_array($id, array_keys($v_params)))
				{
					$v_params[$id] = 0;
				}
			}
			$new_params = array(); $restored = 0;
			foreach ($v_params as $id => $value)
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
					if($param_id == $id && in_array($param["id"], $multiple_params))
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
								if($param_select_type == 'value')
								{
									$new_value = $this->add_param_value($param["id"], $value);
									$this->cache["multiple_params"][$k]["values"][$new_value] = $value;
								}
								else
								{
									$this->error_validate('count', 'не верно задано значение параметра, влияющего на цену');
									continue;
								}
							}
							$value = $new_value;
						}
						break;
					}
				}
				if(! $new_id)
				{
					if($param_select_type == 'value')
					{
						$new_id = $this->add_param($id, 'multiple', true);
						$this->cache["multiple_params"][$new_id] = array(
							"id" => $new_id,
							"name" => $id,
							"values" => array(),
							"cats" => DB::query_fetch_value("SELECT id FROM {%s_param_category_rel} WHERE element_id=%d", $this->import["module_name"], $new_id, "id")
						);
						$new_value = $this->add_param_value($new_id, $value);
						$this->cache["multiple_params"][$new_id]["values"][$new_value] = $value;
						$value = $new_value;
						$multiple_params[] = $new_id;
					}
					else
					{
						$this->error_validate('count', 'не верно задан параметр, влияющий на цену');
						continue;
					}
				}
				$id = $new_id;
				if(! $value)
				{
					// если значения характеристик, влияющих на цену, не заданы в структуре цены,
					// то ищем их в полях, описывающих дополнительные характеристики товара
					foreach ($this->params as $param)
					{
						if(empty($param["id"]) || $param["id"] != $id || empty($param["value"])) continue;
						if(is_array($param["value"]))
						{
							$p_i = 0;
							foreach ($param["value"] as $v)
							{
								$p_i++;
								if($price_index == $p_i)
								{
									$value = $v;
									break;
								}
							}
						}
						else
						{
							if($price_index == 1)
							{
								$value = $param["value"];
							}
						}
						if(! empty($this->cache["multiple_params"][$id]["name"])
						&& isset($new_params[$this->cache["multiple_params"][$id]["name"]])
						&& isset($this->cache["multiple_params"][$id]["values"][$value])
						&& empty($new_params[$this->cache["multiple_params"][$id]["name"]]))
						{
							$new_params[$this->cache["multiple_params"][$id]["name"]] = $this->cache["multiple_params"][$id]["values"][$value];
						}
						if($value)
						{
							$restored++;
						}
						break;
					}
				}
				if(! in_array($id, $multiple_params))
				{
					$this->error_validate('count', 'параметр, влияющий на цену не может быть применен к товару');
					continue;
				}
				if($value)
				{
					$new_params[$id] = $value;
				}
			}
			if($restored) // $restored > 0 && $restored <= count($new_params)
			{
				$new_v["restored"] = true; // если вся структура цены восстановлена из значений характеристик, влияющих на цену
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
		if ($this->import["type"] != 'element')
			return;

		if(! $this->is_field("id") || ! $this->is_field("rel_goods"))
			return;

		DB::query("DELETE FROM {%s_rel} WHERE element_id=%d", $this->import["module_name"], $this->id);

		if (! $this->field_value("rel_goods"))
			return;

		if($this->field("rel_goods", "param_type") == 'site')
		{
			foreach ($this->field_value("rel_goods") as $relation)
			{
				DB::query("INSERT INTO {%s_rel} (element_id, rel_element_id) VALUES (%d, %d)", $this->import["module_name"], $this->id, $relation);
			}
			return;
		}

		foreach ($this->field_value("rel_goods") as $relation)
		{
			DB::query("INSERT INTO {%s_rel} (element_id, rel_element_id_temp) VALUES (%d, '%s')", $this->import["module_name"], $this->id, $relation);
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

			// проверяем распрастранение характеристики на категории, к которым привязан товар
			if(! $this->check_params($param["id"], $this->id))
			{
				$this->error_validate('param', 'значение поля не распрастроняется на категорию товара', $param["name"]);
			}

			if($this->is_field("id") && ! $this->is_ready($this->id))
			{
				DB::query("DELETE FROM {%s_param_element} WHERE param_id=%d AND element_id=%d", $this->import["module_name"], $param["id"], $this->id);
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
								if($param["select_type"] == 'value')
								{
									$new_value[] = $this->params[$k]["values"][$v] = $this->add_param_value($param["id"], $v);
								}
								else $this->error_validate('param', $this->diafan->_('"%s" нет в списке значений', $v), $k, false);
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
						elseif($param["select_type"] == 'value')
						{
							$value = $this->params[$k]["values"][$value] = $this->add_param_value($param["id"], $value);
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
					if(! in_array("attachments", $this->diafan->installed_modules))
						break;

					if(empty($param["directory"]))
					{
						$this->error_validate('param', $this->diafan->_('Невозможно загрузить файлы %s, так как в настройках не указана папка, где они храняться.', $value), $param["name"], false);
						return;
					}

					$this->diafan->_attachments->delete($this->id, $this->import["module_name"], 0, $param["id"]);

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
								$this->diafan->_attachments->upload($a, $this->import["module_name"], $this->id, false, $param["config"]);
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

					$this->diafan->_images->delete($this->id, $this->import["module_name"], $this->import["element_type"], $param["id"], false);

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
								$this->diafan->_images->upload($this->id, $this->import["module_name"], $this->import["element_type"], $this->import['site_id'], $image['address'], $image['name'], false, $param["id"]);
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
					if($ids = DB::query_fetch_value("SELECT id FROM {%s_param_element} WHERE ".$value_name."='%s' AND param_id=%d AND element_id=%d", $this->import["module_name"], $v, $param["id"], $this->id, "id"))
					{
						if(count($ids) > 1)
						{
							$ids = array_slice($ids, -1);
							DB::query("DELETE FROM {%s_param_element} WHERE id IN (%s)", $this->import["module_name"], implode(",", $ids));
						}
					}
					else
					{
						DB::query("INSERT INTO {%s_param_element} (".$value_name.", param_id, element_id) VALUES ('%s', %d, %d)", $this->import["module_name"], $v, $param["id"], $this->id);
					}
				}
			}
			else
			{
				if($ids = DB::query_fetch_value("SELECT id FROM {%s_param_element} WHERE ".$value_name."='%s' AND param_id=%d AND element_id=%d", $this->import["module_name"], $value, $param["id"], $this->id, "id"))
				{
					if(count($ids) > 1)
					{
						$ids = array_slice($ids, -1);
						DB::query("DELETE FROM {%s_param_element} WHERE id IN (%s)", $this->import["module_name"], implode(",", $ids));
					}
				}
				else
				{
					DB::query("INSERT INTO {%s_param_element} (".$value_name.", param_id, element_id) VALUES ('%s', %d, %d)", $this->import["module_name"], $value, $param["id"], $this->id);
				}
			}
			$this->params[$k]["value"] = $value;
		}
	}

	/**
	 * Проверяет распрастранение значений характеристик на товар
	 *
	 * @param integer $param_id индификатор характеристики
	 * @param integer $element_id идентификатор импортируемого элемента
	 * @return boolean
	 */
	private function check_params($param_id, $element_id)
	{
		// TO_DO: использовать данную функцию необходимо после функций:
		// $this->insert_row(), $this->update_row(), $this->set_params(), $this->set_price_count(), $this->set_category_rel().
		if(! $this->diafan->configmodules("cat", $this->import["module_name"], $this->import["site_id"]))
			return true;

		if(! isset($this->cache["param_cat_ids"][$param_id]))
		{
			$this->cache["param_cat_ids"][$param_id] = DB::query_fetch_value("SELECT cat_id FROM {%s_param_category_rel} WHERE element_id=%d", $this->import["module_name"], $param_id, "cat_id");
			if(! is_array($this->cache["param_cat_ids"][$param_id])) $this->cache["param_cat_ids"][$param_id] = array();
		}
		if(! in_array(0, $this->cache["param_cat_ids"][$param_id]))
		{
			if(! isset($this->cache["element_cat_ids"][$element_id]))
			{
				$this->cache["element_cat_ids"][$this->id] = DB::query_fetch_value("SELECT cat_id FROM {%s_category_rel} WHERE element_id=%d", $this->import["module_name"], $element_id, "cat_id");
				if(! is_array($this->cache["element_cat_ids"][$element_id])) $this->cache["element_cat_ids"][$element_id] = array();
			}
			$intersect = array_intersect($this->cache["param_cat_ids"][$param_id], $this->cache["element_cat_ids"][$element_id]);
			if(empty($intersect))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Загружает все изображения товара
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
	 * Получение данных об файлах, доступных для загрузки
	 *
	 * @return array
	 */
	private function get_attachments_data($value, $dir)
	{
		$directory = trim(preg_replace('/(^\/)|(\.)|(\/$)/', '', $dir)).'/';
		$as = array();
		foreach ($value as $i => $address)
		{
			$tmp_name = $this->dir_path.'/importattachs'.$i;
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
	 * Добавляет характеристику
	 *
	 * @param integer $value значение характеристики
	 * @param string $type тип характеристики
	 * @param boolean $required характеристика доступна к выбору при заказе
	 * @return integer
	 */
	private function add_param($value, $type, $required)
	{
		if($values = DB::query_fetch_key_value("SELECT id, [name] FROM {%s_param} WHERE type='%s' AND required='%s' AND site_id IN (%s) AND [name]='%s'", $this->import["module_name"], $type, ($required ? 1 : 0), implode(",", array(0, $this->import["site_id"])), $value, "id", "name"))
		{
			$cats = DB::query_fetch_key_array("SELECT element_id, cat_id FROM {%s_param_category_rel} WHERE element_id IN (%s)", $this->import["module_name"], implode(",", array_keys($values)), "element_id");

			$in_cats = true; $id = false;
			foreach ($values as $key => $name)
			{
				if(empty($cats[$key])) continue;
				$cat_ids = $this->diafan->array_column($cats[$key], "cat_id");
				$in_cats = true;
				if(! in_array(0, $cat_ids) && $this->cache["current_cats"])
				{
					$in_cats = false;
					foreach ($this->cache["current_cats"] as $cat_id)
					{
						if(in_array($cat_id, $cat_ids))
						{
							$in_cats = true;
							break;
						}
					}
				}
				if($in_cats)
				{
					$id = $key;
					break;
				}
			}
			if($id !== false) return $id;
		}

		$id = DB::query("INSERT INTO {%s_param} ([name], type, required, site_id) VALUES ('%s', '%s', '%s', %d)", $this->import["module_name"], $value, $type, ($required ? 1 : 0), $this->import["site_id"]);
		DB::query("UPDATE {%s_param} SET sort=%d WHERE id=%d", $this->import["module_name"], $id, $id);

		if($this->import["type"] == 'element' && $this->import["cat_id"])
		{
			foreach($this->import["cat_ids"] as $val)
			{
				DB::query("INSERT INTO {%s_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $this->import["module_name"], $id, $val);
			}
		}
		else DB::query("INSERT INTO {%s_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $this->import["module_name"], $id, 0);

		return $id;
	}

	/**
	 * Добавляет значение к характеристике
	 *
	 * @param integer $param_id номер характеристики
	 * @param integer $value значение характеристики
	 * @return integer
	 */
	private function add_param_value($param_id, $value)
	{
		if($values = DB::query_fetch_key_value("SELECT id, [name] FROM {%s_param_select} WHERE param_id=%d", $this->import["module_name"], $param_id, "name", "id"))
		{
			if(isset($values[$value]))
			{
				return $values[$value];
			}
		}
		$id = DB::query("INSERT INTO {%s_param_select} ([name], param_id) VALUES ('%s', %d)", $this->import["module_name"], $value, $param_id);

		if(! isset($this->cache["param_page"][$param_id]))
		{
			$this->cache["param_page"][$param_id] = DB::query_result("SELECT page FROM {%s_param} WHERE id=%d LIMIT 1", $this->import["module_name"], $param_id);
		}

		if($this->cache["param_page"][$param_id])
		{
			// Сохранение псевдоссылки
			$rewrite = '';
			$text = $value;
			$element_id = $id;
			$module_name = $this->import["module_name"];
			$element_type = "param";
			$site_id = $this->import["site_id"];
			$cat_id = ($this->import["type"] == 'element' && $this->import["cat_id"] ? $this->import["cat_id"] : 0);
			$parent_id = 0;
			$add_parents = false;
			$change_children = false;
			$this->diafan->_route->save($rewrite, $text, $element_id, $module_name, $element_type, $site_id, $cat_id, $parent_id, $add_parents, $change_children);
		}

		return $id;
	}

	/**
	 * Добавляет производителя
	 *
	 * @param integer $value название производителя
	 * @return integer
	 */
	private function add_brand($value)
	{
		if(! isset($this->cache["brands_names"]))
		{
			$this->cache["brands_names"] = DB::query_fetch_key_value("SELECT id, [name] FROM {%s_brand} WHERE trash='0' AND site_id=%d", $this->import["module_name"], $this->import["site_id"], "name", "id");
		}
		if(! isset($this->cache["brands_names"][$value]))
		{
			$id = DB::query("INSERT INTO {%s_brand} ([name], site_id, import) VALUES ('%s', %d, '%d')", $this->import["module_name"], $value, $this->import["site_id"], 1);
			if($this->import["act_items"])
			{
				DB::query("UPDATE {%s_brand} SET [act]='%d' WHERE id=%d", $this->import["module_name"], 1, $id);
			}
			$this->cache["brands_names"][$value] = (string) $id;

			if($this->import["type"] == 'element' && $this->import["cat_id"])
			{
				foreach($this->import["cat_ids"] as $val)
				{
					DB::query("INSERT INTO {%s_brand_category_rel} (element_id, cat_id) VALUES (%d, %d)", $this->import["module_name"], $id, $val);
				}
			}
			else DB::query("INSERT INTO {%s_brand_category_rel} (element_id, cat_id) VALUES (%d, %d)", $this->import["module_name"], $id, 0);

			// Сохранение псевдоссылки
			$rewrite = '';
			$text = $value;
			$element_id = $id;
			$module_name = $this->import["module_name"];
			$element_type = "brand";
			$site_id = $this->import["site_id"];
			$cat_id = ($this->import["type"] == 'element' && $this->import["cat_id"] ? $this->import["cat_id"] : 0);
			$parent_id = 0;
			$add_parents = false;
			$change_children = false;
			$this->diafan->_route->save($rewrite, $text, $element_id, $module_name, $element_type, $site_id, $cat_id, $parent_id, $add_parents, $change_children);
		}
		return (! empty($this->cache["brands_names"][$value]) ? $this->cache["brands_names"][$value] : false);
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
			// для импортированных товаров проверяет доступ к категориям, если ограничен, то органичевает доступ к товару
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

				// ограничивает доступ к вложенным товарам
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
	 * Обработка временных данных поля "Связанные товары"
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
	 * Обработка временных данных поля "Цена"
	 *
	 * @param array $this->import конфигурация импорта
	 * @param array $this->fields массив типов полей, используемых в импорте
	 * @return void
	 */
	private function finish_price()
	{
		if(! empty($this->cache_data["finish"]["finish_price"]["result"]))
		{
			return true;
		}

		if ($this->import["type"] != 'element')
		{
			$this->cache_data["finish"]["finish_price"]["result"] = true;
			return false;
		}

		if (! $this->is_field("id") || ! $this->is_field("price"))
		{
			$this->cache_data["finish"]["finish_price"]["result"] = true;
			return false;
		}

		$rows = DB::query_fetch_key_array("SELECT id, good_id FROM {%s_price} WHERE import_price_del='1' AND trash='0'", $this->import["module_name"], "good_id");
		foreach ($rows as $good_id => $prices)
		{
			$del_price_ids = $this->diafan->array_column($prices, "id");
			if(empty($del_price_ids)) continue;
			DB::query("DELETE FROM {%s_price_param} WHERE price_id IN (%s)", $this->import["module_name"], implode(",", $del_price_ids));
			DB::query("DELETE FROM {%s_price_image_rel} WHERE price_id IN (%s)", $this->import["module_name"], implode(",", $del_price_ids));
			DB::query("DELETE FROM {%s_price} WHERE price_id IN (%s)", $this->import["module_name"], implode(",", $del_price_ids));
			$this->diafan->_shop->price_calc($good_id);
		}
		DB::query("ALTER TABLE {%s_price} DROP `import_price_del`", $this->import["module_name"]);


		// TO_DO: импорт товаров не требует обязательного указания структуры цены в поле цена.
		// Структура такой цены определяется из импортируемых значений характеристик.
		// Таким образом значения характеристик всегда будут влиять на цену.
		// Следующий ниже по тексту код снимает отметку о влиянии значений характеристик на цену,
		// если такая цена лишь одна у товара.
		//
		if(! empty($this->cache_data["restored"]))
		{
			// если вся структура цены восстановлена из значений характеристик, влияющих на цену,
			// и такая цена у товара одна, то снимаем зависимость цены от значений таких характеристик
			foreach($this->cache_data["restored"] as $key => $dummy)
			{
				$prices = $this->diafan->_shop->price_get_base($key);
				if(count($prices) != 1)
				{
					continue;
				}
				$price = reset($prices);
				// TO_DO: логично снять отметку о влиянии значений характеристик на цену, так как сама цена одна
				DB::query("UPDATE {%s_price_param} SET param_value=%d WHERE price_id=%d", $this->import["module_name"], 0, $price["price_id"]);
				// TO_DO: не логично снимать сам выбор значений, влияющих на цену, так как значений может быть больше одного
				// DB::query("DELETE FROM {%s_param_element} WHERE element_id=%d", $this->import["module_name"], $price["good_id"]);
				// TO_DO: пересчет цен не требуется, так как манипуляции с основной ценой распрастроняются автоматически на все её производные
				// $this->diafan->_shop->price_calc($price["good_id"]);
			}
		}

		$this->cache_data["finish"]["finish_price"]["result"] = true;
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
				DB::query("DELETE FROM {%s_cart} WHERE good_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_wishlist} WHERE good_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_waitlist} WHERE good_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_rel} WHERE rel_element_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_price_param} WHERE price_id IN (SELECT price_id FROM {%s_price} WHERE good_id IN(%s))", $this->import["module_name"], $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_price} WHERE good_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_param_element} WHERE element_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_discount_object} WHERE good_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {access} WHERE element_id IN(%s) AND module_name='%s' AND element_type='element'", implode(",", $ids), $this->import["module_name"]);

				if(in_array('tags', $this->diafan->installed_modules)) $this->diafan->_tags->delete($ids, $this->import["module_name"]);
				if(in_array('comments', $this->diafan->installed_modules)) $this->diafan->_comments->delete($ids, $this->import["module_name"]);
				if(in_array('rating', $this->diafan->installed_modules)) $this->diafan->_rating->delete($ids, $this->import["module_name"]);
				if(in_array('attachments', $this->diafan->installed_modules)) $this->diafan->_attachments->delete($ids, $this->import["module_name"]);
				break;

			case 'category':
				DB::query("DELETE FROM {%s_category_parents} WHERE element_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_brand_category_rel} WHERE cat_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_param_category_rel} WHERE cat_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_category_rel} WHERE cat_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_discount_object} WHERE cat_id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {access} WHERE element_id IN(%s) AND module_name='%s' AND element_type='cat'", implode(",", $ids), $this->import["module_name"]);

				if(in_array('comments', $this->diafan->installed_modules)) $this->diafan->_comments->delete($ids, $this->import["module_name"], "cat");
				if(in_array('rating', $this->diafan->installed_modules)) $this->diafan->_rating->delete($ids, $this->import["module_name"], "cat");
				break;

			case 'brand':
				DB::query("DELETE FROM {%s_brand_category_rel} WHERE element_id IN(%s)", $this->import["module_name"], implode(",", $ids));
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

			$ids = DB::query_fetch_value("SELECT id FROM {%s_brand} WHERE site_id=%d".$where, $this->import["module_name"], $this->import["site_id"], "id");
			if($ids)
			{
				DB::query("DELETE FROM {%s_brand} WHERE id IN(%s)", $this->import["module_name"], implode(",", $ids));
				DB::query("DELETE FROM {%s_brand_category_rel} WHERE element_id IN(%s)", $this->import["module_name"], implode(",", $ids));
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

		// публикует / скрывает результаты импорта автозаполнения
		if($this->import["type"] == 'element')
		{
			$where = " AND `import`='1'";

			$ids = DB::query_fetch_value("SELECT id FROM {%s_brand} WHERE site_id=%d".$where, $this->import["module_name"], $this->import["site_id"], "id");
			if($ids)
			{
				DB::query("UPDATE {%s_brand} SET [act]='%d' WHERE id IN(%s)", $this->import["module_name"], $act ? 1 : 0, implode(",", $ids));
			}
		}

		$this->diafan->_cache->delete("", $this->import["module_name"]);

		return true;
	}
}
