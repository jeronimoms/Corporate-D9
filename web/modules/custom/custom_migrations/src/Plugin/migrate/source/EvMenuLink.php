<?php

namespace Drupal\custom_migrate\Plugin\migrate\source;

use Drupal\menu_link_content\Plugin\migrate\source\MenuLink;

/**
 * Drupal 7 node source from database.
 *
 * @MigrateSource(
 *   id = "ev_menu_link"
 * )
 */
class EvMenuLink extends MenuLink {

  /**
   * {@inheritdoc}
   */
  public function query() {

    $query = $this->select('menu_links', 'ml')
      ->fields('ml')
      ->fields('n', ['tnid']);
    $and = $query->orConditionGroup()
      ->condition('ml.module', 'menu')
      ->condition('ml.router_path', ['admin/build/menu-customize/%', 'admin/structure/menu/manage/%'], 'NOT IN');
    $condition = $query->andConditionGroup()
      ->condition('n.status', 1)
      ->condition('ml.customized', 1)
      ->condition($and);
    $query->condition($condition);
    $query->leftJoin('menu_links', 'pl', 'ml.plid = pl.mlid');
    $query->join('node', 'n', "concat('node/', n.nid) = ml.link_path");
    $query->addField('pl', 'link_path', 'parent_link_path');
    $query->orderBy('ml.depth');
    $query->orderby('ml.mlid');

    return $query;

  }

  /**
   * This function returns the translation.
   */
  public function fields() {
    $fields = parent::fields();
    $fields['tnid'] = t('The translation id of a node');

    return $fields;
  }

}
