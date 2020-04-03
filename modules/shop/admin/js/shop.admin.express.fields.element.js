/**
 * Импорт/экспорт данных, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

$(document).on('click', '.box_refresh i', function(event) {
  var self = $(this),
      that = self.closest('.box_refresh');
  if (self.attr('disabled')) {
    return;
  }
  self.attr('disabled', 'disabled');
  diafan_ajax.init({
    data: {
      action: 'table_params_refresh',
      module: 'shop',
      id: that.attr("field_id") || 0
    },
    success: function(response) {
      if(response.result) {
        //that.replaceWith(prepare(response.result));
        that.html(prepare(response.result));
        var selects = that.find('select');
        if (selects.length) {
          selects.each(function () {
            if (! $(this).attr("name")) {
              return true;
            }
            var content = $(this).html();
            $('.fields_express .field .box_refresh select[name="'+$(this).attr("name")+'"]').each(function () {
              var select = $(this).val();
              $(this).html(content);
              $(this).val(select);
            });
          });
        }
      }
    }
  });
  return false;
});

$(document).on('refresh', 'table.fields_express tr.row td.col1 select[name^="type["]', function(event, previous, value) {
  if (previous == value) {
		return;
	}
	if (previous != 'param' && value != 'param') {
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
  var param = th.find('td select[name^="param_id["]').eq(0),
      param_text = '';
  if (! param.length) {
		if (previous == 'param') {
      param_text = $(this).find('option[value="'+previous+'"]').text();
    }
    if (value == 'param') {
      param_text = $(this).find('option[value="'+value+'"]').text();
    }
	} else {
    param_text = param.find("option:selected").text();
  }
  var prev_text = '';
  if (previous == 'param') {
    prev_text = param_text;
  } else {
    prev_text = $(this).find('option[value="'+previous+'"]').text();
  }
	if (name.val() == '' || name.val() == prev_text) {
    if (value == 'param') {
      name.val(param_text).change();
    } else {
	    name.val($(this).find("option:selected").text()).change();
    }
	}
});

(function( $ ) {
	var previous;
	$(document).on('focus', 'table.fields_express tr.field td select[name^="param_id["]', function() {
		previous = this.value;
	}).on('change', 'table.fields_express tr.field td select[name^="param_id["]', function() {
		$(this).trigger('refresh', [ previous, this.value ]);
		previous = this.value;
	});
})( jQuery );
$(document).on('refresh', 'table.fields_express tr.field td select[name^="param_id["]', function(event, previous, value) {
  if (previous == value) {
		return;
	}
	var th = $(this).closest('tr.field').eq(0);
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
