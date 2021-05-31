<?php

namespace Drupal\oira_masquerade;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\masquerade\Masquerade;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\ViewExecutable;

class OmView implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The masquerade service.
   *
   * @var \Drupal\masquerade\Masquerade
   */
  protected $masquerade;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Masquerade $masquerade) {
    $this->entityTypeManager = $entity_type_manager;
    $this->masquerade = $masquerade;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('masquerade')
    );
  }

  /**
   * Implements hook_views_pre_view().
   */
  public function viewPreView(ViewExecutable $view, $display_id, array &$args) {
    if ($view->id() == 'partner_content') {
      if ($this->masquerade->isMasquerading()) {
        $partner = $this->getPartnerId();
        if ($partner !== 0) {
          $args[0] = $partner;
        }
      }
    }
  }

  /**
   * Get the associated partner.
   *
   * @return int
   */
  public function getPartnerId() {
    $user = $this->entityTypeManager->getStorage('user')->load(\Drupal::currentUser()->id());
    if ($user) {
      $ldap_code = $user->get('field_user_partner_guid')->getString();
      if ($ldap_code) {
        $tax_partner = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
          'field_ldap_section_code' => $ldap_code,
        ]);
        if ($tax_partner) {
          $tax_partner = reset($tax_partner);
          $node_partner = $this->entityTypeManager->getStorage('node')->loadByProperties([
            'type' => 'partner',
            'title' => $tax_partner->getName(),
          ]);
          if ($node_partner) {
            $node_partner = reset($node_partner);
            return $node_partner->id();
          }
        }
      }
    }
    return 0;
  }

}
