<?php
/**
 * [Шаблонный тег]: выводит ссылки на социальные сети.
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

if ($this->diafan->_site->titlemodule_meta)
{
	$title = $this->diafan->_site->titlemodule_meta;
}
if($this->diafan->_route->page > 1)
{
	$page = $this->diafan->_(' — Страница %d', false, $this->diafan->_route->page);
}
else
{
	$page = '';
}
if($this->diafan->configmodules('title_tpl', 'site'))
{
	if($this->diafan->_site->parent_id && ! $this->diafan->_site->parent_name
	   && strpos($this->diafan->configmodules("title_tpl", 'site'), '%parent') !== false)
	{
		$this->diafan->_site->parent_name = DB::query_result("SELECT [name] FROM {site} WHERE id=%d", $this->diafan->_site->parent_id);
	}
	$this->diafan->_site->title_meta = str_replace(
		array('%title', '%name', '%parent'),
		array($this->diafan->_site->title_meta, $this->diafan->_site->name, $this->diafan->_site->parent_name),
		$this->diafan->configmodules("title_tpl", 'site')
	);
	$title = $this->diafan->_site->title_meta.$page;	
}
if ($this->diafan->_site->title_meta)
{
	$title = ($this->diafan->_site->titlemodule ? $this->diafan->_site->titlemodule.' — ' : '').$this->diafan->_site->title_meta.$page;	
}
$title = ($this->diafan->_site->titlemodule ? $this->diafan->_site->titlemodule.' — ' : '').$this->diafan->_site->name.$page.(TITLE ? ' — '.TITLE : '');

$title = rawurlencode($title);

echo '
<div class="socials">
      <h3>'.$this->diafan->_('Поделиться в соцсетях').'</h3>
       <a href="http'.(IS_HTTPS ? "s" : '').'://share.yandex.ru/go.xml?service=vkontakte&amp;url=http'.(IS_HTTPS ? "s" : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&amp;title='.$title.'"><i class="fa fa-vk"></i></a>
       <a href="http'.(IS_HTTPS ? "s" : '').'://share.yandex.ru/go.xml?service=odnoklassniki&amp;url=http'.(IS_HTTPS ? "s" : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&tamp;itle='.$title.'"><i class="fa fa-odnoklassniki"></i></a>
       <a href="http'.(IS_HTTPS ? "s" : '').'://share.yandex.ru/go.xml?service=twitter&amp;url=http'.(IS_HTTPS ? "s" : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&amp;title='.$title.'"><i class="fa fa-twitter"></i></a>
       <a href="http'.(IS_HTTPS ? "s" : '').'://share.yandex.ru/go.xml?service=facebook&amp;url=http'.(IS_HTTPS ? "s" : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&amp;title='.$title.'"><i class="fa fa-facebook"></i></a>
    </div>';
