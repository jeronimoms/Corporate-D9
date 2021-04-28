<?php

namespace Drupal\ncw_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Skip on empty images.
 *
 * @MigrateProcessPlugin(
 *   id = "skip_on_empty_image",
 * )
 */
class SkipOnEmptyImage extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function process($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!is_array($value)) {
      if (empty($value)) {
        throw new MigrateSkipProcessException();
      }
    }
    return $value;
  }

}
