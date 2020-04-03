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

class Reviews extends Controller
{
	/**
	 * @var array переменные, передаваемые в URL страницы
	 */
	public $rewrite_variable_names = array('rpage');

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

				case 'upload_image':
					$this->action->upload_image();
					break;

				case 'delete_image':
					$this->action->delete_image();
					break;

				case 'show':
					$this->action->show();
					break;
			}
		}
	}

	/**
	 * Шаблонная функция: выводит отзывы и форму добавления отзывов для текущей страницы.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * modules - модули (разделенные запятой), к которым подключены отзывы
	 * element_types - типы данных (разделенные запятой): *element* – элемент (по умолчанию), *cat* – категория, *brand* – производитель. Если не задан, то подключается ко всем типам данных модуля
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/reviews/views/reviews.view.show_**template**.php; по умолчанию шаблон modules/reviews/views/reviews.view.show.php)
	 *
	 * @return void
	 */
	public function show($attributes)
	{
		$this->get_attributes($attributes, 'template', 'modules', 'element_type');

		$element_types  = $attributes["element_type"] ? explode(",", $attributes["element_type"]) : array('cat', 'element', 'brand');

		$modules = $attributes["modules"] ? explode(",", $attributes["modules"]) : array($this->diafan->_site->module, 'site');

		$element_type = 'element';
		if($this->diafan->_route->show && in_array('element', $element_types))
		{
			$element_id = $this->diafan->_route->show;
			$element_type = 'element';
		}
		elseif($this->diafan->_route->cat && in_array('cat', $element_types))
		{
			$element_id = $this->diafan->_route->cat;
			$element_type = 'cat';
		}
		elseif($this->diafan->_route->brand && in_array('brand', $element_types))
		{
			$element_id = $this->diafan->_route->brand;
			$element_type = 'brand';
		}

		if(in_array($this->diafan->_site->module, $modules) && $this->diafan->_site->module && ! empty($element_id))
		{
			$module_name = $this->diafan->_site->module;
		}
		elseif(in_array('site', $modules))
		{
			$element_id = $this->diafan->_site->id;
			$module_name = 'site';
		}

		if(empty($module_name))
		{
			return;
		}

		$result = $this->model->show($element_id, $module_name, $element_type);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show', 'reviews', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит последние отзывы.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых отзывов (по умолчанию 3)
	 * modules - модули (разделенные запятой), к которым подключены отзывы
	 * element_id - идентификаторы элементов модулей (разделенные запятой), к которым подключены отзывы
	 * element_type - тип данных (*element* – элемент (по умолчанию), *cat* – категория, *brand* – производитель)
	 * sort - сортировка отзывов: **date** – по дате (по умолчанию), **rand** – в случайном порядке
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/reviews/views/reviews.view.show_block_**template**.php; по умолчанию шаблон modules/reviews/views/reviews.view.show_block.php)
	 *
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes($attributes, 'count', 'modules', 'element_id', 'element_type', 'sort', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$element_ids  = explode(",", $attributes["element_id"]);
		$modules = explode(",", $attributes["modules"]);
		$element_type  = $attributes["element_type"] ? $attributes["element_type"] : 'element';
		$sort    = $attributes["sort"] == "date" || $attributes["sort"] == "rand" ? $attributes["sort"] : "date";

		$result = $this->model->show_block($count, $element_ids, $modules, $element_type, $sort);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block', 'reviews', $result, $attributes["template"]);
	}
}
