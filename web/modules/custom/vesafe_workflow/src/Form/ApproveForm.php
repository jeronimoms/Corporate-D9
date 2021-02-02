<?php

namespace Drupal\vesafe_workflow\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\RouteMatchInterface;

class ApproveForm extends FormBase {

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
   * The Request object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $database, RouteMatchInterface $route_match) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'approve_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $node = $this->getNode();
    ksm($node->toArray());

    // Get the list of states of current bundle.
    /** @var \Drupal\workflows\Entity\Workflow $workflow */
    $workflow = \Drupal::service('content_moderation.moderation_information')->getWorkflowForEntityTypeAndBundle('node', $node->bundle());
    $workflow_settings = $workflow->get('type_settings');
    ksm($workflow_settings);

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Approve'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  public function getUsers() {
    // Output array.
    $output = [];

    // List of users.
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple();

    /** @var \Drupal\user\Entity\User $user */
    foreach ($users as $user) {
      if ($user->id() == 0) {
        continue;
      }
      $output[$user->id()] = $user->getDisplayName();
    }

    return $output;
  }

  /**
   * Gets the las revision node.
   *
   * @return array|\Drupal\node\Entity\Node
   *   The node if exists.
   */
  protected function getNode() {
    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof Node) {
      /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $storage */
      $storage = $this->entityTypeManager->getStorage('node');
      $revision = $storage->getLatestRevisionId($node->id());
      return $storage->loadRevision($revision);
    }

    return [];
  }

}
