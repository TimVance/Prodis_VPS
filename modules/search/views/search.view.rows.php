<?php
/**
 * Шаблон результатов поиска по сайту
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

foreach ($result["rows"] as $module_name => $res)
{
	if (! empty($res["class"]))
	{
		echo $this->get($res["func"], $res["class"], $res);
	}
	else
	{
		echo '<div class="search_list">';
		foreach ($res["rows"] as $row)
		{
			echo '
			<div class="search_name"><a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["name"].'</a></div>
			<div class="search_text">'.$row["snippet"].'</div>';
		}
		echo '</div>';
	}
}

//Кнопка "Показать ещё"
if(! empty($result["show_more"]))
{
	echo $result["show_more"];
}