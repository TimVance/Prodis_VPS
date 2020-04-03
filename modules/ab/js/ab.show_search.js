/**
 * JS-сценарий формы поиска по объявлениям
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

diafan_ajax.before['ab_search'] = function(form) {
	if(! $(".ab_list").length) {
		$(form).removeClass('ajax').submit();
		return false;
	}
	$(form).attr('method', 'POST');
}

diafan_ajax.success['ab_search'] = function(form, response) {
	$(".ab_list").text('');
	$(".ab_list").first().html(prepare(response.data)).focus();
	if (response.js) {
		$.each(response.js, function (k, val) {
			if(val) {
				if (val['src']) val['src'] = prepare(val['src']);
				if (val['func']) val['func'] = prepare(val['func']);
				diafan_ajax['manager'].addScript(val['src'], val['func']);
			}
		});
	}
	return false;
}

$('.ab_search form').each(function() {
	if($(this).find('.ab_search_cat_ids select').length) {
		ab_select_search_cat_id($(this), $(this).find('.ab_search_cat_ids select').val());
	}
	if($(this).find('.ab_search_site_ids select').length) {
		ab_select_search_site_id($(this), $(this).find('.ab_search_site_ids select').val());
	}
});
$('.ab_search_cat_ids select').change(function() {
	ab_select_search_cat_id($(this).parents('form'), $(this).val());
});
$('.ab_search_site_ids select').change(function() {
	ab_select_search_site_id($(this).parents('form'), $(this).val());
});

function ab_select_search_site_id(form, site_id) {
	form.attr('action', form.find('.ab_search_site_ids select option:selected').attr('path'));
	if(! form.find('select[name=cat_id]').length) {
		return;
	}
	var current_cat_id = form.find('select[name=cat_id] option:selected');
	if(current_cat_id.attr('site_id') != site_id) {
		form.find('select[name=cat_id] option').hide();
		form.find('select[name=cat_id] option[site_id='+site_id+']').show();
		var cat_id = form.find('select[name=cat_id] option[site_id='+site_id+']').first().attr('value');
		form.find('select[name=cat_id]').val(cat_id);
		ab_select_search_cat_id(form, cat_id);
	}
}
function ab_select_search_cat_id(form, cat_id) {
	form.find('.ab_search_param').each(function() {
		var cat_ids = $(this).attr('cat_ids').split(',');
		if(cat_ids == cat_id || cat_ids == 0 || $.inArray(0, cat_ids) > -1 || $.inArray(cat_id, cat_ids) > -1) {
			$(this).show();
		} else {
			$(this).hide();
		}
	});
}
