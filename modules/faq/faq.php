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
 * Faq
 */
class Faq extends Controller
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
		$this->model->result["form"] = $this->model->form();
	}

	/**
	 * Шаблонная функция: выводит последние вопросы. На странице вопросов, когда выводится список аналогичных вопросов, блок не выводится.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых вопросов (по умолчанию 3)
	 * site_id - страницы, к которым прикреплен модуль. Идентификаторы страниц перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены вопросы из указанного раздела. По умолчанию выбираются все страницы
	 * cat_id - категории вопросов, если в настройках модуля отмечено «Использовать категории». Идентификаторы категорий перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены вопросы из указанной категории. Можно указать значение **current**, тогда будут показаны вопросы из текущей (открытой) категории или из всех категорий, если ни одна категория не открыта. По умолчанию категория не учитывается, выводятся все вопросы
	 * sort - сортировка вопросов: **date** – по дате (по умолчанию), **rand** – в случайном порядке
	 * often - часто задаваемые вопросы : **true** – выводятся только вопросы с пометкой «Часто задаваемый вопрос», по умолчанию пометка «Часто задаваемый вопрос» игнорируется
	 * only_module - выводить блок только на странице, к которой прикреплен модуль «Вопрос-Ответ»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * tag - тег, прикрепленный к вопросам
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/faq/views/faq.view.show_block_**template**.php; по умолчанию шаблон modules/faq/views/faq.view.show_block.php)
	 * 
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes($attributes, 'count', 'site_id', 'cat_id', 'sort', 'often', 'only_module', 'tag', 'template');
		
		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$cat_ids  = explode(",", $attributes["cat_id"]);
		$site_ids = explode(",", $attributes["site_id"]);
		$sort    = $attributes["sort"] == "date" || $attributes["sort"] == "rand" ? $attributes["sort"] : "date";
		$often   = $attributes["often"] ? true : false;
		$tag = $attributes["tag"] && $this->diafan->configmodules('tags', 'faq') ? strval($attributes["tag"]) : '';

		if ($attributes["only_module"] && ($this->diafan->_site->module != "faq" || in_array($this->diafan->_site->id, $site_ids)))
			return;
		
		if($attributes["cat_id"] == "current")
		{
			if($this->diafan->_site->module == "faq" && (empty($site_ids[0]) || in_array($this->diafan->_site->id, $site_ids))
			   && $this->diafan->_route->cat)
			{
				$cat_ids[0] = $this->diafan->_route->cat;
			}
			else
			{
				$cat_ids = array();
			}
		}

		$result = $this->model->show_block($count, $site_ids, $cat_ids, $sort, $often, $tag);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block', 'faq', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: на странице вопроса выводит похожие вопросы. По умолчанию связи между вопросами являются односторонними, это можно изменить, отметив опцию «В блоке похожих вопросов связь двусторонняя» в настройках модуля.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых вопросов (по умолчанию 3)
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/faq/views/faq.view.show_block_rel_**template**.php; по умолчанию шаблон modules/faq/views/faq.view.show_block_rel.php)
	 * 
	 * @return void
	 */
	public function show_block_rel($attributes)
	{
		if ($this->diafan->_site->module != "faq" || ! $this->diafan->_route->show)
			return;

		$this->get_attributes($attributes, 'count', 'template', 'only_module');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;

		$result = $this->model->show_block_rel($count);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block_rel', 'faq', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит форму добавления вопроса. Для правильной работы тега должна существовать страница, к которой прикреплен модуль Вопрос-Ответ.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * site_id - страница, к которой прикреплен модуль, по умолчанию выбирается одна страница
	 * cat_id - категория вопросов (id категории, по умолчанию выбирается одна категория), если в настройках модуля отмечено «Использовать категории»
	 * only_module - выводить форму только на странице, к которой прикреплен модуль «Вопрос-Ответ»: **true** – выводить форму только на странице модуля, по умолчанию форма будет выводиться на всех страницах
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/faq/views/faq.view.form_**template**.php; по умолчанию шаблон modules/faq/views/faq.view.form.php)
	 * 
	 * @return void
	 */
	public function show_form($attributes)
	{
		$this->get_attributes($attributes, 'site_id', 'cat_id', 'template', 'only_module');

		$site_id = intval($attributes["site_id"]);
		$cat_id  = intval($attributes["cat_id"]);

		if ($attributes["only_module"] && ($this->diafan->_site->module != "faq" || $site_id && $this->diafan->_site->id != $site_id))
			return;

		$result = $this->model->form($site_id, $cat_id, true);
		$result["attributes"] = $attributes;
		if (! empty($result))
		{
			echo $this->diafan->_tpl->get('form', 'faq', $result, $attributes["template"]);
		}
	}
}