<?php

namespace Drupal\oira_masquerade;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * General class for Form hooks.
 */
class OmForm implements ContainerInjectionInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
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
   * Implements hook_form_alter().
   */
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    if ($form_id == 'masquerade_block_form') {
      // Get the user list.
      $user_list = $this->entityTypeManager->getStorage('user')->loadMultiple();
      $users = [];

      /** @var \Drupal\user\Entity\User  $user */
      foreach ($user_list as $user) {
        if (!$user->hasField('field_user_partner_guid')) {
          continue;
        }

        // The user guid associated with the partner.
        $guid = $user->get('field_user_partner_guid')->getString();

        // Looking for the partner associated.
        $term_entity = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => 'section', 'field_ldap_section_code' => $guid]);

        /** @var \Drupal\taxonomy\Entity\Term  $term */
        $term = reset($term_entity);
        if (empty($term)) {
          continue;
        }

        // Check if the partner is already included in the array.
        $duplicated = FALSE;
        foreach ($users as $id => $array_user) {
          if ($users[$id] == $term->getName()) {
            $duplicated = TRUE;
            break;
          }
        }

        // Insert the user.
        if (!$duplicated) {
          $users[$user->id()] = $term->getName();
        }
      }

      $form['autocomplete']['masquerade_as'] = [
        '#type' => 'select',
        '#title' => 'Username',
        '#options' => $users,
        '#title_display' => 'invisible',
        '#required' => TRUE,
      ];
    }
  }

}
