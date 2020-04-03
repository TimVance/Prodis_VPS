<?php
/**
 * Обработка POST-запросов в административной части модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if ( ! defined('DIAFAN'))
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
 * Config_admin_action
 */
class Config_admin_action extends Action_admin
{
	/**
	 * Вызывает обработку Ajax-запросов
	 *
	 * @return void
	 */
	public function init()
	{
		if (! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'size':
					$this->size();
					break;
			}
		}
	}

	/**
	 * Определение размера используемых ресурсов
	 *
	 * @return void
	 */
	private function size()
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));
		//кеширование
		$cache_meta = array(
			"name" => "size",
			"time" => $time
		);

		if(! empty($_POST["refresh"]))
		{
			$this->diafan->_cache->delete($cache_meta, 'config');
		}

		if ( ! $result = $this->diafan->_cache->get($cache_meta, 'config'))
		{
			$result = array(
				"files" => File::rglob_size('', false, '/^'.preg_quote((defined('USERFILES') && USERFILES ? USERFILES : 'userfls'), '/').'$/', -1, RGLOD_FILE_GLOB),
				"user_files" => File::rglob_size((defined('USERFILES') && USERFILES ? USERFILES : 'userfls').'/', false, false, -1, RGLOD_FILE_GLOB),
				"db" => DB::size(),
			);

			//сохранение кеша
			$this->diafan->_cache->save($result, $cache_meta, 'config', CACHE_DEVELOPER);
		}

		if(! is_array($result)) $result = array();
		if(empty($result["files"]) || $result["files"] < 0) $result["files"] = 0;
		if(empty($result["user_files"]) || $result["user_files"] < 0) $result["user_files"] = 0;
		if(empty($result["db"]) || $result["db"] < 0) $result["db"] = 0;
		$this->result["result"]["files_size"] = $this->diafan->convert($result["files"] + $result["user_files"]);
		$this->result["result"]["user_files_size"] = $this->diafan->convert($result["user_files"]);
		$this->result["result"]["db_size"] = $this->diafan->convert($result["db"]);
	}
}
