var tinyMCE_init = {
	config: {
		// General options
		mode : "specific_textareas",
		editor_selector: "htmleditor",
		theme : "modern",
		language : config_language,
		file_browser_callback : "diafanimages",
		convert_urls : false,
		plugins : "spellchecker,table,hr,image,link,lists,emoticons,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,diafanimages,filemanager,responsivefilemanager,code,textcolor,colorpicker",
		toolbar: [	"code,|,undo,redo,|,cut,copy,paste,pastetext,selectall,removeformat,|,link,unlink,anchor,table,hr,|,media,diafanimages,responsivefilemanager","bold,italic,underline,strikethrough,superscript,subscript,|,forecolor,backcolor,|,alignleft,aligncenter,alignright,alignjustify,|,numlist,bullist,|,outdent,indent,blockquote",
		"formatselect,fontselect,fontsizeselect"],
		valid_elements : "*[*],insert",
		extended_valid_elements: "*[*]",
		cleanup : false,
		menubar : false,
		verify_html : false,
		cleanup_on_startup : false,
		element_format : "html",
		gecko_spellcheck : true,
		inline_styles: false,
		external_filemanager_path: base_path + "adm/htmleditor/tinymce/plugins/filemanager/",
		image_advtab: true,
	},
	i : 0,
	start: function(){	
		$('.htmleditor').each(function(){
			if(! $(this).attr('id'))
			{
				$(this).attr('id', 'tinymce_id_' + tinyMCE_init.i);
			}
			setTimeout('tinyMCE_init.load("'+ $(this).attr('id') +'");', tinyMCE_init.i * 1000);
			tinyMCE_init.i++;
		});
	},
	load : function(id){
		this.config.selector = "#" + id;
		console.log(this.config);
		tinyMCE.init(this.config);
	},
};
tinyMCE_init.start();