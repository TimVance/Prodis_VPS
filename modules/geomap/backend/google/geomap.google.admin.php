<?php
/**
 * Настройки карты "Google Maps" для административного интерфейса
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

class Geomap_google_admin extends Diafan
{
	public $config = array(
		"name" => 'Google Maps',
		"params" => array(
			'api_key' => array(
				'name' => 'API key',
				'type' => 'text',
				'help' => 'https://developers.google.com/console/help/console',
			),
			'center' => array(
				'name' => 'Центр карты и масштаб',
				'type' => 'function',
			),
			'zoom' => array(
				'type' => 'none',
			),
		)
	);
	
	/**
	 * Редактирвание поля "Центр карты"
	 *
	 * @return void
	 */
	public function edit_variable_center($value, $values)
	{
		if(empty($values["api_key"]))
		{
			echo '<div class="unit">'
			.$this->diafan->_('Заполните поле API ключ в настройках модуля.');
			echo ' ';
			echo $this->diafan->_('Затем определите центр карты.')
			.'</div>';
			return;
		}
		$value = ($value ? $value : '55.76, 37.64');
		echo '<div class="unit">
			<div class="infofield">'.$this->diafan->_('Центр карты и масштаб').'</div>';
			
			echo '<div id="geomap_google_map" style="width: 100%; height: 400px;"></div>
			<input type="hidden" name="google_center" value="'.$value.'" id="geomap_google_point">
			<input type="hidden" name="google_zoom" value="'.(! empty($values["zoom"]) ? $values["zoom"] : '').'" id="geomap_google_zoom">';
			
			echo '<script type="text/javascript">
			var geomap_google_map;
			function google_init_geomap(){
				geomap_google_map = new google.maps.Map(document.getElementById("geomap_google_map"), {
					center: new google.maps.LatLng('.$value.'),
					zoom: '.(! empty($values["zoom"]) ? $values["zoom"] : 13).'
				});
				geomap_google_map.addListener("center_changed", function(e) {
					document.getElementById("geomap_google_point").value = geomap_google_map.getCenter();
				});
				geomap_google_map.addListener("zoom_changed", function(e) {
					document.getElementById("geomap_google_zoom").value = geomap_google_map.getZoom();
				});
			}
			</script>
			
			<script async defer src="https://maps.googleapis.com/maps/api/js?key='.$values["api_key"].'&callback=google_init_geomap"></script>';
			echo '
		</div>';
	}
	
	/**
	 * Сохранение поля "Центр карты"
	 *
	 * @return void
	 */
	public function save_variable_center()
	{
		return str_replace(array('(', ')'), '', $_POST["google_center"]);
	}
}