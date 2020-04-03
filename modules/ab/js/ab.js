/**
 * JS-сценарий модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('mouseover', ".js_ab, .ab", function() {
	$(this).find(".ab_actions a").show();
});

$(document).on('mouseout', ".js_ab, .ab", function() {
	$(this).find(".js_ab_action, .ab_actions a").hide();
});

$(document).on('click', '.js_ab_action, .ab_actions a', function() {
	if ($(this).attr("confirm")) {
		if (! confirm($(this).attr("confirm"))) {
			return false;
		}
	}
});
