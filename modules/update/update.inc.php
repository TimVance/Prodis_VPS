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
 * Update_inc
 */
class Update_inc extends Diafan
{
	/**
	 * @var array папки и файлы, индексируеме для точек возврата
	 */
	public $folders = array('adm', 'css', 'img', 'themes', 'modules', 'includes', 'plugins', 'js');

	/**
	 * @var array папки и файлы, не индексируемые для точек возврата
	 */
	public $exclude = array('adm/htmleditor', 'includes/custom.php');
	
	/**
	 * Добавляет первую точку возврата
	 * 
	 * @return void
	 */
	public function first_return()
	{
		$VERSION = Custom::version_core();

		DB::query("INSERT INTO {update_return} (id, name, current, created, version) VALUES (1, 'Установка', '1', %d, '%s')", time(), $VERSION);

		// создает  файл  .htaccess, чтобы закрыть доступ извне ко всем файлам точек возврата
		File::create_dir('return', true);

		if(! class_exists('ZipArchive'))
		{
			throw new Exception('Не доступно PHP-расширение ZipArchive. Обратитесь в техническую поддержку хостинга.');
		}
		$zip = new ZipArchive;
		if ($zip->open(ABSOLUTE_PATH.'return/1.zip', ZipArchive::CREATE) === true)
		{
			if ($dir = opendir(ABSOLUTE_PATH))
			{
				while (($file = readdir($dir)) !== false)
				{
					if ($file == '.' || $file == '..' || ! in_array($file, $this->folders) || in_array($file, $this->exclude))
						continue;

					if(is_dir(ABSOLUTE_PATH.$file))
					{
						$this->add_to_zip($zip, $file);
					}
					else
					{
						$zip->addFile(ABSOLUTE_PATH.$file, $file);
					}
				}
				closedir($dir);
			}
			$zip->close();
		}
	}

	/**
	 * Получает обновленные файлы точки с содержимым
	 *
	 * @param integer $id идентификатор точки
	 * @return array
	 */
	public function get_files($id)
	{
		$files = array();
		if(! $id)
		{
			return $files;
		}
		if(! class_exists('ZipArchive'))
		{
			throw new Exception('Не доступно PHP-расширение ZipArchive. Обратитесь в техническую поддержку хостинга.');
		}
		$zip = new ZipArchive;
		if ($zip->open(ABSOLUTE_PATH.'return/'.$id.'.zip') === true)
		{
			for($i = 0; $i < $zip->numFiles; $i++)
			{
				$file = $zip->getNameIndex($i);
				if(substr($file, -1) !== '/')
				{
					$files[$file] = $zip->getFromName($file);
				}
			}
			$zip->close();
		}
		return $files;
	}

	/**
	 * Получает все файлы DIAFAN.CMS в точке с содержимым
	 *
	 * @param integer $id идентификатор точки
	 * @return array
	 */
	public function get_all_files($id)
	{
		$files = array();
		if(! $id)
		{
			return $files;
		}
		if(! class_exists('ZipArchive'))
		{
			throw new Exception('Не доступно PHP-расширение ZipArchive. Обратитесь в техническую поддержку хостинга.');
		}

		// получает все точки начиная от точки возврата и ранее
		$ids  = DB::query_fetch_value("SELECT id FROM {update_return} WHERE id<=%d ORDER BY id DESC", $id, "id");
		foreach($ids as $id)
		{
			$zip = new ZipArchive;
			if ($zip->open(ABSOLUTE_PATH.'return/'.$id.'.zip') === true)
			{
				for($i = 0; $i < $zip->numFiles; $i++)
				{
					$file = $zip->getNameIndex($i);
					$exclude = false;
					foreach($this->exclude as $f)
					{
						if(strpos($file, $f) === 0)
						{
							$exclude = true;
						}
					}
					if(! $exclude)
					{
						$exclude = true;
						foreach($this->folders as $f)
						{
							if(strpos($file, $f) === 0)
							{
								$exclude = false;
								break;
							}
						}
					}
					if(! in_array($file, array('updrade.php', 'downgrade.php')) && ! $exclude && ! isset($files[$file]) && substr($file, -1) !== '/')
					{
						$files[$file] = $zip->getFromName($file);
					}
				}
				$zip->close();
			}
		}
		return $files;
	}

	/**
	 * Добавляет файлы из директории в архив
	 *
	 * @param object $zip архив
	 * @param string $dir относительный путь до директории
	 * @return void
	 */
	private function add_to_zip(&$zip, $dir)
	{
		if ($ddir = opendir(ABSOLUTE_PATH.$dir))
		{
			while (($file = readdir($ddir)) !== false)
			{
				if ($file != '.' && $file != '..' && ! in_array($dir.'/'.$file, $this->exclude))
				{
					if(is_dir(ABSOLUTE_PATH.$dir.'/'.$file))
					{
						$this->add_to_zip($zip, $dir.'/'.$file);
					}
					else
					{
						$zip->addFile(ABSOLUTE_PATH.$dir.'/'.$file, $dir.'/'.$file);
					}
				}
			}
			closedir($ddir);
		}
	}
}