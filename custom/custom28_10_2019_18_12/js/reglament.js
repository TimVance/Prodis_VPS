$(function() {
    $(".select-all").click(function() {
        $(this).parent().parent().find("input").prop('checked', true);
    });
    $(".select-empty").click(function() {
        $(this).parent().parent().find("input").prop('checked', false);
    });
    $(".weekdays").click(function() {
        $(this).parent().parent().find("input").prop('checked', true);
        $(this).parent().parent().find("label:nth-child(n+7) input").prop('checked', false);
    });
});