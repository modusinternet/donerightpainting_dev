/* Loading Screen START */
window.setTimeout(function(){
  document.getElementById("loading_svg").style.opacity="0";
  window.setTimeout(function(){
    document.getElementById("loading_svg").style.display="none";
  },500);
},500);
window.setTimeout(function(){
  document.getElementsByTagName("body")[0].style.opacity="1";
},250);
/* Loading Screen END */


/* nav bar active selector */
navActiveArray.forEach(function(s){$("#"+s).addClass("active");});
/*navActiveFooterArray.forEach(function(s){$("#"+s).addClass("active");});*/


/* Shrink the Nav bar once we've scrolled 50px or more down the screen. */
$(window).scroll(function() {
	var scroll = $(window).scrollTop();
	if(scroll >= 80) {
		$("#logo1").attr("style","opacity:0");
		$("#logo2").attr("style","opacity:1");
    $(".navShadowBox").addClass("active");
		/*
		$(".navigation").addClass("scrolled");
		$(".navigation").attr("style","position:fixed;top:0px");
		$(".cd-header-buttons").addClass("scrolled");
		$(".cd-search").addClass("scrolled");
		$(".scrollToTopButton").addClass("scrollToTopButton-active");
		*/
	} else {
		$("#logo1").attr("style","opacity:1");
		$("#logo2").attr("style","opacity:0");
    $(".navShadowBox").removeClass("active");
		/*
		$(".navigation").removeClass("scrolled");
		$(".navigation").attr("style","position:absolute;top:80px");
		$(".cd-header-buttons").removeClass("scrolled");
		$(".cd-search").removeClass("scrolled");
		$(".scrollToTopButton").removeClass("scrollToTopButton-active");
		*/
	}
});


/*************************/
/* Navigation Code Begin */
/*************************/
jQuery(document).ready(function($){
	/* If you change this breakpoint in the css file, don't forget to update this value as well. */
	var MqL = 1024;
	/* Move nav element position according to window width. */
	moveNavigation();
	$(window).on('resize',function(){
		(!window.requestAnimationFrame)?setTimeout(moveNavigation,300):window.requestAnimationFrame(moveNavigation);
	});

	/* Mobile - open lateral menu clicking on the menu icon. */
	$('.cd-nav-trigger').on('click',function(event){
		event.preventDefault();
		if($('#cd-main-content').hasClass('nav-is-visible')){
			closeNav();
			$('.cd-overlay').removeClass('is-visible');
		} else {
			$(this).addClass('nav-is-visible');
			$('.cd-primary-nav').addClass('nav-is-visible');
			$('.cd-main-header').addClass('nav-is-visible');
			$('#cd-main-content').addClass('nav-is-visible').one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend',function(){
				$('body').addClass('overflow-hidden');
			});
			toggleSearch('close');
			$('.cd-overlay').addClass('is-visible');
		}
	});

	/* Open search form. */
	$('.cd-search-trigger').on('click',function(event){
		event.preventDefault();
		toggleSearch();
		closeNav();
	});

	/* Close lateral menu on mobile. */
	$('.cd-overlay').on('swiperight',function(){
		if($('.cd-primary-nav').hasClass('nav-is-visible')){
			closeNav();
			$('.cd-overlay').removeClass('is-visible');
		}
	});
	$('.nav-on-left .cd-overlay').on('swipeleft',function(){
		if($('.cd-primary-nav').hasClass('nav-is-visible')){
			closeNav();
			$('.cd-overlay').removeClass('is-visible');
		}
	});
	$('.cd-overlay').on('click',function(){
		closeNav();
		toggleSearch('close');
		$('.cd-overlay').removeClass('is-visible');
	});

	/* Prevent default clicking on direct children of .cd-primary-nav. */
	$('.cd-primary-nav').children('.has-children').children('a').on('click',function(event){
		event.preventDefault();
	});
	/* Open submenu. */
	$('.has-children').children('a').on('click',function(event){
		if(!checkWindowWidth()) event.preventDefault();
		var selected = $(this);
		if(selected.next('ul').hasClass('is-hidden')){
			/* Desktop version only. */
			selected.addClass('selected').next('ul').removeClass('is-hidden').end().parent('.has-children').parent('ul').addClass('moves-out');
			selected.parent('.has-children').siblings('.has-children').children('ul').addClass('is-hidden').end().children('a').removeClass('selected');
			$('.cd-overlay').addClass('is-visible');
		} else {
			selected.removeClass('selected').next('ul').addClass('is-hidden').end().parent('.has-children').parent('ul').removeClass('moves-out');
			$('.cd-overlay').removeClass('is-visible');
		}
		toggleSearch('close');
	});

	/* Submenu items - go back link. */
	$('.go-back').on('click',function(){
		$(this).parent('ul').addClass('is-hidden').parent('.has-children').parent('ul').removeClass('moves-out');
	});

	function closeNav(){
		$('.cd-nav-trigger').removeClass('nav-is-visible');
		$('.cd-main-header').removeClass('nav-is-visible');
		$('.cd-primary-nav').removeClass('nav-is-visible');
		$('.has-children ul').addClass('is-hidden');
		$('.has-children a').removeClass('selected');
		$('.moves-out').removeClass('moves-out');
		$('#cd-main-content').removeClass('nav-is-visible').one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend',function(){
			$('body').removeClass('overflow-hidden');
		});
	}

	function toggleSearch(type){
		if(type=="close"){
			/* Close serach. */
			$('.cd-search').removeClass('is-visible');
			$('.cd-search-trigger').removeClass('search-is-visible');
			$('.cd-overlay').removeClass('search-is-visible');
		} else {
			/* Toggle search visibility. */
			$('.cd-search').toggleClass('is-visible');
			$('.cd-search-trigger').toggleClass('search-is-visible');
			$('.cd-overlay').toggleClass('search-is-visible');
			if($('.cd-search').hasClass('is-visible')) $('.cd-search').find('input[type="search"]').focus();
			($('.cd-search').hasClass('is-visible'))?$('.cd-overlay').addClass('is-visible'):$('.cd-overlay').removeClass('is-visible');
		}
	}

	function checkWindowWidth(){
		/* Check window width (scrollbar included). */
		var e = window,
		a = 'inner';
		if(!('innerWidth' in window)){
			a = 'client';
			e = document.documentElement || document.body;
		}
		if(e[a+'Width']>=MqL){
			return true;
		} else {
			return false;
		}
	}

	function moveNavigation(){
		var navigation = $('.cd-nav');
		var desktop = checkWindowWidth();
		if(desktop){
			navigation.detach();
			navigation.insertBefore('.cd-header-buttons');
		} else {
			navigation.detach();
			navigation.insertAfter('#cd-main-content');
		}
	}
});
/***********************/
/* Navigation Code End */
/***********************/


