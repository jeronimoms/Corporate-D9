<?php

namespace Drupal\napo_content_cart\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;


/**
 * Provides a 'NCC link' Block.
 *
 * @Block(
 *   id = "ncc_link_block",
 *   admin_label = @Translation("Ncc Link Block"),
 * )
 */
class NccDownloadCentreBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $privateTempStoreFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tempStoreFactory = $privateTempStoreFactory;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('tempstore.private')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\Core\TempStore\PrivateTempStore $temp */
    $temp = $this->tempStoreFactory->get('napo_content_cart.downloads');
    $items = $temp->get('video_downloads');

    return [
      '#type' => 'link',
      '#title' => $this->t('Download Centre(@count)', ['@count' => count($items)]),
      '#url' => Url::fromRoute('content_cart.viewList', [], [
        'attributes' => [
          'class' => ['header-download-centre'],
        ]
      ]),
    ];
  }

}
