<?php

namespace Drupal\vesafe_workflow\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\vesafe_workflow\VesafeWorkFlowHelper;

class ApproveForm extends FormBase {

  /**
   * The Vesafe helper service.
   *
   * @var \Drupal\vesafe_workflow\VesafeWorkFlowHelper
   */
  protected $helper;

  /**
   * {@inheritdoc}
   */
  public function __construct(VesafeWorkFlowHelper $vasefe_helper) {
    $this->helper = $vasefe_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('vesafe_workflow.helper')
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Show the form if the node contains the correct moderation state.
    if ($this->helper->getNodeModerationState() !== 'to_be_approved') {
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
        '#value' => $this->t('Reject, send back to ' . $workflow_settings['to_be_reviewed']['label']),
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
    $this->helper->approveUser('approvers');

    // If all users approved the content, then set the node as "ready_to_publish".
    if ($this->helper->getNodeModerationState()) {
      $this->helper->setNodeModerationState('ready_to_publish');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitReject(array &$form, FormStateInterface $form_state) {
    // Set the previous state.
    $this->helper->setNodeModerationState('to_be_reviewed');

    // Reset the current users approver status.
    $this->helper->resetUsersStatus('approvers');
  }

}
