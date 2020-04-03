/* DIAFAN.CMS */
var validate = {
	success: function(response){},
	submit: function(){
		$('.errors').remove();
		$('#save input[name=action]').val("validate");
		tinyMCE.triggerSave();
		$('#save').ajaxSubmit({
			url: window.location.href,
			type: 'POST',
			success:(function (result, statusText, xhr, form) {
				try {
					var response = $.parseJSON(result);
				} catch(err){
					$('body').append(result);
					$('.diafan_div_error').css('left', $(window).width()/2 - $('.diafan_div_error').width()/2);
					$('.diafan_div_error').css('top', $(window).height()/2 - $('.diafan_div_error').height()/2 + $(document).scrollTop());
					$('.diafan_div_error_overlay').css('height', $('body').height());
					return false;
				}
				validate.success(response);
				$("#save input[name=action]").val("save");
				if (response.hash) {
					$('input[name=check_hash_user]').val(response.hash);
					$('.check_hash_user').text(response.hash);
				}
				if (response.result == 'success') {
					$("#save").submit();
				}
				if(response.errors){
					var focus = false;
					var other = false;

					$.each(response.errors, function (k, val) {
						if(k) {
							if(! other)
							{
								if($("#"+k).parents('.content__right_supp').length && parseInt($('.content__right_supp').css('right')) < 0)
								{
									$('.btn_supp').click();
									other = true;
								}
							}
							$("#"+k).after('<div class="errors error">' + prepare(val) + '</div>');
							if(! focus)
							{
								$("#"+k).find("input, submit, textarea").first().focus();
								focus = true;
							}
						}
						else
						{
							$(".nav-box-wrap").after('<div class="errors error">' + prepare(val) + '</div>');
						}
					});
				}
			})
		});
	},
};
$('#save .btn_save').click(function(){
	validate.submit();
	return false;
});

if($(window).width() > 1320) $('.content__right_supp').addClass('desctop');


$('.btn_supp').click(function() {
	var $supp = $('.content__right_supp');
	if($supp.hasClass('desctop')){
		if(parseInt($supp.css('right')) < 0) {
			$supp.stop().animate({
				right: 24
			}, 'fast');

			$('.content__left_full, .nav-box-wrap').stop().animate({
				'margin-right': 350
			}, 'fast');
		} else{
			$supp.stop().animate({
				right: '-150%'
			}, 'fast');

			$('.content__left_full, .nav-box-wrap').stop().animate({
				'margin-right': 0
			}, 'fast');
		}
	} else{
		var $overlay = $('.ctr-overlay');

		if($overlay.is(':hidden')) {
			$('html, body').scrollTop(0);

			$('.content__right').animate({
				right: '0'
			});
			$overlay.fadeIn('fast');
		} else{
			$('.content__right').animate({
				right: '-150%'
			});
			$overlay.fadeOut('fast');
		}
	}
});

var dataStore = window.sessionStorage;
try {
	var tab_cookie = dataStore.getItem('tab_active'+$("#tabs").attr("index"));
} catch(e) {
	var tab_cookie = 0;
}

$('.nav-box_float').css('left', $('.nav').outerWidth());

$('.compress_nav').click(function() {
	var $nav_box = $(this).closest('.nav-box');

	if($nav_box.hasClass('nav-box_compress')) {
		$nav_box.removeClass('nav-box_compress').css({
			marginLeft: 0
		});
		nav_box_compress = 1;
	} else {
		$nav_box.addClass('nav-box_compress').css({
			marginLeft: ($(window).width()-$('.nav').outerWidth())/2-153
		});
		nav_box_compress = 0;
	}
	diafan_ajax.init({
		data:{
			action:'settings',
			name: 'nav_box_compress',
			value: nav_box_compress,
		}
	});
});

if (nav_box_compress == 1) {
	$('.nav-box').removeClass('nav-box_compress').css({
		marginLeft: 0
	});
} else {
	$('.nav-box').addClass('nav-box_compress').css({
		marginLeft: ($(window).width()-$('.nav').outerWidth())/2-153
	});
}

$("#tabs").tabs ({
    active : tab_cookie,
    activate : function ( event, ui ) {
        var newIndex = ui.newTab.parent().children().index(ui.newTab);
		dataStore.setItem('tab_active'+$("#tabs").attr("index"), newIndex );
    }
});(jQuery )

