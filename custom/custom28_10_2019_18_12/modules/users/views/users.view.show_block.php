<?php
/**
 * Шаблон статистики пользователей на сайте
 * 
 * Шаблонный тег <insert name="show_block" module="users">:
 * выводит статистику пользователей на сайте
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

if (! $result)
{
	return;
}
echo '<div class="show_users">'.$this->diafan->_('Сейчас на сайте: %s гостей, %s пользователей.', true, $result["count_user"], $result["count_user_auth"]).'</div>';
