<?php
/**
 * Модель
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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

class Consultant_model extends Model
{
	/**
	 * Генерирует данные для шаблонной функции: on-line консультант
	 * @return string
	 */
	public function show_block()
	{
		$result = '';
		$backend = $this->diafan->configmodules('backend', 'consultant');
		if(Custom::exists('modules/consultant/backend/'.$backend.'/consultant.'.$backend.'.model.php'))
		{
			Custom::inc('modules/consultant/backend/'.$backend.'/consultant.'.$backend.'.model.php');
			
			$name_class = 'Consultant_'.$backend.'_model';
			$class = new $name_class($this->diafan);
			if (is_callable(array(&$class, "get")))
			{
				$result = $class->get();
			}
		}
		return $result;
	}
}