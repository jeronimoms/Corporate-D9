<?php

namespace Drupal\osha_workflow\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Session\AccountInterface;
use Drupal\osha_workflow\VwHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;
use Drupal\osha_workflow\Form\VwApproverAddForm;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;

/**
 * General class for Vw approvers controller.
 */
class VwApproversController extends ControllerBase implements ContainerInjectionInterface {

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
   * The AccountInterface object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The osha helper service.
   *
   * @var \Drupal\osha_workflow\VwHelper
   */
  protected $helper;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FormBuilder $form_builder, Connection $database, BlockManager $block_manager, AccountInterface $account, VwHelper $vasefe_helper) {
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
    $this->database = $database;
    $this->blockManager = $block_manager;
    $this->account = $account;
    $this->helper = $vasefe_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
      $container->get('database'),
      $container->get('plugin.manager.block'),
      $container->get('current_user'),
      $container->get('osha_workflow.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function list(Node $node, $list_name) {


    $state = $this->helper->getNodeModerationState();
    if (!isset($state)) {
      return [
        '#markup' => $this->t('The current entity is not associated with any workflow.'),
      ];
    }

    // Set the table of current list.
    $table = strtolower($list_name);

    // Set the final array output.
    $content = [];

    // Block of current states.
    $plugin_block = $this->blockManager->createInstance('osha_workflow_block', []);
    $content['block'] = $plugin_block->build();


    // Show the Approver form to add users inline.
    $content['form_add'] = $this->formBuilder->getForm(VwApproverAddForm::class, $table);
    $content['form_add']['node_id']['#value'] = $node->id();
    $content['form_add']['node_id']['#attributes']['class'] = ['osha-workflow-node-field'];

    return $content;
  }

  /**
   * {@inheritdoc}
   */
  public function getUser($user_id) {
    if (empty($user_id)) {
      return '';
    }

    return $this->entityTypeManager->getStorage('user')->load($user_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getUserName($user_id) {
    /** @var \Drupal\user\Entity\User $user */
    if ($user = $this->getUser($user_id)) {
      return $user->getAccountName();
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getUserMail($user_id) {
    /** @var \Drupal\user\Entity\User $user */
    if ($user = $this->getUser($user_id)) {
      return $user->getEmail();
    }

    return '';
  }

}
