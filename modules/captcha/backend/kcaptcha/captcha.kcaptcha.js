/**
 * JS-сценарий стандартной капчи
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', '.js_captcha_update, .captcha_update', function () {
	$(this).parents("form").find("input[name=captcha_update]").val("1");
	$(this).parents("form").submit();
});