<?php

namespace Drupal\osha_workflow\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * General class for custom access check for approvers.
 */
class VwApproverAccessCheck implements AccessInterface {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    // Return allow if the user has the role "Project Manager".
    if (array_search('project_manager', $account->getRoles())) {
      return AccessResult::allowed();
    }

    // Get the helper object.
    $helper = $this->getoshaHelper();
    $node = $helper->getLastRevisionNode();
    $node = reset($node);
    //ksm($node);
    return AccessResult::allowed();
    if ($node->bundle() == 'node_article_edit_form' || $node->bundle() == 'node_25th_anniversary_edit_form'||
    $node->bundle() == 'node_calls_edit_form'|| $node->bundle() == 'node_directive_edit_form'|| $node->bundle() == 'node_guideline_edit_form'||
    $node->bundle() == 'node_highlight_edit_form'|| $node->bundle() == 'node_infographic_edit_form'|| $node->bundle() == 'node_job_vacancies_edit_form'||
    $node->bundle() == 'node_news_edit_form'|| $node->bundle() == 'node_press_release_edit_form'|| $node->bundle() == 'node_publication_edit_form'||
    $node->bundle() == 'node_seminar_edit_form')
    {
      $moderaiton_state = $node->get('moderation_state')->getValue();
      if ($moderaiton_state == 'to_be_approved') {
        // By default we have to deny the access to all users that they.
        // have the approver role.
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
      }
    }

    return AccessResult::allowed();
  }

  /**
   * Gets osha Helper object.
   *
   * @return \Drupal\osha_workflow\VwHelper
   *   The object.
   */
  public function getoshaHelper() {
    return \Drupal::service('osha_workflow.helper');
  }

}
