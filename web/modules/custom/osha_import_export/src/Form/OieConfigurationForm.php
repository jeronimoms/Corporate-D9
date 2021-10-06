<?php

namespace Drupal\osha_import_export\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * General class for Nm settings form.
 */
class OieConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'osha_import_export.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'osha_import_export_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->config('osha_import_export.config');

    $form['hwc_events'] = [
      '#type' => 'textfield',
      '#title' => $this->t('HWCEvents endpoint URI'),
      '#description' => $this->t('Uri that will be appended to the Root endpoint URL defined'),
      '#default_value' => $config->get('hwc_events'),
      '#required' => TRUE,
    ];

    $form['hwc_news'] = [
      '#type' => 'textfield',
      '#title' => $this->t('HWCNews endpoint URI'),
      '#description' => $this->t('Uri that will be appended to the Root endpoint URL defined'),
      '#default_value' => $config->get('hwc_news'),
      '#required' => TRUE,
    ];

    $form['oira_news'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OiraNews endpoint URI'),
      '#description' => $this->t('Uri that will be appended to the Root endpoint URL defined'),
      '#default_value' => $config->get('oira_news'),
      '#required' => TRUE,
    ];

    $form['hwc_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('HWC endpoint URL'),
      '#default_value' => $config->get('hwc_endpoint'),
      '#required' => TRUE,
    ];

    $form['oira_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OIRA endpoint URLL'),
      '#default_value' => $config->get('oira_endpoint'),
      '#required' => TRUE,
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
    $this->config('osha_import_export.config')
      ->set('hwc_events', $values['hwc_events'])
      ->set('hwc_news', $values['hwc_news'])
      ->set('oira_news', $values['oira_news'])
      ->set('hwc_endpoint', $values['hwc_endpoint'])
      ->set('oira_endpoint', $values['oira_endpoint'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
