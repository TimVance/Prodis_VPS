/**
 * Импорт/экспорт данных, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$('.shop_import_button').click(function() {
	var value = $(this).attr('rel');
	$('input[name="shop_action"]').val(value);
});
$('select[name=cat_id]').change(check_type_cat);
$('select[name=type]').change(check_type);
$('select[name=param_id]').change(check_param);
$(document).ready(function() { check_type_cat(); });

function check_type_cat() {
	var type = $('select[name=cat_id] option:selected').attr('type');
	if (! type) {
		return;
	}
	$('select[name=type] option').each(function() {
		if($(this).attr(type)) {
			$(this).show();
		} else {
			$(this).hide();
		}
	});
	if(type == 'good') {
		$("select[name=param_type] option[value=article]").show();
	} else {
		$("select[name=param_type] option[value=article]").hide();
	}
	check_type();
}
function check_type() {
	$('.params').hide();
	$('.param_'+$('select[name=type]').val()).show();
	check_param();
}
function check_param() {
	if($('select[name=type]').val() == 'param' && ($('select[name=param_id] option:selected').attr("type") == 'select' || $('select[name=param_id] option:selected').attr("type") == 'multiple')) {
		$('#param_select_type').show();
	} else {
		$('#param_select_type').hide();
	}
	if($('select[name=type]').val() == 'param' && ($('select[name=param_id] option:selected').attr("type") == 'images' || $('select[name=param_id] option:selected').attr("type") == 'attachments') || $('select[name=type]').val() == 'images') {
		$('#param_directory').show();
	} else {
		$('#param_directory').hide();
	}
}