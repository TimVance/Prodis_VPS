<?php
/**
 * Шаблон данных, доступных для редактирования с помощью панели быстрого редактирования
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

echo '<span class="useradmin_contener" href="'
.($result["module_name"] == "languages" ? BASE_PATH_HREF : BASE_PATH)
.'useradmin/edit/?module_name='.$result["module_name"]
.'&amp;name='.urlencode($result["name"])
.'&amp;element_id='.$result["element_id"]
.'&amp;lang_id='.$result["lang_id"]
.'&amp;type='.$result["type"]
.'&amp;rand='.rand(0, 999);

if($result["is_lang"])
{
	echo '&amp;is_lang=true&amp;lang_module_name='.$result["lang_module_name"];
}
echo '&amp;iframe=true&amp;';

switch($result["type"])
{
	case 'editor':
	case 'textarea':
		echo 'width=800&amp;height=600';
		break;
	case 'date':
		echo 'width=300&amp;height=250';
		break;
	case 'text':
	case 'numtext':
		echo 'width=600&amp;height=120';
		break;
}
echo '">'.$result["text"].'</span>';