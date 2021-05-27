<?php

namespace Drupal\ncw_migration\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\MigrateSkipProcessException;

/**
 * This plugin get the content mdoeration.
 *
 * @MigrateProcessPlugin(
 *   id = "nm_content_moderation",
 * )
 */
class NmContentModeration extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($value == 1) {
      $state = 'published';
    }
    else {
      $state = 'draft';
    }

    return $state;
  }

}
