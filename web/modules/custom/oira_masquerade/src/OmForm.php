<?php

namespace Drupal\oira_masquerade;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;

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
   * The AccountInterface object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxy $account) {
    $this->entityTypeManager = $entity_type_manager;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user')
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

        /** @var \Drupal\taxonomy\Entity\Term  $term */
        $term = $this->getTermsPartnerByUser($user);

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
        if (!$duplicated && $this->isPartnerPublished($user)) {
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

      $form['#validate'][] = [$this, 'formValidateAlter'];
      $form['#submit'][] = [$this, 'omSubmit'];
    }

    if ($form_id == 'user_login_form') {
      $form['#submit'][] = [$this, 'omLoginSubmit'];
    }
  }

  /**
   * Adds a new Oira Masquerade validaton.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object
   */
  public function formValidateAlter(&$form, FormStateInterface $form_state) {
    // Get the partner id to set the redirect.
    $nid = $this->getNodePartnerByUser($form_state->getValue('masquerade_target_account'));
    if (empty($nid)) {
      $form_state->setErrorByName('Oira Masquerade', $this->t('URL of partnet not found'));
      return;
    }

    // Store in the form the partner id.
    $form_state->setValue('om_partner_nid', $nid);
  }

  /**
   * Adds a new Oira Masquerade submit.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object
   */
  public function omSubmit(array &$form, FormStateInterface $form_state) {
    // Redirect to partner page.
    $form_state->setRedirect('entity.node.canonical', ['node' => $form_state->getValue('om_partner_nid')]);
  }

  /**
   * Adds a new Oira Masquerade submit.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object
   */
  public function omLoginSubmit(array &$form, FormStateInterface $form_state) {
    // The account session.
    $user = $this->account->getAccount();
    // Filter by role.
    if (in_array('partner', $user->getRoles())) {
      // Access if the partner is published.
      if ($this->isPartnerPublished($user)) {
        // Redirect to partner node.
        if ($nid = $this->getNodePartnerByUser($user)) {
          $form_state->setRedirect('entity.node.canonical', ['node' => $nid]);
        }
      }
    }
  }

  /**
   * Method to return the taxonomy of the partner.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user object
   */
  public function getTermsPartnerByUser(User $user) {
    // The user guid associated with the partner.
    $guid = $user->get('field_user_partner_guid')->getString();

    // Looking for the partner associated.
    $term_entity = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => 'section', 'field_ldap_section_code' => $guid]);

    /** @var \Drupal\taxonomy\Entity\Term  $term */
    $term = reset($term_entity);
    if (!$term) {
      return '';
    }
    return $term;
  }

  /**
   * Method to return the node id of the partner.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user object
   */
  public function getNodePartnerByUser(User $user) {
    // Get the taxonomy partner.
    /** @var \Drupal\taxonomy\Entity\Term  $term */
    $term = $this->getTermsPartnerByUser($user);

    if (empty($term)) {
      return '';
    }
    // Get the node partner
    $node = $this->entityTypeManager->getStorage('node')->loadByProperties(['title' => $term->getName()]);
    /** @var \Drupal\node\Entity\Node $node */
    $node = reset($node);
    if (!$node) {
      return '';
    }

    return $node->id();
  }

  /**
   * Method to check if the partner is published.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user object
   */
  public function isPartnerPublished(User $user) {
    $nid = $this->getNodePartnerByUser($user);
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    return boolval($node->get('status')->getString());
  }

}
