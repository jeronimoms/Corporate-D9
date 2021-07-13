<?php

namespace Drupal\osha_workflow;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * General class for custom permissions.
 */
class VwPermissions {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * Get the osha Workflow permissions.
   *
   * @return array
   *   The array with the permissions.
   */
  public function getPermissions() {
    $permissions = [];

    $config = $this->configFactory()->getEditable('osha_workflow.general');
    $lists = $config->get('lists');

    foreach ($lists['list'] as $list) {
      $name = $list['name'];
      // Set list permissions.
      $permissions["administer " . strtolower($name)] = [
        'title' => $this->t('Administer @name', ['@name' => $name]),
      ];
    }

    return $permissions;
  }

  /**
   * Get the ConfigFactory object.
   *
   * @return \Drupal\Core\Config\ConfigFactoryInterface
   *   The the ConfigFactory object.
   */
  public function configFactory() {
    return \Drupal::configFactory();
  }

}
