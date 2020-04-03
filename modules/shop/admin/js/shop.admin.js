/**
 * Редактирование товаров, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$('.item__price__toggle').click(function() {
	var $this = $(this);

	$this.find('i,span').toggle();

	$this.parent().find('.item__price__popup').stop().slideToggle('fast', function() {
		$(this).css('overflow', 'visible');
	});
});

$(".param_actions").click(function() {
	var self = $(this);
	if (! self.attr("action")) {
		return true;
	}
	if (self.attr("confirm") && ! confirm(self.attr("confirm"))) {
		return false;
	}
	if (! self.parents(".param").find(".param_id").length) {
		if (self.attr("action") == "delete_param") {
			self.parents(".param").remove();
		}
		return false;
	}
	return false;
});

$('.param_plus').click(function() {
	var div = $(this).parents('.unit');
	$('.param:last', div).clone(true).appendTo($('.param_table', div));
	$('.param:last .param_id', div).remove();

	$('.param:last .param_image_rel_actions img', div).remove();
	$('.param:last .param_image_rel_actions .images_button', div).hide();
	$('.param:last .param_image_rel_actions .add_price_image_rel', div).removeClass('hide');
	$('.param:last .param_image_rel_actions input[name="price_image_rel[]"]', div).val("");
});

$(".delete_file").click(function() {
	$('input[name=delete_attachment]').val('1');
	$(this).parents('.attachment').remove();
	return false;
});

$('input[name=depend_price]').change(function() {
	if($(this).is(':checked')) {
		$("select[name='hide_param_value"+$(this).attr('rel')+"[]']").attr('name', 'param_value'+$(this).attr('rel')+'[]');
		$("select[name='param"+$(this).attr('rel')+"[]']").hide();
		$(".param_value_unit"+$(this).attr('rel')).show();
		$(".param_value_unit"+$(this).attr('rel')).addClass('shop_param_unit');
	} else {
		$("select[name='param_value"+$(this).attr('rel')+"[]']").attr('name', 'hide_param_value'+$(this).attr('rel')+'[]');
		$("select[name='param"+$(this).attr('rel')+"[]']").show();
		$(".param_value_unit"+$(this).attr('rel')).hide();
		$(".param_value_unit"+$(this).attr('rel')).removeClass('shop_param_unit');
	}
});

$('input[name=depend_price]').each(function() {
	if($(this).is(':checked')) {
		$("select[name='hide_param_value"+$(this).attr('rel')+"[]']").attr('name', 'param_value'+$(this).attr('rel')+'[]');
		$("select[name='param"+$(this).attr('rel')+"[]']").hide();
		$(".param_value_unit"+$(this).attr('rel')).show();
		$(".param_value_unit"+$(this).attr('rel')).addClass('shop_param_unit');
	} else {
		$("select[name='param_value"+$(this).attr('rel')+"[]']").attr('name', 'hide_param_value'+$(this).attr('rel')+'[]');
		$("select[name='param"+$(this).attr('rel')+"[]']").show();
		$(".param_value_unit"+$(this).attr('rel')).hide();
		$(".param_value_unit"+$(this).attr('rel')).removeClass('shop_param_unit');
	}
});

$(document).on('click', ".add_price_image_rel", function() {
	$(".add_price_image_rel").attr('current', 'false');
	$(this).attr('current', 'true');
	$('#shop_price_image_rel').remove();
	var shop_price_image_rel = '<div id="shop_price_image_rel" class="ipopup hide"><div class="fa fa-close ipopup__close"></div><div class="shop_price_image_rel">';
	$(".images_container0 .images_actions").each(function(){
		shop_price_image_rel += '<img src="'+$(this).find('.image img').attr('src')+'" image_id="'+$(this).attr('image_id')+'"> ';
	});
	shop_price_image_rel += '</div></div>';
	$(".images_container0").after(shop_price_image_rel);
	$('#shop_price_image_rel').hide();
	centralize($('#shop_price_image_rel'));
	return false;
});
$(document).on('click', ".shop_price_image_rel img", function() {
	var current = $(".add_price_image_rel[current=true]");
	current.after('<img src="'+$(this).attr('src')+'">');
	var param_image_rel_actions = current.parents('.param_image_rel_actions');
	param_image_rel_actions.find('input[name="price_image_rel[]"]').val($(this).attr('image_id'));
	param_image_rel_actions.find('.images_button').show();
	param_image_rel_actions.find('.add_price_image_rel').addClass('hide');
	$('.ipopup__close').click();
});
$(document).on('click', ".delete_price_image_rel", function() {
	$(this).parents('.param_image_rel_actions').find('img').remove();
	$(this).parents('.param_image_rel_actions').find('.images_button').hide();
	$(this).parents('.param_image_rel_actions').find('.add_price_image_rel').removeClass('hide');
	$(this).parents(".param_image_rel_actions").find('input[name="price_image_rel[]"]').val("");
});
$('.item__labels a').click(function() {
	if(! $(this).attr('action')) {
		return false;
	}
	var self = $(this);
	diafan_ajax.init({
		data:{
			action: self.attr('action'),
			module: 'shop',
			id: self.parents('li').attr('row_id')
		},
		success: function(response) {
			if (response.action) {
				self.attr('action', response.action);
			}
			if (self.is('.disable')) {
				self.removeClass('disable');
			} else {
				self.addClass('disable');
			}
		}
	});
	return false;
});

$('input[name=file_type]').change(function () {
	$('.file_type1').hide();
	$('.file_type2').hide();
	$('.file_type3').hide();
	$('.file_type' + $(this).val()).show();
});
