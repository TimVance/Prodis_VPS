<?php
/**
 * Контроллер
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
 * Bs
 */
class Bs extends Controller
{
	/**
	 * Шаблонная функция: выводит баннеры
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых баннеров. По умолчанию 1. Значение **all** выведет все баннеры
	 * id - идентификатор баннера, если задан, атрибут **count** игнорируется
	 * sort - сортировка баннеров: по умолчанию как в панели администрирования, **date** – по дате, **rand** – в случайном порядке
	 * cat_id - категория баннеров, если в настройках модуля отмечено «Использовать категории»
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/bs/views/bs.view.show_block_**template**.php; по умолчанию шаблон modules/bs/views/bs.view.show_block.php)
	 * 
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes($attributes, 'count', 'id', 'sort', 'cat_id', 'template');
		
		$id   = intval($attributes["id"]);
		$sort = (in_array($attributes["sort"], array("date", "rand")) ? $attributes["sort"] : '');
		if($attributes["count"] === "all")
		{
			$count = "all";
		}
		else
		{
			$count   = intval($attributes["count"]);
			if($count < 1)
			{
				$count = 1;
			}
		}
		$cat_id  = intval($attributes["cat_id"]);

		$result = $this->model->show_block($id, $count, $sort, $cat_id);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block', 'bs', $result["rows"], $attributes["template"]);
	}
}