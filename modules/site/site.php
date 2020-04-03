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
 * Site
 */
class Site extends Controller
{
	/**
	 * Шаблонная функция: выводит содержимое блока на сайте, номер которой передан в виде атрибута id.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * id - идентификатор блока
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/site/views/site.view.show_block_**template**.php; по умолчанию шаблон modules/site/views/site.view.show_block.php)
	 * 
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes($attributes, 'id', 'template');

		$attributes["id"] = intval($attributes["id"]);
		if(! $attributes["id"])
		{
			return;
		}

		if(! empty($this->diafan->_site->block_ids[$attributes["id"]]))
		{
			return;
		}
		if(empty($this->diafan->_site->block_ids))
		{
			$this->diafan->_site->block_ids = array();
		}
		$this->diafan->_site->block_ids[$attributes["id"]] = true;

		$result = $this->model->show_block($attributes["id"]);

		if (! $result)
			return;

		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block', 'site', $result, $attributes["template"]);
		
		$this->diafan->_site->block_ids[$attributes["id"]] = false;
	}

	/**
	 * Шаблонная функция: выводит содержимое динамического блока, номер которой передан в виде атрибута id.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * id - идентификатор динамического блока
	 * element_id - номер элемента, для которого будет выведено значение блока, по умолчанию текущий элемент
	 * module_name - модуль элемента, для которого будет выведено значение блока, по умолчанию текущий модуль
	 * element_type - тип элемента, для которого будет выведено значение блока, по умолчанию тип текущего элемента
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/site/views/site.view.show_dynamic_**template**.php; по умолчанию шаблон modules/site/views/site.view.show_dynamic.php)
	 * 
	 * @return void
	 */
	public function show_dynamic($attributes)
	{
		$this->get_attributes($attributes, 'id', 'template', 'element_id', 'module_name', 'element_type');

		$attributes["id"] = intval($attributes["id"]);
		$result = $this->model->show_dynamic($attributes["id"], $attributes["element_id"], $attributes["module_name"], $attributes["element_type"]);

		if (! $result)
			return;

		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_dynamic', 'site', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит ссылки на страницы нижнего уровня, принадлежащие текущей странице.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/site/views/site.view.show_links_**template**.php; по умолчанию шаблон modules/site/views/site.view.show_links.php)
	 * 
	 * @return void
	 */
	public function show_links($attributes)
	{
		$this->get_attributes($attributes, 'template');
		$result["rows"] = $this->model->show_links();
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_links', 'site', $result["rows"], $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит ссылки на предыдущую и последующую страницы.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/site/views/site.view.show_previous_next_**template**.php; по умолчанию шаблон modules/site/views/site.view.show_previous_next.php)
	 * 
	 * @return void
	 */
	public function show_previous_next($attributes)
	{
		$this->get_attributes($attributes, 'template');

		$result = $this->model->show_previous_next();
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_previous_next', 'site', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит изображения, прикрепленные к странице сайта, если в конфигурации модуля «Страницы сайта» включен параметры «Использовать изображения».
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/site/views/site.view.show_images_**template**.php; по умолчанию шаблон modules/site/views/site.view.show_images.php)
	 * 
	 * @return void
	 */
	public function show_images($attributes)
	{
		if (! $this->diafan->configmodules('images_element', 'site'))
		{
			return;
		}
		$this->get_attributes($attributes, 'template');

		$result = $this->model->show_images();
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_images', 'site', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит комментарии, прикрепленные к странице сайта, если в конфигурации модуля «Страницы сайты» подключены комментарии.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/site/views/site.view.show_comments_**template**.php; по умолчанию шаблон modules/site/views/site.view.show_comments.php)
	 * 
	 * @return void
	 */
	public function show_comments($attributes)
	{
		if ($this->diafan->_site->module)
		{
			return;
		}
		$this->get_attributes($attributes, 'template');

		$result["comments"] = $this->diafan->_comments->get($this->diafan->_site->id, 'site');

		if(! $result["comments"])
			return false;

		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_comments', 'site', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит теги (слова-якори), прикрепленные к странице сайта, если в конфигурации модуля «Страницы сайты» подключены теги.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/site/views/site.view.show_tags_**template**.php; по умолчанию шаблон modules/site/views/site.view.show_tags.php)
	 * 
	 * @return void
	 */
	public function show_tags($attributes)
	{
		$this->get_attributes($attributes, 'template');

		$result = $this->diafan->_tags->get($this->diafan->_site->id, "site");

		echo $this->diafan->_tpl->get('show_tags', 'site', $result, $attributes["template"]);
	}
}