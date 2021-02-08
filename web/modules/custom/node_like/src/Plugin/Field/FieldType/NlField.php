<?php

namespace Drupal\node_like\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of node like field type.
 *
 * @FieldType(
 *   id = "node_like",
 *   module = "node_like",
 *   label = @Translation("Like"),
 *   description = @Translation("Demonstrates a field composed of an RGB color."),
 *   default_widget = "node_like_widget",
 *   default_formatter = "node_like_formatter"
 * )
 */

class NlField extends FieldItemBase{

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'numeric',
          'size' => 'tiny',
          'not null' => FALSE,
          'precision' => 1,
          'scale' => 1,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */

  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('numeric')
      ->setLabel(t('Like value'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

}
