var $ = jQuery;
$(function(){
	$(document).ready(function(){
		$(".block-search-form-block").addClass("col-md-4");
		$("#block-primarymenublock").addClass("col-md-4");
		$('.language-menu, .account-menu').addClass("dropdown-menu");
		$('.primary-menu li:nth-child(2), .primary-menu li:last-child').addClass('dropdown expanded');
		$('.primary-menu li:nth-child(2)').on('click',function(){
			// $(this).toggleClass('open');
			$('ul.account-menu').hide();
			$('ul.language-menu').toggle();
		});
		$('.primary-menu li:last-child').on('click',function(){
			$('ul.language-menu').hide();
			$('ul.account-menu').toggle();
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
	      $(".account-menu").hide();
	      $(".language-menu").hide();
	  	});

	  	var widthMenu = $(window).width();
	  if($(window).width() >= 1024){
	  	$('.menu-drop').parent('div').css('width', widthMenu);
     //  $('.menu-drop').parent('div').css({
	    //   "background": "#002431",
	    //   "position": "relative";
	    //   "right": "27%";
	    // });
	   $('.menu-drop').parent('div').addClass('menu-drop-parent');
	  }
      
	});
});