/**
 * Файловый менеджер, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
var path;
var muliupload_count = 0;

var path = $('input[name=path]').val();
$('#fileupload').fileupload({
	dataType: 'json',
	submit: function(e, data) {
		data.formData = {
			path: (path ? path : ''),
			action: 'save',
			id: true,
			action_filemanager : "upload_file",
			check_hash_user: $('.check_hash_user').text()
		};
	},
	done: function (e, data) {
		var response = data.result;
		if(response.redirect)
		{
			window.location.href = response.redirect;
		}
	}
});

$(".item__unit a").click(function () {
	var self = $(this);
	if (! self.attr("action")) {
		return true;
	}
	if (self.attr("confirm") && ! confirm(self.attr("confirm"))) {
		return false;
	}
	diafan_ajax.init({
		data:{
			action: self.attr("action"),
			id : self.parents("li").first().attr("row_id")
		},
		success: function(response) {
			self.parents("li").first().remove();
		}
	});
	return false;
});

if (document.getElementById("text_area")) {
	var editor = CodeMirror.fromTextArea(document.getElementById("text_area"), {
	    mode: "javascript",
	    lineNumbers: true,
	    lineWrapping: true,
	    matchBrackets: true,
	    indentUnit: 4,
	    indentWithTabs: true,
	    theme : "neat",
			extraKeys: {
				"Ctrl-Space": "autocomplete",
				"F11": function(cm) {
			  cm.setOption("fullScreen", !cm.getOption("fullScreen"));
			},
			"Esc": function(cm) {
			  if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
			}
		},
		matchBrackets: true,
		autoCloseBrackets: true,
	});
	editor.setSize("100%", 500);
}

$('.folders .item__folder').each(function() {
	var $this = $(this);
	$this.attr('href', $this.attr('href')+'#scroll'+$this.offset().top);
});

$(document).ready(function() {
	if(window.location.hash.length)
		$('html, body').scrollTop(window.location.hash.substr(window.location.hash.lastIndexOf('scroll')+6));
});
