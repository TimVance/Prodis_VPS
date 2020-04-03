/**
 * JS-сценарий модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if ($('.js_shop_preview_img').length)
{
	$('.js_shop_preview_img').click(function(){
		var shop = $(this).parents('.js_shop_id');
		shop.find('.js_shop_all_img .js_shop_img').hide();
		shop.find('.js_shop_all_img .js_shop_img[image_id='+$(this).attr('image_id')+']').show();
		if ($('.js_shop_param_price[image_id='+$(this).attr('image_id')+']')) {
			var curr = $('.js_shop_param_price[image_id='+$(this).attr('image_id')+']');
			if (curr.length) {
				$('.js_shop_param_price').hide();
				curr.show();
				$(".js_shop_id .js_shop_depend_param").each(function(){
					$(this).val(curr.attr($(this).attr('name')));
				});
				var th = curr.parents('form');
				if(curr.find('.js_shop_no_buy, .shop_no_buy').length) {
					$('.js_show_waitlist, .shop_waitlist', th).show();
					$('.js_shop_buy, .to-cart', th).hide();
					$('.js_shop_one_click, .shop_one_click', th).hide();
				} else {
					if($('.js_shop_no_buy_good, .shop_no_buy_good', th).length) {
						$('.js_shop_waitlist, .shop_waitlist', th).show();
					} else {
						$('.js_shop_waitlist, .shop_waitlist', th).hide();
					}
					$('.js_shop_buy, .to-cart', th).show();
					$('.js_shop_one_click, .shop_one_click', th).show();
				}
			}
		}
		return false;
	});
	var price_image = false;
	if($('.js_shop_id .js_shop_param_price').length) {
		var param_code = '';
		$(".js_shop_id .js_shop_depend_param").each(function(){
			param_code = param_code + '['+$(this).attr('name')+'='+$(this).val()+']';
		});
		if($('.js_shop_id .js_shop_param_price'+param_code).length && $('.js_shop_id .js_shop_param_price'+param_code).attr('image_id')) {
			price_image = true;
		}
	}
	if(! price_image) {
		$('.js_shop_all_img .js_shop_img').first().show();
	}
} else {
	if ($(".shop-item-previews .item").length) {
		$(".shop-item-previews .item").each(function(index, element) {
			$(this).on('click',function(e) {
				e.preventDefault();
				$(".shop-item-big-images .shop-item-image").removeClass("active");
				$(".shop-item-big-images .shop-item-image").eq(index).addClass("active");
				$(".shop-item-big-images .shop-item-image").eq(index).show();
			});
		});

		$('.shop-item-previews .item').click(function() {
			if ($('.shop_param_price[image_id='+$(this).attr('image_id')+']')) {
				var curr = $('.shop_param_price[image_id='+$(this).attr('image_id')+']');
				if (curr.length) {
					$('.shop_param_price').hide();
					curr.show();
					$(".js_shop_id .js_shop_depend_param, .shop_id .depend_param").each(function(){
						$(this).val(curr.attr($(this).attr('name')));
					});
				}
			}
		});
	}
	else if ($('.js_shop_all_img .js_shop_img').length) {
		$('.js_shop_all_img .js_shop_img').first().show();
	}
}
