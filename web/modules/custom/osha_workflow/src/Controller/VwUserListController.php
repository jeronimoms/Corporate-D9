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
   * The osha helper service.
   *
   * @var \Drupal\osha_workflow\VwHelper
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
      $container->get('osha_workflow.helper')
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
