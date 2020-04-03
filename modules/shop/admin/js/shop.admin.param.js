/**
 * Редактирование дополнительных характеристик товаров, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).ready(function() { param_select_site_id(); $(".settmd-link").hide(); });
$('select[name=site_id]').change(param_select_site_id);

$('#attachments_access_admin').remove();

function param_select_site_id() {
	if(! $('select[name=site_id]').length) {
		return;
	}
	var val = $('select[name=site_id]').val();
	if(val && val != 0) {
		$("select[name='cat_ids[]'] optgroup").hide();
		$("select[name='cat_ids[]'] optgroup[data-site_id="+val+"]").show();
	} else {
		$("select[name='cat_ids[]'] optgroup").show();
	}
}