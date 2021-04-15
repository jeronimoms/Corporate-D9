<?php

namespace Drupal\oira_partner_content_count\Plugin\TabRenderer;

use Drupal\quicktabs\Entity\QuickTabsInstance;
use Drupal\quicktabs\Plugin\TabRenderer\QuickTabs;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;

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
    $pages = $build['pages'];
    $pages_counter = [];

    // Get the num of results of each tab.
    foreach ($pages as $i => $page) {
      if (is_numeric($i) && array_key_exists('#block', $page)) {
        /** @var \Drupal\views\ViewExecutable $view */
        $view = $page['#block']['#view'];
        $view_id = $page['#block']['#display_id'];
        if (isset($view_id)) {
          $view->execute($view_id);
          $pages_counter[] = $view->total_rows;
        }
      }
    }

    // Modify the tab titles with the counter.
    $items = &$build[0]['#items'];
    foreach ($items as $i => $item) {
      /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $title */
      $title = $item[0]['#title'];
      if ($title->render() == $this->t('Show all')->render()) {
        continue;
      }
      $items[$i][0]['#title'] = new TranslatableMarkup('@title (@count)', ['@title' => $items[$i][0]['#title']->render(),'@count' => $pages_counter[$i]]);
    }

    $build['#attached']['library'][] = 'oira_partner_content_count/oira_partner_content_count.quicktabs';

    return $build;
  }

}
