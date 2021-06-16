/**
 * @file
 * This file is loaded in Download Centre form.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.nccForm = {
    attach: function (context, settings) {
      console.log('hola');
    }
  };
})(jQuery, Drupal, drupalSettings);
