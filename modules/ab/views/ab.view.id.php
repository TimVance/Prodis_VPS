<?php
/**
 * Шаблон страница объявления
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

echo '<div class="js_ab ab block">';

	//вывод изображений объявления
	if (! empty($result["img"]))
	{
		echo '<div class="ab_img">';
		foreach($result["img"] as $img)
		{
			switch ($img["type"])
			{
				case 'animation':
					echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$result["id"].'ab">';
					break;
				case 'large_image':
					echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'">';
					break;
				default:
					echo '<a href="'.BASE_PATH_HREF.$img["link"].'">';
					break;
			}
			echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">'
			. '</a> ';
		}
		echo '</div>';
	}
            
    echo '<div class="block-text">';

        echo '<h4>'.$result['titlemodule'].'</h4>';

        //вывод рейтинга объявления
		if (! empty($result["rating"]))
		{			
			echo '<div class="rate"> '. $result["rating"] . '</div>';			
		}		

		//полное описание объявления
		echo '<p>'.$result['text'].'</p>';

		//параметры объявления
		if (! empty($result["param"]))
		{
			echo $this->get('param', 'ab', array("rows" => $result["param"], "id" => $result["id"]));
		}

		echo '<p class="author">';
		// автор
		if (! empty($result["author"]))
		{			
			if(is_array($result["author"]))
			{
			    $name = $result["author"]["fio"].($result["author"]["name"] ? ' ('.$result["author"]["name"].')' : '');
			    if (! empty($result["author"]["avatar"]))
			    {
				    echo '<img src="'.$result["author"]["avatar"].'" width="'.$result["author"]["avatar_width"].'" height="'.$result["author"]["avatar_height"].'" alt="'.$result["author"]["fio"].' ('.$result["author"]["name"].')" class="avatar"> ';
			    }
			    if(! empty($result["author"]["user_page"]))
			    {
				    $name = '<a class="black" href="'.$result["author"]["user_page"].'"><strong>'.$name.'</strong></a>';
			    }
			}
			else
			{
			    $name = '<strong>' . $result["author"] . '</strong>';
			}	
			echo $name . ', ';		
		}

		//дата
		if (! empty($result["date"]))
		{
			echo '<span class="date">'.$result["date"].'</span>';	
		}

		echo '</p>';

    echo '</div>';

echo '<div class="ab_actions">';

//ссылка на редактирование
if ($result["edit_access"])
{
	echo '<a href="'.BASE_PATH_HREF.$result["edit_link"].'" class="js_ab_action"><img src="'.BASE_PATH.Custom::path('modules/ab/img/edit.gif').'" width="12" height="14" title="'.$this->diafan->_('Редактировать', false).'" alt="'.$this->diafan->_('Редактировать', false).'"></a>';
}

//ссылка на блокирование/разблокирование
if ($result["block_access"])
{
	echo ' <a href="'.BASE_PATH_HREF.$result["block_link"].'" class="js_ab_action"><img src="'.BASE_PATH.Custom::path('modules/ab/img/'.(empty($result["unblock"]) ? 'un' : '').'block.gif').'" width="12" height="18"'
	.' title="'.$this->diafan->_((! empty($result["unblock"]) ? 'Разблокировать' : 'Блокировать'), false).'" alt="'.$this->diafan->_((! empty($result["unblock"]) ? 'Разблокировать' : 'Блокировать'), false).'"></a>';
}

//ссылка на удаление
if ($result["delete_access"])
{
	echo ' <a href="'.BASE_PATH_HREF.$result["delete_link"].'" confirm="'.$this->diafan->_('Вы действительно хотите удалить объявление?', false).'" class="js_ab_action"><img src="'.BASE_PATH.Custom::path('modules/ab/img/delete.gif').'" width="15" height="15"'
	.' title="'.$this->diafan->_('Удалить', false).'" alt="'.$this->diafan->_('Удалить', false).'"></a>';
}

echo '</div>';

echo '</div>';

//счетчик просмотров
if(! empty($result["counter"]))
{
	echo '<div class="ab_counter">'.$this->diafan->_('Просмотров').': '.$result["counter"].'</div>';
}

// вывод точки на карте
echo $this->diafan->_geomap->get($result["id"], 'ab');

//теги объявления
if(! empty($result["tags"]))
{
	echo $result["tags"];
}

//комментарии
if(! empty($result["comments"]))
{
	echo $result["comments"];
}

//форма добавления объявления
if (! empty($result["form"]))
{
	echo $this->get('form', 'ab', $result["form"]);
}

echo $this->htmleditor('<insert name="show_block_rel" module="ab" count="4" images="1">');
