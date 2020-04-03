/**
 * Дополнения, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$('.item__labels a').click(function() {
	if(! $(this).attr('action')) {
		return false;
	}
	var self = $(this);
	if (self.attr("confirm") && ! confirm(self.attr("confirm"))) {
		return false;
	}
	diafan_ajax.init({
		data:{
			action: self.attr('action'),
			module: 'addons',
			id: self.parents('li').attr('row_id')
		},
		success: function(response) {
			if (response.action) {
				self.attr('action', response.action);
			}
			if (self.is('.disable')) {
				self.removeClass('disable');
			} else {
				self.addClass('disable');
			}
		}
	});
	return false;
});

$('#check_update, #delete_return').click(function() {
	if($(this).hasClass("disable")) {
		return false;
	}
	if(! $(this).attr('action')) {
		return false;
	}
	var self = $(this);
	diafan_ajax.init({
		data:{
			action: self.attr('action'),
			module: 'addons',
		},
		success: function(response) {
			if (response.action) {
				self.attr('action', response.action);
			}
			if (self.is('.disable')) {
				self.removeClass('disable');
			} else {
				self.addClass('disable');
			}
			if (response.errors && response.errors.message) {
				var cnt = 0;
				$.each(response.errors, function (k, val) {
					cnt++;
				});
				if (cnt < 2) {
					var message = prepare(response.errors.message);
					alert(message);
					response.result = 'success';
				}
			}
		}
	});
	return false;
});
