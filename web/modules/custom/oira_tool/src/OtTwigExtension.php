<?php

namespace Drupal\oira_tool;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Twig;
use Twig\Extension\AbstractExtension;
use Drupal\views\Views;

/**
 * Extend twig with additional functions used in layoutcomponents.
 */
class OtTwigExtension extends AbstractExtension {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get additional functions in twig.
   */
  public function getFunctions() {
    return [
      new Twig\TwigFunction('getViewResults', [$this, 'getViewResults']),
    ];
  }

  /**
   * Get media image url according to the selected language in page.
   */
  public function getTaxonomyTerms($vid) {
    return $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => $vid]);
  }

  /**
   * Get media image url according to the selected language in page.
   */
  public function getViewResults($view_id, $view_display, $vid, $node_id) {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => $vid]);

    $res = [];
    foreach ($terms as $id => $term) {
      /** @var \Drupal\views\Entity\View $view */
      $view = $this->entityTypeManager->getStorage('view')->load($view_id);
      $view = Views::executableFactory()->get($view);
      $view->setDisplay($view_display);
      $view->setArguments([$node_id, $node_id, $node_id, $term->id()]);
      $view->execute();
      if (count($view->result) > 0) {
        $res[$id] = [
          'id' => $term->id(),
          'name' => $term->getName(),
          'href' => preg_replace("/[\/\s+&,%#()\$]/", '-', strtolower($term->getName())),
          'results' => $view->result,
        ];
      }
    }

    return $res;
  }

}
