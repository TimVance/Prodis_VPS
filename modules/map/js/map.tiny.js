/**
 * Подгружает карту сайта в визуальном редакторе, JS-сценарий
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
$(document).ready(function () {
  $(document).on('click', '#diafan_map a.plus',function() {
    var li = $(this).parent("li");
    var parent_id=parseInt(li.attr("parent_id"),10);
    var site_id=parseInt(li.attr("site_id"),10);
    var module_name = $(this).attr("module_name");

    $(this).removeClass("plus").addClass("expand");
    if($(this).text() != '+') {
        parent_id=0;
    } else {
        $(this).text("—");
    }

    diafan_map(li, parent_id, site_id, module_name);

    return false;
  });

   $(document).on('click', '#diafan_map a.expand', function() {
    var li=$(this).parent("li");
    var parent_id=parseInt(li.attr("parent_id"),10);
    var site_id=parseInt(li.attr("site_id"),10);
    var module_name = $(this).attr("module_name");

    $(this).removeClass("expand").addClass("plus");
    if($(this).text() == '—') {
        $(this).text("+");
    } else {
        parent_id=0;
    }
    $("#diafan_map").find("ul[parent_id="+parent_id+module_name+"]").remove();

    return false;
  });

  $(document).on('click', '#diafan_map a.link', function() {
    var win = top.tinymce.activeEditor.windowManager.getWindows()[0];
    win.find('#href').value($(this).attr("href"));
    var ed = parent.tinymce.activeEditor;
    ed.windowManager.close();
    return false;
  });
});
function diafan_map(elem, parent_id, site_id, module_name) {
  $.ajax({
    url : base_path + "map/tiny/",
    type : 'POST',
    dataType : 'html',
    data : {
      parent_id: parent_id,
      site_id: site_id,
      module_name: module_name,
    },
    success : (function(response)
    {
      elem.append(response);
    });
  });
}
