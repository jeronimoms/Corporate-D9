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
 *   id = "change_value",
 * )
 */
class ChangeValue extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($value)) {
      return '';
    }
    if (empty($this->configuration['value_old'])) {
      throw new MigrateException('Change value plugin is missing value_old configuration.');
    }
    if (empty($this->configuration['value_new'])) {
      throw new MigrateException('Change value plugin is missing value_new configuration.');
    }

    if ($value == $this->configuration['value_old']) {
      $value = $this->configuration['value_new'];
    }

    return $value;
  }

}
