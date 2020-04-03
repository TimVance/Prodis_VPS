<?php
/**
 * Шаблон формы поиска по темам и сообщениям
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

echo '
<div class="forum_search">
	<form action="'.BASE_PATH_HREF.(! empty($result["action"]) ? $result["action"] : '').'" method="GET">
		<input type="text" name="searchword" value="'.(! empty($result["value"]) ? $result["value"] : '').'">
		<input type="submit" value="'.$this->diafan->_('Поиск', false).'">
	</form>
</div>';