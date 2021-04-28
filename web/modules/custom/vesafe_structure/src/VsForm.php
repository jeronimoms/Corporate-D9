<?php

namespace Drupal\vesafe_structure;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * General class for Form hooks.
 */
class VsForm {

  /**
   * Form alter vesafe content types.
   *
   * @see \hook_form_alter()
   */
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    if ($form_id == 'node_did_you_know_slide_form') {
      // Include new form submission.
      array_unshift($form['actions']['submit']['#submit'], [$this, 'didYouKnowSubmitAlter']);
    }

    // Change "Authored on" to "Creation date".
    if ($form_id == 'node_key_article_edit_form') {
      $form['created']['widget'][0]['value']['#title'] = new TranslatableMarkup('Creation date');
    }

  }

  /**
   * {@inheritdoc}
   */
  public function didYouKnowSubmitAlter($form, FormStateInterface $form_state) {
    // Get the body value without HTML.
    $body = strip_tags($form_state->getValue('body')[0]['value']);

    // Set the title with body value.
    $form_state->setValue('title', $body);
  }

}
