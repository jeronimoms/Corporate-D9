<?php

namespace Drupal\vesafe_workflow;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\vesafe_workflow\VwHelper;
use Drupal\vesafe_workflow\Form\VwApproveForm;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;

/**
 * General class for entity hooks.
 */
class VwEntity implements ContainerInjectionInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * The Vesafe helper service.
   *
   * @var \Drupal\vesafe_workflow\VwHelper
   */
  protected $helper;

  /**
   * The Request object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The form builder manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $formBuilder;

  /**
   * The Config Factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The AccountInterface object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public function __construct(BlockManager $block_manager, VwHelper $vasefe_helper, RouteMatchInterface $route_match, FormBuilder $form_builder, ConfigFactoryInterface $config_factory, AccountInterface $account) {
    $this->blockManager = $block_manager;
    $this->helper = $vasefe_helper;
    $this->routeMatch = $route_match;
    $this->formBuilder = $form_builder;
    $this->configFactory = $config_factory;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('vesafe_workflow.helper'),
      $container->get('current_route_match'),
      $container->get('form_builder'),
      $container->get('config.factory'),
      $container->get('current_user')
    );
  }

  /**
   * Form alter for for good practices and key articles.
   *
   * @see \hook_form_alter()
   */
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    if ($form_id == 'content_moderation_entity_moderation_form') {
      $state = $this->helper->getNodeModerationState();

      // Remove the moderation form if the current user has the role "approver".
      if ($state == 'to_be_approved' && array_search('approver', $this->account->getRoles())) {
        // Pending cofirmation! $form['#access'] = FALSE;.
        return;
      }

      // Custom validation to control if the list is empty.
      $form['#validate'][] = [$this, 'formValidateAlter'];
    }

    if ($form_id == 'node_good_practice_edit_form' || $form_id == 'node_key_article_edit_form') {
      // Load the vesafe block.
      $plugin_block = $this->blockManager->createInstance('vesafe_workflow_block', []);
      $vesafe_block = $plugin_block->build();

      // Set the position of new item on form.
      $vesafe_block['#weight'] = 99;
      $vesafe_block['#group'] = 'footer';

      // Set the new item.
      $form['vesafe_block'] = $vesafe_block;

      // Custom validation to control if the list is empty.
      $form['#validate'][] = [$this, 'formValidateAlter'];
    }
  }

  /**
   * Adds a new Vesafe Workflow validaton.
   *
   * @see \hook_form_alter()
   */
  public function formValidateAlter(&$form, FormStateInterface $form_state) {
    // Get the new moderation state.
    $moderation_state = ($form_state->hasValue('moderation_state')) ? $form_state->getValue('moderation_state')[0]['value'] : $form_state->getValue('new_state');
    if ($moderation_state == 'to_be_approved') {
      // Get the list of approvers.
      $list = $this->helper->getModerationList('approvers');
      if (empty($list)) {
        // Return error if the list is empty.
        $form_state->setErrorByName('Empty Approvers', $this->t('The list of approvers is empty.'));
      }
    }

    if ($moderation_state == 'final_draft') {
      // Get the list of approvers.
      $list = $this->helper->getModerationList('reviewers');
      if (empty($list)) {
        // Return error if the list is empty.
        $form_state->setErrorByName('Empty Reviewers', $this->t('The list of reviewers is empty.'));
      }
    }
  }

  /**
   * View alter to add the approver form in latest revision view.
   *
   * @see \hook_entity_view_alter()
   */
  public function viewAlter(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display) {
    // Filter by node type.
    if (!$entity instanceof Node || $this->routeMatch->getRouteName() == 'layout_builder.defaults.node.view') {
      return;
    }

    // Workflow settings of current node.
    $workflow = $this->helper->getWorkflow();

    if (!isset($workflow) || empty($workflow)) {
      return;
    }

    $workflow_settings = $workflow->get('type_settings')['entity_types']['node'];

    // Check if current entity is included in the workflow settings.
    if (!in_array($entity->bundle(), $workflow_settings)) {
      return;
    }

    // Validate if is the full display.
    if ($display->id() !== 'node.' . $entity->bundle() . '.full') {
      return;
    }

    // Determine the list.
    $table = '';
    $config = $this->configFactory->getEditable('vesafe_workflow.general');
    $lists = $config->get('lists');
    if (array_key_exists('list', $lists)) {
      foreach ($lists['list'] as $list) {
        if ($list['workflow'] == $workflow->id() && $list['workflow_state'] == $this->helper->getNodeModerationState()) {
          $table = $list['name'];
        }
      }
    }

    // Show the approve form if the list exists and the user has access.
    if (!empty($table) && $this->helper->checkUserAccess(strtolower($table))) {
      array_unshift($build, $this->formBuilder->getForm(VwApproveForm::class, $table));
    }

    // Set the block as first element.
    $plugin_block = $this->blockManager->createInstance('vesafe_workflow_block', []);
    array_unshift($build, $plugin_block->build());
  }

  /**
   * Alter the local tasks.
   *
   * @see \hook_local_tasks_alter()
   */
  public function localTastAlter(&$local_tasks) {
    // Get VW default settings.
    $config = $this->configFactory->getEditable('vesafe_workflow.general');
    $lists = $config->get('lists');
    if (array_key_exists('list', $lists)) {
      foreach ($lists['list'] as $list) {
        $name = strtolower($list['name']);
        // Generate the new local task.
        $local_tasks['entity.node.' . $name . '_node'] = [
          'route_name' => "vesafe_workflow." . $name . ".list",
          'base_route' => "entity.node.canonical",
          'class' => 'Drupal\Core\Menu\LocalTaskDefault',
          'route_parameters' => [
            'list_name' => $name,
          ],
          'options' => [],
          'title' => $name,
        ];
      }
    }

  }

}
