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

/**
 * General service for moderation-related queries to the database.
 */
class VwHelper {

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

  /**
   * {@inheritdoc}
   */
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

  /**
   * Get he moderation state of current node.
   *
   * @return string
   *   The moderation state of current node.
   */
  public function getNodeModerationState() {
    $node = $this->getLastRevisionNode();
    return $node->get('moderation_state')->getValue()[0]['value'];
  }

  /**
   * Set the new state to the current node.
   *
   * @param $state
   *   The state name.
   */
  public function setNodeModerationState($state) {
    $this->getLastRevisionNode()->set('moderation_state', $state)->save();
  }

  /**
   * Check if any user of the list has not approved it.
   *
   * @param $table
   *   The table name.
   *
   * @return bool
   *   If one of the users has not approved it
   */
  public function getModerationListStatus($table) {
    // Get complete moderation list.
    $results = $this->getModerationList($table);

    foreach ($results as $result) {
      if ($result->status == $this->t('Waiting to approve')) {
        // Return FALSE if any user has not approved it.
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Gets the moderation list.
   *
   * @param $table
   *   The table name.
   */
  public function getModerationList($table) {
    $query = $this->database->select("vesafe_workflow_$table", 'v')
      ->condition('node_id', $this->getLastRevisionNode()->id() , '=')
      ->fields('v', ['id', 'node_id', 'user_id', 'status']);

    return $query->execute()->fetchAll();
  }

  /**
   * Set the status to approve of users list.
   *
   * @param $table
   *   The table name.
   */
  public function resetUsersStatus($table) {
    $this->database->update("vesafe_workflow_$table")
      ->fields([
        'status' => $this->t('Waiting to approve'),
      ])
      ->condition('node_id', $this->getLastRevisionNode()->id())->execute();
  }

  /**
   * Add a user in the list.
   *
   * @param $table
   *   The table name.
   * @param array $fields
   *   The array with fields.
   */
  public function addUserToList($table, array $fields) {
    $this->database->insert("vesafe_workflow_$table")
      ->fields($fields)
      ->execute();
  }

  /**
   * Set the status to approve of user list.
   *
   * @param $table
   *   The table name.
   */
  public function approveUser($table) {
    // Update the status of current user.
    $this->database->update("vesafe_workflow_$table")
      ->fields([
        'status' => $this->t('Approved'),
      ])
      ->condition('node_id', $this->getLastRevisionNode()->id())
      ->condition('user_id', $this->account->id())->execute();
  }

  /**
   * Gets the next user of list.
   *
   * @param $table
   *   The table name.
   *
   * @return array|\Drupal\user\Entity\User
   *   The next user.
   */
  public function getNextUser($table) {
    $users = $this->getModerationList($table);
    foreach ($users as $i => $user) {
      if ($user->user_id == $this->account->id()) {
        return $this->entityTypeManager->getStorage('user')->load($users[($i + 1)]->user_id);
      }
    }

    return [];
  }

  /**
   * Check if the user is included to the list.
   *
   * @param $table
   *   The table name.
   *
   * @return bool
   *   If the user has access.
   */
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

  public function checkUserExists($table, $user_id) {
    $results = $this->getModerationList($table);
    foreach ($results as $data) {
      if ($data->user_id == $user_id) {
        return TRUE;
      }
    }

    return FALSE;
  }

  public function getWorkflow() {
    /** @var \Drupal\workflows\Entity\Workflow $flow */
    return $this->moderationManager->getWorkflowForEntityTypeAndBundle('node', $this->getLastRevisionNode()->bundle());
    return $flow->get('type_settings');
  }

  /**
   * Get the workflow states.
   *
   * @return array
   *   The array with states.
   */
  public function getWorkFlowStates() {
    /** @var \Drupal\workflows\Entity\Workflow $flow */
    $flow = $this->moderationManager->getWorkflowForEntityTypeAndBundle('node', $this->getLastRevisionNode()->bundle());
    return $flow->get('type_settings')['states'];
  }

}
