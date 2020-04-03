<?php
/**
 * Шаблон блока тем форума
 * 
 * Шаблонный тег <insert name="show_block" module="forum" [count="количество"]
 * [cat_id="категория"] [template="шаблон"]>:
 * блок последних тем
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
<div class="block_header">'.$this->diafan->_('Последние темы на форуме').'</div>
<ul class="forum_block">';

echo $this->get($result["view_rows"], 'forum', $result);

echo '</ul><br><br>';
