<?php

namespace Drupal\custom_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Uses the strtolower() method on a source string.
 *
 * @MigrateProcessPlugin(
 *   id = "strtolower"
 * )
 *
 * @code
 * process:
 *   field_body:
 *     plugin: strtolower
 *     source: body
 * @endcode
 */
class StrToLower extends ProcessPluginBase {
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    print_r($row);
    echo "StrToLower  \n";
    echo "value ini=";print_r($value); echo " \n";
    // Use C-locale for ASCII-only.
    $value = mb_strtolower($value);
    echo "value mid=";print_r($value); echo " \n";
    // Case flip Latin-1 accented letters.
    $value = preg_replace_callback('/\xC3[\xA0-\xB6\xB8-\xBE]/', '\Drupal\Component\Utility\Unicode::caseFlip', $value);
    echo "value fin="; print_r($value); echo " \n";die;
    return $value;
  }
}


