$(document).on('click', ".rel_element_actions a", function() {
	var self = $(this);
	if (self.attr("action") != 'delete_rel_element')
	{
		return true;
	}
	if (! confirm(self.attr("confirm")))
	{
		return false;
	}
	diafan_ajax.init({
		data:{
			action: 'delete_rel_element',
			element_id : self.parents(".rel_element").attr("element_id"),
			rel_id : self.parents(".rel_element").attr("rel_id"),
			rel_two_sided:  $("#rel_elements").attr("rel_two_sided")
		},
		success: function(response){
			self.parents(".rel_element").remove();
		}
	});
	return false;
});
$('.rel_module_plus').click(function() {
	var self = $(this);
	diafan_ajax.init({
		data:{
			action: 'show_rel_elements',
			element_id: $('input[name=id]').val(),
			rel_two_sided:  $("#rel_elements").attr("rel_two_sided")
		},
		success: function(response){
			if (response.data)
			{
				$("#ipopup").html(prepare(response.data));
				centralize($("#ipopup"));
			}
		}
	});
	return false;
});
$(document).on('click', '.rel_module_navig a', function() {
	var self = $(this);
	diafan_ajax.init({
		data:{
			action: 'show_rel_elements',
			element_id: $('input[name=id]').val(),
			rel_two_sided:  $("#rel_elements").attr("rel_two_sided"),
			page: self.attr("page"),
			search: search,
			cat_id: cat_id
		},
		success: function(response){
			if (response.data)
			{
				$(".rel_all_elements_container").html(prepare(response.data));
			}
		}
	});
	return false;
});
var search = '';
var cat_id = '';
$(document).on('keyup change', '.rel_module_search, select.rel_module_cat_id', function() {
	if($(this).is('.rel_module_search'))
	{
		search = $(this).val();
	}
	if($(this).is('.rel_module_cat_id'))
	{
		cat_id = $(this).val();
	}
	diafan_ajax.init({
		data:{
			action: 'show_rel_elements',
			element_id: $('input[name=id]').val(),
			rel_two_sided:  $("#rel_elements").attr("rel_two_sided"),
			search: search,
			cat_id: cat_id
		},
		success: function(response){
			if (response.data)
			{
				$(".rel_all_elements_container").html(prepare(response.data));
			}
		}
	});
});
$(document).on('click', '.rel_module a', function() {
	var self = $(this);
	if (! self.parents('.rel_module').is('.rel_module_selected'))
	{
		diafan_ajax.init({
			data:{
				action: 'rel_elements',
				rel_id: self.parents(".rel_module").attr("element_id"),
				element_id: $('input[name=id]').val(),
				rel_two_sided:  $("#rel_elements").attr("rel_two_sided")
			},
			success: function(response){
				self.parents('.rel_module').addClass('rel_module_selected');
				if (response.data)
				{
					$(".rel_elements").html(prepare(response.data));
				}
				if (response.id)
				{
					$("input[name=id]").val(response.id);
				}
			}
		});
	}
	else
	{
		diafan_ajax.init({
			data:{
				action: 'delete_rel_element',
				element_id : $('input[name=id]').val(),
				rel_id : self.parents(".rel_module").attr("element_id"),
				rel_two_sided:  $("#rel_elements").attr("rel_two_sided")
			},
			success: function(response){
				self.parents('.rel_module').removeClass('rel_module_selected');
				$(".rel_element[rel_id="+self.parents(".rel_module").attr("element_id")+"]").remove();
			}
		});
	}
	return false;
});