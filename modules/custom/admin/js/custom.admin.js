/**
 * Темы, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

var fast_edit_current = {
	old_value: false,
	element: false,
	init: function(){
		$(document).on('change', this.element, function () {
 			fast_edit_current.change($(this));
		});
	},
	change: function(e){
		if (e.attr("confirm") && ! confirm(e.attr("confirm")))
		{
			e.prop('checked', ! e.is(':checked'));
			return false;
		}
		var question = 0;
		if (e.attr('question')) {
			if (confirm(e.attr('question'))) {
				question = 1;
			}
			e.removeAttr('question');
		}
		diafan_ajax.init({
			data:{
				action:'fast_save',
				name:e.attr('name'),
				value:(e.is(':checked') ? '1' : '0'),
				type:e.attr('type'),
				id:e.attr('row_id'),
				question: question,
			},
			success: function(response){
				if (response.res == false) {
					e.val(fast_edit_current.old_value);
				}
				location.reload();
			}
		});
	}
}

$(document).on('diafan.ready', function() {
	fast_edit_current.element = ".fast_edit_current input";
	fast_edit_current.init();
});
