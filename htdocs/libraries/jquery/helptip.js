$(function() {
   $("span.helptext").hide();
   $("img.helptip").hover(function() {
     $(this).nextAll().each( function() {
    if ($(this).filter('span.helptext').is(":visible")) {
     $(this).filter('span.helptext').toggle("slow");
       return false;
    } else {
	$("span.helptext").fadeOut("slow");
    }
      if ($(this).filter('img.helptip').length) {
       return false;
      }
     $(this).filter('span.helptext').toggle("slow");
    });
   });

 $('input.checkemall').click(function() {
  if($(this).is(":checked")) {
   $(this).parents(".grouped").find("input").attr("checked",true);
  } else {
   $(this).parents(".grouped").find("input").attr("checked",false);
  }
 });
});