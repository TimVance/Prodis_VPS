/**
 * JS-сценарий модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', ".js_photo_link_ajax, .previous_link a, .next_link a", function() {
	var url = $(this).attr("href");
	$.ajax({
		url : url,
		data : {module_ajax : 'photo'},
		type: 'POST',
		dataType : 'json',
		success : (function(response)
		{
			if (response.text) {
				$(".js_photo_id, .photo_id").html(prepare(response.text));
			}
			if (response.h1) {
				$("h1").html(prepare(response.h1));
			}
		})
	});
	return false;
});
