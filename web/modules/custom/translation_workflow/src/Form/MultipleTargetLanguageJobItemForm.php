<?php

namespace Drupal\translation_workflow\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tmgmt\Form\JobItemForm;
use Drupal\translation_workflow\Entity\MultipleTargetLanguageJobItem;

/**
 *
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

      if (isset($parentForm['info']['target_language'])) {
        $parentForm['info']['target_language']['#markup'] = $item->getTargetLanguage()
          ->getName();
      }
    }
    return $parentForm;
  }

}
