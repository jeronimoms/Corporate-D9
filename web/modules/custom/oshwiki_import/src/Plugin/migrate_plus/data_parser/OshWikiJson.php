<?php

namespace Drupal\oshwiki_import\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json as MigrateJson;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Obtain JSON data for migration.
 *
 * @DataParser(
 *   id = "oshwiki_json",
 *   title = @Translation("OshWiki Import JSON")
 * )
 */
class OshWikiJson extends MigrateJson implements ContainerFactoryPluginInterface {
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
// $logsPath = "/var/www/html/web/sites/default/files/oshwikiMigration/parserlog.txt";
    // Get the default items.
// file_put_contents($logsPath, '');
// file_put_contents($logsPath, '******************* URL *******************'.PHP_EOL, FILE_APPEND);
// file_put_contents($logsPath, print_r($url.PHP_EOL, true), FILE_APPEND);
    if(!$response = $this->getCachedResponse($url)){
      // For some reason the response was not cached. Fetch it again.
      $response = $this->getDataFetcherPlugin()->getResponseContent($url);
    }
// file_put_contents($logsPath, '******************* RESPONSE *******************'.PHP_EOL, FILE_APPEND);
// file_put_contents($logsPath, print_r($response, true).PHP_EOL, FILE_APPEND);
    $source_data = json_decode($response, TRUE);
// file_put_contents($logsPath, '******************* SOURCEDATA *******************'.PHP_EOL, FILE_APPEND);
// file_put_contents($logsPath, print_r($source_data, true).PHP_EOL, FILE_APPEND);
    $items_new = [];
    // Find the data by item.
//    foreach ($source_data as $item) {
    foreach ($source_data as $decoded_data) {
// file_put_contents($logsPath, '******************* ITEM *******************'.PHP_EOL, FILE_APPEND);
// file_put_contents($logsPath, print_r($item, true).PHP_EOL, FILE_APPEND);

      // Get the data decoded.
//      $decoded_data = json_decode(implode($item), TRUE);
// file_put_contents($logsPath, '******************* DECODEDDATA *******************'.PHP_EOL, FILE_APPEND);
// file_put_contents($logsPath, print_r($decoded_data, true).PHP_EOL, FILE_APPEND);

      // If the node already exists, update it.
      $node = $this->entityTypeManager->getStorage('node')->loadByProperties(['title' => $decoded_data['title']]);
// file_put_contents($logsPath, '******************* NODE *******************'.PHP_EOL, FILE_APPEND);
// file_put_contents($logsPath, print_r($node, true).PHP_EOL, FILE_APPEND);
      if (!empty($node)) {
        /** @var \Drupal\node\Entity\Node  $node */
        $node = reset($node);
        $decoded_data['nid'] = $node->id();
      }
      // Set the new item.
//      $items_new[$i] = $decoded_data;
      $items_new[] = $decoded_data;
// file_put_contents($logsPath, '******************* ITEMSNEW[I] *******************'.PHP_EOL, FILE_APPEND);
// file_put_contents($logsPath, print_r($decoded_data, true).PHP_EOL, FILE_APPEND);
    }

// file_put_contents($logsPath, '******************* ITEMSNEW *******************'.PHP_EOL, FILE_APPEND);
// file_put_contents($logsPath, print_r($items_new, true).PHP_EOL, FILE_APPEND);
    return $items_new;
  }

  /**
   * Returns cached response.
   *
   * @param $url
   * @return bool|mixed
   */
  protected function getCachedResponse($url){
    return isset($this->cachedResponses[$url]) ? $this->cachedResponses[$url]: FALSE;
  }

}
