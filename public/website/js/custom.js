(function ($) {
  "use strict"; // Start of use strict

// Preloader Start
    $(window).load(function () {
        $('#status').fadeOut();
        $('#preloader')
            .delay(350)
            .fadeOut('slow');
        $('body')
            .delay(350)
            .css({
                overflow: 'visible'
            });
    });
// Preloader End

/// MAIN MENU SCRIPT START

  // Smooth scrolling using jQuery easing
  $('a.js-scroll-trigger[href*="#"]:not([href="#"])').click(function() {
    if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
      if (target.length) {
        $('html, body').animate({
          scrollTop: (target.offset().top - 48)
        }, 1000, "easeInOutExpo");
        return false;
      }
    }
  });

  // Closes responsive menu when a scroll trigger link is clicked
  $('.js-scroll-trigger').click(function() {
    $('.navbar-collapse').collapse('hide');
  });

  // Activate scrollspy to add active class to navbar items on scroll
  $('body').scrollspy({
    target: '#mainNav',
    offset: 54
  });

  // Collapse Navbar
  var navbarCollapse = function() {
    if ($("#mainNav").offset().top > 100) {
      $("#mainNav").addClass("navbar-shrink");
    } else {
      $("#mainNav").removeClass("navbar-shrink");
    }
  };
  // Collapse now if page is not at top
  navbarCollapse();
  // Collapse the navbar when page is scrolled
  $(window).scroll(navbarCollapse);

  // Dropdown Menu Script Start

  $('#mainNav ul.navbar-nav li.dropdown').hover(function() {
    $(this).find('.dropdown-menu').stop(true, true).delay(100).fadeIn(300);
  }, function() {
    $(this).find('.dropdown-menu').stop(true, true).delay(100).fadeOut(300);
  });
  // Dropdown Menu Script End

  // mobile Menu area
  $('nav.mobile_menu').meanmenu({
    meanScreenWidth: '991'
  });
  $('nav.mean-nav li > a:first-child').on('click', function () {
    $('a.meanmenu-reveal').click();
  });
  // mobile Menu area

  // MAIN MENU SCRIPT END

  // Jquery counterUp
  $('.counter').counterUp({
    time: 3000
  });

  // ---- App Screenshots Slider JS

  var screenshots_slide = $('.screenshots-slider');
  screenshots_slide.owlCarousel({
    loop:true,
    margin:0,
    autoplay:false,
    dots:true,
    nav:false,
    center: true,
    smartSpeed: 1e3,
    autoplayTimeout: 8e3,
    autoplaySpeed: 1e3,
    autoplayHoverPause: !0,
    responsive:{
      0:{
        items:1
      },
      600:{
        items:1
      },
      992:{
        items:5
      }
    }
  });
  $('.screenshots_slide_nav .testi_next').on('click', function() {
    screenshots_slide.trigger('next.owl.carousel');
  });

  $('.screenshots_slide_nav .testi_prev').on('click', function() {
    screenshots_slide.trigger('prev.owl.carousel');
  });

  // ---- App Screenshots Slider JS

// Veno Box

  $('.venobox').venobox();


  // Reviews Slider
  var reviews_slide = $('.reviews-slider');
  reviews_slide.owlCarousel({
    loop:true,
    margin:0,
    autoplay:true,
    dots:true,
    nav:false,
    smartSpeed: 1e3,
    autoplayTimeout: 8e3,
    autoplaySpeed: 1e3,
    autoplayHoverPause: !0,
    responsive:{
      0:{
        items:1
      },
      600:{
        items:1
      },
      992:{
        items:1
      }
    }
  });
  $('.reviews_slide_nav .testi_next').on('click', function() {
    reviews_slide.trigger('next.owl.carousel');
  });

  $('.reviews_slide_nav .testi_prev').on('click', function() {
    reviews_slide.trigger('prev.owl.carousel');
  });
  // Reviews Slider

  // Home 4 Reviews Slider
  var reviews_slide_4 = $('.reviews-slider-4');
  reviews_slide_4.owlCarousel({
    loop:true,
    margin:25,
    autoplay:true,
    dots:true,
    nav:false,
    smartSpeed: 1e3,
    autoplayTimeout: 8e3,
    autoplaySpeed: 1e3,
    autoplayHoverPause: !0,
    responsive:{
      0:{
        items:1
      },
      600:{
        items:1
      },
      992:{
        items:2
      }
    }
  });
  $('.reviews_slide_nav_4 .testi_next').on('click', function() {
    reviews_slide_4.trigger('next.owl.carousel');
  });

  $('.reviews_slide_nav_4 .testi_prev').on('click', function() {
    reviews_slide_4.trigger('prev.owl.carousel');
  });
  // Reviews Slider

  // Clients Logo Slider
  var client_logo_slide = $('.client-logo-slider');
  client_logo_slide.owlCarousel({
    loop:true,
    margin:15,
    autoplay:true,
    dots:false,
    nav:false,
    smartSpeed: 1e3,
    autoplayTimeout: 8e3,
    autoplaySpeed: 1e3,
    autoplayHoverPause: !0,
    responsive:{
      0:{
        items:2
      },
      600:{
        items:4
      },
      992:{
        items:5
      }
    }
  });
  // Clients Logo Slider

  // Scroll To Top
    $(window).scroll(function () {
        if ($(this).scrollTop() > 200) {
            $('#scroll').fadeIn();
        } else {
            $('#scroll').fadeOut();
        }
    });
    $('#scroll').click(function () {
        $("html, body").animate({
            scrollTop: 200
        }, 600);
        return false;
    });
  // Scroll To Top

  //CONTACT FORM

  $('#contact-form').submit(function (e) {
    e.preventDefault();
    // UI ELEMENTS
    const name = document.querySelector('#name');
    const email = document.querySelector('#email');
    const message = document.querySelector('#message');

    const sendBtn = document.querySelector('.contact-btn');
    const loader = document.querySelector('.sending-gif');
    const msg = document.querySelector('.success-msg');

    //   Clear Fields
    function clear() {
      name.value = '';
      email.value = '';
      message.value = '';
    }

    //   Loader On, Button Disabled
    loader.style.display = 'block';
    sendBtn.disabled = true;

    $.ajax({
      url: 'mail.php',
      method: 'POST',
      data: {
        name: name.value,
        email: email.value,
        message: message.value
      }
    })
        .done(function (response) {
          console.log(response);
          setTimeout(() => {
            loader.style.display = 'none';
            msg.style.display = 'block';
            setTimeout(() => {
              msg.style.display = 'none';
              sendBtn.disabled = false;
              clear();
            }, 1000);
          }, 1500);
        })
        .error(function () {
          console.log('ERROR');
        });
  });

  // Initialize WOW JS
  new WOW().init();

  //  Disable

   $('body').bind('cut copy paste', function (e) {
     e.preventDefault();
   });
  
   $("body").on("contextmenu",function(e){
     return false;
   });

})(jQuery); // End of use strict