/**
 * Услуги, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$('input[name=price]').blur(function() {
	if($(this).val()) {
		$('input[name=percent]').val('');
	}
	discount_info();
}).focus(function() {
	$('input[name=percent]').addClass('item_disable');
	$(this).removeClass('item_disable');
});

$('input[name=percent]').blur(function() {
	if($(this).val()) {
		$('input[name=price]').val('');
	}
	discount_info();
}).focus(function() {
	$('input[name=price]').addClass('item_disable');
	$(this).removeClass('item_disable');
});

$(document).on('click', "#input_shop_rel_order_label", function() {
	$('#category').addClass('hidden_block');
});

$(document).on('click', "#input_shop_rel_label", function() {
	$('#category').removeClass('hidden_block');
});
