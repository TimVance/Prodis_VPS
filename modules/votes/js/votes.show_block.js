/**
 * JS-сценарий модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', '.js_votes_form :button, .votes_form :button', function() {
	$(this).parents('form').find("input[name=result]").val(1);
	$(this).parents('form').submit();
});

$(document).on('click', '.js_votes_form :radio, .votes_form :radio', function() {
	if($(this).attr('value') == 'userversion') {
		$('.js_votes_userversion, .votes_userversion').show();
	} else {
		$('.js_votes_userversion, .votes_userversion').hide();
	}
});
