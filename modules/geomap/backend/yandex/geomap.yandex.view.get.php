<?php
/**
 * Шаблон точки на карте Яндекс.Карты
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

$center = '55.76, 37.64';
if($result["point"])
{
	$center = $result["point"];
}
elseif($this->diafan->configmodules("yandex_center", "geomap"))
{
	$center = $this->diafan->configmodules("yandex_center", "geomap");
}
$zoom = '13';
if($this->diafan->configmodules("yandex_zoom", "geomap"))
{
	$zoom = $this->diafan->configmodules("yandex_zoom", "geomap");
}

if(! isset($GLOBALS['include_geomap_yandex']))
{
	echo '<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>';
	$GLOBALS['include_geomap_yandex'] = true;
}

echo '<div id="geomap_map" style="width:500px; height:400px"></div>';

echo '<script type="text/javascript">
	ymaps.ready(function(){
		var map = new ymaps.Map("geomap_map", {
			center: ['.$center.'], 
			zoom: '.$zoom.'
		});
		';
		if($result["point"])
		{
			echo 'var marker = new ymaps.Placemark(['.$result["point"].']);
			map.geoObjects.add(marker);';
		}
		echo '
	});
</script>';