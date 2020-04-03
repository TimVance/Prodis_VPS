$(document).on('submit', 'form.ajax', function () { return diafan_ajax.init(this); });

function error_position(k, form) {
	var input = $("input[name=" + k + "], textarea[name=" + k + "], select[name=" + k + "]",form),
		error = $(".error_" + k,form);

	if (error.css("position") !== "absolute")
		return;

	var off = input.offset();
	off.top += input.outerHeight();
	off.left += 5;
	error.offset(off);
}

/* diafan_ajax  - start */
var diafan_ajax = {
	before: {},
	success: {},
	tag: false,
	options: {
		manager: true,
	},
	init: function(form) {
		this.tag = $("input[name=module]", form).val() + "_" + $("input[name=action]", form).val();

		if(this.before[this.tag] && this.before[this.tag](form) === false)
		{
			return false;
		}
		$('input:submit', form).attr('disabled', 'disabled');
		$('.errors').hide();
		$('.error_input').removeClass('error_input');
		if (! $('input[name=ajax]', form).length)
		{
				$(form).append('<input type="hidden" name="ajax" value="1">');
		}
		if(! this.options.manager)
		{
			$(form).trigger('order_ajax_submit.before', [ form, 1 ]);
			this.onSubmitBefore(form, 1);

			$(form).ajaxSubmit({
				success: function (result, statusText, xhr, form) {
					diafan_ajax.result(form, result);

					$(document).trigger('order_ajax_submit.after', [ form, 0 ]);
					diafan_ajax.manager.onSubmitAfter(form, 0);

					$(document).trigger('order_ajax_submit.after_last', [ form ]);
					diafan_ajax.manager.onSubmitAfterLast(form);
				}
			});
		}
		else this.manager.addForm(form);
		$(":text, :password, input[type=email], input[type=tel], textarea, select, :radio, :checkbox", form).change(function(){
			if($('body').attr('name'))
			{
				$(".error_" + $('body').attr('name').replace(/\[|\]/gi, ""), form).hide();
			}
			$(this).removeClass('error_input');
		});
		return false;
	},
	result: function (form, result) {
		$('input:submit', form).removeAttr('disabled');
		try {
			var response = $.parseJSON(result);
		} catch(err){
			$('body').append(result);
			$('.diafan_div_error').css('left', $(window).width()/2 - $('.diafan_div_error').width()/2);
			$('.diafan_div_error').css('top', $(window).height()/2 - $('.diafan_div_error').height()/2 + $(document).scrollTop());
			$('.diafan_div_error_overlay').css('height', $('body').height());
			return false;
		}
		if(response.profiler) {
			$(".devoloper_profiling[ajax]").remove();
			$(".devoloper_profiler[ajax]").remove();
			$('body').append(prepare(response.profiler));
			delete response.profiler;
		}
		if(this.success[this.tag] && this.success[this.tag](form, response) === false)
		{
			return false;
		}
		var captcha_update = $(form).find("input[name='captcha_update']").val();
		if (response.captcha) {
			if(response.captcha == "recaptcha")
			{
				var c = $('.js_captcha', form).find('div').first();
				grecaptcha.reset(recaptcha[c.attr("id")]);
			}
			else
			{
				$(".captcha", form).html(prepare(response.captcha)).show();
			}
		}
		if (! captcha_update && response.errors) {
			$.each(response.errors, function (k, val) {
				if(k == 0)
				{
					if(! $(".error", form).length)
					{
						$(form).parent().find(".error").addClass("error_message").html(prepare(val)).show();
					}
					else
					{
						$(".error", form).addClass("error_message").html(prepare(val)).show();
					}
				}
				else
				{
					var input = $("input[name="+k+"], textarea[name="+k+"], select[name="+k+"]", form);
					$(".error_" + k, form).addClass("error_message").html(prepare(val)).show();
					if (input.length)
					{
						input.addClass('error_input').addClass('focus_input');
						error_position(k, form);
					}
				}
			});
			$('.focus_input:first', form).focus();
			$('.focus_input').removeClass('focus_input');
		}
		if (response.result && response.result == 'success') {
			$(form).clearForm();
			$(form).find('.inpattachment input:file').each(function () {
				if($(this).parents('.inpattachment').is(":hidden"))
				{
					var clone = $(this).parents('.inpattachment');
					clone.before(clone.clone(true));
					clone.prev('.inpattachment').show();
					var name = str_replace('hide_', '', clone.prev('.inpattachment').find('input').val('').attr("name"), 0 );
					clone.prev('.inpattachment').find('input').val('').attr("name", name);
				}
				else
				{
					$(this).parents('.inpattachment').remove();
				}
			});
			$('input:file', form).removeClass('last');
			$('input:file', form).val('');
			if($('.inpimages', form).length){
				$('.images').text('');
			}
		}
		if (response.add) {
			$('.' + $(form).attr("id")).append(prepare(response.add)).show();
			$(".error:empty").hide();
		}
		if (response.redirect) {
			window.location = prepare(response.redirect);
		}
		if(response.curLoc) {
			var curLoc = prepare(response.curLoc);
			diafan_ajax.set_location(curLoc);
		}
		if (response.set_location) {
			diafan_ajax.set_location($(form).attr("action"));
			/*var top = $(window).scrollTop(),
				destination = $(form).offset().top,
				header = $("header.diafan-admin-panel, header.useradmin_panel").eq(0),
				diff = 0;
			if (header.length)
			{
				diff = header.outerHeight(true);
				destination = destination - diff;
			}
			if (top < destination) $('html, body').animate({scrollTop: destination}, 850);*/
		}
		if (response.data)
		{
			$.each(response.data, function (k, val) {
				if(k == "form")
				{
					k = form;
				}
				if(val)
				{
					if(response.replace)
					{
						$(k).replaceWith(prepare(val)).show();
					}
					else
					{
						//$(k).html(prepare(val)).show();
						// alternative method:
						$(k).contents().remove();
						$(k).append(prepare(val)).show();
					}
				}
				else
				{
					if(response.replace)
					{
						$(k).replaceWith('').show();
					}
					else
					{
						$(k).hide();
					}
				}
			});
			$(".error:empty").hide();
		}
		if(response.attachments){
			 $.each(response.attachments, function (k, val) {
				$(form).find(".attachment[name='"+k+"']").remove();
				$(form).find(".inpattachment input[name='"+k+"']").parents('.inpattachment').remove();
				$(form).find(".inpattachment input[name='hide_"+k+"']").parents('.inpattachment').before(prepare(val));
				$(form).find(".attachment[name='"+k+"']").show();
				if($(form).find(".inpattachment input[name='hide_"+k+"']").attr("max") > $(form).find(".attachment[name='"+k+"']").length)
				{
					var clone = $(form).find("input[name='hide_" + k + "']").parents('.inpattachment');
					clone.before(clone.clone(true));
					clone.prev('.inpattachment').show();
					clone.prev('.inpattachment').find('input').val('').attr("name", k);
				}
			});
		}
		if(response.images){
			 $.each(response.images, function (k, val) {
				$(form).find("input[name='"+k+"']").val('');
				$(form).find("input[name='"+k+"']").parents('div').first().find('.image').remove();
				if(val == false)
				{
					val = '';
				}
				$(form).find("input[name='"+k+"']").before(prepare(val));
			});
		}
		if (response.hash) {
			$('input[name=check_hash_user]').val(response.hash);
		}
		if (response.js) {
			$.each(response.js, function (k, val) {
				if(val)
				{
					if (val['src']) val['src'] = prepare(val['src']);
					if (val['func']) val['func'] = prepare(val['func']);
					diafan_ajax['manager'].addScript(val['src'], val['func']);
				}
			});
		}
		if (response.css) {
			$.each(response.css, function (k, val) {
				if(val)
				{
					diafan_ajax['manager'].addCSS(prepare(val));
				}
			});
		}

		var ajax_errors = $('.diafan_errors[ajax_errors]'),
			diafan_errors = $('.diafan_errors:not([ajax_errors])');
		if(ajax_errors.length)
		{
			var c = $('a[diafan_errors]').length + 1;
			$('a[ajax_errors]').each(function(){
				if($(this).closest('.diafan_errors').length) return true;
				$(this).removeAttr('ajax_errors').attr('diafan_errors', '').attr('href', '#error' + c).text('[ERROR' + c +']');

				$(this).next('.diafan_errors').find('a[ajax_errors]').eq(0).removeAttr('ajax_errors').attr('name', 'error' + c);

				c = c + 1;
			});
			if(diafan_errors.length)
			{
				diafan_errors.find('table tr').after(ajax_errors.find('table tr'));
				ajax_errors.remove();
				$('.diafan_div_error').css('left', $(window).width()/2 - $('.diafan_div_error').width()/2);
				$('.diafan_div_error').css('top', $(window).height()/2 - $('.diafan_div_error').height()/2 + $(document).scrollTop());
				$('.diafan_div_error_overlay').css('height', $('body').height());
			}
			else
			{
				ajax_errors.appendTo('body');
				$('.diafan_div_error').css('left', $(window).width()/2 - $('.diafan_div_error').width()/2);
				$('.diafan_div_error').css('top', $(window).height()/2 - $('.diafan_div_error').height()/2 + $(document).scrollTop());
				$('.diafan_div_error_overlay').css('height', $('body').height());
				ajax_errors.removeAttr('ajax_errors');

				if(window["base_path"] === undefined )
				{
					window["base_path"] = '';
				}
				diafan_ajax['manager'].addScript(window["base_path"] + 'adm/js/admin.errors.js');
			}
		}

		return false;
	},
	manager: {
		order: [],
		count: function(){
			var count = 0;
			this.order.forEach(function(item, index, list) {
				count++;
			});
			return count;
		},
		curForm: {},
		flag_busy: false,
		addForm: function(form){
			this.order.push(form);
			this.run();
			return this;
		},
		onSubmitBefore: function(form, order_count){},
		onSubmitAfter: function(form, order_count){},
		onSubmitAfterLast: function(form){},
		onSubmitError: function(form, errorThrown){},
		run: function(){
			var count = this.count();
			if (! this.flag_busy && count > 0)
			{
				this.flag_busy = ! this.flag_busy;
				this.curForm = this.order.shift();

				$(this.curForm).trigger('order_ajax_submit.before', [ this.curForm, count ]);
				this.onSubmitBefore(this.curForm, count);

				$(this.curForm).ajaxSubmit({
					async: true,
					// timeout: 3000,
					beforeSubmit: function(arr, form, options) {
						// return false to cancel submit
						return true;
					},
					success: function (result, statusText, xhr, form) {
						diafan_ajax.result(form, result);

						var current_count = diafan_ajax.manager.count();
						$(document).trigger('order_ajax_submit.after', [ form, current_count ]);
						diafan_ajax.manager.onSubmitAfter(form, current_count);
						if (current_count <= 0)
						{
							$(document).trigger('order_ajax_submit.after_last', [ form ]);
							diafan_ajax.manager.onSubmitAfterLast(form);
						}
					},
					error: function(xhr, statusText, errorThrown){
						$(document).trigger('order_ajax_submit.error', [ diafan_ajax.manager.curForm, errorThrown ]);
						// TO_DO: errorThrown = (statusText === 'timeout' ? 'timeout' : 'aborted');
						diafan_ajax.manager.onSubmitError(diafan_ajax.manager.curForm, errorThrown);
					},
					complete: function(xhr, statusText) {
						diafan_ajax.manager.curForm = {};
						diafan_ajax.manager.flag_busy = false;
						if (diafan_ajax.manager.count() > 0)
						{
							diafan_ajax.manager.run();
						}
					},
				});
			}
			return this;
		},
		js_view: [],
		inArray: function(array, value){
			return $.inArray(array, value);
		},
		addScript: function(src, init_func){
			src = src || '';
			init_func = init_func || '';
			if(src && ! $('script[src="'+src+'"]').length && this.inArray(this.js_view, src) === -1)
			{
				this.js_view.push(src);
				$._cachedScript(src).done(function(script, textStatus){});
			}
			else if(init_func && typeof window[init_func] == 'function')
			{
				window[init_func]();
			}
			return this;
		},
		addCSS: function(src){
			src = src || '';
			if(src && ! $('link[href="'+src+'"][type="text/css"]').length)
			{
				$('head').append('<link rel="stylesheet" href="'+src+'" type="text/css" rel="stylesheet" charset="utf-8">');
			}
			return this;
		},
		isEmpty: function(obj) {
			for (var key in obj) {
				return false;
			}
			return true;
		},
	},
	set_location: function(curLoc){
		curLoc = curLoc.replace(window.location.protocol + "//" + window.location.host + "/", '');
		try {
			history.pushState(null, null, "/" + curLoc);
			return true;
		} catch(e) {}
		curLoc = curLoc.replace(/[\#|\?|\.](.*?)$/gi, '');
		var parts = curLoc.split(/\s*\/\s*/),
			part = '';
		while (parts.length)
		{
			part = parts.pop().replace(/[^\w]*/,'');
			if(! part) continue;
			window.chHashFlag = true;
			location.hash = '#' + part;
			break;
		}
		return false;
	},
}
// alternative method: переопределяем метод diafan_ajax.manager.inArray
if ([].indexOf) { diafan_ajax.manager.inArray = function(array, value) { return array.indexOf(value); } }
else { diafan_ajax.manager.inArray = function(array, value) { for (var i = 0; i < array.length; i++) { if (array[i] === value) return i; } return -1; } }
// alternative method: загрузка скриптов методом $.ajax - более гибкий, чем $.getScript
jQuery._cachedScript = function(url, options) {
	// TO_DO: позволяем задать настройки dataType, cache и url
	options = $.extend(options || {}, {
		dataType: "script",
		cache: false,
		url: url,
	});
	// TO_DO: возвращаем объект jqXHR для использования методов обратного действия
	return jQuery.ajax(options);
};
// alternative method: определяем была ли установленна реакция на событие
(function( $ ) {
	$.fn.hasEvent = function(eventType, func) {
		if (! this.length || ! eventType) return false;
		eventType = eventType.split(/\s*\.\s*/);
		namespace = eventType[1] || false;
		eventType = eventType[0];
		func = func || false;
		var result = false,
			events = $._data(this.get(0), 'events');
		if (events) $.each(events, function (event, value) {
			if (event != eventType) return true;
			if (namespace || func)
			{
				if (value) $.each(value, function (k, val) {
					if (! val) return true;
					if (namespace && namespace != val.namespace) return true;
					if (func && val.handler != func) return true;
					result = true;
					return false;
				});
			}
			else result = true;
			return false;
		});
		return result;
	};
})(jQuery);
/* diafan_ajax - end */

/* defer_loading - start */
var defer_loading = {
	options: {
		container: window,
		emergence: {
			async: true,   // true - асинхронная загрузка, false - синхронная загрузка
			debounce: 250, // задержка вызова функции (значение указано в мс)
		},
		timeout: 3000,     // время ожидания ответа от сервера (задается в в миллисекундах)
	},
	_core: {
		flag_init: false,
		defer_id: 0,
		objects: function(){
			return $('form input[name="defer"]').filter(':not([loading=true])');
		},
		prepare_async: function(form){
			if(! form.length) return form;
			this.defer_id++;
			form.attr("defer_id", this.defer_id).append('<input type="hidden" name="ajax_async" value="1">').find('input').filter('[name^="attributes"]').each(function(){
				var name = $(this).attr("name").replace(/^attributes/gi, "attributes[" + defer_loading._core.defer_id + "]");
				$(this).attr("name", name);
			});
			return form;
		},
		before_submit: function(event, form, count){
			if($(form).length && $(form).attr('loading') == 'true' && $(form).find('input[name="defer"]').eq(0).length)
			{
				var defer_ids = {};
				$(form).find('[name ^= attributes]').each(function(){
					var attributes = $(this).attr('name').split(/\[([^\]]*)\]/g);
					if(attributes.length != 5) return true;
					var defer_id = attributes[1].replace(/\D+/g,"");
					if(! defer_id || defer_ids[defer_id]) return true;
					defer_ids[defer_id] = true;

					var fm = $('form[defer_id="'+defer_id+'"]');
					if(! fm.length) return true;
					fm.delay(defer_loading.options.timeout).hide(0);
				});
				$(form).delay(defer_loading.options.timeout).hide(0);
			}
		},
		after_submit: function(event, form){
			var objects = defer_loading._core.objects();
			if(! objects.length)
			{
				defer_loading.destroy();
			}
			else
			{
				if(objects.filter('[value="async"]').length || objects.filter('[value="sync"]').length)
				{
					defer_loading.load();
				}
				else if(! objects.filter('[value="emergence"]').length)
				{
					defer_loading.destroy();
				}
			}
		},
		viewport: function(container){
			var offset = $(container).offset() || {top: 0, left: 0};
			container = container || defer_loading.options.container;
			return {
				x: $(container).scrollLeft(),
				y: $(container).scrollTop(),
				left: offset.left,
				top: offset.top,
				width: $(container).outerWidth(true),
				height: $(container).outerHeight(true),
			};
		},
		in_viewport: function(owner, subject){
			var comparison = function(a1, a2, b1, b2){
				a1 = a1 || 0; a2 = a2 || 0; b1 = b1 || 0; b2 = b2 || 0;
				var buf = 0;
				if (a1 > a2) { buf = a1; a1 = a2; a2 = buf; }
				if (b1 > b2) { buf = b1; b1 = b2; b2 = buf; }
				if (a1 > b2) return -2;				// слева от диапазона
				if (a2 < b1) return 2;				// справа от диапазона
				if (a1 > b1 && a2 >= b2) return -1;	// пересекает слева от диапазона
				if (a1 <= b1 && a2 < b2) return 1;	// пересекает справа от диапазона
				if (a1 >= b1 && a2 <= b2) return 0;	// внутри или равен диапазону
				return 0;							// больше диапазона
			},
			vertical = comparison(owner.y, (owner.y + owner.height), subject.top, (subject.top + subject.height));
			horizontal = comparison(owner.x, (owner.x + owner.width), subject.left, (subject.left + subject.width));

			return (vertical > -2 && vertical < 2 && horizontal > -2 && horizontal < 2);
		},
	},
	init: function(){
		if (this._core.flag_init || ! this._core.objects().length) return false;
		this._core.flag_init = true;

		if (! $(document).hasEvent('order_ajax_submit.before', this._core.before_submit))
		{
			$(document).on('order_ajax_submit.before', this._core.before_submit);
		}
		if (! $(document).hasEvent('order_ajax_submit.after_last', this._core.after_submit))
		{
			$(document).on('order_ajax_submit.after_last', this._core.after_submit);
		}
		if (! $(this.options.container).hasEvent('scroll', this.emergence))
		{
			$(this.options.container).on('scroll', {async: this.options.emergence.async}, this.emergence.debounce(this.options.emergence.debounce));
		}
		if (! $(this.options.container).hasEvent('resize', this.emergence))
		{
			$(this.options.container).on('resize', {async: this.options.emergence.async}, this.emergence.debounce(this.options.emergence.debounce));
		}

		if (! $(document).hasEvent("submit", defer_loading.event))
		{
			$(document).on('submit', 'form.js_block_defer_form', this.event);
		}

		this.load();
		this.load_in_viewport(true);

		return true;
	},
	destroy: function(){
		if (! this._core.flag_init) return false;
		$(this.options.container).off('scroll', this.emergence);
		$(this.options.container).off('resize', this.emergence);
		$(document).off('order_ajax_submit.after_last', this._core.after_submit);
		$(document).off('order_ajax_submit.before', this._core.before_submit);
		this._core.flag_init = false;
		return true;
	},
	load: function(){
		var form = $();
		$('form input[name="defer"][value="async"]').each(function(){
			defer_loading._core.defer_id++;
			if(! form.length)
			{
				form = defer_loading._core.prepare_async($(this).attr("loading", "true").closest('form')).attr("loading", "true");
			}
			else
			{
				defer_loading._core.prepare_async($(this).attr("loading", "true").closest('form')).attr("loading", "true").find('input').filter('[name^="attributes"]').appendTo(form);
			}
		});
		if(form.length) form.submit();
		$('form input[name="defer"][value="sync"]').each(function(){
			$(this).attr("loading", "true").closest('form').attr("loading", "true").submit();
		});
	},
	load_in_viewport: function(async){
		async = async || false;
		var viewport = defer_loading._core.viewport(),
			vp = {};
		if(async)
		{
			var form = $();
			$('form input[name="defer"][value="emergence"]').filter(':not([loading=true])').each(function(){
				var fm = $(this).closest('form');
				vp = defer_loading._core.viewport(fm);
				if(! defer_loading._core.in_viewport(viewport, vp)) return true;
				$(this).attr("loading", "true");
				defer_loading._core.prepare_async(fm).attr("loading", "true");
				if(! form.length)
				{
					form = fm;
				}
				else
				{
					fm.find('input').filter('[name^="attributes"]').appendTo(form);
				}
			});
			if(form.length) form.submit();
		}
		else
		{
			$('form input[name="defer"][value="emergence"]').filter(':not([loading=true])').each(function(){
				var fm = $(this).closest('form');
				vp = defer_loading._core.viewport(fm);
				if(! defer_loading._core.in_viewport(viewport, vp)) return true;
				$(this).attr("loading", "true");
				fm.attr("loading", "true");
				fm.submit();
			});
		}
		return this;
	},
	emergence: function(event){
		var async = false;
		if(event !== undefined && event.data !== undefined && "async" in event.data)
		{
			async = event.data.async;
		}
		defer_loading.load_in_viewport(async);
	},
	event: function(e){
		$(this).attr("loading", "true").find('input[name="defer"][value="event"]').attr("loading", "true");
		return false;
	},
}

/**
 * Вызывает функцию указанное количество миллисекунд в контексте obj с аргументами args.
 *
 * @param {Number} millis
 * @param {Object} obj
 * @param {Array} args
 * @return {Number} Идентификатор таймаута.
 */
Function.prototype.defer = function(timeout, obj, args) {
	var that = this;
	return setTimeout(function() {
		that.apply(obj, args || []);
	}, timeout);
};
// TO_DO: defer возвращает идентификатор создаваемого таймера, который в случае необходимости можно отменить.
// Вызов в нужном контексте с параметрами:
// any_function.defer(100, this, [1, 2]);

/**
 * Возвращает функцию, вызывающую исходную с задержкой delay в контексте obj.
 * Если во время задержки функция была вызвана еще раз, то предыдующий вызов
 * отменяется, а таймер обновляется. Таким образом из нескольких вызовов,
 * совершающихся чаще, чем delay, реально будет вызван только последний.
 *
 * @param {Number} delay
 * @param {Object} obj
 * @return {Function}
 */
Function.prototype.debounce = function(delay, obj) {
	var fn = this, timer;
	return function() {
		var args = arguments, that = this;
		clearTimeout(timer);
		timer = setTimeout(function() {
			fn.apply(obj || that, args);
		}, delay);
	};
};
// TO_DO: пример привязки функции
// document.getElementById('textbox').onkeypress = any_function.debounce(500);

$(window).on('load', function(e){
	$(document).on('order_ajax_submit.after_last', function(event, form){
		// TO_DO: js/main.js
		var init_func = 'init_main';
		if(init_func && typeof window[init_func] == 'function')
		{
			window[init_func]();
		}

		// TO_DO: инициализация отложенной загрузки шаблонных тегов,
		// если такие теги появились впервые по результатам AJAX
		defer_loading.init();
	});

	// TO_DO: первичная инициализация отложенной загрузки шаблонных тегов
	defer_loading.init();
});
/* defer_loading - end */

/* diafan_cookie - start */
var diafan_cookie = {
	get: function(name) {
		if(! this.isEnable()) {
			return false;
		}

		var matches = document.cookie.match(new RegExp(
		"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, "\\$1") + "=([^;]*)"
		));
		return matches ? decodeURIComponent(matches[1]) : undefined;
	},
	set: function(name, value, options) {
		if(! this.isEnable()) {
			return false;
		}

		options = options || {};
		var expires = options.expires;

		if(typeof expires == "number" && expires) {
			var d = new Date();
			d.setTime(d.getTime() + expires * 1000);
			expires = options.expires = d;
		}
		if(expires && expires.toUTCString) {
			options.expires = expires.toUTCString();
		}

		value = encodeURIComponent(value);
		var updatedCookie = name + "=" + value;

		for(var propName in options) {
			updatedCookie += "; " + propName;
			var propValue = options[propName];
			if (propValue !== true) {
				updatedCookie += "=" + propValue;
			}
		}

		document.cookie = updatedCookie;
	},
	delete: function(name) {
		if(! this.isEnable()) {
			return false;
		}

		setCookie(name, "", {
			expires: -1
		});
	},
	isEnable: function() {
		if(navigator.cookieEnabled)
		{
			return true;
		}
		return false;
	}
}
/* diafan_cookie - end */

$(document).on('click', '.error_message', function(){
	$(this).hide();
});

$(document).on('change', ".inpfiles", function () {
	var inpattachment = $(this).parents('.inpattachment');
	if (! $(this).attr("max") || $(this).parents('form').find('input[name="' + $(this).attr("name") + '"], .attachment[name="' + $(this).attr("name") + '"]').length < $(this).attr("max")) {
		var clone = $(this).parents('form').find('input[name="hide_' + $(this).attr("name") + '"]').parents('.inpattachment');
		clone.before(clone.clone(true));
		clone.prev('.inpattachment').show().find('input').val('').attr("name", $(this).attr("name"));
	}
	if(! inpattachment.find(".inpattachment_delete").length)
	{
		inpattachment.append(' <a href="javascript:void(0)" class="inpattachment_delete">x</a>');
	}
});

$(document).on('click', ".inpattachment_delete", function () {
	var inpattachment = $(this).parents('.inpattachment');
	var input = inpattachment.find('.inpfiles');
	var last_input = input.parents('form').find('input[name="' + input.attr("name") + '"]').last();
	if (last_input.val()) {
		var clone = $(this).parents('form').find('input[name="hide_' + input.attr("name") + '"]').parents('.inpattachment');
		clone.before(clone.clone(true));
		clone.prev('.inpattachment').show().find('input').val('').attr("name", input.attr("name"));
	}
	inpattachment.remove();
	return false;
});

$(document).on('click', ".attachment_delete", function(){
	var attachment = $(this).parents('.attachment');
	attachment.find("input[name='hide_attachment_delete[]']").attr("name", "attachment_delete[]");
	attachment.hide().removeClass('attachment');

	var last_input = attachment.parents('form').find('input[name="' + attachment.attr("name") + '"]').last();
	if(! last_input.length || last_input.val())
	{
		var clone = $(this).parents('form').find("input[name='hide_" + attachment.attr("name") + "']").parents('.inpattachment');
		clone.before(clone.clone(true));
		clone.prev('.inpattachment').show();
		clone.prev('.inpattachment').find('input').val('').attr("name", attachment.attr("name"));
	}
	return false;
});

$(document).on('change', ".inpimages", function () {
	var form = $(this).parents('form');
	var self = $(this);
	form.ajaxSubmit({
		dataType:'json',
		data : {
			ajax: 1,
			images_param_id:self.attr('param_id'),
			images_prefix: self.attr('prefix'),
			action: 'upload_image'
		},

		beforeSubmit:function (a, form, o) {
			$('.errors').hide();
		},

		success:function (response) {
			if (response.hash)
			{
				$('input[name=check_hash_user]').val(response.hash);
			}
			if (response.data)
			{
				self.prev('.images').html(prepare(response.data));
				self.val('');
			}
			if (response.errors)
			{
				$.each(response.errors, function (k, val) {
					form.find(".error" + (k != 0 ? "_" + k : '')).html(prepare(val)).show();
				});
			}
		}
	});
});

$(document).on('click', ".image_delete", function(){
	var image= $(this).parents('.image');
	var form = $(this).parents('form');
	$.ajax({
		url : window.location.href,
		type : 'POST',
		dataType : 'json',
		data : {
			module:  form.find('input[name=module]').val(),
			action: 'delete_image',
			ajax: true,
			element_id: form.find('input[name=id]').val(),
			tmpcode: form.find('input[name=tmpcode]').val(),
			id: image.find('img').attr('image_id'),
			check_hash_user : $('input[name=check_hash_user]').val()
		},
		success : (function(response)
		{
			if (response.hash)
			{
				$('input[name=check_hash_user]').val(response.hash);
			}
		})
	});
	image.remove();
	return false;
});

$(".timecalendar").each(function () {
	var st = $(this).attr('showtime');

	if (st && st.match(/true/i)) {
		$(this).datetimepicker({
			dateFormat:'dd.mm.yy',
			timeFormat:'hh:mm'
		}).mask('99.99.9999 99:99');
	}
	else {
		$(this).datepicker({
			dateFormat:'dd.mm.yy'
		}).mask('99.99.9999');
	}
});

$(document).on('keydown', 'input[type=number], input.number', function (evt) {
	evt = (evt) ? evt : ((window.event) ? event : null);

	if (evt) {
		var elem = (evt.target)
			? evt.target
			: (
			evt.srcElement
				? evt.srcElement
				: null
			);

		if (elem) {
			var charCode = evt.charCode
				? evt.charCode
				: (evt.which
				? evt.which
				: evt.keyCode
				);

			if ((charCode < 32 ) ||
				(charCode > 44 && charCode < 47) ||
				(charCode > 95 && charCode < 106) ||
				(charCode > 47 && charCode < 58) || charCode == 188 || charCode == 191 || charCode == 190 || charCode == 110) {
				return true;
			}
			else {
				return false;
			}
		}
	}
});

$('input[type=tel]').mask('+9999 999 9999');

$('.js_mask').each(function(){
	$(this).mask($(this).attr('mask'));
});

$(".error:empty").hide();

$(document).on('click', 'a[rel=large_image]', function () {
	var self = $(this);
	window.open(self.attr("href"), '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' + (self.attr("width") * 1 + 40) + ',height=' + (self.attr("height") * 1 + 40));
	return false;
});

function prepare(string) {
	string = str_replace('&lt;', '<', string);
	string = str_replace('&gt;', '>', string);
	string = str_replace('&amp;', '&', string);
	return string;
}

function str_replace(search, replace, subject, count) {
	f = [].concat(search),
		r = [].concat(replace),
		s = subject,
		ra = r instanceof Array, sa = s instanceof Array;
	s = [].concat(s);
	if (count) {
		this.window[count] = 0;
	}
	for (i = 0, sl = s.length; i < sl; i++) {
		if (s[i] === '') {
			continue;
		}
		for (j = 0, fl = f.length; j < fl; j++) {
			temp = s[i] + '';
			repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
			s[i] = (temp).split(f[j]).join(repl);
			if (count && s[i] !== temp) {
				this.window[count] += (temp.length - s[i].length) / f[j].length;
			}
		}
	}
	return sa ? s : s[0];
}

function get_selected (element, parent)
{
	var option = (typeof element == 'string') ? $(element + " option:first-child", parent) : $("option:first-child", element);

	$(element, parent).find("option").each(function() {
		if($(this).attr("selected") === "selected") {
			option = $(this);
			return false;
		}
	});
	return option;
}
