/**
 * Редактирование пользователей, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

var admin_fields = "#hr3, #start_admin, #useradmin, #htmleditor, #copy_files";

$(document).on('change', "select[name=role_id]", show_param_role_rel);

$(document).ready(function() {
  show_param_role_rel();
});

function show_param_role_rel() {
  var role_id = $("select[name=role_id],input[name=role_id]").val();

  if(admin_roles.indexOf(role_id) == -1)
  {
      $(admin_fields).hide();
  }
  else
  {
      $(admin_fields).show();
  }

  $(param[0]).hide();
  $(param[role_id]).show();
}
