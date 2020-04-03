<?php
/**
 * Редактирование сводных статистических данных
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
class Visitors_admin extends Frame_admin
{
	/**
	 * @var string javascript-code
	 */
	private $js_code = '';

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		echo '<br>';
		echo '<div class="commentary">'.sprintf($this->diafan->_('Бета-версия модуля. Замечания отправляйте в %sТехническую поддержку%s DIAFAN.CMS.'), '<a href="https://user.diafan.ru/support/">', '</a>').'</div>';
		echo '<br>';

		if(! $this->diafan->_visitors->counter_is_enable())
		{
			echo '<br>';
			echo '<div class="error">'.sprintf($this->diafan->_('Требуется активировать ведение Статистики cms в %sнастройках%s модуля.'), '<a href="'.BASE_PATH_HREF.'visitors/config/">', '</a>').'</div>';
		}
		else
		{
			$html_code = $this->js_code = '';
			$html_code .= $this->get_traffic();
			$html_code .= $this->get_traffic_source();
			$html_code .= $this->get_traffic_search_bot();
			$html_code .= $this->get_traffic_bot();
			$html_code .= $this->get_traffic_pages();
			$html_code .= $this->get_traffic_pages_bot();
			$html_code .= $this->get_traffic_pages_search_bot();
			$html_code .= $this->get_traffic_names_search_bot();
			if(empty($html_code))
			{
				echo '<div class="commentary">'.$this->diafan->_('Ответ от Метрики cms не получен. Пока не собраны статистические данные.').'</div>';
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
			<h2>'.$this->diafan->_("Посетители").'</h2>
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

	/**
	 * Выводит статистику количества визитов, просмотров и уникальных поисковых ботов
	 *
	 * @return string
	 */
	private function get_traffic_search_bot()
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

		$visits_search_bot = $users_search_bot = $pageviews_search_bot = '';
		foreach ($stat["data"] as $key => $value) {
			$visits_search_bot .= ($key == 0 ? '' : ', ').'[Date.parse("'.$value["categories"].'"), '.$value["visits_search_bot"].']';
			$users_search_bot .= ($key == 0 ? '' : ', ').'[Date.parse("'.$value["categories"].'"), '.$value["users_search_bot"].']';
			$pageviews_search_bot .= ($key == 0 ? '' : ', ').'[Date.parse("'.$value["categories"].'"), '.$value["pageviews_search_bot"].']';
		}
		$min = $stat["min"]["visits_search_bot"];
		$min = $min > $stat["min"]["users_search_bot"] ? $min : $stat["min"]["users_search_bot"];
		$min = $min > $stat["min"]["pageviews_search_bot"] ? $min : $stat["min"]["pageviews_search_bot"];
		$max = $stat["max"]["visits_search_bot"];
		$max = $max > $stat["max"]["users_search_bot"] ? $max : $stat["max"]["users_search_bot"];
		$max = $max > $stat["max"]["pageviews_search_bot"] ? $max : $stat["max"]["pageviews_search_bot"];
		$max += ($min > 150 ? $min : 150);

		$this->js_code .= '
		var dataset_traffic_search_bot = {
			"visits_search_bot": {
				label: "'.$this->diafan->_("поисковые боты").'",
				data: ['.$visits_search_bot.']
			},
			"users_search_bot": {
				label: "'.$this->diafan->_("новые поисковые боты").'",
				data: ['.$users_search_bot.']
			},
			"pageviews_search_bot": {
				label: "'.$this->diafan->_("просмотры").'",
				data: ['.$pageviews_search_bot.']
			},
		};

		// hard-code color indices to prevent them from shifting as
		// countries are turned on/off
		var i = 0;
		$.each(dataset_traffic_search_bot, function(key, val) {
			val.color = i;
			++i;
		});

		// insert checkboxes
		var choiceContainer_traffic_search_bot = $("#co_traffic_search_bot_choices");
		$.each(dataset_traffic_search_bot, function(key, val) {
			choiceContainer_traffic_search_bot.append("<tr><td><input type=\'checkbox\' name=\'" + key +
			"\' checked=\'checked\' id=\'id" + key + "\'></input></td>" +
			"<td><label for=\'id" + key + "\'>"
			+ val.label + "</label></td></tr>");
		});

		choiceContainer_traffic_search_bot.find("input").click(traffic_search_bot_redraw);
		$(window).resize(function() {
			traffic_search_bot_redraw();
		});

		function traffic_search_bot_redraw() {
			var data = [];
			choiceContainer_traffic_search_bot.find("input:checked").each(function () {
				var key = $(this).attr("name");
				if (key && dataset_traffic_search_bot[key]) {
					data.push(dataset_traffic_search_bot[key]);
				}
			});
			if (data.length > 0) {
				$.plot("#co_traffic_search_bot_placeholder", data, {
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
						container: $(".co_traffic_search_bot_legend")
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
		traffic_search_bot_redraw();

		$("<div id=\'co_traffic_search_bot_tooltip\'></div>").css({
			position: "absolute",
			display: "none",
			border: "1px solid #fdd",
			padding: "2px",
			"background-color": "#fee",
			opacity: 0.80
		}).appendTo("body");

		$("#co_traffic_search_bot_placeholder").on("plothover", function (event, pos, item) {
			if ($("#co_traffic_search_bot_enablePosition:checked").length > 0) {
				var px = pos.x.toFixed(0),
					py = pos.y.toFixed(0),
					pd = new Date(px*1);
				py = py*1; if(py < 0) { py = 0; }
				$("#co_traffic_search_bot_hoverdata").text(pd.format("dd-mm-yyyy") + ", " + py);

				if ($("#co_traffic_search_bot_enableTooltip:checked").length > 0) {
					if (item) {
						var x = item.datapoint[0].toFixed(0),
							y = item.datapoint[1].toFixed(0),
							d = new Date(x*1);
						$("#co_traffic_search_bot_tooltip").html(d.format("dd-mm-yyyy") + " " + item.series.label + " - " + y)
							.css({top: item.pageY+5, left: item.pageX+5})
							.fadeIn(200);
					} else {
						$("#co_traffic_search_bot_tooltip").hide();
					}
				}
			}
		});

		/*$("#co_traffic_search_bot_placeholder").on("plotclick", function (event, pos, item) {
			if(item) {
				$("#co_traffic_search_bot_clickdata").text(" - click point " + item.dataIndex + " in " + item.series.label);
				plot.highlight(item.series, item.datapoint);
			}
		});*/';

		return '
		<div class="co_traffic_search_bot_content">
			<h2>'.$this->diafan->_("Поисковые боты").'</h2>
			<div class="co_traffic_search_bot_container">
				<div class="co_traffic_search_bot_legend"></div>
				<div id="co_traffic_search_bot_placeholder" class="co_traffic_search_bot_placeholder"></div>
			</div>
			<div class="co_traffic_search_bot_option">
				<div class="co_traffic_search_bot_choices"><table id="co_traffic_search_bot_choices"></table></div>
				<div class="co_traffic_search_bot_setting">
					<div class="co_traffic_search_bot_position">
						<input id="co_traffic_search_bot_enablePosition" type="checkbox" checked="checked"></input><label>'.$this->diafan->_("Отображать курсор").': </label>
						<span id="co_traffic_search_bot_hoverdata"></span> <span id="co_traffic_search_bot_clickdata"></span>
					</div>
					<div class="co_traffic_search_bot_tooltip">
						<input id="co_traffic_search_bot_enableTooltip" type="checkbox" checked="checked"></input><label>'.$this->diafan->_("Отображать подсказку").'</label>
					</div>
				</div>
			</div>
		</div>';
	}

	/**
	 * Выводит статистику количества визитов, просмотров и уникальных Спам-ботов и иных
	 *
	 * @return string
	 */
	private function get_traffic_bot()
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

		$visits_bot = $users_bot = $pageviews_bot = '';
		foreach ($stat["data"] as $key => $value) {
			$visits_bot .= ($key == 0 ? '' : ', ').'[Date.parse("'.$value["categories"].'"), '.$value["visits_bot"].']';
			$users_bot .= ($key == 0 ? '' : ', ').'[Date.parse("'.$value["categories"].'"), '.$value["users_bot"].']';
			$pageviews_bot .= ($key == 0 ? '' : ', ').'[Date.parse("'.$value["categories"].'"), '.$value["pageviews_bot"].']';
		}
		$min = $stat["min"]["visits_bot"];
		$min = $min > $stat["min"]["users_bot"] ? $min : $stat["min"]["users_bot"];
		$min = $min > $stat["min"]["pageviews_bot"] ? $min : $stat["min"]["pageviews_bot"];
		$max = $stat["max"]["visits_bot"];
		$max = $max > $stat["max"]["users_bot"] ? $max : $stat["max"]["users_bot"];
		$max = $max > $stat["max"]["pageviews_bot"] ? $max : $stat["max"]["pageviews_bot"];
		$max += ($min > 150 ? $min : 150);

		$this->js_code .= '
		var dataset_traffic_bot = {
			"visits_bot": {
				label: "'.$this->diafan->_("боты").'",
				data: ['.$visits_bot.']
			},
			"users_bot": {
				label: "'.$this->diafan->_("новые боты").'",
				data: ['.$users_bot.']
			},
			"pageviews_bot": {
				label: "'.$this->diafan->_("просмотры").'",
				data: ['.$pageviews_bot.']
			},
		};

		// hard-code color indices to prevent them from shifting as
		// countries are turned on/off
		var i = 0;
		$.each(dataset_traffic_bot, function(key, val) {
			val.color = i;
			++i;
		});

		// insert checkboxes
		var choiceContainer_traffic_bot = $("#co_traffic_bot_choices");
		$.each(dataset_traffic_bot, function(key, val) {
			choiceContainer_traffic_bot.append("<tr><td><input type=\'checkbox\' name=\'" + key +
			"\' checked=\'checked\' id=\'id" + key + "\'></input></td>" +
			"<td><label for=\'id" + key + "\'>"
			+ val.label + "</label></td></tr>");
		});

		choiceContainer_traffic_bot.find("input").click(traffic_bot_redraw);
		$(window).resize(function() {
			traffic_bot_redraw();
		});

		function traffic_bot_redraw() {
			var data = [];
			choiceContainer_traffic_bot.find("input:checked").each(function () {
				var key = $(this).attr("name");
				if (key && dataset_traffic_bot[key]) {
					data.push(dataset_traffic_bot[key]);
				}
			});
			if (data.length > 0) {
				$.plot("#co_traffic_bot_placeholder", data, {
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
						container: $(".co_traffic_bot_legend")
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
		traffic_bot_redraw();

		$("<div id=\'co_traffic_bot_tooltip\'></div>").css({
			position: "absolute",
			display: "none",
			border: "1px solid #fdd",
			padding: "2px",
			"background-color": "#fee",
			opacity: 0.80
		}).appendTo("body");

		$("#co_traffic_bot_placeholder").on("plothover", function (event, pos, item) {
			if ($("#co_traffic_bot_enablePosition:checked").length > 0) {
				var px = pos.x.toFixed(0),
					py = pos.y.toFixed(0),
					pd = new Date(px*1);
				py = py*1; if(py < 0) { py = 0; }
				$("#co_traffic_bot_hoverdata").text(pd.format("dd-mm-yyyy") + ", " + py);

				if ($("#co_traffic_bot_enableTooltip:checked").length > 0) {
					if (item) {
						var x = item.datapoint[0].toFixed(0),
							y = item.datapoint[1].toFixed(0),
							d = new Date(x*1);
						$("#co_traffic_bot_tooltip").html(d.format("dd-mm-yyyy") + " " + item.series.label + " - " + y)
							.css({top: item.pageY+5, left: item.pageX+5})
							.fadeIn(200);
					} else {
						$("#co_traffic_bot_tooltip").hide();
					}
				}
			}
		});

		/*$("#co_traffic_bot_placeholder").on("plotclick", function (event, pos, item) {
			if(item) {
				$("#co_traffic_bot_clickdata").text(" - click point " + item.dataIndex + " in " + item.series.label);
				plot.highlight(item.series, item.datapoint);
			}
		});*/';

		return '
		<div class="co_traffic_bot_content">
			<h2>'.$this->diafan->_("Спам-боты и иные").'</h2>
			<div class="co_traffic_bot_container">
				<div class="co_traffic_bot_legend"></div>
				<div id="co_traffic_bot_placeholder" class="co_traffic_bot_placeholder"></div>
			</div>
			<div class="co_traffic_bot_option">
				<div class="co_traffic_bot_choices"><table id="co_traffic_bot_choices"></table></div>
				<div class="co_traffic_bot_setting">
					<div class="co_traffic_bot_position">
						<input id="co_traffic_bot_enablePosition" type="checkbox" checked="checked"></input><label>'.$this->diafan->_("Отображать курсор").': </label>
						<span id="co_traffic_bot_hoverdata"></span> <span id="co_traffic_bot_clickdata"></span>
					</div>
					<div class="co_traffic_bot_tooltip">
						<input id="co_traffic_bot_enableTooltip" type="checkbox" checked="checked"></input><label>'.$this->diafan->_("Отображать подсказку").'</label>
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
		// формируем статистику по источникам трафика (визиты)
		$this->diafan->_visitors->counter_set_traffic_source();
		if(! $stat = $this->diafan->_visitors->counter_traffic_source())
		{
			return false;
		}
		if(empty($stat["data"]))
		{
			return false;
		}

		$this->diafan->_admin->css_view[] = 'modules/visitors/admin/css/visitors.counter.flot.css';
		$this->diafan->_admin->js_view[] = 'modules/visitors/admin/js/jquery.flot.min.js';

		$this->js_code .= '
		function traffic_source_redraw() {
			var data = [';
			$first_key = true;
			foreach ($stat["data"] as $key => $value) {
				$value["categories"] = $value["categories"] == "" ? "Прямые заходы" : $value["categories"];
				$value["categories"] = $value["categories"] == "/" ? "Внутренние переходы" : $value["categories"];
				$this->js_code .=  ($first_key ? '' : ', ');
				$this->js_code .=  '{label: "'.$this->diafan->_($value["categories"]).'", data: '.$value["value"].'}';
				$first_key = false;
			}
			$this->js_code .= '];
			$.plot("#co_traffic_source_placeholder", data, {
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
		<div class="co_traffic_source_content">
			<h2>'.$this->diafan->_("Источники трафика").'</h2>
			<div class="co_traffic_source_container">
				<div id="co_traffic_source_placeholder" class="co_traffic_source_placeholder"></div>
			</div>
		</div>';
	}

	/**
	 * Выводит статистику по посещаемым страницам (визиты)
	 *
	 * @return string
	 */
	private function get_traffic_pages()
	{
		// формируем статистику по посещаемым страницам
		$this->diafan->_visitors->counter_set_traffic_source();
		if(! $stat = $this->diafan->_visitors->counter_traffic_pages())
		{
			return false;
		}
		if(empty($stat["data"]))
		{
			return false;
		}

		$this->diafan->_admin->css_view[] = 'modules/visitors/admin/css/visitors.counter.flot.css';
		$this->diafan->_admin->js_view[] = 'modules/visitors/admin/js/jquery.flot.min.js';

		$this->js_code .= '
		function traffic_pages_redraw() {
			var data = [';
			$first_key = true;
			foreach ($stat["data"] as $value) {
				$this->js_code .=  ($first_key ? '' : ', ');
				$this->js_code .=  '{label: "'.$this->diafan->_($value["categories"]).'", data: '.$value["value"].'}';
				$first_key = false;
			}
			if(! empty($stat["other"]))
			{
				$this->js_code .=  ($first_key ? '' : ', ');
				$this->js_code .=  '{label: "'.$this->diafan->_("Остальные страницы").'", data: '.$stat["other"].'}';
			}
			$this->js_code .= '];
			$.plot("#co_traffic_pages_placeholder", data, {
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
		traffic_pages_redraw();
		$(window).resize(function() {
			traffic_pages_redraw();
		});

		';

		return '
		<div class="co_traffic_pages_content">
			<h2>'.$this->diafan->_("Пользователи смотрят").'</h2>
			<div class="co_traffic_pages_container">
				<div id="co_traffic_pages_placeholder" class="co_traffic_pages_placeholder"></div>
			</div>
		</div>';
	}

	/**
	 * Выводит статистику по посещаемым поисковыми ботами страницам (визиты)
	 *
	 * @return string
	 */
	private function get_traffic_pages_search_bot()
	{
		// формируем статистику по посещаемым страницам
		$this->diafan->_visitors->counter_set_traffic_source();
		if(! $stat = $this->diafan->_visitors->counter_traffic_pages_search_bot())
		{
			return false;
		}
		if(empty($stat["data"]))
		{
			return false;
		}

		$this->diafan->_admin->css_view[] = 'modules/visitors/admin/css/visitors.counter.flot.css';
		$this->diafan->_admin->js_view[] = 'modules/visitors/admin/js/jquery.flot.min.js';

		$this->js_code .= '
		function traffic_pages_search_bot_redraw() {
			var data = [';
			$first_key = true;
			foreach ($stat["data"] as $key => $value) {
				$this->js_code .=  ($first_key ? '' : ', ');
				$this->js_code .=  '{label: "'.$this->diafan->_($value["categories"]).'", data: '.$value["value"].'}';
				$first_key = false;
			}
			if(! empty($stat["other"]))
			{
				$this->js_code .=  ($first_key ? '' : ', ');
				$this->js_code .=  '{label: "'.$this->diafan->_("Остальные страницы").'", data: '.$stat["other"].'}';
			}
			$this->js_code .= '];
			$.plot("#co_traffic_pages_search_bot_placeholder", data, {
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
		traffic_pages_search_bot_redraw();
		$(window).resize(function() {
			traffic_pages_search_bot_redraw();
		});

		';

		return '
		<div class="co_traffic_pages_search_bot_content">
			<h2>'.$this->diafan->_("Поисковые боты смотрят").'</h2>
			<div class="co_traffic_pages_search_bot_container">
				<div id="co_traffic_pages_search_bot_placeholder" class="co_traffic_pages_search_bot_placeholder"></div>
			</div>
		</div>';
	}

	/**
	 * Выводит статистику по посещаемым ботами страницам (визиты)
	 *
	 * @return string
	 */
	private function get_traffic_pages_bot()
	{
		// формируем статистику по посещаемым страницам
		$this->diafan->_visitors->counter_set_traffic_source();
		if(! $stat = $this->diafan->_visitors->counter_traffic_pages_bot())
		{
			return false;
		}
		if(empty($stat["data"]))
		{
			return false;
		}

		$this->diafan->_admin->css_view[] = 'modules/visitors/admin/css/visitors.counter.flot.css';
		$this->diafan->_admin->js_view[] = 'modules/visitors/admin/js/jquery.flot.min.js';

		$this->js_code .= '
		function traffic_pages_bot_redraw() {
			var data = [';
			$first_key = true;
			foreach ($stat["data"] as $key => $value) {
				$this->js_code .=  ($first_key ? '' : ', ');
				$this->js_code .=  '{label: "'.$this->diafan->_($value["categories"]).'", data: '.$value["value"].'}';
				$first_key = false;
			}
			if(! empty($stat["other"]))
			{
				$this->js_code .=  ($first_key ? '' : ', ');
				$this->js_code .=  '{label: "'.$this->diafan->_("Остальные страницы").'", data: '.$stat["other"].'}';
			}
			$this->js_code .= '];
			$.plot("#co_traffic_pages_bot_placeholder", data, {
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
		traffic_pages_bot_redraw();
		$(window).resize(function() {
			traffic_pages_bot_redraw();
		});

		';

		return '
		<div class="co_traffic_pages_bot_content">
			<h2>'.$this->diafan->_("Спам-боты смотрят").'</h2>
			<div class="co_traffic_pages_bot_container">
				<div id="co_traffic_pages_bot_placeholder" class="co_traffic_pages_bot_placeholder"></div>
			</div>
		</div>';
	}

	/**
	 * Выводит статистику по поисковыми ботами (визиты)
	 *
	 * @return string
	 */
	private function get_traffic_names_search_bot()
	{
		// формируем статистику по поисковым ботам
		$this->diafan->_visitors->counter_set_traffic_names_search_bot();
		if(! $stat = $this->diafan->_visitors->counter_traffic_names_search_bot())
		{
			return false;
		}
		if(empty($stat["data"]))
		{
			return false;
		}

		$this->diafan->_admin->css_view[] = 'modules/visitors/admin/css/visitors.counter.flot.css';
		$this->diafan->_admin->js_view[] = 'modules/visitors/admin/js/jquery.flot.min.js';

		$this->js_code .= '
		function traffic_names_search_bot_redraw() {
			var data = [';
			$first_key = true;
			foreach ($stat["data"] as $key => $value) {
				$this->js_code .=  ($first_key ? '' : ', ');
				$this->js_code .=  '{label: "'.$this->diafan->_($value["categories"]).'", data: '.$value["value"].'}';
				$first_key = false;
			}
			if(! empty($stat["other"]))
			{
				$this->js_code .=  ($first_key ? '' : ', ');
				$this->js_code .=  '{label: "'.$this->diafan->_("Остальные страницы").'", data: '.$stat["other"].'}';
			}
			$this->js_code .= '];
			$.plot("#co_traffic_names_search_bot_placeholder", data, {
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
		traffic_names_search_bot_redraw();
		$(window).resize(function() {
			traffic_names_search_bot_redraw();
		});

		';

		return '
		<div class="co_traffic_names_search_bot_content">
			<h2>'.$this->diafan->_("Доли поисковых ботов").'</h2>
			<div class="co_traffic_names_search_bot_container">
				<div id="co_traffic_names_search_bot_placeholder" class="co_traffic_names_search_bot_placeholder"></div>
			</div>
		</div>';
	}
}
