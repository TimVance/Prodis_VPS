/**
 * Панель быстрого редактирования, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

var useradmin = {
	is_edit : false,
	is_toggle: 0,
	top: false,
	store: false,
	init: function(){
		useradmin.store = window.sessionStorage;

		try {
			useradmin.is_edit = parseInt(useradmin.store.getItem('useradmin_is_edit'), 10);
			useradmin.is_toggle = useradmin_is_toggle;
		} catch(e) {}

		useradmin.top = $("*").filter(function() {
			return $(this).css("position") === 'fixed' && $(this).css("top") === '0px';
		});

		$.ajax({
			url:window.location.href,
			type:"POST",
			dataType:"html",
			data:{ module: 'useradmin', deactivate: window.useradmin_page_deactivate || 0 },
			success:(function (response) {
				if(response) {
					$("body").css("padding-top", "52px");
					if(useradmin.top.length) {
						useradmin.top.css("top", "52px");
					}

					$("body").prepend(response);
					if (useradmin.is_toggle == 1) {
						useradmin.toggle();
					}
					if(useradmin.is_edit == 1) {
						useradmin.show_panel();
					}
				}
			})
		});

		$(document).on('mouseover', ".useradmin_contener", function () {
			if (useradmin.is_edit == 1) {
				$(this).addClass("useradmin_active");
				return false;
			}
		});
		$(document).on('mouseout', ".useradmin_contener", function () {
			if (useradmin.is_edit == 1) {
				$(this).removeClass("useradmin_active");
				return false;
			}
		});
		$(document).on('click', ".useradmin_contener", function () {
			if (useradmin.is_edit == 1) {
				$.fancybox.open({src : $(this).attr('href'), type: 'iframe'});
				return false;
			}
		});

		$(document).on('click', '.diafan-admin-toggle', function() {
			if (useradmin.is_toggle == 1) {
				useradmin.is_toggle = 0;
			} else {
				useradmin.is_toggle = 1;
			}
			$.ajax({
				url: useradmin_path,
				type:"POST",
				dataType: 'json',
				data:{
					action:'settings',
					name: 'useradmin_is_toggle',
					value: useradmin.is_toggle,
					check_hash_user: useradmin_hash,
				},
				success:(function (response) {
					useradmin_hash = response.hash;
				})
			});
			useradmin.toggle();
		});

		$(document).on('mouseover', '.diafan-admin-link', function() {
			$(this).find('.diafan-admin-popup').stop().fadeIn('fast');
		});
		$(document).on('mouseout', '.diafan-admin-link', function() {
			$(this).find('.diafan-admin-popup').stop().fadeOut('fast');
		});

		$(document).on('click', ".useradmin_panel .go_edit", useradmin.edit);
	},
	edit: function(){
		if (useradmin.is_edit) {
			useradmin.is_edit = 0;
			$(".useradmin_panel .diafan-admin-link_toggle").removeClass("diafan-admin-toggle-blink");
			$(".useradmin_meta").hide();
			$(".fa-toggle-off").show();
			$(".fa-toggle-on").hide();
		} else {
			useradmin.is_edit = 1;
			useradmin.show_panel();
		}
		useradmin.store.setItem('useradmin_is_edit', useradmin.is_edit);
		return false;
	},

	show_panel: function() {
		$(".useradmin_panel .diafan-admin-link_toggle").addClass("diafan-admin-toggle-blink");
		$(".useradmin_meta").show();
		$(".fa-toggle-off").hide();
		$(".fa-toggle-on").show();
		$(".useradmin_meta").html(
			'<table><tr><td class="useradmin_meta_first_td"><span class="useradmin_contener" href="'+$("meta[name=useradmin_title]").attr("content")+'">title:</span></td><td><span class="useradmin_contener" href="'+$("meta[name=useradmin_title]").attr("content")+'">'+$("title").text()+'</span></td></tr>'
			+
			'<tr><td class="useradmin_meta_first_td"><span class="useradmin_contener" href="'+$("meta[name=useradmin_keywords]").attr("content")+'">keywords:</span></td><td><span class="useradmin_contener" href="'+$("meta[name=useradmin_keywords]").attr("content")+'">'+$("meta[name=keywords]").attr("content")+'</span></td></tr>'
			+
			'<tr><td class="useradmin_meta_first_td"><span class="useradmin_contener" href="'+$("meta[name=useradmin_description]").attr("content")+'">description:</span></td><td><span class="useradmin_contener" href="'+$("meta[name=useradmin_description]").attr("content")+'">'+$("meta[name=description]").attr("content")+'</span></td></tr></table>'
		);
		/* $("body").css("padding-top", "52px"); */
	},

	toggle: function() {
		if (useradmin.is_toggle != 1) {
			$("body").css("padding-top", "52px");
			if(useradmin.top.length) {
				useradmin.top.css("top", "52px");
			}
			$("body").css("padding-bottom", "0px");
		} else {
			$("body").css("padding-top", "0px");
			if(useradmin.top.length)
			{
				useradmin.top.css("top", "0px");
			}
			$("body").css("padding-bottom", "52px");
		}
		$('.diafan-admin-panel').toggleClass('diafan-admin-panel_bottom');
	}
}

useradmin.init();
