<?php

namespace Drupal\vesafe_workflow;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;


class VesafeWorkflowPermissions {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  public function getPermissions() {
    $permissions = [];

    // Administer approvers.
    $permissions["administer approvers"] = [
      'title' => $this->t('Administer Approvers'),
    ];

    // Administer reviewers.
    $permissions["administer reviewers"] = [
      'title' => $this->t('Administer Reviewers'),
    ];

    return $permissions;
  }
}
