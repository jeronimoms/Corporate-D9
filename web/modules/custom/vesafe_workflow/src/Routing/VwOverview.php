<?php

namespace Drupal\vesafe_workflow\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class VwOverview {

  /**
   * Dynamically generate the routes for the entity details.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function routes() {
    $config = $this->configFactory()->getEditable('vesafe_workflow.general');
    $lists = $config->get('lists');

    if (isset($lists) && array_key_exists('list', $lists)) {
      $collection = new RouteCollection();

      foreach ($lists['list'] as $list) {
        $name = $list['name'];
        $route = new Route(
          "/node/{node}/{list_name}",
          [
            '_controller' => '\Drupal\vesafe_workflow\Controller\VwApproversController::list',
          ],
          [
            'node' => '\d+',
            '_permission' => 'administer ' . strtolower($name),
          ],
          [
            'parameters' => [
              'node' => [
                'type' => 'entity:node',
              ],
              'list_name' => $name,
            ],
            '_admin_route' => TRUE,
          ]
        );

        $collection->add('vesafe_workflow.' . strtolower($name) . '.list', $route);
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
