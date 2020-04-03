/**
 * Конструктор формы оформления заказа, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).ready(function() { show_param_shop_order($("#type select")); });
$("#type select").change(function(){
	show_param_shop_order($(this));
});

function show_param_shop_order(obj) {
	if (obj.val() == "attachments") {
		$("#show_in_form_register_number").hide();
	} else {
		$("#show_in_form_register_number").show();
	}
}
