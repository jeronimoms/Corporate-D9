<?php

namespace Drupal\vesafe_workflow\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Session\AccountInterface;
use Drupal\vesafe_workflow\VwHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;
use Drupal\vesafe_workflow\Form\VwApproverAddForm;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * General class for Vw approvers controller.
 */
class VwUserListController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The database manager.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The Vesafe helper service.
   *
   * @var \Drupal\vesafe_workflow\VwHelper
   */
  protected $helper;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database, VwHelper $vasefe_helper) {
    $this->database = $database;
    $this->helper = $vasefe_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('vesafe_workflow.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($list, $node, $user, $weight) {
    $data = [
      'table' => $list,
      'node_id' => $node,
      'user_id' => $user,
      'weight' => $weight,
    ];

    $this->helper->setUserWeight($list, $data);

    return new JsonResponse($data);
  }
}
