$(function() {
	if($.fn.styler) {
		$('input.file').styler();
	}
	if($.fn.tooltip)
	    $(document).tooltip();

	if($.fn.datepicker)
		$('.datepicker').datepicker({
			dateFormat: 'dd.mm.yy'
		});

	$('.header__link').hover(function() {
		$(this).find('.header__popup').stop().fadeToggle('fast');
	});
	$('.header__link_pp > a').click(function(e) {e.preventDefault()});

	$('.lang-more').click(function(e) {
		e.stopPropagation();
		$(this).find('.header__popup').stop().fadeToggle('fast');
	});
	$('.search__link').click(function(e) {
		e.preventDefault();
		$('.search__in').stop().show('fast');
	});
	$('.search__in .fa-close').click(function(e) {
		e.preventDefault();
		$('.search__in').stop().hide('fast');
	});

	$('.nav__toggle').click(function() {
		var $this = $(this),
			$nav = $('.nav');
		$this.toggleClass('active');

		if(!$nav.hasClass('nav_hidden')) {
			$this.find('.fa-caret-left').hide();

			if($(window).width() > 744)
				$this.parent().find('.fa-caret-right').fadeIn('fast');

			var nav_width = ($(window).width() > 744) ? 62: 42;

			$nav.addClass('nav_hidden').add('.nav-bg')
			.stop().animate({width: nav_width}, 'fast', function() {
				$nav.find('.nav__info').each(function() {
					$(this).css('margin-left', '-'+($(this).outerWidth()/2)+'px');
				})

				$('.col-right').css({
					'min-height': ($('.nav').height()) + 'px'
				});

				if($(window).width() > 1023)
					$('.nav-box_float').css('left', $('.nav').outerWidth());

				$('.nav-box_compress').css({
					marginLeft: ($(window).width()-$('.nav').outerWidth())/2-153
				});
			});
			$('.nav__item span:not(.nav__info)').fadeOut('fast');
			$('.nav__heading').hide();
		} else {
			$this.find('.fa-caret-right').hide()
			.parent().find('.fa-caret-left').fadeIn('fast');

			$nav.removeClass('nav_hidden').add('.nav-bg')
			.stop().animate({width: 230}, 'fast', function() {
				$nav.find('.nav__info').each(function() {
					$(this).css('margin-left', 0);

					$('.col-right').css({
						'min-height': ($('.nav').height()) + 'px'
					});
				})

				if($(window).width() > 1023)
					$('.nav-box_float').css('left', $('.nav').outerWidth());

				$('.nav-box_compress').css({
					marginLeft: ($(window).width()-$('.nav').outerWidth())/2-153
				});
			});
			$('.nav__item span:not(.nav__info)').fadeIn('fast');
			$('.nav__heading').fadeIn('fast');
		}
	});

	hide_nav();

	$('.item__folder, .folders .name').click(function() {
		var $item = $(this).closest('.item');

		if($item.hasClass('open')) {
			$item.find(' > .list > .item').slideUp('fast', function() {
				$item.removeClass('open').find('.item').removeAttr('style');
			});
		} else{
			$item.find(' > .list > .item').slideDown('fast', function() {
				$item.addClass('open');
			});
		}
	});

	$('.btn_inp_file').click(function(e) {
                e.preventDefault();
		$(this).closest('.inp-file').find('input[type="file"]').trigger('click');
	});
	$('.inp-file input[type="file"]').change(function(e) {
		$(this).closest('.inp-file').find('.btn_inp_name').remove();
		$(this).closest('.inp-file').append('<span class="btn_inp_name">' + $(this).val().replace(/[\"\=\<\>]+/, '') + '</span>');
	});
	$('.box-file').mouseup(function() {
		$(this).find('input[type="file"]').click();
	});

	$(document).on('click', '.btn_filter, .ctr-overlay, .ipopup__close', function() {
		var $overlay = $('.ctr-overlay');

		if($overlay.is(':hidden')) {
			$('html, body').scrollTop(0);

			$('.content__right').addClass('fix_anc').animate({
				right: '0'
			});
			$overlay.fadeIn('fast');
		} else{
			if (! $('.content__right').is('.content__right_supp')) {
				$('.content__right').removeClass('fix_anc').animate({
					right: '-150%'
				}, 'fast', function() {
				$(this).removeClass('fix_anc');
				});
			}
			$overlay.fadeOut('fast');

			$('.ipopup').fadeOut('fast');
		}
	});
	$('.col-right').css({
		'min-height': ($('.nav').height()) + 'px',
		'padding-bottom': ($('.footer').outerHeight()+20) + 'px'
	});


	if($(window).width() > 1320)
		$('.content__right').css('top', ($('.content__left').position()||$()).top);

	$(window).load(function() {
		if($(window).width() > 1320)
			$('.content__right').css('top', ($('.content__left').position()||$()).top);

		if($('.content__right').outerHeight()
		   + ($('.content__right').position()||$()).top
		   > $('.col-right').outerHeight()) {

			$('.col-right')
			.css('min-height', $('.content__right').outerHeight()
						 + ($('.content__right').position()||$()).top
						 + $('.footer').outerHeight());
		}
	});

	if($('.content__right').outerHeight()
	   + ($('.content__right').position()||$()).top
	   > $('.col-right').outerHeight()) {
		
		$('.col-right')
		.css('min-height', $('.content__right').outerHeight()
					 + ($('.content__right').position()||$()).top
					 + $('.footer').outerHeight());
	}
	$('.dapicker-wrap .fa-calendar').click(function() {
		$(this).parent().find('.datepicker').focus();
	});


	if($(window).width() > 1023) $('.nav').addClass('desctop');

	$('.chpopup').change(function() {
		$(this).closest('.unit').find('.unit__hidden').stop().slideToggle('fast');
	});

	$('.unit__hidden').hide();
	$('.chpopup').each(function() {
		var $this = $(this)

		if($this.is(':checked')) {
			$this.closest('.unit').find('.unit__hidden').show();
		}
	});

	$('.tags_container a .fa-close').click(function(e) {
		e.preventDefault();
	});

	$('.plink').click(function(e) {
		e.preventDefault();

		centralize($($(this).attr('href')));
	});

	$('.images_button a[action*="edit"]').click(function(e) {
		e.preventDefault();
		centralize($(this).closest('.images_actions').find('.ipopup_edit'))
	});

	centralize($('.login-form'));

	$('.login-field').focus();

	$('.login-pas-toggle').click(function() {
		var $pas_field = $('.pass-field');

		$(this).toggleClass('show_pas')

		if($pas_field.prop('type') == 'text')
			$pas_field.prop('type', 'password')
		else
			$pas_field.prop('type', 'text')
	});

	$('.content-fix')
	.width($('.content__right').outerWidth())
	.height($(document).height());
});


$(document).click(function() {
	$('.lang-more .header__popup').stop().fadeOut('fast');
});
var resizing_timer;
$(window).resize(function() {
	$('.nav-box').css('left', $('.nav').outerWidth());

	$('.nav-box_compress').css({
		marginLeft: ($(window).width()-$('.nav').outerWidth())/2-153
	});

	if($('.nav').hasClass('desctop') && $(window).width() < 1024) {
		hide_nav();
		$('.nav').removeClass('desctop');
	}
	if(!$('.nav').hasClass('desctop') && $(window).width() > 1023)
		$('.nav').addClass('desctop');


	if(!$('.content__right_supp').hasClass('desctop') && $(window).width() > 1320) {
		$('.content__right_supp').addClass('desctop');

		$('.content__right_supp').removeClass('fix_anc').css({
			right: '-150%'
		});
		$('.ctr-overlay').hide();
	}

	if($('.content__right_supp').hasClass('desctop') && $(window).width() < 1321) {
		$('.content__right_supp').removeClass('desctop fix_anc').css({
			right: '-150%'
		});
		$('.content__left_full, .nav-box-wrap').css('margin-right', 0);
	}

	if($('.nav').hasClass('nav_mob') && $(window).width() > 1023) {
		var $nav = $('.nav');

		$nav.find('.fa-caret-right').hide()
		.parent().find('.fa-caret-left').fadeIn('fast');

		$nav.find('.nav__toggle').removeClass('active');

		$nav.removeClass('nav_hidden nav_mob').add('.nav-bg')
		.stop().animate({width: 230}, 'fast', function() {
			$nav.find('.nav__info').each(function() {
				$(this).css('margin-left', 0);

				$('.col-right').css({
					'min-height': ($('.nav').height()) + 'px'
				});

				if($('.content__right').outerHeight()
				   + ($('.content__right').position()||$()).top
				   > $('.col-right').outerHeight()) {

					$('.col-right')
					.css('min-height', $('.content__right').outerHeight()
								 + ($('.content__right').position()||$()).top
								 + $('.footer').outerHeight()+30);
				}
			});

			$('.nav-box').css('left', $('.nav').outerWidth());

			$('.nav-box_compress').css({
				marginLeft: ($(window).width()-$('.nav').outerWidth())/2-153
			});
		});
		$('.nav__item span:not(.nav__info)').fadeIn('fast');
		$('.nav__heading').fadeIn('fast');
	}

	if($(window).width() < 745 && $('.nav').hasClass('nav_hidden')) {
		$('.col-right').css('margin-left', $('.nav').outerWidth())
	}

	if(!$('.content__right').hasClass('content__right_supp') && $(window).width() > 1188) {
		$('.content__right').removeClass('fix_anc').animate({
			right: '-150%'
		});
		$('.ctr-overlay').fadeOut('fast');
	}

	if($(window).width() > 1320 && !$('.content__right').hasClass('installed_top')) {
		$('.content__right').addClass('installed_top');

		setTimeout(function() {
			$('.content__right').css('top', ($('.content__left').position()||$()).top)
		}, 100)
	} else if($(window).width() < 1321 && ($('.content__right').position()||$()).top > 0)
		$('.content__right').removeClass('installed_top').css('top', 0);

	if($('.content__right').hasClass('content__right_supp')
	   && $('.nav').height() 
	   < $('.content__right').outerHeight()
	   + ($('.content__right').position()||$()).top) {

		clearInterval(resizing_timer)
		resizing_timer = setTimeout(function() {
			$('.col-right')
			.css('min-height', $('.content__right').outerHeight()
						 + ($('.content__right').position()||$()).top
						 + $('.footer').outerHeight()+30);
		}, 300)
	}

	centralize($('.login-form'));
});

$(window).scroll(function() {
	if($(this).width() < 480) {
		$('.nav-box').css({
			left: -$(this).scrollLeft()+($('.nav').outerWidth())
		});
	}

	if($(document).scrollTop() > (($('.nav-box-wrap').offset()||$()).top - $(window).height())+40) {
		$('.nav-box').removeClass('nav-box_float')
	} else if(!$('.nav-box').hasClass('nav-box_float')) {
		$('.nav-box').addClass('nav-box_float')
	}
});

// обрезает строку и возвращает её ставя в конце '...'
function hide_nav() {
	if(!$('.nav').hasClass('nav_hidden') && $(window).width() < 1024) {
		var $nav = $('.nav');

		if($(window).width() < 1023)
			$nav.addClass('nav_hidden');

		$nav.addClass('nav_mob').find('.nav__toggle').addClass('active')
		.parent().find('.fa-caret-left').hide();

		if($(window).width() > 744)
			$nav.find('.fa-caret-right').fadeIn('fast');

		var nav_width = ($(window).width() > 744) ? 62: 42;

		$nav.add('.nav-bg').css({width: nav_width})		
		.find('.nav__info').each(function() {
			$(this).css('margin-left', '-'+($(this).outerWidth()/2)+'px');
		});

		$('.nav__item span:not(.nav__info)').hide();
		$('.nav__heading').hide();

		$('.nav-box').css('left', $('.nav').outerWidth());

		$('.nav-box_compress').css({
			marginLeft: ($(window).width()-$('.nav').outerWidth())/2-153
		});
	}
};


// функция центрирует по высоте передоваемый ей элемент и показывает его
function centralize(elem) {
	if(elem.length) {
		elem.add('.ctr-overlay').fadeIn('fast');
		var diff = ($(window).height() - elem.outerHeight());

		if(diff < 0 ) diff = 20;

		var elem_top = $(document).scrollTop() + ( diff /2);

		if(elem.hasClass('login-form') && $(window).height() < 352)
			elem_top = 44

		elem.css('top', elem_top);
	}
}

function do_auto_width() {
	$('.do_auto_width').each(function() {
		var $this = $(this),
			$item_in = $(this).find('> .item > .item__in'),
			arr_width = [];

		for(var i=0; i < $item_in.eq(1).find('> *').length; i++) {
			arr_width.push(0)
		}
		$item_in.each(function() {
			$i = 0;
			$(this).find('> *').each(function() {
				if(arr_width[$i] < $(this).outerWidth()) {
					arr_width[$i] = $(this).outerWidth();
				}
				$i++;
			});
		});
		$item_in.each(function() {
			$i = 0;
			$(this).find('> *').each(function() {
				$(this).outerWidth(arr_width[$i]);
				$i++;
			});
		});

		$('.item__th',$this).each(function() {
			var $obj = $(this),
				index = $(this).index(),
				$sample = ($obj.closest('.list_pages').length) ?
							$('.list_pages .item .item:first-child', $this):
							$('.item:not(.item_heading)', $this);

			$obj.outerWidth($sample.find('.item__in > *').eq(index).outerWidth())
			.css({
					'padding-left': $sample.find('.item__in > *').eq(index).css('padding-left'),
					'padding-right': $sample.find('.item__in > *').eq(index).css('padding-right')
			});

			$(window).resize();
		});
	});
}