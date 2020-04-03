/*DIAFAN.CMS*/
$('input[type=number], input.number, input.js_number').keydown(function (evt) {
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
			(charCode > 36 && charCode < 41) ||
			(charCode > 44 && charCode < 47) ||
			(charCode > 95 && charCode < 106) ||
			(charCode > 47 && charCode < 58) || charCode == 188 || charCode == 191 || charCode == 190 || charCode == 110 || charCode == 86 || charCode == 67) {
			return true;
		}
		else {
			return false;
		}
		}
	}
});

$('input[type=tel]').mask('+9999 999 9999');

var diafan_ajax = {
	data: false,
	url: false,
	cache: true,
	before: false,
	error: false,
	success: false,
	complete: false,
	done: false,
	fail: false,
	timeout: false,
	timeOut: (30 * 1000),
  timerId: 0,
	init: function(config){
		if (config.data) {
			this.data = config.data;
		} else {
			this.data = {};
		}
		if (config.url) {
			this.url = config.url;
		} else {
			this.url = window.location.href;
		}
		if (! ("cache" in config)) {
			this.cache = this.cache;
		} else {
			this.cache = !! config.cache;
		}
		if (config.before) {
			this.before = config.before;
		} else {
			this.before = (function(jqXHR, settings){});
		}
		if (config.error) {
			this.error = config.error;
		} else {
			this.error = (function(jqXHR, textStatus, errorThrown){});
		}
		if (config.success) {
			this.success = config.success;
		} else {
			this.success = (function(response){});
		}
		if (config.complete) {
			this.complete = config.complete;
		} else {
			this.complete = (function(jqXHR, textStatus){});
		}
		if (config.fail) {
			this.fail = config.fail;
		} else {
			this.fail = (function(){});
		}
		if (config.done) {
			this.done = config.done;
		} else {
			this.done = (function(){});
		}
		if (config.timeout) {
			this.timeout = config.timeout;
		} else {
			this.timeout = (function(data, timeout){});
		}
		this.data.check_hash_user = $('.check_hash_user').text();

		if (! Array.isArray) { // Полифилл
			Array.isArray = function(arg) {
				return Object.prototype.toString.call(arg) === '[object Array]';
			};
		}
		if (Array.isArray(this.data)) { // jQuery serializeArray() Method
			this.data = {"_data_": $().serializeJSON(this.data)};
		} else if (typeof this.data === 'object') { // Object
			this.data = {"_data_": JSON.stringify(this.data)};
		}

		return $.ajax({
			url: diafan_ajax.url,
			type: 'POST',
			cache: diafan_ajax.cache,
			data: diafan_ajax.data,
			beforeSend:(function (jqXHR, settings) {
				clearTimeout(diafan_ajax.timerId);
				diafan_ajax.timeOut = (((window.MAX_EXECUTION_TIME || 30) * 1000) + (10 * 1000));
				diafan_ajax.timerId = setTimeout(function() {
					$(document).trigger('diafan_ajax.timeout', [ diafan_ajax.data, diafan_ajax.timeOut ]);
					diafan_ajax.timeout(diafan_ajax.data, diafan_ajax.timeOut);
				}, diafan_ajax.timeOut);
				diafan_ajax.before(jqXHR, settings);
			}),
			error:(function (jqXHR, textStatus, errorThrown) {
				clearTimeout(diafan_ajax.timerId);
				diafan_ajax.before(jqXHR, textStatus, errorThrown);
			}),
			success:(function (data, textStatus, jqXHR) {
				clearTimeout(diafan_ajax.timerId);
				try {
					var response = $.parseJSON(data);
				} catch(err){
					$('body').append(data);
					$('.diafan_div_error').css('left', $(window).width()/2 - $('.diafan_div_error').width()/2);
					$('.diafan_div_error').css('top', $(window).height()/2 - $('.diafan_div_error').height()/2 + $(document).scrollTop());
					$('.diafan_div_error_overlay').css('height', $('body').height());
					return false;
				}
				if (response.hash) {
					$('input[name=check_hash_user]').val(response.hash);
					$('.check_hash_user').text(response.hash);
				}
				if(response.curLoc) {
					var curLoc = prepare(response.curLoc);
					diafan_ajax.set_location(curLoc);
				}
				if(response.profiler) {
					$(".devoloper_profiling[ajax]").remove();
					$(".devoloper_profiler[ajax]").remove();
					$('body').append(prepare(response.profiler));
					delete response.profiler;
				}
				diafan_ajax.success(response);
				if(response.redirect) {
					window.location.href = prepare(response.redirect);
				}
			}),
			complete:(function (jqXHR, textStatus) {
				clearTimeout(diafan_ajax.timerId);
				diafan_ajax.complete(jqXHR, textStatus);
			}),
		}).done(function () {
			clearTimeout(diafan_ajax.timerId);
			diafan_ajax.done();
		}).fail(function () {
			clearTimeout(diafan_ajax.timerId);
			diafan_ajax.fail();
		});
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

$(".timecalendar").each(function () {
	if ($(this).attr('showtime') == "true") {
		$(this).datetimepicker({
			dateFormat:'dd.mm.yy',
			timeFormat:'hh:mm',
			language: 'ru',
		}).mask('99.99.9999 99:99');
	}
	else if($(this).attr('hideYear') == "true")
	{
		$(this).datepicker({
		dateFormat:'dd.mm'
		}).mask('99.99');
	}
	else{
		$(this).datepicker({
		dateFormat:'dd.mm.yy'
		}).mask('99.99.9999');
	}
});

if ($('input[name=check_hash_user]').length && $('input[name=check_hash_user]').val() != $('.check_hash_user').text()) {
	$('input[name=check_hash_user]').val($('.check_hash_user').text());
}

$('select.redirect').change(function () {
	var path = $(this).attr("rel");
	if ($(this).val()) {
		path = path + $(this).attr("name") + $(this).val() + '/';
	}
	window.location.href = path;
});

$(document).tooltip();

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
function htmlentities(s) {
	var div = document.createElement('div');
	var text = document.createTextNode(s);
	div.appendChild(text);
	return div.innerHTML;
}

// Временный редирект с закладки "Импорт/экспорт" модуля "Интернет магарин" на закладку "Импорт/экспорт" модуля "Модули и БД"
$(".tabs a.tabs__item").click(function (event) {
	event = event || window.event;
	if(event.ctrlKey || event.altKey || event.shiftKey) return true;
	event.preventDefault ? event.preventDefault() : (event.returnValue=false);
	var url = $(this)[0],
			href = url.href;
	if(pathname = url.pathname)
	{
		var arr = pathname.split('/');
		var index = arr.indexOf('importexport');
		if(index != -1)
		{
			pathname = '';
			arr.every(function(item, i) {
				if(i == (index - 1)) item = 'service';
				if(i == index) item = 'express';
				pathname += item + '/';
				if(i == index) return false;
				else return true;
			});
		}
		href = url.protocol+'//'+url.hostname;
		if(url.port) href += ':'+url.port;
		if(pathname) href += pathname;
		if(url.search) href += url.search;
		if(url.hash) href += url.hash;
	}
	document.location.href = href; // document.location.replace(href);
});

var diafan_action = {
	inArray: null,
  timeOut: 100,
  timerId: 0,
	self: {},
	informer: {
		object: {},
		class: 'informer',
		reset: true,
		set: function(content) {
			content = content || '';
			var informer = diafan_action.informer.object;
			if (! informer.length)
			{
				if (diafan_action.self.length && diafan_action.informer.class != '') {
					informer = diafan_action.self.prev('.' + diafan_action.informer.class);
					if (! informer.length)
					{
						diafan_action.self.before('<div class="' + diafan_action.informer_class + '"></div>');
						informer = diafan_action.self.prev('.' + diafan_action.informer_class);
					}
				}
			}
			if(informer.length)
			{
				informer.html(content); //informer.contents().remove(); informer.append(content);
				return true;
			}
			else return false;
		}
	},
	informer_class: 'informer',
	response: false,
	continue: false,
	success: false,
  error: false,
	config: false,
	ajax: diafan_ajax,
  init: function(config) {
		if (config.self) this.self = config.self;
		if (config.informer) {
			if (config.informer.object) this.informer.object = config.informer.object;
			if (config.informer.class) this.informer.class = config.informer.class;
			if ('reset' in config.informer) this.informer.reset = config.informer.reset;
		}
		if (config.response) this.response = config.response;
		else this.response = (function(response){});
		if (config.continue) this.continue = config.continue;
    else this.continue = (function(response){});
		if (config.success) this.success = config.success;
    else this.success = (function(response){});
    if (config.error) this.error = config.error;
    else this.error = (function(response){});
		if (config.config) this.config = config.config;
    else this.config = {};

		if(! this.config.success)
		{
			this.config["success"] = (function(response) {
				if(diafan_action.response(response) === false) {
					return false;
				}
				if (response.informer) {
					diafan_action.informer.set(prepare(response.informer));
        } else {
					if (diafan_action.informer.reset) diafan_action.informer.set();
				}
        if (response.result == 'success') {
					diafan_action.success(response);
				}
        if (response.result == 'error') {
					diafan_action.error(response);
        }
				if (response.result == 'continue') {
					if(diafan_action.continue(response) !== false) {
						diafan_action.timerId = setTimeout(function() {
							diafan_action.ajax.init(diafan_action.config);
						}, diafan_action.timeOut);
					}
        }
      });
		}
		this.ajax.init(this.config);
  }
};
// alternative method: переопределяем метод diafan_action.inArray
if ([].indexOf) { diafan_action.inArray = function(array, value) { return array.indexOf(value); } }
else { diafan_action.inArray = function(array, value) { for (var i = 0; i < array.length; i++) { if (array[i] === value) return i; } return -1; } }

(function( $ ) {
	var patterns = {
    "validate": /^[a-z_][a-z0-9_]*(?:\[(?:\d*|[a-z0-9_]+)\])*$/i,
    "key":      /[a-z0-9_]+|(?=\[\])/gi,
    "push":     /^$/,
    "fixed":    /^\d+$/,
    "named":    /^[a-z0-9_]+$/i
  };
	function FormSerializer($form) {
		// private variables
    var data   = {},
        pushes = {};
		// private API
		function build(base, key, value) {
      base[key] = value;
      return base;
    }
    function makeObject(root, value) {
      var keys = root.match(patterns.key), k;
      while ((k = keys.pop()) !== undefined) {
        if (patterns.push.test(k)) { // foo[]
          var idx = incrementPush(root.replace(/\[\]$/, ''));
          value = build([], idx, value);
        }
        else if (patterns.fixed.test(k)) { // foo[n]
          value = build([], k, value);
        }
        else if (patterns.named.test(k)) { // foo; foo[bar]
          value = build({}, k, value);
        }
      }
      return value;
    }
    function incrementPush(key) {
      if (pushes[key] === undefined) {
        pushes[key] = 0;
      }
      return pushes[key]++;
    }
    function encode(pair) {
			if(! $form || ! $form.length)
	    {
				return pair.value;
	    }
			switch ($('[name="' + pair.name + '"]', $form).attr("type")) {
        case "checkbox":
          return pair.value === "on" ? true : pair.value;
        default:
          return pair.value;
      }
    }
    function addPair(pair) {
      if (! patterns.validate.test(pair.name)) return this;
      var obj = makeObject(pair.name, encode(pair));
      data = $.extend(true, data, obj);
      return this;
    }
    function addPairs(pairs) {
      if (! $.isArray(pairs)) {
        throw new Error("formSerializer.addPairs expects an Array");
      }
      for (var i=0, len=pairs.length; i<len; i++) {
        this.addPair(pairs[i]);
      }
      return this;
    }
    function serialize() {
      return data;
    }
    function serializeJSON() {
      return JSON.stringify(serialize());
    }
    // public API
    this.addPair = addPair;
    this.addPairs = addPairs;
    this.serialize = serialize;
    this.serializeJSON = serializeJSON;
  }
  FormSerializer.patterns = patterns;
  FormSerializer.serializeObject = function serializeObject(arr) {
		if (this.length) {
			arr = this.serializeArray();
			/* Because serializeArray() ignores unset checkboxes and radio buttons: */
			var form = this;
			// ignores unset checkboxes
			if ($('input:checkbox:not(:checked)', form).length) {
				var chechbox = $('input:checkbox', form);
				var pairs = chechbox.map(function() {
					return { name: this.name, value: this.checked ? this.value : '' };
				});
				del = [];
				for (var idx=0, len=arr.length; idx<len; idx++) {
	        for (var i=0, l=pairs.length; i<l; i++) {
		        if (arr[idx].name == pairs[i].name
						&& arr[idx].value == pairs[i].value) {
							del.push(idx); break;
						}
		      }
	      }
				for (var i=0, l=del.length; i<l; i++) { arr.splice(del[i], 1); }
				for (var i=0, l=pairs.length; i<l; i++) { arr.push(pairs[i]); }
			}
		} else if ($.isArray(arr)) { arr = arr || []; } else { arr = []; }
    return new FormSerializer(this).
      addPairs(arr). // addPairs(this.serializeArray()).
      serialize();
  };
  FormSerializer.serializeJSON = function serializeJSON(arr) {
		if (this.length) {
			arr = this.serializeArray();
			/* Because serializeArray() ignores unset checkboxes and radio buttons: */
			var form = this;
			// ignores unset checkboxes
			if ($('input:checkbox:not(:checked)', form).length) {
				var chechbox = $('input:checkbox', form);
				var pairs = chechbox.map(function() {
					return { name: this.name, value: this.checked ? this.value : '' };
				});
				del = [];
				for (var idx=0, len=arr.length; idx<len; idx++) {
	        for (var i=0, l=pairs.length; i<l; i++) {
		        if (arr[idx].name == pairs[i].name
						&& arr[idx].value == pairs[i].value) {
							del.push(idx); break;
						}
		      }
	      }
				for (var i=0, l=del.length; i<l; i++) { arr.splice(del[i], 1); }
				for (var i=0, l=pairs.length; i<l; i++) { arr.push(pairs[i]); }
			}
		} else if ($.isArray(arr)) { arr = arr || []; } else { arr = []; }
    return new FormSerializer(this).
      addPairs(arr). // addPairs(this.serializeArray()).
      serializeJSON();
  };
  if (typeof $.fn !== "undefined") {
    $.fn.serializeObject = FormSerializer.serializeObject;
    $.fn.serializeJSON   = FormSerializer.serializeJSON;
  }
  return FormSerializer;
})( jQuery );

(function( $ ) {
  $.fn.clicker = function() {
    if(! this || ! this.length)
    {
      return this;
    }
		var link = this[0];
    var linkEvent = null;
    if (document.createEvent) {
      linkEvent = document.createEvent('MouseEvents');
      linkEvent.initEvent('click', true, true);
      link.dispatchEvent(linkEvent);
    }
    else if (document.createEventObject) {
      linkEvent = document.createEventObject();
      link.fireEvent('onclick', linkEvent);
    }
		return this;
  };
})( jQuery );

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


$(document).on('change', ".action-popup input, .action-popup textarea", function(){
	if($(this).attr('type') == 'checkbox')
	{
		$(".action-popup input[name='"+$(this).attr("name")+"'][value="+$(this).attr("value")+"]").prop("checked", $(this).is(":checked"));
	}
	else
	{
		$(".action-popup [name="+$(this).attr("name")+"]").val($(this).val());
	}
});
	
$('.depend_field').each(function(){
	var s = $(this);
	var depend = $(this).attr('depend').split(',');

	$.each(depend, function(){
		var f = function() {
			var show = true;
			$.each(depend, function(){
				var da = this.split('|');
				if (da.length > 1) {
					show = false;
					$.each(da, function(){
						var val = this.split('=', 2);
						if (val.length > 1) {
							if ($('input[name='+val[0]+']').is(':checked') || $('select[name='+val[0]+']').val() == val[1]) {
								show = true;
							}
						}
						else
						{
							if ($('input[name='+val[0]+']').is(':checked') || $('select[name='+val[0]+']').val()) {
								show = true;
							}
						}
					});
				}
				else
				{
					$.each(da, function(){
						var val = this.split('=', 2);
						if (val.length > 1) {
							if (! $('input[name='+val[0]+']').is(':checked') && ! ($('select[name='+val[0]+']').val() == val[1])) {
								show = false;
							}
						}
						else
						{
							if (! $('input[name='+val[0]+']').is(':checked') && ! $('select[name='+val[0]+']').val()) {
								show = false;
							}
						}
					});
				}
			});
			if (show) {
				s.show();
			}
			else
			{
				s.hide();
			}
		}
		var da = this.split('|');
		var d = '';
		$.each(da, function(){
			var val = this.split('=', 2);
			if (d) {
				d = d + ',';
			}
			d = d + 'input[name=' + val[0] + '],select[name=' + val[0] + ']';
		});
		$(document).on("change", d, f); // $(d).change(f);
		f();
	});
});
