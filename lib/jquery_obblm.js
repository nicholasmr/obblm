/**

Various jQuery scripts for OBBLM
Author: Sune Radich Christensen

**/

(function($) {
    $.fn.htmlTrim = function() {
		
		var regexp = /<("[^"]*"|'[^']*'|[^'">])*>/gi;
        
		this.each(function() {        	
			tmp = $(this).html().replace(/^\s\s*/,'')
				.replace(/\s\s*$/,'')
				.replace(regexp,"");
				
				//.replace(/(\r\n|[\r\n])/g, "<br>");
		
			$(this).html(tmp);
        });
        return $(this);
    }
})(jQuery);


$(document).ready(function(){
	
	$("#descEdit").click(function(){
		tmpHtml = $(".playerDescriptionBody").htmlTrim().html();
		//Make form
		tmp = $("<form method='POST' enctype='multipart/form-data'><textarea name='playertext' rows='1' cols='1'>" + tmpHtml + "</textarea><input type='hidden' name='type' value='playertext'><input type='submit' name='Save' value='Save'></form>");
		$(".playerDescriptionBody").replaceWith(tmp);
		$(this).hide();
	});
	
});