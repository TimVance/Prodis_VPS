/**
 * Редактирование тегов, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', "#tags_mulitupload", function() {
	$(".tags_mulitupload").toggle();
	return false;
});

$('.tags_mulitupload form').submit(function () {
	$('.tags_mulitupload .error').hide();
	$(this).ajaxSubmit({
		url: window.location.href,
		type: 'POST',
		dataType:'json',
		success:(function (response, statusText, xhr, form) {
			if (response.hash) {
				$('input[name=check_hash_user]').val(response.hash);
				$('.check_hash_user').text(response.hash);
			}
			if(response.error) {
				$('.tags_mulitupload .error').show().html(prepare(response.error));
			}
			if (response.redirect) {
				window.location = response.redirect;
			}
		})
	});
	return false;
});
