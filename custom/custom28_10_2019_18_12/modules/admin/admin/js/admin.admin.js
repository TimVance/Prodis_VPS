/**
 * Редактирование страниц административной части сайта, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('change', 'select[name=parent_id]', function(){
	if($(this).val()) {
		$('#group_id').hide();
	} else {
		$('#group_id').show();
	}
});
$(document).ready(function() {
	if($('select[name=parent_id]').val()) {
		$('#group_id').hide();
	}
});
