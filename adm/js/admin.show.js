$(function() {
	$("ul.list").on('click', '.action', function () {
		var self = $(this);
		if (! self.attr("action"))
		{
			return true;
		}
		if (self.attr("confirm") && ! confirm(self.attr("confirm")))
		{
			return false;
		}
		var form = $(this).parents('form');
		form.find('input[name=id]').val(self.parents("li").attr("row_id"));
		form.find('input[name=action]').val(self.attr("action"));
		form.find('input[name=module]').val(self.attr("module"));
		if (self.attr("question"))
		{
			var question = 0;
			if(confirm(self.attr("question")))
			{
				question = 1;
			}
			form.find('input[name=question]').remove();
			form.append('<input name="question" type="hidden" value="'+question+'">');
		}
		form.submit();
		return false;
	});

	$(document).on('mouseup', '.checkbox', function() {
		var $this = $(this);
		if($this.hasClass('checked')) {
			$this.add($this.closest('.item').find('.list .checkbox'))
			.removeClass('checked').find('input').prop('checked', false);
		} else {
			$this.add($this.closest('.item').find('.list .checkbox'))
			.addClass('checked').find('input').prop('checked', true);
		}
	});

	$('.select-all + label').click(function() {
		if($('.select-all').is(':checked')) {
			$('.checkbox').removeClass('checked').find('input:checked').click();
		} else {
			$('.checkbox').addClass('checked').find('input:not(:checked)').click();
		}

		$('.select-all').click();
	});

	$('select[name=group_action]').change(function() {
		var $checked =  $(this).find(':selected');

		$('.action-popup').css('padding-left', $('.select-all + label').outerWidth()+16);

		$('select[name=group_action]')
		.find('option:nth-child('+($checked.index()+1)+')')
		.prop('selected', true);

		$('.action-popup').each(function(){
			if($(this).is('.dop_' + $checked.attr("value")))
			{
				$(this).slideDown('fast');
			}
			else
			{
				$(this).slideUp('fast');
			}
		});

	});

	$(document).on('click', ".group_actions", function () {
		if (! $("input[name='ids[]']:checked").length) return false;

		var self = $("select[name=group_action] option:selected");
		if (self.attr("confirm") && ! confirm(self.attr("confirm")))
		{
			return false;
		}
		var form = $(this).parents('form');
		form.find('input[name=module]').val(self.attr('module'));
		form.find('input[name=action]').val($("select[name=group_action]").val());

		if (self.attr("question"))
		{
			var question = 0;
			if(confirm(self.attr("question")))
			{
				question = 1;
			}
			form.find('input[name=question]').remove();
			form.append('<input name="question" type="hidden" value="'+question+'">');
		}

		form.submit();

		return false;
	});
	$('.action-popup select').change(function() {
		var $checked =  $(this).find(':selected');

		$('.action-popup select[name="'+$(this).attr("name")+'"]')
		.find('option:nth-child('+($checked.index()+1)+')')
		.attr('selected', 'selected');

		$('.action-popup select[name="'+$(this).attr("name")+'"] ~ .jq-selectbox__dropdown').each(function() {
			$(this).find('li').removeClass('sel selected')
			.eq($checked.index()).addClass('sel').addClass('selected');
		});
		$('.action-popup select[name="'+$(this).attr("name")+'"] ~ .jq-selectbox__select').each(function() {
			$(this).find('.jq-selectbox__select-text')
			.text($checked.text())
		});
	});

	$(document).on('click', ".change_nastr", function () {
		diafan_ajax.init({
			data:{
				nastr:  $(this).prev('input[name=nastr]').val(),
				action: 'change_nastr'
			}
		});
		return false;
	});

	$('[name*="nastr"]').keyup(function(e) {
		if(e.keyCode == 13)
			$(this).next('.change_nastr').click();
	});

	var fast_edit = {
		old_value: false,
		element: false,
		init: function(){
			$(document).on('focus', this.element, function () {
				fast_edit.focus($(this));
			}).on('blur', this.element, function () {
				fast_edit.blur($(this));
			}).on('keyup', this.element, function (e) {
				fast_edit.keyup(e, $(this));
			});
			$(document).on('click', '.fast_edit .item__field i', function () {
				fast_edit.click($(this).closest('.item__field').find('input[type=text]').eq(0));
			});
		},
		blur: function($this){
			$this.parent().removeClass('focus');
		},
		focus: function($this){
			if($this.attr("type") == 'radio')
			{
				fast_edit.ajax($this);
			}
			else
			{
				$this.parent().addClass('focus');
			}
		},
		keyup: function(e, $this){
			if(!(e.keyCode == 37) && !(e.keyCode == 38) && !(e.keyCode == 39) && !(e.keyCode == 40)) {
				var $item = $this.closest('.item__field');


				$item.addClass('change').removeClass('success');

				$item.find('.item__field__cover span').text($this.val());

				if(e.keyCode == 13) {
					$item.addClass('success').removeClass('change');
					fast_edit.old_value = $this.attr('value');
					fast_edit.ajax($this);
				}
			}
		},
		click: function($this){
			var $item = $this.closest('.item__field');


			$item.addClass('change').removeClass('success');

			$item.find('.item__field__cover span').text($this.val());

			$item.addClass('success').removeClass('change');
			fast_edit.old_value = $this.attr('value');
			fast_edit.ajax($this);
		},
		ajax: function(e){
			diafan_ajax.init({
				data:{
					action:'fast_save',
					name:e.attr('name'),
					value:e.val(),
					type:e.attr('type'),
					id:e.attr('row_id')
				},
				success: function(response){
					if (response.res === false) {
						e.val(fast_edit.old_value);
						var $item = e.closest('.item__field');
						$item.find('.item__field__cover span').text(e.val());
					}
					if (response.res !== false && response.res !== true)
					{
						var $item = e.closest('.item__field');
						if (e.hasClass('numtext'))
						{
							e.val(formatStr(response.res));
							$item.find('.item__field__cover span').text(e.val());
						}
						else
						{
							e.val(response.res);
							$item.find('.item__field__cover span').text(e.val());
						}
					}
					if (e.attr('reload')) {
						window.location.href=document.location;
					}
				}
			});
		}
	}

	$(document).on('diafan.ready', function() {
		fast_edit.element = ".fast_edit textarea, .fast_edit input";
		fast_edit.init();
	});

	$(document).on("click", ".item__toggle .fa", function () {
		var $item = $(this).closest('.item');
		if (! $item.length)
		{
			$item = $(this).closest('.action-box');
			$(".item .item__toggle .fa").each(function(){
				tree_plus($(this).closest('.item'), $item.hasClass('active_all'), true);
			});
			if ($item.hasClass('active_all')) {
				$('.action-box').removeClass('active_all')
			}
			else
			{
				$('.action-box').addClass('active_all')
			}
		}
		else
		{
			tree_plus($item, $item.hasClass('active'), false);
		}
	});

	$(document).on("keydown", '.item__field input', function(e) {
		if(e.keyCode == 13) {
			e.preventDefault();
		}
	});

	$(document).on("keyup", '.item__field input', function(e) {
		if($(this).is('.numtext'))
		{
			$(this).val(formatStr($(this).val()));
			$('.item__field__cover span', $(this).parents('.item__field')).text($(this).val());
		}
	});

	$(document).on("click", '.item__field .fa-check-circle', function() {
		$(this).closest('.item__field').addClass('success').removeClass('change');
	});

	$(document).on("click", '.item__field__cover', function() {
		$(this).parent().find('input').focus();
		$(this).parent().addClass('focus');
	});

	if($(window).width() < 1023) {
		$('.item__ui').addClass('item__ui_adapt')

		$('.item__ui').click(function(e) {
			if($(this).hasClass('item__ui_adapt'))
				e.preventDefault()
		});

		$('.item__in').click(function() {
			$('.item__in .item__ui').addClass('item__ui_adapt');
			$(this).find('.item__ui_adapt').removeClass('item__ui_adapt');
		});
	}

	$('.item .text').each(function() {
		if ($(this).find('.fast_edit').length) {
			return;
		}
		$(this).html(truncate($(this).html(), 240));
	});
	$('.item .name').each(function() {
		if($(this).find('> a').length)
			$(this).find('> a').text(truncate($(this).find('> a').text(), 150));
		else
			$(this).text(truncate($(this).text(), 150));
	});

	$('.item__adapt').click(function() {
		var $this = $(this);

		if($this.hasClass('active')) {
			$this.css('padding-top', 0).removeClass('active')
			.closest('.item__in').removeClass('item__in_adaptive')
			.find('.item__unit').css('margin-top', 0);
		} else {
			$this.css('padding-top', (($(this).height()-18)/2)-2).addClass('active')
			.closest('.item__in').addClass('item__in_adaptive')
			.find('.item__unit').css('margin-top', '-'
										+($(this).closest('.item__in')
										  .find('.item__unit').height()/2)+'px');
		}
	});
	init_list();
	$(document).on('click', '.param-settings-show', function(){
		if($(this).next('.param-settings').css('display') == 'none')
		{
			$(this).next('.param-settings').fadeIn('slow');
		}
		else
		{
			$(this).next('.param-settings').fadeOut('fast');
		}
	});
});

