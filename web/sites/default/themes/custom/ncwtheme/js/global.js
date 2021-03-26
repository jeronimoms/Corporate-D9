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

// Accordions

jQuery(document).ready(function($) {

  $(".wysiwyg_accordion h3").click(function(){
    $(this).toggleClass("active");
    $(this).next('.wysiwyg_accordion_panel').slideToggle();
  });


});