/***********************/
/* Add to Home screen (A2HS) Code Begin. */
/* https://developer.mozilla.org/en-US/docs/Web/Apps/Progressive/Add_to_home_screen#How_do_you_make_an_app_A2HS-ready */
/***********************/
function getCookie(cname){
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++){
		var c = ca[i];
		while(c.charAt(0) == ' '){
			c = c.substring(1);
		}
		if(c.indexOf(name) == 0){
			return c.substring(name.length, c.length);
		}
	}
	return "";
}
let a2Cookie;
let deferredPrompt;
const A2HSbox = document.getElementById("A2HS-box");
const A2HSbox_no = document.getElementById("A2HS-box-no");
const A2HSbox_yes = document.getElementById("A2HS-box-yes");
window.addEventListener("beforeinstallprompt",e =>{
	a2Cookie = getCookie("A2HSbox");
	/* Test for A2HSbox cookie. */
	if(a2Cookie == ""){
		/* A2HSbox cookie not found so run 'beforeinstallprompt' event detection code. */
		console.log('A2HSbox cookie not found and "beforeinstallprompt" event detected, dropping A2HS box.');
		/* Prevent Chrome 67 and earlier from automatically showing the prompt. */
		e.preventDefault();
		/* Stash the event so it can be triggered later. */
		deferredPrompt = e;
		/* Update UI to notify the user they can add to home screen. */
		A2HSbox.classList.add("active");

		A2HSbox_no.addEventListener('click',e =>{
			console.log('User dismissed A2HS prompt #1.');
			/* hide our user interface that shows our A2HS button. */
			A2HSbox.classList.remove("active");
			/* Set cookie to defer A2HS box apearence in the future.	(15768000 = 6 months) */
			document.cookie = "A2HSbox=1; max-age=15768000; path=/";
			deferredPrompt = null;
		});

		A2HSbox_yes.addEventListener('click',e =>{
			console.log('User accepted A2HS prompt #1.');
			/* hide our user interface that shows our A2HS button. */
			A2HSbox.classList.remove("active");
			/* Show the prompt. */
			deferredPrompt.prompt();
			/* Wait for the user to respond to the prompt. */
			deferredPrompt.userChoice.then(choiceResult =>{
				if (choiceResult.outcome === 'accepted') {
					console.log('User accepted A2HS prompt #2.');
				} else {
					console.log('User dismissed A2HS prompt #2.');
					/* Set cookie to defer A2HS box apearence in the future.	(15768000 = 6 months) */
					document.cookie = "A2HSbox=1; max-age=15768000; path=/";
				}
				deferredPrompt = null;
			});
		});
	}
});
/***********************/
/* Add to Home Screen (A2HS) Code End. */
/* https://developer.mozilla.org/en-US/docs/Web/Apps/Progressive/Add_to_home_screen#How_do_you_make_an_app_A2HS-ready */
/***********************/
