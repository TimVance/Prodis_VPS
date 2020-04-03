/**
 * JS-сценарий формы добавления объявления
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$('.js_ab_form, .ab_form').each(function() {
	if($(this).find('.js_ab_form_cat_ids, .ab_form_cat_ids select').length) {
		ab_select_form_cat_id($(this).find('form'), $(this).find('.js_ab_form_cat_ids, .ab_form_cat_ids select').val());
	}
	if($(this).find('.js_ab_form_site_ids, .ab_form_site_ids select').length) {
		ab_select_form_site_id($(this).find('form'), $(this).find('.js_ab_form_site_ids, .ab_form_site_ids select').val());
	}
});
$('.js_ab_form_cat_ids, .ab_form_cat_ids select').change(function() {
	ab_select_form_cat_id($(this).parents('form'), $(this).val());
});
$('.js_ab_form_site_ids, .ab_form_site_ids select').change(function() {
	ab_select_form_site_id($(this).parents('form'), $(this).val());
});

function ab_select_form_site_id(form, site_id) {
	form.attr('action', form.find('.js_ab_form_site_ids, .ab_form_site_ids select option:selected').attr('path'));
	if(! form.find('select[name=cat_id]').length) {
		return;
	}
	form.find('select[name=cat_id] option').hide();
	form.find('select[name=cat_id] option[site_id='+site_id+']').show();
	var cat_id = form.find('select[name=cat_id] option[site_id='+site_id+']').first().attr('value');
	form.find('select[name=cat_id]').val(cat_id);
	ab_select_form_cat_id(form, cat_id);
}
function ab_select_form_cat_id(form, cat_id) {
	form.find('.js_ab_form_param, .ab_form_param').each(function() {
		var cat_ids = $(this).attr('cat_ids').split(',');
		if(cat_ids == cat_id || cat_ids == 0 || $.inArray(0, cat_ids) > -1 || $.inArray(cat_id, cat_ids) > -1) {
			$(this).show();
		} else {
			$(this).hide();
		}
	});
}
