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
 * Forum
 */
class Forum extends Controller
{
	/**
	 * Инициализация модуля
	 * 
	 * @return void
	 */
	public function init()
	{
		$this->rewrite_variable_names = array('add', 'show', 'cat', 'edit', 'page');

		$this->model->moderator = $this->diafan->_users->roles('moderator', 'forum', '', 'site');

		if ($this->diafan->_route->add)
		{
			$this->model->add();	
		}
		elseif ($this->diafan->_route->show)
		{
			if($this->diafan->_route->page || $this->diafan->_route->add || $this->diafan->_route->cat || $this->diafan->_route->edit)
			{
				Custom::inc('includes/404.php');
			}
			$this->model->id();	
		}
		elseif ($this->diafan->_route->edit)
		{
			$this->model->edit();	
		}
		elseif (! empty($_GET["searchword"]))
		{
			$this->model->list_search();	
		}
		elseif (! empty($_GET["action"]) && $_GET["action"] == 'news')
		{
			$this->model->list_new();	
		}
		elseif ($this->diafan->_route->cat)
		{
			$this->model->list_category();	
		}
		else
		{
			$this->model->first_page();	
		}
	}

	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function action()
	{
		if($this->diafan->_site->module != 'forum')
			return;

		$this->action->moderator = $this->diafan->_users->roles('moderator', 'forum', '', 'site');

		if(! empty($_POST["action"]) && $_POST["action"] == "list_category")
		{
			return $this->action->list_category();
		}
		
		if ($this->diafan->configmodules("only_user", "forum"))
		{
			$this->action->check_user();
		}
		$this->action->check_user_hash();
		
		if($this->action->result())
			return;

		if(! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case "save":
					return $this->action->save();
	
				case "savenew":
					return $this->action->savenew();
	
				case "delete":
					return $this->action->delete();
	
				case "block":
					return $this->action->block(true);
	
				case "unblock":
					return $this->action->block(false);
	
				case "close":
					return $this->action->close(true);
	
				case "open":
					return $this->action->close(false);
	
				case "prior":
					return $this->action->prior(true);
	
				case "unprior":
					return $this->action->prior(false);

				case "upload_message":
					return $this->action->upload_message();

				case "save_message":
					return $this->action->save_message();

				case "delete_message":
					return $this->action->delete_message();

				case "edit_message":
					return $this->action->edit_message();

				case "block_message":
					return $this->action->block_message();

				case "delete_attachment":
					return $this->action->delete_attachment();
			}
		}
	}

	/**
	 * Шаблонная функция: выводит последние темы.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых тем (по умолчанию 3)
	 * block_id - блоки форума. Идентификаторы блоков перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены темы из блока. По умолчанию блок не учитывается, выводятся темы из всех блоков
	 * cat_id - категории форума. Идентификаторы категорий перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены темы из указанной категории. По умолчанию категория не учитывается, выводятся темы из всех категорий
	 * sort - сортировка тем: по умолчанию как на странице модуля, **date** – по дате, **rand** – в случайном порядке, **keywords** – темы, похожие по названию для текущей страницы
	 * only_module - выводить блок только на странице, к которой прикреплен модуль «Форум»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/forum/views/forum.view.show_block_**template**.php; по умолчанию шаблон modules/forum/views/forum.view.show_block.php)
	 * 
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes($attributes, 'count', 'block_id', 'cat_id', 'sort', 'only_module', 'template');

		if ($attributes["only_module"] && $this->diafan->_site->module != "forum")
			return;

		$count  = $attributes["count"] ? intval($attributes["count"]) : 3;
		$block_ids = explode(",", $attributes["block_id"]);
		$cat_ids = explode(",", $attributes["cat_id"]);
		$sort    = $attributes["sort"] == "rand" || $attributes["sort"] == "keywords" ? $attributes["sort"] : "date";

		$result = $this->model->show_block($count, $block_ids, $cat_ids, $sort);
		if(! $result)
			return;
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block', 'forum', $result, $attributes["template"]);
	}

	/**
	 * Устаревшая шаблонная функция
	 * 
	 * @return void
	 */
	public function show_block_rel($attributes)
	{
		$this->get_attributes($attributes, 'count', 'cat_id');
		$attributes["only_module"] = true;
		$attributes["sort"] = 'keywords';
		$this->show_block($attributes);
	}

	/**
	 * Шаблонная функция: выводит блок сообщений.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых сообщений (по умолчанию 3)
	 * block_id - блоки форума. Идентификаторы блоков перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены сообщения из указаннного блока. По умолчанию блок не учитывается, выводятся сообщения из всех блоков.
	 * cat_id - категории форума. Идентификаторы категорий перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены сообщения из указанной категории. По умолчанию категория не учитывается, выводятся сообщения из всех категорий
	 * only_module - выводить блок только на странице, к которой прикреплен модуль «Форум»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/forum/views/forum.view.show_block_messages_**template**.php; по умолчанию шаблон modules/forum/views/forum.view.show_block_messages.php)
	 * 
	 * @return void
	 */
	public function show_block_messages($attributes)
	{
		$this->get_attributes($attributes, 'count', 'block_id', 'cat_id', 'only_module', 'template');

		if ($attributes["only_module"] && $this->diafan->_site->module != "forum")
			return;

		$count  = $attributes["count"] ? intval($attributes["count"]) : 3;
		$block_ids = explode(",", $attributes["block_id"]);
		$cat_ids = $attributes["cat_id"] == "current" ? "current" : explode(",", $attributes["cat_id"]);

		$result = $this->model->show_block_messages($count, $block_ids, $cat_ids);
		if(! $result)
			return;

		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block_messages', 'forum', $result, $attributes["template"]);
	}
}