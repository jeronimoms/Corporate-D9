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
          'type' => 'int',
          'unsigned' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */

  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(t('Like value'))
      ->setSetting('unsigned', TRUE);

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
