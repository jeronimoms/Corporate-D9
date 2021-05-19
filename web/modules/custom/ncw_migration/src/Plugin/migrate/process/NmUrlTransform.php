<?php

namespace Drupal\ncw_migration\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This plugin transform the url of the image to osha url.
 *
 * @MigrateProcessPlugin(
 *   id = "nm_url_transform",
 * )
 */
class NmUrlTransform extends ProcessPluginBase {


  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $nn = str_replace('public://', '', $value);

    $osha_patch = 'https://osha.europa.eu/sites/default/files/' . $nn;

    return $osha_patch;
  }

}
