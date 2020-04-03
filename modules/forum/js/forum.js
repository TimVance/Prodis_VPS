/**
 * JS-сценарий модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', '.js_delete_attachment, .delete_attachment', function() {
	var del_id = $(this).attr("del_id");
	if (! confirm($(this).attr("title")))
		return false;

	$.ajax({
		type : 'POST',
		dataType : 'json',
		data : { module : 'forum', action: 'delete_attachment', del_id: del_id , check_hash_user : $('input[name=check_hash_user]').val()},
		success : (function(response)
		{
			$(response.target).hide();
			if (response.hash)
			{
				$('input[name=check_hash_user]').val(response.hash);
			}
		})
	});
	return false;
});

$(document).on('click', '.js_forum_message_show_form, .forum_message_show_form', function() {
	$(this).next('.js_forum_message_block_form, .forum_message_block_form').toggle();
});

$(document).on('mouseover', ".js_forum_message, .js_forum_theme, .forum_message, .forum_theme", function() {
	$(this).find(".js_forum_actions, .forum_actions span").show();
});

$(document).on('mouseout', ".js_forum_message, .js_forum_theme, .forum_message, .forum_theme", function() {
	$(this).find(".js_forum_actions, .forum_actions span").hide();
});

$(document).on('click', '.js_forum_action, .forum_actions a', function(){
	if (! $(this).attr("action")) {
		return true;
	}
	if ($(this).attr("title")) {
		if (! confirm($(this).attr("title")))
		{
			return false;
		}
	}
	$(this).parents('form').find('input[name=action]').val($(this).attr("action"));
	$(this).parents('form').submit();
});

$(document).on('click', ".js_forum_list input[type=submit].js_paginator_more_button", function() {
	var th = $(this).parents('form');
	if (! th.length) return false;
	var uid = th.children("input[name=uid]").val(),
		parent = th.closest('tr');
	if(parent.length) {
		parent.attr("uid", uid);
	}
	return true;
});
