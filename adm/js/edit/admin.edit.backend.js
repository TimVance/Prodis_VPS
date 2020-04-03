/*DIAFAN.CMS*/

$("select[name=backend]").change(function() {
	$("#name input[name=name]").val($( "select[name=backend] option:selected" ).text());
});