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
 * Search_admin_inc
 */
class Search_admin_inc extends Diafan
{
	/**
	 * Добавление элемента в поисковый индекс.
	 * 
	 * @return void
	 */
	public function save()
	{
		if(! $this->diafan->configmodules("auto_index", "search"))
		{
			return;
		}
		if(! $config = $this->diafan->_search->check_module_config($this->diafan->_admin->module, $this->diafan->table))
		{
			return;
		}
		$row = $_POST;
		// элемент не новый
		if($this->diafan->is_new)
		{
			$row["id"] = $this->diafan->id;
			$edit = true;
		}
		else
		{
			$edit = false;
			$config["fields"][] = 'act';
			if($this->diafan->is_variable('search_no_show'))
			{
				$config["fields"][] = 'search_no_show';
			}
			if($this->diafan->is_variable('date_period'))
			{
				$this->diafan->get_date_period();
				$config["fields"][] = 'date_start';
				$row['date_start'] = $this->diafan->unixdate($row['date_start']);
				$config["fields"][] = 'date_finish';
				$row['date_finish'] = $this->diafan->unixdate($row['date_finish']);
			}
			foreach($config["fields"] as $field)
			{
				if(empty($row[$field]))
				{
					$row[$field] = '';
				}
				if($row[$field] != $this->diafan->values($field))
				{
					$edit = true;
				}
				if($this->diafan->variable_multilang($field))
				{
					foreach($this->diafan->_languages->all as $l)
					{
						if($l["id"] != _LANG)
						{
							$row[$field.$l["id"]] = $this->diafan->values($field.$l["id"]);
						}
					}
				}
			}
			if(! $edit)
			{
				return;
			}
		}
		$this->diafan->_search->index_element($row, $this->diafan->table);
	}

	/**
	 * Удаление поискового индекса для выбранных модулей
	 * 
	 * @param array $element_ids номера элементов
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @param integer $trash_id номер записи в корзине, с которой связано удаление
	 * @return void
	 */
	public function delete($element_ids, $module_name, $element_type, $trash_id = 0)
	{
		if(! $this->diafan->configmodules("auto_index", "search"))
		{
			return;
		}
		$table_name = $this->diafan->table_element_type($module_name, $element_type);
		$this->diafan->_search->delete($element_ids, $module_name);
	}

	/**
	 * Создание/удаление поискового индекса при активации/деактивации/удалении элементов
	 *
	 * @param string $table_name таблица
	 * @param array $element_ids номера элементов
	 * @param boolean $act активация/деактивация
	 * @return void
	 */
	public function act($table_name, $element_ids, $act)
	{
		if(! $this->diafan->configmodules("auto_index", "search"))
		{
			return;
		}
		// только при активации/деактивации из  списка, при сохранении индексируем функцией save
		if($this->diafan->is_action("save"))
		{
			return;
		}

		if(! $this->diafan->_search->check_module_config('', $table_name))
		{
			return;
		}
		
		if($act)
		{
			$rows = DB::query_fetch_all("SELECT * FROM {%s} WHERE id IN (%s) AND trash='0'", $table_name, implode(",", $element_ids));
			if($rows)
			{
				$this->diafan->_search->index_elements($rows, $table_name);

				// индексирует модуль, подключенный к странице сайта
				if($table_name == 'site')
				{
					foreach($rows as $row)
					{
						$this->diafan->_search->index_site_module($row);
					}
				}
			}
		}
		else
		{
			$this->diafan->_search->delete($element_ids, $table_name);

			// удаляет индекс модуля, подключенного к странице сайта
			if($table_name == 'site')
			{
				$this->diafan->_search->delete_sites($element_ids);
			}
		}
	}

	/**
	 * Восстанавливает из корзины различные элементы модуля
	 * 
	 * @param string $table_name таблица
	 * @param integer $id номер элемента
	 * @return void
	 */
	public function restore_from_trash($table_name, $id)
	{
		if(! $this->diafan->configmodules("auto_index", "search"))
		{
			return;
		}
		if(! $this->diafan->_search->check_module_config('', $table_name))
		{
			return;
		}
		$row = DB::query_fetch_all("SELECT * FROM {%s} WHERE id=%d", $table_name, $id);
		$this->diafan->_search->index_element($row, $table_name);
	}
}