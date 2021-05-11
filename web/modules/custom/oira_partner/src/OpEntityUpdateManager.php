<?php

namespace Drupal\oira_partner;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\Tests\search\Kernel\SearchMatchTest;

/**
 * OpEntityUpdateManager to update the Oira entities.
 */
class OpEntityUpdateManager {

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
   * The array of fields.
   *
   * @var array
   */
  protected $fields;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $account) {
    $this->entityTypeManager = $entity_type_manager;
    $this->account = $account;
  }

  /**
   * Update all nodes with the correspond partner.
   */
  public function updateAll() {
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple();
    foreach ($nodes as $node) {
      $this->updatePartners($node, TRUE);
    }
  }

  /**
   * Update the node with correspond partner.
   *
   * @var \Drupal\Core\Entity\EntityInterface entity
   *   The entity node.
   * @var bool $save
   *   If the entity should be saved.
   */
  public function updatePartners(EntityInterface $entity, $save = FALSE, $inverse = FALSE) {
    // Ignore if the entity isn't a node.
    if (!$entity instanceof Node) {
      return;
    }

    if ($inverse) {
      $this->fields = [
        'field_co_author_node' => 'field_co_author',
        'field_related_partners' => 'field_workbench_access',
        'field_third_partner_node' => 'field_third_partner',
      ];
    }
    else {
      $this->fields = [
        'field_co_author' => 'field_co_author_node',
        'field_workbench_access' => 'field_related_partners',
        'field_third_partner' => 'field_third_partner_node',
      ];
    }

    foreach ($this->fields as $key => $field_name) {
      // Ignore if the entity doesn't have the one of fields.
      if (!$entity->hasField($key)) {
        continue;
      }

      // The workbench access id.
      $access_id = $entity->get($key)->getString();
      if (empty($access_id)) {
        continue;
      }


      // Find the node with the current workbench access id.
      if ($inverse) {
        $partner = $this->entityTypeManager->getStorage('node')->load($access_id);
        if (!empty($partner)) {
          $id = $partner->get('field_workbench_access')->getString();
        }
      } else {
        $partner = $this->entityTypeManager->getStorage('node')->loadByProperties(['type' => 'partner', 'field_workbench_access' => $access_id]);
        /** @var \Drupal\node\Entity\Node $partner_node */
        $partner_node = reset($partner);
        if ($partner_node) {
          $id = $partner_node->id();
        }
      }

      if (empty($partner)) {
        continue;
      }

      // Update the node field.
      if ($entity->hasField($field_name)) {
        $entity->set($field_name, $id);
        if ($save) {
          $entity->save();
        }
      }
    }
  }

  public function getTermParent() {
    $user = $this->entityTypeManager->getStorage('user')->load($this->account->id());
    // The user guid associated with the partner.
    $guid = $user->get('field_user_partner_guid')->getString();

    // Looking for the partner associated.
    $term_entity = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => 'section', 'field_ldap_section_code' => $guid]);

    /** @var \Drupal\taxonomy\Entity\Term  $term */
    $term = reset($term_entity);
    if (!$term) {
      return '';
    }
    return $term->id();
  }

  public function getCountryFromPartner($partner_id) {
    $nodes = $this->entityTypeManager->getStorage('node')->loadByProperties(['type' => 'partner', 'field_workbench_access' => $partner_id]);
    /** @var \Drupal\node\Entity\Node $partner_node */
    $partner_node = reset($nodes);
    if ($partner_node) {
      if ($partner_node->hasField('field_country')) {
        $country_id = $partner_node->get('field_country')->getString();
        /** @var \Drupal\taxonomy\Entity\Term  $term_entity */
        $term_entity = $this->entityTypeManager->getStorage('taxonomy_term')->load($country_id);
        if ($term_entity) {
          return $term_entity->label();
        }
      }
    }
    return NULL;
  }

}
