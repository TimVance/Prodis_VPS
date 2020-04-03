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
 * News
 */
class News extends Controller
{
	/**
	 * @var array переменные, передаваемые в URL страницы
	 */
	public $rewrite_variable_names = array('page', 'show', 'year', 'month', 'day');

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
			if($this->diafan->_route->page || $this->diafan->_route->year || $this->diafan->_route->month || $this->diafan->_route->day)
			{
				Custom::inc('includes/404.php');
			}
			$this->model->id();
		}
		elseif (! $this->diafan->configmodules("cat") || $this->diafan->_route->year || $this->diafan->_route->month)
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
	 * Шаблонная функция: выводит последние новости на всех страницах, кроме страницы новостей, когда выводится список тех же новостей, что и в функции.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых новостей (по умолчанию 3)
	 * site_id - страницы, к которым прикреплен модуль. Идентификаторы страниц перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены новости из указанного раздела. По умолчанию выбираются все страницы
	 * cat_id - категории новостей, если в настройках модуля отмечено «Использовать категории». Идентификаторы категорий перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены новости из указанной категории. Можно указать значение **current**, тогда будут показаны новости из текущей (открытой) категории или из всех категорий, если ни одна категория не открыта. По умолчанию категория не учитывается, выводятся все новости
	 * sort - сортировка новостей: **date** – по дате (по умолчанию), **keywords** – новости, похожие по названию для текущей страницы (должен быть подключен модуль «Поиск по сайту» и проиндексированы новости)
	 * images - количество изображений, прикрепленных к новости
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * only_module - выводить блок только на странице, к которой прикреплен модуль «Новости»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * tag - тег, прикрепленный к новостям
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/news/views/news.view.show_block_**template**.php; по умолчанию шаблон modules/news/views/news.view.show_block.php)
	 * 
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes($attributes, 'count', 'site_id', 'cat_id', 'sort', 'images', 'images_variation', 'only_module', 'tag', 'template');
		
		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$site_ids = explode(",", $attributes["site_id"]);
		$cat_ids  = explode(",", $attributes["cat_id"]);
		$sort    = $attributes["sort"] == "date" || $attributes["sort"] == "keywords" ? $attributes["sort"] : "";
		$images   = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';
		$tag = $attributes["tag"] && $this->diafan->configmodules('tags', 'news') ? strval($attributes["tag"]) : '';

		if ($attributes["only_module"] && ($this->diafan->_site->module != "news" || in_array($this->diafan->_site->id, $site_ids)))
			return;
		
		if($attributes["cat_id"] == "current")
		{
			if($this->diafan->_site->module == "news" && (empty($site_ids[0]) || in_array($this->diafan->_site->id, $site_ids))
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

		echo $this->diafan->_tpl->get('show_block', 'news', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: на странице новости выводит похожие новости. По умолчанию связи между новостями являются односторонними, это можно изменить, отметив опцию «В блоке похожих новостей связь двусторонняя» в настройках модуля.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых новостей (по умолчанию 3)
	 * images - количество изображений, прикрепленных к новости
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/news/views/news.view.show_block_rel_**template**.php; по умолчанию шаблон modules/news/views/news.view.show_block_rel.php)
	 * 
	 * @return void
	 */
	public function show_block_rel($attributes)
	{
		if ($this->diafan->_site->module != "news" || ! $this->diafan->_route->show)
			return;

		$this->get_attributes($attributes, 'count', 'images', 'images_variation', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$images  = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';

		$result = $this->model->show_block_rel($count, $images, $images_variation);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block_rel', 'news', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит календарь со ссылками на новости за период. Периоды отображаются в виде ссылок на месяцы, только если имеются новости, соответствующие этим периодам.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * site_id - страница, к которой прикреплен модуль, по умолчанию выбирается одна страница
	 * cat_id - категория новостей (id категории, по умолчанию учитываются все новости), если в настройках модуля отмечено «Использовать категории»
	 * detail - детализация (**day** – дни, **month** – месяца (по умолчанию), **year** – годы)
	 * only_module - выводить блок только на странице «Новости»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/news/views/news.view.show_calendar_**template**.php или modules/news/views/news.view.show_calendar_day_**template**.php для детализации по дням; по умолчанию шаблон modules/news/views/news.view.show_calendar.php или modules/news/views/news.view.show_calendar_day.php для детализации по дням)
	 * 
	 * @return void
	 */
	public function show_calendar($attributes)
	{
		$this->get_attributes($attributes, 'site_id', 'cat_id', 'detail', 'only_module', 'only_news', 'template');

		$cat_id  = $attributes["cat_id"];
		$site_id = $attributes["site_id"];
		$detail = in_array($attributes["detail"], array("day", "month", "year")) ? $attributes["detail"] : "month";
		$template = $attributes["template"];

		// устаревший атрибут
		if($attributes["only_news"])
		{
			$attributes["only_module"] = true;
		}

		if ($attributes["only_module"] && ($site_id && $this->diafan->_site->id != $site_id || $this->diafan->_site->module != "news"))
		{
			return false;
		}

		$result = $this->model->show_calendar($detail, $site_id, $cat_id, $template);
		if (! $result)
		{
			return;
		}
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_calendar'.($detail == "day" ? '_day' : ''), 'news', $result, $attributes["template"]);
	}
}