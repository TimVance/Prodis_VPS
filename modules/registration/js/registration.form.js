/**
 * JS-сценарий модуля «Регистрация»
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('change', "select[name=role_id]", function() {
	show_param_rele_rel();
});
$(document).ready(function() {
	show_param_rele_rel();
});

$(":text, :password, input[type=email], input[type=tel]", ".js_registration_form, .registration_form").blur(function() {
	var name = $(this).attr('name');
	var value2 = $(":password", ".js_registration_form, .registration_form").val();
	$.ajax({
		url:window.location.href,
		type:'POST',
		dataType:'json',
		data:{
			action:'fast_validate',
			name: name,
			module: 'registration',
			value: $(this).val(),
			value2: value2
		},
		success:(function (response) {
			if (response.data) {
				var input = $("input[name="+name+"], textarea[name="+name+"]", '.js_registration_form, .registration_form');
				input.addClass('error_input').addClass('focus_input');
				var off = input.offset();
				$(".error_" + name, '.js_registration_form, .registration_form').html(prepare(response.data)).show();
				off.top += input.outerHeight();
				off.left += 5;
				$(".error_" + name, '.js_registration_form, .registration_form').addClass('error_message').offset(off);
			} else {
				$(".error_"+name, '.js_registration_form, .registration_form').text('').hide();
			}
		})
	});
});

function show_param_rele_rel() {
	$('.js_param_role_rels, .param_role_rels').hide();
	var role_id = $("select[name=role_id]").val();
	$('.js_param_role_'+role_id+', .param_role_'+role_id).show();
}
