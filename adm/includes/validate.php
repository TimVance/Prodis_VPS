<?php
/**
 * @package    DIAFAN.CMS
 *
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
 * Validate_admin
 *
 * Валидация данных перед сохранением
 */
class Validate_admin extends Diafan
{
	/**
	 * @var Validate_functions_admin функции валидации полей
	 */
	public $_functions;

	/**
	 * @var array массив результатов валидации
	 */
	public $result;

	/**
	 * Вызывает функции валидации полей
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		if(! $this->_functions)
		{
			Custom::inc("adm/includes/validate_functions.php");
			$this->_functions = new Validate_functions_admin($this->diafan);
		}

		if (is_callable(array(&$this->_functions, $name)))
		{
			return call_user_func_array(array(&$this->_functions, $name), $arguments);
		}
		else
		{
			return 'fail_function';
		}
	}

	/**
	 * Проверяет данные
	 *
	 * @return void
	 */
	public function validate()
	{
		// Проверка прав на сохранение
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["errors"][0] = $this->diafan->_('Нет прав на сохранение.');
			return $this->result();
		}
		if (! $this->diafan->_users->checked)
		{
			$this->result["errors"][0] = $this->diafan->_('Идентификационный хэш не прошел проверку. Обновите страницу.');
			return $this->result();
		}
		$this->result["hash"] = $this->diafan->_users->get_hash();

		if($this->diafan->_route->addnew)
		{
			$this->diafan->is_new = true;
		}
		else
		{
			$this->diafan->id = $this->diafan->_route->edit;
		}

		Custom::inc('includes/validate.php');
		foreach ($this->diafan->variables as $title => $variable_table)
		{
			foreach ($variable_table as $key => $type_value)
			{
				if(is_array($type_value))
				{
					if(! empty($type_value["disabled"]))
					{
						continue;
					}
					$type_value = $type_value["type"];
				}
				else
				{
					$type_value = $type_value;
				}
	
				$func = 'validate'. ( $this->diafan->config("config") ? '_config' : '' ).'_variable_'.str_replace('-', '_', $key);
				if (call_user_func_array (array(&$this->diafan, $func), array()) !== 'fail_function')
				{
					continue;
				}
				$this->diafan->validate_variable($key, $type_value);
			}
		}

