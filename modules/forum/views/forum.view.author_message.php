<?php
/**
 * Шаблон вывода информации о пользователе
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

if (! is_array($result))
{
	if($this->diafan->configmodules("avatar_none", "users"))
	{
		$avatar = BASE_PATH.USERFILES.'/avatar_none.png';
		$avatar_width = $this->diafan->configmodules("avatar_width", "users");
		$avatar_height = $this->diafan->configmodules("avatar_height", "users");
		echo '<img src="'.$avatar.'" width="'.$avatar_width.'" height="'.$avatar_height.'" alt="'.$result.'" class="avatar"> ';
	}	
	echo $result;	
	return;
}
if (! empty($result["avatar"]))
{
	echo '<img src="'.$result["avatar"].'" width="'.$result["avatar_width"].'" height="'.$result["avatar_height"].'" alt="'.$result["fio"].' ('.$result["name"].')" class="avatar"> ';
}
else
{
	echo '<span class="empty_avatar"></span>';
}
$name = $result["fio"].($result["name"] ? ' ('.$result["name"].')' : '');
if(! empty($result['user_page']))
{
	$name = '<a href="'.$result['user_page'].'">'.$name.'</a>';
}
echo $name;