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
 * Files
 */
class Files extends Controller
{
	/**
	 * @var array переменные, передаваемые в URL страницы
	 */
	public $rewrite_variable_names = array('page', 'show');

	/**
	 * Инициализация модуля
	 * 
	 * @return void
	 */
	public function init()
	{
		if($this->diafan->configmodules("cat"))
		{
			$this->rewrite_variable_names[] = 'cat';
		}

		if ($this->diafan->_route->show)
		{
			if($this->diafan->_route->page)
			{
				Custom::inc('includes/404.php');
			}
			$this->model->id();
		}
		elseif (! $this->diafan->configmodules("cat"))
		{
			$this->model->list_();
		}
		elseif (! $this->diafan->_route->cat)
		{
			$this->model->first_page();
		}
		else
		{
			$this->model->list_category();
		}
	}

	/**
	 * Шаблонная функция: выводит несколько файлов из файлового архива.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых файлов (по умолчанию 3)
	 * site_id - страницы, к которым прикреплен модуль. Идентификаторы страниц перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены  файлы из указанного раздела. По умолчанию выбираются все страницы
	 * cat_id - категории файлов, если в настройках модуля отмечено «Использовать категории». Идентификаторы категорий перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены файлы из указанной категории. Можно указать значение **current**, тогда будут показаны файлы из текущей (открытой) категории или из всех категорий, если ни одна категория не открыта. По умолчанию категория не учитывается, выводятся все файлы
	 * sort - сортировка файлов: по умолчанию как на странице модуля, **rand** – в случайном порядке, **date** – по дате
	 * images - количество изображений, прикрепленных к файлу
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * only_module - выводить блок только на странице, к которой прикреплен модуль «Файловый архив»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * tag - тег, прикрепленный к файлам
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/files/views/files.view.show_block_**template**.php; по умолчанию шаблон modules/files/views/files.view.show_block.php)
	 * 
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes($attributes, 'count', 'site_id', 'cat_id', 'sort', 'images', 'images_variation', 'only_module', 'tag', 'template');

		$count      = $attributes["count"] ? intval($attributes["count"]) : 3;
		$site_ids   = explode(",", $attributes["site_id"]);
		$cat_ids    = explode(",", $attributes["cat_id"]);
		$sort       = $attributes["sort"] == "date" || $attributes["sort"] == "rand" ? $attributes["sort"] : "";
		$images      = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';
		$tag = $attributes["tag"] && $this->diafan->configmodules('tags', 'files') ? strval($attributes["tag"]) : '';

		if ($attributes["only_module"] && ($this->diafan->_site->module != "files" || in_array($this->diafan->_site->id, $site_ids)))
			return;
		
		if($attributes["cat_id"] == "current")
		{
			if($this->diafan->_site->module == "files" && (empty($site_ids[0]) || in_array($this->diafan->_site->id, $site_ids))
			   && $this->diafan->_route->cat)
			{
				$cat_ids[0] = $this->diafan->_route->cat;
			}
			else
			{
				$cat_ids = array();
			}
		}

		$result = $this->model->show_block($count, $site_ids, $cat_ids, $sort, $images, $images_variation, $tag);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block', 'files', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: на странице файлы выводит похожие файлы. По умолчанию связи между файлами являются односторонними, это можно изменить, отметив опцию «В блоке похожих файлов связь двусторонняя» в настройках модуля.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых файлов (по умолчанию 3)
	 * images - количество изображений, прикрепленных к файлу
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/files/views/files.view.show_block_rel_**template**.php; по умолчанию шаблон modules/files/views/files.view.show_block_rel.php)
	 * 
	 * @return void
	 */
	public function show_block_rel($attributes)
	{
		if ($this->diafan->_site->module != "files" || ! $this->diafan->_route->show)
			return;

		$this->get_attributes($attributes, 'count', 'images', 'images_variation', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$images  = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';

		$result = $this->model->show_block_rel($count, $images, $images_variation);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block_rel', 'files', $result, $attributes["template"]);
	}
}