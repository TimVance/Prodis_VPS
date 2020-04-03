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
 * Menu
 */
class Menu extends Controller
{
	/**
	 * Шаблонная функция: выводит меню.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * id - идентификатор категории меню
	 * template - имя шаблона
	 * tag_start_номер - текст, выводимый перед каждой ссылкой меню (может содержать слово **Increment** – при выводе автоматически заменяемое на арифметическую прогресиию, 1, 2, 3, … и Level – заменяемое на номер уровня меню)
	 * tag_end_номер - текст, выводимый после каждой ссылки пункта меню (может содержать слово Increment - заменяемое на увеличивающийся номер и **Level** – заменяемое на номер уровня меню)
	 * tag_end_after_children_номер - текст, выводимый после вложенных пунктов каждого пункта меню (может содержать слово Increment - заменяемое на увеличивающийся номер и **Level** – заменяемое на номер уровня меню)
	 * tag_level_start_номер - текст, выводимый перед уровнем (номер) меню
	 * tag_level_end_номер - текст, выводимый после уровня (номер) меню
	 * tag_active_start_номер - текст, выводимый перед активным пунктом меню уровня (может содержать слово **Increment** - заменяемое на увеличивающийся номер и **Level** – заменяемое на номер уровня меню)
	 * tag_active_end_номер - текст, выводимый после активного пункта меню уровня (может содержать слово Increment - заменяемое на увеличивающийся номер и **Level** – заменяемое на номер уровня меню)
	 * tag_active_end_after_children_номер - текст, выводимый после вложенных пунктов активного пункта меню уровня (может содержать слово Increment - заменяемое на увеличивающийся номер и **Level** – заменяемое на номер уровня меню)
	 * tag_active_child_start_номер - текст, выводимый перед пунктом меню уровня (номер) с активным дочерним пунктом (может содержать слово **Increment** - заменяемое на увеличивающийся номер и **Level** – заменяемое на номер уровня меню)
	 * tag_active_child_end_номер - текст, выводимый после пункта меню уровня (номер) с активным дочерним пунктом (может содержать слово **Increment** - заменяемое на увеличивающийся номер и **Level** – заменяемое на номер уровня меню)
	 * tag_active_child_end_after_children_номер - текст, выводимый после вложенных пунктов пункта меню уровня (номер) с активным дочерним пунктом (может содержать слово **Increment** - заменяемое на увеличивающийся номер и **Level** – заменяемое на номер уровня меню)
	 * separator_номер - текст, разделяющий пункты меню
	 * count_level - количество выводимых уровней меню, атрибут используется при оформлении меню атрибутами (template="")
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * 
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes(
			$attributes, 'id', 'template',
			'tag_start_1', 'tag_end_1', 'tag_active_start_1', 'tag_active_end_1', 'tag_level_start_1',
			'tag_level_end_1', 'tag_active_child_start_1', 'tag_active_child_end_1',
			'tag_active_child_end_after_children_1', 'tag_active_end_after_children_1', 'tag_end_after_children_1',
			'separator_1', 'count_level'
		);

		$id = intval($attributes["id"]);
		$result = $this->model->show_block($id);
		if (! $result)
		{
			return false;
		}
		
		$result["attributes"] = $attributes;

		if ($attributes["template"] === "default")
		{
			echo $this->diafan->_tpl->get('show_block', 'menu', $result);
		}
		elseif($attributes["template"] === "select" && ! empty($result['menu_template']))
		{
			echo $this->diafan->_tpl->get($result['menu_template'], 'menu', $result);
		}
		elseif ($attributes["template"])
		{
			echo $this->diafan->_tpl->get('show_block', 'menu', $result, $attributes["template"]);
		}
		else
		{
			echo $this->diafan->_tpl->get('show_menu', 'menu', $result);
		}
	}
}