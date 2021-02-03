<?php

namespace Drupal\vesafe_workflow;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Database\Connection;
use Drupal\content_moderation\ModerationInformation;
use Drupal\node\Entity\Node;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

class VesafeWorkFlowHelper {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The AccountInterface object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The database manager.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The Request object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The moderation manager.
   *
   * @var \Drupal\content_moderation\ModerationInformation
   */
  protected $moderationManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(AccountProxy $account, Connection $database, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, ModerationInformation $moderation_information) {
    $this->account = $account;
    $this->database = $database;
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->moderationManager = $moderation_information;
  }

  /**
   * Gets the las revision node.
   *
   * @return array|\Drupal\node\Entity\Node
   *   The node if exists.
   */
  public function getLastRevisionNode() {
    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof Node) {
      /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $storage */
      $storage = $this->entityTypeManager->getStorage('node');
      $revision = $storage->getLatestRevisionId($node->id());
      return $storage->loadRevision($revision);
    }

    return [];
  }

  public function getNodeModerationState() {
    $node = $this->getLastRevisionNode();
    return $node->get('moderation_state')->getValue()[0]['value'];
  }

  public function setNodeModerationState($state) {
    $this->getLastRevisionNode()->set('moderation_state', $state)->save();
  }

  public function getModerationListStatus($table) {
    // Get complete moderation list.
    $results = $this->getModerationList($table);

    foreach ($results as $result) {
      if ($result->status == $this->t('Waiting to approve')) {
        // Return FALSE if any user is not approved.
        return FALSE;
      }
    }

    return TRUE;
  }

  public function getModerationList($table) {
    $query = $this->database->select("vesafe_workflow_$table", 'v')
      ->condition('node_id', $this->getLastRevisionNode()->id() , '=')
      ->fields('v', ['id', 'node_id', 'user_id', 'status']);

    return $query->execute()->fetchAll();
  }

  public function resetUsersStatus($table) {
    $this->database->update("vesafe_workflow_$table")
      ->fields([
        'status' => $this->t('Waiting to approve'),
      ])
      ->condition('node_id', $this->getLastRevisionNode()->id())->execute();
  }

  public function addUserToList($table, $fields) {
    $this->database->insert("vesafe_workflow_$table")
      ->fields($fields)
      ->execute();
  }

  public function approveUser($table) {
    // Update the status of current user.
    $this->database->update("vesafe_workflow_$table")
      ->fields([
        'status' => $this->t('Approved'),
      ])
      ->condition('node_id', $this->getLastRevisionNode()->id())
      ->condition('user_id', $this->account->id())->execute();
  }

  public function checkUserAccess($table) {
    $user_list = [];
    $results = $this->getModerationList($table);
    foreach ($results as $data) {
      if ($data->status == $this->t('Waiting to approve')) {
        $user_list[] = $data->user_id;
      }
    }

    if (isset($user_list[0]) && $user_list[0] == $this->account->id()) {
      return TRUE;
    }

    return FALSE;
  }

  public function getWorkFlowStates() {
    /** @var \Drupal\workflows\Entity\Workflow $flow */
    $flow = $this->moderationManager->getWorkflowForEntityTypeAndBundle('node', $this->getLastRevisionNode()->bundle());
    return $flow->get('type_settings')['states'];
  }

}
