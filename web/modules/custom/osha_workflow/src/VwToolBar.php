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
   * @param $items
   *   All toolbar items.
   *
   * @see hook_toolbar_alter()
   */
  public function toolBar(&$items) {
    $items['workbench']['tab']['#title'] = "My Workbench";
  }

}