		if(empty($this->result["errors"]))
		{
			$this->result["result"] = "success";
		}
		return $this->result();
	}

	/**
	 * Выводит результат обработки данных
	 *
	 * @return void
	 */
	private function result()
	{
		Custom::inc('plugins/json.php');
		echo to_json($this->result);
		exit;
	}

	/**
	 * Подготавливает новые значения для сохранения
	 *
	 * @return boolean true
	 */
	public function validate_variable($key, $type)
	{
		if(empty($_POST[$key]))
			return;

		switch($type)
		{
			case 'module':
				if (Custom::exists('modules/'.$key.'/admin/'.$key.'.admin.inc.php'))
				{
					Custom::inc('modules/'.$key.'/admin/'.$key.'.admin.inc.php');
					$func = 'validate'.( $this->diafan->config("config") ? '_config' : '' );
					$class = ucfirst($key).'_admin_inc';
					if (method_exists($class, $func))
					{
						$module_class = new $class($this->diafan);
						call_user_func_array (array(&$module_class, $func), array());
					}
				}
				break;

			case 'password':
				$this->diafan->set_error($key, Validate::password($_POST[$key]));
				break;

			case 'email':
				$this->diafan->set_error($key, Validate::mail($_POST[$key]));
				break;

			case 'url':
				if(! Validate::url($_POST[$key], true))
				{
					$this->diafan->set_error($key, 'Некорректная ссылка.');
				}
				break;

			case 'date':
				if(! empty($_POST[$key]) && preg_replace('/[^0-9]+/', '', $_POST[$key]))
				{
					$this->diafan->set_error($key, Validate::date($_POST[$key]));
				}
				break;

			case 'datetime':
				if(! empty($_POST[$key]) && preg_replace('/[^0-9]+/', '', $_POST[$key]))
				{
					$this->diafan->set_error($key, Validate::datetime($_POST[$key]));
				}
				break;

			case 'floattext':
				$this->diafan->set_error($key, Validate::floattext($_POST[$key]));
				break;

			case 'numtext':
				$this->diafan->set_error($key, Validate::numtext($_POST[$key]));
				break;

			case 'editor':
				if (! empty($_POST[$key."_typograf"]) && strlen($_POST[$key]) > 32768)
				{
					$this->diafan->set_error($key, 'Типограф можно применить только для текстам размером меньше 32Kb.');
				}
				break;
		}
	}

	/**
	 * Запоминает найденную ошибку
	 *
	 * @return void
	 */
	public function set_error($key, $value)
	{
		if($value)
		{
			$this->result["errors"][$key] = $this->diafan->_($value);
		}
	}

	/**
	 * Получает значения полей для формы (альтернативный метод)
	 *
	 * @return array
	 */
	public function get_values()
	{
		return array ();
	}

	/**
	 * Получает значение поля
	 * @param string $field название поля
	 * @param mixed $default значение по умолчанию
	 * @param boolean $save записать значение по умолчанию
	 * @return mixed
	 */
	public function values($field, $default = false, $save = false)
	{
		if(! isset($this->cache["oldrow"]))
		{
			$values = $this->diafan->get_values();
	
			if ($this->diafan->config("config"))
			{
				foreach ($this->diafan->variables as $title => $variable_table)
				{
					foreach ($variable_table as $k => $v)
					{
						if ( empty($values[$k]))
						{
							$values[$k] = $this->diafan->configmodules($k);
						}
					}
				}
			}
			elseif($this->diafan->is_new)
			{
				foreach ($this->diafan->variables as $title => $variable_table)
				{
					foreach ($variable_table as $k => $v)
					{
						if ( ! empty($this->diafan->get_nav_params[$k]))
						{
							$values[$k] = $this->diafan->get_nav_params[$k.(! empty($v["multilang"]) ? _LANG : '')];
						}
						if($k == 'act')
						{
							$values[$k.(! empty($v["multilang"]) ? _LANG : '')] = true;
						}
					}
				}
			}
			elseif (! $values)
			{
				$values = DB::query_fetch_array("SELECT * FROM {".$this->diafan->table."} WHERE id=%d"
					.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '' )." LIMIT 1",
					$this->diafan->id
				);
				if (empty($values))
				{
					Custom::inc('includes/404.php');
				}
			}
			$this->cache["oldrow"] = $values;
		}

		$field .= ($this->diafan->variable_multilang($field) ? _LANG : '');

		if(! isset($this->cache["oldrow"][$field]))
		{
			switch($field)
			{
				case 'parent_id':
					if ($this->diafan->is_new)
					{
						$this->cache["oldrow"]["parent_id"] = $this->diafan->_route->parent;
					}
					break;
	
				case 'cat_id':
					if ($this->diafan->is_new)
					{
						$this->cache["oldrow"]["cat_id"] = $this->diafan->_route->cat;
					}
					break;
	
				case 'site_id':
					if($this->diafan->table == 'site')
					{
						$this->cache["oldrow"]["site_id"] = $this->diafan->id;
					}
					else
					{
						if(empty($this->cache["oldrow"]["site_id"]))
						{
							$this->cache["oldrow"]["site_id"] = $this->diafan->_route->site;
						}
						if(empty($this->cache["oldrow"]["site_id"]))
						{
							$this->cache["oldrow"]["site_id"] = DB::query_result("SELECT id FROM {site} WHERE module_name='%s' AND trash='0'", $this->diafan->_admin->module);
						}
					}
					break;
			}
		}
		if(! isset($this->cache["oldrow"][$field]))
		{
			if($default)
			{
				if($save)
				{
					$this->cache["oldrow"][$field] = $default;
				}
				else
				{
					return $default;
				}
			}
			elseif ($this->diafan->config("config"))
			{
				$this->cache["oldrow"][$field] = $this->diafan->configmodules($field);
			}
		}
		if(isset($this->cache["oldrow"][$field]))
		{
			return $this->cache["oldrow"][$field];
		}
		else
		{
			return false;
		}
		return $this->cache["site_id"];
	}
}