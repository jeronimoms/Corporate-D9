<?php

namespace Drupal\napo_content_cart;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * General class for Views data hooks.
 */
class NccView {

  use StringTranslationTrait;

  /**
   * Implements hook_views_data_alter().
   */
  public function viewsDataAlter(array &$data) {
    $data['views']['ncc_download'] = [
      'title' => $this->t('Napo download'),
      'group' => $this->t('Content datasource'),
      'field' => [
        'title' => $this->t('Napo download'),
        'help' => $this->t('Download this content'),
        'id' => 'ncc_download',
      ],
    ];
  }

}
