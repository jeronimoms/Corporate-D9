<?php

namespace Drupal\translation_workflow\Entity;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\JobItemInterface;

/**
 *
 */
class MultipleTargetLanguageJobItem extends JobItem {

  /**
   *
   */
  const STATE_TRANSLATION_VALIDATION_REQUIRED = 5;

  /**
   *
   */
  const STATE_TRANSLATION_VALIDATED = 6;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fieldsDefinitions = parent::baseFieldDefinitions($entity_type);
    $fieldsDefinitions['target_language'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Target language code'))
      ->setCardinality(1)
      ->setDescription(t('The target language.'));

    $fieldsDefinitions['retranslation_data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Retranslation data'))
      ->setDescription(t('The source retranslation data'));
    return $fieldsDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public static function getStates() {
    return [
      static::STATE_ACTIVE => t('On Translation'),
      static::STATE_REVIEW => t('Translated'),
      static::STATE_ACCEPTED => t('Ready to Publish'),
      static::STATE_ABORTED => t('Translation Rejected'),
      static::STATE_INACTIVE => t('Inactive'),
      static::STATE_TRANSLATION_VALIDATION_REQUIRED => t('Content Validation Required'),
      static:: STATE_TRANSLATION_VALIDATED => t('Translation Validated'),
    ];
  }

  /**
   * Get page count for items.
   *
   * @return string
   *   Page count.
   */
  public function getPageCount() {
    return number_format($this->getCharactersCount() / MultipleTargetLanguageJob::CHARACTERS_PER_PAGE, 2, ',', '');
  }

  /**
   * Returns characters count for items.
   *
   * @return int
   *   Characters count.
   */
  public function getCharactersCount() {
    $countedItems = [];
    $count = 0;
    $dataService = \Drupal::service('tmgmt.data');
    $itemId = $this->getItemId();
    if (!isset($countedItems[$itemId])) {
      $countedItems[$itemId] = TRUE;
      $data = array_filter($dataService->flatten($this->getData()), function ($value) {
        return !(empty($value['#text']) || (isset($value['#translate']) && $value['#translate'] === FALSE));
      });
      foreach ($data as $key => $field) {
        if (isset($field['#text'])) {
          $text = $field['#text'];
          $text = strip_tags(html_entity_decode($text));
          // C2A0 is unicode nbsp.
          $text = preg_replace("/\x{00A0}|&nbsp;|\s/", '', $text);
          $count += mb_strlen($text, 'utf-8');
        }
      }
    }

    return $count;
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
   * Assign a target language.
   *
   * @param string $langcode
   *   Target language to assign.
   */
  public function setTargetLanguage(string $langcode) {
    $this->set('target_language', $langcode);
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
  public function addTranslatedData(array $translation, $key = [], $status = NULL) {
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
      $data = \Drupal::service('tmgmt.data')
        ->filterTranslatable($this->getData());
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
          $variables = [
            '@source' => $this->getSourceLabel(),
            '@language' => $job->getTargetLanguage()->getName(),
            ':review_url' => $this->toUrl('canonical', ['query' => ['destination' => $job_url]])
              ->toString(),
          ];
          (!$this->getSourceUrl()) ? $variables[':source_url'] = (string) $job_url : $variables[':source_url'] = $this->getSourceUrl()
            ->toString();
          $this->needsReview('The translation of <a href=":source_url">@source</a> to @language is finished and can now be <a href=":review_url">reviewed</a>.', $variables);
        }
      }
    }
    $this->save();
  }

  /**
   * Check if item referenced by job exists.
   *
   * @return bool
   *   If referred entity exists.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function currentItemExists() {
    return self::itemExists($this->getItemType(), $this->getItemId());
  }

  /**
   * Check if item referenced by job exists.
   *
   * @param string $entityType
   *   Entity type name to check.
   * @param string $itemId
   *   Entity id to load.
   *
   * @return bool
   *   If referred entity exists.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function itemExists(string $entityType, string $itemId) {
    return !is_null(\Drupal::entityTypeManager()
      ->getStorage($entityType)
      ->load($itemId));
  }

  /**
   * Check if there are job items created for those conditions.
   *
   * @param array $conditions
   *   Conditions array.
   *
   * @return bool
   *   If there are job items created with those conditions.
   */
  public static function jobItemExists(array $conditions = []) {
    $query = \Drupal::entityQuery('tmgmt_job_item');
    foreach ($conditions as $field => $values) {
      if (is_array($values)) {
        $query->condition($field, $values, 'IN');
      }
      else {
        $query->condition($field, $values);
      }
    }
    $query->condition('state', [
      static::STATE_ACTIVE,
      static::STATE_ACCEPTED,
      static::STATE_REVIEW,
    ], 'IN');
    $existingJobItems = $query->execute();
    return !empty($existingJobItems);
  }

  /**
   * Set retranslation data.
   *
   * @param array $retranslationData
   *   Retranslation data to add.
   */
  public function setRetranslationData(array $retranslationData = []) {
    $this->set('retranslation_data', Json::encode($retranslationData));
  }

  /**
   * Get retranslation dataadded to job.
   *
   * @return array|null
   *   Retranslation data.
   */
  public function getRetranlationData() {
    return Json::decode($this->get('retranslation_data')->value);
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceData() {
    $retranslationData = $this->getRetranlationData();
    $ret = parent::getSourceData();
    if (!empty($retranslationData)) {
      $ret = array_filter($ret, function ($key) use ($retranslationData) {
        return in_array($key, array_keys($retranslationData));
      }, ARRAY_FILTER_USE_KEY);
    }
    return $ret;
  }

}
