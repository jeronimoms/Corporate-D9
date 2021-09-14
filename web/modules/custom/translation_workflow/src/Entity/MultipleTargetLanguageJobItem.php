<?php

namespace Drupal\translation_workflow\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\JobItemInterface;

/**
 *
 */
class MultipleTargetLanguageJobItem extends JobItem {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fieldsDefinitions = parent::baseFieldDefinitions($entity_type);
    $fieldsDefinitions['target_language'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Target language code'))
      ->setCardinality(1)
      ->setDescription(t('The target language.'));
    return $fieldsDefinitions;
  }

  /**
   * Returns the target language.
   *
   * @return \Drupal\Core\Language\LanguageInterface
   *   The target language.
   */
  public function getTargetLanguage() {
    return $this->get('target_language')->language;
  }

  /**
   * Returns the target language code.
   *
   * @return string
   *   The target language code
   */
  public function getTargetLangcode() {
    return $this->getTargetLanguage()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function getJob() {
    return MultipleTargetLanguageJob::load($this->getJobId());
  }

  /**
   * {@inheritdoc}
   */
  public function addTranslatedData(array $translation, $key = array(), $status = NULL) {
    $job = $this->getJob();

    if ($this->isInactive()) {
      // The job item can not be inactive and receive translations.
      $this->setState(JobItemInterface::STATE_ACTIVE);
    }

    $this->addTranslatedDataRecursive($translation, $key, $status);
    // Check if the job item has all the translated data that it needs now.
    // Only attempt to change the status to needs review if it is currently
    // active.
    if ($this->isActive()) {
      $data = \Drupal::service('tmgmt.data')->filterTranslatable($this->getData());
      $finished = TRUE;
      foreach ($data as $item) {
        if (empty($item['#status']) || $item['#status'] == TMGMT_DATA_ITEM_STATE_PENDING || $item['#status'] == TMGMT_DATA_ITEM_STATE_PRELIMINARY) {
          $finished = FALSE;
          break;
        }
      }
      if ($finished && $job->hasTranslator()) {
        // There are no unfinished elements left.
        if ($job->getTranslator()->isAutoAccept()) {
          // If the job item is going to be auto-accepted, set to review without
          // a message.
          $this->needsReview(FALSE);
        }
        else {
          // Otherwise, create a message that contains source label, target
          // language and links to the review form.
          $job_url = $job->toUrl()->toString();
          $variables = array(
            '@source' => $this->getSourceLabel(),
            '@language' => $job->getTargetLanguage()->getName(),
            ':review_url' => $this->toUrl('canonical', array('query' => array('destination' => $job_url)))->toString(),
          );
          (!$this->getSourceUrl()) ? $variables[':source_url'] = (string) $job_url : $variables[':source_url'] = $this->getSourceUrl()->toString();
          $this->needsReview('The translation of <a href=":source_url">@source</a> to @language is finished and can now be <a href=":review_url">reviewed</a>.', $variables);
        }
      }
    }
    $this->save();
  }

}
