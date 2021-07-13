<?php

namespace Drupal\osha_workflow\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Alter the routes to check the access.
 */
class VwRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.node.edit_form')) {
      $route->setRequirement('_custom_access', 'osha_workflow.access_checker::access');
    }
    if ($route = $collection->get('osha_workflow.approvers.list')) {
      $route->setRequirement('_custom_access', 'osha_workflow.access_checker.list::access');
    }
    if ($route = $collection->get('osha_workflow.reviewers.list')) {
      $route->setRequirement('_custom_access', 'osha_workflow.access_checker.list::access');
    }
  }

}
