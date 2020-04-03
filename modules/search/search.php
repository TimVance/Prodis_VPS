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
 * Search
 */
class Search extends Controller
{
	/**
	 * Инициализация модуля
	 * 
	 * @return void
	 */
	public function init()
	{
	   $this->rewrite_variable_names = array('page');
	   $this->model->show_module();
	}

	/**
	 * Шаблонная функция: выводит форму поиска по сайту.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * button - значение кнопки «Найти». Для неосновной языковой версии значение можно перевести в административной части в меню «Языки сайта» – «Перевод интерфейса»
	 * ajax - подгружать результаты поиска без перезагрузки страницы.: **true** – результаты поиска подгружаются, по умолчанию будет перезагружена вся страница.
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/search/views/search.view.show_search_**template**.php; по умолчанию шаблон modules/search/views/search.view.show_search.php)
	 * 
	 * @return void
	 */
	public function show_search($attributes)
	{
		$this->get_attributes($attributes, 'button', 'ajax', 'template');
		
		$button = $this->diafan->_($attributes["button"], false);
		$ajax   = $attributes["ajax"] == "true" ? true : false;
		$result = $this->model->show_search($button, $ajax);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_search', 'search', $result, $attributes["template"]);
	}
}