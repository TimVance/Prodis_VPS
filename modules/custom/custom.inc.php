<?php
/**
 * Подключение модуля
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
 * Custom_inc
 */
class Custom_inc extends Diafan
{
	/**
	 * @var array папки и файлы, индексируеме для точек возврата
	 */
	private $folders = array('adm', 'css', 'img', 'themes', 'modules', 'includes', 'plugins', 'js');

	/**
	 * @var array папки и файлы, не индексируемые для точек возврата
	 */
	private $exclude = array('adm/htmleditor', 'includes/custom.php');

	/**
	 * Генерирует тему из кастомизированных файлов
	 *
	 * @return array
	 */
	public function generate()
	{
		// получает все файлы в текущей системе
		$current_files = array();
		if ($dir = opendir(ABSOLUTE_PATH))
		{
			while (($file = readdir($dir)) !== false)
			{
				if ($file == '.' || $file == '..' || ! in_array($file, $this->folders) || in_array($file, $this->exclude))
					continue;

				if(is_dir(ABSOLUTE_PATH.$file))
				{
					$this->read_dir(ABSOLUTE_PATH, $file, $current_files);
				}
				else
				{
					$current_files[$file] = file_get_contents(ABSOLUTE_PATH.$file);
				}
			}
			closedir($dir);
		}

		// получает все файлы из текущей точки возврата
		$return_id = DB::query_result("SELECT id FROM {update_return} WHERE current='1' LIMIT 1");
		$return_files = $this->diafan->_update->get_all_files($return_id);
		foreach($return_files as $k => $v)
		{
			$return_files[$k] = $v;
		}

		// находит кастомизированные файлы
		$custom_diff = array();
		foreach($current_files as $k => $file)
		{
			if(! isset($return_files[$k]) || str_replace("\r\n", "\n", $return_files[$k]) != str_replace("\r\n", "\n", $file))
			{
				$custom_diff[$k] = $file;
			}
			if($GLOBALS["custom"]["names"][0] && file_exists(ABSOLUTE_PATH.'custom/'.$GLOBALS["custom"]["names"][0].'/'.$k) && str_replace("\r\n", "\n", $file) == str_replace("\r\n", "\n", file_get_contents(ABSOLUTE_PATH.'custom/'.$GLOBALS["custom"]["names"][0].'/'.$k)))
			{
				File::delete_file('custom/'.$GLOBALS["custom"]["names"][0].'/'.$k);
			}
		}

		// находит файлы из текущей точки возврата, которые были изменены
		$return_diff = array();
		foreach($return_files as $k => $file)
		{
			if(! isset($current_files[$k]) || str_replace("\r\n", "\n", $current_files[$k]) != str_replace("\r\n", "\n", $file))
			{
				$return_diff[$k] = $file;
			}
		}
		if(! $custom_diff && ! $return_diff)
		{
			return 0;
		}

		// если нет текущей темы, создает новую тему, иначе добавляет кастомизированные файлы к текущей теме
		if(! Custom::name())
		{
			Custom::name('custom'.date("d_m_Y_H_i"));
			DB::query("INSERT INTO {custom} (name, created, text) VALUES ('%s', %d, '%s')", Custom::name(), time(), $this->diafan->_('Автоматически сгенерированная тема.'));
			Custom::inc('includes/config.php');
			$config = new Config();
			$config->save(array('CUSTOM' => Custom::name()), $this->diafan->_languages->all);
			File::create_dir('custom/'.Custom::name());
		}
		$result = array("custom" => array(), "return" => array());

		// добавляет кастомизированные файлы к текущей теме
		foreach($custom_diff as $k => $f)
		{
			if(! file_exists(ABSOLUTE_PATH.'custom/'.Custom::name().'/'.$k))
			{
				try
				{
					File::save_file($f, 'custom/'.Custom::name().'/'.$k);
				}
				catch (Exception $e){}
				$result["custom"][] = $k;
			}
		}

		// очищает основную систему от кастомизированных файлов
		foreach($custom_diff as $k => $f)
		{
			if(! isset($return_diff[$k]))
			{
				File::delete_file($k);
			}
		}

		// добавляет все файлы из текущей точки возврата
		foreach($return_diff as $k => $f)
		{
			if($f != 'deleted' && ! in_array($k, array('upgrade.php', 'downgrade.php')))
			{
				try
				{
					File::save_file($f, $k);
				}
				catch (Exception $e){}
				$result["return"][] = $k;
			}
		}
		return $result;
	}

