<?php

namespace Drupal\ncw_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Skip on empty, including multivalue fields.
 *
 * This is needed for text fields and many fields on Drupal sources, because
 * even if the cardinality is set to 1, the field data is still available in a
 * single-item array.
 *
 * @MigrateProcessPlugin(
 *   id = "skip_on_empty_multiple",
 *   handle_multiples = TRUE
 * )
 */
class SkipOnEmptyMultiple extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function process($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!is_array($value)) {
      ksm($value);
      if (empty($value)) {
        throw new MigrateSkipProcessException();
      }
    }
    return $value;
  }

}
