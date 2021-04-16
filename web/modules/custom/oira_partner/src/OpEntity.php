<?php

namespace Drupal\oira_partner;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\oira_partner\OpEntityUpdateManager;

/**
 * General class for entity hooks.
 */
class OpEntity implements ContainerInjectionInterface {

  /**
   * The Oria entity update manager
   *
   * @var \Drupal\oira_partner\OpEntityUpdateManager
   */
  protected $opEntityManager;

  /**
   * The array of fields.
   *
   * @var array
   */
  protected $fields;

  /**
   * {@inheritdoc}
   */
  public function __construct(OpEntityUpdateManager $op_entity_manager) {
    $this->opEntityManager = $op_entity_manager;
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
      $container->get('oira_partner.update')
    );
  }

  /**
   * Implements hook_entity_presave().
   */
  public function entityPreSave(EntityInterface $entity) {
    $this->opEntityManager->updatePartners($entity);
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
          //$form[$field_name]['#attributes']['class'][] = 'hidden';
        }
      }
    }
  }

}
