<?php

namespace Drupal\translation_workflow\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tmgmt\Form\JobItemForm;
use Drupal\translation_workflow\Entity\MultipleTargetLanguageJobItem;

/**
 * Class to override job item form.
 */
class MultipleTargetLanguageJobItemForm extends JobItemForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $parentForm = parent::form($form, $form_state);
    $item = $this->entity;
    if ($item instanceof MultipleTargetLanguageJobItem) {
      if (isset($parentForm['info']['translator'])) {
        unset($parentForm['info']['translator']);
      }

      // We need to alter target_language to show apropiate value and
      // we change name of it to avoid saving an empty value on entity.
      if (isset($parentForm['info']['target_language'])) {
        $parentForm['info']['target_language']['#markup'] = $item->getTargetLanguage()
          ->getName();
        $newInfo = [];
        array_walk($parentForm['info'], function ($item, $key) use (&$newInfo) {
          if ($key == 'target_language') {
            $key = 'target_language_item';
          }
          $newInfo[$key] = $item;
        });
        $parentForm['info'] = $newInfo;
      }
    }
    return $parentForm;
  }

}
