/**
 * Настройки модуля, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', '#hash_refresh_button', function(event) {
	var self = $(this);
	if($(this).attr('disabled')) {
		return false;
	}
	$(this).attr('disabled', 'disabled');
	diafan_action.init({
		self: self,
		config: {
			data: {
				action: "delete_hash",
				module: "images"
			}
		},
		success: function(response) {
			diafan_action.init({
				self: self,
				config: {
					data: {
						action: "create_hash",
						module: "images"
					}
				},
				success: function(response) {
					$('#hash_refresh_button').removeAttr('disabled');
				},
				error: function(response) {
					$('#hash_refresh_button').removeAttr('disabled');
				}
			});
		},
		error: function(response) {
			$('#hash_refresh_button').removeAttr('disabled');
		}
	});
});
