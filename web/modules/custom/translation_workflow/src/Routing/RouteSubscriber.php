<?php

namespace Drupal\translation_workflow\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\translation_workflow\Form\MultipleTargetLanguageCartForm;
use Symfony\Component\Routing\RouteCollection;

/**
 *
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * @inheritDoc
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('tmgmt.cart')) {
      $route->setDefault('_form', MultipleTargetLanguageCartForm::class);
    }
  }

}
