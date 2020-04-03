<?php
/**
 * Подключение модуля к административной части других модулей
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
 * Map_admin_inc
 */
class Map_admin_inc extends Diafan
{
	/**
	 * Добавление элемента в поисковый индекс.
	 * 
	 * @return void
	 */
	public function save()
	{
		// если есть поле "Не показывать на карте сайта" и оно отмечено
		if ($this->diafan->is_variable('map_no_show') && ! empty($_POST["map_no_show"]))
		{
			// и элемент проиденксирован (не новые и поле не было отмечено раньше)
			if(! $this->diafan->is_new && ! $this->diafan->values("map_no_show"))
			{
				// удаляем из индекса
				$this->diafan->_map->delete($this->diafan->id, $this->diafan->table);
			}
			return;
		}

		if(! Custom::exists('modules/'.$this->diafan->_admin->module.'/'.$this->diafan->_admin->module.'.sitemap.php'))
			return;

		Custom::inc('modules/'.$this->diafan->_admin->module.'/'.$this->diafan->_admin->module.'.sitemap.php');
		$class_name = ucfirst($this->diafan->_admin->module).'_sitemap';
		$class = new $class_name($this->diafan);
		if(! is_callable(array(&$class, 'config')))
			return;

		$config = call_user_func_array(array(&$class, 'config'), array($this->diafan->get_site_id()));

		if(! is_array($config))
			return;

		if(empty($config["type"]))
			return;

		if(! in_array($this->diafan->element_type(), $config["type"]))
			return;

		if ($this->diafan->is_variable('rewrite') && ! $this->diafan->is_save_rewrite)
		{
			$this->diafan->get_rewrite();
		}

		$row = array(
				"module_name" => $this->diafan->_admin->module,
				"id"    => $this->diafan->id,
				"site_id" => $this->diafan->get_site_id(),
				"changefreq" => ! empty($_POST["changefreq"]) ? $_POST["changefreq"] : '',
				"priority" => ! empty($_POST["priority"]) ? $_POST["priority"] : '',
				"is_save" => true,
				"element_type" => $this->diafan->element_type(),
			);
		if(! empty($_POST["cat_id"]))
		{
			$row["cat_id"] = $_POST["cat_id"];
		}
		if($this->diafan->is_variable('act'))
		{
			if($this->diafan->variable_multilang('act'))
			{
				foreach ($this->diafan->_languages->all as $l)
				{
					if($l["id"] == _LANG)
					{
						$row["act".$l["id"]] = ! empty($_POST["act"]) ? 1 : 0;
					}
					else
					{
						$row["act".$l["id"]] = $this->diafan->values("act".$l["id"]) ? 1 : 0;
					}
				}
			}
			else
			{
				$row["act"] = ! empty($_POST["act"]) ? 1 : 0;
			}
		}
		if($this->diafan->is_variable('date_period'))
		{
			$this->diafan->get_date_period();
			$row["date_start"] = $this->diafan->unixdate($_POST['date_start']);
			$row["date_finish"] = $this->diafan->unixdate($_POST['date_finish']);
		}
		$this->diafan->_map->index_element($row);
		// если изменен ЧПУ у родителя, то у дочерних элементов тоже поменяется ссылка
	}

	/**
	 * Удаление индекса для выбранных модулей
	 * 
	 * @param array $element_ids номера элементов
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @return void
	 */
	public function delete($element_ids, $module_name, $element_type)
	{
		$table_name = $this->diafan->table_element_type($module_name, $element_type);
		$this->diafan->del_or_trash_where("map_index", "module_name='".$table_name."' AND element_type='".$element_type."' AND element_id IN (".implode(",", $element_ids).")");
	}

