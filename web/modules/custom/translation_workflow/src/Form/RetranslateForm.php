<?php

namespace Drupal\translation_workflow\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 *
 */
class RetranslateForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $node = $this->entity;
    $form['title'] = [
      '#type' => 'html_tag,',
      '#tag' => 'h1',
      '#value' => 'Re-translation for ' . $node->label(),
    ];
    $form['desc']['#markup'] = 'Choose the fields and text that you want to re-translate';
    $translationLanguages = $node->getTranslationLanguages();
    $defaultLanguage = \Drupal::languageManager()->getDefaultLanguage();
    if (isset($translationLanguages[$defaultLanguage->getId()])) {
      unset($translationLanguages[$defaultLanguage->getId()]);
    }
    if (empty($translationLanguages)) {
      $this->messenger()->addWarning($this->t('The node is not translated so you cannot re-translate.'));
      $redirectResponse = new TrustedRedirectResponse($node->toUrl('drupal:content-translation-overview')->toString());
      $redirectResponse->send();
    }
    $fieldsDefinitions = \Drupal::service('entity_field.manager')
      ->getFieldDefinitions($node->getEntityTypeId(), $node->bundle());
    foreach ($fieldsDefinitions as $fieldName => $fieldsDefinition) {
      if ($fieldsDefinition->isTranslatable() && in_array($fieldsDefinition->getType(), [
        'string',
        'text_with_summary',
        'text_long',
      ])) {
        $value = $node->get($fieldName)->getString();
        switch ($fieldsDefinition->getType()) {
          case 'string':
          case 'text_long':
            $form[$fieldName] = [
              '#type' => 'checkbox',
              '#prefix' => '<label>' . $fieldsDefinition->getLabel() . '</label>',
              '#title' => $value,
            ];
            break;

          case 'text_with_summary':
            $domDocument = new \DOMDocument('1.0', 'utf-8');
            $domDocument->loadHTML(mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8'));
            $paragraphs = $domDocument->getElementsByTagName('p');
            $options = [];
            foreach ($paragraphs as $paragraph) {
              $elemId = $paragraph->getAttribute('id');
              if (preg_match('/tmgmt-\d/', $elemId)) {
                $options[$elemId] = $domDocument->saveHTML($paragraph);
              }
            }
            $form[$fieldName] = [
              '#type' => 'checkboxes',
              '#title' => $fieldsDefinition->getLabel(),
              '#options' => $options,
            ];
            break;
        }
      }
    }
    return $form;
  }

}
