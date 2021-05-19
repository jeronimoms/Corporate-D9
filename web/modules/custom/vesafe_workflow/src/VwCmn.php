<?php

namespace Drupal\vesafe_workflow;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\vesafe_workflow\VwHelper;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * General class for Content moderatoin notification hooks.
 */
class VwCmn implements ContainerInjectionInterface {

  use StringTranslationTrait;

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(BlockManager $block_manager, VwHelper $vasefe_helper, RouteMatchInterface $route_match, FormBuilder $form_builder, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->blockManager = $block_manager;
    $this->helper = $vasefe_helper;
    $this->routeMatch = $route_match;
    $this->formBuilder = $form_builder;
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * Hook bridge.
   *
   * @see \hook_content_moderation_notification_mail_data_alter()
   */
  public function mailDataAlter(EntityInterface $entity, array &$data) {
    // Workflow settings of current node.
    $workflow = $this->helper->getWorkflow();

    // Determine the list.
    $table = '';
    $config = $this->configFactory->getEditable('vesafe_workflow.general');
    $lists = $config->get('lists');
    if (array_key_exists('list', $lists)) {
      foreach ($lists['list'] as $list) {
        if ($list['workflow'] == $workflow->id() && $list['workflow_state'] == $this->helper->getNodeModerationState()) {
          $table = strtolower($list['name']);
        }
      }
    }

    /** @var \Drupal\content_moderation_notifications\Entity\ContentModerationNotification $notification */
    $notification = $data['notification'];

    // Alter the notification to send the email only to the first user.
    if ($notification->getOriginalId() == 'to_fnal_draft') {
      $users = $this->helper->getModerationList($table);
      $users = (array) $users;
      /** @var \Drupal\user\Entity\User $user */
      $user = $this->entityTypeManager->getStorage('user')->load($users[0]->user_id);
      $data['to'] = [
        0 => $user->getEmail(),
      ];
      $this->helper->resetUsersStatus('reviewers');
    }

    // Alter the notification to send the email to the next user of.
    // list if exists.
    if ($notification->getOriginalId() == 'final_draft_to') {
      $next_user = $this->helper->getNextUser($table);
      if (!empty($next_user)) {
        $data['to'] = [
          0 => $next_user->getEmail(),
        ];
      }
    }

    // Alter the notification to send the email only to the first user.
    if ($notification->getOriginalId() == 'to_approved') {
      $users = $this->helper->getModerationList($table);
      $users = (array) $users;
      /** @var \Drupal\user\Entity\User $user */
      $user = $this->entityTypeManager->getStorage('user')->load($users[0]->user_id);
      $data['to'] = [
        0 => $user->getEmail(),
      ];

      $this->helper->resetUsersStatus('approvers');
    }

    // Alter the notification to send the email to the next user of.
    // list if exists.
    if ($notification->getOriginalId() == 'to_be_approved_to_approved') {
      $next_user = $this->helper->getNextUser($table);
      if (!empty($next_user)) {
        $data['to'] = [
          0 => $next_user->getEmail(),
        ];
      }
    }
  }

}
