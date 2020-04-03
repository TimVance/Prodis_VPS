/**
 * Редактирование файлов, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$('input[name=file_type]').change(function () {
	$('.file_type1').hide();
	$('.file_type2').hide();
	$('.file_type3').hide();
	$('.file_type' + $(this).val()).show();
});
$(".attachment_delete").click(function() {
	if(! $(this).parents('.attachment').length)
	{
		return false;
	}
	if (! confirm($(this).attr("confirm")))
	{
		return false;
	}
	$(this).parents('.attachment').find("input[name='hide_attachment_delete[]']").attr("name", "attachment_delete[]");
	$(this).parents('.attachment').find("input[name='hide_link_delete[]']").attr("name", "link_delete[]");
	$(this).parents('.attachment').removeClass('attachment').hide();
	return false;
});
