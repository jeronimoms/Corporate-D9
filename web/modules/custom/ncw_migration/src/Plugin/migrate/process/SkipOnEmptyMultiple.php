<?php

namespace Drupal\ncw_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\migrate\process\SkipOnEmpty;
use Drupal\migrate\Row;

/**
 * Skip on empty, including multivalue fields.
 *
 * This is needed for text fields and many fields on Drupal sources, because
 * even if the cardinality is set to 1, the field data is still available in a
 * single-item array.
 *
 * @MigrateProcessPlugin(
 *   id = "skip_on_empty_multiple",
 *   handle_multiples = TRUE
 * )
 */
class SkipOnEmptyMultiple extends SkipOnEmpty {

  /**
   * Indicate whether there are multiple values for this field.
   *
   * @var bool
   */
  protected $multiple;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Set multiple.
    $this->multiple = is_array($value) && !empty($value);

    return parent::transform($value, $migrate_executable, $row, $destination_property);
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return $this->multiple;
  }

}
