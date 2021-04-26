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
    $('#share-this-on').appendTo('.move-add-to-any');
  }

  //CLick on Search in Responsive menu - Show input seach box
  if ($(".toolbar-tray-horizontal.is-active")[0]) {
    $('#header').addClass('toolbar-tray-open');
  }
  $("#toolbar-item-administration").click(function(){
    $('#header').removeClass('toolbar-tray-open');
    if ($(".toolbar-tray-horizontal.is-active")[0]) {
      $('#header').addClass('toolbar-tray-open');
    }else{
      $('#header').removeClass('toolbar-tray-open');
    }
  });

  $("#block-ncwtheme-search").click(function(){
    $('#block-ncwtheme-search .btn-primary').addClass('activate');
    $('#search-block-form .form-search').addClass('activate');
  });

});

