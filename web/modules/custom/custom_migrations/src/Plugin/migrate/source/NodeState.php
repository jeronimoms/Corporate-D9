<?php

namespace Drupal\custom_migrations\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d7\Node;

/**
 * Drupal 7 nodes with moderation state control.
 *
 * @MigrateSource(
 *   id = "d7_node_moderation_state"
 * )
 */
class NodeState extends Node {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (!parent::prepareRow($row)) {
      return FALSE;
    }

    // If the item is published, we should set the content moderation state to
    // active.
    if ($row->get('status') == 1) {
      $state = 'published';
    }
    else {
      $state = 'draft';
    }
    // Set the Moderation State on the source for processing.
    $row->setSourceProperty('moderation_state', $state);

    return parent::prepareRow($row);
  }

}
