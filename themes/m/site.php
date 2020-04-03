<?php
/**
 * Шаблон сайта для мобильной версии
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if(! defined("DIAFAN"))
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
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><insert name="show_title"></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta content="Russian" name="language">
<meta content="DiAfan <?php echo "http".(IS_HTTPS ? "s" : '')."://"; ?>www.diafan.ru/" name="author">
<meta name="viewport" content="target-densitydpi=device-dpi, width=device-width, initial-scale=0.7, user-scalable=yes" />
<link href="<insert name="path">css/default.css" rel="stylesheet" type="text/css">
<link href="<insert name="path">css/style.css" rel="stylesheet" type="text/css">
<link href="<insert name="path">css/m/style.css" rel="stylesheet" type="text/css">
<insert name="show_head">
<insert name="show_css">
</head>
<body>

<div id="side-menu" class="slide_menu">
      <!-- шаблонный тег вывода первого меню (параметр id=1). Настраивается в файле modules/menu/views/menu.view.show_block_topmenu.php
                  Документация тега http://www.diafan.ru/dokument/full-manual/templates-functions/#show_block_menu -->
      <insert name="show_block" module="menu" id="1" template="topmenu">
</div>

<div class="slide">
<insert name="show_include" file="mheader">
<div id="h"><h1><insert name="show_h1"></h1></div>
<insert name="show_links" module="site">
<div id="content">
	<insert name="show_text">
	<insert name="show_module">
	<insert name="show_block" module="news" count="2">
</div>
 <insert name="show_block" module="menu"
			id="2"
			tag_level_start_1="[ul id=`menu`]"
			tag_start_1="[li]"
			tag_end_1="[/li]"
			tag_level_end_1="[/ul]"
			tag_level_start_2=""
			tag_start_2="[li class='sub']"
			tag_end_2="[/li]"
			tag_level_end_2=""
			>
<div id="footer" class="footer_inside">
	<div id="to_full"><a href="<insert name="path_url" mobile="no">?mobile=no">Полная версия сайта</a></div>
	&copy; <insert name="show_year"> <insert name="title">
</div>
</div>
<!-- шаблонный тег show_js подключает JS-файлы. Описан в файле themes/functions/show_js.php. -->
<insert name="show_js">
<script type="text/javascript" src="<insert name="custom" path="js/main.js" absolute="true" compress="js">" charset="UTF-8"></script>

<!-- шаблонный тег подключает вывод информации о Политике конфиденциальности. Если необходимо вывести свой текст сообщения, то добавле его в атрибут "text". -->
<insert name="show_privacy" hash="false" text="">

<insert name="show_include" file="counters">

</body>
</html>
