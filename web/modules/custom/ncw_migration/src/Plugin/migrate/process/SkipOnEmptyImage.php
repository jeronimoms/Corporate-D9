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
    if ($destination_property == 'field_image/target_id' || $destination_property == 'media') {
      $source_image = $row->getSourceProperty('source_image');
      ksm($source_image);
      if (array_key_exists('ignore', $source_image) && $source_image['ignore'] == TRUE) {
        ksm('nop');
        throw new MigrateSkipProcessException();
      }
    }
    return $value;
  }

}
