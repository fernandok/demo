var $ = jQuery;
$(function(){
	$(document).ready(function(){
		$(".block-search-form-block").addClass("col-md-4");
		$("#block-primarymenublock").addClass("col-md-4");
		$('.language-menu, .account-menu').addClass("dropdown-menu");
		$('.primary-menu li:nth-child(2), .primary-menu li:last-child').addClass('dropdown expanded');
		$('.primary-menu li:nth-child(2)').on('click',function(){
			$(this).toggleClass('open');
		});
		$('.primary-menu li:last-child').on('click',function(){
			$(this).toggleClass('open');
		});
		$("[role='heading']").addClass('col-md-12 header');
		// $(".form-search").append("<input class="form-submit" type="submit" id="edit-submit">");
		
		$("#block-mainmenu > ul > li:nth-child(2)").click(function(e){
				e.preventDefault();
	      		var $content = $('#products-menu-content');
	      		var isVisible =  $content.is(":visible");
			      $('.menu-drop').hide();
			      if(isVisible) {

			        return;
			      }
			      $content.show();
		});

		// $("#block-mainmenu > ul > li:nth-child(1)").click(function(e){
	 //      e.preventDefault();

	 //      var $content = $('#solutions-menu-content');
	 //      var isVisible =  $content.is(":visible");
	 //      $('.dropdown-menu').hide();
	 //      $('.cypress-main-menu ul li').removeClass('hover-menu-item');
	 //      $('.cypress-main-menu ul li a').removeClass('hover-menu-item');
	 //      if(isVisible) {
	 //        return;
	 //      }
	 //      $content.show();
  //   	});

  		$("#block-mainmenu > ul > li:nth-child(1)").click(function(e){
	      e.preventDefault();

	      var $content = $('#solutions-menu-content');
	      var isVisible =  $content.is(":visible");
	      $('.menu-drop').hide();
	      if(isVisible) {
	        return;
	      }
	      $content.show();
    	});


    	$("#block-mainmenu > ul > li:nth-child(3)").click(function(e){
	      e.preventDefault();

	      var $content = $('#design-menu-content');
	      var isVisible =  $content.is(":visible");
	      $('.menu-drop').hide();
	        if(isVisible) {
	        return;
	      }
	      $content.show();
    	});

    	$("#block-mainmenu > ul > li:nth-child(4)").click(function(e){
	      e.preventDefault();

	      var $content = $('#buy-menu-content');
	      var isVisible =  $content.is(":visible");
	      $('.menu-drop').hide();
	      if(isVisible) {
	        return;
	      }
	      $content.show();
    	});
    	$("img.close-nav").click(function(){
	      $('.menu-drop').hide();
	      $(".login-menu").hide();
	      $(".language-menu").hide();
	  	});

	});
});