<?php

namespace Drupal\node_like\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AlertCommand;
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

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /**
     * @var  array $delta
     * @var \Drupal\node_like\Plugin\Field\FieldType\NlField $item
     */
    foreach ($items as $delta => $item) {
      $node = $item->getEntity();
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
            'class' => ['use-ajax', 'node_like-like'],
          ],
        ]),
      ];
    }

    return $elements;

  }

}
