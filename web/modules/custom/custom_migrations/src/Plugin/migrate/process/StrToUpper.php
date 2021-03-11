<?php

namespace Drupal\custom_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Uses the strtoupper() method on a source string.
 *
 * @MigrateProcessPlugin(
 *   id = "strtoupper"
 * )
 *
 * @code
 * process:
 *   field_body:
 *     plugin: strtoupper
 *     source: body
 * @endcode
 */
class StrToUpper extends ProcessPluginBase {
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Use C-locale for ASCII-only uppercase.
    $value = strtoupper($value);
    // Case flip Latin-1 accented letters.
    $value = preg_replace_callback('/\xC3[\xA0-\xB6\xB8-\xBE]/', '\Drupal\Component\Utility\Unicode::caseFlip', $value);

    return $value;
  }
}


