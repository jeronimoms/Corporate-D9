<?php

namespace Drupal\custom_migrations\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * This plugin remove the html tags.
 *
 * @MigrateProcessPlugin(
 *   id = "strip_tags",
 * )
 */
class StripTags extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return strip_tags($value);
  }

}
