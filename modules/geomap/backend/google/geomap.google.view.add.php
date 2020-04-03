<?php
/**
 * Шаблон редактирования точки на карте "Google Maps"
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
if($result["point"])
{
	$center = $result["point"];
}
elseif($this->diafan->configmodules("google_center", "geomap"))
{
	$center = $this->diafan->configmodules("google_center", "geomap");
}
$zoom = '13';
if($this->diafan->configmodules("google_zoom", "geomap"))
{
	$zoom = $this->diafan->configmodules("google_zoom", "geomap");
}

if(empty($GLOBALS['include_geomap_i']))
{
	$GLOBALS['include_geomap_i'] = 0;
}
$i = intval($GLOBALS['include_geomap_i']);
$i++;
$GLOBALS['include_geomap_i'] = $i;

echo '<div id="geomap_map_'.$i.'" style="width: 100%; height: 400px;"></div>

<input type="hidden" name="geomap_point" value="'.$result["point"].'" id="geomap_google_point_'.$i.'">';

if(! isset($GLOBALS['include_geomap_google']))
{
	echo '<script src="https://maps.googleapis.com/maps/api/js?key='.$this->diafan->configmodules("google_api_key", "geomap").'"></script>';
	$GLOBALS['include_geomap_google'] = true;
}

echo '<script type="text/javascript">
var geomap_map_'.$i.' = new google.maps.Map(document.getElementById("geomap_map_'.$i.'"), {
    center: new google.maps.LatLng('.$center.'),
    zoom: '.$zoom.'
});

var geomap_marker_'.$i.' = new google.maps.Marker({
	position: new google.maps.LatLng('.$center.'),
	draggable:true,
	map: geomap_map_'.$i.'
});

geomap_map_'.$i.'.addListener("click", function(e) {
	geomap_marker_'.$i.'.setMap(null);
	geomap_marker_'.$i.' = new google.maps.Marker({
	  position: e.latLng,
	  map: geomap_map_'.$i.'
	});
	document.getElementById("geomap_google_point_'.$i.'").value = str_replace(")", "", str_replace("(", "", e.latLng));
});

geomap_marker_'.$i.'.addListener("dragend", function(e) {
	document.getElementById("geomap_google_point_'.$i.'").value = str_replace(")", "", str_replace("(", "", e.latLng));
});

</script>

<a href="#" onclick="geomap_marker_'.$i.'.setMap(null);document.getElementById(\'geomap_google_point_'.$i.'\').value = \'\';return false">
<i class="fa fa-close"></i></a> '.$this->diafan->_('Delete point');
