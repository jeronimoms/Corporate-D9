<?php

namespace Drupal\translation_workflow\EventSubscriber;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\translation_workflow\Event\TranslationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for translation notifications.
 */
class TranslationEventSubscriber implements EventSubscriberInterface {

  /**
   * Mail manager to send emails.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  private $mailManager;

  /**
   * Handler constructor.
   */
  public function __construct(MailManagerInterface $mailManager) {
    $this->mailManager = $mailManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      TranslationEvent::TRANSLATION_CONTENT_READY_TO_PUBLISH => ['onReadyToPublish'],
      TranslationEvent::TRANSLATION_JOB_STATE_CHANGED => ['onJobStateChanged'],
    ];
  }

  /**
   * Event handler for a job state change.
   *
   * @param \Drupal\translation_workflow\Event\TranslationEvent $event
   *   Translation event.
   */
  public function onJobStateChanged(TranslationEvent $event) {
    // $this->mailManager->mail('user', 'password_reset', $user->getEmail(), $preferredLangcode, $params);
  }

  /**
   * Event handler for a content ready to publish.
   *
   * @param \Drupal\translation_workflow\Event\TranslationEvent $event
   *   Translation event.
   */
  public function onReadyToPublish(TranslationEvent $event) {
    // $this->mailManager->mail('user', 'password_reset', $user->getEmail(), $preferredLangcode, $params);
  }

}
