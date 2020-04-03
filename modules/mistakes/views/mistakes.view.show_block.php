<?php
/**
 * Шаблон формы добавления уведомления об ошибке
 * 
 * Шаблонный тег <insert name="show_block" module="mistakes">
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

echo '<div class="mistakes">'.$this->diafan->_('Если Вы заметили ошибку на сайте, выделите ее и нажмите Ctrl+Enter.').'</div>';
echo '<div id="mistakes_comment" style="display:none">
<form method="post" class="js_mistakes_form mistakes_form ajax">
<input type="hidden" name="module" value="mistakes">
<input type="hidden" name="action" value="add">
<input type="hidden" name="url" value="">
<input type="hidden" name="selected_text" value="">
'.$this->diafan->_('Ваш комментарий').':<br>
<textarea name="comment"></textarea>
<br>
<input type="submit" value="'.$this->diafan->_('Отправить', false).'">
</form>
</div>';