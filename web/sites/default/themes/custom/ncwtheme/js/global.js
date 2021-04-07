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

  // Pager index
  $('.pagination').each(function () {
    let itemLast = $(this).find('[title="Go to last page"]').length;
    if(itemLast>0){
      $(this).find('[title="Go to last page"]').closest('.page-item').addClass('last');
    }
    let itemFirst = $(this).find('[title="Go to first page"]').length;
    if(itemFirst>0){
      $(this).find('[title="Go to first page"]').closest('.page-item').addClass('first');
    }

    let itemPrev = $(this).find('[title="Go to previous page"]').length;
    if(itemPrev>0){
      $(this).find('[title="Go to previous page"]').closest('.page-item').addClass('prev');
    }
  });

});

