<?php

namespace Drupal\oira_workflow\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class OwOiraConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'oira_workflow.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oira_workflow_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->config('oira_workflow.config');

    $moderation_minutes = $config->get('moderation_minutes');
    $form['moderation_minutes'] = [
      '#type' => 'number',
      '#title' => $this->t('How many minutes can the partner still edit the node?'),
      '#min' => 0,
      '#max' => 100,
      '#description' => $this->t('After partner submits the content, he can still edit for this amount of time'),
      '#default_value' => (isset($moderation_minutes) ? $moderation_minutes : 0)
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the filled values.
    $values = $form_state->getValues();

    // Store the values in the config form.
    $this->config('oira_workflow.config')
      ->set('moderation_minutes', $values['moderation_minutes'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
