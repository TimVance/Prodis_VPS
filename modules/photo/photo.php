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
 * Photo
 */
class Photo extends Controller
{
	/**
	 * @var array переменные, передаваемые в URL страницы
	 */
	public $rewrite_variable_names = array('page');

	/**
	 * Инициализация модуля
	 * 
	 * @return void
	 */
	public function init()
	{
		if ($this->diafan->configmodules('page_show'))
		{
			$this->rewrite_variable_names[] = 'show';
		}
		if($this->diafan->configmodules("cat"))
		{
			$this->rewrite_variable_names[] = 'cat';
		}

		if ($this->diafan->_route->show && $this->diafan->configmodules('page_show'))
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
	 * Шаблонная функция: выводит несколько фотографий.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых фотографий (по умолчанию 3)
	 * site_id - страницы, к которым прикреплен модуль. Идентификаторы страниц перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены фотографии из указанного раздела. По умолчанию выбираются все страницы
	 * cat_id - альбомы фотографий, если в настройках модуля отмечено «Использовать альбомы». Идентификаторы альбомов перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены фотографии из указанной категории. Можно указать значение **current**, тогда будут показаны фотографии из текущей (открытой) категории или из всех категорий, если ни одна категория не открыта. По умолчанию альбом не учитывается, выводятся все фотографии
	 * sort - сортировка фотографий: по умолчанию как на странице модуля, **date** – по дате, **rand** – в случайном порядке
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * only_module - выводить блок только на странице, к которой прикреплен модуль «Фотогалерея»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * tag - тег, прикрепленный к фотографиям
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/photo/views/photo.view.show_block_**template**.php; по умолчанию шаблон modules/photo/views/photo.view.show_block.php)
	 * 
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes($attributes, 'count', 'site_id', 'cat_id', 'sort', 'images_variation', 'only_module', 'tag', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$site_ids = explode(",", $attributes["site_id"]);
		$cat_ids  = explode(",", $attributes["cat_id"]);
		$sort    = $attributes["sort"] == "date" || $attributes["sort"] == "rand" ? $attributes["sort"] : "";
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';
		$tag = $attributes["tag"] && $this->diafan->configmodules('tags', 'photo') ? strval($attributes["tag"]) : '';

		if ($attributes["only_module"] && ($this->diafan->_site->module != "photo" || in_array($this->diafan->_site->id, $site_ids)))
			return;
		
		if($attributes["cat_id"] == "current")
		{
			if($this->diafan->_site->module == "photo" && (empty($site_ids[0]) || in_array($this->diafan->_site->id, $site_ids))
			   && $this->diafan->_route->cat)
			{
				$cat_ids[0] = $this->diafan->_route->cat;
			}
			else
			{
				$cat_ids = array();
			}
		}

		$result = $this->model->show_block($count, $site_ids, $cat_ids, $sort, $images_variation, $tag);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block', 'photo', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: на странице фотографии выводит похожие фотографии. По умолчанию связи между фотографиями являются односторонними, это можно изменить, отметив опцию «В блоке похожих фотографий связь двусторонняя» в настройках модуля.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых фотографий (по умолчанию 3)
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/photo/views/photo.view.show_block_rel_**template**.php; по умолчанию шаблон modules/photo/views/photo.view.show_block_rel.php)
	 * 
	 * @return void
	 */
	public function show_block_rel($attributes)
	{
		if ($this->diafan->_site->module != "photo" || ! $this->diafan->_route->show)
			return;

		$this->get_attributes($attributes, 'count', 'images_variation', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';

		$result = $this->model->show_block_rel($count, $images_variation);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block_rel', 'photo', $result, $attributes["template"]);
	}
}