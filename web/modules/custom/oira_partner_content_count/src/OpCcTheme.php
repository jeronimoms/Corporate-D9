<?php

namespace Drupal\oira_partner_content_count;

/**
 * General class for Theme hooks.
 */
class OpCcTheme {

  /**
   * Implements hook_theme().
   */
  public function theme($existing, $type, $theme, $path) {
    return [
      'opcc_field_node_type_flagger' => [
        'variables' => ['counters' => NULL],
      ],
    ];
  }

}
