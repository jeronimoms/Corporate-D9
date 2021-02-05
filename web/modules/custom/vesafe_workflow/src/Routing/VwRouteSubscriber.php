<?php

namespace Drupal\vesafe_workflow\Routing;

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
      $route->setRequirement('_custom_access', 'vesafe_workflow.access_checker::access');
    }
  }

}
