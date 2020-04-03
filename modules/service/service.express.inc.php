<?php
/**
 * Подключение класса импорт/экспорт данных
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
 * Service_express_inc
 */
class Service_express_inc extends Diafan
{
	/**
	 * @var object бэкэнд
	 */
	private $backend;

	/**
	 * @var string имя модуля
	 */
	private $module_name;

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);

		$args = func_get_args();
		if( empty($args[1]) ) $this->module_name = '';
		else
		{
			if(is_numeric($args[1]))
			{
				if(! $this->module_name = DB::query_result("SELECT module_name FROM {%s_category} WHERE trash='0' AND id=%d LIMIT 1", 'service_express_fields', $args[1]))
				{
					$this->module_name = '';
				}
			}
			elseif(is_string($args[1])) $this->module_name = $args[1];
			else $this->module_name = '';
		}
		$this->module_name = $this->module_name ?: 'service';
	}

	/**
	 * Подключает расширения для подключения
	 *
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		list($prefix, $method) = explode('_', $name, 2);
		switch($prefix)
		{
			case 'import':
				if(! isset($this->backend['import']))
				{
					Custom::inc('modules/service/service.express.import.php');
					if(! $name_class_module = $this->get_class('import'))
					{
						$this->module_name = 'service';
						if(! $name_class_module = $this->get_class('import')) return false;
					}
					if(! in_array($name_class_module, get_declared_classes())) return false;
					$this->backend['import'] = new $name_class_module( $this->diafan );
				}
				$express = &$this->backend['import'];
				break;

			case 'export':
				if(! isset($this->backend['export']))
				{
					Custom::inc('modules/service/service.express.export.php');
					if(! $name_class_module = $this->get_class('export'))
					{
						$this->module_name = 'service';
						if(! $name_class_module = $this->get_class('export')) return false;
					}
					if(! in_array($name_class_module, get_declared_classes())) return false;
					$this->backend['export'] = new $name_class_module( $this->diafan );
				}
				$express = &$this->backend['export'];
				break;

			default:
				return false;
		}
		return call_user_func_array(array(&$express, $method), $args);
	}

	/**
	 * Возвращает имя класса
	 *
	 * @param string $extension расширение
	 * @return string
	 */
	private function get_class($extension)
	{
		if(! $this->module_name || ! $extension) return '';

		$e_type = 'express';
		$module_file = 'modules/'.$this->module_name.'/'.$this->module_name.($e_type ? '.'.$e_type : '').($extension ? '.'.$extension : '').'.php';
		if(! Custom::exists($module_file)) return '';

		Custom::inc($module_file);
		return ucfirst($this->module_name).($e_type ? '_'.str_replace('.', '_', $e_type) : '').($extension ? '_'.str_replace('.', '_', $extension) : '');
	}
}
