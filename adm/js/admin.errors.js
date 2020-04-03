$(document).ready(function () {
	$(document).on('click', '.diafan_errors td.calls', function () {
		$(this).children('div').toggle();
	});
	$(document).on('click', '.diafan_div_error_overlay', function(){
		$('.diafan_div_error_overlay, .diafan_div_error').remove();
	});
});