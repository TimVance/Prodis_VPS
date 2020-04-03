/**
 * Редактирование тегов, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$('.tags_upload').click(function() {
	var button = $(this);
	$(this).parents('form').ajaxSubmit({
		type : 'POST',
		data : {action: 'upload', module : 'tags'},
		dataType: 'json',
		url: window.location.href,

		beforeSubmit: function(a,form,o) {
			$('.errors').hide();
		},

		success: function(response, statusText, xhr, form)
		{
			if (response.error) {
				$(form).find(".error_tags").html(prepare(response.error)).show();
			}
			if (response.id) {
				$(form).find("input[name=id]").val(response.id);
			}
			if (response.data && response.target) {
				$(response.target).html(prepare(response.data));
				$('textarea[name=tags]').val('');
			}
			if (response.hash) {
				$('input[name=check_hash_user]').val(response.hash);
				$('.check_hash_user').text(response.hash);
			}
			$(".tags_search").hide();
			return false;
		}
	});
	return false;
});

$(document).on('click', ".tags_delete", function() {
	var self = $(this);
	if (! confirm(self.attr("confirm"))) {
		return false;
	}
	diafan_ajax.init({
		data:{
			action: 'delete',
			module: 'tags',
			tag_id : self.attr("tag_id")
		},
		success: function(response) {
			if (response.error) {
				$(".error_tags").html(prepare(response.error)).show();
			} else {
				$(".tags_container").html(prepare(response.data));
			}
			$(".tags_search").hide();
		}
	});
	return false;
});

$('.tags_cloud').click(function() {
	var self = $(this);
	diafan_ajax.init({
		data:{
			action: 'search',
			module: 'tags',
			element_id : self.attr("element_id")
		},
		success: function(response) {
			if (response.error) {
				$(".error_tags").html(prepare(response.error)).show();
			}
			if (response.data) {
				$("#ipopup").html(prepare(response.data)).show();

				centralize($("#ipopup"));
			} else {
				$(".tags_search").hide();
			}
		}
	});
	return false;
});

$(document).on('click', ".tags_add", function() {
	$("textarea[name=tags]").val($(this).text());
	$('.ipopup__close').click();
	$(".tags_upload").click();
});
