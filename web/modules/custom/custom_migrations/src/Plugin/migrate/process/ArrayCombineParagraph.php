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
 *   id = "array_combine_paragraph"
 * )
 */
class ArrayCombineParagraph extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($this->configuration['delimiter'])) {
      throw new MigrateException('delimiter is empty');
    }

    $strict = array_key_exists('strict', $this->configuration) ? $this->configuration['strict'] : TRUE;
    $values = $value[1];
    if ($strict && !is_string($values)) {
      throw new MigrateException(sprintf('%s is not a string', var_export($values, TRUE)));
    }
    elseif (!$strict) {
      // Check if the incoming value can cast to a string.
      $original = $values;
      if (!is_string($original) && ($original != ($values = @strval($values)))) {
        throw new MigrateException(sprintf('%s cannot be casted to a string', var_export($original, TRUE)));
      }
      // Empty strings should be exploded to empty arrays.
      if ($values === '') {
        return [];
      }
    }

    $limit = isset($this->configuration['limit']) ? $this->configuration['limit'] : PHP_INT_MAX;
    $values = explode($this->configuration['delimiter'], $values, $limit);

    $new_array = [];
    if (is_array($values)) {
      foreach ($values as $val) {
        $new_array[] = [
          $this->configuration['key1'] => $value[0],
          $this->configuration['key2'] => $val,
        ];
      }
    }

    return $new_array;
  }

}
