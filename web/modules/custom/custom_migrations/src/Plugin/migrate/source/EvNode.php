<?php

namespace Drupal\custom_migrate\Plugin\migrate\source;

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
  public function query() {
    $prefix = 'node/';
    $id_key = 'nid';
    $query = parent::query();
    $query->addExpression("(SELECT CONCAT('/', alias) FROM url_alias WHERE CONCAT('$prefix', n . $id_key) = source ORDER BY pid DESC LIMIT 1)", 'alias');
    // $query->addExpression("CONCAT('/', alias)", 'alias');
    // $query->leftJoin('url_alias', 'ua', "CONCAT('" . $prefix . "', n . $id_key) = ua.source");
    // echo $query->__toString(); die("qqw");
    return $query;
  }

}
