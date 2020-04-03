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
