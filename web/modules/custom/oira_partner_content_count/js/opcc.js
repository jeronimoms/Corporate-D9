/**
 * @file
 * JavaScript file for the Text Resize module.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.quicktabsRender = {
    attach: function (context, settings) {
      var $id = window.location.hash;
      $('li > a.quicktabs-loaded').once('quicktabsRender').each(function (a) {
        var $li = $(this).parent();
        if ($li.hasClass($id.replace('#', ''))) {
          $li.addClass('active');
          $(this).click();
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
