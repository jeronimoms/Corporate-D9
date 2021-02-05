<?php

namespace Drupal\vesafe_workflow\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\vesafe_workflow\VesafeWorkFlowHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;

class ApproverAddForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database manager.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The Vesafe helper service.
   *
   * @var \Drupal\vesafe_workflow\VesafeWorkFlowHelper
   */
  protected $helper;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $database, VesafeWorkFlowHelper $vasefe_helper) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
    $this->helper = $vasefe_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('vesafe_workflow.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'approver_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['node_id'] = [
      '#type' => 'number',
      '#min' => 0,
      '#step' => 1,
    ];

    $users = $this->getUsers();
    $form['user_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a new user to add it to the queue'),
      '#options' => $users,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to queue'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $node_id = $form_state->getValue('node_id');
    $user_id = $form_state->getValue('user_id');

    // Check node id empty.
    if (empty($node_id)) {
      $form_state->setErrorByName('Node ID', $this->t('The field Node ID is empty'));
    }

    // Check user id empty.
    if (empty($user_id)) {
      $form_state->setErrorByName('User ID', $this->t('The field User ID is empty'));
    }

    // Check if the user already exists.
    $query = $this->database->select('vesafe_workflow_approvers', 'v')
      ->condition('node_id', $node_id , '=')
      ->condition('user_id', $user_id , '=')
      ->fields('v', ['id', 'node_id', 'user_id', 'status']);

    $results = $query->execute()->fetchAll();
    if (!empty($results)) {
      $form_state->setErrorByName('Duplicated', $this->t('That user already exists as approver'));
    }


    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fields = [
      'node_id' => $form_state->getValue('node_id'),
      'user_id' => $form_state->getValue('user_id'),
      'status' => $this->t('Waiting to approve'),
    ];

    // Add the user to the list.
    $this->helper->addUserToList('approvers', $fields);
  }

  public function getUsers() {
    // Output array.
    $output = [];

    // List of users.
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple();

    /** @var \Drupal\user\Entity\User $user */
    foreach ($users as $user) {
      if ($user->id() == 0) {
        continue;
      }

      if ($user->hasRole('approver') || $user->hasRole('administrator')) {
        $output[$user->id()] = $user->getDisplayName();
      }
    }

    return $output;
  }

}
