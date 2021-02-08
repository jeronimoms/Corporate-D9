<?php

namespace Drupal\custom_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateException;

/**
 * This plugin change value old by new value.
 *
 * @MigrateProcessPlugin(
 *   id = "skip_on_error",
 * )
 */
class SkipOnError extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $url_downloadable = get_headers($value);

    // Check the response code.
    if (strpos($url_downloadable[0], '200') !== FALSE) {
      return $value;
    }
    else {
      throw new MigrateException($value . ' Error ' . $url_downloadable[0]);
    }

  }

}
