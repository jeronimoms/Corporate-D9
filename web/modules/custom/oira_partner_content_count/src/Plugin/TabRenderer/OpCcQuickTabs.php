<?php

namespace Drupal\oira_partner_content_count\Plugin\TabRenderer;

use Drupal\quicktabs\Entity\QuickTabsInstance;
use Drupal\quicktabs\Plugin\TabRenderer\QuickTabs;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Views;

/**
 * Provides a 'QuickTabs' tab renderer.
 *
 * @TabRenderer(
 *   id = "opcc_quick_tabs",
 *   name = @Translation("Oira Quicktabs"),
 * )
 */
class OpCcQuickTabs extends QuickTabs {

  /**
   * {@inheritdoc}
   */
  public function render(QuickTabsInstance $instance) {

    // Default output.
    $build =  parent::render($instance);

    foreach ($instance->getConfigurationData() as $index => $tab) {
      // Tabs results count.
      $view_content = $tab['content']['view_content'];
      $view_results = views_get_view_result($view_content['options']['vid'], $view_content['options']['display']);
      if (empty($view_results)) {
        if ($index == 0) {
          // If is the first tab and the results are 0, force the view.
          $view = Views::getView($view_content['options']['vid']);
          $view->setDisplay($view_content['options']['display']);
          $build['pages'][$index]['#block']['#type'] = 'view';
          $build['pages'][$index]['#block']['#name'] = $view_content['options']['vid'];
          $build['pages'][$index]['#block']['#display_id'] = $view_content['options']['display'];
          $build['pages'][$index]['#block']['#arguments'] = [];
          $build['pages'][$index]['#block']['#embed'] = FALSE;
          $build['pages'][$index]['#block']['#view'] = $view;
        }
        else {
          // Remove the rest of items with 0 items.
          unset($build[0]['#items'][$index]);
        }
      }
      else {
        if ($index !== 0) {
          /** @var \Drupal\views\ViewExecutable $view */
          $view = $build['pages'][$index]['#block']['#view'];
          $view_id = $build['pages'][$index]['#block']['#display_id'];
          if (isset($view_id)) {
            // Show the counters.
            $view->execute($view_id);
            $build[0]['#items'][$index][0]['#title'] = new TranslatableMarkup('@title (@count)', ['@title' => $tab['title'],'@count' => $view->total_rows]);
          }
        }
      }
    }

    // Include the library.
    $build['#attached']['library'][] = 'oira_partner_content_count/oira_partner_content_count.quicktabs';

    return $build;
  }

}
