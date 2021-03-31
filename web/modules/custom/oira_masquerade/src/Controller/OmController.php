<?php

namespace Drupal\oira_masquerade\Controller;

use Drupal\Core\Block\BlockManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\EventSubscriber\RedirectResponseSubscriber;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\masquerade\Masquerade;

/**
 * General class for Oira Masquerade controller.
 */
class OmController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * The masquerade service.
   *
   * @var \Drupal\masquerade\Masquerade
   */
  protected $masquerade;

  /**
   * {@inheritdoc}
   */
  public function __construct(BlockManager $block_manager, Masquerade $masquerade) {
    $this->blockManager = $block_manager;
    $this->masquerade = $masquerade;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('masquerade')
    );
  }

  /**
   * The function to show the Masquerade block in the mnodal.
   */
  public function modalContent() {
    // Masquerade block.
    $plugin_block = $this->blockManager->createInstance('masquerade', []);
    return $plugin_block->build();
  }

  /**
   * The function to switch back and redirect.
   */
  public function switchBackUser() {
    $this->masquerade->switchBack();
    return $this->redirect(Url::fromRoute('<front>')->getRouteName());
  }

}