	/**
	* Создание/удаление индекса карты сайта при активации/деактивации/удалении элементов
	* 
	* @param array $table_name таблица
	* @param array $element_ids номера элементов
	* @param boolean $act активация/деактивация
	* @return void
	*/
	public function act($table_name, $element_ids, $act)
	{
		// только при активации/деактивации из  списка, при сохранении индексируем функцией save
		if($this->diafan->is_action("save"))
		{
			return;
		}

		$module_name = $this->diafan->_admin->module;
		$element_type = $this->diafan->element_type();

		if (! Custom::exists('modules/'.$module_name.'/'.$module_name.'.sitemap.php'))
		{
			return;
		}

		if($act)
		{
			if($element_ids)
			{
				Custom::inc('modules/'.$module_name.'/'.$module_name.'.sitemap.php');
				$class_name = ucfirst($module_name).'_sitemap';
				$class = new $class_name($this->diafan);
				if(is_callable(array(&$class, 'config')))
				{
					if($this->diafan->config('element_site'))
					{
						if($this->diafan->_route->site)
						{
							$site_ids = array($this->diafan->_route->site);
						}
						else
						{
							$site_result = DB::query_result("SELECT GROUP_CONCAT(DISTINCT(site_id), ',') FROM {%s} WHERE id IN (%s)", $table_name, implode(",", $element_ids));
							$site_ids = explode(',', $site_result);
						}
					}
					else
					{
						$site_ids = array(0);
					}
					foreach ($site_ids as $site_id)
					{
						$config = call_user_func_array(array(&$class, 'config'), array($site_id));
						if(empty($config["type"]) || ! in_array($element_type, $config["type"]))
						{
							continue;
						}

						$where =  ! empty($config['where'][$element_type]) ? $config['where'][$element_type] : '';

						$rows = DB::query_fetch_all("SELECT * FROM {%s} WHERE id IN (%s) ".$where, $table_name, implode(",", $element_ids));
						if($rows)
						{
							foreach ($rows as &$row)
							{
								// индексирует модуль, подключенный к странице сайта
								if($module_name == 'site' && $element_type == 'element')
								{
									$this->diafan->_map->index_site_module($row);
								}
								$row["module_name"] = $module_name;
								$row["site_id"] = ! empty($row["site_id"]) ? $row["site_id"] : 0;
								$row["element_type"] = $element_type;
							}
							$this->diafan->_map->index_elements($rows);
						}
					}
				}
			}
		}
		else
		{
			$this->diafan->_map->delete($element_ids, $module_name, $element_type);
			// удаляет индекс модуля, подключенного к странице сайта
			if($module_name == 'site' && $element_type == 'element')
			{
				$this->diafan->_map->delete_sites($element_ids);
			}
		}
	}

	/**
	 * Восстанавливает из корзины различные элементы модуля
	 * 
	 * @param string $table_name таблица
	 * @param array $id номер элемента
	 * @return void
	 */
	public function restore_from_trash($table_name, $id)
	{
		//todo
	}

	/**
	 * Редактирование поля "Подключить комментарии" для настроек модуля
	 * 
	 * @return void
	 */
	public function edit_config()
	{
		if($this->diafan->configmodules("module_".$this->diafan->_admin->module."_index", "map"))
		{
			$this->diafan->_map->index_module($this->diafan->_admin->module);
		}
	}

	/**
	 * Сохранение настроек конфигурации модулей
	 * 
	 * @return void
	 */
	public function save_config()
	{
		$fields = array('page_show', 'cat');

		foreach ($fields as $field)
		{
			$value = ! empty($_POST[$field]) ? $_POST[$field] : 0;
			if($this->diafan->configmodules($field, $this->diafan->_admin->module, $this->diafan->_route->site, _LANG) != $value)
			{
				$this->diafan->configmodules($field, $this->diafan->_admin->module, $this->diafan->_route->site, _LANG, $value);
				$edit = true;
			}
		}
		if(! empty($edit))
		{
			$this->diafan->configmodules("module_".$this->diafan->_admin->module."_index", "map", 0, _LANG, true);
		}
	}
}