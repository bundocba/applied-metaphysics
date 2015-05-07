jQuery(document).ready(function($) {
	$('ul li:last-child').addClass('lastItem');
	$('ul li:first-child').addClass('firstItem');
	
/*ScrollToTop button*/
	$(function() {
		$(window).scroll(function() {
			if($(this).scrollTop() != 0) {
				$('.rt-block.totop').fadeIn();	
			} else {
				$('.rt-block.totop').fadeOut();
			}
		});
	});
	if ($.browser.msie) {
		$('img#jform_profile_dob_img').hide();
	}
/*Avoid input bg in Chrome*/
	if ($.browser.webkit) {
		$('input').attr('autocomplete', 'off');
	}
	$('div.smile a').hover(function(){$(this).find('img').stop().animate({top:-5},200)},function(){$(this).find('img').stop().animate({top:0},200)})
/*Zoom Icon. Portfolio page*/
	$('a.modal.img').hover(function(){
		$(this).find('img').stop().animate({opacity:.1},{queue:false}).parent().find('span.zoom-icon').stop().animate({top: '50%'},{queue:false}).parent().find('span.zoom-text').stop().animate({bottom:'50%'},{queue:false});
	},function(){
		$(this).find('img').stop().animate({opacity:1},{queue:false}).parent().find('span.zoom-icon').stop().animate({top:0},{queue:false}).parent().find('span.zoom-text').stop().animate({bottom:0},{queue:false});
	})
});