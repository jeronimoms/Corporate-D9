/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.ncwtheme = {
    attach: function (context, settings) {

    }
  };

})(jQuery, Drupal);


jQuery(document).ready(function($){
  $("#block-ncwtheme-main-menu .menu-item a").after("<span class='mean-expand'>&nbsp;</span>");
  $('.mean-expand').click(function(){
    $(this).parent().children('div').children('ul').slideToggle();
    $(this).toggleClass('active');
  });
})

jQuery(document).ready(function($) {
  // Accordions
  $(".wysiwyg_accordion h3:nth-child(1)").addClass('active');
  $(".wysiwyg_accordion div:nth-child(2)").css('display','block');
  $(".wysiwyg_accordion h3").click(function(){
      $(this).toggleClass("active");
      $(this).next('.wysiwyg_accordion_panel').slideToggle();
  });

  $(".sidebar-second .view-grouping .view-grouping-header").click(function(){
    $(this).toggleClass("active");
    $(this).next('.view-grouping-content').slideToggle();
  });


  // Text resize
  $('#_biggify').on('click', function() {
    var fontSize = $('html').css('font-size');
    var newFontSize = parseInt(fontSize)+1;

    $('html').css('font-size', newFontSize+'px')
  })

  $('#_smallify').on('click', function() {
    var fontSize = $('html').css('font-size');
    var newFontSize = parseInt(fontSize)-1;

    $('html').css('font-size', newFontSize+'px')
  })

  $('#_reset').on('click', function() {
    $('html').css('font-size', '16px')
  })

  //Move AddToAny to the bottom page
  if ($(".move-add-to-any")[0]) {
    $('.addtoany_list').appendTo('.move-add-to-any');
  }

  //Show input search when click in Search button responsive menu
  $("#block-ncwtheme-search").click(function(){
    $('#block-ncwtheme-search .btn-primary').addClass('activate');
    $('#search-block-form .form-search').addClass('activate');
  });

  //Hide the blocks in responsive until the components are loading
  if ($(window).width() < 992) {
    $('#block-languagedropdownswitchercontent').hide();
    $('#block-ncwtheme-search').hide();
    $('#block-headermenu').hide();
  }

  //Move the Blocks in responsive and desktop when we resize the page
  $(window).on('resize', function(){
    if ($(window).width() < 992) {
      $('#block-languagedropdownswitchercontent').appendTo('#navbar-main');
      $('#block-languagedropdownswitchercontent').show();
      $('#block-ncwtheme-search').appendTo('#navbar-main');
      $('#block-ncwtheme-search').show();
      $('#block-headermenu').appendTo('#navbar-main');
      $('#block-headermenu').show();
    }
    if ($(window).width() > 992) {
      $('#block-languagedropdownswitchercontent').appendTo('.region-header-form');
      $('#block-languagedropdownswitchercontent').show();
      $('#block-ncwtheme-search').appendTo('.region-header-form');
      $('#block-ncwtheme-search').show();
      $('#block-headermenu').appendTo('.region-header-links');
      $('#block-headermenu').show();
    }
  });

  //Left menu in sidebar first
  $(".sidebar-first .menu-level-1 .menu-item--expanded > a").after("<span class='mean-expand-custom no-active'>&nbsp;</span>");
  $('.sidebar-first .menu-level-1 .mean-expand-custom').click(function(){
    $(this).parent().children('div').children('ul').slideToggle();
    $(this).toggleClass('active');
    $(this).toggleClass('no-active');
  });

});

//Move Language Selector to the menu responsive
(function($) {
  $(window).on('load', function() {
    if ($(window).width() < 992) {
      $('#block-languagedropdownswitchercontent').appendTo('#navbar-main');
      $('#block-languagedropdownswitchercontent').show();
      $('#block-ncwtheme-search').appendTo('#navbar-main');
      $('#block-ncwtheme-search').show();
      $('#block-headermenu').appendTo('#navbar-main');
      $('#block-headermenu').show();
    }
  });
})(jQuery);
