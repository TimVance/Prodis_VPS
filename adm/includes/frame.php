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
 * Frame_admin
 *
 * Каркас административной части
 */
class Frame_admin extends Diafan
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table;

	/**
	 * @var string тип элементов
	 */
	public $element_type;

	/**
	 * @var array настройки отображения
	 */
	public $config = array();

	/**
	 * @var array категории
	 */
	public $categories;

	/**
	 * @var boolean существуют категории
	 */
	public $not_empty_categories;

	/**
	 * @var array разделы сайта, к которым прикреплен модуль
	 */
	public $sites;

	/**
	 * @var boolean разделов сайта больше одного
	 */
	public $not_empty_site;

	/**
	 * @var array поля таблицы
	 */
	public $variables = array();

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array();

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array();

	/**
	 * @var string SQL-условия для списка
	 */
	public $where;

	/**
	 * @var string часть SQL-запроса - соединение с таблицей
	 */
	public $join;

	/**
	* @var string часть SQL-запроса - дополнительные столбцы
	*/
	public $fields;

	/**
	 * @var string контент, сформированный модулем
	 */
	public $module_contents;

	/**
	 * @var array параметры, переданные методом GET
	 */
	public $get_nav_params;

	/**
	 * @var string параметры, переданные методом GET
	 */
	public $get_nav;

	/**
	 * @var integer общее количество элементов
	 */
	public $count;

	/**
	 * @var integer идентификатор текущего элемента
	 */
	public $id;

	/**
	 * @var boolean добавление нового элемента
	 */
	public $is_new;

	/**
	 * @var object экземпляр класса действия
	 */
	private $action_object;

	/**
	 * @var object экземпляр класса представления
	 */
	private $_theme;

	/**
	 * Возвращает переменные, определенные в файлах действий
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->action_object->$name;
	}

	/**
	 * Вызывает методы, определенные в файлах действий
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		if (is_callable(array(&$this->action_object, $name)))
		{
			return call_user_func_array(array(&$this->action_object, $name), $arguments);
		}
		if (is_callable(array(&$this->_theme, $name)))
		{
			return call_user_func_array(array(&$this->_theme, $name), $arguments);
		}
		return 'fail_function';
	}

	/**
	 * Инициализация модуля
	 *
	 * @return void
	 */
	public function init()
	{
		Custom::inc("adm/includes/theme.php");

		$this->diafan->prepare_config();
		$this->legacy();

		if(empty($_POST["id"]) && ! empty($_POST["group_action"]))
		{
			$_SESSION["group_action"][$this->diafan->_admin->rewrite] = $_POST["group_action"];
		}
		if (! empty($_POST["action"]))
		{
			$this->diafan->set_get_nav();
			if(! empty($_POST["module"]))
			{
				Custom::inc("adm/includes/action.php");
				$this->action_object = new Action_admin($this->diafan);
				$this->action_object->init();
			}
			else
			{
				switch ($_POST["action"])
				{
					case 'save':
						Custom::inc("adm/includes/save.php");
						$this->action_object = new Save_admin($this->diafan);
						$this->diafan->save();
						return;

					case 'validate':
						Custom::inc("adm/includes/validate.php");
						$this->action_object = new Validate_admin($this->diafan);
						$this->validate();
						return;

					case 'trash':
					case 'delete':
						Custom::inc("adm/includes/del.php");
						$this->action_object = new Del_admin($this->diafan);
						$this->diafan->del();
						return;

					case 'restore':
						Custom::inc("adm/includes/del.php");
						$this->action_object = new Del_admin($this->diafan);
						$this->diafan->restore();
						return;

					case 'unblock':
					case 'block':
						Custom::inc("adm/includes/act.php");
						$this->action_object = new Act_admin($this->diafan);
						$this->act();
						return;

					case 'move':
						Custom::inc("adm/includes/move.php");
						$this->action_object = new Move_admin($this->diafan);
						$this->move();
						return;

					case 'move_parent':
						Custom::inc("adm/includes/move.php");
						$this->action_object = new Move_admin($this->diafan);
						$this->move_parent();
						return;

					case 'move_page':
						Custom::inc("adm/includes/move.php");
						$this->action_object = new Move_admin($this->diafan);
						$this->move_page();
						return;

					case 'show_rel_elements':
                    case 'show_rel_goods':
						$this->_theme = new Theme_admin($this->diafan);

					case 'rel_elements':
					case 'delete_rel_element':
						Custom::inc("adm/includes/rel_elements.php");
						$this->action_object = new Rel_elements_admin($this->diafan);
						$this->ajax();

                    case 'rel_goods':
                    case 'delete_rel_good':
                        Custom::inc("adm/includes/rel_goods.php");
                        $this->action_object = new Rel_goods_admin($this->diafan);
                        $this->ajax();

					case 'auth':
						break;

					default:
						Custom::inc("adm/includes/action.php");
						$this->action_object = new Action_admin($this->diafan);
						$this->action_object->init();
				}
			}
		}

		$this->_theme = new Theme_admin($this->diafan);

		// если конфигурация, то открывается форма редактирования
		$this->diafan->config("only_edit", $this->diafan->config("config") || $this->diafan->config("only_edit"));

		if ($this->diafan->is_action("edit"))
		{
			Custom::inc("adm/includes/edit.php");
			$this->action_object = new Edit_admin($this->diafan);
			$this->diafan->set_get_nav();
			ob_start();
			$this->edit();
			$this->module_contents = ob_get_contents();
			ob_end_clean();
		}
		else
		{
			Custom::inc("adm/includes/show.php");
			$this->action_object = new Show_admin($this->diafan);
			$this->diafan->set_get_nav();
			if($this->diafan->_users->id)
			{
				if (! empty($_POST['ajax']) && $_POST['ajax'] == 'expand')
				{
					$this->ajax_expand();
				}
				ob_start();
				$this->prepare_variables();
				$this->show();
				$this->module_contents = ob_get_contents();
				ob_end_clean();
			}
		}
		if(in_array('visitors', $this->diafan->installed_modules))
		{
			$this->diafan->_visitors->counter_init();
		}
		$this->show_theme();
	}

	/**
	 * Поддержка старого синтаксиса
	 * @return void
	 */
	private function legacy()
	{
		if(! empty($this->text_for_base_link))
		{
			if(isset($this->text_for_base_link["text"]))
			{
				$this->variable_list('name', 'text', $this->text_for_base_link["text"]);
			}
			if(isset($this->text_for_base_link["variable"]))
			{
				$this->variable_list('name', 'variable', $this->text_for_base_link["variable"]);
				if($this->text_for_base_link["variable"])
				{
					$this->variable_list($this->text_for_base_link["variable"], 'sql', true);
					if($this->text_for_base_link["variable"] != 'name')
					{
						$this->variable_list($this->text_for_base_link["variable"], 'type', 'none');
					}
				}
			}
		}
		if($this->variable_list('name'))
		{
			if(! $this->variable_list('name', 'variable') && ! $this->variable_list('name', 'text'))
			{
				$this->variable_list('name', 'sql', true);
			}
			if($this->variable_list('name', 'variable'))
			{
				$this->variable_list($this->variable_list('name', 'variable'), 'sql', true);
				if($this->variable_list('name', 'variable') != 'name')
				{
					$this->variable_list($this->variable_list('name', 'variable'), 'type', 'none');
				}
			}
		}
		if(! empty($this->select_arr))
		{
			foreach($this->select_arr as $k => $arr)
			{
				if($this->variable($k, 'type') == 'select')
				{
					$this->variable($k, 'select', $arr);
				}
			}
		}
		if(! empty($this->select))
		{
			foreach($this->select as $k => $arr)
			{
				if($this->variable($k, 'type') == 'select')
				{
					$newarr = array();
					if(! empty($arr[0]))
					{
						$newarr["table"] = $arr[0];
					}
					if(! empty($arr[1]))
					{
						$newarr["id"] = $arr[1];
					}
					if(! empty($arr[2]))
					{
						$newarr["name"] = $arr[2];
					}
					if(! empty($arr[4]))
					{
						$newarr["empty"] = $arr[4];
					}
					if(! empty($arr[5]))
					{
						$newarr["where"] = $arr[5];
					}
					if(! empty($arr[6]))
					{
						$newarr["hierarchy"] = true;
					}
					if(! empty($arr[7]))
					{
						$newarr["order"] = $arr[7];
					}
					$this->variable($k, 'select_db', $newarr);
				}
			}
		}
		if(! empty($this->show_tr_click_checkbox))
		{
			foreach($this->show_tr_click_checkbox as $k => $arr)
			{
				foreach($arr as $v)
				{
					$d = $this->diafan->variable($v, 'depend');
					$this->diafan->variable($v, 'depend', ($d ? $d.',' : '').$k);
				}
			}
		}
	}

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config(){}

	/**
	 * Возвращает, назначает информацию о поле
	 *
	 * @param string $key название переменной
	 * @param string $type_info тип информации
	 * @param mixed $value
	 * @return mixed
	 */
	public function variable($key = '', $type_info = 'type', $value = NULL)
	{
		$key = $key ? $key : $this->diafan->key;

		foreach ($this->variables as $title => $arr)
		{
			if (! empty($this->variables[$title][$key]))
			{
				$res = $arr[$key];
				if(is_array($res))
				{
					if($value !== NULL)
					{
						$this->variables[$title][$key][$type_info] = $value;
						return;
					}
					return ! empty($res[$type_info]) ? $res[$type_info] : false;
				}
				else
				{
					if($value !== NULL)
					{
						$this->variables[$title][$key]['type'] = $value;
						return;
					}
					if($type_info == 'type')
					{
						return $res;
					}
					else
					{
						return false;
					}
				}
				break;
			}
		}
		return NULL;
	}

	/**
	 * Удаляет переменную из списка полей
	 *
	 * @param string $key название переменной
	 * @return void
	 */
	public function variable_unset($key)
	{
		foreach ($this->variables as $title => $arr)
		{
			if (! empty($this->variables[$title][$key]))
			{
				unset($this->variables[$title][$key]);
			}
		}
	}

	/**
	 * Возвращает название поля
	 *
	 * @param string $key название переменной
	 * @return string
	 */
	public function variable_name($key = '')
	{
		$key = $key ? $key : $this->diafan->key;

		foreach ($this->variables as $title => $arr)
		{
			if (! empty($this->variables[$title][$key]))
			{
				$res = $arr[$key];
				if(is_array($res) && ! empty($res["name"]))
				{
					return $this->diafan->_($res["name"]);
				}
				break;
			}
		}
		return '';
	}

	/**
	 * Определяет является ли поле мультиязычным
	 *
	 * @param string $key название переменной
	 * @return boolean
	 */
	public function variable_multilang($key = '')
	{
		if(empty($this->variables))
		{
			return false;
		}
		$key = ($key ? $key : $this->diafan->key);
		foreach ($this->variables as $title => $arr)
		{
			if (! empty($arr[$key]))
			{
				$res = $arr[$key];
				if(is_array($res) && ! empty($res["multilang"]))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		return false;
	}

	/**
	 * Возвращает, назначает атрибут disabled для переменной
	 *
	 * @param string $key название переменной
	 * @param boolean $value значение атрибута
	 * @return boolean
	 */
	public function variable_disabled($key = '', $value = NULL)
	{
		if(empty($this->variables))
		{
			return false;
		}
		$key = ($key ? $key : $this->diafan->key);
		foreach ($this->variables as $title => $arr)
		{
			if (! empty($arr[$key]))
			{
				$res = $arr[$key];
				if(is_array($res) && ! empty($res["disabled"]))
				{
					if($value === false)
					{
						$this->variables[$title][$key]['disabled'] = false;
					}
					return true;
				}
				else
				{
					if($value === true)
					{
						$this->variables[$title][$key]['disabled'] = true;
					}
					return false;
				}
			}
		}
		return false;
	}

	/**
	 * Возвращает определена ли переменная в списке полей
	 *
	 * @param string $key название переменной
	 * @return boolean
	 */
	public function is_variable($key)
	{
		if(! empty($this->variables))
		{
			foreach ($this->variables as $title => $arr)
			{
				if (! empty($this->variables[$title][$key]))
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Возвращает определена ли переменная в списке полей
	 *
	 * @param string $key название переменной
	 * @return boolean
	 */
	public function is_variable_list($key)
	{
		if(! empty($this->variables_list))
		{
			if (isset($this->variables_list[$key]))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Возвращает, назначает информацию о поле в списке
	 *
	 * @param string $key название переменной
	 * @param string $type_info тип информации
	 * @param mixed $value
	 * @return mixed
	 */
	public function variable_list($key = '', $type_info = '', $value = NULL)
	{
		if (isset($this->variables_list[$key]))
		{
			if(! $type_info)
			{
				return true;
			}
			$res = $this->variables_list[$key];
		}
		if($value !== NULL)
		{
			$this->variables_list[$key][$type_info] = $value;
			return;
		}
		return ! empty($res[$type_info]) ? $res[$type_info] : false;
	}

	/**
	 * Удаляет переменную из списка полей
	 *
	 * @param string $key название переменной
	 * @return void
	 */
	public function variable_list_unset($key)
	{
		foreach ($this->variables_list as $title => $arr)
		{
			if (! empty($this->variables_list[$title][$key]))
			{
				unset($this->variables_list[$title][$key]);
			}
		}
	}

	/**
	 * Возвращает, назначает информацию о поле в фильтре
	 *
	 * @param string $key название переменной
	 * @param string $type_info тип информации
	 * @param mixed $value
	 * @return mixed
	 */
	public function variable_filter($key = '', $type_info = '', $value = NULL)
	{
		if (isset($this->variables_filter[$key]))
		{
			if(! $type_info)
			{
				return true;
			}
			$res = $this->variables_filter[$key];
		}

		if($value !== NULL)
		{
			$this->variables_filter[$key][$type_info] = $value;
			return;
		}
		return ! empty($res[$type_info]) ? $res[$type_info] : false;
	}

	/**
	 * Определяет включена ли настройка отображения модуля или включает/выключает настройку
	 *
	 * @param string $name название настройки
	 * @param boolean $value настройка включена/выключена
	 * @return boolean
	 */
	public function config($key, $value = NULL)
	{
		if($value !== NULL)
		{
			if($value)
			{
				$this->config[] = $key;
			}
			elseif (in_array($key, $this->config))
			{
				unset($this->config[array_search($key, $this->config)]);
			}
			return true;
		}
		if (in_array($key, $this->config))
		{
			return true;
		}
		return false;
	}

	/**
	 * Определяет тип редактируемых элементов
	 *
	 * @return string
	 */
	public function element_type()
	{
		if($this->diafan->element_type)
		{
			return $this->diafan->element_type;
		}
		if($this->diafan->config('category') && $this->diafan->table == $this->diafan->_admin->module.'_category')
		{
			return 'cat';
		}
		if($this->diafan->table == $this->diafan->_admin->module)
		{
			return 'element';
		}
		else
		{
			return str_replace($this->diafan->_admin->module.'_', '', $this->diafan->table);
		}
	}

	/**
	 * Определяет таблицу по типу элемента
	 *
	 * @param string $module_name модуль
	 * @param string $element_type тип элемента
	 * @return string
	 */
	public function table_element_type($module_name, $element_type)
	{
		$table_name = $module_name;
		switch($element_type)
		{
			case 'cat':
				$table_name .= '_category';
				break;

			case 'element':
				if($this->diafan->element_type == 'element')
				{
					$table_name = $this->diafan->table;
				}
				break;

			case 'param':
				$table_name .= '_param_select';
				break;
			default:
				$table_name .= '_'.$element_type;
		}
		return $table_name;
	}

	/**
	 * Определяет строку с GET переменными
	 *
	 * @return void
	 */
	public function set_get_nav()
	{
		$get_nav_params = $this->diafan->get_nav_params;

		foreach($this->diafan->variables_filter as $name => $row)
		{
			$func = 'save_filter_variable_'.preg_replace('/[^a-z_]+/', '', $name);
			$result = call_user_func_array (array(&$this->diafan, $func), array($row));
			if ($result !== 'fail_function')
			{
				$get_nav_params["filter_".$name] = $result;
				continue;
			}

			switch($row["type"])
			{
				case 'text':
				case 'none':
					$get_nav_params["filter_".$name] = $this->diafan->filter($_GET, "string", "filter_".$name);
					if($get_nav_params["filter_".$name])
					{
						$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_'.$name.'='.$this->diafan->filter($_GET, "url", "filter_".$name);
						$this->diafan->where .= " AND e.".($this->diafan->variable_multilang($name) ? "[".$name."]" : $name)." LIKE '%%".$this->diafan->filter($_GET, "sql", "filter_".$name)."%%'";
					}
					break;

				case 'numtext':
					$get_nav_params["filter_".$name] = $this->diafan->filter($_GET, "integer", "filter_".$name);
					if($get_nav_params["filter_".$name])
					{
						$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_'.$name.'='.$get_nav_params["filter_".$name];
						$this->diafan->where .= " AND e.".($this->diafan->variable_multilang($name) ? "[".$name."]" : $name)."=".$get_nav_params["filter_".$name];
					}
					break;

				case 'date':
					$get_nav_params["filter_".$name] = $this->diafan->unixdate($_GET["filter_".$name]);
					if($get_nav_params["filter_".$name])
					{
						$this->diafan->where .= " AND e.created>=".$get_nav_params["filter_".$name]."  AND e.created<".($get_nav_params["filter_".$name] + 86400);
						$get_nav_params["filter_"].$name = date("d.m.Y", $get_nav_params["filter_".$name]);
						$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_'.$name.'='.$get_nav_params["filter_".$name];
					}
					break;

				case 'checkbox':
					if(! empty($_GET["filter_".$name]))
					{
						$get_nav_params["filter_".$name] = 1;
						$this->diafan->where .= " AND e.".$name."='1'";
						$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_'.$name.'=1';
					}
					break;

				case 'radio':
				case 'select':
					$get_nav_params["filter_".$name] = $this->diafan->filter($_GET, "string", "filter_".$name);
					if($get_nav_params["filter_".$name])
					{
						$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_'.$name.'='.$this->diafan->filter($_GET, "url", 'filter_'.$name);
						$this->diafan->where .= " AND e.".($this->diafan->variable_multilang($name) ? "[".$name."]" : $name)."='".$this->diafan->filter($_GET, "sql", 'filter_'.$name)."'";
					}
					break;

				case 'multiselect':
					if(! empty($_GET["filter_".$name]) && is_array($_GET["filter_".$name]))
					{
						$value = array();
						foreach($_GET["filter_".$name] as $v)
						{
							$value_url[] = $this->diafan->filter($v, "url");
							$v = $this->diafan->filter($v, "sql");
							if($v)
							{
								$value[] = $v;
							}
						}
						if(! $v)
						{
							break;
						}
						$get_nav_params["filter_".$name] = $value;
						$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_'.$name.'[]='.implode('&amp;filter_'.$name.'[]=', $value_url);
						$this->diafan->where .= " AND (e.".($this->diafan->variable_multilang($name) ? "[".$name."]" : $name)."='".implode("' OR e.".($this->diafan->variable_multilang($name) ? "[".$name."]" : $name)."='", $value)."')";
					}
					break;

				case 'numtext_interval':
					$get_nav_params["filter_start_".$name] = $this->diafan->filter($_GET, "integer", "filter_start_".$name);
					if ($get_nav_params["filter_start_".$name])
					{
						$this->diafan->where .= " AND e.".$name.">=".$get_nav_params["filter_start_".$name];
						$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_start_'.$name.'='.$get_nav_params["filter_start_".$name];
					}
					else
					{
						$get_nav_params["filter_start_".$name] = '';
					}
					$get_nav_params["filter_finish_".$name] = $this->diafan->filter($_GET, "integer", "filter_finish_".$name);
					if ($get_nav_params["filter_finish_".$name])
					{
						$this->diafan->where .= " AND e.".$name."<=".$get_nav_params["filter_finish_".$name];
						$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_finish_'.$name.'='.$get_nav_params["filter_finish_".$name];
					}
					else
					{
						$get_nav_params["filter_finish_".$name] = '';
					}
					break;

				case 'date_interval':
				case 'datetime_interval':
					$get_nav_params["filter_start_".$name] = '';
					$get_nav_params["filter_finish_".$name] = '';
					if(! empty($_GET["filter_start_".$name]))
					{
						$get_nav_params["filter_start_".$name] = $this->diafan->unixdate($_GET["filter_start_".$name]);
						if ($get_nav_params["filter_start_".$name])
						{
							$this->diafan->where .= " AND e.".$name.">=".$get_nav_params["filter_start_".$name];
							$get_nav_params["filter_start_".$name] = date('d.m.Y'.($row["type"] == 'datetime_interval' ? ' H:i' : ''), $get_nav_params["filter_start_".$name]);
							$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?').'filter_start_'.$name.'='.$get_nav_params["filter_start_".$name];
						}
						else
						{
							$get_nav_params["filter_start_".$name] = '';
						}
					}
					if(! empty($_GET["filter_finish_".$name]))
					{
						$get_nav_params["filter_finish_".$name] = $this->diafan->unixdate($_GET["filter_finish_".$name]);
						if ($get_nav_params["filter_finish_".$name])
						{
							$this->diafan->where .= " AND e.".$name."<=".$get_nav_params["filter_finish_".$name];
							$get_nav_params["filter_finish_".$name] = date('d.m.Y'.($row["type"] == 'datetime_interval' ? ' H:i' : ''), $get_nav_params["filter_finish_".$name]);
							$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_finish_'.$name.'='.$get_nav_params["filter_finish_".$name];
						}
						else
						{
							$get_nav_params["filter_finish_".$name] = '';
						}
					}
					break;
			}
		}
		$this->diafan->get_nav_params = $get_nav_params;
	}

	/**
	 * Определяет является ли текущее действие указанным в аргументе
	 *
	 * @param string $action действие
	 * @return boolean
	 */
	public function is_action($action)
	{
		if($action == "edit")
		{
			if($this->diafan->_route->edit || $this->diafan->_route->addnew || $this->diafan->config("only_edit"))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		if(! empty($_POST["action"]) && $_POST["action"] == $action)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Формирует массив значений для списка
	 *
	 * @return array
	 */
	public function get_select_from_db($config)
	{
		$array = array();
		if(empty($config["table"]))
		{
			return $array;
		}
		if(empty($config["name"]))
		{
			$config["name"] = 'name';
		}
		if(empty($config["id"]))
		{
			$config["id"] = 'id';
		}
		$config["name"] = str_replace('LANG', _LANG, $config["name"]);
		if(isset($config["empty"]))
		{
			$array[''] = $config["empty"];
		}

		if(! empty($config["hierarchy"]))
		{
			$rows = DB::query_fetch_key_array("SELECT ".$config["id"].' AS id,'.$config["name"]." AS name, parent_id FROM {".$config["table"]."}"
			.(! empty($config["where"]) ? " WHERE ".$config["where"] : '' )
			.(! empty($config["order"]) ? " ORDER BY ".$config["order"] : ''), "parent_id");

			$array = $this->diafan->hierarchy_list($array, $rows);
		}
		else
		{
			$rows = DB::query_fetch_all("SELECT ".$config["id"].' AS id,'.$config["name"]." AS name FROM {".$config["table"]."}"
			.(! empty($config["where"]) ? " WHERE ".$config["where"] : '' )
			.(! empty($config["order"]) ? " ORDER BY ".$config["order"] : ''));
			foreach($rows as $row)
			{
				$array[$row["id"]] = $row["name"];
			}
		}
		return $array;
	}

	public function hierarchy_list($result, $rows, $parent = 0, $l = 0)
	{
		if(empty($rows[$parent]))
		{
			return $result;
		}
		foreach($rows[$parent] as $r)
		{
			if(! empty($result[$r["id"]]))
			{
				return $result;
			}
			$result[$r["id"]] = str_repeat('-', $l).($l ? ' ' : '').$r["name"];
			$result = $this->hierarchy_list($result, $rows, $r["id"], $l + 1);
		}
		return $result;
	}

	/**
	 * Одноразовая запись
	 *
	 * @param string|array $name метка кэша
	 * @param mixed $data данные, сохраняемые в кэше
	 * @return mixed
	 */
	public function one_shot($name, $data = null)
	{
		$argc = func_num_args();
		$params = func_get_args();

		// метка кеша
		$cache_meta = array(
			"name" => $name,
			"prefix" => $this->diafan->_session->id,
			"is_admin" => (defined('IS_ADMIN') && IS_ADMIN),
		);
		// чтение
		if($cache_data = $this->diafan->_cache->get($cache_meta, __METHOD__, CACHE_GLOBAL))
		{
			//удаление
			$this->diafan->_cache->delete($cache_meta, __METHOD__);
		}

		if($argc > 1)
		{
			$data = $params[1];
			//кеширование
			$this->diafan->_cache->save($data, $cache_meta, __METHOD__, CACHE_GLOBAL);
		}

		return $cache_data;
	}

	/**
	 * Сохраняет сообщение для модуля
	 *
	 * @param string $value содержание сообщения
	 * @return boolean
	 */
	public function set_one_shot($value = false)
	{
		if(! is_string($value) && $value !== false)
		{
			return false;
		}
		if($message = $this->diafan->one_shot(array(
      "module" => $this->diafan->_admin->module,
      "rewrite" => $this->diafan->_admin->rewrite,
      "method" => 'message')))
		{
			if(! is_array($message)) $message = array($message);
		}
		else $message = array();
		if($value !== false)
		{
			$message[] = $value;
			$this->diafan->one_shot(array(
				"module" => $this->diafan->_admin->module,
				"rewrite" => $this->diafan->_admin->rewrite,
				"method" => 'message'), $message);
		}
		return true;
	}

	/**
	 * Возвращает сообщение для модуля
	 *
	 * @return mixed
	 */
	public function get_one_shot()
	{
		if($message = $this->diafan->one_shot(array(
      "module" => $this->diafan->_admin->module,
      "rewrite" => $this->diafan->_admin->rewrite,
      "method" => 'message')))
		{
			$this->cache["one_shot"] = $message;
		}
		if(! isset($this->cache["one_shot"]))
		{
			return false;
		}
		return $this->cache["one_shot"];
	}

	/**
	 * Форматирует дату в соответствии с конфигурацией модуля
	 *
	 * @param integer $date дата в формате UNIX
	 * @param string $module_name название модуля, по умолчанию модуль, прикрепленный к текущей странице
	 * @param integer $site_id номер страницы сайта
	 * @param integer $config_format номер формата
	 * @return string
	 */
	public function format_date($date, $module_name = '', $site_id = 0, $config_format = false)
	{
		$months_array = array(
			'01' => $this->diafan->_('января'),
			'02' => $this->diafan->_('февраля'),
			'03' => $this->diafan->_('марта'),
			'04' => $this->diafan->_('апреля'),
			'05' => $this->diafan->_('мая'),
			'06' => $this->diafan->_('июня'),
			'07' => $this->diafan->_('июля'),
			'08' => $this->diafan->_('августа'),
			'09' => $this->diafan->_('сентября'),
			'10' => $this->diafan->_('октября'),
			'11' => $this->diafan->_('ноября'),
			'12' => $this->diafan->_('декабря')
		);
		$week_array = array(
			'1' => $this->diafan->_('понедельник'),
			'2' => $this->diafan->_('вторник'),
			'3' => $this->diafan->_('среда'),
			'4' => $this->diafan->_('четверг'),
			'5' => $this->diafan->_('пятница'),
			'6' => $this->diafan->_('суббота'),
			'0' => $this->diafan->_('воскресенье')
		);
		if ($config_format === false && !$module_name)
		{
			$module_name = $this->diafan->_admin->module;
		}
		if ($config_format === false && !$site_id)
		{
			$site_id = $this->diafan->_admin->id;
		}

		$config_format = $config_format === false ? $this->diafan->configmodules("format_date", $module_name, $site_id) : $config_format;

		switch ($config_format)
		{
			case 1:
			return date("d ", $date).$months_array[date("m", $date)].date(" Y ", $date).' '.$this->diafan->_('г.');

			case 2:
			return date("d ", $date).$months_array[date("m", $date)];

			case 3:
			return date("d ", $date).$months_array[date("m", $date)].date(" Y, ", $date).$week_array[date("w", $date)];

			case 4:
			return '';

			case 5:
			return $this->format_date_5($date);

			case 6:
			return date("d.m.Y H:i", $date);

			default:
			return date("d.m.Y", $date);
		}
	}

	/**
	 * Осуществляет умное форматирование даты
	 *
	 * @param integer $date дата в формет UNIX
	 * @return string
	 */
	private function format_date_5($date)
	{
		if (! $date)
			return '';

		$months_array = array(
			'01' => $this->diafan->_('января'),
			'02' => $this->diafan->_('февраля'),
			'03' => $this->diafan->_('марта'),
			'04' => $this->diafan->_('апреля'),
			'05' => $this->diafan->_('мая'),
			'06' => $this->diafan->_('июня'),
			'07' => $this->diafan->_('июля'),
			'08' => $this->diafan->_('августа'),
			'09' => $this->diafan->_('сентября'),
			'10' => $this->diafan->_('октября'),
			'11' => $this->diafan->_('ноября'),
			'12' => $this->diafan->_('декабря')
		);
		$week_array = array(
			'1' => $this->diafan->_('понедельник'),
			'2' => $this->diafan->_('вторник'),
			'3' => $this->diafan->_('среда'),
			'4' => $this->diafan->_('четверг'),
			'5' => $this->diafan->_('пятница'),
			'6' => $this->diafan->_('суббота'),
			'0' => $this->diafan->_('воскресенье')
		);
		if (time() - $date < 3600)
		{
			$min = round((time() - $date) / 60);
			if ($min < 2)
			{
			return $this->diafan->_('1 минуту назад');
			}
			if ($min % 10 == 1 && $min > 20)
			{
			return $this->diafan->_('%s минуту назад', false, $min);
			}
			if ($min % 10 < 5 && ($min > 20 || $min < 10))
			{
			return $this->diafan->_('%s минуты назад', false, $min);
			}
			return $this->diafan->_('%s минут назад', false, $min);
		}

		if ($date >= mktime(0, 0, 0, date("m"), date("d"), date("Y")))
		{
			return  $this->diafan->_('Сегодня').', '.date("H:i", $date);
		}

		if ($date >= mktime(0, 0, 0, date("m"), date("d"), date("Y")) - 86400)
		{
			return $this->diafan->_('Вчера').', '.date("H:i", $date);
		}

		if ($date >= time() - 86400 * 30)
		{
			return date("d ", $date).$months_array[date("m", $date)].', '.$week_array[date("w", $date)];
		}

		if (date("Y", $date) == date("Y"))
		{
			return date("d ", $date).$months_array[date("m", $date)];
		}
		return date("d ", $date).$months_array[date("m", $date)].date(" Y ", $date).' '.$this->diafan->_('г.');
	}
}
