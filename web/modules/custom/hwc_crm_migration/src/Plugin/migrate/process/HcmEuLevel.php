<?php

namespace Drupal\hwc_crm_migration\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * This plugin find the term by name and vocabulary.
 * @code
 * process:
 *   destination_field:
 *     plugin: hcm_eu_level
 *     source: source_field
 * @endcode
 * @MigrateProcessPlugin(
 *   id = "hcm_eu_level",
 * )
 */
class HcmEuLevel extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($value)) {
      return 0;
    }


    return 1;
  }

}
