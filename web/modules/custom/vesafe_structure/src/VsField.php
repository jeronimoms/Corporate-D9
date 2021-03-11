<?php

namespace Drupal\vesafe_structure;

/**
 * General class for Field hooks.
 */
class VsField {

  /**
   * Form alter vesafe content types.
   *
   * @see \hook_preprocess_HOOK()
   */
  public function preprocessField(&$variables) {
    $element = $variables['element'];
    $field_name = $element['#field_name'];
    if ($field_name == 'field_risks') {
      $this->setGpUrl($field_name, 'risks', $variables);
    }
    if ($field_name == 'field_vehicles') {
      $this->setGpUrl($field_name, 'vehicles', $variables);
    }
  }

  /**
   * Set the correct URL for facet filters.
   *
   * @param string $field_name
   *   The field name.
   * @param string $filter
   *   The filter name.
   * @param array $variables
   *   The complete array.
   */
  public function setGpUrl($field_name, $filter, &$variables) {
    $element = $variables['element'];
    /** @var \Drupal\node\Entity\Node $node */
    $node = $element['#object'];
    $values = $node->get($field_name)->getValue();
    foreach ($values as $i => $value) {
      $id = $value['target_id'];
      $link = $variables['items'][$i]['content']['#markup'];
      $variables['items'][$i]['content']['#markup'] = preg_replace('/<a(.*?)href=(["\'])(.*?)\\2(.*?)>/i', '<a href="/good-practices?f%5B0%5D=' . $filter . '%3A' . $id . '">', $link);
    }
  }

}
