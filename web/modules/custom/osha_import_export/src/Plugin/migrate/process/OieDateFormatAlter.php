<?php

namespace Drupal\osha_import_export\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateException;

/**
 * This plugin change value old by new value.
 *
 * @MigrateProcessPlugin(
 *   id = "oie_date_format_alter",
 * )
 */
class OieDateFormatAlter extends ProcessPluginBase {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $date = date("Y-m-d", strtotime($value));
    $time = date("H:i:s", strtotime($value));

    return $date . "T" . $time;
  }
}
