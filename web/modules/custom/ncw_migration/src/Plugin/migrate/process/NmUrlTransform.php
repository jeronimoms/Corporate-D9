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
class NmUrlTransform extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The Config Factory manager.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );

    $instance->configFactory = $container->get('config.factory');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $nn = str_replace('public://', '', $value);

    if ($this->configFactory->get('config_split.config_split.local')->get('status')) {
      $base = 'http://ncw.ddev.site';
    }

    if ($this->configFactory->get('config_split.config_split.staging')->get('status')) {
      $base = 'https://testd9.osha.europa.eu';
    }

    if ($this->configFactory->get('config_split.config_split.production')->get('status')) {
      $base = 'https://osha.europa.eu/';
    }

    $osha_patch = $base. '/sites/default/files/' . $nn;

    return $osha_patch;
  }

}
