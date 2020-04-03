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
 * Cache
 * 
 * Кэширование в файлах
 */
class Cache_files implements Cache_interface
{
	/**
	 * @var array текущий кэш
	 */
	private $cache;

	/**
	 * @var integer максимальное количество файлов в кэше для одного модуля
	 */
	private $max_files = 300;

	/**
	 * Закрывает ранее открытое соединение
	 * 
	 * @return void
	 */
	public function close(){}

	/**
	 * Читает кэш модуля $module с меткой $name
	 *
	 * @param string|array $name метка кэша
	 * @param string $module название модуля
	 * @return mixed
	 */
	public function get($name, $module)
	{
		if (empty($this->cache[$module][$name]))
		{
			$this->inc_cache_file($name, $module);
		}

		if (! isset($this->cache[$module][$name]))
		{
			return false;
		}
		return unserialize($this->cache[$module][$name]);
	}

	/**
	 * Сохраняет данные $data для модуля $module с меткой $name
	 *
	 * @param mixed $data данные, сохраняемые в кэше
	 * @param string|array $name метка кэша
	 * @param string $module название модуля
	 * @return void
	 */
	public function save($data, $name, $module)
	{
		$this->cache[$module][$name] = serialize($data);
		$this->write_cache($name, $module);
	}

	/**
	 * Удаляет кэш для модуля $module с меткой $name. Если функция вызвана с пустой меткой, то удаляется весь кэш для модуля $module
	 *
	 * @param string $name метка кэша
	 * @param string $module название модуля
	 * @return void
	 */
	public function delete($name, $module)
	{
		if (! $module)
		{
			if(! $d = dir(ABSOLUTE_PATH.'cache'))
			{
				throw new Cache_exception('Папка '.ABSOLUTE_PATH.'cache не существует. Создайте папку и установите права на запись (777).');
			}
			$error = '';
			try
			{
				while ($entry = $d->read())
				{
					if ($entry != "." and $entry != ".." and $entry != ".htaccess")
					{
						if (is_dir(ABSOLUTE_PATH.'cache/'.$entry))
						{
							File::delete_dir('cache/'.$entry);
						}
						else
						{
							File::delete_file('cache/'.$entry);
						}
					}
				}
			}
			catch (Exception $e)
			{
				$error .= $e->getMessage()."\n";
			}

			$d->close();
			if($error)
			{
				throw new Cache_exception($error);
			}
		}
		elseif (! $name)
		{
			File::delete_dir('cache/'.$module);
			unset($this->cache[$module]);
		}
		else
		{
			$this->cache[$module][$name] = '';
			File::delete_file('cache/'.$module.'/'.$name.'.txt');
		}
	}

	/**
	 * Подключает файл с кэшем модуля
	 *
	 * @param string $name метка кэша
	 * @param string $module название модуля
	 * @return void
	 */
	private function inc_cache_file($name, $module)
	{
		if (empty($this->cache[$module][$name]) && file_exists(ABSOLUTE_PATH.'cache/'.$module.'/'.$name.'.txt'))
		{
			$this->cache[$module][$name] = file_get_contents(ABSOLUTE_PATH.'cache/'.$module.'/'.$name.'.txt');
		}
	}

	/**
	 * Записывает кэш в файл
	 *
	 * @param string $name метка кэша
	 * @param string $module название модуля
	 * @return void
	 */
	private function write_cache($name, $module)
	{
		if (! is_dir(ABSOLUTE_PATH."cache/".$module))
		{
			if(! mkdir(ABSOLUTE_PATH."cache/".$module, 0777))
			{
				throw new Cache_exception('Невозможно создать папку '.ABSOLUTE_PATH."cache/".$module.'. Установите права на запись (777) для папки '.ABSOLUTE_PATH.'cache.');
			}
		}
		else
		{
			$c = 0;
			$d = dir(ABSOLUTE_PATH."cache/".$module); 
			while($str = $d->read())
			{ 
				if($str{0} != '.')
				{ 
					if(! is_dir(ABSOLUTE_PATH."cache/".$module.'/'.$str))
					{
						$c++;
					}
				}
			}
			$d->close();
			if($c > $this->max_files)
			{
				File::delete_dir('cache/'.$module);
				mkdir(ABSOLUTE_PATH."cache/".$module, 0777);
			}
		}

		if(! $fp = fopen(ABSOLUTE_PATH."cache/".$module.'/'.$name.'.txt', "wb"))
		{
			throw new Cache_exception('Невозможно записать файл '.ABSOLUTE_PATH."cache/".$module.'/'.$name.'. Установите права на запись (777) для на папку '.ABSOLUTE_PATH."cache/".$module.' и для файла '.ABSOLUTE_PATH."cache/".$module.'/'.$name.'.');
		}
		fwrite($fp, $this->cache[$module][$name]);
		fclose($fp);
	}
}