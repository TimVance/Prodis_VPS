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
 * Ab
 */
class Ab extends Controller
{
	/**
	 * @var array переменные, передаваемые в URL страницы
	 */
	public $rewrite_variable_names = array('page', 'show', 'year', 'month', 'day', 'param', 'edit', 'sort');

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
			if($this->diafan->_route->page || $this->diafan->_route->param || $this->diafan->_route->sort || $this->diafan->_route->edit || $this->diafan->_route->year || $this->diafan->_route->month || $this->diafan->_route->day)
			{
				Custom::inc('includes/404.php');
			}
			$this->model->id();
		}
		elseif ($this->diafan->_route->edit)
		{
			$this->model->edit();
		}
		elseif ($this->diafan->_route->param)
		{
			$this->model->list_param();
		}
		elseif(isset($_GET["action"]))
		{
			switch($_GET["action"])
			{
				case 'search':
					$this->model->list_search();
					break;

				case 'my':
					$this->model->list_my();
					break;

				case 'block':
					$this->action->block();
					break;

				case 'delete':
					$this->action->delete();
					break;

				default:
					Custom::inc('includes/404.php');
			}
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
		$this->model->result["form"] = $this->model->form();
	}

	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function action()
	{
		if(! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'add':
					$this->action->add();
					break;

				case 'edit':
					$this->action->edit();
					break;

				case 'upload_image':
					$this->action->upload_image();
					break;

				case 'delete_image':
					$this->action->delete_image();
					break;

				case 'search':
					$this->action->search();
					break;
			}
		}
	}

	/**
	 * Шаблонная функция: выводит последние объявления на всех страницах, кроме страницы объявлений, когда выводится список тех же объявлений, что и в функции.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых объявлений (по умолчанию 3)
	 * site_id - страницы, к которым прикреплен модуль. Идентификаторы страниц перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены объявления из указанного раздела. По умолчанию выбираются все страницы
	 * cat_id - категории объявлений, если в настройках модуля отмечено «Использовать категории». Идентификаторы категорий перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены объявления из указанной категории. Можно указать значение **current**, тогда будут показаны объявления из текущей (открытой) категории или из всех категорий, если ни одна категория не открыта. По умолчанию категория не учитывается, выводятся все объявления
	 * sort - сортировка объявлений: **date** – по дате (по умолчанию), **rand** – в случайном порядке
	 * images - количество изображений, прикрепленных к объявления
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * param - значения дополнительных характеристик
	 * only_module - выводить блок только на странице, к которой прикреплен модуль «Объявления»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * tag - тег, прикрепленный к объявлениям
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/ab/views/ab.view.show_block_**template**.php; по умолчанию шаблон modules/ab/views/ab.view.show_block.php)
	 * 
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes($attributes, 'count', 'site_id', 'cat_id', 'sort', 'images', 'images_variation', 'param', 'only_module', 'only_ab', 'tag', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$site_ids = explode(",", $attributes["site_id"]);
		$cat_ids  = explode(",", $attributes["cat_id"]);
		$sort    = in_array($attributes["sort"], array("date", "rand")) ? $attributes["sort"] : "date";
		$images  = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';
		$param   = $attributes["param"];
		$tag = $attributes["tag"] && $this->diafan->configmodules('tags', 'ab') ? strval($attributes["tag"]) : '';

		// устаревший атрибут
		if($attributes["only_ab"])
		{
			$attributes["only_module"] = true;
		}

		if ($attributes["only_module"] && ($this->diafan->_site->module != "ab" || ! in_array($this->diafan->_site->id, $site_ids)))
			return;
		
		if($attributes["cat_id"] == "current")
		{
			if($this->diafan->_site->module == "ab" && (empty($site_ids[0]) || in_array($this->diafan->_site->id, $site_ids))
			   && $this->diafan->_route->cat)
			{
				$cat_ids[0] = $this->diafan->_route->cat;
			}
			else
			{
				$cat_ids = array();
			}
		}

		$result = $this->model->show_block($count, $site_ids, $cat_ids, $sort, $images, $images_variation, $param, $tag);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block', 'ab', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: на странице объявления выводит похожие объявления. По умолчанию связи между объявлениями являются односторонними, это можно изменить, отметив опцию «В блоке похожих объявлений связь двусторонняя» в настройках модуля.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых объявлений (по умолчанию 3)
	 * images - количество изображений, прикрепленных к объявления
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/ab/views/ab.view.show_block_rel_**template**.php; по умолчанию шаблон modules/ab/views/ab.view.show_block_rel.php)
	 *  
	 * @return void
	 */
	public function show_block_rel($attributes)
	{
		if ($this->diafan->_site->module != "ab" || ! $this->diafan->_route->show)
			return false;

		$this->get_attributes($attributes, 'count', 'images', 'images_variation', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$images  = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';

		$result = $this->model->show_block_rel($count, $images, $images_variation);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block_rel', 'ab', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит форму поиска объявлений. Если для категорий прикреплены дополнительные характеристики, то поиск по ним производится только на странице категории.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * site_id - страницы, к которым прикреплен модуль. Идентификаторы страниц перечисляются через запятую. По умолчанию выбираются все страницы. Если выбрано несколько страниц сайта, то в форме поиска появляется выпадающих список по выбранным страницам. Можно указать отрицательное значение, тогда указанные страницы будут исключены из списка
	 * cat_id - категории объявлений, если в настройках модуля отмечено «Использовать категории». Идентификаторы категорий перечисляются через запятую. Можно указать значение **current**, тогда поиск будет осуществляться по текущей (открытой) категории или по всем категориям, если ни одна категория не открыта. Если выбрано несколько категорий, то в форме поиска появится выпадающий список категорий, который будет подгружать прикрепленные к категориям характеристики. Можно указать отрицательное значение, тогда указанные категории будут исключены из списка. Можно указать значение **all**, тогда поиск будет осуществлятся по всем категориям объявлений и в форме будут участвовать только общие характеристики. Атрибут не обязателен
	 * ajax - подгружать результаты поиска без перезагрузки страницы. Результаты подгружаются только если открыта страница со списком объявлений, иначе поиск работает обычным образом: **true** – результаты поиска подгружаются, по умолчанию будет перезагружена вся страница.
	 * only_module - выводить блок только на странице, к которой прикреплен модуль «Объявления»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/ab/views/ab.view.show_search_**template**.php; по умолчанию шаблон modules/ab/views/ab.view.show_search.php)
	 * 
	 * @return void
	 */
	public function show_search($attributes)
	{
		$this->get_attributes($attributes, 'site_id', 'cat_id', 'ajax', 'only_module', 'only_ab', 'template');

		$site_ids  = explode(",", $attributes["site_id"]);
		$cat_ids   = $attributes["cat_id"] === 'current' || $attributes["cat_id"] === 'all' ? $attributes["cat_id"] : explode(",", $attributes["cat_id"]);
		$ajax     = $attributes["ajax"] == "true" ? true : false;

		if ($cat_ids === 'current')
		{
			if($this->diafan->_route->cat && $this->diafan->_site->module == "ab" && (count($site_ids) == 1 && $site_ids[0] == 0 || in_array($this->diafan->_site->id, $site_ids)))
			{
				$cat_ids  = array($this->diafan->_route->cat);
				$site_ids = array($this->diafan->_site->id);
			}
			else
			{
				$cat_ids = array();
			}
		}

		// устаревший атрибут
		if($attributes["only_ab"])
		{
			$attributes["only_module"] = true;
		}

		if ($attributes["only_module"] && ($this->diafan->_site->module != "ab" || $site_ids && ! in_array($this->diafan->_site->id, $site_ids)))
			return;

		$result = $this->model->show_search($site_ids, $cat_ids, $ajax);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_search', 'ab', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит форму добавления сообщения. Для правильной работы тега должна существовать страница, к которой прикреплен модуль Объявления.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * site_id - страницы, к которым прикреплен модуль. Идентификаторы страниц перечисляются через запятую. По умолчанию выбираются все страницы. Если задано несколько страниц, то в форме появляется выпадающий список «Раздел»
	 * cat_id - категории объявлений, если в настройках модуля отмечено «Использовать категории». Идентификаторы категорий перечисляются через запятую. Можно указать значение **current**, тогда форма будет добавлять объявление в текущую (открытую) категорию или выводить поле «Категория», если ни одна категория не открыта. Если задано несколько категорий, то в форме появляется выпадающий список «Категория»
	 * only_module - выводить форму только на странице, к которой прикреплен модуль «Объявления»: **true** – выводить форму только на странице модуля, по умолчанию форма будет выводиться на всех страницах
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/ab/views/ab.view.form_**template**.php; по умолчанию шаблон modules/ab/views/ab.view.form.php)
	 * 
	 * @return void
	 */
	public function show_form($attributes)
	{
		$this->get_attributes($attributes, 'site_id', 'cat_id', 'only_module', 'only_ab', 'template');

		$site_ids  = explode(",", $attributes["site_id"]);
		$cat_ids   = $attributes["cat_id"] === 'current' ? $attributes["cat_id"] : explode(",", $attributes["cat_id"]);

		if ($cat_ids === 'current')
		{
			if($this->diafan->_route->cat && $this->diafan->_site->module == "ab" && (count($site_ids) == 1 && $site_ids[0] == 0 || in_array($this->diafan->_site->id, $site_ids)))
			{
				$cat_ids  = array($this->diafan->_route->cat);
				$site_ids = array($this->diafan->_site->id);
			}
			else
			{
				$cat_ids = array();
			}
		}

		// устаревший атрибут
		if($attributes["only_ab"])
		{
			$attributes["only_module"] = true;
		}

		if ($attributes["only_module"] && ($this->diafan->_site->module != "ab" || $site_ids && ! in_array($this->diafan->_site->id, $site_ids)))
			return;

		$result = $this->model->form($site_ids, $cat_ids, true);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('form', 'ab', $result, $attributes["template"]);
	}
}