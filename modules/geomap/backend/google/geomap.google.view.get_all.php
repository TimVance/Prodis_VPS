<?php
/**
 * Шаблон вывода нескольких точек на карте "Google Maps"
 * 
 * @package    DIAFAN.CMS
 * @author     diafancms.com
 * @version    6.0
 * @license    http://www.diafancms.com/license.html
 * @copyright  Copyright (c) 2003-2018 Diafan (http://www.diafancms.com/) 
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

if(! $this->diafan->configmodules("google_api_key", "geomap"))
{
	echo $this->diafan->_('Заполните поле API ключ в настройках модуля.');
	return;
}

$center = '55.76, 37.64';

if($this->diafan->configmodules("google_center", "geomap"))
{
	$center = $this->diafan->configmodules("google_center", "geomap");
}
$zoom = '13';
if($this->diafan->configmodules("google_zoom", "geomap"))
{
	$zoom = $this->diafan->configmodules("google_zoom", "geomap");
}

echo '<div id="geomap_map" style="width:100%; height:800px"></div>';

echo '<script type="text/javascript">
var geomap_map_all;
function init_geomap_google_all() {
	geomap_map_all = new google.maps.Map(document.getElementById("geomap_map"), {
	  center: new google.maps.LatLng('.$center.'),
	  zoom: '.$zoom.'
	});';
	if(empty($result["rows"]))
	{
		echo '
		var geomap_marker_all = new google.maps.Marker({
			position: new google.maps.LatLng('.$center.'),
			map: geomap_map_all
		});';
	}
	else
	{
		foreach($result["rows"] as $i => $row)
		{
			echo '
			var geomap_marker_all_'.$i.' = new google.maps.Marker({
				position: new google.maps.LatLng('.$row["point"].'),
				map: geomap_map_all,
				title: "'.$row["name"].'",
			});';
		}
	}
	echo '
}
</script>';

if(! isset($GLOBALS['include_geomap_google']))
{
	echo '<script async defer src="https://maps.googleapis.com/maps/api/js?key='.$this->diafan->configmodules("google_api_key", "geomap").'&callback=init_geomap_google_all"></script>';
	$GLOBALS['include_geomap_google'] = true;
}