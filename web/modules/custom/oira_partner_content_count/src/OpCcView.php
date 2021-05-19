<?php

namespace Drupal\oira_partner_content_count;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * General class for Views data hooks.
 */
class OpCcView {

  use StringTranslationTrait;

  /**
   * Implements hook_views_data_alter().
   */
  public function viewsDataAlter(array &$data) {
    $data['node']['node_type_flagger'] = [
      'title' => $this->t('Node type flagger'),
      'group' => $this->t('Content'),
      'field' => [
        'title' => $this->t('Node type flagger'),
        'help' => $this->t('Flags a specific node type.'),
        'id' => 'node_type_flagger',
      ],
    ];
  }

}
