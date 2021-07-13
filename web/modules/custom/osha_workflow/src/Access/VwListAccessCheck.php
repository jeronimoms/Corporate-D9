<?php

namespace Drupal\osha_workflow\Access;

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
    return AccessResult::allowed();
    // Get the helper object.
    $helper = $this->getoshaHelper();
    $node = $helper->getLastRevisionNode();
    if ($node->bundle() == 'node_article_edit_form' || $node->bundle() == 'node_25th_anniversary_edit_form'||
      $node->bundle() == 'node_calls_edit_form'|| $node->bundle() == 'node_directive_edit_form'|| $node->bundle() == 'node_guideline_edit_form'||
      $node->bundle() == 'node_highlight_edit_form'|| $node->bundle() == 'node_infographic_edit_form'|| $node->bundle() == 'node_job_vacancies_edit_form'||
      $node->bundle() == 'node_news_edit_form'|| $node->bundle() == 'node_press_release_edit_form'|| $node->bundle() == 'node_publication_edit_form'||
      $node->bundle() == 'node_seminar_edit_form') {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
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
