<?php

namespace Drupal\ncw_migration;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class NmForm {

  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    ksm($form_id);
    if ($form_id == 'node_news_edit_form') {
      if ($this->checkNodeMigration('highlight', $form_state)) {
        ksm($form);
        /*$form['title']['#access'] = FALSE;
        $form['field_summary']['#access'] = FALSE;
        $form['body']['#access'] = FALSE;
        $form['field_publication_date']['#access'] = FALSE;
        $form['field_aditional_resources']['#access'] = FALSE;
        $form['field_tags']['#access'] = FALSE;*/
      }
    }
  }

  public function checkNodeMigration($id, FormStateInterface $form_state) {
    // Return if the entity associated is not a Node.
    if (!$form_state->getFormObject()->getEntity() instanceof Node) {
      return FALSE;
    }

    // Get node entity.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $form_state->getFormObject()->getEntity();

    // Get migration from id.
    /** @var \Drupal\migrate\Plugin\Migration $migration */
    $migration = \Drupal::service('plugin.manager.migration')->createInstance($id);

    // Load map of migration.
    /** @var \Drupal\migrate\Plugin\migrate\id_map\Sql $id_map */
    $id_map = $migration->getIdMap();

    // Check if current node has been migrated.
    if (!$id_map->getRowByDestination(['nid' => $node->id()])) {
      return FALSE;
    }

    return TRUE;
  }

}
