/**
 * JS-сценарий модуля
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', '.js_comments_show_form, .comments_show_form', function(){
	$(this).next('.js_comments_block_form, .comments_block_form').toggle();
});