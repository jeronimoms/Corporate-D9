<?php

namespace Drupal\node_like\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'field_example_simple_text' formatter.
 *
 * @FieldFormatter(
 *   id = "node_like_formatter",
 *   module = "node_like",
 *   label = @Translation("Simple like formatter"),
 *   field_types = {
 *     "node_like"
 *   }
 * )
 */
class NlFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $node = $item->getEntity();
      $title = '';
      $liked_nodes = '0;';

      // Get the cookies.
      $cookies = \Drupal::request()->cookies->all();

      if (array_key_exists('liked-nodes', $cookies)) {
        $liked_nodes = $cookies['liked-nodes'];
      }

      $string_nodes = explode(';', $liked_nodes);
      if (array_search($node->id(), $string_nodes) > 0) {
        $title = $this->t('Already liked!');
      }

      $elements[$delta] = [
        '#type' => 'link',
        '#title' => $item->value,
        '#attributes' => [
          'class' => ['use-ajax', 'node_like-like-' . $node->id()],
        ],
        '#url' => Url::fromRoute('node_like.controller', [
          'node' => $node->id(),
        ],
        [
          'attributes' => [
            'class' => ['use-ajax', 'node_like-like', 'nl-tooltip'],
            'title' => $title,
          ],
        ]),
        '#attached' => [
          'library' => 'core/drupal.ajax',
        ],
      ];
    }

    return $elements;
  }

}
