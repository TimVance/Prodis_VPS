/**
 * JS-сценарий формы поиска
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).click(function(event){
	if($(event.target).closest(".js_search_result, .search_result").length) {
		return true;
	}
	$(".js_search_result, .search_result").fadeOut("slow");
});

$(".js_search_form input[type=text], #search input[type=text]").keyup(function() {
	if($(this).val()) {
		$(this).parents('.js_search_form, #search').addClass('active');
	} else {
		$(this).parents('.js_search_form, #search').removeClass('active');
	}
});
