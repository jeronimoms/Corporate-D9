<?php

namespace Drupal\oshwiki_import\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Config form to save OshWiki ULR configs.
 */
class OshWikiConfigForm extends ConfigFormBase {

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames() {
    return ['oshwiki_import.settings'];
  }

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'oshwiki_import_api_config_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('oshwiki_import.settings');
    $form['oshwiki_import_api'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OshWiki hostname configuration'),
      '#default_value' => $config->get('oshwiki_import_api'),
      '#required' => TRUE,
      '#description' => $this->t('Insert OshWiki hostname and protocol (i.e. https://oshwiki.eu). Please do not add trailing slash.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('oshwiki_import.settings');
    if ($form_state->hasValue('oshwiki_import_api')) {
      $config->set('oshwiki_import_api', $form_state->getValue('oshwiki_import_api'));
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
