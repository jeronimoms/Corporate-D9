<?php

namespace Drupal\vesafe_workflow\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\Entity\Node;
use Drupal\Component\Utility\SortArray;

/**
 * Provides a 'Vesafe Workflow' Block.
 *
 * @Block(
 *   id = "vesafe_workflow_block",
 *   admin_label = @Translation("Vesafe Workflow"),
 * )
 */
class VesafeWorkflowBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The AccountInterface object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The Request object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $account, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->account = $account;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [
      '#type' => 'container',
      'content' => [],
      '#attributes' => ['class' => 'vesafe-workflow-list-widget'],
    ];

    // Get the current node object.
    $node = $this->getNode();
    $node_state = $node->get('moderation_state')->getValue();

    // Get the list of states of current bundle.
    /** @var \Drupal\workflows\Entity\Workflow $workflow */
    $workflow = \Drupal::service('content_moderation.moderation_information')->getWorkflowForEntityTypeAndBundle('node', $node->bundle());
    $workflow_settings = $workflow->get('type_settings');

    if (array_key_exists('states', $workflow_settings)) {
      // Sort states by weight.
      uasort($workflow_settings['states'], [SortArray::class, 'sortByWeightElement']);

      // Set the items by state.
      $i = 0;
      foreach ($workflow_settings['states'] as $name => $state) {
        // Default attributes.
        $label = $state['label'];
        $item_class = '';

        // Set the active item.
        if ($node_state[0]['value'] == $name) {
          $item_class = ' active';
        }

        // Set the first item.
        if ($i == 0) {
          $item_class = 'first';
        }

        // Set the last item.
        if ($i == (count($workflow_settings['states']) -1)) {
          $item_class = 'last';
        }

        // Generate the item.
        $output['content'][] = [
          '#markup' => "<p class='$item_class'>$label</p>",
        ];

        $i++;
      }
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
