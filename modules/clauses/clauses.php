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
 * Clauses
 */
class Clauses extends Controller
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
		elseif (! $this->diafan->configmodules("cat") || (!$this->diafan->_route->cat && $this->diafan->configmodules("first_page_list")))
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
	 * Шаблонная функция: выводит последние статьи на всех страницах, кроме страницы статей, когда выводится список тех же статей, что и в функции.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых статей (по умолчанию 3)
	 * site_id - страницы, к которым прикреплен модуль. Идентификаторы страниц перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены статьи из указанного раздела. По умолчанию выбираются все страницы
	 * cat_id - категории статей, если в настройках модуля отмечено «Использовать категории». Идентификаторы категорий перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены статьи из указанной категории. Можно указать значение **current**, тогда будут показаны статьи из текущей (открытой) категории или из всех категорий, если ни одна категория не открыта. По умолчанию категория не учитывается, выводятся все статьи
	 * sort - сортировка статей: по умолчанию как на странице модуля, **date** – по дате, **rand** – в случайном порядке, **keywords** – статьи, похожие по названию для текущей страницы (должен быть подключен модуль «Поиск по сайту» и проиндексированы статьи)
	 * images - количество изображений, прикрепленных к статье
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * only_module - выводить блок только на странице, к которой прикреплен модуль «Статьи»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * tag - тег, прикрепленный к статьям
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/clauses/views/clauses.view.show_block_**template**.php; по умолчанию шаблон modules/clauses/views/clauses.view.show_block.php)
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes($attributes, 'count', 'site_id', 'cat_id', 'sort', 'images', 'images_variation', 'only_module', 'tag', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$site_ids = explode(",", $attributes["site_id"]);
		$cat_ids  = explode(",", $attributes["cat_id"]);
		$sort    = $attributes["sort"] == "date" || $attributes["sort"] == "rand" || $attributes["sort"] == "keywords" ? $attributes["sort"] : "";
		$images  = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';
		$tag = $attributes["tag"] && $this->diafan->configmodules('tags', 'clauses') ? strval($attributes["tag"]) : '';

		if ($attributes["only_module"] && ($this->diafan->_site->module != "clauses" || in_array($this->diafan->_site->id, $site_ids)))
			return;
		
		if($attributes["cat_id"] == "current")
		{
			if($this->diafan->_site->module == "clauses" && (empty($site_ids[0]) || in_array($this->diafan->_site->id, $site_ids))
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

		echo $this->diafan->_tpl->get('show_block', 'clauses', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: на странице статьи выводит похожие статьи. По умолчанию связи между статьями являются односторонними, это можно изменить, отметив опцию «В блоке похожих статей связь двусторонняя» в настройках модуля.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых статей (по умолчанию 3)
	 * images - количество изображений, прикрепленных к статье
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/clauses/views/clauses.view.show_block_rel_**template**.php; по умолчанию шаблон modules/clauses/views/clauses.view.show_block_rel.php)
	 * 
	 * @return void
	 */
	public function show_block_rel($attributes)
	{
		if ($this->diafan->_site->module != "clauses" || ! $this->diafan->_route->show)
			return;

		$this->get_attributes($attributes, 'count', 'images', 'images_variation', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$images  = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';

		$result = $this->model->show_block_rel($count, $images, $images_variation);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block_rel', 'clauses', $result, $attributes["template"]);
	}
}