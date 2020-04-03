$(".email_admin_plus").click(function(){
$("#emailadmintext div").last().after($("#emailadmintext div").last().clone());
$("#emailadmintext div input").last().val("");
});