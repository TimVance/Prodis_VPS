/**
 * Редактирование заказов, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
 */

var timeout = 120000;
var search;
var cat_id;

$("select[name=status]").change(function() {
	if ($(this).val() == "all") {
		$(this).attr("name", "");
	}
	$(this).parents("form").submit();
})
$('.order_good_plus').click(function() {
	var self = $(this);
	var new_goods = [];
	$('.js_order_new_good').each(function() {
		new_goods.push($(this).attr('good_id'));
	});
	diafan_ajax.init({
		data:{
			action: 'show_order_goods',
			module: 'order',
			order_id: $('input[name=id]').val(),
			new_goods: new_goods,
		},
		success: function(response) {
			if (response.data) {
				$("#ipopup").html(prepare(response.data));
				centralize($("#ipopup"));
			}
		}
	});
	return false;
});
$(document).on('click', '.order_good_show_price', function() {
	$(this).next('.order_good_all_price').show();
	return false;
});
$(document).on('click', '.order_good_price_close', function() {
	$(this).parents('.order_good_all_price').hide();
	return false;
});

$(document).on('click', '.order_goods_navig a', function() {
	var self = $(this);
	diafan_ajax.init({
		data:{
			action: 'show_order_goods',
			module: 'order',
			order_id: $('input[name=id]').val(),
			page: self.attr("page"),
			search: search,
			cat_id: cat_id
		},
		success: function(response) {
			if (response.data) {
				$(".order_all_goods_container").html(prepare(response.data));
			}
		}
	});
	return false;
});
$(document).on('keyup', '.order_goods_search', search_goods_order);
$(document).on('change', '.order_goods_cat_id', search_goods_order);
$(document).on('click', '.order_good_add', function() {
	var self = $(this);
	diafan_ajax.init({
		data:{
			action: 'add_order_good',
			module: 'order',
			order_id: $('input[name=id]').val(),
			price_id: self.attr("price_id"),
			good_id: self.attr("good_id")
		},
		success: function(response) {
			if (response.data) {
				$(".order_good_plus").parents('.item').before(prepare(response.data));
			}
			$('.ipopup__close').click();
		}
	});
	return false;
});
$(document).on('click', ".delete_order_good", function() {
	var self = $(this);
	if (! confirm(self.attr("confirm"))) {
		return false;
	}
	$(this).parents('.item').remove();
	return false;
});

$(document).ready(function() {
	setTimeout("check_new_order()", timeout);

	do_auto_width();
});

function check_new_order() {
	diafan_ajax.init({
		data:{
			action: 'new_order',
			module: 'order',
			last_order_id: last_order_id
		},
		success: function(response) {
			if (response.next_order_id != false) {
				title_new_order();
			} else {
				setTimeout('check_new_order()', timeout ? timeout : 120000);
			}
		}
	});
}
function search_goods_order() {
	if($(this).is('.order_goods_search')) {
		search = $(this).val();
	}
	if($(this).is('.order_goods_cat_id')) {
		cat_id = $(this).val();
	}
	diafan_ajax.init({
		data:{
			action: 'show_order_goods',
			module: 'order',
			order_id: $('input[name=id]').val(),
			search: search,
			cat_id: cat_id
		},
		success: function(response) {
			if (response.data) {
				$(".order_all_goods_container").html(prepare(response.data));
			}
		}
	});
}
function title_new_order() {
	var new_title  = '****************************************';
	if($('title').text() == new_title) {
		$('title').text(title);
	} else {
		$('title').text(new_title);
	}
	setTimeout('title_new_order()', 360);
}
