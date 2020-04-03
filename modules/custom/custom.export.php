<?php
/**
 * Экспорт темы
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
 * Custom_export
 */
class Custom_export extends Diafan
{
	/**
	 * @var object объект для работы с ZIP-архивом
	 */
	private $zip;

	/**
	 * @var string путь до темы
	 */
	private $path;

	/**
	 * Инициирует скачивание темы
	 * 
	 * @return void
	 */
	public function init()
	{
		if(! $this->diafan->_users->roles("init", "custom", array(), 'admin'))
		{
			Custom::inc('includes/404.php');
		}
		
		if(defined('IS_DEMO') && IS_DEMO)
		{
			echo $this->diafan->_('В демонстрационном режиме эта функция не доступна.');
			exit;
		}
		
		if(! class_exists('ZipArchive'))
		{
			echo $this->diafan->_('Не доступно PHP-расширение ZipArchive. Обратитесь в техническую поддержку хостинга.');
			exit;
		}
		$rew = intval($_GET["rewrite"]);
		if($rew != $_GET["rewrite"])
		{
			Custom::inc('includes/404.php');
		}
		
		$row = DB::query_fetch_array("SELECT * FROM {custom} WHERE id=%d", $rew);
		if(! $row)
		{
			Custom::inc('includes/404.php');
		}
		if(! is_dir(ABSOLUTE_PATH.'custom/'.$row["name"]))
		{
			echo $this->diafan->_('Папка пуста.');
			exit;
		}
		
		$name = ABSOLUTE_PATH.'tmp/'.md5(mt_rand(0, 9999)).'.zip';
		$this->zip = new ZipArchive;
		if ($this->zip->open($name, ZipArchive::CREATE) === true)
		{
			$this->path = ABSOLUTE_PATH.'custom/'.$row["name"].'/';
			$this->folder('');
			$this->zip->close();

			$text = file_get_contents($name);
			unlink($name);

			$this->get_zip($row["name"], $text);
		}
	}

	/**
	 * Добавляет папку в архив
	 * 
	 * @return void
	 */
	private function folder($path)
	{
		//добавляем папку в архив
		$this->zip->addEmptyDir($path);
	
		if($dir = opendir($this->path.$path))
		{
			while (($file = readdir($dir)) !== false)
			{
				if($file == '.' || $file == '..')
					continue;

				if(is_dir($this->path.$path.'/'.$file))
				{
					$this->folder($path.'/'.$file);
				}
				else
				{
					$text = file_get_contents($this->path.$path.'/'.$file);
		
					// добавляем файл
					$this->zip->addFromString($path.'/'.$file, $text);
				}
			}
		}
	}

	/**
	 * Отдает ZIP-архив
	 * 
	 * @return void
	 */
	private function get_zip($name, $text)
	{		
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Cache-Control: max-age=86400');
		header("Content-type: application/zip");
		header("Content-Disposition: attachment; filename=DIAFAN.CMS.custom.".$name.".zip");
		header('Content-transfer-encoding: binary');
		header("Connection: close");

		echo $text;
	}
}

$class = new Custom_export($this->diafan);
$class->init();
exit;