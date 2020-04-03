<?php
/**
 * Шаблон административной части
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
?><!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<title><insert name="show_title"> — from diafan.ru</title>
<meta http-equiv="pragma" content="no-cache">
<meta name="HandheldFriendly" content="True">
<meta name="viewport" content="width=device-width, initial-scale=-0.2, minimum-scale=-0.2, maximum-scale=3.0">
<meta name="format-detection" content="telephone=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<insert name="show_head">

<!--[if lte IE 8]>
	<link rel="stylesheet" href="<insert name="custom" absolute="true" path="adm/css/ie/ie.css">" media="all" />
<![endif]-->
</head>
<body>
	<insert name="show_developer">
	<div id="wrapper">
		<!-- |===============| header start |===============| -->
		<header class="header">
			<a href="<insert name="protocol">://<insert name="base_url">/?<insert name="show_rand">" class="logo" target="_blank">
				<img src="<insert name="path">img/logo.png" alt="">
				<span class="logo__title"><insert value="Система управления"></span>
				<span class="logo__link"><insert name="base_url"></span>
			</a>

			<div class="header__link">
				<a href="<insert name="protocol">://<insert name="base_url">/?<insert name="show_rand">">
					<i class="fa fa-laptop"></i>
					<span><insert value="Просмотр сайта"></span>
				</a>
			</div>

			<insert name="show_addnew">

			<insert name="show_search">

			<insert name="show_languages">

			<div class="header__unit">
				<a href="<insert name="path_url">logout/?<insert name="show_rand">" class="sign-out"><i class="fa fa-sign-out"></i></a>
				<a href="<insert name="path_url">users/edit<insert name="userid">/" class="settings-link"><i class="fa fa-gear"></i></a>
				
				<a href="<insert name="path_url">users/edit<insert name="userid">/" class="header__user">
					<i class="fa fa-user"></i>
					<span class="header__user__in"><insert name="userfio"></span>
				</a>
			</div>
			
		</header>
		<!-- |===============| header end |===============| -->
		
		<!-- |===============| wrap start |===============| -->
		<div class="wrap">
			<div class="nav-bg"></div>
			<div class="nav">
				
				<insert name="show_menu">
			</div>
			<!-- |===============| col-right start |===============| -->
			<div class="col-right">
				<insert name="show_demo">
				<insert name="show_body">
				
				<footer class="footer">
					<div class="footer__links">
						<a href="https://user.diafan.ru/support/"><insert value="Техническая поддержка"></a>
						<insert name="show_docs">
					</div>
					
					<div class="footer__copy">
						<insert name="show_brand" id="0">
					</div>
				</footer>
			</div>
			<!-- |===============| col-right end |===============| -->
			
		</div>
		<!-- |===============| wrap end |===============| -->
		
	</div>
<insert name="show_js">
<insert name="show_include" file="counters">
</body>
</html>
