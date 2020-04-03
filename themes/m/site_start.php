<?php
/**
 * Шаблон стартовой страницы сайта
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
<link rel="shortcut icon" href="<insert name="path">favicon.ico" type="image/x-icon">
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

  <div class="wrapper content">

  <!--/right-col -->
  <div >
  <!-- <div id="center-col"> -->
    <insert name="show_block" module="menu" id="2" template="leftmenu">
    <!-- шаблонный тег вывода основного контента сайта -->
    <insert name="show_body">

    <!-- шаблонный тег вывода блока некоторых товаров из магазина. Вид блока товаров редактируется в файле modules/shop/views/shop.view.show_block.php. -->
    <insert name="show_block" module="shop" count="2" images="1" sort="rand">

    <!-- шаблонный тег вывода блока вопросов и ответов сайта. Вид блока редактируется в файле modules/faq/views/faq.view.show_block.php. -->
    <insert name="show_block" module="faq" count="2" often="0">

    <!-- шаблонный тег вывода блока новостей. Вид блока файлов редактируется в файле modules/news/views/news.view.show_block.php. -->
    <insert name="show_block" module="news" count="2" images="1">

  </div>
  <div class="clear">
    &nbsp;
  </div>
  </div>
  <!-- шаблонный тег вывода формы для подписчиков. Вид блока редактируется в файле modules/subscription/views/subscription.view.form.php.  -->
  <insert name="show_form" module="subscription">
  <div id="footer">
  <div class="wrapper">

    <!-- шаблонный тег вывода кнопок социальных сетей. Правится в файле themes/functions/show_social_links_main.php -->
    <insert name="show_social_links_main">

    <div class="footer-menu">
		<h3><insert value="Основное меню сайта"></h3>
		<!-- шаблонный тег вывода первого меню (параметр id=1). Настраивается в файле modules/menu/views/menu.view.show_menu.php, так как параметр template не был передан. Тогда в оформлении используются параметры tag
								Документация тега http://www.diafan.ru/dokument/full-manual/templates-functions/#show_block_menu -->
		<insert name="show_block" module="menu"
			id="1"
      count_level="1"
			tag_level_start_1="[ul]"
			tag_start_1="[li]"
			tag_end_1="[/li]"
			tag_level_end_1="[/ul]"
			tag_level_start_2=""
			tag_start_2="[li class='podmenu']"
			tag_end_2="[/li]"
			tag_level_end_2=""
			>
    </div>
    <div class="copyright">
      	<h3>&copy; <insert name="show_year"> <insert name="show_href" alt="title"></h3>
      	<!-- шаблонный тег подключает файл-блок -->
		<insert name="show_include" file="diafan">
	    <div class="notes">
			<span class="note error">
		        <!-- шаблонный тег ошибка на сайте -->
				<insert name="show_block" module="mistakes">
		    </span>
	        <span class="note sitemap">
		        <!-- шаблонный тег show_href выведет ссылку на карту сайта <a href="/map/"><img src="/img/map.png"></a>, на странице карты сайта тег выведет активную иконку -->
	        	<insert name="show_href" rewrite="map" alt="Карта сайта">
	        </span>
			<span class="note siteinfo">
				<!-- шаблонный тег вывода количества пользователей on-line. Вид блока редактируется в файле modules/users/views/users.view.show_block.php. -->
				<insert name="show_block" module="users">
			</span>
	    </div>
    </div>
  </div>
  </div>
  <div class="to_full">
    <br>
    <div id="to_full"><a href="<insert name="path_url" mobile="no">?mobile=no">Полная версия сайта</a></div>
    &copy; <insert name="show_year"> <insert name="title">
  </div>
  <!--/footer -->


</div>


<!-- шаблонный тег show_js подключает JS-файлы. Описан в файле themes/functions/show_js.php. -->
<insert name="show_js">
<script type="text/javascript" src="<insert name="custom" path="js/main.js" absolute="true" compress="js">" charset="UTF-8"></script>

<!-- шаблонный тег подключает вывод информации о Политике конфиденциальности. Если необходимо вывести свой текст сообщения, то добавле его в атрибут "text". -->
<insert name="show_privacy" hash="false" text="">

<insert name="show_include" file="counters">

</body>
</html>
