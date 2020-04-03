<?php
/**
 * Шаблон списка контактов
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

if (empty($result['rows']))
{
	echo $this->diafan->_('У Вас нет ни одного контакта. Чтобы вести приватную переписку с пользователем нужно на странице пользователя выбрать «Напишите сообщение».');
	return false;
}

if(! empty($result["rows"]))
{
	//вывод списка контактов
	echo '<table class="messages js_messages">';
	echo $this->get($result["view_rows"], 'messages', $result);
	echo '</table>';
}

//постраничная навигация
if(! empty($result["paginator"]))
{
	echo $result["paginator"];
}