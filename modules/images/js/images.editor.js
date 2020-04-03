/**
 * Плагин для визуального редактора, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

var selectarea = [];
var list = false;
var images_count = 0;
var dataStore = window.sessionStorage;
$(document).ready(function () {
	$("#tabs").tabs();
	if (list) {
		$('#fileupload').fileupload({
			dataType: 'json',
			submit: function (e, data) {
				data.formData = {
					action: "upload",
					folder_id: folder_id,
					module: "images"
				};
				if (images_count < 1) {
					$('#fileupload').before('<div class="images_loading"></div>');
				}
				$.each(data.files, function (k, v) {
					$('#fileupload').after('<div class="images_status" name="' + v.name + '">...uploading ' + v.name + '</div>');
				});
				images_count = images_count + 1;
			},
			done: function (e, data) {
				result_upload(data.result);
				images_count = images_count - 1;
				$.each(data.files, function (k, v) {
					$('.images_status[name="' + v.name + '"]').html(v.name + ' <span style="color:green">ok</span>').removeClass('images_status').addClass('images_status_ok');
				});
				if (images_count < 1) {
					$('.images_status_ok').remove();
					$('.images_loading').remove();
				}
			}
		});
		$("input[name=images_upload_links_check]").click(function () {
			$(this).next().next('.div_images_links').toggle();
		});
		$(".images_upload_links").click(function () {
			var textarea_links = $(this).parents('.div_images_links').find('textarea');
			$.ajax({
				type: "POST",
				data: {
					action: "upload_links",
					links: textarea_links.val(),
					module: "images",
					folder_id: folder_id,
					check_hash_user: $('input[name=check_hash_user]').val()
				},
				dataType: "json",
				url: window.location.href,
				success: function (response, statusText, xhr, form)
				{
					result_upload(response);
					textarea_links.val('');
				}
			});
		});

		$(document).on('click', ".images_actions a", function () {
			var self = $(this);
			if (!self.attr("action")) {
				return true;
			}
			if (self.attr("confirm") && !confirm(self.attr("confirm"))) {
				return false;
			}
			$.ajax({
				url: window.location.href,
				type: 'POST',
				dataType: 'json',
				data: {
					action: self.attr("action"),
					image_id: self.parents(".images_actions").attr("image_id"),
					check_hash_user: $('input[name=check_hash_user]').val()
				},
				success: (function (response)
				{
					if (response.error) {
						$(".error_images").html(prepare(response.error)).show();
					}
					if (response.errors && response.errors['image']) {
						$(form).find(".error_images").html(prepare(response.errors['image'])).show();
					}
					if (response.target) {
						$(response.target).html(prepare(response.data));
					}
					if (response.hash) {
						$('input[name=check_hash_user]').val(response.hash);
					}
					if (response.result == 'success' && self.attr("action") == 'delete') {
						self.parents('.images_actions').remove();
					}
				})
			});
			return false;
		});

		$(document).on('mouseover', ".images_actions", function () {
			$(this).addClass('hover');
			$(this).find('.images_button').show();
		});

		$(document).on('mouseout', ".images_actions", function () {
			$(this).removeClass('hover');
			$(this).find('.images_button').hide();
		});

		$(document).on('click', "#images_selectarea_button", function () {
			if ($("input[name=x1]").val() == $("input[name=x2]").val()
					|| $("input[name=y1]").val() == $("input[name=y2]").val())
			{
				alert($("#images_selectarea_info").text());
				return false;
			}
			$.ajax({
				type: "POST",
				data: {
					action: "selectarea",
					x1: $("input[name=x1]").val(),
					x2: $("input[name=x2]").val(),
					y1: $("input[name=y1]").val(),
					y2: $("input[name=y2]").val(),
					id: $("input[name=image_id]").val(),
					variation_id: $("input[name=variation_id]").val(),
					check_hash_user: $('input[name=check_hash_user]').val()
				},
				dataType: "json",
				url: window.location.href,
				success: function (response, statusText, xhr, form)
				{
					if (response.hash)
					{
						$('input[name=check_hash_user]').val(response.hash);
					}
					$("#selectarea").hide();
					$('.imgareaselect-selection').parents('div').remove();
					$('.imgareaselect-outer').remove();
					get_selectarea();
					return false;
				}
			});
			return false;
		});
	}
	$('#images_variations').each(function () {
		show_delete_images_variation($(this));
	});
	$('.images_variation_plus').click(function () {
		var contaner = $(this).parents('#images_variations');
		var last = contaner.find(".images_variation").last();
		if (contaner.find(".images_variation").length >= last.find('select option').length) {
			return false;
		}
		last.after(last.clone(true));
		contaner.find(".images_variation").last().find("input").val('');
		show_delete_images_variation(contaner);
		if (contaner.find(".images_variation").length == last.find('select option').length) {
			contaner.find('.images_variation_plus').hide();
		}
	});
	$(document).on('click', '.images_variation_delete', function () {
		var contaner = $(this).parents('#images_variations');
		if (contaner.find('.images_variation').length == 1) {
			return false;
		}
		if (!confirm($(this).attr('confirm'))) {
			return false;
		}
		$(this).parents('.images_variation').remove();
		show_delete_images_variation(contaner);
		contaner.find('.images_variation_plus').show();
	});

	$(".folder img, .folder_open img").hide();

	$(document).on('mouseover', ".folder, .folder_open", function () {
		$(this).find('img').show();
	});

	$(document).on('mouseout', ".folder, .folder_open", function () {
		$(this).find('img').hide();
	});

	$(document).on('click', ".folder_delete", function () {
		if (confirm($(this).attr('confirm')))
		{
			$(this).parents('form').submit();
		}
	});

	$(document).on('click', '.images_close', function () {
		var ed = parent.tinymce.activeEditor;
		ed.windowManager.close();
	});
	if($('.images_insert').length) {
		try {
			var link_to = dataStore.getItem('images_link_to');
			var active_tab = dataStore.getItem('images_active_tab');
		} catch(e) {
			var link_to = '-';
			var active_tab = '';
		}
		$('select[name=link_to] option[data-folder='+link_to+']').attr('selected', true);
		if (active_tab) {
			$('.tags_image_h[data-folder='+active_tab+'] a').click();
		}
	}
	$(document).on('click', '.images_insert', function () {
		var text = '';
		var src = '';
		var width = 0;
		var height = 0;
		$('.tabs_image').each(function () {
			if (! $(this).is(":hidden")) {
				src = $(this).find('img').attr('src');
				width = $(this).find('img').attr('w');
				height = $(this).find('img').attr('h');
				dataStore.setItem('images_active_tab', $(this).attr('data-folder'));
			}
		});
		var ed = parent.tinymce.activeEditor;
		if (src) {
			dataStore.setItem('images_link_to', $('select[name=link_to] option:selected').attr('data-folder'));
			text = '<img src="' + src + '" alt="' + $('input[name=alt]').val() + '" title="' + $('input[name=title]').val() + '" width="' + width + '" height="' + height + '">';

			if ($('select[name=link_to]').val())
			{
				text = '<a href="' + $('select[name=link_to]').val() + '" data-fancybox="editor">' + text + '</a>';
			}
			ed.execCommand('mceInsertContent', false, text);
		}
		ed.windowManager.close();
	});
});



function result_upload(response) {
	if (response.selectarea) {
		$.each(response.selectarea, function (k, v) {
			selectarea.push(v);
		});
		get_selectarea();
	}
	if (response.error) {
		$(".error_images").html(prepare(response.error)).show();
	}
	if (response.errors && response.errors['image']) {
		$(".error_images").html(prepare(response.errors['image'])).show();
	}
	if (response.data) {
		$(".dip_images").prepend(prepare(response.data));
	}
	if (response.hash) {
		$('input[name=check_hash_user]').val(response.hash);
	}
}

function get_selectarea() {
	if (!$("#selectarea").is(":hidden")) {
		return;
	}
	var stop = false;
	$.each(selectarea, function (k, v) {
		if (!stop && v) {
			$("#selectarea").html(prepare(v)).show();
			selectarea[k] = '';
			stop = true;
		}
	});
}

function show_delete_images_variation(contaner) {
	if (contaner.find('.images_variation').length == 1) {
		contaner.find('.images_variation_delete').hide();
	} else {
		contaner.find('.images_variation_delete').show();
	}
}

function prepare(string) {
	string = str_replace('&lt;', '<', string);
	string = str_replace('&gt;', '>', string);
	string = str_replace('&amp;', '&', string);
	return string;
}

function str_replace(search, replace, subject, count) {
	f = [].concat(search),
			r = [].concat(replace),
			s = subject,
			ra = r instanceof Array, sa = s instanceof Array;
	s = [].concat(s);
	if (count) {
		this.window[count] = 0;
	}
	for (i = 0, sl = s.length; i < sl; i++) {
		if (s[i] === '') {
			continue;
		}
		for (j = 0, fl = f.length; j < fl; j++) {
			temp = s[i] + '';
			repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
			s[i] = (temp).split(f[j]).join(repl);
			if (count && s[i] !== temp) {
				this.window[count] += (temp.length - s[i].length) / f[j].length;
			}
		}
	}
	return sa ? s : s[0];
}
