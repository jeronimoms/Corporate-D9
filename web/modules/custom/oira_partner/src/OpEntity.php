<?php

namespace Drupal\oira_partner;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormStateInterface;

/**
 * General class for entity hooks.
 */
class OpEntity implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The array of fields.
   *
   * @var array
   */
  protected $fields;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fields = [
      'field_co_author' => 'field_co_author_node',
      'field_workbench_access' => 'field_related_partners',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Implements hook_entity_presave().
   */
  public function entityPreSave(EntityInterface $entity) {
    // Ignore if the entity isn't a node.
    if (!$entity instanceof Node) {
      return;
    }

    foreach ($this->fields as $key => $field_name) {
      // Ignore if the entity doesn't have the one of fields.
      if (!$entity->hasField($key)) {
        continue;
      }

      // The workbench access id.
      $access_id = $entity->get($key)->getString();
      if (empty($access_id)) {
        continue;
      }

      // Find the node with the current workbench access id.
      $partner = $this->entityTypeManager->getStorage('node')->loadByProperties(['type' => 'partner', 'field_workbench_access' => $access_id]);
      /** @var \Drupal\node\Entity\Node $partner_node */
      $partner_node = reset($partner);
      if (empty($partner)) {
        continue;
      }

      // Update the node field.
      if ($entity->hasField($field_name)) {
        $entity->set($field_name, $partner_node->id());
      }
    }
  }

  /**
   * Implements hook_form_alter().
   */
  public function entityFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    if (array_key_exists('#entity_type', $form)) {
      if ($form['#entity_type'] == 'node') {
        foreach ($this->fields as $key => $field_name) {
          // Ignore if the entity doesn't have the one of fields.
          if (!array_key_exists($field_name, $form)) {
            continue;
          }
          // Hidde the field.
          $form[$field_name]['#attributes']['class'][] = 'hidden';
        }
      }
    }
  }

}
