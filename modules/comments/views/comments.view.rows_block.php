<?php
/**
 * Шаблон блока комментариев
 * 
 * Шаблонный тег <insert name="show_block" module="comments" [count="количество"]
 * [element_id="элементы"] [modules="модули"]
 * [sort="порядок_вывода"] [template="шаблон"]>:
 * блок комментариев
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

//комментарии
foreach ($result["rows"] as $row)
{
	echo '<div class="block-row">';
	
	foreach ($row["params"] as $param)
	{
		echo '<div class="comments_param'.($param["type"] == 'title' ? '_title' : '').'">'.$param["name"];
		if (! empty($param["value"]))
		{
			echo  ': <span class="comments_param_value">';
			if($param["type"] == "attachments")
			{
				foreach ($param["value"] as $a)
				{
					if ($a["is_image"])
					{
						if($param["use_animation"])
						{
							echo ' <a href="'.$a["link"].'" data-fancybox="gallery'.$row["id"].'comments" class="block-row-img"><img class="avatar" src="'.$a["link_preview"].'"></a> <a href="'.$a["link"].'" data-fancybox="gallery'.$row["id"].'comments_link]">'.$a["name"].'</a>';
						}
						else
						{
							echo ' <a href="'.$a["link"].'" class="block-row-img"><img class="avatar" src="'.$a["link_preview"].'"></a> <a href="'.$a["link"].'">'.$a["name"].'</a>';
						}
					}
					else
					{
						echo ' <a href="'.$a["link"].'">'.$a["name"].'</a>';
					}
				}
			}
			elseif($param["type"] == "images")
			{
				foreach ($param["value"] as $img)
				{
					echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">';
				}
			}
			elseif (is_array($param["value"]))
			{
				foreach ($param["value"] as $p)
				{
					if ($param["value"][0] != $p)
					{
						echo  ', ';
					}
					if (is_array($p))
					{
						echo  $p["name"];
					}
					else
					{
						echo  $p;
					}
				}
			}
			else
			{
				echo  $param["value"];
			}
			echo  '</span>';
		}
		echo  '</div>';
	}	

		echo '<div class="block-text">';

			echo '<p>'.$row['text'].'</p>';

			echo '<div class="forum-author">';
			if (! empty($row["name"]))
			{
				if(is_array($row["name"]))
				{
					$name = '';
					if (! empty($row["name"]["avatar"]))
					{
						$name .= '<img src="'.$row["name"]["avatar"].'" width="'.$row["name"]["avatar_width"].'" height="'.$row["name"]["avatar_height"].'" alt="'.$row["name"]["fio"].' ('.$row["name"]["name"].')" class="avatar"> ';
					}
					if(array_key_exists('fio', $row["name"]) && array_key_exists('name', $row["name"])) 
					{
						$name .= $row["name"]["fio"].($row["name"]["name"] ? ' ('.$row["name"]["name"].')' : '');
					}
					
					if(! empty($row["name"]["user_page"]))
					{
						$name = '<a href="'.$row["name"]["user_page"].'">'.$name.'</a>';
					}
				}
				else
				{
					$name = $row["name"];
				}
				echo '<strong>'.$name.'</strong>';
			}
			echo '</div>';
		
			if ($row['date'])
			{
				echo '<div class="date">'.$row['date'].'</div>';
			}

			echo '<div class="theme">';
				echo '<div class="comments_link">'.$this->diafan->_('Тема').': <a href="'.BASE_PATH_HREF.$row['link'].'#comment'.$row["id"].'">'.$row["theme_name"].'</a></div>';
			echo '</div>';

		echo '</div>';
	
	echo '</div>';
}