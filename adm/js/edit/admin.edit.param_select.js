show_param($("#type select"));
$("#type select").change(function(){
	show_param($(this));
});
$('input[name=param_textarea_check]').click(function(){
	if($(this).is(':checked'))
	{
		$('.param_textarea').show();
		$('.param_container').hide();
	}
	else
	{
		$('.param_textarea').hide();
		$('.param_container').show();
	}
});
$(".param_actions a[action=up_param]").first().hide();
$(".param_actions a[action=down_param]").last().hide();

$(document).on('click', ".param_actions a[action=delete_param]", function(){
	if ( $(this).attr("confirm") && ! confirm( $(this).attr("confirm")))
	{
		return false;
	}
	if($(this).parents(".param").find("a[action=down_param]").is(":hidden"))
	{
		$(this).parents(".param").prev(".param").find("a[action=down_param]").hide();
	}
	if($(this).parents(".param").find("a[action=up_param]").is(":hidden"))
	{
		$(this).parents(".param").next(".param").find("a[action=up_param]").hide();
	}
	$(this).parents(".param").remove();
	return false;
});

$(document).on('click', ".param_actions a[action=up_param]", function() {
	var self = $(this).parents(".param");
	self.prev(".param").before(self.clone(true));
	self.remove();

	$(".param_actions a[action=up_param]").show();
	$(".param_actions a[action=down_param]").show();
	$(".param_actions a[action=up_param]").first().hide();
	$(".param_actions a[action=down_param]").last().hide();
	return false;
});
$(document).on('click', ".param_actions a[action=down_param]", function() {
	var self = $(this).parents(".param");
	self.next(".param").after(self.clone(true));
	self.remove();

	$(".param_actions a[action=up_param]").show();
	$(".param_actions a[action=down_param]").show();
	$(".param_actions a[action=up_param]").first().hide();
	$(".param_actions a[action=down_param]").last().hide();
	return false;
});
$('.param_plus').click(function() {
	$('.param:last').after($('.param:last').clone(true));
	$('.param:last input').val('');

	$(".param_actions a[action=up_param]").show();
	$(".param_actions a[action=down_param]").show();
	$(".param_actions a[action=up_param]").first().hide();
	$(".param_actions a[action=down_param]").last().hide();
	return false;
});
$('.param_sort_name').click(function(){
	var sr = '';
	$(this).after('<div class="new_param_container"></div>');
	$(".param").each(function(){
		var name = $(this).find("input[name='paramv[]']").val();
		var clone = $(this).clone(true);
		if(! name)
		{
			$('.new_param_container').append(clone);
			$(this).remove();
			return true;
		}
		var is_insert = false;
		$('.new_param_container .param').each(function(){
			if(is_insert)
			{
				return;
			}
			var name2 = $(this).find("input[name='paramv[]']").val();
			if(! name2 || name2 > name)
			{
				$(this).before(clone);
				is_insert = true;
			}
		});
		if(! is_insert)
		{
			$('.new_param_container').append(clone);
		}
		$(this).remove();
	});
	$('.new_param_container').removeClass('new_param_container');
	$(".param_actions a[action=up_param]").show();
	$(".param_actions a[action=down_param]").show();
	$(".param_actions a[action=up_param]").first().hide();
	$(".param_actions a[action=down_param]").last().hide();
	return false;
});
/* D I A F A N . C M S */
function show_param(obj)
{
	if (obj.val() == "select" || obj.val() == "multiple" || obj.val() == "radio")
	{
		$("#param").show();
	}
	else
	{
		$("#param").hide();
	}
	if(! obj.attr("req_self") && ! obj.closest(".unit").attr("req_self"))
	{
		if (obj.val() == "title")
		{
			$("#required").hide();
		}
		else
		{
			$("#required").show();
		}
	}
	if (obj.val() == "checkbox")
	{
		$("#param_check").show();
	}
	else
	{
		$("#param_check").hide();
	}
	if(obj.val() == "attachments")
	{
		$('#attachments, #max_count_attachments, #attachment_extensions, #recognize_image, #attachments_access_admin, #upload_max_filesize').show();
		if($('#recognize_image input[type=checkbox]').is(':checked'))
		{
			$('#attach_big, #attach_medium, #attach_use_animation').show();
		}
	}
	else
	{
		$('#attachments, #max_count_attachments, #attachment_extensions, #recognize_image, #attach_big, #attach_medium, #attach_use_animation, #attachments_access_admin, #upload_max_filesize').hide();
	}
	if(obj.val() == "images")
	{
		$('#images_variations, #resize').show();
	}
	else
	{
		$('#images_variations, #resize').hide();
	}
}
