<?php

namespace Drupal\oira_masquerade;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\masquerade\Masquerade;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\ViewExecutable;

class OmLink implements ContainerInjectionInterface {

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
   * Implements hook_link_alter().
   */
  public function linkAlter(&$variables) {
    // End process if is not masqueraded.
    if (!$this->masquerade->isMasquerading()) {
      return;
    }

    // Get the correct partner id.
    $partner_id = $this->masqueradeManager->getPartnerId();
    if (empty($partner_id) || !isset($partner_id)) {
      return;
    }

    /** @var \Drupal\Core\Url $url */
    $url = &$variables['url'];
    if (!$url->isExternal() && $variables['text']) {
      $parameters = $url->getRouteParameters();
      if (array_key_exists('node', $parameters)) {
        if ($parameters['node'] == '1158') {
          $url->setRouteParameter('node', $partner_id);
        }
      }
    }
  }

}
