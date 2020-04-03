/**
 * Количество доступных обновлений, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('diafan.ready', function() {
	var update_count = {
		first_load: false,
		init: function(){
			update_count.load();
		},
		load : function() {
			if(update_count.first_load) {
				return false;
			}

			update_count.first_load = true;

			diafan_ajax.init({
				data:{
					action: 'count',
					module: 'update'
				},
				success: function(response) {
					if(! response.redirect && response.rows) {
						var item = $('.nav.desctop .nav__item .fa-update').eq(0).parent();
						if(item.length) {
							var info = item.find(".nav__info").eq(0);
							if(info.length) {
								info.remove();
							}
							item.append(prepare(response.data));
						}
					}
					if(response.messages) {
						var item = $('.wrap .col-right').eq(0);
						if(item.length) {
							item.prepend(prepare(response.messages));
						}
					}
					else update_count.first_load = false;
				}
			});
		}
	}

	update_count.init();
});
