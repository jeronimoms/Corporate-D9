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

  //Facets Accordions
  $(".sidebar-first #block-contenttypesearchsite-2 h2").click(function(){
    $(this).toggleClass("active");
    $(this).parent().find('.content').slideToggle();
  });

  $(".sidebar-first #block-topics-2 h2").click(function(){
    $(this).toggleClass("active");
    $(this).parent().find('.content').slideToggle();
  });

  $(".sidebar-first #block-topicsblog h2").click(function(){
    $(this).toggleClass("active");
    $(this).parent().find('.content').slideToggle();
  });

  $(".sidebar-first #block-dateofdirective h2").click(function(){
    $(this).toggleClass("active");
    $(this).parent().find('.content').slideToggle();
  });

  $(".sidebar-first #block-topicsdirectives h2").click(function(){
    $(this).toggleClass("active");
    $(this).parent().find('.content').slideToggle();
  });

  $(".sidebar-first #block-guideline-topics h2").click(function(){
    $(this).toggleClass("active");
    $(this).parent().find('.content').slideToggle();
  });

  $(".sidebar-first #block-seminar-tags h2").click(function(){
    $(this).toggleClass("active");
    $(this).parent().find('.content').slideToggle();
  });

  //View MSD glossary accordion #block-ncwtheme-content > div > div > div > div.view-content.row > div:nth-child(8)
  $(".view-view-glossary > div.view-content.row > h3:nth-child(7)").addClass('active');
  $(".view-view-glossary > div.view-content.row > div:nth-child(8)").css('display','block');
  $(".view-view-glossary h3").click(function(){
    $(this).toggleClass("active");
    $(this).next('.views-view-grid').slideToggle();
  });

  // View Accordions view-board-members
  $(".view-board-members .view-grouping-header").click(function(){
    $(this).toggleClass("active");
    $(this).next('.view-board-members .view-grouping-content').slideToggle();
  });

  // View Accordions view-advisory-groups
  $(".view-advisory-groups .view-grouping-header").click(function(){
    $(this).toggleClass("active");
    $(this).next('.view-advisory-groups .view-grouping-content').slideToggle();
  });

  // View Accordions view-seminar
  $(".view-seminar .view-grouping-header").click(function(){
    $(this).toggleClass("active");
    $(this).next('.view-seminar .view-grouping-content').slideToggle();
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

  //Move Search Blog
  if ($(".move-block-search-blog")[0]) {
    $('.block-views-exposed-filter-blocksearch-blog-page-1').appendTo('.move-block-search-blog');
  }

  //Move infographic filter
  if ($(".views-row.moved-by-jquery")[0]) {
    $('.views-row.moved-by-jquery').prependTo('.page-view-infographic .view-infographic.view-display-id-block_3  .view-content.row');
  }

  //Show input search when click in Search button responsive menu
  $("#block-searchsite").click(function(){
    $('#block-searchsite .btn-primary').addClass('activate');
    $('#block-searchsite .form-search').addClass('activate');
  });

  //Hide the blocks in responsive until the components are loading
  if ($(window).width() < 992) {
    $('#block-languagedropdownswitchercontent').hide();
    $('#block-searchsite').hide();
    $('#block-headermenu').hide();
  }

  //Move the Blocks in responsive and desktop when we resize the page
  $(window).on('resize', function(){
    if ($(window).width() < 992) {
      $('#block-languagedropdownswitchercontent').appendTo('#navbar-main');
      $('#block-languagedropdownswitchercontent').show();
      $('#block-searchsite').appendTo('#navbar-main');
      $('#block-searchsite').show();
      $('#block-headermenu').appendTo('#navbar-main');
      $('#block-headermenu').show();
    }
    if ($(window).width() > 992) {
      $('#block-languagedropdownswitchercontent').appendTo('.region-header-form');
      $('#block-languagedropdownswitchercontent').show();
      $('#block-searchsite').appendTo('.region-header-form');
      $('#block-searchsite').show();
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

  //Breadcumbs - Fix &amp;
  $('.breadcrumb-item a').each(function() {
    var text = $(this).text();
    $(this).text(text.replace('&amp;', '&'));
  });

  //Archivied calls - Add class custom-active in year
  if ($(".view-id-calls.view-display-id-page_1")[0]) {
    let url = $(location).attr('href');
    //The year is in the URL
    let urlKey = url.replace(/\/\s*$/, "").split('/').pop();
    $('.view-display-id-page_1 .call__item a').filter(function () {
      return $(this).text() == urlKey;
    }).addClass('active-custom');
    //Add the active class to the left menu 'Procurement'
    $('.menu-level-1 > li:nth-child(6)').addClass('menu-item--active-trail');
    $('.menu-level-1 > li:nth-child(6) a').addClass('is-active');

  }

  //Hierarchical view
  $('#tree ul').css('display', 'none');
  $("#tree .has-child > .expand_menu").click(function(){
    $(this).toggleClass('expanded');
    $(this).parent('.has-child').find('> .item-list > ul').slideToggle('slow');
  });

  //Tooltip Thesaurus
  if ($(".content-tooltip img")[0]) {
    $('.content-tooltip img').click(function() {
      $(".thesaurus-tooltip").fadeIn(300);
    });
    $('.close-thes-tooltip').click(function() {
      $(".thesaurus-tooltip").fadeOut(300);
    });
  }

  //Display clear filter button if url has parameter
  if ($("#edit-reset")[0]) {
    let url = window.location.href;
    if(url.includes('?')){
      $('#edit-reset').addClass('custom-active');
      $('.views-exposed-form').addClass('custom-active-filter');
    }
  }

  //Remove equal elements in MSD Glosaary filter
  if ($(".view-msd-glossary")[0]) {
    var seen = {};
    $('.term-glosssary-msd-letter').each(function() {
      var txt = $(this).text();
      if (seen[txt])
        $(this).remove();
      else
        seen[txt] = true;
    });
    //Scroll up the anchor
    $(".term-glosssary-msd-letter").click(function(event){
      setTimeout(function() {
        $('html,body').animate({
          scrollTop: $(window).scrollTop() -500
        });
      }, 100);
    });
  }

});

//Move Language Selector to the menu responsive
(function($) {
  $(window).on('load', function() {
    if ($(window).width() < 992) {
      $('#block-languagedropdownswitchercontent').appendTo('#navbar-main');
      $('#block-languagedropdownswitchercontent').show();
      $('#block-searchsite').appendTo('#navbar-main');
      $('#block-searchsite').show();
      $('#block-headermenu').appendTo('#navbar-main');
      $('#block-headermenu').show();
    }
  });
})(jQuery);
