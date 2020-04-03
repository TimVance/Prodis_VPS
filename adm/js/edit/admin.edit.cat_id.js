/*DIAFAN.CMS*/
var cat_search = '';

$(document).on('click', '.cat_id_edit', function () {
	var th = $(this).nextAll('.cat_id_edit_container');
	if (! th.length)
	{
		th = $(this).closest(".infofield").nextAll('.cat_id_edit_container');
		if (! th.length)
		{
			th = $(this).closest(".infofield").nextAll('.additional_cat_ids').find('.cat_id_edit_container').eq(0);
		}
	}
	if (! th.length)
	{
		return;
	}
	th.toggle();
});
$(document).on('click', '.cat_id_remove', function () {
	var th = $(this).nextAll('.cat_id_edit_container');
	if (! th.length)
	{
		th = $(this).closest(".infofield").nextAll('.cat_id_edit_container');
	}
	if (! th.length)
	{
		return;
	}
	th.find('input[name=cat_id]').eq(0).val('');
	var target = $(this).prevAll('.cat_id_edit_target');
	if (! target.length)
	{
		return;
	}
	target.replaceWith('<span class="cat_id_edit_target">-</span>');
});
$(document).on('diafan.ready', function() {
	$('input[name=cat_search]').keyup(function() {
		var self = $(this);
		if(cat_search == self.val())
		{
			return;
		}
		cat_search = self.val();
		if(! cat_search)
		{
			$('.cat_search_select').remove();
			return;
		}
		diafan_ajax.init({
			data:{
				action: 'cat_list',
				search: cat_search,
				cat_id: $('input[name="cat_id"]').val(),
				cat_ids: $('input[name="cat_ids[]"]').serializeObject()
			},
			success: function(response){

				self.next('.cat_search_select').remove();
				self.after(prepare(response.data));
			}
		});
	}.debounce(500));
});
$(document).on('click', '.cat_search_select li', function(){
	var th = $(this).closest(".cat_id_edit_container");
	if (! th.length)
	{
		return;
	}
	var addition = th.parents('.additional_cat_ids');
	if(addition.length)
	{
		th.find('input[name=cat_search]').val('');
		addition.append('<div><input type="checkbox" name="cat_ids[]" value="'+$(this).attr('cat_id')+'" id="input_user_additional_cat_id_'+$(this).attr('cat_id')+'" checked> <label for="input_user_additional_cat_id_'+$(this).attr('cat_id')+'">'+$(this).text()+'</label></div>');
		th.toggle();
	}
	else
	{
		th.find('input[name=cat_search]').eq(0).val(''); // th.find('input[name=cat_search]').eq(0).val($(this).text());
		th.find('input[name=cat_id]').eq(0).val($(this).attr('cat_id'));
		th.toggle();
		var target = th.prevAll('.cat_id_edit_target');
		if (! target.length)
		{
			return;
		}
		target.replaceWith('<a class="cat_id_edit_target" href="'+$(this).attr('href')+'">'+$(this).text()+'</a>')
	}
	th.find('.cat_search_select').remove();
});


$('#input_user_additional_cat_id').change(function() {
	$('.cat_ids').stop().slideToggle('fast');
});

if(!$('#input_user_additional_cat_id').is(':checked')) $('.cat_ids').hide();

$('input[name=multi_site]').change(function(){
	if ($(this).is(':checked'))
	{
		$('select[name="cat_ids[]"] option').show();
	}
	else
	{
		var site_id = $('select[name=site_id]').val();
		$('select[name="cat_ids[]"] option').each(function(){
			if ($(this).attr('rel') && $(this).attr('rel') !== "0" && $(this).attr('rel') !== site_id) {
				$(this).hide();
				if ($(this).is(':selected')) {
					$(this).prop('selected', false);
				}
			}
			else
			{
				$(this).show();
			}
		});
	}
});
