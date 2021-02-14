<?php

namespace Drupal\vesafe_workflow\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * General class for custom access check for approvers.
 */
class VwListAccessCheck implements AccessInterface {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    // Get the helper object.
    $helper = $this->getVesafeHelper();
    $node = $helper->getLastRevisionNode();
    if ($node->bundle() == 'good_practice' || $node->bundle() == 'key_article') {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

  /**
   * Gets Vesafe Helper object.
   *
   * @return \Drupal\vesafe_workflow\VwHelper
   *   The object.
   */
  public function getVesafeHelper() {
    return \Drupal::service('vesafe_workflow.helper');
  }

}
