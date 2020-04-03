/**
 * Подключение модуля к административной части других модулей, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('change', ".inpfiles", function () {
	if ($(this).attr("max") && $(this).parents('.attachments').find('input[name="' + $(this).attr("name") + '"]').length >= $(this).attr("max") - $(this).parents('.attachments').find('.attachment').length) {
		return false;
	}
	$(this).after($(this).clone(true).val(''));
	$(this).after($('<br>'));
	check_max_count($(this).parents('.attachments'));
});
$(".attachment_delete").click(function() {
	if(! $(this).parents('.attachment').length) {
		return false;
	}
	if (! confirm($(this).attr("confirm"))) {
		return false;
	}
	$(this).parents('.attachment').find("input[name='hide_attachment_delete[]']").attr("name", "attachment_delete[]");
	$(this).parents('.attachment').removeClass('attachment').hide();
	check_max_count($(this).parents('.attachments'));
	return false;
});
$(document).on('mouseover', ".attachment", function(){
	$(this).find('.attachment_delete').show();
});
$(document).on('mouseout', ".attachment", function(){
	$(this).find('.attachment_delete').hide();
});
$('.attachments').each(function() {
	check_max_count($(this));
});
$("#recognize_image input, #attachments_access_admin input").click(function() {
	show_param_attachments();
});
$(document).ready(function() {
	show_param_attachments();
});

function show_param_attachments() {
	if ($('#recognize_image input').is(':checked')) {
		$("#attachments_access_admin").hide();
		$("#attachments_access_admin input").prop('checked', false);
	} else if(! $("#type select").val() || $("#type select").val() == "attachments") {
		$("#attachments_access_admin").show();
	}
	if ($('#attachments_access_admin input').is(':checked')) {
		$("#show_in_list, #show_in_page").hide();
		$("#show_in_list input, #show_in_page input").prop('checked', false);
	} else {
		$("#show_in_list, #show_in_page").show();
	}
}

function check_max_count(th) {
	if(th.find('.attachment_files :file').first().attr('max') <= th.find('.attachment').length) {
		th.find('.attachment_files').hide();
	} else {
		th.find('.attachment_files').show();
	}
}
