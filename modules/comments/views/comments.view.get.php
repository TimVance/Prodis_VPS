<?php
/**
 * Шаблон вывода комментариев
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

echo '<div class="comments"'.(empty($result["rows"]) ? ' style="display:none"' : '').'>';
echo '<div class="block_header">'.$this->diafan->_('Комментарии').'</div>';
if(! empty($result["rows"]))
{
	echo $this->get('list', 'comments', array("rows" => $result["rows"], "result" => $result));
}
echo '</div>';

//постраничная навигация
if(! empty($result["paginator"]))
{
	echo $result["paginator"];
}

if(! empty($result["unsubscribe"]))
{
	echo '<a name="comment0"></a><div class="errors">'.$this->diafan->_('Вы отписаны от уведомлений на новые комментарии.').'</div>';
}

if($result["form"])
{
	echo $this->get('form', 'comments', $result["form"]);
}
if($result["register_to_comment"])
{
	echo $this->diafan->_('Чтобы комментировать, зарегистрируйтесь или авторизуйтесь');
}
