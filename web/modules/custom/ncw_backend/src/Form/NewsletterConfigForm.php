<?php

namespace Drupal\ncw_backend\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Config form to save osha newsletter configs.
 */
class NewsletterConfigForm extends ConfigFormBase {

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames() {
    return ['ncw_backend.settings'];
  }

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'newsletter_api_config_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ncw_backend.settings');
    $form['newsletter_api'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Crm api url'),
      '#default_value' => $config->get('newsletter_api'),
      '#required' => TRUE,
      '#description' => $this->t('Insert crm api url. You can use {email} token to indicate where email should appear in final call url.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('ncw_backend.settings');
    if ($form_state->hasValue('newsletter_api')) {
      $config->set('newsletter_api', $form_state->getValue('newsletter_api'));
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
