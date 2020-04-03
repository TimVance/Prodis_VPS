<?php
/**
 * Шаблон одного комментария
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

echo '<div class="comment"><a name="comment'.$result["id"].'"></a>';
if (! empty($result["name"]))
{
	if(is_array($result["name"]))
	{
		$name = '';
		if (! empty($result["name"]["avatar"]))
		{
			$name .= '<img src="'.$result["name"]["avatar"].'" width="'.$result["name"]["avatar_width"].'" height="'.$result["name"]["avatar_height"].'" alt="'.$result["name"]["fio"].' ('.$result["name"]["name"].')" class="avatar"> ';
		}
		if(array_key_exists('fio', $result["name"]) && array_key_exists('name', $result["name"])) 
		{
			$name .= $result["name"]["fio"].($result["name"]["name"] ? ' ('.$result["name"]["name"].')' : '');
		}
		
		if(! empty($result["name"]["user_page"]))
		{
			$name = '<a href="'.$result["name"]["user_page"].'">'.$name.'</a>';
		}
	}
	else
	{
		$name = $result["name"];
	}
	echo '<div class="comments_name">';
	echo $name.'</div>';
}
if ($result['date'])
{
	echo '<div class="comments_date">'.$result['date'].'</div>';
}

foreach ($result["params"] as $param)
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
						echo ' <a href="'.$a["link"].'" data-fancybox="gallery'.$result["id"].'comments"><img src="'.$a["link_preview"].'"></a> <a href="'.$a["link"].'" data-fancybox="gallery'.$result["id"].'comments_link">'.$a["name"].'</a>';
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
		elseif($param["type"] == 'textarea')
		{
			echo  nl2br($param["value"]);
		}
		else
		{
			echo $param["value"];
		}
		echo  '</span>';
	}
	echo  '</div>';
}

echo '<div class="comments_text">'.$result['text']."</div>";

if($result["form"])
{
	echo '
	<a href="javascript:void(0)" class="js_comments_show_form comments_show_form">'.$this->diafan->_('Ответить').'</a>
	<div style="display:none;" class="comments_block_form comments'.$result["id"].'_block_form">';
	echo $this->get('form', 'comments', $result["form"]);
	echo '</div>';
}

if(! empty($result["children"]))
{
	echo '<div class="comments_level comments'.$result["id"].'_result">'.$this->get('list', 'comments', array("rows" => $result["children"], "result" => $result)).'</div>';
}
else
{
	echo '<div class="comments_level comments'.$result["id"].'_result" style="display:none;"></div>';
}
echo '</div>';
