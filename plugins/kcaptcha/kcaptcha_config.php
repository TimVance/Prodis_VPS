<?php
/**
 * @package    DIAFAN.CMS
 *
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2015 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	include dirname(dirname(dirname(__FILE__))).'/includes/404.php';
}

$alphabet = "0123456789abcdefghijklmnopqrstuvwxyz"; # не правьте, без изменений файла шрифтов!

# используемые символы в капче
$allowed_symbols = "0123456789"; #только цифры
//$allowed_symbols = "23456789abcdegikpqsvxyz"; #латинские буквы и цифры без похожих символов (o=0, 1=l, i=j, t=f)

# папка со шрифтами
$fontsdir = 'fonts';	

# количество символов в капче
$length = mt_rand(5,7); # случайно от 5 до 7
//$length = 6;

# размер картинки капчи
$width = 160;
$height = 80;

# сила вертикального искажения символов
$fluctuation_amplitude = 8;

#шум
//$white_noise_density=0; // без белого шума
$white_noise_density=1/6; 
//$black_noise_density=0; // без черного шума
$black_noise_density=1/30;

# повышение безопасности путем уменьшения межбуквенного пространства
$no_spaces = true;

$show_credits = false; 
$credits = 'www.captcha.ru';

# цвета капчи
//$foreground_color = array(0, 0, 0);
//$background_color = array(220, 230, 255);
$foreground_color = array(mt_rand(0,80), mt_rand(0,80), mt_rand(0,80));
$background_color = array(mt_rand(220,255), mt_rand(220,255), mt_rand(220,255));

# JPEG качество сжатия картинки, 0-100. Чем выше, тем качественнее
$jpeg_quality = 90;