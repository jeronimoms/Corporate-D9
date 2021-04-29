<?php

namespace Drupal\ncw_migration\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipProcessException;

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
    throw new MigrateSkipProcessException();
  }

}
