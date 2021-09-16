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
    $form['info']['source_language'] = [
      '#title' => $this->t('Source language'),
      '#type' => 'item',
      '#markup' => $job->getSourceLanguage()->getName(),
      '#prefix' => '<div id="tmgmt-ui-source-language" class="tmgmt-ui-source-language tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#value' => $job->getSourceLangcode(),
    ];
    $form['info']['target_language'] = [
      '#title' => $this->t('Target languages'),
      '#type' => 'item',
      '#markup' => implode(',', array_map(function ($language) {
        return $language->getName();
      }, $job->getTargetLanguages())),
      '#prefix' => '<div id="tmgmt-ui-target-language" class="tmgmt-ui-target-language tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#value' => $job->getTargetLangcodes(),
    ];

    $form['info']['translator'] = [
      '#type' => 'item',
      '#title' => $this->t('Provider'),
      '#markup' => $job->getTranslatorLabel(),
      '#prefix' => '<div class="tmgmt-ui-translator tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#value' => $job->getTranslatorId(),
    ];

    $form['info']['word_count'] = [
      '#type' => 'item',
      '#title' => $this->t('Total words'),
      '#markup' => number_format($job->getWordCount()),
      '#prefix' => '<div class="tmgmt-ui-word-count tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    ];

    $form['info']['tags_count'] = [
      '#type' => 'item',
      '#title' => $this->t('Total HTML tags'),
      '#markup' => number_format($job->getTagsCount()),
      '#prefix' => '<div class="tmgmt-ui-tags-count tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    ];

    $form['info']['created'] = [
      '#type' => 'item',
      '#title' => $this->t('Created'),
      '#markup' => $this->dateFormatter->format($job->getCreatedTime()),
      '#prefix' => '<div class="tmgmt-ui-created tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#value' => $job->getCreatedTime(),
    ];

    $form['info']['priority'] = [
      '#type' => 'item',
      '#title' => $this->t('Priority'),
      '#markup' => $job->getPriority(),
      '#prefix' => '<div class="tmgmt-ui-created tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#value' => $job->getPriority(),
    ];

    $jobItemView = Views::getView('tmgmt_job_items');
    if ($jobItemView) {
      $form['job_items_wrapper'] = [
        '#type' => 'container',
        '#weight' => 10,
        '#prefix' => '<div id="tmgmt-ui-job-checkout-details">',
        '#suffix' => '</div>',
      ];
      $form['footer'] = tmgmt_color_job_item_legend();
      $form['footer']['#weight'] = 100;
      // Translation jobs.
      $output = $jobItemView->preview($job->isSubmittable() ? 'checkout' : 'submitted', [$job->id()]);
      $form['job_items_wrapper']['items'] = [
        '#type' => 'details',
        '#title' => t('Job items'),
        '#open' => in_array($job->getState(), [JobInterface::STATE_ACTIVE]),
        '#prefix' => '<div class="' . 'tmgmt-ui-job-items ' . ($job->isSubmittable() ? 'tmgmt-ui-job-submit' : 'tmgmt-ui-job-manage') . '">',
        'view' => $output,
        '#attributes' => [
          'class' => [
            'tmgmt-ui-job-items',
            $job->isSubmittable() ? 'tmgmt-ui-job-submit' : 'tmgmt-ui-job-manage',
          ],
        ],
        '#suffix' => '</div>',
      ];
    }

    $form['translator_wrapper'] = [
      '#type' => 'details',
      '#title' => t('Provider information'),
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

    if (!$this->getEntity()->isSentToCdt()) {
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

    return $parentActions;
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
