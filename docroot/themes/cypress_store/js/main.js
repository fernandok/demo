var $ = jQuery;
$(function(){
	$(document).ready(function(){
		$(".block-search-form-block").addClass("col-md-4");
		$("#block-primarymenu").addClass("col-md-4");
		$('.language-menu, .account-menu').addClass("dropdown-menu");
		$('.primary-menu li:nth-child(2), .primary-menu li:last-child').addClass('dropdown expanded');
		$('.primary-menu li:nth-child(2), .primary-menu li:last-child').on('click',function(){
			$(this).toggleClass('open');
		});
		
	});
});