	/**
	 * Читает папку в файлах точки
	 *
	 * @param string $path путь до файлов
	 * @param array $files получаемые файлы точки
	 * @return void
	 */
	private function read_dir($abspath, $path, &$files)
	{
		if ($dir = opendir($abspath.($path ? '/'.$path : '')))
		{
			while (($file = readdir($dir)) !== false)
			{
				if(($path ? $path.'/' : '').$file == USERFILES || in_array(($path ? $path.'/' : '').$file, $this->exclude))
				{
					continue;
				}
				if ($file != '.' && $file != '..')
				{
					if(is_dir($abspath.($path ? '/'.$path : '').'/'.$file))
					{
						$this->read_dir($abspath, ($path ? $path.'/' : '').$file, $files);
					}
					else
					{
						$files[($path ? $path.'/' : '').$file] = file_get_contents($abspath.($path ? '/'.$path : '').'/'.$file);
					}
				}
			}
			closedir($dir);
		}
	}

	/**
	 * Читает директорию с учетом активных тем сайта
	 *
	 * @param string $path путь до директории относительно корня сайта
	 * @param mixed $names темы из числа активных тем сайта, исключаемые из чтения
	 * @return array
	 */
	private function get_dir($path, $names = false)
	{
		if(! is_array($names))
		{
			$names = array($names);
		}
		foreach($names as $key => $name)
		{
			if(! empty($name)) continue;
			unset($names[$key]);
		}

		$rows = array();
		if(! isset($this->cache["path"]))
		{
			$this->cache["path"] = array();
			if (is_dir(ABSOLUTE_PATH.$path) && $dir = opendir(ABSOLUTE_PATH.$path))
			{
				while (($file = readdir($dir)) !== false)
				{
					if ($file != '.' && $file != '..')
					{
						$this->cache["path"][$path.'/'.$file] = $file;
					}
				}
				closedir($dir);
			}
		}
		$rows = $this->cache["path"];
		if(Custom::names())
		{
			foreach(Custom::names() AS $name)
			{
				if(! empty($names) && in_array($name, $names))
				{
					continue;
				}
				if(! isset($this->cache["custom"][$name]))
				{
					$this->cache["custom"][$name] = array();
					if (is_dir(ABSOLUTE_PATH.'custom/'.$name.'/'.$path) && $dir = opendir(ABSOLUTE_PATH.'custom/'.$name.'/'.$path))
					{
						while (($file = readdir($dir)) !== false)
						{
							if ($file != '.' && $file != '..')
							{
								$this->cache["custom"][$name][$path.'/'.$file] = $file;
							}
						}
						closedir($dir);
					}
				}
				$rows = array_replace($rows, $this->cache["custom"][$name]);
			}
		}

		return $rows;
	}

	/**
	 * Изменяет состояние темы
	 *
	 * @param mixed $array название темы или массив названий тем
	 * @param boolean $enable активирует тему
	 * @param boolean $sql выполняет дополнительные запросы к базе данных
	 * @return boolean
	 */
	public function set($array, $enable, $sql = false)
	{
		if(! is_array($array))
		{
			$array = array($array);
		}
		foreach($array as $key => $name)
		{
			if(! empty($name)) continue;
			unset($array[$key]);
		}
		if(empty($array))
		{
			return false;
		}

		$edit = false;
		$names = Custom::names();
		if ($enable)
		{
			foreach($array as $name)
			{
				if(! in_array($name, $names))
				{
					$edit = true;
					$names[] = $name;
					if($sql)
					{
						$this->query($name, true);
					}
				}
			}
		}
		else
		{
			$new_names = array();
			foreach($names as $n)
			{
				if(! in_array($n, $array))
				{
					$new_names[] = $n;
				}
				else
				{
					$edit = true;
					if($sql)
					{
						$this->query($n, false);
					}
				}
			}
			$names = $new_names;
		}
		if($edit)
		{
			$new_values = array('CUSTOM' => implode(',', $names));
			Custom::inc('includes/config.php');
			Config::save($new_values, $this->diafan->_languages->all);
		}
		return $edit;
	}

