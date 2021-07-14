<?php

namespace Drupal\translation_workflow\Entity;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt\Entity\JobItem;

/**
 * Defines a job group entity.
 *
 * @ingroup tmgmt_job
 *
 * @ContentEntityType(
 *   id = "job_group",
 *   label = @Translation("Job group"),
 *   base_table = "job_group",
 *   module = "translation_workflow",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 *   handlers = {
 *     "list_builder" = "Drupal\translation_workflow\Entity\ListBuilder\JobGroupListBuilder"
 *   },
 *   links = {
 *     "collection" = "/admin/translation_workflow/jobgroups"
 *   }
 * )
 */
class JobGroup extends ContentEntityBase {

  const PRIORITY_LOW = 'Low';
  const PRIORITY_NORMAL = 'Normal';
  const PRIORITY_HIGH = 'High';

  /**
   * {@inheritdoc}
   */
public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
  $fields = parent::baseFieldDefinitions($entity_type);

  $fields['items_id'] = BaseFieldDefinition::create('entity_reference')
    ->setLabel(t('Job items id'))
    ->setDescription(t('The items id'))
    ->setSetting('target_type', 'tmgmt_job_item')
    ->setTranslatable(FALSE);

  $fields['jobs'] = BaseFieldDefinition::create('entity_reference')
    ->setLabel(t('Jobs'))
    ->setDescription(t('The jobs id'))
    ->setSetting('target_type', 'tmgmt_job')
    ->setTranslatable(FALSE);

  $fields['priority'] = BaseFieldDefinition::create('list_string')
    ->setLabel(t('Job priority'))
    ->setDescription(t('Priority of job'))
    ->setSetting('allowed_values', [
      static::PRIORITY_LOW,
      static::PRIORITY_NORMAL,
      static::PRIORITY_HIGH
    ]);
  return $fields;
}

public function label() {
  $label = "";

  $items = $this->get('items_id')->getValue();
  $count = count($items);
  $jobItems = [];
  foreach ($items as $item) {
    $jobItem = JobItem::load($item['target_id']);
    if ($jobItem) {
      $jobItems[] = $jobItem;
    }
  }
  if ($count > 0) {


    $source_label = reset($jobItems)->getSourceLabel();
    $t_args = ['@title' => $source_label, '@more' => $count - 1];
    $label = \Drupal::translation()
      ->formatPlural($count, '@title', '@title and @more more', $t_args);

    // If the label length exceeds maximum allowed then cut off exceeding
    // characters from the title and use it to recreate the label.
    if (strlen($label) > Job::LABEL_MAX_LENGTH) {
      $max_length = strlen($source_label) - (strlen($label) - Job::LABEL_MAX_LENGTH);
      $source_label = Unicode::truncate($source_label, $max_length, TRUE);
      $t_args['@title'] = $source_label;
      $label = \Drupal::translation()
        ->formatPlural($count, '@title', '@title and @more more', $t_args);
    }
  }

  return $label;
}

}
