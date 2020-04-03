<?php
/**
 * Шаблон блока сообщений форума
 * 
 * Шаблонный тег <insert name="show_block_messages" module="forum" [count="количество"]
 * [cat_id="категории"] [template="шаблон"]>:
 * блок последних сообщений 
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

if(! $result["rows"]) return;

echo '
<h2>'.$this->diafan->_('Последние сообщения на форуме').'</h2>
';

echo $this->get($result["view_rows"], 'forum', $result);

echo '<div class="clear:both"></div>';