/*DIAFAN.CMS*/
var user_search = '';

$('.user_id_edit').click(function () {
	$(this).next('div').toggle();
});
$('input[name=user_search]').keyup(function(){
	var self = $(this);
	if(user_search == self.val())
	{
		return;
	}
	user_search = self.val();
	if(! user_search)
	{
		$('.user_search_select').remove();
		return;
	}
	diafan_ajax.init({
		data:{
			action: 'user_list',
			search: user_search
		},
		success: function(response){
			if (response.data) {
				self.next('.user_search_select').remove();
				self.after(prepare(response.data));
			}
		}
	});
});
$(document).on('click', '.user_search_select li', function(){
	$('input[name=user_search]').val($(this).text());
	$('input[name=user_id]').val($(this).attr('user_id'));
	$('.user_search_select').remove();
});