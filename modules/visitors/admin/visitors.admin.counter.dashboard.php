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
class Visitors_admin_counter_dashboard extends Diafan
{
	/**
	 * @var string название таблицы
	 */
	public $name = 'Пользователи смотрят';

	/**
	 * @var integer порядковый номер для сортировки
	 */
	public $sort = 2;

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
			$html_code .= $this->get_traffic_pages();
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
		return $text;
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
			<!--<h2>'.$this->diafan->_("Пользователи смотрят").'</h2>-->
			<div class="co_traffic_pages_container">
				<div id="co_traffic_pages_placeholder" class="co_traffic_pages_placeholder"></div>
			</div>
		</div>';
	}
}
