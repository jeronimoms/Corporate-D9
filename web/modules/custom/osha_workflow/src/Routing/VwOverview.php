<?php

namespace Drupal\osha_workflow\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * General class for Vw overview routing.
 */
class VwOverview {

  /**
   * Dynamically generate the routes for the entity details.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   The collections.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function routes() {
    $config = $this->configFactory()->getEditable('osha_workflow.general');
    $lists = $config->get('lists');

    if (isset($lists) && array_key_exists('list', $lists)) {
      $collection = new RouteCollection();

      foreach ($lists['list'] as $list) {
        $name = strtolower($list['name']);
        $route = new Route(
          "/node/{node}/$name/{list_name}",
          [
            '_controller' => '\Drupal\osha_workflow\Controller\VwApproversController::list',
          ],
          [
            'node' => '\d+',
            '_permission' => 'administer ' . $name,
          ],
          [
            'parameters' => [
              'node' => [
                'type' => 'entity:node',
              ],
              'list_name' => $name,
            ],
          ]
        );

        $collection->add('osha_workflow.' . $name . '.list', $route);
      }

      return $collection;
    }
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
