<?php
/**
 * Шаблон формы авторизации для административной части
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
<meta name="HandheldFriendly" content="True">
<meta name="viewport" content="width=device-width, initial-scale=-0.2, minimum-scale=-0.2, maximum-scale=3.0">
<meta name="format-detection" content="telephone=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="pragma" content="no-cache">
<insert name="show_head">

<!--[if lte IE 8]>
	<link rel="stylesheet" href="<insert name="custom" absolute="true" path="adm/css/ie/ie.css">" media="all" />
<![endif]-->
	
</head>

<body class="login-page">
	<div id="wrapper">
		<!-- |===============| header start |===============| -->
		<header class="header">
			<a href="<insert name="protocol">://<insert name="base_url">/" class="logo">
				<img src="<insert name="path">img/logo.png" alt="">
				<span class="logo__title"><insert value="Система управления"></span>
				<span class="logo__link"><insert name="base_url"></span>
			</a>
			
			<div class="header__link">
				<a href="<insert name="protocol">://<insert name="base_url">/">
					<i class="fa fa-laptop"></i>
					<span><insert value="Просмотр сайта"></span>
				</a>
			</div>
			
			<insert name="show_languages">
			
		</header>
		<!-- |===============| header end |===============| -->
		
		<!-- |===============| wrap start |===============| -->
		<div class="wrap">
			<form name="auth" method="post" action="" class="login-form">
				<input type="hidden" name="action" value="auth">
				<div class="login-heading"><insert value="Войти в систему"></div>
				
				<div class="infofield"><insert name="userlogin">:</div>
				<input type="text" class="login-field" name="name" autocomplete="off">
				<div class="infofield"><insert value="Пароль">:</div>
				
				<div class="login-pas">
					<div class="fa fa-eye-slash login-pas-toggle"></div>
					<input type="password" class="pass-field" name="pass" autocomplete="off">
				</div>
				
				<button class="btn btn_blue btn_small">
					<i class="fa fa-lock"></i>
					<insert value="Вход">
				</button>
				
				<a href="<insert name="protocol">://<insert name="base_url">/admin_reminding/" class="login-rememb"><insert value="Забыли пароль?"></a>

				<insert name="errauth">
			</form>
		</div>
		<!-- |===============| wrap end |===============| -->
		
	</div>
<insert name="show_js">
</body>
</html>
