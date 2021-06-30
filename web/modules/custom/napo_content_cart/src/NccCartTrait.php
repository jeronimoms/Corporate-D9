<?php

namespace Drupal\napo_content_cart;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Methods to help Cart elements.
 */
trait NccCartTrait {

  use StringTranslationTrait;

  /**
   * Get the add element.
   *
   * @return array
   *   The array with the link.
   */
  protected function addElement(Node $node) {
    return [
      '#type' => 'link',
      '#title' => $this->t('Download Video'),
      '#url' => Url::fromRoute('content_cart.add',
        [
          'node' => $node->id(),
        ],
        [
          'attributes' => [
            'class' => ['use-ajax', 'napo-cart-add-' . $node->id()],
          ],
        ],
      ),
    ];
  }

  /**
   * Get the remove element.
   *
   * @return array
   *   The array with the link.
   */
  protected function removeElement(Node $node, $centre = 0) {
    return [
      '#type' => 'link',
      '#title' => $this->t('Remove Video'),
      '#url' => Url::fromRoute('content_cart.delete',
        [
          'node' => $node->id(),
          'centre' => $centre,
        ],
        [
          'attributes' => [
            'class' => ['use-ajax', 'napo-cart-delete-' . $node->id()],
          ],
        ],
      ),
    ];
  }
}
