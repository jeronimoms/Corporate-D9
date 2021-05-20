<?php

namespace Drupal\hwc_crm_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\hwc_crm_migration\Plugin\migrate\process\HcmTaxonomyTermType;

/**
 * This plugin find the term by name and vocabulary.
 * @code
 * process:
 *   destination_field:
 *     plugin: hcm_taxonomy_term_name
 *     source: source_field
 *     vocabulary: vocabulary_name
 *     create: false
 * @endcode
 * @MigrateProcessPlugin(
 *   id = "hcm_taxonomy_term_name",
 * )
 */
class HcmTaxonomyTermName extends HcmTaxonomyTermType {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $partner_type = $row->getSourceProperty('field_partner_type');
    if (!empty($partner_type)) {
      if ($partner_type == 'Sectoral social partners') {
        return [
          'target_id' => 256,
        ];
      }
    }

    return parent::transform($value, $migrate_executable, $row, $destination_property);
  }

}
