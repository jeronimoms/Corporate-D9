<?php

namespace Drupal\oira_workflow;

use Drupal\content_moderation\ModerationInformation;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;

/**
 * General service for Oira workflow.
 */
class OwHelper {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The moderation manager.
   *
   * @var \Drupal\content_moderation\ModerationInformation
   */
  protected $moderationManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModerationInformation $moderation_information) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moderationManager = $moderation_information;
  }

  /**
   * Gets the las revision node.
   *
   * @return array|\Drupal\node\Entity\Node
   *   The node if exists.
   */
  public function getLastRevisionNode(Node $node) {
    if ($node instanceof Node) {
      /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $storage */
      $storage = $this->entityTypeManager->getStorage('node');
      $revision = $storage->getLatestRevisionId($node->id());
      return $storage->loadRevision($revision);
    }

    return [];
  }

  /**
   * Get the workflow entity.
   *
   * @return \Drupal\workflows\Entity\Workflow
   *   The complete workflow.
   */
  public function getWorkFlowList($id) {
    return $this->entityTypeManager->getStorage('workflow')->load($id);
  }

  /**
   * Get the entities by type and moderation state.
   *
   * @return array|\Drupal\node\Entity\Node
   *   The node or list of entities.
   */
  public function getEntities($data) {
    $res = [];

    //Load all entities.
    $entities = $this->entityTypeManager->getStorage($data['entity_type'])->loadByProperties([
      'type' => $data['type'],
    ]);

    // Filter by moderation state.
    foreach ($entities as $id => $entity) {
      $revisioned = $this->getLastRevisionNode($entity);
      if ($revisioned->get('moderation_state')->getString() == $data['moderation_state']) {
        $res[$revisioned->id()] = $revisioned;
      }
    }

    return $res;
  }

  /**
   * Get the list of user by rol.
   *
   * @return array|\Drupal\user\Entity\User
   *   The user or list of users.
   */
  public function getUsersByRol($rol) {
    return $this->entityTypeManager->getStorage('user')->loadByProperties(['roles' => $rol]);
  }

}
