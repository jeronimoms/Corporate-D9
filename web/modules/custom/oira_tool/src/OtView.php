<?php

namespace Drupal\oira_tool;

use Drupal\views\ViewExecutable;
use Drupal\search_api\Plugin\views\ResultRow;

/**
 * General class for entity hooks.
 */
class OtView {

  /**
   * Implements hook_views_pre_render().
   */
  public function viewsPreRender(ViewExecutable $view) {
    if (($view->id() == 'country_partner_content' && $view->current_display == 'block_3') || $view->id() == 'oira_ws' || ($view->id() == 'tools')) {

      $current_lang = \Drupal::languageManager()->getCurrentLanguage()->getId();

      if ($current_lang == 'en') {
        return;
      }

      if ($current_lang == 'pt-pt') {
        $current_lang = 'pt';
      }

      foreach ($view->result as $value) {
        // The entity dependly the type of view.
        /** @var \Drupal\node\Entity\Node $entity */
        if ($value instanceof ResultRow) {
          $entity = $value->_object->getEntity();
        }
        else {
          $entity = $value->_entity;
        }

        $n_title = '';
        $n_description = '';

        // Get the values of alternative language.
        $alt_lang = $entity->get('field_language')->getString();
        $alt_title = $entity->get('field_alternative_title')->getString();
        $alt_descrition = $entity->get('field_alternative_body')->getValue();

        // Store them if are not empty.
        if ($current_lang == $alt_lang) {
          if (!empty($alt_title)) {
            $n_title = $alt_title;
          }
          if (!empty($alt_descrition)) {
            $n_description = $alt_descrition;
          }
        }

        // Get the values of third language.
        $third_lang = $entity->get('field_third_language')->getString();
        $third_title = $entity->get('field_third_title')->getString();
        $third_description = $entity->get('field_third_description')->getValue();

        // Store them if are not empty.
        if ($current_lang == $third_lang) {
          if (!empty($third_title)) {
            $n_title = $third_title;
          }
          if (!empty($third_description)) {
            $n_description = $third_description;
          }
        }

        // Set the translations as normal fields.
        if (!empty($n_title)) {
          $entity->set('title', $n_title);
        }

        if (!empty($n_description)) {
          $entity->set('body', $n_description);
        }
      }
    }
  }

}
