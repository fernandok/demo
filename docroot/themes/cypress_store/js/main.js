var $ = jQuery;
$(function(){
	$(document).ready(function(){
		$(".block-search-form-block").addClass('col-md-4 hidden-xs');
		$("#block-primarymenublock").addClass('col-md-4 hidden-xs');
		$('.language-menu, .account-menu').addClass("dropdown-menu");
		$('.primary-menu li:nth-child(2), .primary-menu li:last-child').addClass('dropdown expanded');
		$('.primary-menu li:nth-child(2)').on('click',function(){
			// $(this).toggleClass('open');
			$('ul.account-menu').hide();
			$('ul.language-menu').toggle();
		});
		$('.user-menu li:first-child').on('click',function(){
			$('ul.language-menu').hide();
			$('ul.user-menu').toggle();
		});
		$('.user-logged-in .user-menu ul.menu.nav li ul.menu ').append('<img alt="Close" class="h1 close-nav" src="/themes/cypress_store/images/main-nav-caret.svg">');
		$("[role='heading']").addClass('col-md-12 header');
		// $(".form-search").append("<input class="form-submit" type="submit" id="edit-submit">");
		$('.region-header').prepend('<button type="button" class="navbar-toggle"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>');
		$('button.navbar-toggle').on('click',function(){
			$('.main-menu').toggleClass('hidden-xs');
			$('.menu-drop').hide();
		});


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
    	/* close icon*/
    	$("img.h1.close-nav").click(function(){
	      $('.menu-drop').hide();
	      $(".language-menu").hide();
	      $(".account-menu").hide();
	      $('.language-menu').css("display", "none");
	  	});

	  // 	var widthMenu = $(window).width();
	  if($(window).width() > 1024){
	  	// $('.menu-drop').parent('div').css('width', widthMenu);
	  	$('.menu-drop').parent('div').width($(window).width());
	   // $('.menu-drop').parent('div').addClass('menu-drop-parent');
	  }
      var windowsize = $(window).width();
      
		$(window).resize(function() {
		  // $('.menu-drop').parent('div').css('width', windowsize);
		  // var windowsize = $(window).width();
		  $('.menu-drop').parent('div').width($(window).width());
		  $('.menu-drop').parent('div').addClass('menu-drop-parent');
		});
	$('.menu-drop').parent('div').addClass('menu-drop-parent');

	});
});