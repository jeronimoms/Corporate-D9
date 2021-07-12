<?php

namespace Drupal\osha_workflow;

use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * General class toolbar hook.
 */
class VwToolBar {

  use StringTranslationTrait;

  /**
   * Hook bridge.
   *
   * @return array
   *   The my workbench toolbar items render array.
   *
   * @see hook_toolbar()
   */
  public function toolBar() {
    $items['osha_workflow'] = [
      '#type' => 'toolbar_item',
      'tab' => [
        '#type' => 'link',
        '#title' => $this->t('My workbench'),
        '#url' => Url::fromRoute('content_moderation.admin_moderated_content'),
      ],
      '#weight' => -15,
    ];
    return $items;
  }

}
