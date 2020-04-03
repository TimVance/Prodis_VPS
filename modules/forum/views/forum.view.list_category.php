<?php
/**
 * Шаблон списка категорий форума
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

echo ($result["access_add"] ? '<div class="forum_add"><i class="fa fa-plus-circle"></i><a href="'.BASE_PATH_HREF.$result["link_add"].'">'.$this->diafan->_('Добавить тему').'</a></div>' : '').'

<table class="forum_list js_forum_list">
	<tr><th>'.$this->diafan->_('Темы').'</th><th>'.$this->diafan->_('Ответы').'</th><th>'.$this->diafan->_('Автор').'</th><th colspan="2">'.$this->diafan->_('Последний ответ').'</th></tr>';
if(! empty($result["rows"]))
{
	echo $this->get($result["view_rows"], 'forum', $result);
}
echo '
</table>
'.(!empty($result["paginator"]) ? $result["paginator"] : '');

// форма поиска по темам и сообщениям
echo $this->get('form_search', 'forum', array("action" => $result["action"]));
