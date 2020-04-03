$(window).resize(function () {

    var visible_td = $(".cart tbody > tr:first-child > td:visible").length;

    if (!$(".cart_img").is(":visible")) {
      $(".cart_total").attr("colspan", 1);
      $(".cart_additional").attr("colspan", visible_td - 2);
    } else {
      $(".cart_total").attr("colspan", 2);
      $(".cart_additional").attr("colspan", visible_td - 3);
    }

    $(".cart_delivery_title, .cart_additional_title").attr("colspan", visible_td - 1);
    $(".cart_delivery, .cart_discount_total_text").attr("colspan", visible_td - 2);
});
$(window).trigger('resize');
