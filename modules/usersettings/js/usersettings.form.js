/**
 * JS-сценарий модуля «Настройки аккаунта»
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', '.js_usersettings_avatar_delete, .usersettings_avatar_delete', function() {
	$.ajax({
		data: {
			action : "delete_avatar",
			module : "usersettings"
		},
		type : 'POST'
	});
	$(this).parents('.js_usersettings_avatar, .usersettings_avatar').html('');
	return false;
});
$(document).on('change', "select[name=role_id]", function(){
	show_param_rele_rel(this);
});
show_param_rele_rel("select[name=role_id]");

function show_param_rele_rel(th)
{
	var role_id = $(th).val();
	$('.js_param_role_rels, .param_role_rels').hide();
	$('.js_param_role_'+role_id+', .param_role_'+role_id).show();
}

diafan_ajax.success['usersettings_edit'] = function(form, response){
	if(response.data) {
		$("input[name=avatar]", form).val('');
	}
}
