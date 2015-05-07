//tab effects

var TabbedContent = {
	init: function() {	
		jQuery(".tab_item").mouseover(function() {
		
			var background = jQuery(this).parent().find(".moving_bg");
			
			jQuery(background).stop().animate({
				left: jQuery(this).position()['left']
			}, {
				duration: 300
			});
			
			TabbedContent.slideContent(jQuery(this));
			
		});
	},
	
	slideContent: function(obj) {
		
		var margin = jQuery(obj).parent().parent().parent().find(".slide_content").width();
		margin = margin * (jQuery(obj).prevAll().size() - 1);
		margin = margin * -1;
		
		jQuery(obj).parent().parent().parent().find(".tabslider").stop().animate({
			marginLeft: margin + "px"
		}, {
			duration: 300
		});
	}
}

jQuery(document).ready(function() {
	TabbedContent.init();
});
