<?php
/**
 * Шаблон элементов в списке объявлений
 *
 * Шаблон вывода списка объявлений
 * в категории объявлений, в результатах поиска или если группировка не используется
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

if(empty($result['rows'])) return false;

foreach ($result["rows"] as $row)
{
	echo '<div class="js_ab ab block">';

	//вывод изображений объявления
	if (! empty($row["img"]))
	{
		echo '<div class="ab_img">';
		foreach ($row["img"] as $img)
		{
			switch ($img["type"])
			{
				case 'animation':
					echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$row["id"].'ab" class="block-row-img">';
					break;
				case 'large_image':
					echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'" class="block-row-img">';
					break;
				default:
					echo '<a href="'.BASE_PATH_HREF.$img["link"].'" class="block-row-img">';
					break;
			}
			echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">'
			. '</a> ';
		}
		echo '</div>';
	}
	echo '<div class="block-text">';
	//вывод названия и ссылки
	echo '<h4'.(isset($row["act"]) && ! $row["act"] ? ' class="noact"' : '').'>';
		echo '<a class="black" href="'.BASE_PATH_HREF.$row["link"].'">'.$row["name"].'</a>';
	echo '</h4>';

	//рейтинг объявления
	if (! empty($row["rating"]))
	{
	   echo '<div class="rate">'.$row["rating"] . '</div>';
	}

	//вывод краткого описания объявления
	if (!empty($row["anons"]))
	{
		echo '<p><a href="'.BASE_PATH_HREF.$row["link"].'" class="black">'.$row['anons'].'</a></p>';
	}

	//вывод параметров объявления
	if (!empty($row["param"]))
	{
		echo $this->get('param', 'ab', array("rows" => $row["param"], "id" => $row["id"]));
	}

	echo '<p class="author">';
	// автор
	if (! empty($row["author"]))
	{
		if(is_array($row["author"]))
		{
			$name = $row["author"]["fio"].($row["author"]["name"] ? ' ('.$row["author"]["name"].')' : '');
			if (! empty($row["author"]["avatar"]))
			{
				echo '<img src="'.$row["author"]["avatar"].'" width="'.$row["author"]["avatar_width"].'" height="'.$row["author"]["avatar_height"].'" alt="'.$row["author"]["fio"].' ('.$row["author"]["name"].')" class="avatar"> ';
			}
			if(! empty($row["author"]["user_page"]))
			{
				$name = '<a class="black" href="'.$row["author"]["user_page"].'"><strong>'.$name.'</strong></a>';
			}
		}
		else
		{
			$name = '<strong>' . $row["author"] . '</strong>';
		}
		echo $name . ', ';
	}

	//дата
	if (! empty($row["date"]))
	{
		echo '<span class="date">'.$row["date"].'</span>';
	}

	echo '</p>';

	//теги объявления
	if (!empty($row["tags"]))
	{
		echo $row["tags"];
	}

	echo '</div>';

	echo '<div class="ab_actions">';
	//ссылка на редактирование
	if (! empty($row["edit_access"]))
	{
		echo '<a href="'.BASE_PATH_HREF.$row["edit_link"].'" class="js_ab_action"><img src="'.BASE_PATH.Custom::path('modules/ab/img/edit.gif').'" width="12" height="14"'
		.' title="'.$this->diafan->_('Редактировать', false).'" alt="'.$this->diafan->_('Редактировать', false).'"></a>';
	}

	//ссылка на блокирование/разблокирование
	if (! empty($row["block_access"]))
	{
		echo ' <a href="'.BASE_PATH_HREF.$row["block_link"].'" class="js_ab_action"><img src="'.BASE_PATH.Custom::path('modules/ab/img/'.(empty($row["unblock"]) ? 'un' : '').'block.gif').'" width="12" height="18"'
		.' title="'.$this->diafan->_((! empty($row["unblock"]) ? 'Разблокировать' : 'Блокировать'), false).'" alt="'.$this->diafan->_((! empty($row["unblock"]) ? 'Разблокировать' : 'Блокировать'), false).'"></a>';
	}

	//ссылка на удаление
	if (! empty($row["delete_access"]))
	{
		echo ' <a href="'.BASE_PATH_HREF.$row["delete_link"].'" confirm="'.$this->diafan->_('Вы действительно хотите удалить объявление?', false).'" class="js_ab_action"><img src="'.BASE_PATH.Custom::path('modules/ab/img/delete.gif').'" width="15" height="15"'
		.' title="'.$this->diafan->_('Удалить', false).'" alt="'.$this->diafan->_('Удалить', false).'"></a>';
	}
	echo '</div>';


	echo '</div>';
}

//Кнопка "Показать ещё"
if(! empty($result["show_more"]))
{
	echo $result["show_more"];
}