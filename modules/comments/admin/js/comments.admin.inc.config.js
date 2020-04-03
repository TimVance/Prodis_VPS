/**
 * Поле "Комментарии", JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$('input[name=cat]').attr("rel", $('input[name=cat]').attr("rel") + ',#comments_cat');