	/**
	 * Импортирует тему
	 *
	 * @param string $file_path архивный файл темы
	 * @param string $name название темы
	 * @return boolean
	 */
	public function import($file_path, $name)
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			return false;
		}
		if ($name != '')
		{
			//File::delete_dir('custom/'.$name);
			File::create_dir('custom/'.$name);
			if(class_exists('ZipArchive'))
			{
				$paths = array();
				$zip = new ZipArchive;
				if ($zip->open($file_path) === true)
				{
					for($i = 0; $i < $zip->numFiles; $i++)
					{
						$file_name = $zip->getNameIndex($i);
						if($file_name && substr($file_name, 0, 1) != '/')
						{
							$file_name = '/'.$file_name;
						}
						if(substr($file_name, -1) == '/')
						{
							$arr = explode('/', $file_name);
							array_pop($arr);
							$file_name = array_pop($arr);
							File::create_dir('custom/'.$name.($arr ? '/'.implode('/', $arr) : '').'/'.$file_name);
						}
						else
						{
							File::save_file($zip->getFromName($zip->getNameIndex($i)), 'custom/'.$name.$file_name);
						}
					}
					$zip->close();
				}
			}
		}
		return File::check_dir('custom/' . $name);
	}

	/**
	 * Исполнение SQL-запросов в файле install.sql или uninstall.sql
	 *
	 * @param string $name название темы
	 * @param boolean $install определяет файл запросов: install или uninstall
	 * @return void
	 */
	public function query($name, $install = true)
	{
		$install = $install ? 'install' : 'uninstall';

		if(! file_exists(ABSOLUTE_PATH.'custom/'.$name.'/'.$install.'.sql'))
			return;

		Custom::inc("modules/service/admin/service.admin.db.php");
		$obj = new Service_admin_db($this->diafan);
		$obj->import_query(ABSOLUTE_PATH.'custom/'.$name.'/'.$install.'.sql', false);
	}

	/**
	 * Получает список всех модулей которые можно установить
	 *
	 * @param mixed $names темы, для которых определяются модули (по умолчанию все активные темы)
	 * @return array
	 */
	public function get_modules($names = false)
	{
		if(! is_array($names))
		{
			$names = array($names);
		}
		foreach($names as $key => $name)
		{
			if(! empty($name)) continue;
			unset($names[$key]);
		}

		$globals_custom = $GLOBALS["custom"];
		if(! empty($names))
		{
			$customs = Custom::names();
			foreach($names as $name)
			{
				if(in_array($name, $customs))
				{
					continue;
				}
				Custom::add($name);
			}
		}

		$modules = array();
		foreach ($this->diafan->all_modules as $r)
		{
			if($r["module_name"] == $r["name"])
			{
				$modules[$r["name"]] = $r["title"];
			}
		}

		foreach($this->diafan->_languages->all as $l)
		{
			$langs[] = $l["id"];
		}

		if(! class_exists('Install'))
		{
			Custom::inc("includes/install.php");
		}
		$rows = array();
		$rs = $this->get_dir("modules");
		if(! empty($names))
		{
			$rs = array_diff($rs, $this->get_dir("modules", $names));
		}
		foreach($rs as $module)
		{
			if (Custom::exists('modules/'.$module.'/'.$module.'.install.php'))
			{
				Custom::inc('modules/'.$module.'/'.$module.'.install.php');
				$name = Ucfirst($module).'_install';
				$this->install[$module] = new $name($this->diafan);

				if($this->install[$module]->is_core)
					continue;

				$this->install[$module]->langs = $langs;
				$this->install[$module]->module = $module;

				$row["installed"] = in_array($module, array_keys($modules));

				if($row["installed"])
				{
					$row["name"] = $modules[$module];
				}
				else
				{
					$row["name"] = $this->install[$module]->title;
				}
				if(! $row["name"])
				{
					$row["name"] = $module;
				}
				$row["module_name"] = $module;
				$rows[$module] = $row;
			}
		}

		$GLOBALS["custom"] = $globals_custom;
		return $rows;
	}

	/**
	 * Установка/удаление модулей
	 *
	 * @param mixed $modules название модуля или массив названий модулей
	 * @param boolean $enable маркер установки/удаления модулей
	 * @param mixed $names не активные темы, которые необходимо временно подключить для установки/удаления определенных в их коде модулей
	 * @return boolean
	 */
	public function set_modules($modules, $enable, $names = false)
	{
		if(empty($modules))
		{
			return false;
		}

		if(! is_array($modules))
		{
			$modules = array($modules);
		}

		if(! is_array($names))
		{
			$names = array($names);
		}
		foreach($names as $key => $name)
		{
			if(! empty($name)) continue;
			unset($names[$key]);
		}

		$globals_custom = $GLOBALS["custom"];
		if(! empty($names))
		{
			$customs = Custom::names();
			foreach($names as $name)
			{
				if(in_array($name, $customs))
				{
					continue;
				}
				Custom::add($name);
			}
		}

		$rows = $this->diafan->_custom->get_modules();
		if(empty($rows))
		{
			return false;
		}

		foreach($rows as $module => $row)
		{
			if(! in_array($module, $modules))
			{
				continue;
			}

			// удаление модуля
			if(! $enable)
			{
				if($row["installed"])
				{
					$this->install[$module]->uninstall();
				}
			}
			else
			{
				if(! $row["installed"])
				{
					$this->install[$module]->tables();
					$this->install[$module]->start(false);

					//установка прав на административную часть установленного модуля текущему пользователю
					if(! $this->diafan->_users->roles('all', 'all'))
					{
						$rs = DB::query_fetch_all("SELECT rewrite FROM {admin} WHERE BINARY rewrite='%s' OR BINARY rewrite LIKE '%s%%'", $module, $module);
						foreach ($rs as $r)
						{
							DB::query("INSERT INTO {users_role_perm} (role_id, perm, rewrite, type) VALUES (%d, 'all', '%s', 'admin')", $this->diafan->_users->role_id, $r["rewrite"]);
						}
					}
				}
			}
		}
		foreach ($rows as $module => $row)
		{
			// удаление модуля
			if($enable &&  ! $row["installed"])
			{
				$this->install[$module]->action_post();
			}
		}

		$GLOBALS["custom"] = $globals_custom;
		return true;
	}
}
