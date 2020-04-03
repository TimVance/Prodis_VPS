<?php
/**
 * Шаблон прикрепленных к объявлению файлов в форме редактирования объявления
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

foreach ($result["rows"] as $a)
{
	echo '<div class="attachment" name="attachments'.$result["param_id"].'[]"><input type="hidden" name="hide_attachment_delete[]" value="'.$a["id"].'">';
	if ($a["is_image"])
	{
		if($result["use_animation"])
		{
			echo ' <a href="'.$a["link"].'" data-fancybox="gallery'.$result["param_id"].'ab_att"><img src="'.$a["link_preview"].'"></a> <a href="'.$a["link"].'" data-fancybox="gallery'.$result["param_id"].'ab_att_link">'.$a["name"].'</a>';
		}
		else
		{
			echo ' <a href="'.$a["link"].'"><img src="'.$a["link_preview"].'"></a> <a href="'.$a["link"].'">'.$a["name"].'</a>';
		}
	}
	else
	{
		echo '<a href="'.$a["link"].'">'.$a["name"].'</a>';
	}
	echo ' <a href="javascript:void(0)" class="attachment_delete">x</a> </div>';
}