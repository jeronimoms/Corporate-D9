<?php

namespace Drupal\napo_content_cart;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

trait NccCartTrait {

  use StringTranslationTrait;

  protected function addElement(Node $node) {
    return [
      '#type' => 'link',
      '#title' => $this->t('Download Video'),
      '#url' => Url::fromRoute('content_cart.addcart',
        [
          'node' => $node->id(),
        ],
        [
          'attributes' => [
            'class' => ['use-ajax', 'napo-cart-add'],
          ],
        ],
      ),
    ];
  }

  protected function removeElement(Node $node) {
    return [
      '#type' => 'link',
      '#title' => $this->t('Remove Video'),
      '#url' => Url::fromRoute('content_cart.deleteone',
        [
          'node' => $node->id(),
        ],
        [
          'attributes' => [
            'class' => ['use-ajax', 'napo-cart-delete'],
          ],
        ],
      ),
    ];
  }
}
