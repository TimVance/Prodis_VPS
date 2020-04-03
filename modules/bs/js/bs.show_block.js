/**
 * JS-сценарий обработки клика по баннеру
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
var banner_current = false;
var win;
$(document).on('click', '.js_bs_counter, .bs_counter', function() {
	banner_current = $(this);
	$("input[name='banner_id']", '.js_bs_form, .bs_form').val(banner_current.attr('rel'));
	$('.js_bs_form, .bs_form').submit();
	if(! banner_current.attr('target') == '_blank')
	{
		return false;
	}
});

diafan_ajax.success['bs_click'] = function(form, response) {
	if(banner_current.attr('target') != '_blank') {
		window.location = banner_current.attr('href');
	}
	return false;
}
