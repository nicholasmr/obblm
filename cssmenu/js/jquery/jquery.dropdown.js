$(document).ready(function(){

 $("ul.dropdown li").hover(function(){
   $(this).addClass("hover");
   $('> .dir',this).addClass("open");
   $('ul:first',this).css('visibility', 'visible');
 },function(){
   $(this).removeClass("hover");
   $('.open',this).removeClass("open");
   $('ul:first',this).css('visibility', 'hidden');
 });

// Hack by Nicholas, 2013 June
 var ddstate = 1;
 $("ul.dropdown li").click(function(){
    if (ddstate == 1) {
       $(this).removeClass("hover");
       $('.open',this).removeClass("open");
       $('ul:first',this).css('visibility', 'hidden');
       ddstate = 0;
   } else {
       $(this).addClass("hover");
       $('> .dir',this).addClass("open");
       $('ul:first',this).css('visibility', 'visible');
       ddstate = 1;
   }
   
   
 });


}).Statistics
