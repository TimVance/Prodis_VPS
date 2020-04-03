/**
 * Обновление, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

var update = {
	first_click: false,
	rows: false,
	init: function() {
		$("#update").click(function () {
			update.click();
		});
		$(document).on('click', "#update_list .btn_dwnl", function () {
			update.download();
		});
	},
	click : function(){
		if(update.first_click) {
			return false;
		}

		$('#update').append('<div class="loading"></div>');

		update.first_click = true;

		diafan_ajax.init({
			data:{
				action: 'update',
				module: 'update'
			},
			success: function(response) {
				if(!response.redirect && response.rows){
					$('#update').after(prepare(response.data));
					update.rows = response.rows;
					$('.progress-bar').each(function() {
						var $this = $(this);
						var width = $this.outerWidth()/$this.find('> *').length;

						$this.find('> *').css('width', (width*100)/$this.outerWidth()+'%');
					});
				}
				if(response.messages){
					$('.ok').remove();
					$('#update').after(prepare(response.messages));
					update.first_click = false;
				}
				$('#update').remove();
			}
		});
	},
	download: function() {
		row = update.rows.shift();
		diafan_ajax.init({
			data:{
				action: 'download',
				module: 'update',
				id : row.id,
				text: row.text,
				hash: row.hash,
				version: row.version || '',
			},
			success: function(response) {
				$('#update_download .empty').first().removeClass('empty').addClass('active');
				$('.progress-procent').text(Math.round($('.progress-item.active').length / $('.progress-item').length * 100)+'%');
				if(response.errors) {
					$.each(response.errors, function (k, val) {
						alert(val);
					});
				} else if($('#update_download .empty').length) {
					update.download();
				}
				else if(response.redirect_url) {
					window.location = response.redirect_url;
				}
			}
		});
	}
}

$(document).on('diafan.ready', function() {
	update.init();
});
