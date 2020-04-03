<?php
/**
 * Шаблон одного отзыва
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

echo '<div class="block-row">';	

	echo '<div class="block-text">';
echo '<a name="comment'.$result["id"].'"></a>';
if (! empty($result["name"]))
{
	if(is_array($result["name"]))
	{
		$fio = '';
		if(array_key_exists('fio', $result["name"])) 
		{
			$fio .= $result["name"]["fio"];
		}
		$name = '';
		if (! empty($result["name"]["avatar"]))
		{
			$name .= '<img src="'.$result["name"]["avatar"].'" width="'.$result["name"]["avatar_width"].'" height="'.$result["name"]["avatar_height"].'" alt="'.$fio.'" class="avatar"> ';
		}
		$name .= $fio;
		
		if(! empty($result["name"]["user_page"]))
		{
			$name = '<a href="'.$result["name"]["user_page"].'">'.$name.'</a>';
		}
	}
	else
	{
		$name = $result["name"];
	}
	echo '<div class="reviews_name">';
	echo $name.'</div>';
}
if ($result['date'])
{
	echo '<div class="reviews_date">'.$result['date'].'</div>';
}

foreach ($result["params"] as $param)
{
	echo '<div class="reviews_param'.($param["type"] == 'title' ? '_title' : '').'">'.$param["name"];
	if (! empty($param["value"]))
	{
		echo  ': <span class="reviews_param_value">';
		switch($param["type"])
		{
			case "attachments":
				foreach ($param["value"] as $a)
				{
					if ($a["is_image"])
					{
						if($param["use_animation"])
						{
							echo ' <a href="'.$a["link"].'" data-fancybox="gallery'.$result["id"].'reviews"><img src="'.$a["link_preview"].'"></a> <a href="'.$a["link"].'" data-fancybox="gallery'.$result["id"].'reviews_link">'.$a["name"].'</a>';
						}
						else
						{
							echo ' <a href="'.$a["link"].'"><img src="'.$a["link_preview"].'"></a> <a href="'.$a["link"].'">'.$a["name"].'</a>';
						}
					}
					else
					{
						echo ' <a href="'.$a["link"].'">'.$a["name"].'</a>';
					}
				}
				break;

			case "images":
				foreach ($param["value"] as $img)
				{
					echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">';
				}
				break;

			case 'url':
				echo '<a href="'.$param["value"].'">'.$param["value"].'</a>';
				break;

			case 'email':
				echo '<a href="mailto:'.$param["value"].'">'.$param["value"].'</a>';
				break;

			default:
				if (is_array($param["value"]))
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
					echo $param["value"];
				}
				break;
		}
		echo  '</span>';
	}
	echo  '</div>';
}
if(! empty($result["text"]))
{
	echo '<div class="reviews_answer">'.$this->diafan->_('Ответ').': '.$result["text"].'</div>';
}
if(! empty($result["theme_name"]))
{
	echo '<div class="theme">';
		echo '<div class="reviews_link">'.$this->diafan->_('Тема').': <a href="'.BASE_PATH_HREF.$result['link'].'#review'.$result["id"].'">'.$result["theme_name"].'</a></div>';
	echo '</div>';
}
echo '</div>';
echo '</div>';
