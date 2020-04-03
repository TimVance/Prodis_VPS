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
 * Subscription
 */
class Subscription extends Controller
{
	/**
	 * Инициализация модуля
	 * 
	 * @return void
	 */
	public function init()
	{
		if(empty($_GET["action"]))
		{
			$_GET["action"] = '';
		}
		if(empty($_GET["mail"]))
		{
			$_GET["action"] = 'form';
		}
		switch($_GET["action"])
		{
			case "form":
				$this->model->form();
				break;

			case "del":
				$this->model->del();
				break;

			default:
				$this->model->edit();
		}		
	}

	/**
	 * Шаблонная функция: выводит форму подписки на рассылки.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/subscription/views/subscription.view.form_**template**.php; по умолчанию шаблон modules/subscription/views/subscription.view.form.php)
	 * 
	 * @return void
	 */
	public function show_form($attributes)
	{
		$this->get_attributes($attributes, 'template');		

		$result = $this->model->form(true);
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('form', 'subscription', $result, $attributes["template"]);
	}
}