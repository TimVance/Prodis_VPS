<?php
/**
 * Cводные статистические данные для событий
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

/**
 * Visitors_admin_dashboard
 */
class Visitors_admin_dashboard extends Diafan
{
	/**
	 * @var string название таблицы
	 */
	public $name = 'Посещаемость';

	/**
	 * @var integer порядковый номер для сортировки
	 */
	public $sort = 1;

	/**
	 * @var string нет элементов
	 */
	public $empty_rows = 'Нет данных.';

	/**
	 * Выводит контент модуля
	 * @return string
	 */
	public function show()
	{
		//$text .= '<ul class="list list_dash do_auto_width"><li class="item">
		//	<div class="item__in"><div class="text">'.$this->diafan->_($this->empty_rows).'</div></div></li></ul>';
		//return $text;

		$text = '';
		if(! $this->diafan->_visitors->counter_is_enable())
		{
			$text .= '<div class="error">'.sprintf($this->diafan->_('Требуется активировать ведение Статистики cms в %sнастройках%s модуля.'), '<a href="'.BASE_PATH_HREF.'visitors/config/">', '</a>').'</div>';
		}
		else
		{
			$html_code = $this->js_code = '';
			$html_code .= $this->get_traffic();
			if(empty($html_code))
			{
				$text .= '<div class="commentary">'.$this->diafan->_('Ответ от Метрики cms не получен. Пока не собраны статистические данные.').'</div>';
			}
			else
			{
				$text .= $html_code;
				if(! empty($this->js_code))
				{
					$this->diafan->_admin->js_code[__CLASS__] = '<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="'
						.Custom::path('modules/visitors/admin/js/excanvas.min.js').'"></script><![endif]-->
						<script language="javascript" type="text/javascript">
							$(function() {
								function run_after_ready() {
									if( window.$.plot && window.$.plot.formatDate ) {'
									.$this->js_code
									.'}
									else
									{
										window.setTimeout( run_after_ready, 50 );
									}
								}
								run_after_ready();
							});
						</script>';
				}
			}
		}
		$text .= '</li></ul>';
		return $text;
	}

	/**
	 * Выводит статистику количества визитов, просмотров и уникальных посетителей
	 *
	 * @return string
	 */
	private function get_traffic()
	{
		// формируем статистику количества визитов, просмотров и уникальных посетителей
		$this->diafan->_visitors->counter_set_traffic();
		if(! $stat = $this->diafan->_visitors->counter_traffic())
		{
			return false;
		}
		if(empty($stat["data"]))
		{
			return false;
		}

		$this->diafan->_admin->css_view[] = 'modules/visitors/admin/css/visitors.counter.flot.css';
		$this->diafan->_admin->js_view[] = 'modules/visitors/admin/js/jquery.flot.min.js';
		$this->diafan->_admin->js_view[] = 'modules/visitors/admin/js/date.format.js';

		$visits = $users = $pageviews = '';
		foreach ($stat["data"] as $key => $value) {
			$visits .= ($key == 0 ? '' : ', ').'[Date.parse("'.$value["categories"].'"), '.$value["visits"].']';
			$users .= ($key == 0 ? '' : ', ').'[Date.parse("'.$value["categories"].'"), '.$value["users"].']';
			$pageviews .= ($key == 0 ? '' : ', ').'[Date.parse("'.$value["categories"].'"), '.$value["pageviews"].']';
		}
		$min = $stat["min"]["visits"];
		$min = $min > $stat["min"]["users"] ? $min : $stat["min"]["users"];
		$min = $min > $stat["min"]["pageviews"] ? $min : $stat["min"]["pageviews"];
		$max = $stat["max"]["visits"];
		$max = $max > $stat["max"]["users"] ? $max : $stat["max"]["users"];
		$max = $max > $stat["max"]["pageviews"] ? $max : $stat["max"]["pageviews"];
		$max += ($min > 150 ? $min : 150);

		$this->js_code .= '
		var dataset_traffics = {
			"visits": {
				label: "'.$this->diafan->_("посетители").'",
				data: ['.$visits.']
			},
			"users": {
				label: "'.$this->diafan->_("новые посетители").'",
				data: ['.$users.']
			},
			"pageviews": {
				label: "'.$this->diafan->_("просмотры").'",
				data: ['.$pageviews.']
			},
		};

		// hard-code color indices to prevent them from shifting as
		// countries are turned on/off
		var i = 0;
		$.each(dataset_traffics, function(key, val) {
			val.color = i;
			++i;
		});

		// insert checkboxes
		var choiceContainer_traffic = $("#co_traffic_choices");
		$.each(dataset_traffics, function(key, val) {
			choiceContainer_traffic.append("<tr><td><input type=\'checkbox\' name=\'" + key +
			"\' checked=\'checked\' id=\'id" + key + "\'></input></td>" +
			"<td><label for=\'id" + key + "\'>"
			+ val.label + "</label></td></tr>");
		});

		choiceContainer_traffic.find("input").click(traffic_redraw);
		$(window).resize(function() {
			traffic_redraw();
		});

		function traffic_redraw() {
			var data = [];
			choiceContainer_traffic.find("input:checked").each(function () {
				var key = $(this).attr("name");
				if (key && dataset_traffics[key]) {
					data.push(dataset_traffics[key]);
				}
			});
			if (data.length > 0) {
				$.plot("#co_traffic_placeholder", data, {
					series: {
						lines: {
							show: true
						},
						points: {
							show: true
						},
					},
					grid: {
						hoverable: true,
						clickable: true,
						borderWidth: 0,
						borderColor: null
					},
					legend: {
						show: true,
						container: $(".co_traffic_legend")
					},
					yaxis: {
						min: 0,
						max: '.$max.'
					},
					xaxis: {
						mode: "time",
						timeformat: "%d-%m-%y"
					}
				});
			}
		}
		traffic_redraw();

		$("<div id=\'co_traffic_tooltip\'></div>").css({
			position: "absolute",
			display: "none",
			border: "1px solid #fdd",
			padding: "2px",
			"background-color": "#fee",
			opacity: 0.80
		}).appendTo("body");

		$("#co_traffic_placeholder").on("plothover", function (event, pos, item) {
			if ($("#co_traffic_enablePosition:checked").length > 0) {
				var px = pos.x.toFixed(0),
					py = pos.y.toFixed(0),
					pd = new Date(px*1);
				py = py*1; if(py < 0) { py = 0; }
				$("#co_traffic_hoverdata").text(pd.format("dd-mm-yyyy") + ", " + py);

				if ($("#co_traffic_enableTooltip:checked").length > 0) {
					if (item) {
						var x = item.datapoint[0].toFixed(0),
							y = item.datapoint[1].toFixed(0),
							d = new Date(x*1);
						$("#co_traffic_tooltip").html(d.format("dd-mm-yyyy") + " " + item.series.label + " - " + y)
							.css({top: item.pageY+5, left: item.pageX+5})
							.fadeIn(200);
					} else {
						$("#co_traffic_tooltip").hide();
					}
				}
			}
		});

		/*$("#co_traffic_placeholder").on("plotclick", function (event, pos, item) {
			if(item) {
				$("#co_traffic_clickdata").text(" - click point " + item.dataIndex + " in " + item.series.label);
				plot.highlight(item.series, item.datapoint);
			}
		});*/';

		return '
		<div class="co_traffic_content">
			<!--<h2>'.$this->diafan->_("Посетители").'</h2>-->
			<div class="co_traffic_container">
				<div class="co_traffic_legend"></div>
				<div id="co_traffic_placeholder" class="co_traffic_placeholder"></div>
			</div>
			<div class="co_traffic_option">
				<div class="co_traffic_choices"><table id="co_traffic_choices"></table></div>
				<div class="co_traffic_setting">
					<div class="co_traffic_position">
						<input id="co_traffic_enablePosition" type="checkbox" checked="checked"></input><label>'.$this->diafan->_("Отображать курсор").': </label>
						<span id="co_traffic_hoverdata"></span> <span id="co_traffic_clickdata"></span>
					</div>
					<div class="co_traffic_tooltip">
						<input id="co_traffic_enableTooltip" type="checkbox" checked="checked"></input><label>'.$this->diafan->_("Отображать подсказку").'</label>
					</div>
				</div>
			</div>
		</div>';
	}
}
