<?php

namespace Drupal\custom_commerce_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 *
 * @code
 * process:
 *   field_date:
 *     plugin: format_number
 *     decimals: 2
 *     decimals_point: '.'
 *     thousands_sep: ''
 *     from_decimals_point: ','
 *     source: weight
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "format_number",
 * )
 */
class FormatNumber extends ProcessPluginBase {
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($value)) {
      return '';
    }

    // Validate the configuration.
    if (isset($this->configuration['decimals'])) {
      $decimals = $this->configuration['decimals'];
    } else {
      $decimals = 2;
    }

    if (isset($this->configuration['decimals_point'])) {
      $decimals_point = $this->configuration['decimals_point'];
    } else {
      $decimals_point = '.';
    }

    if (isset($this->configuration['thousands_sep'])) {
      $thousands_sep = $this->configuration['thousands_sep'];
    } else {
      $thousands_sep = '';
    }

    if (isset($this->configuration['from_decimals_point'])) {
      $from_decimals_point = $this->configuration['from_decimals_point'];
      $value = str_replace($from_decimals_point, $decimals_point, $value);
    }

    $value =  number_format($value, $decimals, $decimals_point, $thousands_sep);


    return $value;
  }
}
