<?php

namespace Drupal\node_like\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\HtmlCommand;
use \Symfony\Component\HttpFoundation\Cookie;

class NlController extends ControllerBase {

  public function addLike(Node $node) {
    $response = new AjaxResponse();
    //\Drupal::request()->cookies->set('hola', 'yolo');
    user_cookie_save(['quemepaspen' => [0,1]]);
    ksm(\Drupal::request()->cookies->all());

    $cookie = new Cookie('test','1');



    // The current value.
    $current_value = $node->get('field_like')->getValue()[0]['value'];

    // Add new like.
    $current_value += 1;
    $node->set('field_like', $current_value)->save();

    $selector = '.node_like-like-' . $node->id();

    $response->addCommand(new HtmlCommand($selector, '<span>'.$current_value.'</span>'));
    return $response;
  }
}
