/**
 * JS-сценарий шаблонного тега <insert show_add_coupon module="shop" template="cart">
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
 */

var shop_add_coupon_cart = function(){
	var self = {
		objects: {
			form: {
				name: '.js_shop_add_coupon_cart',
				children: {
					input: ':text',
					submit: 'button',
					error: '.js_shop_add_coupon_cart_error',
				},
			},
		},
		init: function(){
			return this;
		},
		events: function(){
			$(document).off('change', _('form.input'));
			$(document).on('change', _('form.input'), function (e){
				e.preventDefault();
				self.submit();
			});
			return this;
		},
		submit: function(){
			$.ajax({
				'type':'POST',
				'data':{
				    'module': 'shop',
				    'action': 'add_coupon',
				    'coupon' : $(_('form.input')).val()
				},
				'dataType': 'JSON',
				success: function(result){
					if (result.errors){
						$(_('form.error')).html(prepare(result.errors[0])).addClass("error_message").show();
						error_position('coupon', $(_('form.input')).parents('form'))
					}
					if (result.redirect) {
						window.location = prepare(result.redirect);
					}
				}
			});
			return false;
		},
	};
	var __ = function(name)
	{
		var res = name.split(".");
		var o = self.objects;
		var children = false;
		$.each(res, function(i, k){
			if(children)
			{
				o = children;
			}
			if(typeof o[k] == "object" && typeof o[k].children == "object")
			{
				children = o[k].children;
			}
			else
			{
				children = false;
			}
			o = o[k];
		});
		if(typeof o.name == "string")
		{
			o = o.name;
		}
		return o;
	}
	var _ = function(name)
	{
		var result = '';
		var res = name.split(".");
		var o = self.objects;
		var children = false;
		$.each(res, function(i, k){
			if(typeof o.name == "string")
			{
				if(result)
				{
					result += ' ';
				}
				result += o.name;
			}
			if(children)
			{
				o = children;
			}
			if(typeof o[k] == "object" && typeof o[k].children == "object")
			{
				children = o[k].children;
			}
			else
			{
				children = false;
			}
			o = o[k];
		});
		if(typeof o == "string")
		{
			if(result)
			{
				result += ' ';
			}
			result += o;
		}
		else
		{
			if(result)
			{
				result += ' ';
			}
			result += o.name;
		}
		return result;
	}
	return self;
}

shop_add_coupon_cart().init().events();