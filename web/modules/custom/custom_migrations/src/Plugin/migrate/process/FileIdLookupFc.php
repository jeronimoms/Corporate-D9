<?php

namespace Drupal\custom_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\migrate\process\MigrationLookup;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "file_id_lookup_fc"
 * )
 */
class FileIdLookupFc extends MigrationLookup {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($value['value']) {
      $fid = $value['value'];
      $query = \Drupal::database()->select('migrate_file_to_media_mapping', 'map');
      $query->fields('map');
      $query->condition('fid', $fid, '=');
      $result = $query->execute()->fetchObject();

      if ($result) {
        // If the record has an existing media entity, return it.
        if (!empty($result->media_id)) {
          return $result->media_id;
        }
        return parent::transform($result->target_fid, $migrate_executable, $row, $destination_property);
      }
    }
    throw new MigrateSkipRowException();
  }

}
