/**
 * JS-сценарий формы подписки на рассылки модуля «Рассылки»
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

diafan_ajax.success['subscription_add'] = function(form, response) {
	if (response.errors && response.errors.captcha && ! response.result) {
		$(".captcha", form).addClass("active");
	} else {
		$(".captcha", form).removeClass("active");
	}
	return true;
};
