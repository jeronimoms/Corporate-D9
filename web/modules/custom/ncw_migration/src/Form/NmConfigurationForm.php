<?php

namespace Drupal\ncw_migration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * General class for Nm settings form.
 */
class NmConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ncw_migration.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ncw_migration_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->config('ncw_migration.config');

    $form['highlights_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Highlights endpoint URI'),
      '#description' => $this->t('Uri that will be appended to the Root endpoint URL defined'),
      '#default_value' => $config->get('highlights_endpoint'),
      '#required' => TRUE,
    ];

    $form['news_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('News endpoint URI'),
      '#description' => $this->t('Uri that will be appended to the Root endpoint URL defined'),
      '#default_value' => $config->get('news_endpoint'),
      '#required' => TRUE,
    ];

    $form['country_status_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tax country status endpoint URI'),
      '#description' => $this->t('Uri that will be appended to the Root endpoint URL defined'),
      '#default_value' => $config->get('country_status_endpoint'),
      '#required' => TRUE,
    ];

    $form['country_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tax country endpoint URI'),
      '#description' => $this->t('Uri that will be appended to the Root endpoint URL defined'),
      '#default_value' => $config->get('country_endpoint'),
      '#required' => TRUE,
    ];

    $form['tag_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tax tags endpoint URI'),
      '#description' => $this->t('Uri that will be appended to the Root endpoint URL defined'),
      '#default_value' => $config->get('tag_endpoint'),
      '#required' => TRUE,
    ];

    $form['wiki_categories_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wiki categories endpoint URI'),
      '#description' => $this->t('Uri that will be appended to the Root endpoint URL defined'),
      '#default_value' => $config->get('wiki_categories_endpoint'),
      '#required' => TRUE,
    ];

    $form['wiki_pages_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wiki pages endpoint URI'),
      '#description' => $this->t('Uri that will be appended to the Root endpoint URL defined'),
      '#default_value' => $config->get('wiki_pages_endpoint'),
      '#required' => TRUE,
    ];

    $form['root_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Root endpoint URL'),
      '#description' => $this->t('Uri that will be appended to the Root endpoint URL defined'),
      '#default_value' => $config->get('root_endpoint'),
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
    $this->config('ncw_migration.config')
      ->set('highlights_endpoint', $values['highlights_endpoint'])
      ->set('news_endpoint', $values['news_endpoint'])
      ->set('country_status_endpoint', $values['country_status_endpoint'])
      ->set('country_endpoint', $values['country_endpoint'])
      ->set('tag_endpoint', $values['tag_endpoint'])
      ->set('wiki_categories_endpoint', $values['wiki_categories_endpoint'])
      ->set('wiki_pages_endpoint', $values['wiki_pages_endpoint'])
      ->set('root_endpoint', $values['root_endpoint'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
