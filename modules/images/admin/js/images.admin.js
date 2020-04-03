/**
 * Редактирование вариантов генерирования изображений, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$('.images_action').each(function() {
	show_images_action($(this));
});
$(".images_action select[name='actions[]']").change(function() {
	show_images_action($(this).parents(".images_action"));
});
$('.images_action_plus').click(function() {
	var last = $(".images_action").last();
	var i = last.find("input[name='i[]']").val();
	last.after(last.clone(true));

	last = $(".images_action").last();

	last.find("select[name='actions[]']").val('resize');
	last.find("input[name='i[]']").val(i*1 + 1);
	last.find(".images_action_container").attr("id", "images_action_"+(i*1 + 1));
	last.find("input[name=watermark_file_"+i+"]").attr("name", "watermark_file_"+(i*1 + 1));
	last.find(':text,:file').val('');
	last.find('select').each(function() {
		$(this).val($(this).find('option').first().attr("value"));
	});
	last.find('.resize_max').attr('id', 'resize_max_c_'+i );
	last.find('.resize_max_label').attr('for', 'resize_max_c_'+i );
	show_images_action(last);
});
$('.images_action_delete').click(function() {
	if($('.images_action').length == 1) {
		return false;
	}
	if(! confirm($(this).attr('confirm'))) {
		return false;
	}
	$(this).parents('.images_action').remove();
	if($('.images_action').length == 1) {
		$('.images_action_delete').hide();
	}
});
$(document).on('change', '.resize_max', function() {
	var val = 0;
	if($(this).is(':checked')) {
		val = 1;
	}
	$(this).siblings('input[name="resize_max[]"]').val(val);
});

function show_images_action(self) {
	self.find('.images_param').hide();
	self.find('.images_param_'+self.find("select[name='actions[]']").val()).show();
	if($('.images_action').length == 1) {
		$('.images_action_delete').hide();
	} else {
		$('.images_action_delete').show();
	}
}
