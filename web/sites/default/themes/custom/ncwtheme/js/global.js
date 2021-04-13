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

});

