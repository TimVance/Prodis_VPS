<?php
/**
 * Настройки карты «Яндекс.Карты» для административного интерфейса
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

class Geomap_yandex_admin extends Diafan
{
	public $config = array(
		"name" => 'Яндекс.Карты',
		"params" => array(
			'center' => array(
				'name' => 'Центр карты',
				'type' => 'function',
			),
			'zoom' => array(
				'name' => 'Масштаб',
				'help' => 'Оптимально 13-15.',
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
		echo '<div class="unit">
			<div class="infofield">'.$this->diafan->_('Центр карты').'</div>';

			echo '<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>';
			
			echo '<div id="geomap_yandex_map" style="width: 100%; height: 400px;"></div>
			<input type="hidden" name="yandex_center" value="'.$value.'" id="geomap_yandex_point">';
			
			echo '<script type="text/javascript">
				ymaps.ready(function(){
					var yandex_map = new ymaps.Map("geomap_yandex_map", {
					    center: ['.($value ? $value : '55.76, 37.64').'], 
					    zoom: '.(! empty($values["yandex_zoom"]) ? $values["yandex_zoom"] : 13).',
						controls: []
					});
					var searchControl = new ymaps.control.SearchControl();
					yandex_map.controls.add(searchControl);
					var yandex_marker = new ymaps.GeoObject({
						// Описание геометрии.
						geometry: {
						    type: "Point",
						    coordinates: ['.($value ? $value : '55.76, 37.64').']
						},
					}, {
						draggable: true
					});
					yandex_marker.events.add("dragend", function (e) {
						var coords = e.get("target").geometry.getCoordinates();
						document.getElementById("geomap_yandex_point").value = coords.join(", ");
					});
					yandex_map.geoObjects.add(yandex_marker);
					yandex_map.events.add("click", function (e) {
						yandex_marker.geometry.setCoordinates(e.get("coords"));
						var coords = e.get("coords");
						document.getElementById("geomap_yandex_point").value = coords.join(", ");
					});
					searchControl.events.add("resultselect", function (e) {
						var results = searchControl.getResultsArray(),
						selected = e.get("index"),
						point = results[selected].geometry.getCoordinates();
						yandex_marker.geometry.setCoordinates(point);
						document.getElementById("geomap_yandex_point").value = point.join(", ");
						searchControl.hideResult();
					});
				});
			</script>';
			echo '
		</div>';
	}
}