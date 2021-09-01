<?php

namespace Drupal\ncw_migration\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json as MigrateJson;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Obtain JSON data for migration.
 *
 * @DataParser(
 *   id = "nm_json",
 *   title = @Translation("NCW JSON")
 * )
 */
class NmJson extends MigrateJson implements ContainerFactoryPluginInterface {

  /**
   * The Config Factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );

    $instance->configFactory = $container->get('config.factory');
    $instance->entityTypeManager = $container->get('entity_type.manager');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceData($url) {
    // NCW URL configuration.
    $config = $this->configFactory->getEditable('ncw_migration.config');
    $nm_root_url = $config->get('root_endpoint');

    // Get the default items.
    $items =  parent::getSourceData($nm_root_url . $config->get($this->configuration['content_type'] . '_endpoint'));
    $items_new = [];
    // Find the data by item.
    foreach ($items as $i => $item) {
      $nm_item = $item['item'];
      $nm_nid = $nm_item['nid'];
      $nm_url = $nm_root_url . '/export/node/' . $nm_nid;
      $response = $this->getDataFetcherPlugin()->getResponseContent($nm_url);
      $response_data = json_decode($response, TRUE);

      if (empty($response_data) || !array_key_exists($this->configuration['constants']['lang'], $response_data['translations']['data'])) {
        continue;
      }

      // Get the data decoded.
      $decoded_data = json_decode($response, TRUE);

      // If the node already exists, update it.
      $node = $this->entityTypeManager->getStorage('node')->loadByProperties(['title' => $decoded_data['title']]);
      if (!empty($node)) {
        /** @var \Drupal\node\Entity\Node  $node */
        $node = reset($node);
        $decoded_data['nid'] = $node->id();

        // Set as false to do not update this field if the node already exists.
        if (array_key_exists('field_image', $decoded_data)) {
          $decoded_data['field_image']['en'][0]['ignore'] = TRUE;
        }
      }

      // Set the new item.
      $items_new[$i]['item'] = $decoded_data;
    }

    return $items_new;
  }

}
