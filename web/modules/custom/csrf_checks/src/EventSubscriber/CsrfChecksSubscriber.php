<?php

namespace Drupal\csrf_checks\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


/**
 * CsrfChecksSubscriber class to prevent csrf attacks.
 */
class CsrfChecksSubscriber implements EventSubscriberInterface {


  /**
   * Drupal logger object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;


  /**
   * The class constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   The LoggerChannelFactoryInterface object.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_channel_factory) {
    $this->logger = $logger_channel_factory->get('csrf_checks');
  }

  /**
   * To control the request.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event object.
   */
  public function checkForCsrfAttack(RequestEvent $event) {
    /** @var \Symfony\Component\HttpFoundation\Request $request */
    $request = $event->getRequest();
    global $base_root;

    // The base url.
    $base = str_replace([':', '/', '.'], ['\:', '\/', '\.'], $base_root);


    if ($request->getMethod() == 'POST') {
      // Check the origin.
      $http_origin = $request->server->get('HTTP_ORIGIN');
      if (isset($http_origin)) {
        if (preg_match("/^$base/", $http_origin) != 1) {
          $this->blockCsrfRequest('HTTP_ORIGIN not allowed from ip:' . $request->getClientIp());
          $event->setResponse(Response::create('x-origin', 403, []));
        }
      }

      // Check the referer header.
      $refererToCheck = $request->server->get('HTTP_REFERER');
      if (isset($refererToCheck)) {
        if (preg_match("/^$base/", $refererToCheck) != 1) {
          // Possible CSRF attack. Block the request.
          $this->blockCsrfRequest('HTTP_REFERER not allowed from ip:' . $request->getClientIp());
          $event->setResponse(Response::create('x-referer', 403, []));
        }
      }
      else {
        // Possible CSRF attack. Block the request.
        $this->blockCsrfRequest('HTTP_REFERER is empty from ip:' . $request->getClientIp());
        $event->setResponse(Response::create('x-referer-empty', 403, []));
      }

    }

  }

  /**
   * Block the request if contains a csrf attack.
   *
   * @param string $message
   *   The message to the log.
   */
  public function blockCsrfRequest($message) {
    $this->logger->error($message);
   // throw new AccessDeniedHttpException();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkForCsrfAttack'];
    return $events;
  }

}
