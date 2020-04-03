<?php
/**
 * Шаблон редактирования точки на карте Яндекс.Карты
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

echo '<div id="geomap_yandex_map_add" style="width: 100%; height: 400px;"></div>
<a href="#" onclick="document.getElementById(\'geomap_yandex_point\').value = \'\';if(marker_yandex) marker_yandex.options.set(\'visible\', false);return false">
<i class="fa fa-close"></i></a> '.$this->diafan->_('Удалить точку').'

<input type="hidden" name="geomap_point" value="'.$result["point"].'" id="geomap_yandex_point">';

echo '<script type="text/javascript">
	var map_yandex, marker_yandex;
	ymaps.ready(function(){
		map_yandex = new ymaps.Map("geomap_yandex_map_add", {
		    center: ['.$center.'], 
		    zoom: '.$zoom.',
			controls: []
		});
		var searchControl = new ymaps.control.SearchControl();
		map_yandex.controls.add(searchControl);
		marker_yandex = new ymaps.GeoObject({
			// Описание геометрии.
			geometry: {
			    type: "Point",
			    coordinates: ['.$center.']
			},
		}, {
			draggable: true
		});
		marker_yandex.events.add("dragend", function (e) {
			var coords = e.get("target").geometry.getCoordinates();
			document.getElementById("geomap_yandex_point").value = coords.join(", ");
		});
		map_yandex.events.add("click", function (e) {
			var coords = e.get("coords");
			marker_yandex.geometry.setCoordinates(coords);
			marker_yandex.options.set("visible", true);
			document.getElementById("geomap_yandex_point").value = coords.join(", ");
		});
		map_yandex.geoObjects.add(marker_yandex);
		searchControl.events.add("resultselect", function (e) {
			var results = searchControl.getResultsArray(),
			selected = e.get("index"),
			point = results[selected].geometry.getCoordinates();
			marker_yandex.geometry.setCoordinates(point);
			document.getElementById("geomap_yandex_point").value = point.join(", ");
			searchControl.hideResult();
		});
	});
</script>';