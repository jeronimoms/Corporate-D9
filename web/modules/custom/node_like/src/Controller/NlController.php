<?php

namespace Drupal\node_like\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * General class for node like controller.
 */
class NlController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request_stack) {
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function addLike(Node $node) {
    $response = new AjaxResponse();
    $liked_nodes = '0;';

    // Get the cookies.
    $cookies = $this->request->cookies->all();
    if (array_key_exists('liked-nodes', $cookies)) {
      $liked_nodes = $cookies['liked-nodes'];
    }

    $string_nodes = explode(';', $liked_nodes);
    if (array_search($node->id(), $string_nodes) == 0) {
      // The current value.
      $current_value = $node->get('field_like')->getValue()[0]['value'];

      // Add new like.
      $current_value += 1;
      $node->set('field_like', $current_value)->save();

      // Register the new like with cookie.
      $liked_nodes = $liked_nodes . $node->id() . ';';
      $cookie = new Cookie('liked-nodes', $liked_nodes);

      // Set the responses.
      $response->addCommand(new InvokeCommand('.node_like-like-' . $node->id(), 'attr',
        [
          'title',
          $this->t('Thank you'),
        ])
      );
      $response->addCommand(new HtmlCommand('.node_like-like-' . $node->id(), '<span>' . $current_value . '</span>'));
      $response->addCommand(new InvokeCommand(NULL, 'nlToolTip', ['.node_like-like-' . $node->id()]));
      $response->headers->setCookie($cookie);
    }

    return $response;
  }

}
