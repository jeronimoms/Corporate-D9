<?php

namespace Drupal\translation_workflow_node_block;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class NCWForm
 * @package Drupal\translation_workflow_node_block
 */
class NCWForm extends FormBase {
  /**
   * @param $form
   * @param FormStateInterface $form_state
   * @param $form_id
   */
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    // edition form
    if ($form_id == 'node_25th_anniversary_edit_form' || $form_id == 'node_article_edit_form' ||
      $form_id == 'node_banner_edit_form' || $form_id == 'node_blog_edit_form' || $form_id == 'node_calls_edit_form' ||
      $form_id == 'node_press_contact_edit_form' || $form_id == 'node_dangerous_substances_edit_form' || $form_id == 'node_directive_edit_form' ||
      $form_id == 'node_e_guide_edit_form' || $form_id == 'node_events_edit_form' || $form_id == 'node_gallery_edit_form' ||
      $form_id == 'node_guideline_edit_form' || $form_id == 'node_highlight_edit_form' || $form_id == 'node_infographic_edit_form' ||
      $form_id == 'node_job_vacancies_edit_form' || $form_id == 'node_musculoskeletal_disorders_edit_form' ||
      $form_id == 'node_news_edit_form' || $form_id == 'node_newsletter_content_edit_form' || $form_id == 'node_newsletter_article_edit_form' ||
      $form_id == 'node_note_to_editor_edit_form' || $form_id == 'node_publication_edit_form' || $form_id == 'node_register_records_edit_form' ||
      $form_id == 'node_seminar_edit_form' || $form_id == 'node_slideshare_edit_form' || $form_id == 'node_thesaurus_edit_form' ||
      $form_id == 'node_webform_edit_form' || $form_id == 'node_wiki_page_edit_form' || $form_id == 'node_youtube_edit_form' ) {

      // Get the id of the current node
      $node = \Drupal::routeMatch()->getParameter('node');
      if ($node instanceof \Drupal\node\NodeInterface) {
        $nid = $node->id();

        // Get the state of the current node
        $queryUserSectionResult = \Drupal::database()
          ->query('SELECT t.item_id, t.state, t.target_language
              FROM tmgmt_job_item t
              WHERE t.item_id = :item_id', array(":item_id" => $nid,)
          );

        // Block node if it has been sent to translate.
        foreach ($queryUserSectionResult as $item) {
          if ($item->state == 1 || $item->state == 2 || $item->state == 5 || $item->state == 6) {
            $form['#disabled'] = TRUE;
            $this->messenger()->addWarning($this->t('This node has been sent to translate.'));
          }
        }
      }
    }
  }

  public function getFormId(){ // TODO: Implement getFormId() method.
  }

  public function buildForm(array $form, FormStateInterface $form_state){ // TODO: Implement buildForm() method.
    }

  public function submitForm(array &$form, FormStateInterface $form_state){ // TODO: Implement submitForm() method.
    }
}
