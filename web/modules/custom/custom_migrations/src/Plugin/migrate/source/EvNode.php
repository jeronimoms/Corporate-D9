<?php

namespace Drupal\custom_migrations\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d7\Node;

/**
 * Drupal 7 node source from database.
 *
 * @MigrateSource(
 *   id = "ev_d7_node"
 * )
 */
class EvNode extends Node {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (!parent::prepareRow($row)) {
      return FALSE;
    }

    // Get the WAN control access.
    $workbench_access = $this->select('workbench_access_node', 'wan')
      ->fields('wan', ['access_id'])
      ->condition('nid', $row->getSourceProperty('nid'))
      ->execute()
      ->fetchCol();
    if (!empty($workbench_access[0])){
      if ($workbench_access[0] !== 'section') {
        $row->setSourceProperty('workbench_access', [0 => ['tid' => $workbench_access[0]]]);
      }
    }

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = parent::fields();
    $fields['workbench_access'] = $this->t('The workbench access field for this node.');
    return $fields;
  }

}
