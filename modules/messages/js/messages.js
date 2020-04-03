/**
 * JS-сценарий модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', ".js_messages input[type=submit].js_paginator_more_button", function() {
	var th = $(this).parents('form');
	if (! th.length) return false;
	var uid = th.children("input[name=uid]").val(),
		parent = th.closest('tr');
	if(parent.length) {
		parent.attr("uid", uid);
	}
	return true;
});
