/**
 * Импорт/экспорт данных, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', ".box_toggle i", function(event) {
  var unit_id = $(this).closest(".box_toggle").attr("unit_id");
  if(! unit_id)
  {
  	return false;
  }
  $("#"+unit_id).toggleClass("hide");
});

$(document).on('change', 'select[name=modules]', function () {
  var self = $(this), element = this, form = $(this).closest('form');
  diafan_ajax.init({
    data:{
     action: self.attr('name') + '_export' + '_change',
     module: 'service',
     module_name: element.value
   },
   success: function(response) {
     if (response.result) {
       $.each(response.result, function (k, val) {
         $("#"+k).replaceWith(prepare(val));
       });
     }
     if (response.curLoc) {
       var curLoc = prepare(response.curLoc);
       $("#mode_express a.tabs__item.tabs__item_active").attr('href', curLoc);
     }
     if ($("#fields_cat_id .error").length && ! $("#fields_cat_param").hasClass("hide")) {
       $("#fields_cat_param").addClass("hide");
     }
   }
 });
 return false;
});

$(document).on('change', 'select[name=fields_cat_id]', function () {
	var self = $(this), element = this, form = $(this).closest('form');
	diafan_ajax.init({
		data:{
			action: self.attr('name') + '_export' + '_change',
			module: 'service',
			module_name: $("select[name=modules]", form).val(),
			id: element.value
		},
		success: function(response) {
			if (response.curLoc) {
        var curLoc = prepare(response.curLoc);
        $("#mode_express a.tabs__item.tabs__item_active").attr('href', curLoc);
      }
      if (response.href) {
        var href = prepare(response.href);
        $("#fields_cat_edit").attr('href', href).show();
      }
		}
	});
	return false;
});

$(document).on('click', '#express_button', function() {
  var self = $(this);
	if($(this).attr('disabled')) {
		return false;
	}
	$(this).attr('disabled', 'disabled');
  $("#fields_cat_param input[name=delimiter]").attr("disabled", "disabled");
  $("#fields_cat_param input[name=enclosure]").attr("disabled", "disabled");
  $("#fields_cat_param select[name=encoding]").attr("disabled", "disabled");

  var form = $('#form_express_export');
  if(! form.length) {
    return false;
  }
  var data = form.serializeObject();
  data["action"] = 'export_data';
  data["module"] = 'service';

	diafan_action.init({
		self: self,
		config: {
			data: data
		},
		success: function(response) {
			diafan_action.timerId = setTimeout(function() {
        $('#file_export').clicker();
      }, diafan_action.timeout);
      $('#express_button').removeAttr('disabled');
      $("#fields_cat_param input[name=delimiter]").removeAttr('disabled');
      $("#fields_cat_param input[name=enclosure]").removeAttr('disabled');
      $("#fields_cat_param select[name=encoding]").removeAttr('disabled');
      return true;
		},
		error: function(response) {
			$('#express_button').removeAttr('disabled');
      $("#fields_cat_param input[name=delimiter]").removeAttr('disabled');
      $("#fields_cat_param input[name=enclosure]").removeAttr('disabled');
      $("#fields_cat_param select[name=encoding]").removeAttr('disabled');
			return true;
		}
	});
});

$(document).ready(function() {
  var anchor = window.location.hash || document.location.hash;
  anchor = anchor.replace("#","");
  if (anchor == 'export') {
    try {
			history.pushState('', document.title, window.location.pathname);
		} catch(e) {
      window.location = window.location.protocol + "//" + window.location.hostname
        + window.location.pathname + window.location.search;
    }
    $('#express_button').clicker();
  }
});
