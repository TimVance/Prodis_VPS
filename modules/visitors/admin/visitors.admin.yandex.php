<?php
/**
 * Редактирование статистических данных Yandex
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if ( ! defined('DIAFAN'))
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
 * Visitors_admin
 */
class Visitors_admin_yandex extends Frame_admin
{
	/**
	 * @var string javascript-code
	 */
	private $js_code = '';

	/**
	 * Выводит контент модуля
	 *
	 * @return void
	 */
	public function show()
	{
		if(! $this->diafan->configmodules('yandex_mail_login', 'visitors')
		|| ! $this->diafan->configmodules('yandex_client_id', 'visitors')
		|| ! $this->diafan->configmodules('yandex_client_password', 'visitors')
		|| ! $this->diafan->configmodules('yandex_counter_id', 'visitors'))
		{
			echo '<div class="error">'.sprintf($this->diafan->_('Требуется настройка для использования Яндекс API. Заполните соответствующие параметры соединения в %sнастройках%s модуля.'), '<a href="'.BASE_PATH_HREF.'visitors/config/">', '</a>').'</div>';
		}
		else
		{
			$html_code = $this->js_code = '';
			$html_code .= $this->get_traffic();
			$html_code .= $this->get_traffic_source();
			$html_code .= $this->get_bounce_rate();
			$html_code .= $this->get_duration();
			if(empty($html_code))
			{
				echo '<div class="error">'.sprintf($this->diafan->_('Ответ от Яндекс API не получен. Проверьте корректность заполнения соответствующих параметров соединения в %sнастройках%s модуля.'), '<a href="'.BASE_PATH_HREF.'visitors/config/">', '</a>').'</div>';
			}
			else
			{
				echo $html_code;
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
	}

	/**
	 * Выводит статистику количества визитов, просмотров и уникальных посетителей
	 *
	 * @return string
	 */
	private function get_traffic()
	{
		if(! $stat = $this->diafan->_visitors->yandex_traffic())
		{
			if(! $stat = $this->diafan->_visitors->yandex_traffic(true))
			{
				return false;
			}
		}
		if(empty($stat["data"]))
		{
			return false;
		}

		$this->diafan->_admin->css_view[] = 'modules/visitors/admin/css/visitors.yandex.flot.css';
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
		var datasets = {
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
		$.each(datasets, function(key, val) {
			val.color = i;
			++i;
		});

		// insert checkboxes
		var choiceContainer = $("#ya_traffic_choices");
		$.each(datasets, function(key, val) {
			choiceContainer.append("<tr><td><input type=\'checkbox\' name=\'" + key +
			"\' checked=\'checked\' id=\'id" + key + "\'></input></td>" +
			"<td><label for=\'id" + key + "\'>"
			+ val.label + "</label></td></tr>");
		});

		choiceContainer.find("input").click(traffic_redraw);
		$(window).resize(function() {
			traffic_redraw();
		});

		function traffic_redraw() {
			var data = [];
			choiceContainer.find("input:checked").each(function () {
				var key = $(this).attr("name");
				if (key && datasets[key]) {
					data.push(datasets[key]);
				}
			});
			if (data.length > 0) {
				$.plot("#ya_traffic_placeholder", data, {
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
						container: $(".ya_traffic_legend")
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

		$("<div id=\'ya_traffic_tooltip\'></div>").css({
			position: "absolute",
			display: "none",
			border: "1px solid #fdd",
			padding: "2px",
			"background-color": "#fee",
			opacity: 0.80
		}).appendTo("body");

		$("#ya_traffic_placeholder").on("plothover", function (event, pos, item) {
			if ($("#ya_traffic_enablePosition:checked").length > 0) {
				var px = pos.x.toFixed(0),
					py = pos.y.toFixed(0),
					pd = new Date(px*1);
				py = py*1; if(py < 0) { py = 0; }
				$("#ya_traffic_hoverdata").text(pd.format("dd-mm-yyyy") + ", " + py);

				if ($("#ya_traffic_enableTooltip:checked").length > 0) {
					if (item) {
						var x = item.datapoint[0].toFixed(0),
							y = item.datapoint[1].toFixed(0),
							d = new Date(x*1);
						$("#ya_traffic_tooltip").html(d.format("dd-mm-yyyy") + " " + item.series.label + " - " + y)
							.css({top: item.pageY+5, left: item.pageX+5})
							.fadeIn(200);
					} else {
						$("#ya_traffic_tooltip").hide();
					}
				}
			}
		});

		/*$("#ya_traffic_placeholder").on("plotclick", function (event, pos, item) {
			if(item) {
				$("#ya_traffic_clickdata").text(" - click point " + item.dataIndex + " in " + item.series.label);
				plot.highlight(item.series, item.datapoint);
			}
		});*/';

		return '
		<div class="ya_traffic_content">
			<h2>'.$this->diafan->_("Статистика посещений").'</h2>
			<div class="ya_traffic_container">
				<div class="ya_traffic_legend"></div>
				<div id="ya_traffic_placeholder" class="ya_traffic_placeholder"></div>
			</div>
			<div class="ya_traffic_option">
				<div class="ya_traffic_choices"><table id="ya_traffic_choices"></table></div>
				<div class="ya_traffic_setting">
					<div class="ya_traffic_position">
						<input id="ya_traffic_enablePosition" type="checkbox" checked="checked"></input><label>'.$this->diafan->_("Отображать курсор").': </label>
						<span id="ya_traffic_hoverdata"></span> <span id="ya_traffic_clickdata"></span>
					</div>
					<div class="ya_traffic_tooltip">
						<input id="ya_traffic_enableTooltip" type="checkbox" checked="checked"></input><label>'.$this->diafan->_("Отображать подсказку").'</label>
					</div>
				</div>
			</div>
		</div>';
	}

	/**
	 * Выводит статистику по источникам трафика (визиты)
	 *
	 * @return string
	 */
	private function get_traffic_source()
	{
		if(! $stat = $this->diafan->_visitors->yandex_traffic_source())
		{
			if(! $stat = $this->diafan->_visitors->yandex_traffic_source(true))
			{
				return false;
			}
		}
		if(empty($stat["data"]))
		{
			return false;
		}

		$this->diafan->_admin->css_view[] = 'modules/visitors/admin/css/visitors.yandex.flot.css';
		$this->diafan->_admin->js_view[] = 'modules/visitors/admin/js/jquery.flot.min.js';

		$this->js_code .= '
		function traffic_source_redraw() {
			var data = [';
			$first_key = true;
			foreach ($stat["data"] as $key => $value) {
				$this->js_code .=  ($first_key ? '' : ', ');
				$this->js_code .=  '{label: "'.$this->diafan->_($value["categories"]).'", data: '.$value["value"].'}';
				$first_key = false;
			}
			$this->js_code .= '];
			$.plot("#ya_traffic_source_placeholder", data, {
				series: {
					pie: {
						show: true,
						radius: 4/5, // radius: 1,
						innerRadius: 0.25,
						label: {
							show: true,
							radius: 1/2,
							formatter: labelFormatter,
							background: {
								opacity: 0.8
							},
							threshold: 0.01 // отображать легенду только для результатов, превышающих 1%
						}
					}
				},
				legend: {
					show: true
				}
			});
			function labelFormatter(label, series) {
				return "<div class=\'label_formatter\'>"
					//+ label + "<br/>"
					+ Math.round(series.percent) + "%</div>";
			}
		}
		traffic_source_redraw();
		$(window).resize(function() {
			traffic_source_redraw();
		});

		';

		return '
		<div class="ya_traffic_source_content">
			<h2>'.$this->diafan->_("Статистика по источникам трафика").'</h2>
			<div class="ya_traffic_source_container">
				<div id="ya_traffic_source_placeholder" class="ya_traffic_source_placeholder"></div>
			</div>
		</div>';
	}

	/**
	 * Выводит статистику по отказам
	 *
	 * @return string
	 */
	private function get_bounce_rate()
	{
		if(! $stat = $this->diafan->_visitors->yandex_bounce_rate())
		{
			if(! $stat = $this->diafan->_visitors->yandex_bounce_rate(true))
			{
				return false;
			}
		}
		if(empty($stat["data"]))
		{
			return false;
		}

		$this->diafan->_admin->css_view[] = 'modules/visitors/admin/css/visitors.yandex.flot.css';
		$this->diafan->_admin->js_view[] = 'modules/visitors/admin/js/jquery.flot.min.js';

		/*$min = round($stat["min"], 2);
		$max = round($stat["max"], 2);
		$max += ($min > 100 ? $min : 100);*/
		$min = 0;
		$max = 100;

		$this->js_code .= '
		function bounce_rate_redraw() {
			var data = [{
				data: [';
		$first_key = true;
		foreach ($stat["data"] as $key => $value) {
			$this->js_code .=  ($first_key ? '' : ', ');
			$this->js_code .=  '[Date.parse("'.$value["categories"].'"), '.$value["value"].']';
			$first_key = false;
		}
		$this->js_code .= '],
				label: "отказы",
				color: 1
			}];
			$.plot("#ya_bounce_rate_placeholder", data, {
				series: {
					lines: {
						show: true,
						fill: true,
					},
				},
				legend: {
					show: false
				},
				yaxis: {
					min: 0,
					max: '.$max.',
					show: false
				},
				xaxis: {
					mode: "time",
					timeformat: "%d-%m-%y",
					show: false
				},
				grid: {
					borderWidth: 0.5,
					borderColor: "#ccc"
				}
			});
		}
		bounce_rate_redraw();
		$(window).resize(function() {
			bounce_rate_redraw();
		});';

		$bounce_rate = '';
		end($stat["data"]);
		if(! is_null($key = key($stat["data"])))
		{
			$value = current($stat["data"]);
			$end = ! empty($value["value"]) ? $value["value"] : 0;
			$bounce_rate .= '<span class="ya_bounce_rate_end">'.round($end, 2).' %'.'</span>';
			prev($stat["data"]);
			if(! is_null($key = key($stat["data"])))
			{
				$value = current($stat["data"]);
				$prev = ! empty($value["value"]) ? $value["value"] : 0;
				$val = $end - $prev;
				if(! $plus = ($val >= 0)) $val = $val * -1;
				$bounce_rate .= '<span class="ya_bounce_rate_prev'.($plus ? ' plus' : '').'" title="'.$this->diafan->_("По сравнению с предыдущим периодом").'">'
								.($plus ? '+' : '-').' '.round($val, 2).' %'
								.'</span>';
			}
		}

		return '
		<div class="ya_bounce_rate_content">
			<h2>'.$this->diafan->_("Статистика по отказам").'</h2>
			<div class="ya_bounce_rate_container">
				<div class="ya_bounce_rate_value">'.$bounce_rate.'</div>
				<div id="ya_bounce_rate_placeholder" class="ya_bounce_rate_placeholder"></div>
			</div>
		</div>';
	}

	/**
	 * Выводит статистику по времени на сайте
	 *
	 * @return string
	 */
	private function get_duration()
	{
		if(! $stat = $this->diafan->_visitors->yandex_duration())
		{
			if(! $stat = $this->diafan->_visitors->yandex_duration(true))
			{
				return false;
			}
		}
		if(empty($stat["data"]))
		{
			return false;
		}

		$this->diafan->_admin->css_view[] = 'modules/visitors/admin/css/visitors.yandex.flot.css';
		$this->diafan->_admin->js_view[] = 'modules/visitors/admin/js/jquery.flot.min.js';

		$min = round($stat["min"], 2);
		$max = round($stat["max"], 2);
		$max += ($min > 100 ? $min : 100);

		$this->js_code .= '
		function duration_redraw() {
			var data = [{
				data: [';
		$first_key = true;
		foreach ($stat["data"] as $key => $value) {
			$this->js_code .=  ($first_key ? '' : ', ');
			$this->js_code .=  '[Date.parse("'.$value["categories"].'"), '.$value["value"].']';
			$first_key = false;
		}
		$this->js_code .= '],
				label: "отказы",
				color: 1
			}];
			$.plot("#ya_duration_placeholder", data, {
				series: {
					lines: {
						show: true,
						fill: true,
					},
				},
				legend: {
					show: false
				},
				yaxis: {
					min: 0,
					max: '.$max.',
					show: false
				},
				xaxis: {
					mode: "time",
					timeformat: "%d-%m-%y",
					show: false
				},
				grid: {
					borderWidth: 0.5,
					borderColor: "#ccc"
				}
			});
		}
		duration_redraw();
		$(window).resize(function() {
			duration_redraw();
		});';

		$duration = '';
		end($stat["data"]);
		if(! is_null($key = key($stat["data"])))
		{
			$value = current($stat["data"]);
			$end = ! empty($value["value"]) ? $value["value"] : 0;
			$end_str = ((int) date('i', $end)) . ':' . date('s', $end); // $value = date('H:i:s', $end);
			$duration .= '<span class="ya_duration_end">'.$end_str.' '.$this->diafan->_("мин").'</span>';
			prev($stat["data"]);
			if(! is_null($key = key($stat["data"])))
			{
				$value = current($stat["data"]);
				$prev = ! empty($value["value"]) ? $value["value"] : 0;
				$val = $prev > 0 ? (($end * 100 / $prev) - 100) : ($end > 0 ? 100 : 0);
				if(! $plus = ($val >= 0)) $val = $val * -1;
				$duration .= '<span class="ya_duration_prev'.($plus ? ' plus' : '').'" title="'.$this->diafan->_("По сравнению с предыдущим периодом").'">'
								.($plus ? '+' : '-').' '.round($val, 2).' %'
								.'</span>';
			}
		}

		return '
		<div class="ya_duration_content">
			<h2>'.$this->diafan->_("Статистика по времени на сайте").'</h2>
			<div class="ya_duration_container">
				<div class="ya_duration_value">'.$duration.'</div>
				<div id="ya_duration_placeholder" class="ya_duration_placeholder"></div>
			</div>
		</div>';
	}
}
