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
 * Comments
 */
class Comments extends Controller
{
	/**
	 * @var array переменные, передаваемые в URL страницы
	 */
	public $rewrite_variable_names = array('dpage');

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

				case 'get':
					$this->action->get();
					break;
			}
		}
	}

	/**
	 * Шаблонная функция: выводит последние комментарии.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых комментариев (по умолчанию 3)
	 * modules - модули (разделенные запятой), к которым подключены комментарии
	 * element_id - идентификаторы элементов модулей (разделенные запятой), к которым подключены комментарии
	 * element_type - тип данных (*element* – элемент (по умолчанию), *cat* – категория)
	 * sort - сортировка комментариев: **date** – по дате (по умолчанию), **rand** – в случайном порядке
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/comments/views/comments.view.show_block_**template**.php; по умолчанию шаблон modules/comments/views/comments.view.show_block.php)
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

		echo $this->diafan->_tpl->get('show_block', 'comments', $result, $attributes["template"]);
	}
}
