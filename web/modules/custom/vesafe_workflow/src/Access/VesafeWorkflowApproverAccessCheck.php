<?php

namespace Drupal\vesafe_workflow\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\vesafe_workflow\VesafeWorkFlowHelper;

class VesafeWorkflowApproverAccessCheck implements AccessInterface {

  public function access(AccountInterface $account) {
    // Get the helper object.
    $helper = $this->getVesafeHelper();

    // By default we have to deny the access to all users that thay have the approver role.
    if (array_search('approver', $account->getRoles())) {
      // List of current approvers.
      $users = $helper->getModerationList('approvers');
      foreach ($users as $user) {
        if ($user->user_id == $account->id()) {
          // If current approver is in the list, then allow the access.
          return AccessResult::allowed();
        }
      }
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

  /**
   * Gets Vesafe Helper object.
   *
   * @return \Drupal\vesafe_workflow\VesafeWorkFlowHelper
   *   The object.
   */
  public function getVesafeHelper() {
    return \Drupal::service('vesafe_workflow.helper');
  }
}
