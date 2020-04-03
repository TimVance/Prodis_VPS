/**
 * JS-сценарий модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

var rating = 0;
if ((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))){ 
	$(document).on('touchstart', '.js_rating_votes_item, .rating_votes img', function() {
		var self = $(this).parents('.js_rating_votes, .rating_votes');
		if(self.attr("disabled") == "disabled") {
			return false;
		}
		rating = 0;
		var plus = true;
		$(this).attr("current", "true");
		self.find('img').each(function() {
			if(plus) {
				rating = rating + 1;
				$(this).attr("src", $(this).attr("src").replace("rminus", "rplus"));
				$(this).attr("alt", "+");
			} else {
				$(this).attr("src", $(this).attr("src").replace("rplus", "rminus"));
				$(this).attr("alt", "-");
			}
			if($(this).attr("current") == "true") {
				plus = false;
			}
		});
		$(this).attr("current", "false");
		$.ajax({
			data: {
				module : "rating",
				element_id: self.attr("element_id"),
				module_name: self.attr("module_name"),
				element_type: self.attr("element_type"),
				action: 'add',
				rating: rating
			},
			type : 'POST'
		});
		self.attr("disabled", "disabled");
		return false;
});
}
else
{
	$(document).on('click', '.js_rating_votes', function(){
		if($(this).attr("disabled") == "disabled") {
			return false;
		}
		$.ajax({
			data: {
				module : "rating",
				element_id: $(this).attr("element_id"),
				module_name: $(this).attr("module_name"),
				element_type: $(this).attr("element_type"),
				action: 'add',
				rating: rating
			},
			type : 'POST'
		});
		$(this).attr("disabled", "disabled");
		return false;
	});
	$(document).on('mouseover', '.js_rating_votes_item, .rating_votes img', function() {
		if($(this).parents('.js_rating_votes, .rating_votes').attr("disabled") == "disabled") {
			return false;
		}
		if(! $(this).parents('.js_rating_votes, .rating_votes').next('.js_rating_votes_hide').length) {
			$(this).parents('.js_rating_votes, .rating_votes').after('<span class="js_rating_votes_hide">'+$(this).parents('.js_rating_votes, .rating_votes').html()+'</span>');
			$(this).parents('.js_rating_votes, .rating_votes').next('.js_rating_votes_hide').hide();
		}
		rating = 0;
		var plus = true;
		$(this).attr("current", "true");
		$(this).parents('.js_rating_votes, .rating_votes').find('img').each(function() {
			if(plus) {
				rating = rating + 1;
				$(this).attr("src", $(this).attr("src").replace("rminus", "rplus"));
				$(this).attr("alt", "+");
			} else {
				$(this).attr("src", $(this).attr("src").replace("rplus", "rminus"));
				$(this).attr("alt", "-");
			}
			if($(this).attr("current") == "true") {
				plus = false;
			}
		});
		$(this).attr("current", "false");
	});
}
$(document).on('mouseout', '.js_rating_votes, .rating_votes', function() {
	if($(this).attr("disabled") == "disabled") {
		return false;
	}
	if($(this).next('.js_rating_votes_hide').length) {
		$(this).html($(this).next('.js_rating_votes_hide').html());
	}
});
