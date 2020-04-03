<?php
/**
 * Шаблон списка элементов, к которым прикреплен тег
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

if(empty($result["rows"])) return false;

foreach ($result["rows"] as $module_name => $r)
{
	if(! empty($r["class"]))
	{
		echo $this->get($r["func"], $r["class"], $r);
	}
	else
	{
		echo '<div class="tags_list">';
		foreach ($r["rows"] as $row)
		{
			echo '
			<div class="tag_name"><a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["name"].'</a></div>
			<div class="tag_text">'.$row["snippet"].'</div>';
		}
		echo '</div>';
	}
}

//Кнопка "Показать ещё"
if(! empty($result["show_more"]))
{
	echo $result["show_more"];
}