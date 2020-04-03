/**
 * Редактирование темы, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */


validate.success = function(response) {
	var question = 0;
	if (response.errors && (response.errors.confirm || response.errors.question)) {
		var cnt = 0;
		$.each(response.errors, function (k, val) {
			cnt++;
		});
		if (cnt < 3) {
			if (response.errors.confirm && confirm(prepare(response.errors.confirm))) {
				if (response.errors.question && confirm(prepare(response.errors.question))) {
					question = 1;
				}
				response.result = 'success';
				$('input[name=name]').after('<input name="question" type="hidden" value="'+question+'">');
			}
		}
	}
}

$(".unit").on('click', '.action[action]', function () {
	var self = $(this);
	if(self.attr('disabled')) {
		return false;
	}
	if (! self.attr("action"))
	{
		return true;
	}
	if (self.attr("confirm") && ! confirm(self.attr("confirm")))
	{
		return false;
	}
	var form = $(this).parents('form'),
			question = 0;
	if (self.attr("question"))
	{
		if(confirm(self.attr("question")))
		{
			question = 1;
		}
	}

	self.attr('disabled', 'disabled');
	var data = {
		id: form.find('input[name=id]').val(),
		action: self.attr("action") || form.find('input[name=action]').val(),
		module: self.attr("module") || form.find('input[name=module]').val(),
		question: question
	};
	diafan_ajax.init({
		data: data,
		success: function(response) {
			if (self.attr('disabled')) {
				self.removeAttr('disabled');
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
