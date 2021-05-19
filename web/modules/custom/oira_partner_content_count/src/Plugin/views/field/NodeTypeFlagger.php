<?php

/**
 * @file
 * Definition of Drupal\partner_content_count\Plugin\views\field\NodeTypeFlagger
 */

namespace Drupal\oira_partner_content_count\Plugin\views\field;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("node_type_flagger")
 * handlers = {
 * "views_data" = "Drupal\views\EntityViewsData"
 * }
 */
class NodeTypeFlagger extends FieldPluginBase implements ContainerFactoryPluginInterface{

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * Define the available options
   *
   * @return array
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['node_type'] = ['default' => 'partner'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // Set the initial output.
    $build = $this->htmlElement();
    $count_pt = 0;
    $count_news = 0;
    $count_pr = 0;

    /** @var \Drupal\node\Entity\Node $node */
    $current_node = $values->_entity;

    // Check if current row ndoe is empty.
    if (!$current_node instanceof Node) {
      return $build;
    }

    $access_id = $current_node->get('field_workbench_access')->getString();
    if (!empty($access_id)) {
      // Count by field_workbench_access.
      $nodes = $this->entityTypeManager
        ->getStorage('node')
        ->loadByProperties([
          'field_workbench_access' => $access_id,
          'status' => 1,
        ]);

      // Sum the node type.
      $this->countNodes($nodes, $count_pt, $count_news, $count_pr);

      // Count by field_co_author.
      $nodes = $this->entityTypeManager
        ->getStorage('node')
        ->loadByProperties([
          'field_co_author' => $access_id,
          'status' => 1,
        ]);

      // Sum the node type.
      $this->countNodes($nodes, $count_pt, $count_news, $count_pr);

      // Count by field_third_partner.
      $nodes = $this->entityTypeManager
        ->getStorage('node')
        ->loadByProperties([
          'field_third_partner' => $access_id,
          'status' => 1,
        ]);

      // Sum the node type.
      $this->countNodes($nodes, $count_pt, $count_news, $count_pr);
    }

    // Normalize the values.
    if ($count_pt == 0) {
      $count_pt = '-';
    }

    if ($count_news == 0) {
      $count_news = '-';
    }

    if ($count_pr == 0) {
      $count_pr = '-';
    }

    // Set the new output with the data.
    $build = $this->htmlElement([
      'practical_tool' => [
        'count' => $count_pt,
        'href' => Url::fromRoute('entity.node.canonical', ['node' => $current_node->id()])->toString() . '#oira-tools',
      ],
      'news' => [
        'count' => $count_news,
        'href' => Url::fromRoute('entity.node.canonical', ['node' => $current_node->id()])->toString() . '#news',
      ],
      'promotional_resources' => [
        'count' => $count_pr,
        'href' => Url::fromRoute('entity.node.canonical', ['node' => $current_node->id()])->toString() . '#promotional-resources',
      ],
    ]);

    return $build;
  }

  /**
   * Return an array with the output.
   *
   * @param array $nodes
   *   The array of nodes.
   * @param $count_pt
   *   The counter.
   * @param $count_news
   *   The counter.
   * @param $count_pr
   *   The counter.
   */
  public function countNodes(array $nodes, &$count_pt, &$count_news, &$count_pr) {
    foreach ($nodes as $node){
      if($node->bundle() == 'practical_tool'){
        $count_pt++;
      }
      if($node->bundle() == 'news'){
        $count_news++;
      }
      if($node->bundle() == 'promotional_material'){
        $count_pr++;
      }
    }
  }

  /**
   * Return an array with the output.
   *
   * @return array
   */
  public function htmlElement($data = []) {
    if (empty($data)) {
      $data = [
        'practical_tool' => [
          'count' => '-',
          'href' => '',
        ],
        'news' => [
          'count' => '-',
          'href' => '',
        ],
        'promotional_resources' => [
          'count' => '-',
          'href' => '',
        ],
      ];
    }
    return [
      '#theme' => 'opcc_field_node_type_flagger',
      '#counters' => $data,
    ];
  }

}
