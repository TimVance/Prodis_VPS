/**
 * Редактирование параметров сайта, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).ready(function() { check_route_method(); });
$(document).on('change', "select[name=route_method]", check_route_method);
function check_route_method() {
	if($("select[name=route_method]").val() == 1) {
		$('#route_translit_from,#route_translit_to').show();
	} else {
		$('#route_translit_from,#route_translit_to').hide();
	}
	if($("select[name=route_method]").val() == 2) {
		$('#route_translate_yandex_key').show();
	} else {
		$('#route_translate_yandex_key').hide();
	}
}

$(document).on('diafan.ready', function() { check_data_size(); });
function check_data_size(refresh) {
	refresh = refresh || false;
	if($('#files_size').length || $('#db_size').length) {
		if($("#size_refresh").attr("disabled")) {
			return false;
		}
		$("#size_refresh").attr("disabled", "disabled");

		$('#files_size').html('...').next('img').show();
		$('#user_files_size').html('...').next('img').show();
		$('#db_size').html('...').next('img').show();

		diafan_ajax.init({
			data:{
				action: 'size',
				module: 'config',
				refresh: refresh ? 1 : 0
			},
			success: function(response) {
				if (response.result) {
					$.each(response.result, function (k, val) {
						switch (k) {
							case "files_size":
							case "user_files_size":
							case "db_size":
								var th = $('#'+k);
								if(th.length)
								{
									if(val == 0 || val == '')
									{
										val = '0 B';
									}
									th.html(prepare(val)).next('img').hide();
								}
								break;

							default:
								break;
						}
					});
				}
				$("#size_refresh").removeAttr("disabled");
			}
		});
	}
}
$("#size_refresh").click(function() { check_data_size(true); });
