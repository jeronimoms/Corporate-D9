<?php

namespace Drupal\custom_migrations\Plugin\migrate\source;

use Drupal\content_translation\Plugin\migrate\source\I18nQueryTrait;
use Drupal\migrate\Row;
use Drupal\taxonomy\Plugin\migrate\source\d7\Term;

/**
 * Drupal 7 locale terms.
 *
 * @MigrateSource(
 *   id = "d7_locale_terms"
 * )
 */
class LocaleTerms extends Term {

  use I18nQueryTrait;

  /**
   * {@inheritdoc}
   */
  public function query() {
    /** @var \Drupal\Core\Database\Query\Select $query */
    $query = parent::query();
    $query->addField('td', 'language', 'td.language');

    // Add in the translation for the property.
    $query->innerJoin('field_data_name_field', 'lt', 'td.tid = lt.entity_id');
    $query->addField('lt', 'language', 'lt.language');
    $query->addField('lt', 'name_field_value');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (!parent::prepareRow($row)) {
      return FALSE;
    }

    if ($this->moduleExists('title')) {
      $name = $row->getSourceProperty('name_field_value');
      $row->setSourceProperty('name', $name);
    }

    // Override language with ltlanguage.
    $language = $row->getSourceProperty('ltlanguage');
    $row->setSourceProperty('language', $language);

    // Set specific description.
    $desc = $this->select('field_data_description_field', 'fdf')
      ->fields('fdf', ['description_field_value'])
      ->condition('entity_id', $row->getSourceProperty('tid'))
      ->condition('language' , $language)
      ->execute()
      ->fetchCol();
    $row->setSourceProperty('description_field_value', $desc[0]);

    // Set alias by lang.
    $alias = $this->select('url_alias', 'ua')
      ->fields('ua', ['alias'])
      ->where("ua.source = CONCAT('taxonomy/term/', :tid) AND language = :lang", [
        ':tid' => $row->getSourceProperty('tid'),
        ':lang' => 'en'
      ])
      ->execute()
      ->fetchCol();

    if ($alias[0]) {
      $alias_text = $alias[0];
    }
    else {
      $alias_text = '';
    }

    $row->setSourceProperty('alias', '/' . $alias_text);

    // Country images.
    $field_flag = $row->getSourceProperty('field_flag');
    if (isset($field_flag) && isset($field_flag[0])) {
      $field_flag_fid = $field_flag[0]['fid'];
      $image = $this->select('file_managed', 'fm')
        ->fields('fm', ['uri'])
        ->where("fm.fid = :fid", [
          ':fid' => $field_flag_fid,
        ])
        ->execute()
        ->fetchCol();

      if (isset($image[0])) {
        $url = str_replace('public://', '', $image[0]);
        $row->setSourceProperty('field_flag_url', $url);

      }
    }

    // Field images.
    $field_image = $row->getSourceProperty('field_image');
    if (isset($field_image) && isset($field_image[0])) {
      $field_image_fid = $field_image[0]['fid'];
      $image = $this->select('file_managed', 'fm')
        ->fields('fm', ['uri'])
        ->where("fm.fid = :fid", [
          ':fid' => $field_image_fid,
        ])
        ->execute()
        ->fetchCol();

      if (isset($image[0])) {
        $url = str_replace('public://', '', $image[0]);
        $row->setSourceProperty('field_image_url', $url);

      }
    }


    // Set specific include field.
//    $includeField = $this->select('field_data_field_nace_includes', 'fdf')
//      ->fields('fdf', ['field_nace_includes_value'])
//      ->condition('entity_id', $row->getSourceProperty('tid'))
//      ->condition('language' , $language)
//      ->execute()
//      ->fetchCol();
//    if (!empty($includeField[0])){
//      $row->setSourceProperty('includes_field_value', $includeField[0]);
//    }
//
//    // Set specific excluded field.
//    $excludeField = $this->select('field_data_field_nace_excludes', 'fdf')
//      ->fields('fdf', ['field_nace_excludes_value'])
//      ->condition('entity_id', $row->getSourceProperty('tid'))
//      ->condition('language' , $language)
//      ->execute()
//      ->fetchCol();
//    if (!empty($excludeField[0])){
//      $row->setSourceProperty('excludes_field_value', $excludeField[0]);
//    }
//
//    // Set specific include also field.
//    $include_alsoField = $this->select('field_data_field_nace_includes_also', 'fdf')
//      ->fields('fdf', ['field_nace_includes_also_value'])
//      ->condition('entity_id', $row->getSourceProperty('tid'))
//      ->condition('language' , $language)
//      ->execute()
//      ->fetchCol();
//    if (!empty($include_alsoField[0])){
//      $row->setSourceProperty('includes_also_field_value', $include_alsoField[0]);
//    }

    // Set the i18n string table for use in I18nQueryTrait.
    $this->i18nStringTable = 'i18n_string';

    // Save the translation for the property already in the row.
    $property_in_row = $row->getSourceProperty('property');

    // Get the translation for the property not already in the row and save it
    // in the row.
    $property_not_in_row = ($property_in_row == 'name') ? 'description' : 'name';

    $tst = $this->getPropertyNotInRowTranslation($row, $property_not_in_row, 'tid', $this->idMap);

    return $tst;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'language' => $this->t('Language for this term.'),
      'name_field_value' => $this->t('Term name translation.'),
      'description_field_value' => $this->t('Term description translation.'),
//      'includes_field_value' => $this->t('Term includes translation.'),
//      'excludes_field_value' => $this->t('Term excludes translation.'),
//      'includes_also_field_value' => $this->t('Term includes also translation.'),
    ];
    return parent::fields() + $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['language']['type'] = 'string';
    $ids['language']['alias'] = 'lt';
    return parent::getIds() + $ids;
  }

}
