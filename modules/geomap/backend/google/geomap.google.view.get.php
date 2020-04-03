<?php
/**
 * Шаблон точки на карте "Google Maps"
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

if(! isset($GLOBALS['include_geomap_google']))
{
	echo '<script src="https://maps.googleapis.com/maps/api/js?key='.$this->diafan->configmodules("google_api_key", "geomap").'"></script>';
	$GLOBALS['include_geomap_google'] = true;
}

echo '<div id="geomap_map_'.$i.'" style="width: 100%; height:400px"></div>';

echo '<script type="text/javascript">
var geomap_map_'.$i.' = new google.maps.Map(document.getElementById("geomap_map_'.$i.'"), {
    center: new google.maps.LatLng('.$center.'),
    zoom: '.$zoom.'
});
';
if($result["point"])
{
	echo '
	var geomap_marker_'.$i.' = new google.maps.Marker({
		position: new google.maps.LatLng('.$center.'),
		map: geomap_map_'.$i.'
	});';
}
echo '
</script>';