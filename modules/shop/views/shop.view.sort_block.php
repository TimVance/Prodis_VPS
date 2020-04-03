<?php
/**
 * Шаблон блока «Сортировать» с ссылками на направление сортировки
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
       
$link_sort   = $result["link_sort"];
$sort_config = $result['sort_config'];

echo '<div class="block">';

echo '<div class="sort-by by-rate">';

$symbol = 'up';
for ($i = 1; $i <= count($sort_config['sort_directions']); $i++)
{
	if(! empty($sort_config['sort_fields_names'][$i]))
	{
		echo '<a href="'.($link_sort[$i] ? BASE_PATH_HREF.$link_sort[$i] : '').'">' . $sort_config['sort_fields_names'][$i] . '</a>';
	}
	if ($link_sort[$i])
	{
		echo ' <a href="'.BASE_PATH_HREF.$link_sort[$i].'" class="sort-'.$symbol.'"><i class="fa fa-chevron-circle-'.$symbol.'"></i></a> ';
	}
	else
	{
		echo ' <span class="sort-'.$symbol.' active"><i class="fa fa-chevron-circle-'.$symbol.'"></i></span> ';
	}

	$symbol =  ($symbol == 'up' ?  'down' :'up');
}

echo '</div>';

echo '</div>';