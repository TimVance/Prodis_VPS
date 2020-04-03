<?php
/**
 * Шаблон вывода нескольких точек на карте Яндекс.Карты
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
if($this->diafan->configmodules("yandex_center", "geomap"))
{
	$center = $this->diafan->configmodules("yandex_center", "geomap");
}
$zoom = '10';
if($this->diafan->configmodules("yandex_zoom", "geomap"))
{
	$zoom = $this->diafan->configmodules("yandex_zoom", "geomap");
}

if(! isset($GLOBALS['include_geomap_yandex']))
{
	echo '<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>';
	$GLOBALS['include_geomap_yandex'] = true;
}

echo '<div id="geomap_map" style="width:100%; height:800px"></div>';

echo '<script type="text/javascript">
	ymaps.ready(function(){
		';
		if(empty($result["rows"]))
		{
			echo 'var map = new ymaps.Map("geomap_map", {
				center: ['.$center.'], 
				zoom: '.$zoom.'
			});
			map.geoObjects.add(new ymaps.Placemark(['.$center.'],{}));';
		}
		else
		{
			echo '
			var map = new ymaps.Map("geomap_map", {
				center: ['.$center.'], 
				zoom: 10
			});';
			foreach($result["rows"] as $row)
			{
				echo '
				map.geoObjects.add(new ymaps.Placemark(['.$row["point"].'], {
				balloonContent: "<a href=\"'.BASE_PATH_HREF.$row["link"].'\">'.$row["name"].'</a>"
				}));
				';
			}
		}
		echo '
	});
</script>';