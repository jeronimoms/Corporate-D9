<?php

namespace Drupal\search_and_replace\Form;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\scanner\Form\ScannerConfirmForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Confirmation Form operations messages.
 */
class SarScannerConfirmForm extends ScannerConfirmForm {

  use StringTranslationTrait;
  /**
   * The Private temporary storage factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStore;

  /**
   * {@inheritdoc}
   */
  public function __construct(PrivateTempStoreFactory $tempStore) {
    parent::__construct($tempStore);
    $this->tempStore = $tempStore;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $build = parent::buildForm($form, $form_state);
    $scannerStore = $this->tempStore->get('scanner');
    $operations = [
      'search',
      'replace',
      'mode',
      'wholeword',
      'regex',
      'preceded',
      'followed',
      'published',
      'language',
      'results',
      'results_final',
    ];
    foreach ($operations as $value) {
      $values[$value] = $scannerStore->get($value);
    }
    return $build;
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $scannerStore = $this->tempStore->get('scanner');
    foreach ([
      'search',
      'replace',
      'mode',
      'wholeword',
      'regex',
      'preceded',
      'followed',
      'published',
      'language',
      'results',
      'results_final',
    ] as $value) {
      $values[$value] = $scannerStore->get($value);
    }
    $fields = \Drupal::config('scanner.admin_settings')->get('fields_of_selected_content_type');
    $operations = [];
    // Build an array of batch operation jobs. Batch job will need the field
    // and the filter values the users entered in the form.
    foreach ($fields as $field) {
      $operations[] = [
        '\Drupal\scanner\Form\ScannerConfirmForm::batchReplace',
        [
          $field,
          $values,
        ],
      ];
    }
    $batch = [
      'title' => $this->t('Scanner Replace Batch'),
      'operations' => $operations,
      'finished' => '\Drupal\scanner\Form\ScannerConfirmForm::batchFinished',
      'progress_message' => $this->t('Processed @current out of @total'),
    ];
    batch_set($batch);
    // Redirect to the scanner page after the batch is done.
    $form_state->setRedirect('scanner.admin_content');
  }
}
