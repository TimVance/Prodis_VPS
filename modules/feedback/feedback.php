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
 * Feedback
 */
class Feedback extends Controller
{
	/**
	 * Инициализация модуля
	 * 
	 * @return void
	 */
	public function init()
	{
		$this->result = $this->model->form();
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
					return $this->action->add();

				case 'upload_image':
					return $this->action->upload_image();

				case 'delete_image':
					return $this->action->delete_image();
			}
		}
	}

	/**
	 * Шаблонная функция: выводит форму добавления сообщения. Для правильной работы тега должна существовать страница, к которой прикреплен модуль Обратная связь.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * site_id - страница, к которой прикреплен модуль, по умолчанию выбирается одна страница
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/feedback/views/feedback.view.form_**template**.php; по умолчанию шаблон modules/feedback/views/feedback.view.form.php)
	 * 
	 * @return void
	 */
	public function show_form($attributes)
	{
		$this->get_attributes($attributes, 'site_id', 'template');

		$site_id = intval($attributes["site_id"]);

		$result = $this->model->form($site_id, true);
		if ($result)
		{
			$result["attributes"] = $attributes;
			echo $this->diafan->_tpl->get('form', 'feedback', $result, $attributes["template"]);
		}
	}
}