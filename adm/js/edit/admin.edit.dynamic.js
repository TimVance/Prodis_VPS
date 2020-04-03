$('.dynamic_infofield').click(function() {
	$(this).next().stop().slideToggle('fast').toggleClass('dynamic_hide');
});

$('.dynamic_infofield > a').click(function(e) {
	e.stopPropagation();
});