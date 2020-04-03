/**
 * Подключение модуля к настройкам других модулей, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
var resize_count = 0;

$('.images_variations').each(function() {
	show_delete_images_variation($(this));
	if($(this).find(".images_variation").length == $(this).find(".images_variation").first().find('select option').length + 1) {
		$(this).find('.images_variation_plus').hide();
	}
});
$('.images_variation_plus').click(function() {
	var contaner = $(this).parents('.images_variations');
	var last = contaner.find(".images_variation").last();
	if(contaner.find(".images_variation").length >= last.find('select option').length + 1) {
		return false;
	}
	last.before(last.clone(true));
	last = last.prev('.images_variation');
	last.show().find("input").val('');
	last.find('input').attr('name', str_replace('hide_', '', last.find('input').attr('name'), 1));
	show_delete_images_variation(contaner);
	if(contaner.find(".images_variation").length == last.find('select option').length + 1) {
		contaner.find('.images_variation_plus').hide();
	}
});
$(document).on('click', '.images_variation_delete', function() {
	var contaner = $(this).parents('.images_variations');
	if(contaner.find('.images_variation').length == 1) {
		return false;
	}
	if(! confirm($(this).attr('confirm'))) {
		return false;
	}
	$(this).parents('.images_variation').remove();
	show_delete_images_variation(contaner);
	contaner.find('.images_variation_plus').show();
});

$(document).on('click', "#resize :button", function() {
	var self = $(this);
	if (!resize_count && self.attr("confirm") && ! confirm(self.attr("confirm"))) {
		return false;
	}
	var data = $('#save').serializeArray();
	$('.images_loading_resize').show();
	data.push({name: 'action', value: 'resize'});
	data.push({name: 'module', value: 'images'});
	data.push({name: 'resize_count', value: resize_count});
	diafan_ajax.init({
		data:data,
		success: function(response) {
			if (response.error)
			{
				if(response.error.indexOf('next')+1)
				{
					resize_count += 30;
					$("#resize :button").click();
				}
				else
				{
					alert(response.error);
					$('.images_loading_resize').hide();
					resize_count = 0;
				}
			}
		}
	});
	return false;
});

$('input[name=cat]').attr("rel", $('input[name=cat]').attr("rel") + ',#images_cat');

function show_delete_images_variation(contaner) {
	if(contaner.find('.images_variation').length == 1) {
		contaner.find('.images_variation_delete').hide();
	} else {
		contaner.find('.images_variation_delete').show();
	}
}