$(document).on('click', '.change_parent_id a', function () {
	diafan_ajax.init({
		data:{
			action: 'parent',
			parent_id: $('.change_parent_id input[name=parent_id]').val(),
			id: $('input[name=id]').val()
		},
		success: function(response){
			$('.change_parent_id').after(prepare(response.data));
			$('.change_parent_id').remove();
		}
	});
	return false;
});

$(document).on('click', '.change_sort a', function () {
	var self = $(this);
	diafan_ajax.init({
		data:{
			action:'sort',
			sort:self.attr("sort"),
			cat_id:self.attr("cat_id"),
			name:self.attr("sname"),
			site_id:self.attr("site_id"),
			parent_id:self.attr("parent_id"),
			sort:self.attr("sort"),
			id: $('input[name=id]').val(),
		},
		success: function(response) {
			$('.change_sort').after(prepare(response.data));
			$('.change_sort').remove();
		}
	});
	return false;
});

$(document).on('click', 'select option', function(){
	var select = $(this).parents('select');
	if(select.attr('multiple'))
	{
		if($(this).attr("value") == "all")
		{
			select.val("all");
		}
		else
		{
			select.find('option[value=all]').prop("selected", false);
		}
	}
});
var editor_codemirror = new Array;
$(document).on('click', '.htmleditor_check', function(){
	var id = $(this).attr('rel');
	if ($(this).is(":checked")) {
		tinyMCE.get(id).remove();
		editor_codemirror[id] = CodeMirror.fromTextArea(document.getElementById(id), {
			mode: "xml",
			lineNumbers: true,
			lineWrapping: true,
			matchBrackets: true,
			indentUnit: 4,
			indentWithTabs: true,
			extraKeys: {
				"Ctrl-Space": "autocomplete",
				"F11": function(cm) {
					cm.setOption("fullScreen", !cm.getOption("fullScreen"));
				},
				"Esc": function(cm) {
					if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
				}
			},
			matchBrackets: true,
			autoCloseBrackets: true,
		});
		editor_codemirror[id].setSize("100%", 500);
	}
	else
	{
		editor_codemirror[id].toTextArea();
		tinyMCE_init.load(id);
	}
});

$('.htmleditor_off').each(function(){
	var id = $(this).attr('id');
	editor_codemirror[id] = CodeMirror.fromTextArea(document.getElementById(id), {
		mode: "xml",
		lineNumbers: true,
		lineWrapping: true,
		matchBrackets: true,
		indentUnit: 4,
		indentWithTabs: true,
		extraKeys: {
			"Ctrl-Space": "autocomplete",
			"F11": function(cm) {
				cm.setOption("fullScreen", !cm.getOption("fullScreen"));
			},
			"Esc": function(cm) {
				if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
			}
		},
		matchBrackets: true,
		autoCloseBrackets: true,
	});
	editor_codemirror[id].setSize("100%", 500);
});


$('.inp_maxlength').keyup(function () {
	var max = $(this).attr("maxlength");
	if (! max) {
		max = $(this).attr("maxlength_recomm");
	}
	$(this).parents('.unit').find(".maxlength").text(max - $(this).val().length);
});

$('#input_redirect_edit').change(function() {
	if($('#input_redirect_add').is(':disabled'))
		$('#input_redirect_add').prop('disabled', false);
	else
		$('#input_redirect_add').prop('disabled', true);
});
$('#input_redirect_add').change(function() {
	if($('#input_redirect_edit').is(':disabled'))
		$('#input_redirect_edit').prop('disabled', false);
	else
		$('#input_redirect_edit').prop('disabled', true);
});

$('.ctr-close, .ctr-overlay').click(function() {
	var $supp = $('.content__right_supp');

	if($supp.hasClass('desctop')){
		$supp.animate({
			right: '-150%'
		}, 'fast');

		$('.content__left_full, .nav-box-wrap').animate({
			'margin-right': 0
		}, 'fast');
	} else {
		$('.content__right').animate({
			right: '-150%'
		});
		$('.ctr-overlay').fadeOut('fast');
	}
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
