<?php

namespace Drupal\vesafe_workflow\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\vesafe_workflow\VwHelper;

class VwApproveForm extends FormBase {

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
   * {@inheritdoc}
   */
  public function __construct(VwHelper $vasefe_helper, ConfigFactoryInterface $config_factory) {
    $this->helper = $vasefe_helper;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('vesafe_workflow.helper'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'approve_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $table = NULL) {
    // Set the table name.
    $form_state->set('vesafe_workflow_table', strtolower($table));

    // Store the list configration.
    $vesafe_config = $this->configFactory->getEditable('vesafe_workflow.general');
    foreach ($vesafe_config->get('lists')['list'] as $list) {
      if ($list['name'] == ucfirst($form_state->get('vesafe_workflow_table'))) {
        $form_state->set('vesafe_workflow_list_configuration', $list);
      }
    }

    $list_state = $form_state->get('vesafe_workflow_list_configuration')['workflow_state'];
    $list_previous_state = $form_state->get('vesafe_workflow_list_configuration')['workflow_state_previous'];

    // Show the form if the node contains the correct moderation state.
    if ($this->helper->getNodeModerationState() !== $list_state) {
      return $form;
    }

    // Get the list of states of current bundle.
    $workflow_settings = $this->helper->getWorkFlowStates();

    if (isset($workflow_settings)) {
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Approve'),
      ];

      $form['reject'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reject, send back to ' . $workflow_settings[$list_previous_state]['label']),
        '#submit' => [[$this, 'submitReject']],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Update the status of current user.
    $this->helper->approveUser($form_state->get('vesafe_workflow_table'));

    // If all users approved the content, then set the node as the next state defined in the list.
    if ($this->helper->getModerationListStatus($form_state->get('vesafe_workflow_table'))) {
      $this->helper->setNodeModerationState($form_state->get('vesafe_workflow_list_configuration')['workflow_state_next']);
    } else {
      $mailManager = \Drupal::service('plugin.manager.mail');
      $next_user = $this->helper->getNextUser($form_state->get('vesafe_workflow_table'));
      $mailManager->mail(
        'vesafe_workflow',
        $form_state->get('vesafe_workflow_table'),
        $next_user->getEmail(),
        $next_user->getPreferredLangcode(),
        [
          'subject' => 'test',
          'body' => 'hola',
        ],
        NULL,
        TRUE
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitReject(array &$form, FormStateInterface $form_state) {
    // Set the previous state.
    $this->helper->setNodeModerationState($form_state->get('vesafe_workflow_list_configuration')['workflow_state_previous']);

    // Reset the current users approver status.
    $this->helper->resetUsersStatus($form_state->get('vesafe_workflow_table'));
  }

}
