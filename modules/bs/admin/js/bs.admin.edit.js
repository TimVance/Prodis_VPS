/**
 * Редактирование баннеров, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$('input[name=type]').change(function () {
	$('.type1').hide();
	$('.type2').hide();
	$('.type3').hide();
	$('.type' + $(this).val()).show();
});

$(document).on('click', '.menu_check', function() {
	diafan_ajax.init({
		data:{
			action: "list_site_id",
			module: 'menu',
			parent_id: 0
		},
		success: function(response) {
			if (response.error) {
				$(".error_menu").html(prepare(response.error)).show();
			}
			if (response.data) {
				$("#ipopup").html(prepare(response.data));
				centralize($("#ipopup"));
			}
		}
	});
	return false;
});
$(document).on('click', '.menu_plus', function() {
	var self = $(this);
	if (self.parents("p").attr("module_name") == 'site') {
		var parent_id = self.parents("p").attr("site_id");
		var action = "list_site_id";
		var site_id = 0;
		var module_name = '';
	} else {
		var parent_id = self.parents("p").attr("cat_id");
		var action = "list_module";
		var site_id = self.parents("p").attr("site_id");
		var module_name = self.parents("p").attr("module_name");
	}
	diafan_ajax.init({
		data:{
			action: action,
			module: 'menu',
			parent_id: parent_id,
			module_name: module_name,
			site_id: site_id
		},
		success: function(response) {
			if (response.data) {
				self.parents("p").after(prepare(response.data));
				self.removeClass("menu_plus").addClass("menu_minus");
				self.text("—");
				$(".pp_content").height($(".pp_content .menu_list_first").height() + 50);
			}
		}
	});
	return false;
});
$(document).on('click', '.menu_minus', function() {
	$(this).parents("p").next(".menu_list").remove();
	$(this).addClass("menu_plus").removeClass("menu_minus");
	$(this).text("+");
	$(".pp_content").height($(".pp_content .menu_list_first").height() + 50);
	return false;
});
$(document).on('click', '.menu_select', function() {
	$("input[name=link]").val($(this).attr("href"));
	$('.ipopup__close').click();
	return false;
});
$(document).on('click', '.menu_select_module', function() {
	var self = $(this);
	diafan_ajax.init({
		data:{
			action: "list_module",
			module: 'menu',
			site_id: self.parents("p").attr("site_id"),
			parent_id: 0,
			module_name: self.attr("module_name")
		},
		success: function(response) {
			if (response.data) {
				$(".menu_list_first").html(prepare(response.data));
				$(".pp_content").height($(".pp_content .menu_list_first").height() + 50);
			}
		}
	});
	return false;
});
