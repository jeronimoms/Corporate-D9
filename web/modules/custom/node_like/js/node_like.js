/**
 * @file
 * Node like behaviors.
 */
(function ($, Drupal) {

  'use strict';

  var behaviors = Drupal.behaviors;

  $.fn.nlToolTip = function (id) {
    $(id).attr('data-original-title', 'Thank you');
    $(id).tooltip();
    $(id).mouseover();
    $(id).tooltip('show');
  };

  behaviors.nlTooltip = {
    attach: function (context) {
      $('a[class*="node_like-like-"]').tooltip();
    }
  }

})(jQuery, Drupal);
