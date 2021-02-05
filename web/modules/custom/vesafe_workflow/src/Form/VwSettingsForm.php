<?php

namespace Drupal\vesafe_workflow\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\vesafe_workflow\VwHelper;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

class VwSettingsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Vesafe helper service.
   *
   * @var \Drupal\vesafe_workflow\VwHelper
   */
  protected $helper;

  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, VwHelper $helper) {
    $this->entityTypeManager = $entity_type_manager;
    $this->helper = $helper;
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('vesafe_workflow.helper')
    );
  }

  protected function getEditableConfigNames() {
    return [
      'vesafe_workflow.general',
    ];
  }

  public function getFormId() {
    return 'vesafe_workflow_settings_general';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->config('vesafe_workflow.general');

    $lists = $config->get('lists');
    if (!isset($lists)) {
      $lists = [
        'list' => [],
      ];
    }
    $lists = $lists['list'];
    $list_num = $form_state->get('list_num');

    if (!isset($list_num)) {
      if (isset($lists) && count($lists) > 0) {
        $form_state->set('list_num', $lists);
      }
      else {
        $form_state->set('list_num', []);
      }
    }

    $form['#tree'] = TRUE;

    $form['list_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Lists'),
      '#prefix' => '<div id="list-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    ksm($this->entityTypeManager->getStorage('content_moderation_notification')->loadMultiple());

    foreach ($form_state->get('list_num') as $i => $item) {
      $form['list_fieldset']['list'][$i] = [
        '#type' => 'details',
        '#title' => $this->t('List @name', ['@name' => $item['name']]),
      ];

      $form['list_fieldset']['list'][$i]['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
        '#default_value' => $item['name'],
        '#description' => $this->t('The name of this list'),
      ];

      $form['list_fieldset']['list'][$i]['access_roles'] = [
        '#type' => 'select',
        '#multiple' => TRUE,
        '#title' => $this->t('User roles'),
        '#options' => $this->getRoles(),
        '#default_value' => $item['access_roles'],
        '#description' => $this->t('The roles of users that can be added to the list'),
      ];

      $form['list_fieldset']['list'][$i]['workflow'] = [
        '#type' => 'select',
        '#title' => $this->t('Workflow'),
        '#options' => $this->getWorkflows(),
        '#default_value' => $item['workflow'],
        '#description' => $this->t('The workflow where this list must apply'),
        '#ajax'   => [
          'callback' => '::workflowCallback',
          'wrapper'  => 'list-fieldset-wrapper',
        ],
      ];

      if (isset($item['workflow_state'])) {
        $states_options = $this->getWorkflowStates($item['workflow'], TRUE);
      }
      else {
        $workflows = $this->getWorkflows();
        $states_options = $this->getWorkflowStates(key($workflows), TRUE);
      }

      $form['list_fieldset']['list'][$i]['workflow_state'] = [
        '#type' => 'select',
        '#title' => $this->t('Workflow State'),
        '#options' => $states_options,
        '#default_value' => (isset($item['workflow_state'])) ? $states_options[$item['workflow_state']] : '',
        '#description' => $this->t('The workflow state where the users of this list must have access'),
        '#attributes' => ["id" => 'workflow-states'],
        '#validated' => TRUE,
      ];

      $form['list_fieldset']['list'][$i]['workflow_state_previous'] = [
        '#type' => 'select',
        '#title' => $this->t('Workflow Previous State'),
        '#options' => $states_options,
        '#default_value' => (isset($item['workflow_state_previous'])) ? $states_options[$item['workflow_state_previous']] : '',
        '#description' => $this->t('The workflow state when the user reject the current state'),
        '#attributes' => ["id" => 'workflow-previous-states'],
        '#validated' => TRUE,
      ];

      $form['list_fieldset']['list'][$i]['workflow_state_next'] = [
        '#type' => 'select',
        '#title' => $this->t('Workflow Next State'),
        '#options' => $states_options,
        '#default_value' => (isset($item['workflow_state_next'])) ? $states_options[$item['workflow_state_next']] : '',
        '#description' => $this->t('The workflow state when the last use of list approve the node'),
        '#attributes' => ["id" => 'workflow-next-states'],
        '#validated' => TRUE,
      ];

      $form['list_fieldset']['list'][$i]['actions']['remove_list'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove ' . $i),
        '#options' => ['id' => $i],
        '#submit' => ['::removeCallback'],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => 'list-fieldset-wrapper',
        ],
      ];
    }


    $form['list_fieldset']['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['add_list'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'list-fieldset-wrapper',
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];


    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $lists = $form_state->getValue('list_fieldset');

    $this->config('vesafe_workflow.general')
      ->set('lists', $lists)
      ->save();

    parent::submitForm($form, $form_state);
  }

  public function workflowCallback(array &$form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    $response = new AjaxResponse();
    $options = $this->getWorkflowStates($element['#value']);
    $response->addCommand(new HtmlCommand("#workflow-states", $options));
    return $response;
  }

  public function getWorkflowStates($workflow, $array = FALSE) {
    // Define the default array.
    if ($array) {
      $options = [];
    }
    else {
      $options = '';
    }

    // Get the workflow selected.
    $flow = $this->entityTypeManager->getStorage('workflow')->loadByProperties(['id' => $workflow]);
    $flow = reset($flow);

    // Get the states of workflow.
    $states = $flow->get('type_settings')['states'];

    foreach ($states as $name => $state) {
      if ($array) {
        $options[$name] = $name;
      }
      else {
        $options .= "<option value='". $name ."'>" . $name . "</option>";
      }
    }

    return $options;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['list_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $items = $form_state->get('list_num');
    $items[] = '';
    $form_state->set('list_num', $items);
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement()['#options']['id'];
    $items = $form_state->get('list_num');
    unset($items[$element]);
    $form_state->set('list_num', $items);
    $form_state->setRebuild();
  }

  public function getRoles() {
    $options = [];
    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();

    /** @var \Drupal\user\Entity\Role  $rol */
    foreach ($roles as $name => $rol) {
      $options[$rol->id()] = $name;
    }

    unset($options['administrator']);

    return $options;
  }

  public function getWorkflows() {
    $options = [];
    $workflows = $this->entityTypeManager->getStorage('workflow')->loadMultiple();
    foreach ($workflows as $name => $workflow) {
      $options[$workflow->id()] = $name;
    }

    return $options;
  }

}
