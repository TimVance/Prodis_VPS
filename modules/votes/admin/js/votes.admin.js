/**
 * Редактирование опросов, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(".param_actions").on('click', "a[action=delete_param]", function() {
	if ( $(this).attr("confirm") && ! confirm( $(this).attr("confirm"))) {
		return false;
	}
	if($(".param").length == 1) {
		return false;
	}
	$(this).parents(".param").remove();
	if($(".param").length == 1) {
		$(".param").find('a[action=delete_param]').hide();
	}
	$(".param_actions a[action=up_param]").show();
	$(".param_actions a[action=down_param]").show();
	$(".param_actions a[action=up_param]").first().hide();
	$(".param_actions a[action=down_param]").last().hide();
	return false;
});

$('.param_plus').click(function() {
	var last = $('.param:last');
	last.after(last.clone(true));
	$('.param:last input').val('');
	$('.param a[action=delete_param]').show();
	$(".param_actions a[action=up_param]").show();
	$(".param_actions a[action=down_param]").show();
	$(".param_actions a[action=up_param]").first().hide();
	$(".param_actions a[action=down_param]").last().hide();
	return false;
});
$(".param_actions a[action=up_param]").first().hide();
$(".param_actions a[action=down_param]").last().hide();

$(document).on('click', ".param_actions a[action=up_param]", function() {
	var self = $(this).parents(".param");
	self.prev(".param").before(self.clone(true));
	self.remove();

	$(".param_actions a[action=up_param]").show();
	$(".param_actions a[action=down_param]").show();
	$(".param_actions a[action=up_param]").first().hide();
	$(".param_actions a[action=down_param]").last().hide();
	return false;
});
$(document).on('click', ".param_actions a[action=down_param]", function() {
	var self = $(this).parents(".param");
	self.next(".param").after(self.clone(true));
	self.remove();

	$(".param_actions a[action=up_param]").show();
	$(".param_actions a[action=down_param]").show();
	$(".param_actions a[action=up_param]").first().hide();
	$(".param_actions a[action=down_param]").last().hide();
	return false;
});
