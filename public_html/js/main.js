
!function(t){"use strict";t(window).on("load",function(){t("#preloader").fadeOut(),t("#status").fadeOut(9e3)}),


jQuery(".ttm-fbar-btn > a.ttm-fbar-btn-link").on("click",function(){return jQuery(this).closest(".ttm-fbar").hasClass("themetechmount-fbar-position-default")&&("none"==jQuery(".ttm-fbar-box-w").css("display")?(jQuery(".ttm-fbar-open-icon",this).fadeOut(),jQuery(".ttm-fbar-close-icon",this).fadeIn(),jQuery(".ttm-fbar-box-w").slideDown()):(jQuery(".ttm-fbar-open-icon",this).fadeIn(),jQuery(".ttm-fbar-close-icon",this).fadeOut(),jQuery(".ttm-fbar-box-w").slideUp())),!1}),

// Ensure jQuery is properly defined and used
jQuery(document).ready(function ($) {
    $(".ttm-fbar-close, .ttm-fbar-btn > a.ttm-fbar-btn-link, .ttm-float-overlay").on("click", function() {
        $(".ttm-fbar-box-w").toggleClass("animated");
        $(".ttm-float-overlay").toggleClass("animated");
        $(".ttm-fbar-btn").toggleClass("animated");
    });

    $(window).scroll(function() {
        if (matchMedia("only screen and (min-width: 1200px)").matches) {
            if ($(window).scrollTop() >= 50) {
                $(".ttm-stickable-header").addClass("fixed-header");
                $(".ttm-stickable-header").addClass("visible-title");
            } else {
                $(".ttm-stickable-header").removeClass("fixed-header");
                $(".ttm-stickable-header").removeClass("visible-title");
            }
        }
    });

    $("ul li:has(ul)").addClass("has-submenu");
    $("ul li ul").addClass("sub-menu");

    $("ul.dropdown li").on({
        mouseover: function() {
            $(this).addClass("hover");
        },
        mouseout: function() {
            $(this).removeClass("hover");
        }
    });

    var e = $("#menu");
    var a = $("#menu-toggle-form");
    var s = $(".has-submenu > a");

    a.on("click", function() {
        a.toggleClass("active");
        e.toggleClass("active");
    });

    s.on("click", function(e) {
        e.preventDefault();
        $(this).toggleClass("active").next("ul").toggleClass("active");
    });

   
});



jQuery(".progress").each(function(){jQuery(this).find(".progress-bar").animate({width:jQuery(this).attr("data-value")},6e3)}),t(".ttm-tabs").each(function(){t(this).children(".content-tab").children().hide(),t(this).children(".content-tab").children().first().show(),t(this).find(".tabs").children("li").on("click",function(e){var a=t(this).index(),s=t(this).siblings().removeClass("active").parents(".ttm-tabs").children(".content-tab").children().eq(a);s.addClass("active").fadeIn("slow"),s.siblings().removeClass("active"),t(this).addClass("active").parents(".ttm-tabs").children(".content-tab").children().eq(a).siblings().hide(),e.preventDefault()})}),

$(document).ready(function() {
  // Your toggle code here
  $(".toggle")
    .eq(0)
    .addClass("active")
    .find(".toggle-content")
    .css("display", "block");
  
  $(".accordion .toggle-title").on("click", function() {
    $(this).siblings(".toggle-content").slideToggle("fast");
    $(this).parent().toggleClass("active");
    $(this)
      .parent()
      .siblings()
      .children(".toggle-content:visible")
      .slideUp("fast");
    $(this)
      .parent()
      .siblings()
      .children(".toggle-content:visible")
      .parent()
      .removeClass("active");
  });
});


t(function(){})}(jQuery);

document.addEventListener("DOMContentLoaded", function () {
    let heading1 = document.getElementById("heading1");
    let heading2 = document.getElementById("heading2");
    let currentHeading = 1;

    // If heading1 is missing, default to heading2
    if (!heading1 && heading2) {
        heading2.classList.add("show");
        currentHeading = 2;
    } else if (heading1) {
        heading1.classList.add("show");
        currentHeading = 1;
    }

    // Toggle function only if at least one heading exists
    if (heading1 || heading2) {
        setInterval(() => {
            if (currentHeading === 1 && heading2) {
                heading1.classList.remove("show");
                heading2.classList.add("show");
                currentHeading = 2;
            } else if (currentHeading === 2 && heading1) {
                heading2.classList.remove("show");
                heading1.classList.add("show");
                currentHeading = 1;
            }
        }, 6000); // 6 seconds
    }
});

