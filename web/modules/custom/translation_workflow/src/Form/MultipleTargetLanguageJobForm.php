<?php

namespace Drupal\translation_workflow\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\tmgmt\Form\TmgmtFormBase;
use Drupal\tmgmt\JobInterface;
use Drupal\views\Views;

/**
 *
 */
class MultipleTargetLanguageJobForm extends TmgmtFormBase {

  /**
   *
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /**
     * @var \Drupal\translation_workflow\Entity\MultipleTargetLanguageJob $job
     */
    $job = $this->entity;
    $form['info'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['tmgmt-ui-job-info', 'clearfix']],
      '#weight' => 0,
    ];

    $form['info']['created'] = [
      '#type' => 'item',
      '#title' => $this->t('Created'),
      '#markup' => \Drupal::service('date.formatter')
        ->format($job->getCreatedTime(), 'short'),
      '#prefix' => '<div class="tmgmt-ui-created tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    ];
    $form['info']['file_sent'] = [
      '#type' => 'item',
      '#title' => t('File sent'),
      '#markup' => $job->isSentToCdt() ? t('Yes') : t('No'),
      '#prefix' => '<div class="tmgmt-file-sent tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    ];

    $form['language_info'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['tmgmt-ui-job-info', 'clearfix']],
      '#weight' => 1,
    ];

    $targetLanguagesCodes = $job->getTargetLangcodes();

    $form['language_info']['show_target_languages'] = [
      '#title' => t('Target languages') . ' (' . count($targetLanguagesCodes) . ')',
      '#type' => 'checkboxes',
      '#default_value' => $targetLanguagesCodes,
      '#options' => array_combine($targetLanguagesCodes, $targetLanguagesCodes),
      '#disabled' => TRUE,
      '#weight' => 1,
      '#attributes' => ['class' => ['container-inline']],
    ];

    $untranslated = array_map(function ($language) {
      return $language->getId();
    }, \Drupal::languageManager()->getLanguages());

    $form['language_info']['show_target_languages_not'] = [
      '#title' => '',
      '#type' => 'checkboxes',
      '#options' => array_combine($untranslated, $untranslated),
      '#disabled' => TRUE,
      '#weight' => 2,
      '#attributes' => ['class' => ['container-inline']],
    ];

    $form['info']['character_count'] = [
      '#type' => 'item',
      '#title' => $this->t('Total character count'),
      '#markup' => number_format($job->getCharactersCount()),
      '#prefix' => '<div class="tmgmt-ui-characters-count tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    ];

    $form['info']['page_count'] = [
      '#type' => 'item',
      '#title' => $this->t('Page count'),
      '#markup' => $job->getPageCount(),
      '#prefix' => '<div class="tmgmt-ui-page-count tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    ];

    $form['info']['priority'] = [
      '#type' => 'item',
      '#title' => $this->t('Priority'),
      '#markup' => $job->getPriority(),
      '#prefix' => '<div class="tmgmt-ui-created tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#value' => $job->getPriority(),
    ];

    $form['job_items_wrapper'] = [
      '#type' => 'container',
      '#weight' => 10,
      '#prefix' => '<div id="tmgmt-ui-job-checkout-details">',
      '#suffix' => '</div>',
    ];
    $form['footer'] = tmgmt_color_job_item_legend();
    $form['footer']['#weight'] = 100;

    // Translation jobs.
    $form['job_items_wrapper']['items'] = [
      '#type' => 'details',
      '#title' => t('Job items'),
      '#open' => in_array($job->getState(), [JobInterface::STATE_ACTIVE]),
      '#prefix' => '<div class="' . 'tmgmt-ui-job-items ' . ($job->isSubmittable() ? 'tmgmt-ui-job-submit' : 'tmgmt-ui-job-manage') . '">',
      'view' => [
        '#type' => 'view',
        '#name' => 'tmgmt_job_items',
        '#display_id' => $job->isSubmittable() ? 'checkout' : 'submitted',
        '#arguments' => [$job->id()],
        '#weight' => 30,
      ],
      '#attributes' => [
        'class' => [
          'tmgmt-ui-job-items',
          $job->isSubmittable() ? 'tmgmt-ui-job-submit' : 'tmgmt-ui-job-manage',
        ],
      ],
      '#suffix' => '</div>',
    ];

    $form['translator_wrapper'] = [
      '#type' => 'details',
      '#title' => t('Translator information'),
      '#open' => TRUE,
      '#weight' => 20,
    ];

    $form['translator_wrapper']['checkout_info'] = $this->checkoutInfo($job);

    if ($messagesView = Views::getView('tmgmt_job_messages')) {
      $output = $messagesView->preview('embed', [$job->id()]);
      if ($messagesView->result) {
        $form['messages'] = [
          '#type' => 'details',
          '#title' => $messagesView->storage->label(),
          '#open' => TRUE,
          '#weight' => 50,
        ];
        $form['messages']['view'] = $output;
      }
    }

    $form['#attached']['library'][] = 'tmgmt/admin';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $parentActions = parent::actionsElement($form, $form_state);
    $job = $this->getEntity();

    if (!$job->isSentToCdt()) {
      // Add sent to cdt submit button.
      $parentActions['sent_to_cdt'] = [
        '#type' => 'submit',
        '#value' => $this->t('File sent to CDT'),
        '#weight' => 14,
        '#submit' => ['::submitSentToCdt'],
      ];
    }
    $parentActions['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromRoute('view.translation_workflow_jobs_overview.page_1'),
      '#weight' => 15,
    ];

    $form['abort_job'] = [
      '#type' => 'link',
      '#value' => t('Abort job'),
      '#url' => Url::fromRoute('entity.tmgmt_job_multiple_target.abort_form', [
        'tmgmt_job_multiple_target' => $job,
      ]),
      '#access' => $job->isAbortable(),
    ];

    return $parentActions;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->messenger()->addStatus($this->t('Translation job saved.'));
  }

  /**
   * Set job as file sent to CDT.
   */
  public function submitSentToCdt() {
    $job = $this->getEntity();
    $job->set('file_sent', TRUE)->save();
  }

  /**
   * Helper function for retrieving the rendered job checkout information.
   */
  public function checkoutInfo(JobInterface $job) {
    // The translator might have been disabled or removed.
    if (!$job->hasTranslator()) {
      return ['#markup' => t('The job has no provider assigned.')];
    }
    $translator = $job->getTranslator();
    $plugin_ui = $this->translatorManager->createUIInstance($translator->getPluginId());
    return $plugin_ui->checkoutInfo($job);
  }

}
