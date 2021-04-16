<?php

namespace Drupal\oira_partner;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;

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
   * The array of fields.
   *
   * @var array
   */
  protected $fields;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fields = [
      'field_co_author' => 'field_co_author_node',
      'field_workbench_access' => 'field_related_partners',
    ];
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
  public function updatePartners(EntityInterface $entity, $save = FALSE) {
    // Ignore if the entity isn't a node.
    if (!$entity instanceof Node) {
      return;
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
      $partner = $this->entityTypeManager->getStorage('node')->loadByProperties(['type' => 'partner', 'field_workbench_access' => $access_id]);
      /** @var \Drupal\node\Entity\Node $partner_node */
      $partner_node = reset($partner);
      if (empty($partner)) {
        continue;
      }

      // Update the node field.
      if ($entity->hasField($field_name)) {
        $entity->set($field_name, $partner_node->id());
        if ($save) {
          $entity->save();
        }
      }
    }
  }

}