function tree_plus($item, $plus, $all)
{
	if($plus)
	{
		$item.find(' > .list > .item,  > .paginator').slideUp('fast', function() {
			$item.removeClass('active');
		});
	}
	else
	{
		if($item.find(' > .list > .item').length)
		{
			$item.find(' > .list > .item,  > .paginator').slideDown('fast', function() {
				$item.addClass('active');
			});
		}
		else
		{
			var id = $item.attr("row_id");

			$.ajax({
				url:window.location.href,
				type:"POST",
				dataType:"json",
				cache:false,
				async:false,
				data:{
					ajax: "expand",
					parent_id: id,
					check_hash_user: $('.check_hash_user').text()
				},
				success:function (response) {
					if (response.hash) {
						$('input[name=check_hash_user]').val(response.hash);
						$('.check_hash_user').text(response.hash);
					}
					if (response.html) {
						$item.addClass('active');
						$item.append(prepare(response.html));
						init_list();
						sort_items.start();
					}
					if ($all) {
						$(".item .item__toggle .fa", $item).each(function(){
							tree_plus($(this).closest('.item'), $plus, true);
						});
					}
				}
			});
		}
	}
}
function init_list()
{
	$('.numtext').each(function() {
		$(this).val(formatStr($(this).val()));
	});

	$('.item__field').each(function() {
		var $this = $(this);
		$this.find('.item__field__cover span').text($this.find('input').val());
	});

	$('.item__field__cover').click(function() {
		var $this = $(this);

		if(!$this.hasClass('visible')) {
			$this.addClass('visible');

			setCaretPosition($this.parent().find('input').get(0),
							 $this.parent().find('input').val().length + 1)
		}
	});

	do_auto_width();
}

function formatStr(str) {
	str = str.replace(/\s+/g,'');
	d = str.match(/([\.,](.*))/g);
	if (! d)
	{
		d = '';
	}
	str = str.replace(/([\.,](.*))/g, '');
	var arr = str.split('');
	var str_temp = '';
	if (str.length > 3) {
		for (var i = arr.length - 1, j = 1; i >= 0; i--, j++) {
			str_temp = arr[i] + str_temp;
			if (j % 3 == 0 && i !=0) {
				str_temp = ' ' + str_temp;
			}
		}
		return str_temp + d;
	} else {
		return str + d;
	}
}

// обрезает строку и возвращает её ставя в конце '...'
function truncate(str, max) {
	return (str.length > max) ?
			str.substring(0, max-3) + '...': str;
};

// устанавливаем позицию курсора
function setCaretPosition(ctrl, pos) {
	if(ctrl.setSelectionRange) {
		ctrl.setSelectionRange(pos,pos);
	}
	else if (ctrl.createTextRange) {
		var range = ctrl.createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
	}
}
