<?php
/**
 * Подключение для работы с капчей
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

/**
 * Captcha_inc
 */
class Captcha_inc extends Model
{
	/**
	 * Выводит капчу
	 * 
	 * @param string $modules метка капчи
	 * @param string $error ошибка ввода кода, если запрос передан не через Ajax
	 * @param boolean $is_update капча генерируется для обновления
	 * @return string
	 */
	public function get($modules = "modules", $error = "", $is_update = false)
	{
		if($backend = $this->get_backend())
		{
			return $backend->get($modules, $error, $is_update);
		}
		return '';
	}

	/**
	 * Проверяет правильность ввода капчи
	 * 
	 * @param string $modules метка капчи
	 * @return string|boolean false
	 */
	public function error($modules = "modules")
	{
		if($backend = $this->get_backend())
		{
			return $backend->check($modules);
		}
		return false;
	}

	/**
	 * Проверяет подключена ли капта в настройках модуля
	 * 
	 * @param string $module названием модуля
	 * @param integer $site_id страница сайта с подключенным модулем
	 * @return boolean
	 */
	public function configmodules($module, $site_id = 0)
	{
		if($this->diafan->configmodules('captcha', $module, $site_id) && $this->diafan->configmodules('captcha', $module, $site_id) === '1')
		{
			return true;
		}
		if ($this->diafan->configmodules('captcha', $module, $site_id) && in_array($this->diafan->_users->role_id, unserialize($this->diafan->configmodules('captcha', $module, $site_id))))
		{
			return true;
		}
		return false;
	}

	/**
	 * Получает экземпляр класса выбранного бэкенда
	 * 
	 * @return resource|boolean false
	 */
	private function get_backend()
	{
		if(isset($this->cache["backend"]))
		{
			return $this->cache["backend"];
		}
		$this->cache["backend"] = false;
		$backend = $this->diafan->configmodules('backend', 'captcha');
		if(Custom::exists('modules/captcha/backend/'.$backend.'/captcha.'.$backend.'.inc.php'))
		{
			Custom::inc('modules/captcha/backend/'.$backend.'/captcha.'.$backend.'.inc.php');
			
			$name_class = 'Captcha_'.$backend.'_inc';
			$class = new $name_class($this->diafan);
			if (is_callable(array(&$class, "get")) && is_callable(array(&$class, "check")))
			{
				$this->cache["backend"] = &$class;
			}
			if(Custom::exists('modules/captcha/backend/'.$backend.'/captcha.'.$backend.'.js'))
			{
				$this->diafan->_site->js_view[] = 'modules/captcha/backend/'.$backend.'/captcha.'.$backend.'.js';
			}
		}
		return $this->cache["backend"];
	}
}