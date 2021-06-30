<?php

namespace Drupal\oira_masquerade;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\oira_masquerade\OmMasqueradeManager;
use Drupal\masquerade\Masquerade;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\ViewExecutable;

class OmView implements ContainerInjectionInterface {

  use DependencySerializationTrait;

  /**
   * The masquerade manager.
   *
   * @var \Drupal\oira_masquerade\OmMasqueradeManager
   */
  protected $masqueradeManager;

  /**
   * The masquerade service.
   *
   * @var \Drupal\masquerade\Masquerade
   */
  protected $masquerade;

  /**
   * {@inheritdoc}
   */
  public function __construct(OmMasqueradeManager $masquerade_manager, Masquerade $masquerade) {
    $this->masqueradeManager = $masquerade_manager;
    $this->masquerade = $masquerade;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('oira_masquerade.masquerade.manager'),
      $container->get('masquerade')
    );
  }

  /**
   * Implements hook_views_pre_view().
   */
  public function viewPreView(ViewExecutable $view, $display_id, array &$args) {
    if ($view->id() == 'partner_content') {
      if ($this->masquerade->isMasquerading()) {
        $partner = $this->masqueradeManager->getPartnerId();
        if ($partner !== 0) {
          $args[0] = $partner;
        }
      }
    }
  }

}
