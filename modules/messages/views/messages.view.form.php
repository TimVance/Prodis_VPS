<?php
/**
 * Шаблон формы добавления личного сообщения
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

echo '<form method="POST" action="" id="messages" class="ajax messages_form" enctype="multipart/form-data">
<div class="block_header">'.$this->diafan->_('Добавить сообщение').'</div>
<input type="hidden" name="module" value="messages">
<input type="hidden" name="action" value="add">
<input type="hidden" name="to" value="'.$result["to"].'">
<input type="hidden" name="redirect" value="'.(! empty($result["redirect"]) ? $result["redirect"] : '').'">';

echo $this->get('get', 'bbcode', array("name" => "message", "tag" => "message", "value" => ""));

echo '<br>
<input type="submit" value="'.$this->diafan->_('Отправить', false).'">
<div class="errors error"'.(! empty($result["error"]) ? '>'.$result["error"] : ' style="display:none">').'</div>
</form>';