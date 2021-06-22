/**
 * @file
 * This file is loaded in Download Centre form.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.nccForm = {
    attach: function (context, settings) {
      $('#edit-download-all').on('click', function (){
        console.log('yolo');
      })
    }
  };
})(jQuery, Drupal, drupalSettings);
