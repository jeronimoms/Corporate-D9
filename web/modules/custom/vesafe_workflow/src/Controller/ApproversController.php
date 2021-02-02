<?php

namespace Drupal\vesafe_workflow\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;
use Drupal\vesafe_workflow\Form\ApproverAddForm;
use Drupal\vesafe_workflow\Form\ApproveForm;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;


class ApproversController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $formBuilder;

  /**
   * The database manager.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * ChooseBlockController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public  function __construct(EntityTypeManagerInterface $entity_type_manager, FormBuilder $form_builder, Connection $database, BlockManager $block_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
    $this->database = $database;
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
      $container->get('database'),
      $container->get('plugin.manager.block')
    );
  }


  public function list(Node $node) {
    // Set the final array output.
    $content = [];

    // Block of current states.
    $plugin_block = $this->blockManager->createInstance('vesafe_workflow_block', []);
    $content['block'] = $plugin_block->build();

    // Show the form to approve or reject the changes.
    $content['form_approve'] = $this->formBuilder->getForm(ApproveForm::class);

    // Pre table message.
    $content['message'] = [
      '#markup' => $this->t('List of approvers'),
    ];

    // Default table.
    $rows = [];
    $headers = [
      $this->t('User'),
      $this->t('Mail'),
      $this->t('Status'),
      $this->t('Operations'),
    ];

    // Get the actual entries from database.
    $query = $this->database->select('vesafe_workflow_approvers', 'v')
      ->condition('node_id', $node->id() , '=')
      ->fields('v', ['id', 'node_id', 'user_id', 'status']);

    $results = $query->execute()->fetchAll();

    // Create the rows.
    foreach ($results as $data) {
      $rows[] = [
        'user_id' => $this->getUserName($data->user_id),
        'user_mail' => $this->getUserMail($data->user_id),
        'status' => $data->status,
        'operation' => Link::createFromRoute('Delete', 'vesafe_workflow.delete_form',
        [
          'node_id' => $node->id(),
          'user_id' => $data->user_id,
          'back_url' => 'approvers',
        ]
        ),
      ];
    }

    // Final table output.
    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => t('No users found'),
    ];

    // Show the Approver form to add users inline.
    $content['form_add'] = $this->formBuilder->getForm(ApproverAddForm::class);
    $content['form_add']['node_id']['#value'] = $node->id();
    $content['form_add']['node_id']['#attributes']['class'] = ['vesafe-workflow-node-field'];

    return $content;
  }

  public function getUser($user_id) {
    if (empty($user_id)) {
      return '';
    }

    return $this->entityTypeManager->getStorage('user')->load($user_id);
  }

  public function getUserName($user_id) {
    /** @var \Drupal\user\Entity\User $user */
    if ($user = $this->getUser($user_id)) {
      return $user->getAccountName();
    }

    return '';
  }

  public function getUserMail($user_id) {
    /** @var \Drupal\user\Entity\User $user */
    if ($user = $this->getUser($user_id)) {
      return $user->getEmail();
    }

    return '';
  }

}
