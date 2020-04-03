/**
 * Редактирование файлов, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

 /* Теги (url на странице фильтра пожеланий)*/
	$(".js-moretags").click(function(e) {
		el = $(this).parent().find(".tagspane");
		if (el.hasClass("active")) return;
		el.slideDown().addClass("active");
		setTimeout(function(){
			$("body").one("click", function(){
				 el.slideUp().removeClass("active");
			});
		},100);

	});

$(".form-tags .tag").click(function(e) {
	target = $($(this).data("target"));
	// target.val($(this).text()+" "+target.val());
	target.val($(this).text());
});


/* Загрузка файлов */
$(document).on('change', ".unit .jq-file.file.multiple input:file", function() {
	if($(this).attr('multiple') || ! $(this).hasClass('file')) {
		return;
	}
	var wrapper = $(this).closest('.jq-file');
	if(wrapper.attr('changed'))
	{
		return;
	}
	wrapper.attr('changed', 'changed');
	var th = $(this).closest('.unit'),
			k_file = th.find('input.file').length;
	if (k_file > 10) {
		return false;
	}
	var el = $(this).clone(true);
	el[0].value = '';
	el.removeAttr('style');
	var data = el.data();
	for (var i in data) {
		if (i === '_styler') el.removeData(i);
	}
	var target = th.find('.infofield').eq(0);
	if(target.length) {
		target.after(el);
	} else {
		th.prepend(el);
	}
	if($.fn.styler) {
		el.styler();
		$(this).closest('.jq-file').find('.jq-file__browse').hide().after('<div class="jq-file__remove file_remove">&nbsp;</div>');
		var width = ($(this).closest('.jq-file').find('.jq-file__remove').position().left - $(this).position().left) - 5;
		$(this).css({"width":width+"px", "left":"0", "overflow":"hidden"});
	}
});
$(document).on('click', '.unit .jq-file.file.multiple .jq-file__remove', function(event) {
	event = event || window.event;
  var target = event.target || event.srcElement;
	event.stopPropagation ? event.stopPropagation() : (event.cancelBubble=true);
	$(this).closest('.jq-file.file.multiple').remove();
});


/* Прокрутка блока */
$(".scrollable").each(function() {
	$(this).scrollTop($(this)[0].scrollHeight);
});


/* Сообщение - предупреждение */
$(document).on('change', '#input_warn_read', function(event) {
	var self = $(this), form = $(this).closest('form');
	console.log(self.is(':checked'));
	diafan_ajax.init({
		data:{
			action: $("input[name=action]", form).val(),
			module: $("input[name=module]", form).val(),
			checked: (self.is(':checked') ? '1' : '')
		},
		success: function(response) {
			if (response.result) {
				if (response.result = 'success') {
					window.setTimeout( function(){
						var th = $("#warn_box");
						if(! th.length) th = form;
						th.css({"min-height": "inherit"}).slideUp(600, function() {
							$(this).remove();
						});
					}, 300 );
				}
			}
		}
	});
	return false;
});


/* Закрытие тикета */
$(document).on('click', '#subject_close input:button', function(event) {
	var self = $(this),
			th = self.closest("#subject_close");
	th.hide();
	var success = false;
	diafan_ajax.init({
		data:{
			action: 'close',
			module: 'account',
			id: self.data('subject_id')
		},
		success: function(response) {
			if (response.result) {
				if (response.result = 'success') {
					success = true;
					self.prop('disabled', true);
					th.remove();
				}
			}
		}
	});
	return success;
});


$('.tabs a.tabs__item[href *= "account/projects"]').attr({"href":"http://pro.user.diafan.ru/", "target":"_blank"});
