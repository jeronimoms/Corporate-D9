<?php

namespace Drupal\node_like\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

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

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#attributes' => [
          'style' => 'color: ' . $item->value,
        ],
        '#value' => $this->t('Likes @code', ['@code' => $item->value]),
      ];
    }

    return $elements;

  }

}
