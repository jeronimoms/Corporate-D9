<?php

namespace Drupal\custom_migrations\Plugin\migrate\source;

use Drupal\content_translation\Plugin\migrate\source\I18nQueryTrait;
use Drupal\migrate\Row;
use Drupal\taxonomy\Plugin\migrate\source\d7\Term;

/**
 * Drupal 7 locale terms.
 *
 * @MigrateSource(
 *   id = "d7_taxonomy_term_custom"
 * )
 */
class TaxonomyTerm extends Term {

  use I18nQueryTrait;

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'alias' => $this->t('The taxonomy term alias.'),
    ];
    return parent::fields() + $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (!parent::prepareRow($row)) {
      return FALSE;
    }

    // Set alias by lang.
    $alias = $this->select('url_alias', 'ua')
      ->fields('ua', ['alias'])
      ->where("ua.source = CONCAT('taxonomy/term/', :tid) AND language = :lang", [
        ':tid' => $row->getSourceProperty('tid'),
        ':lang' => 'en'
      ])
      ->execute()
      ->fetchCol();

    if (isset($alias[0])) {
      $alias_text = $alias[0];
    }
    else {
      $alias_text = '';
    }

    $row->setSourceProperty('alias', '/' . $alias_text);

    return $row;
  }

}
