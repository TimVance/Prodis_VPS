<?php
/**
 * Контроллер модуля «Список пожеланий»
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
 * Wishlist
 */
class Wishlist extends Controller
{
	/**
	 * Инициализация модуля
	 * 
	 * @return void
	 */
	public function init()
	{
		$this->model->form();
		$this->diafan->_site->hide_previous_next = true;
		$this->diafan->_site->timeedit = time();
	}

	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function action()
	{
		if($this->diafan->configmodules('security_user', 'shop'))
		{
			$this->action->check_user();

			if ($this->action->result())
				return;
		}
		if(! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'recalc':
					return $this->action->recalc();

				case 'buy':
					return $this->action->buy();
			}
		}
	}

	/**
	 * Шаблонная функция: выводит информацию о товарах в списке пожеланий.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/wishlist/views/wishlist.view.show_block_**template**.php; по умолчанию шаблон modules/wishlist/views/wishlist.view.show_block.php)
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes($attributes, 'template');

		$result = $this->model->show_block();
		if($result)
		{
			$result["attributes"] = $attributes;
			echo $this->diafan->_tpl->get('show_block', 'wishlist', $result, $attributes["template"]);
		}
	}
}