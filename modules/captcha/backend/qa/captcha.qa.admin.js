var captcha_qa_i = 1;
$('.js_captcha_qa_plus').click(function() {
	$('.js_captcha_qa_item_tpl').before($('.js_captcha_qa_item_tpl').clone(true));
	$('.js_captcha_qa_item_tpl:first').show().removeClass('js_captcha_qa_item_tpl').addClass('js_captcha_qa_item');
	$('.js_captcha_qa_item:last input[type=checkbox]').each(function(){
		$(this).attr('id', $(this).attr('id') + 'n' + captcha_qa_i);
	});
	$('.js_captcha_qa_item:last label').each(function(){
		$(this).attr('for', $(this).attr('for') + 'n' + captcha_qa_i);
	});
	$('.js_captcha_qa_item:last input[name=captcha_qa_i]').val(captcha_qa_i);
	captcha_qa_i = captcha_qa_i+1;
	return false;
});
$('.js_captcha_qa_a_plus').click(function() {
	$(this).before($('.js_captcha_qa_item_tpl .js_captcha_qa_a').clone(true));
	$(this).prev('.js_captcha_qa_a').find('input[type=checkbox]').each(function(){
		$(this).attr('id', $(this).attr('id') + 'n' + captcha_qa_i);
	});
	$(this).prev('.js_captcha_qa_a').find('label').each(function(){
		$(this).attr('for', $(this).attr('for') + 'n' + captcha_qa_i);
	});
	var i = $(this).parents('.js_captcha_qa_item'). find("input[name='captcha_qa_i[]']").val();
	$(this).prev(".js_captcha_qa_a").find("input[name='captcha_qa_a_i[]']").val(i);
	captcha_qa_i = captcha_qa_i+1;
	return false;
});
$('.js_captcha_qa_a input[type=checkbox], .js_captcha_qa_item input[type=checkbox]').change(function() {
	var val = 0;
	if($(this).is(':checked'))
	{
		val = 1;
	}
	$(this).prev('input[type=hidden]').val(val);
});

$(document).on('click', ".js_captcha_qa_item a[action=delete_param]", function(){
	if ( $(this).attr("confirm") && ! confirm( $(this).attr("confirm")))
	{
		return false;
	}
	$(this).parents(".js_captcha_qa_item").remove();
	return false;
});

$(document).on('click', ".js_captcha_qa_a a[action=delete_a]", function(){
	if ( $(this).attr("confirm") && ! confirm( $(this).attr("confirm")))
	{
		return false;
	}
	$(this).parents(".js_captcha_qa_a").remove();
	return false;
});
