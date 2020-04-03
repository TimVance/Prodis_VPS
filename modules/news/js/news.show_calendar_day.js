/**
 * JS-сценарий модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', ".js_news_calendar_prev, .js_news_calendar_next, .news_calendar_prev, .news_calendar_next", function() {
	if($(this).is(".js_news_calendar_prev, .news_calendar_prev")) {
		$(this).parents('form').find('input[name=arrow]').val('prev');
	} else {
		$(this).parents('form').find('input[name=arrow]').val('next');
	}
	$(this).parents('form').ajaxSubmit({
		dataType: 'html',
		success: function(response, statusText, xhr, form)
		{
			form.parents('.js_news_block, .news_block').replaceWith(response);
			return false;
		}
	});
	return false;
});
