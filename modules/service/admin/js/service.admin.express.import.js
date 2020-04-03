/**
 * Импорт/экспорт данных, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

// === Загрузка файла импорта - start === //

$('#import_upload_file').on('submit', function(event) {
	var self = $(this);
	if (self.attr("disable")) {
		return false;
	}
	self.attr("disable", "disable");
	$("input[name=file]", self).addClass("hide");

	var file_link = $("input[name=file_link]", self).val() || false;
	if (file_link || ! diafan_upload.is_support) {
		$("img.spinner_upload_file", self).removeClass("hide");
		return true;
	}

	event = event || window.event;
	event.preventDefault ? event.preventDefault() : (event.returnValue=false);
	var target = event.target || event.srcElement;

	diafan_upload.init({
    form: self,
    before: function() {
			$("input[name=file]", self).attr("disabled", "disabled");
			$("input[name=file_link]", self).attr("disabled", "disabled");
      $("input[name=delimiter]", self).attr("disabled", "disabled");
    	$("input[name=enclosure]", self).attr("disabled", "disabled");
    	$("select[name=encoding]", self).attr("disabled", "disabled");
		},
    after: function() {
			diafan_upload.button.attr("disabled", "disabled");
      $("img.spinner_upload_file", self).removeClass("hide");
    },
    error: function() {
			self.removeAttr('disable');
			$("input[name=file]", self).removeClass("hide");

      diafan_upload.button.removeAttr('disabled');
      $("input[name=file]", self).removeAttr('disabled');
			$("input[name=file_link]", self).removeAttr('disabled');
      $("input[name=delimiter]", self).removeAttr('disabled');
    	$("input[name=enclosure]", self).removeAttr('disabled');
    	$("select[name=encoding]", self).removeAttr('disabled');
			$("img.spinner_upload_file", self).addClass("hide");
    },
		success: function (response) { },
    complete: function () {
			location.reload(true);
    },
  });
  return false;
});

// === Загрузка файла импорта - end === //

// === Описание импорта в таблице - start === //

$(document).on('click', '.box .fields_express TR.row TD.col1 > .unit + i', function () {
  var table = $(this).closest("table"), owner = $(this).closest("tr"), th = owner.next("tr");
	if (th.length) {
		if (th.hasClass("hide")) {
      //table.find("tr.field").addClass("hide");
			//table.find("tr.row").removeClass("block_hover");
      table.find("tr.field").each(function() {
        if ($(this).find('.errors').length) return true;
        $(this).addClass("hide");
        $(this).prev("tr.row").removeClass("block_hover");
      });

			th.removeClass("hide");
			owner.addClass("block_hover");
		} else {
			th.addClass("hide");
			owner.removeClass("block_hover");
		}
	}
});

$(document).on('change', 'select[name=fields_cat_id]', function () {
	var self = $(this), element = this, form = $(this).closest('form');
	diafan_ajax.init({
		data:{
			action: self.attr('name') + '_import' + '_change',
			module: 'service',
			module_name: $("input[name=module_name]", form).val(),
			id: element.value,
			count: $('table.fields_express tr.field td').length
		},
		success: function(response) {
			if (response.result) {
				$.each(response.result, function (k, val) {
					switch (k) {
						case "type":
						case "site_id":
						case "cat_id":
						case "menu_cat_id":
							if (val == 0 || val == ''
							|| ! $('#'+k+' select[name='+k+'] option[value="'+prepare(val)+'"]').length) {
								var th = $('#'+k+' select[name='+k+']');
								if (th.length) {
									$('option', th).removeAttr("selected");
									$('option:nth-child(1)', th).attr("selected", "selected");
								}
							} else $('#'+k+' select[name='+k+']').val(prepare(val));
							$('#'+k+' select[name='+k+']').change();
							break;

						case "delete_items":
						case "add_new_items":
						case "act_items":
						case "header":
							val = (val == 0 ? false : true);
							$('#'+k+' input[name='+k+']').prop('checked', val);
							break;

						case "sub_delimiter":
            case "count_part":
							$('#'+k+' input[name='+k+']').val(val);
							break;

						default:
							break;
					}
				});
			}
			if (response.table) {
				var rows = response.table.rows || [],
						fields = response.table.fields || [];
				$('table.fields_express tr.row td.col1').each(function(index, element) {
					if (index in rows) $(element).html(prepare(rows[index]));
					else $(element).html('');
				});
				$('table.fields_express tr.field td').each(function(index, element) {
					if (index in rows) $(element).html(prepare(fields[index]));
					else $(element).html('');
				});
			} else {
				$('table.fields_express tr.row td.col1').each(function() {
					$(this).html('');
				});
				$('table.fields_express tr.field td').each(function() {
					$(this).html('');
				});
			}
			if (response.result || response.table) {
				check_type_cat();
				check_cell();
        express_import.info_reset();
			}
			if (response.curLoc)
      {
        var curLoc = prepare(response.curLoc);
        $("#mode_express a.tabs__item.tabs__item_active").attr('href', curLoc);
      }
		}
	});
	return false;
});

$(document).on('change', "input[name='header']", check_cell);
function check_cell()
{
	var th = $('table.fields_express tr.row td:nth-child(2)'),
			checked = $('#header input[name="header"]').prop("checked") || false;
	if (! th.length) {
		return;
	}
	if (checked) {
		th.addClass('cell_disable');
	} else {
		th.removeClass('cell_disable');
	}
}
check_cell();

$(document).on('change', 'table.fields_express tr.field td input[name^="name["]', function() {
	var th = $(this).closest('tr.field').prev('tr.row').find('td.col1').eq(0);
	if (! th.length) {
		return;
	}
	var row_name = th.find('.row_name').eq(0);
	if (! row_name.length) th.prepend('<span class="row_name">' + $(this).val() + '</span> ');
	else row_name.html($(this).val());
});

(function( $ ) {
	var previous;
	$(document).on('focus', 'table.fields_express tr.row td.col1 select[name^="type["]', function() {
		previous = this.value;
	}).on('change', 'table.fields_express tr.row td.col1 select[name^="type["]', function() {
		$(this).trigger('refresh', [ previous, this.value ]);
		previous = this.value;
	});
})( jQuery );
$(document).on('refresh', 'table.fields_express tr.row td.col1 select[name^="type["]', function(event, previous, value) {
	if (previous == value) {
		return;
	}
	if(previous == 'param' || value == 'param') {
		return;
	}
	var th = $(this).closest('tr.row').next('tr.field').eq(0);
	if (! th.length) {
		return;
	}
	var name = th.find('td input[name^="name["]').eq(0);
	if (! name.length) {
		return;
	}
	var prev_text = $(this).find('option[value="'+previous+'"]').text();
	if (name.val() == '' || name.val() == prev_text) {
		name.val($(this).find("option:selected").text()).change();
	}
});

$(document).on('change', 'table.fields_express tr.row td.col1 select[name^="type["]', function() {
  $(this).closest('tr.row').next('tr.field').find('.errors').remove();
});

$(document).on('change', 'table.fields_express tr.row td.col1 select[name^="type["]', function () {
	if ( $(this).val() == 'empty' ) {
		return;
	}
  var owner = $(this).closest('tr.row');
  if (owner.length) {
    var th = owner.next("tr");
    if (th.length && th.hasClass("hide")) {
      $('TD.col1 > .unit + i', owner).trigger('click');
    }
  }
});

// === Описание импорта в таблице - end === //

// === Поле описания импорта в таблице - start === //

$(document).on('change', '#type select[name=type]', check_type_cat); // select[name=cat_id]
$(document).on('change', 'table.fields_express tr.row td.col1 select[name^="type["]', check_type);
$(document).on('change', 'table.fields_express tr.field td select[name^="param_id["]', check_param);
$(document).ready(function() { check_type_cat(); });

function check_type_cat() {
	var type = false;
	if (this === window) {
		type = $('#type select[name=type] option:selected').attr('value'); // type_cat
	} else {
		// $('#type select[name=type]').change();
		type = $(this).val();  // type_cat
	}

	if (! type) {
		return;
	}
	var th = $('table.fields_express').eq(0);
	if (! th.length) {
		return;
	}
	$('select[name^="type["] option', th).each(function() {
		if ($(this).attr(type)) {
			$(this).show();
		} else {
			$(this).hide();
		}
	});
	if (type == 'element') {
		$('select[name^="param_type["] option[value=article]', th).show();
	} else {
		$('select[name^="param_type["] option[value=article]', th).hide();
	}
	check_type();
}
function check_type() {
	if (this === window)
	{ // полный перебор объектов
		var th = $('table.fields_express').eq(0);
		if (! th.length) {
			return;
		}
		$('tr.row td.col1 select[name^="type["]', th).each(function() {
			var fld = $(this).closest('tr.row').next('tr.field').find('td').eq(0);
			if (fld.length) {
				$('.params', fld).hide();
				$('.param_'+$(this).val(), fld).show();
			}
		});
	} else {
		// обработка только инициирующего событие объекта
		// $('table.fields_express tr.row td.col1 select[name^="type["]').change();
		var fld = $(this).closest('tr.row').next('tr.field').find('td').eq(0);
		if (fld.length) {
			$('.params', fld).hide();
			$('.param_'+$(this).val(), fld).show();
		}
	}
	check_param();
}
function check_param() {
	if (this === window)
	{ // полный перебор объектов
		var th = $('table.fields_express').eq(0);
		if (! th.length) {
			return;
		}
		$('tr.field td', th).each(function() {
			var fld = $(this);
			var row = fld.closest('tr.field').prev('tr.row').find('td.col1').eq(0);
			if (! row.length) {
				return;
			}
			if ($('select[name^="type["]', row).val() == 'param' && ($('select[name^="param_id["] option:selected', fld).attr("type") == 'select' || $('select[name^="param_id["] option:selected', fld).attr("type") == 'multiple')) {
				$('[unit_id=param_select_type]', fld).show();
			} else {
				$('[unit_id=param_select_type]', fld).hide();
			}
			if ($('select[name^="type["]', row).val() == 'param' && ($('select[name^="param_id["] option:selected', fld).attr("type") == 'images' || $('select[name^="param_id["] option:selected', fld).attr("type") == 'attachments') || $('select[name^="type["]', row).val() == 'images') {
				$('[unit_id=param_directory]', fld).show();
			} else {
				$('[unit_id=param_directory]', fld).hide();
			}
		});
	} else {
		// обработка только инициирующего событие объекта
		// $('table.fields_express tr.field td select[name^="param_id["]').change();
		var fld = $(this).closest('td');
		if (! fld.length) {
			return;
		}
		var row = fld.closest('tr.field').prev('tr.row').find('td.col1').eq(0);
		if (! row.length) {
			return;
		}
		if ($('select[name^="type["]', row).val() == 'param' && ($('select[name^="param_id["] option:selected', fld).attr("type") == 'select' || $('select[name^="param_id["] option:selected', fld).attr("type") == 'multiple')) {
			$('[unit_id=param_select_type]', fld).show();
		} else {
			$('[unit_id=param_select_type]', fld).hide();
		}
		if ($('select[name^="type["]', row).val() == 'param' && ($('select[name^="param_id["] option:selected', fld).attr("type") == 'images' || $('select[name^="param_id["] option:selected', fld).attr("type") == 'attachments') || $('select[name^="type["]', row).val() == 'images') {
			$('[unit_id=param_directory]', fld).show();
		} else {
			$('[unit_id=param_directory]', fld).hide();
		}
	}
}

// === Поле описания импорта в таблице - end === //

// === Импорт - start === //

var express_import = {
	self: {},
	init: function() {
		$(function() {
			$(document).on('click', 'a.action', function () {
				var self = $(this);
				if (! self.attr("action"))
				{
					return true;
				}
				if (self.attr("confirm") && ! confirm(self.attr("confirm")))
				{
					return false;
				}
				return true;
			});
		});
		express_import.self = $('#express_button');
		$(document).on('click', '#express_button, #express_save_button', express_import.prepare_import);
	},
	errors_reset: function() { $('table.fields_express').find('.errors').remove(); },
	info_reset: function(params)
	{
		params = params || ["request", "button"];
		if (diafan_action.inArray(params, "request") > -1)
		{
			$('#express_form_request').contents().remove();
		}
		if (diafan_action.inArray(params, "button") > -1)
		{
			if ($('#express_button').attr('default'))
			{ $('#express_button').text($('#express_button').attr('default')); }
		}
	},
	prepare_import: function() {
		if (express_import.self.attr('disabled')) {
			return false;
		}
		var form = $('#form_express_import');
		if (! form.length) {
			return false;
		}
		if (! $(this).attr("action")) {
			return false;
		}
		express_import.self.attr('disabled', 'disabled');

		$("#express_save_button").addClass("hide");

		var spinner_express = express_import.self.next("img.spinner_express");
		if (spinner_express.length) spinner_express.removeClass("hide");

		var data = form.serializeObject();
		data["category"] = $('#form_express_import_category').serializeObject();
		data["request"] = $('#express_form_request').serializeObject();
		if ($(this).attr("action") == "only_save") data["only_save"] = '1';
		diafan_action.init({
			self: express_import.self,
			informer: { reset: false },
			config: { data: data },
			response: function(response) {
				if (spinner_express.length) spinner_express.addClass("hide");
				express_import.errors_reset();
				if (response.request) {
	        express_import.info_reset(["request"]);
	        $("#express_form_request").append(prepare(response.request));
	      } else express_import.info_reset(["request"]);
	      if (response.button) {
	        $('#express_button').text(prepare(response.button));
	      } else express_import.info_reset(["button"]);
				if (response.only_save) {
	        $('#express_button').attr("action", "only_save");
	      } else $('#express_button').attr("action", "import");
	  		if (response.redirect) {
	  			window.location = prepare(response.redirect);
	  		}
	      if (response.category) {
	        $("#fields_cat_id").replaceWith(prepare(response.category));
	  		}
				return true;
			},
			error: function(response) {
				// TO_DO: admin.edit.js - response.errors
				if (response.errors) {
		      var focus = false;
					var other = false;
					$.each(response.errors, function (k, val) {
		        if (k) {
		          if (typeof(val) == 'object') {
		            $.each(val, function (key, value) {
		              var th = form.find('[unit_id="'+k+'"]').eq(key);
		              if (th.length)
		              {
		                var field = th.after('<div class="errors error">' + prepare(value) + '</div>')
		                  .closest('tr.field ').removeClass('hide');
		                if (! focus && field.length) {
		                  focus = true;
		                  $('html, body').scrollTop((field||$()).offset().top - (field.prev('tr.row')||$()).outerHeight(true) - 40);
		                }
		              } else $(".content").after('<div class="errors error">' + prepare(value) + '</div>');
		            });
		          } else {
		            if (! other) {
		  						if($("#"+k).parents('.content__right_supp').length && parseInt($('.content__right_supp').css('right')) < 0)
		  						{
		  							$('.btn_supp').click();
		  							other = true;
		  						}
		  					}
		  					$("#"+k).after('<div class="errors error">' + prepare(val) + '</div>');
		  					if (! focus) {
		  						$("#"+k).find("input, submit, textarea").first().focus();
		  						focus = true;
		  					}
		          }
		        } else {
		          if (typeof(val) == 'object') {
		            $.each(val, function (key, value) {
		              $(".content").after('<div class="errors error">' + prepare(value) + '</div>');
		            });
		          } else {
		            $(".content").after('<div class="errors error">' + prepare(val) + '</div>');
		          }
		        }
					});
				}
				diafan_action.self.removeAttr('disabled');
				if (response.only_save) {
	        $("#express_save_button").addClass("hide");
	      } else $("#express_save_button").removeClass("hide");
			},
			success: function(response) {
				diafan_action.self.removeAttr('disabled');
				if (response.only_save) {
	        $("#express_save_button").addClass("hide");
	      } else $("#express_save_button").removeClass("hide");
			},
			continue: function(response) {
				if (! response.action) {
					diafan_action.self.removeAttr('disabled');
					return false;
				}
				if (response.action == 'defer_files') {
					express_import.defer_files();
	  		}
	      if (response.action == 'import_files') {
					express_import.import_files();
	      }
				return false;
			},
		});
	},
	defer_files: function() {
		var data = $("#csv_param").serializeObject();
		data["action"] = 'load_defer_files';
		data["module"] = 'service';
		diafan_action.init({
			self: express_import.self,
			informer: { reset: false },
			config: {
				data: data,
				timeout: function (data, timeout) {
					var confirm_title = (window.CONFIRM_IMPORT_FILES || '');
					if (confirm(confirm_title)) {
						express_import.defer_files();
					}
				}
			},
			success: function(response) {
				diafan_action.self.removeAttr('disabled');
				$("#express_save_button").removeClass("hide");
			},
			error: function(response) {
				diafan_action.self.removeAttr('disabled');
				$("#express_save_button").removeClass("hide");
			},
			continue: function(response) {
				if (response.action == 'defer_files') {
					express_import.defer_files();
	  		}
				if (response.action == 'import_files') {
					express_import.import_files();
				}
				return false;
			},
		});
	},
	import_files: function () {
		diafan_action.init({
			self: express_import.self,
			informer: { reset: false },
			config: {
				data: {
					action: 'import_files',
					module: 'service',
					cat_id: $('select[name=fields_cat_id]', $('#form_express_import_category')).val()
				},
				timeout: function (data, timeout) {
					var confirm_title = (window.CONFIRM_IMPORT_FILES || '');
					if (confirm(confirm_title)) {
						express_import.import_files();
					}
				}
			},
			success: function(response) {
				$('html, body').scrollTop(($('#import_init')||$()).offset().top - ($('#import_description')||$()).outerHeight(true) - 40);
				$('#import_description').remove();

				diafan_action.timerId = setTimeout(function() {
					$('#file_errors_log').clicker();
				}, express_import.timeout);

				diafan_action.self.removeAttr('disabled').remove();
				$("#express_save_button").removeClass("hide").remove();
			},
			error: function(response) {
				diafan_action.self.removeAttr('disabled');
				$("#express_save_button").removeClass("hide");
			}
		});
	}
}
$(document).on('diafan.ready', function() {
	express_import.init();
});

// === Импорт - end === //

// === Операции с запясями после импорта - start === //

$(document).on('click', '.import_button', function() {
 	var value = $(this).attr('rel');
 	$('input[name="import_action"]').val(value);
});

// === Операции с запясями после импорта - end === //

// === Общее - start === //

$(document).on('click', '.box_toggle i', function(event) {
	var unit_id = $(this).closest(".box_toggle").attr("unit_id");
	if (! unit_id) {
		return false;
	}
	$("#"+unit_id).toggleClass("hide");
});

var diafan_upload = {
  data: false,
  url: false,
  before: false,
  after: false,
  success: false,
  error: false,
  complete: false,
  form: {},
  button: {},
  progressBar: {},
  progressLine: {},
  has_progressBar: false,
  has_progressLine: false,
  onSubmitBefore: function(form) {},
  onSubmitAfter: function(form) {},
  onSubmitError: function(form, errorThrown) {},
  is_support: !! window.FormData,

	init: function(config) {
    if (! this.is_support) {
      return true;
    }
    if (config.form) {
      this.form = config.form;
      if(! this.form.length) return true;
    }
    if (config.data) {
      this.data = config.data;
    } else {
      this.data = new FormData(this.form.get(0));
      var ajax = false;
      for (var pair of this.data.entries()) {
        if (pair[0] != 'ajax' || ! pair[1]) continue;
        ajax = true;
      }
      if (! ajax) this.data.append('ajax', '1');
    }
    if (config.url) this.url = config.url;
    else {
      if(this.form.attr('action')) this.url = this.form.attr('action');
      else this.url = window.location.href;
    }
    if (config.before) this.before = config.before;
    else this.before = (function(form) {});
    if (config.success) this.success = config.success;
    else this.success = (function(response, form) {});
    if (config.after) this.after = config.after;
    else this.after = (function(form) {});
    if (config.error) this.error = config.error;
    else this.error = (function(form) {});
    if (config.complete) this.complete = config.complete;
    else this.complete = (function(form) {});
    this.data.check_hash_user = $('.check_hash_user').text();

    this.button = $('input:submit, button', this.form).eq(0);
    if (! this.button.length) return true;
    this.progressBar = $('.progressbar', this.form).eq(0);
    if (! this.progressBar.length) {
      this.has_progressBar = false;
      this.button.after('<div class="progressbar hide"></div>');
      this.progressBar = $('.progressbar', this.form).eq(0);
    } else this.has_progressBar = true;
    this.progressLine = $('.line', this.progressBar).eq(0);
    if (! this.progressLine.length) {
      this.has_progressLine = false;
      this.progressBar = this.progressBar.append('<div class="line"></div>');
      this.progressLine = $('.line', this.progressBar).eq(0);
    } else this.has_progressLine = true;

    if (this.before(this.form) === false) {
      return false;
    }
    $(this.form).trigger('ajax_submit.before', [ this.form ]);
    this.onSubmitBefore(this.form);

    this.button.attr('disabled', 'disabled');
    this.progressLine.css("width", 0+"%");
    this.progressBar.removeClass('hide');
    return $.ajax({
      url: this.url,
      type: this.form.attr('method') || 'POST',
      contentType: false,
      processData: false,
      data: this.data,
      dataType: 'json',
      xhr: function() {
        var xhr = $.ajaxSettings.xhr(); // получаем объект XMLHttpRequest
        xhr.upload.addEventListener('progress', function(evt) { // добавляем обработчик события progress (onprogress)
          if (evt.lengthComputable) { // если известно количество байт
            // высчитываем процент загруженного
            var percentComplete = Math.ceil(evt.loaded / evt.total * 100);
            // устанавливаем значение в progress
            diafan_upload.progressLine.css("width", percentComplete+"%");
          }
        }, false);
        return xhr;
      },
      success: function(response, statusText, xhr, form) {
        diafan_upload.button.removeAttr('disabled');
        if (diafan_upload.after(this.form) === false) {
          return false;
        }
        $(document).trigger('ajax_submit.after', [ form ]);
        if (response.redirect) {
          window.location = prepare(response.redirect);
        }
        if (response.hash) {
          $('input[name=check_hash_user]').val(response.hash);
          $('.check_hash_user').text(response.hash);
        }
        diafan_upload.success(response, form);
      },
      error: function(xhr, statusText, errorThrown) {
				diafan_upload.progressBar.addClass('hide');
        diafan_upload.progressLine.css("width", 0+"%");
        if (! diafan_upload.has_progressLine) diafan_upload.progressLine.remove();
        if (! diafan_upload.has_progressBar) diafan_upload.progressBar.remove();
        if (diafan_upload.error(diafan_upload.form) === false) {
          return false;
        }
        $(document).trigger('ajax_submit.error', [ diafan_upload.form, errorThrown ]);
        // TO_DO: errorThrown = (statusText === 'timeout' ? 'timeout' : 'aborted');
        diafan_upload.onSubmitError(diafan_upload.form, errorThrown);
      },
      complete: function(xhr, statusText) {
        diafan_upload.progressBar.addClass('hide');
        diafan_upload.progressLine.css("width", 0+"%");
        if (! diafan_upload.has_progressLine) diafan_upload.progressLine.remove();
        if (! diafan_upload.has_progressBar) diafan_upload.progressBar.remove();
        if (diafan_upload.complete(diafan_upload.form) === false) {
          return false;
        }
      }
    });
    return false;
  }
};

// === Общее - end === //
