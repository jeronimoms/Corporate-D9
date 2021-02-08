<?php

namespace Drupal\custom_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Splits the source string into an array of strings, using a delimiter.
 *
 * This plugin creates an array of strings by splitting the source parameter on
 * boundaries formed by the delimiter.
 *
 * Available configuration keys:
 * - source: The source string.
 * - delimiter: (optional)The boundary string.
 *
 * Example:
 *
 * @code
 * process:
 *   bar:
 *     plugin: implode
 *     source: foo
 *     delimiter: ','
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "implode"
 * )
 */
class Implode extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $delimiter = ($this->configuration['delimiter']) ? $this->configuration['delimiter'] : '';
    if (!is_array($value)) {
      throw new MigrateException(sprintf('%s is not a array', var_export($value, TRUE)));
    }

    return implode($this->configuration['delimiter'], $value);
  }

}
