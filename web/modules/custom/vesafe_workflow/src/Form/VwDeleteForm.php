<?php

namespace Drupal\vesafe_workflow\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Render\Element;

class VwDeleteForm extends ConfirmFormBase {

  private $node_id;
  private $user_id;
  private $back_url;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database manager.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $database) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Do you want to remove this user from approvers list?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromUserInput("/node/$this->node_id/$this->back_url");
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Remove from queue');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vesaf_workflows_delete_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $node_id = NULL, $user_id = NULL, $back_url = NULL) {
    // Set extra variables.
    $this->node_id = $node_id;
    $this->user_id = $user_id;
    $this->back_url = $back_url;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Delete the user from the list.
    $query = $this->database->delete('vesafe_workflow_approvers')
      ->condition('node_id', $this->node_id)
      ->condition('user_id', $this->user_id);
    $query->execute();

    // Redirect to previus page.
    $form_state->setRedirectUrl(Url::fromUserInput("/node/$this->node_id/$this->back_url"));
    return parent::buildForm($form, $form_state);
  }

}
