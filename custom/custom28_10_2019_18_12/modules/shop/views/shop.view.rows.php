<?php
/**
 * Шаблон элементов в списке товаров
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

if(empty($result['rows'])) {
    echo 'Торговых центров не обнаружено';
    return false;
}

foreach ($result['rows'] as $row)
{		
	echo '<div class="js_shop shop-item shop">';

	//вывод названия и ссылки на товар	
	echo '<a href="'.BASE_PATH_HREF.'/about-tts/?id='.$row["id"].'" class="shop-item-title">'.$row["name"].'</a>';

	echo '</div>';		
}

//Кнопка "Показать ещё"
if(! empty($result["show_more"]))
{
	echo $result["show_more"];
}