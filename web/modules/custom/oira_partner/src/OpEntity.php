<?php

namespace Drupal\oira_partner;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The AccountInterface object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public function __construct(OpEntityUpdateManager $op_entity_manager, EntityTypeManagerInterface $entity_type_manager, AccountInterface $account) {
    $this->opEntityManager = $op_entity_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->account = $account;
    $this->fields = [
      'field_co_author' => 'field_co_author_node',
      'field_workbench_access' => 'field_related_partners',
      'field_third_partner' => 'field_third_partner_node',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('oira_partner.update'),
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * Implements hook_entity_presave().
   */
  public function entityPreSave(EntityInterface $entity) {
    // Store the fields if the user is parner.
    if (in_array('partner', $this->account->getRoles())) {
      if ($entity instanceof Node) {
        foreach ($this->fields as $key => $field_name) {
          // Ignore if the entity doesn't have the one of fields.
          if ($entity->hasField($key)) {
            $entity->set($key, $this->opEntityManager->getTermParent());
          }
          if ($entity->hasField($field_name)) {
            $this->opEntityManager->updatePartners($entity, FALSE, FALSE);
          }
        }
      }
      return;
    }

    // Store the field from normal edito form.
    $this->opEntityManager->updatePartners($entity, FALSE, FALSE);
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

          // Change any option to "COUNTRY - OPTION".
          $options = &$form[$key]['widget']['#options'];
          foreach ($options as $term_id => $name) {
            if ($term_id == '_none') {
              continue;
            }

            // Get the country by partner
            $country_label = $this->opEntityManager->getCountryFromPartner($term_id);
            if (isset($country_label)) {
              // Set the new label
              $options[$term_id] = $country_label . ' - ' . $options[$term_id];
            }
          }

          // If the user is a partner, hidde the fields too.
          if (in_array('partner', $this->account->getRoles())) {
            // Hidde the field.
            $form[$key]['#attributes']['class'][] = 'hidden';
          }
        }
      }
    }
  }

}
