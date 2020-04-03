/**
 * Редактирование фотографий, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
var images_count = 0;

$('.upload_files').click(function() {
	$('#upload_area_multi').show();
	return false;
});
$('#fileupload').fileupload({
	dataType: 'json',
	submit: function(e, data) {
		images_count = images_count+1;
		data.formData = {
			save_upload : "1",
			check_hash_user: $('.check_hash_user').text()
		};
		$.each(data.files, function (k,v){
			$('#fileupload').after('<div class="images_status" name="'+v.name.replace(/[^a-z0-9]+/, '')+'"></div>');
			$(".images_status[name='"+v.name.replace(/[^a-z0-9]+/, '')+"']").text(v.name)
		});
	},
	done: function (e, data) {
		images_count = images_count-1;
		var response = data.result;
		if(response.success) {
			$(".images_status[name='"+response.file+"']").append('<span style="color:green"> ok</span>').removeClass('images_status');
		} else {
			$.each(data.files, function (k,v){
				$(".images_status[name='"+v.name.replace(/[^a-z0-9]+/, '')+"']").append('<span style="color:green"> bad</span>').removeClass('images_status');
			});
		}
		if(images_count < 1) {
			if(response.redirect) {
				window.location.href = response.redirect;
			}
		}
	}
});
