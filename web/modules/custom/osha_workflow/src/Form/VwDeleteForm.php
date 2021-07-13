<?php

namespace Drupal\osha_workflow\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;

/**
 * General class for Vw delete form.
 */
class VwDeleteForm extends ConfirmFormBase {

  /**
   * The node id.
   *
   * @var string
   */
  private $nodeId;

  /**
   * The user id.
   *
   * @var string
   */
  private $userId;

  /**
   * The return url.
   *
   * @var string
   */
  private $backUrl;

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
    return Url::fromUserInput("/node/$this->node_id/$this->back_url/$this->back_url");
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

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node_id = NULL, $user_id = NULL, $back_url = NULL) {
    // Set extra variables.
    $this->nodeId = $node_id;
    $this->userId = $user_id;
    $this->backUrl = $back_url;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Delete the user from the list.
    $query = $this->database->delete('osha_workflow_' . $this->backUrl)
      ->condition('node_id', $this->nodeId)
      ->condition('user_id', $this->userId);
    $query->execute();

    // Redirect to previus page.
    $form_state->setRedirectUrl(Url::fromUserInput("/node/$this->nodeId/$this->backUrl/$this->backUrl"));
    return parent::buildForm($form, $form_state);
  }

}
