<?php
/**
 * Шаблон прикрепленных к сообщению файлов
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

if(empty($result["rows"]))
{
	return;
}
foreach ($result["rows"] as $row)
{
	if ($row["is_image"])
	{
		if ($result["use_animation"])
		{
			$a_href  = '<a href="'.$row["link"].'" data-fancybox="gallery'.$row["element_id"].$row["module_name"].'_1">';
			$a_href2 = '<a href="'.$row["link"].'" data-fancybox="gallery'.$row["element_id"].$row["module_name"].'_2">';
		}
		else
		{
			$a_href .= '<a href="'.$row["link"].'" rel="large_image" width="'.$row["width"].'" height="'.$row["height"].'">';
			$a_href2 = $a_href;
		}
		echo
		'<p id="attachment'.$row["id"].'">'
			.$a_href.$row["name"].'</a>'
			.' ('.$row["size"].')'
			.' '.$a_href2.'<img src="'.$row["link_preview"].'"></a>'
			.($result["access"] ? ' <a href="javascript:void(0)" class="js_delete_attachment delete_attachment" del_id="'.$row["id"].'" title="'.$this->diafan->_('Вы действительно хотите удалить запись?', false).'">x</a>' : '')
		.'</p>';
	}
	else
	{
		echo '<p id="attachment'.$row["id"].'"><a href="'.$row["link"].'">'.$row["name"].'</a> ('.$row["size"].')'
		.($result["access"] ? ' <a href="javascript:void(0)" class="js_delete_attachment delete_attachment" del_id="'.$row["id"].'" title="'.$this->diafan->_('Вы действительно хотите удалить запись?', false).'">x</a>' : '')
		.'</p>
		';
	}
}