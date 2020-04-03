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
 * Tags
 */
class Tags extends Controller
{
	/**
	 * Инициализация модуля
	 * 
	 * @return void
	 */
	public function init()
	{
		$this->rewrite_variable_names = array('page', 'show');
		if (! $this->diafan->_route->show)
		{
			$this->model->show_block(true);
		}
		else
		{
			$this->model->list_module();
		}

		if (! empty($this->result["title"]))
		{
			$this->diafan->_site->name = $this->result["title"];
		}
	}

	/**
	 * Шаблонная функция: выводит список тегов.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/tags/views/tags.view.show_block_**template**.php; по умолчанию шаблон modules/tags/views/tags.view.show_block.php)
	 * 
	 * @return void
	 */
	public function show_block($attributes)
	{
		if ($this->diafan->_site->module == "tags" && ! $this->diafan->_route->show)
			return;

		$this->get_attributes($attributes, 'template');
		$result = $this->model->show_block();
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block', 'tags', $result, $attributes["template"]);
	}
}