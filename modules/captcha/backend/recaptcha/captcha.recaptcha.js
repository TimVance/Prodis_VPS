/**
 * JS-сценарий для reCAPTCHA
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
 */

var recaptcha = {};
var onloadCallback = function() {
	$('.js_captcha').each(function(){
		var c = $(this).find('div').first();
		recaptcha[c.attr("id")] = grecaptcha.render(c.attr("id"), {
		  'sitekey' : c.attr("data-sitekey")
		});
	});
};
