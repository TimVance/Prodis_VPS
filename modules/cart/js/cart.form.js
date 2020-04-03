/**
 * JS-сценарий модуля «Корзина товаров, оформление заказа»
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
 */

var cart_form = function(){
	var self = {
		objects: {
			form: {
				name: '.js_cart_table_form',
				children: {
					submit: ':submit',
					checkbox: ':checkbox',
					radio: ':radio',
					number: 'input[type=number]',
					item: {
						name: '.js_cart_item',
						children: {
							remove: {
									name: '.js_cart_remove',
									attr: {
										confirm: 'data-confirm',
									},
							},
							count: {
								name: '.js_cart_count',
								children: {
									input:
									{
										name: 'input',
										attr: {
											max: 'data-max',
											min: 'data-min',
										},
									},
									plus:
									{
										name: '.js_cart_count_plus',
										class_disabled: 'disabled',
									},
									minus:
									{
										name: '.js_cart_count_minus',
										class_disabled: 'disabled',
									},
								},
							},
						},
					},
				},
			},
		},
		init: function(){
			$(_('form.submit')).hide();
			return this;
		},
		events: function(){
			$(document).on('change', _('form.item.count.input'), self.submit);
			$(document).on('change', _('form.number'), self.submit);
			$(document).on('change', _('form.radio'), self.submit);
			$(document).on('change', _('form.checkbox'), self.submit);
			$(document).on('click', _('form.item.remove'), self.remove);
			$(document).on('click', _('form.item.count.minus'), self.count_minus);
			$(document).on('click', _('form.item.count.plus'), self.count_plus);
			$(document).on('keyup', _('form.item.count.input'), self.count_change);
			return this;
		},
		count_plus: function(){
			var input = $(this).parents(__('form.item.count')).find(__('form.item.count.input'));
			self.format_count(input);
			input.val(input.val() * 1 + 1);
			var max = input.attr(__('form.item.count.input.attr.max'));
			if(max != 0 && input.val() > max)
			{
				input.val(max);
				$(this).addClass(__('form.item.count.plus.class_disabled'));
			}
			else
			{
				self.submit();
			}
			return false;
		},
		count_minus: function(){
			var input = $(this).parents(__('form.item.count')).find(__('form.item.count.input'));
			self.format_count(input);
			var min = input.attr(__('form.item.count.input.attr.min'));
			if(input.val() > 0) {
				input.val(input.val() * 1 - 1);
			}
			if(min && input.val() < min)
			{
				input.val(min);
				$(this).addClass(__('form.item.count.minus.class_disabled'));
			}
			else
			{
				self.submit();
			}
		},
		count_change: function(){
			self.format_count($(this));
		},
		remove: function(){
			if ($(this).attr(__('form.item.remove.attr.confirm')) && ! confirm($(this).attr(__('form.item.remove.attr.confirm')))) {
				return false;
			}
		},
		submit: function(){
			$(_('form')).submit();
		},
		format_count: function(input){
			input.val().replace(/,/g, ".");
			return;
		}
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

cart_form().init().events();