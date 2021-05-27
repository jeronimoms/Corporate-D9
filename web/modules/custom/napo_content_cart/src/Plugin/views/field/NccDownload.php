<?php

namespace Drupal\napo_content_cart\Plugin\views\field;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("ncc_download")
 * handlers = {
 * "views_data" = "Drupal\views\EntityViewsData"
 * }
 */
class NccDownload extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $privateTempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, PrivateTempStoreFactory $private_temp_store_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->privateTempStoreFactory = $private_temp_store_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * Define the available options
   *
   * @return array
   */
  protected function defineOptions() {
    return parent::defineOptions();
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\search_api\Plugin\views\ResultRow $nvalues */
    $nvalues = $values;

    /** @var \Drupal\node\Entity\Node  $node */
    $node = $nvalues->_object->getValue();
    if (!$node) {
      return [];
    }

    /** @var PrivateTempStore $store */
    $store = $this->privateTempStoreFactory->get('napo_content_cart.downloads');
    $ids = $store->get('video_downloads');

    if (isset($ids) && array_key_exists($node->id(), $ids)) {
      $link = [
        '#type' => 'link',
        '#title' => $this->t('Remove Video'),
        '#url' => Url::fromRoute('content_cart.deleteone',
          [
            'node' => $node->id(),
            'nojs' => 'nojs'
          ],
          [
            'attributes' => [
              'class' => ['use-ajax', 'node_in_content_cart'],
              'data-dialog-type' => 'dialog',
            ],
          ],
        ),
      ];
    }
    else {
      $link = [
        '#type' => 'link',
        '#title' => $this->t('Download Video'),
        '#url' => Url::fromRoute('content_cart.addcart',
          [
            'node' => $node->id(),
          ],
          [
            'attributes' => [
              'class' => ['use-ajax'],
              'data-dialog-type' => 'dialog',
            ],
          ],
        ),
      ];
    }

    $link = [
      '#type' => 'link',
      '#title' => $this->t('Download Video'),
      '#url' => Url::fromRoute('content_cart.addcart',
        [
          'node' => $node->id(),
        ],
        [
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'dialog',
          ],
        ],
      ),
    ];

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'download-video',
      ],
      'content' => $link,
    ];
  }


}