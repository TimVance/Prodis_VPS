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
 * Controller
 *
 * Каркас для контроллера модулей
 */
class Controller extends Diafan
{
	/**
	 * @var array переменные, передаваемые в URL страницы
	 */
	public $rewrite_variable_names = array();

	/**
	 * @var array сгенерированные в моделе данные, передаваемые в шаблон
	 */
	public $result;

	/**
	 * Подключает модель
	 *
	 * @return object|null
	 */
	public function __get($name)
	{
		switch($name)
		{
			case 'api':
			case 'action':
			case 'model':
				$module = $this->diafan->current_module;
				if(! isset($this->cache[$name.'_'.$module]))
				{
					if(Custom::exists('modules/'.$module.'/'.$module.'.'.$name.'.php'))
					{
						Custom::inc('modules/'.$module.'/'.$module.'.'.$name.'.php');
						$class = ucfirst($module).'_'.$name;
						$this->cache[$name.'_'.$module] = new $class($this->diafan);
					}
					else
					{
						throw new Controller_exception($this->diafan->_('Файл %s не существует.', false, 'modules/'.$module.'/'.$module.'.'.$name.'.php'));
					}
				}
				return  $this->cache[$name.'_'.$module];

			default:
				return false;
		}
	}

	/**
	 * Инициализация модуля
	 *
	 * @return void
	 */
	public function init(){}

	/**
	 * Обрабатывает полученные данные из формы
	 *
	 * @return void
	 */
	public function action()
	{
		$this->action->init();
	}

	/**
	 * Обрабатывает полученные данные из формы
	 *
	 * @return void
	 */
	public function api()
	{
		$this->api->init();
	}

	/**
	 * Выводит шаблон модуля
	 *
	 * @return void
	 */
	public function show_module()
	{
		if($this->diafan->_site->module)
		{
			$this->diafan->current_module = $this->diafan->_site->module;
			$this->diafan->_tpl->js();
			$this->diafan->_tpl->css();
			echo $this->diafan->_tpl->get($this->result["view"], $this->diafan->_site->module, $this->result);
			$this->diafan->current_module = '';
		}
	}

	/**
	 * Определяет свойства страницы, если они заданы в модуле
	 *
	 * @return void
	 */
	public function get_global_variables()
	{
		$this->result = $this->model->result;
		$this->diafan->_site->timeedit = ! empty($this->result["timeedit"]) && $this->diafan->_site->timeedit < $this->result["timeedit"]
					  ? $this->result["timeedit"]
					  : $this->diafan->_site->timeedit;
		if (! empty($this->result["breadcrumb"]))
		{
			$this->diafan->_site->breadcrumb = $this->result["breadcrumb"];
		}

		if (! empty($this->result["title_meta"]))
		{
			$this->diafan->_site->titlemodule_meta = $this->result["title_meta"];
		}
		if (! empty($this->result["titlemodule"]))
		{
			$this->diafan->_site->titlemodule = $this->result["titlemodule"];
		}
		if (! empty($this->result["edit_meta"]))
		{
			$this->diafan->_site->edit_meta = $this->result["edit_meta"];
		}
		if (! empty($this->result["theme"]))
		{
			$this->diafan->_site->theme = $this->result["theme"];
		}
		if (! empty($this->result["canonical"]))
		{
			$this->diafan->_site->canonical = $this->result["canonical"];
		}
		else
		{
			if($this->diafan->_route->sort || $this->diafan->_route->page)
			{
				$this->diafan->_site->canonical = $this->diafan->_route->current_link(array("page", "sort"));
			}
		}
		if (! empty($this->result["noindex"]))
		{
			$this->diafan->_site->noindex = $this->result["noindex"];
		}
		if($this->diafan->_route->cat || $this->diafan->_route->show || $this->diafan->_route->brand)
		{
			$this->diafan->_site->keywords = isset($this->result["keywords"]) ? $this->result["keywords"] : '';
			$this->diafan->_site->descr = isset($this->result["descr"]) ? $this->result["descr"] : '';

			$langs = $this->diafan->_languages->all;
			foreach ($langs as &$l)
			{
				if(empty($this->result["act".$l["id"]]))
				{
					$l["page_act"] = false;
				}
			}
			$this->diafan->_languages->all = $langs;
		}

		if(! $this->diafan->_site->deactivate)
		{
			$time = mktime(1, 0, 0);
			$act = isset($this->model->result["act"]) ? $this->model->result["act"] : 1;
			$date_start = isset($this->model->result["date_start"]) ? $this->model->result["date_start"] : 0;
			$date_finish = isset($this->model->result["date_finish"]) ? $this->model->result["date_finish"] : 0;
			if($this->diafan->_site->id != 1 && (! $this->diafan->_users->id || ! $this->diafan->_users->roles("init", "site", array(), 'admin')))
			{
				$this->diafan->_site->deactivate = false;
			}
			else
			{
				if($act == '1' && $date_start <= $time
				&& ($date_finish == 0 || $date_finish >= $time))
				{
					$this->diafan->_site->deactivate = false;
				}
				else
				{
					$this->diafan->_site->deactivate = true;
					if($act == '1')
					{
						if ($date_start > $time
						&& ($date_finish == 0 || $date_finish >= $time))
						{
							$this->diafan->_site->deactivate = $date_start;
						}
						if ($date_finish < $time
						&& ($date_start == 0 || $date_start <= $time))
						{
							$this->diafan->_site->deactivate = $date_finish;
						}
					}
				}
			}
		}
	}

	/**
	 * Задает неопределенным атрибутам шаблонного тега значение по умолчанию
	 *
	 * @param array $attributes массив определенных атрибутов
	 * @return array
	 */
	protected function get_attributes(&$attributes)
	{
		$a = func_get_args();
		for ($i = 1; $i < count($a); $i++)
		{
			if (is_array($a[$i]))
			{
				$name = $a[$i][0];
				$value = $a[$i][1];
			}
			else
			{
				$name = $a[$i];
				$value = '';
			}
			if (empty($attributes[$name]))
			{
				$attributes[$name] = $value;
			}
		}
		return $attributes;
	}

	/**
	 * Проверяет, существует ли класс или метод в классе
	 *
	 * @param string $module имя модуля
	 * @param string $name тип класса
	 * @param string $method_name имя метода
	 * @return boolean
	 */
	public static function method_exists($module, $name, $method_name = false)
	{
		if($name == 'model' || $name == 'inc' || $name == 'action' || $name == 'api' || $name == 'install')
		{
			if($name == 'install')
			{
				if(Custom::exists('includes/'.$name.'.php'))
				{
					Custom::inc('includes/'.$name.'.php');
				}
				else return false;
			}

			if(Custom::exists('modules/'.$module.'/'.$module.'.'.$name.'.php'))
			{
				Custom::inc('modules/'.$module.'/'.$module.'.'.$name.'.php');
				try
				{
					$class = ucfirst($module).'_'.$name;
					$object = new $class($module);
					if($method_name)
					{
						$method_exists = method_exists($object, $method_name);
						unset($object);
						return $method_exists;
					}
					elseif(is_object($object))
					{
						unset($object);
						return true;
					}
				}
				catch( Exception $e )
				{
					return false;
				}
			}
		}
		return false;
	}
}

/**
 * Controller_exception
 *
 * Исключение для контроллера
 */
class Controller_exception extends Exception{}
