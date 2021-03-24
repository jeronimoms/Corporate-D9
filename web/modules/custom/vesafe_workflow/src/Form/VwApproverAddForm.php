<?php

namespace Drupal\vesafe_workflow\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\vesafe_workflow\VwHelper;
use http\Env\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * General class for Vw approve add form.
 */
class VwApproverAddForm extends FormBase {

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
   * @var \Drupal\vesafe_workflow\VwHelper
   */
  protected $helper;

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $database, VwHelper $vasefe_helper, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
    $this->helper = $vasefe_helper;
    $this->configFactory = $config_factory;
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
      $container->get('vesafe_workflow.helper'),
      $container->get('config.factory')
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
  public function buildForm(array $form, FormStateInterface $form_state, $table = NULL) {

    // Pre table message.
    $form['message'] = [
      '#markup' => $this->t('List of @name', ['@name' => $table]),
    ];

    $group_class = 'group-order-weight';

    // Final table output.
    $form['table'] = [
      '#type' => 'table',
      '#header' => [
        '',
        $this->t('User'),
        $this->t('Mail'),
        $this->t('Status'),
        $this->t('Operations'),
        $this->t('Weight'),
      ],
      '#empty' => t('No users found'),
      '#tabledrag' => [[
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'draggable-weight',
      ]],
      '#attributes' => [
        'class' => ['vw-table-' . $table]
      ],
    ];

    // Get the actual entries from database.
    $results = $this->helper->getModerationList($table);

    // Create the rows.
    foreach ($results as $i => $data) {

      $form['table'][$i] = [
        'data' => [],
      ];

      $form['table'][$i]['#attributes']['class'] = ['draggable'];
      $form['table'][$i]['#attributes']['user'] = $data->user_id;
      $form['table'][$i]['#attributes']['node'] = $data->node_id;
      $form['table'][$i]['#attributes']['table'] = $table;

      $form['table'][$i]['user_id'] = [
        '#type' => 'item',
        '#value' => $data->user_id,
        '#markup' => $this->getUserName($data->user_id),
      ];
      $form['table'][$i]['user_mail'] = [
        '#plain_text' => $this->getUserMail($data->user_id),
      ];
      $form['table'][$i]['status'] = [
        '#plain_text' => $data->status,
      ];
      $form['table'][$i]['operation'] = [
        '#type' => 'button',
        '#value' => $this->t('Remove @table: @i', ['@table' => $table,'@i' => $this->getUserName($data->user_id)]),
        '#data' => $i,
        '#user' => $data->user_id,
        '#ajax' => [
          'callback' => '::removeCallback',
          'event' => 'click',
        ],
      ];

      $form['table'][$i]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight'),
        '#title_display' => 'visible',
        '#default_value' => $data->weight,
        '#attributes' => [
          'class' => [
            'draggable-weight'
          ]
        ],
        '#ajax' => [
          'callback' => '::weightCallback',
          'event' => 'change',
        ],
      ];
    }

    // Set the table name.
    $form_state->set('vesafe_workflow_table', $table);

    // Store the list configration.
    $vesafe_config = $this->configFactory->getEditable('vesafe_workflow.general');
    foreach ($vesafe_config->get('lists')['list'] as $list) {
      if ($list['name'] == ucfirst($form_state->get('vesafe_workflow_table'))) {
        $form_state->set('vesafe_workflow_list_configuration', $list);
      }
    }

    $form['node_id'] = [
      '#type' => 'number',
      '#min' => 0,
      '#step' => 1,
    ];

    $users = $this->getUsers($form_state->get('vesafe_workflow_list_configuration')['access_roles']);
    $form['user_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a new user to add it to the queue'),
      '#options' => $users,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to queue'),
      '#attributes' => [
        'class' => [
          'button btn btn-primary',
        ]
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function weightCallback(array &$form, FormStateInterface $form_state) {
    $table = $form_state->getValue('table');
    foreach ($table as $row) {
      $data = [
        'node_id' => $form_state->getValue('node_id'),
        'user_id' => $row['user_id'],
        'user_name' => $this->getUserName($row['user_id']),
        'weight' => $row['weight'],
      ];
      $this->helper->setUserWeight($form_state->get('vesafe_workflow_table'), $data);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $tr = $form_state->getTriggeringElement()['#data'];
    $user = $form_state->getTriggeringElement()['#user'];
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand('tr[data-drupal-selector="edit-table-' . $tr .'"]', 'remove'));

    $data = [
      'node_id' => $form_state->getValue('node_id'),
      'user_id' => $user,
    ];

    $this->helper->deleteUserToList($form_state->get('vesafe_workflow_table'), $data);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
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

    // If the user already exists in the list, then return error.
    if ($this->helper->checkUserExists($form_state->get('vesafe_workflow_table'), $user_id)) {
      $form_state->setErrorByName('Duplicated', $this->t('That user already exists as approver'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $weight = 0;
    $previous_user = $this->helper->getLastUserList($form_state->get('vesafe_workflow_table'));

    if (!empty($previous_user)) {
      $weight = $previous_user->weight + 1;
    }

    $fields = [
      'node_id' => $form_state->getValue('node_id'),
      'user_id' => $form_state->getValue('user_id'),
      'status' => $this->t('Waiting to approve'),
      'weight' => $weight
    ];

    // Add the user to the list.
    $this->helper->addUserToList($form_state->get('vesafe_workflow_table'), $fields);
  }

  /**
   * {@inheritdoc}
   */
  public function getUsers(array $roles = []) {
    // Output array.
    $output = [];

    // List of users.
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple();

    /** @var \Drupal\user\Entity\User $user */
    foreach ($users as $user) {
      if ($user->id() == 0) {
        continue;
      }

      // Assign the access if the rol exists in the list configuration.
      foreach ($roles as $rol) {
        if ($user->hasRole($rol)) {
          $output[$user->id()] = $user->getDisplayName();
        }
      }

      // Set access if the user is administrator.
      if ($user->hasRole('administrator')) {
        $output[$user->id()] = $user->getDisplayName();
      }
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getUser($user_id) {
    if (empty($user_id)) {
      return '';
    }

    return $this->entityTypeManager->getStorage('user')->load($user_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getUserName($user_id) {
    /** @var \Drupal\user\Entity\User $user */
    if ($user = $this->getUser($user_id)) {
      return $user->getAccountName();
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getUserMail($user_id) {
    /** @var \Drupal\user\Entity\User $user */
    if ($user = $this->getUser($user_id)) {
      return $user->getEmail();
    }

    return '';
  }

}
