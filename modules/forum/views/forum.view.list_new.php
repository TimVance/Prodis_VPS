<?php
/**
 * Шаблон страницы новых сообщений
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

if(! empty($result["error"]))
{
	echo '<p>'.$this->diafan->_('Авторизуйтесь на сайте, чтобы увидеть новые сообщения.').'</p>';
	return;
}

if(! empty($result["rows"]))
{
	echo $this->get($result["view_rows"], 'forum', $result);
}

//постраничная навигация
echo $result["paginator"];

// форма поиска по темам и сообщениям
echo $this->get('form_search', 'forum', $result);