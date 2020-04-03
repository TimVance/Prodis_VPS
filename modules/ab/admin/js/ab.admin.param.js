/**
 * Редактирование характеристик объявлений, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).ready(function() { show_param_ab($("#type select")); });
$("#type select").change(function(){
	show_param_ab($(this));
});
$(document).ready(function() { param_select_site_id(); });
$('select[name=site_id]').change(param_select_site_id);

$('#attachments_access_admin').remove();

function show_param_ab(obj) {
	if (obj.val() == "select" || obj.val() == "multiple") {
		$("#page").show();
	} else {
		$("#page").hide();
	}

	if(obj.val() == 'textarea' || obj.val() == 'text' || obj.val() == 'email' || obj.val() == 'attachments' || obj.val() == 'editor' || obj.val() == 'images') {
		$('#search').hide();
	} else {
		$('#search').show();
	}

	if(obj.val() == 'textarea' || obj.val() == 'select' || obj.val() == 'multiple' || obj.val() == 'editor' || obj.val() == 'title') {
		$('#display_in_sort').hide();
	} else {
		$('#display_in_sort').show();
	}

	if(obj.val() == 'numtext') {
		$('#measure_unit').show();
	} else {
		$('#measure_unit').hide();
	}
}

function param_select_site_id() {
	if(! $('select[name=site_id]').length) {
		return;
	}
	var val = $('select[name=site_id]').val();
	if(val && val != 0) {
		$("select[name='cat_ids[]'] optgroup").hide();
		$("select[name='cat_ids[]'] optgroup[data-site_id="+val+"]").show();
	} else {
		$("select[name='cat_ids[]'] optgroup").show();
	}
}
