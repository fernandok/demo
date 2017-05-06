var $ = jQuery;
$(function(){
	$(document).ready(function(){
		if($(".user-logged-in").length > 0){
	 }
	 else{
	 	$("body").addClass('not-logged-in');
	 }
	 $('.user-login-form input').removeAttr("data-original-title");
	 $('#edit-coupon-redemption-coupons input#edit-coupon-redemption-coupons-code').removeAttr("data-original-title");
		// $("body:not(user-logged-in)").addClass('not-logged-in');
		$(".block-search-form-block").addClass('col-md-4 col-sm-4');
		var width= $(window).width();
		$(window).resize(function(){
		    $('.block-search-form-block input[type="search"]').width($(window).width()-41);
		});
		$('.block-search-form-block input[type="search"]').width($(window).width()-41);
		$('.block-search-form-block input[type="search"]').parent().append('<input class="input-submit" type="submit" value="">');
		// $('form').find("input[type=search]").each(function(ev){
		//       if(!$(this).val()) { 
		//      $(this).attr("placeholder", "Enter your keywords");
		//   	}
		// });
		$('.block-search-form-block input[type="search"]').attr("placeholder", "Enter your keywords");
		$("#block-primarymenublock").addClass('col-md-4 hidden-xs');
		$('.language-menu, .account-menu').addClass("dropdown-menu");
		$('.primary-menu li:nth-child(2), .primary-menu li:last-child').addClass('dropdown expanded');
		$('.primary-menu li:nth-child(2) > a').on('click',function(){
			// $(this).toggleClass('open');
			$('ul.account-menu').hide();
			$('ul.language-menu').toggle();
		});
		$('.user-menu li:first-child').on('click',function(){
			$('ul.language-menu').hide();
			$('ul.user-menu').toggle();
		});
		$('.user-logged-in .user-menu ul.menu.nav li ul.menu ').append('<img alt="Close" class="h1 close-nav" src="/themes/cypress_store/images/main-nav-caret.svg">');
		// $("[role='heading']").addClass('col-md-12 header');
		// $(".form-search").append("<input class="form-submit" type="submit" id="edit-submit">");
		$('.region-header').prepend('<button type="button" class="navbar-toggle"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>');
		
			$('button.navbar-toggle').on('click',function(){
				$('.main-menu').toggleClass('hidden-xs');
				$('.menu-drop').hide();
				$('.navbar-toggle span:nth-child(2)').toggleClass('rotate-close-1');
				$('.navbar-toggle span:nth-child(3)').toggleClass('rotate-close-2');
				$('.navbar-toggle span:nth-child(4)').toggleClass('rotate-close-3');
			});

		// if($(window).width() < 767){
		// 	$('.region-header').append('<a href="/user" class="dropdown-toggle user-icon" data-target="#" data-toggle="dropdown" aria-expanded="false"> <img src="/themes/cypress_store/images/user-image.png" alt="my pic"><span class="caret"></span></a>');
		// 	$('a.user-icon').on('click', function(){
		// 		// alert('ok');
		// 		$('.user-menu ul.menu.nav > li:nth-child(1)').addClass('open');
		// 	// });
		// 	// $('.user-menu').toggleClass('hidden-xs');
		// 	// $('.user-menu ul.menu.nav > li:first-child > a').replaceWith("<a href="/user" class="dropdown-toggle user-icon" data-target="#" data-toggle="dropdown" aria-expanded="false"> <img src="/themes/cypress_store/images/user-image.png" alt="my pic"><span class="caret"></span></a>");
		// 	});
		// }
		$('a.user-icon').on('click', function(){
				// alert('ok');
				// $('.primary-menu').toggleClass('hidden-xs');
			$('.mobile-menu').toggle();
			$('.language-menu').hide();
			// $('.main-menu').hide();
		});
		$('.english').on('click', function(e){
			$('.language-menu').toggle();
			// alert("hi");
		});
		$('.mobile-menu .language-menu').removeClass('dropdown-menu');

		$('.main-menu ul.menu.nav ul').addClass('hidden-lg hidden-md hidden-sm');
		// $('.main-menu ul.menu.nav ul').removeClass('dropdown-menu').addClass('nav-xs');
		$('.menu-drop').addClass('hidden-xs');
		// $('.main-menu ul.menu.nav').append('<li><img alt="Close" class="h1 close-nav-menu hidden-sm hidden-md hidden-lg" src="/themes/cypress_store/images/main-nav-caret.svg"></li>');
		// $('.main-menu ul.menu.nav li:nth-child(1) ul').addClass('first-child-menu')

		// if($(window).width() > 767){
		
	  		$("#block-mainmenu > ul > li:nth-child(1)").click(function(e){
		      // e.preventDefault();

		      var $content = $('#solutions-menu-content');
		      var isVisible =  $content.is(":visible");
		      $('.menu-drop').hide();
		      $('.mobile-menu').hide();
		      if(isVisible) {
		        return;
		      }
		      $content.show();
	    	});

	    	$("#block-mainmenu > ul > li:nth-child(2)").click(function(e){
				// e.preventDefault();
		   		var $content = $('#products-menu-content');
	    		var isVisible =  $content.is(":visible");
			    $('.menu-drop').hide();
		        $('.mobile-menu').hide();
		        if(isVisible) {
				    return;
			    }
	  	        $content.show();
			});


	    	$("#block-mainmenu > ul > li:nth-child(3)").click(function(e){
		      // e.preventDefault();

		      var $content = $('#design-menu-content');
		      var isVisible =  $content.is(":visible");
		      $('.menu-drop').hide();
		      $('.mobile-menu').hide();
		        if(isVisible) {
		        return;
		      }
		      $content.show();
	    	});

	    	$("#block-mainmenu > ul > li:nth-child(4)").click(function(e){
		      // e.preventDefault();

		      var $content = $('#buy-menu-content');
		      var isVisible =  $content.is(":visible");
		      $('.menu-drop').hide();
		      $('.mobile-menu').hide();
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
	      // $('.main-menu .menu.nav').hide();
	  	});
	  	$(".mobile-menu img.h1.close-nav-user").click(function(){
	  		$('.mobile-menu').hide();
	  	});
	  	// $(".main-menu img.h1.close-nav-menu").click(function(){
	  	// 	$('.main-menu').hide();
	  	// });
	  	$('.main-menu .menu.nav li ul li:last-child').append('<img alt="Close" class="h1 close-nav" src="/themes/cypress_store/images/main-nav-caret.svg" />');
	  	// $(".mobile-menu img.h1.close-nav:first-child").click(function(){
	  	// 	$('.language-menu').hide();
	  	// });

		$('.menu-drop').parent('div').addClass('menu-drop-parent');
		// var width= $(window).width();
		// $(window).resize(function(){
		//     $('.main-menu .menu.nav ul.menu.dropdown-menu').width($(window).width()-2);
		// });

		    // $('.main-menu .menu.nav ul.menu.dropdown-menu').width($(window).width()-2);
			// $('.main-menu .menu.nav').width($(window).width()-2);
		// $('.main-menu .menu.nav ul.menu.dropdown-menu').width(width-2);
			$('.main-menu .menu.nav > li > ul > li > ul').removeClass('dropdown-menu');
		$('.path-addressbook .slick-list').addClass('col-md-12 col-sm-12 col-lg-12');
		$('.path-addressbook .slick-list .views-row').addClass('col-md-3 col-sm-4 col-lg-4');
		$('.path-addressbook .slick-slide').addClass('col-xs-12');

	});
});