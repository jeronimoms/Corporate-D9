<?php

namespace Drupal\translation_workflow\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\translation_workflow\Entity\PriorityJobInterface;

/**
 * Event class for translation notifications.
 */
class TranslationEvent extends Event {

  const TRANSLATION_CONTENT_READY_TO_PUBLISH = 'translation_workflow.content_ready_to_publish';
  const TRANSLATION_JOB_STATE_CHANGED = 'translation_workflow.job_state_changed';

  // @todo implement content validators.
  const TRANSLATION_CONTENT_VALIDATOR_REMOVED = 'translation_workflow.validator_removed';

  /**
   * Job of the notification.
   *
   * @var \Drupal\translation_workflow\Entity\PriorityJobInterface
   */
  protected $job;

  /**
   * Entity updated.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  private $entity;

  /**
   * Event constructor.
   */
  public function __construct($job, $entity) {
    $this->job = $job;
    $this->entity = $entity;
  }

  /**
   * Get job that fired the event.
   *
   * @return \Drupal\translation_workflow\Entity\PriorityJobInterface
   *   Job that fired the event.
   */
  public function getJob() {
    return $this->job;
  }

  /**
   * Gets updated entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Entity updated.
   */
  public function getEntity() {
    return $this->entity;
  }

}
