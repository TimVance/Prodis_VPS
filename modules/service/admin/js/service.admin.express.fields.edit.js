/**
 * Описание импорт/экспорт данных, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

// === Настройки описания экспорта/импорта - start === //

$(document).on('change', 'select[name=module_name]', function () {
	/*if(! $(this).attr('name'))
	{
		return false;
	}*/
	var self = $(this), element = this;
	diafan_ajax.init({
		data:{
			action: self.attr('name') + '_change',
			module: 'service',
			module_name: element.value,
			id: self.parents('form').find('input[name=id]').val() || 0,
			site_id_infofield: $('#site_id').find('.infofield').eq(0).html(),
			cat_id_infofield: $('#cat_id').find('.infofield').eq(0).html(),
			type_infofield: $('#type').find('.infofield').eq(0).html()
		},
		success: function(response) {
			if (response.site_id) {
				$('#site_id').replaceWith(prepare(response.site_id));
			} else $('#site_id').html('');

			if (response.cat_id) {
				$('#cat_id').replaceWith(prepare(response.cat_id));
			} else $('#cat_id').html('');

			if (response.type) {
				$('#type').replaceWith(prepare(response.type));
			} else $('#type').html('');
		}
	});
	return false;
});

// === Настройки описания экспорта/импорта - end === //

// === Поле описания экспорта/импорта - start === //

$(document).ready(function() {
	$('select[name=cat_id]').change(check_type_cat);
	$('select[name=type]').change(check_type);
	$('select[name=param_id]').change(check_param);
	check_type_cat();
});

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
	if(type == 'element') {
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

// === Поле описания экспорта/импорта - end === //
