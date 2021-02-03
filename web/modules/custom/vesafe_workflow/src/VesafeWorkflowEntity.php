<?php

namespace Drupal\vesafe_workflow;

use Drupal\Core\Access\AccessResult;
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
use Drupal\vesafe_workflow\VesafeWorkFlowHelper;
use Drupal\vesafe_workflow\Form\ApproveForm;

class VesafeWorkflowEntity implements ContainerInjectionInterface {

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
   * @var \Drupal\vesafe_workflow\VesafeWorkFlowHelper
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


  public function __construct(BlockManager $block_manager, VesafeWorkFlowHelper $vasefe_helper, RouteMatchInterface $route_match, FormBuilder $form_builder) {
    $this->blockManager = $block_manager;
    $this->helper = $vasefe_helper;
    $this->routeMatch = $route_match;
    $this->formBuilder = $form_builder;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('vesafe_workflow.helper'),
      $container->get('current_route_match'),
      $container->get('form_builder')
    );
  }

  public function formModerationAlter(&$form, FormStateInterface $form_state, $form_id) {
    // Custom validation to control if the list is empty.
    $form['#validate'][] = [$this, 'formValidateAlter'];
  }

  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
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
  }

  public function preNodeSave(EntityInterface $entity) {
    // Get the new moderation state.
    $moderation_state = $entity->get('moderation_state')->getValue()[0]['value'];

    if ($moderation_state == 'to_be_approved') {
      // Reset the approvers status
      $this->helper->resetUsersStatus('approvers');
    }
  }

  public function viewAlter(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display) {
    if ($this->routeMatch->getRouteName() == 'entity.node.latest_version') {
      if ($this->helper->checkUserAccess('approvers')) {
        array_unshift($build, $this->formBuilder->getForm(ApproveForm::class));
      }
    }

    // Set the block as first element.
    $plugin_block = $this->blockManager->createInstance('vesafe_workflow_block', []);
    array_unshift($build, $plugin_block->build());
  }

}
