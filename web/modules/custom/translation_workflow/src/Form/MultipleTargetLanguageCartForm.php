<?php

namespace Drupal\translation_workflow\Form;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\Entity\Translator;
use Drupal\tmgmt\Form\CartForm;
use Drupal\translation_workflow\Entity\MultipleTargetLanguageJob;

/**
 *
 */
class MultipleTargetLanguageCartForm extends CartForm {

  /**
   * {@inheritdoc}
   */

  /**
   * Custom form submit callback for tmgmt_cart_cart_form().
   */
  public function submitRequestTranslation(array $form, FormStateInterface $form_state) {
    $target_languages = array_filter($form_state->getValue('target_language'));
    $enforced_source_language = NULL;
    if ($form_state->getValue('enforced_source_language')) {
      $enforced_source_language = $form_state->getValue('source_language');
    }

    $skipped_count = 0;
    $jobItems = [];
    // Group the selected items by source language.
    foreach (JobItem::loadMultiple(array_filter($form_state->getValue('items'))) as $job_item) {
      $targetLanguagesTemp = $target_languages;
      $firstLanguage = array_shift($targetLanguagesTemp);
      $job_item->setTargetLanguage($firstLanguage);
      $job_item->save();
      $source_language = $enforced_source_language ? $enforced_source_language : $job_item->getSourceLangCode();
      if (in_array($source_language, $job_item->getExistingLangCodes())) {
        $jobItems[$job_item->id()] = $job_item;
        foreach ($targetLanguagesTemp as $langCode) {
          if ($langCode !== $source_language) {
            $newJobItem = tmgmt_job_item_create($job_item->getPlugin(), $job_item->getItemType(), $job_item->getItemId(), ['target_language' => $langCode]);
            $newJobItem->save();
            $jobItems[$newJobItem->id()] = $newJobItem;
          }
        }
      }
      else {
        $skipped_count++;
      }
    }

    $languageManager = \Drupal::languageManager();
    $targetLanguageObjects = [];
    foreach ($target_languages as $language) {
      $targetLanguageObjects[] = $languageManager->getLanguage($language);
    }
    $translators = Translator::loadMultiple();

    $job = MultipleTargetLanguageJob::create([
      'source_language' => $source_language,
      'target_languages' => $targetLanguageObjects,
      'uid' => \Drupal::currentUser()->id(),
      'job_items' => $jobItems,
      'translator' => reset($translators),
    ]);

    try {
      $job->save();
      $job->set('label', (string) $job->label())->save();
      foreach ($jobItems as $jobItem) {
        $jobItem->set('tjid', $job->id())->save();
      }
      tmgmt_cart_get()->removeJobItems(array_keys($jobItems));
    }
    catch (EntityStorageException $e) {
      $this->messenger()->addError($this->t('Error creating Multiple target language job'));
      $this->getLogger('translation_workflow')->error('Error creating entity: %message', [
        '%message' => $e->getMessage(),
      ]);
    }

    // Start the checkout process if any jobs were created.
    if ($job) {
      if ($enforced_source_language) {

        $this->messenger()->addWarning(t('You have enforced the job source language which most likely resulted in having a translation of your original content as the job source text. You should review the job translation received from the translator carefully to prevent the content quality loss.'));

        if ($skipped_count) {
          $languages = \Drupal::languageManager()->getLanguages();
          $this->messenger()->addStatus(\Drupal::translation()->formatPlural($skipped_count, 'One item skipped as for the language @language it was not possible to retrieve a translation.',
            '@count items skipped as for the language @language it was not possible to retrieve a translations.', ['@language' => $languages[$enforced_source_language]->getName()]));
        }
      }

      \Drupal::service('tmgmt.job_checkout_manager')->checkoutAndRedirect($form_state, [$job]);
    }
    else {
      $this->messenger()->addError(t('From the selection you made it was not possible to create any translation job.'));
    }
  }

}
