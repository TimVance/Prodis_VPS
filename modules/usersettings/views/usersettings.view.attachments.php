<?php
/**
 * Шаблон прикрепленных файлов в настройках аккаунта
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
	echo '<div class="attachment" name="'.$result["prefix"].'attachments'.$result["param_id"].'[]"><input type="hidden" name="hide_attachment_delete[]" value="'.$a["id"].'">';
	if ($a["is_image"])
	{
		if($result["use_animation"])
		{
			echo ' <a href="'.$a["link"].'" data-fancybox="gallery'.$result["param_id"].'registration"><img src="'.$a["link_preview"].'"></a> <a href="'.$a["link"].'" data-fancybox="gallery'.$result["param_id"].'registration_link">'.$a["name"].'</a>';
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