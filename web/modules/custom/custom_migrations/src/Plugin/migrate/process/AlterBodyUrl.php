<?php

namespace Drupal\custom_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * This plugin replace body value from new value.
 *
 * @MigrateProcessPlugin(
 *   id = "alter_body_url",
 * )
 */
class AlterBodyUrl extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $source = $this->configuration['source'];

    // e.g.: 'sites/default'.
    $site_path = \Drupal::service('site.path');
    $site_path = explode('/', $site_path);
    $site_name = $site_path[1];
    $themes_final_url = '/themes/custom/' . $site_name;
    $files_final_url = '/sites/' . $site_name . '/files/';
    $sites_default = ['https://10.25.133.111:8280/sites/default/files/',
      'http://10.25.133.111:8280/sites/default/files/',
      '//10.25.133.111:8280/sites/default/files/',
      'https://www.ree.es/sites/default/files/',
      'http://www.ree.es/sites/default/files/',
      '//www.ree.es/sites/default/files/',
      '/sites/default/files/',
    ];
    $sites_themes = ['https://www.ree.es/sites/all/themes/webree',
      'http://www.ree.es/sites/all/themes/webree',
      '//www.ree.es/sites/all/themes/webree',
      '/sites/all/themes/webree',
    ];

    $value['value'] = str_replace($sites_themes, $themes_final_url, $value['value']);
    $value['value'] = str_replace($sites_default, $files_final_url, $value['value']);

    return $value;
  }

}
