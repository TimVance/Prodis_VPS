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
 * Votes
 */
class Votes extends Controller
{
	/**
	 * Шаблонная функция: выводит опросы.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * id - задает номер вопроса
	 * count - количество вопросов. Значение all выведет все вопросы. По умолчанию 1
	 * sort - сортировка опросов: по умолчанию ручная сортировка как в административной части, **rand** – в случайном порядке
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/votes/views/votes.view.show_block_**template**.php; по умолчанию шаблон modules/votes/views/votes.view.show_block.php)
	 * 
	 * @return void
	 */
	public function show_block($attributes)
	{
		if ($this->diafan->configmodules('security_user', 'votes') && ! $this->diafan->_users->id)
			return;

		$this->get_attributes($attributes, 'id', 'count', 'sort', 'template');
		
		$id   = intval($attributes["id"]);
		if($attributes["count"] === "all")
		{
			$count = "all";
		}
		else
		{
			$count   = intval($attributes["count"]);
			if($count < 1)
			{
				$count = 1;
			}
		}
		$sort   = $attributes["sort"] == "rand" ? $attributes["sort"] : "";
		$result = $this->model->show_block($id, $count, $sort);

		if (! $result)
			return;

		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block', 'votes', $result["rows"], $attributes["template"]);
	}
}