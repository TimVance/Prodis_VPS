/**
 * JS-сценарий формы редактирования объявления
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$('.js_ab_form, .ab_form').each(function() {
	if($(this).find('.js_ab_form_cat_ids, .ab_form_cat_ids select').length) {
		ab_select_form_cat_id($(this), $(this).find('.js_ab_form_cat_ids, .ab_form_cat_ids select').val());
	}
});
$('.js_ab_form_cat_ids, .ab_form_cat_ids select').change(function() {
	ab_select_form_cat_id($(this).parents('form'), $(this).val());
});

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
