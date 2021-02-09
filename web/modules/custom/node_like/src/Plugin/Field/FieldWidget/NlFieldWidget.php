<?php

namespace Drupal\node_like\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\WidgetBase;

/**
 * Plugin implementation of node like widget.
 *
 * @FieldWidget(
 *   id = "node_like_widget",
 *   module = "node_like",
 *   label = @Translation("Like count"),
 *   field_types = {
 *     "node_like"
 *   }
 * )
 */
class NlFieldWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : 0;
    $element += [
      '#type' => 'number',
      '#default_value' => $value,
      '#element_validate' => [
        [$this, 'validate'],
      ],
      '#attributes' => [
        'disabled' => TRUE,
      ],
    ];

    return ['value' => $element];
  }

  /**
   * {@inheritdoc}
   */
  public function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];
    if (isset($value)) {
      $form_state->setValueForElement($element, 0);
      return;
    }
  }

}
