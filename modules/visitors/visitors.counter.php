<?php
/**
 * Сохранение значений для модуля «Посещаемость»
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
 * Visitors_counter
 */
class Visitors_counter extends Diafan
{
	/**
	 * Инициирует сохранение значений
	 *
	 * @return void
	 */
	public function init()
	{
		if(in_array('visitors', $this->diafan->installed_modules))
		{
			if(! empty($_POST))
			{
				$this->diafan->_visitors->counter_set($_POST);
			}
			elseif(! empty($_GET) && ! empty($_GET["watch"]))
			{
				switch($_GET["watch"])
				{
					case 'no_script':
						$this->no_script();
						break;
				}
			}
		}
	}

	/**
	 * Обрабатывает полученные данные при блокировки исполнения JavaScript на стороне пользовательского агента
	 *
	 * @return void
	 */
	private function no_script()
	{
		if(! in_array('visitors', $this->diafan->installed_modules))
		{
			return false;
		}

		$this->diafan->_visitors->counter_init(true);
		// TO_DO: Прозрачный 1x1 PNG
		// header('Content-Type: image/png');
		// echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
		// TO_DO: Прозрачный 1x1 GIF
		header('Content-Type: image/gif');
		echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
	}
}

$class = new Visitors_counter($this->diafan);
$class->init();
exit;
