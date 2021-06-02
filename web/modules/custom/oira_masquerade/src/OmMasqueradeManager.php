<?php

namespace Drupal\oira_masquerade;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;

class OmMasqueradeManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The AccountInterface object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxy $account) {
    $this->entityTypeManager = $entity_type_manager;
    $this->account = $account;
  }

  /**
   * Get the associated partner.
   *
   * @return int
   */
  public function getPartnerId() {
    $user = $this->entityTypeManager->getStorage('user')->load($this->account->id());
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
