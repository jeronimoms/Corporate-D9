<?php

namespace Drupal\napo_film;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Twig;
use Twig\Extension\AbstractExtension;
use Drupal\views\Views;
use Drupal\node\Entity\Node;

/**
 * Extend twig with additional functions used in layoutcomponents.
 */
class NfTwigExtension extends AbstractExtension {

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
      new Twig\TwigFunction('getParent', [$this, 'getParent']),
    ];
  }

  /**
   * Get media image url according to the selected language in page.
   */
  public function getParent($node_id) {

    /** @var \Drupal\node\Entity\Node $current_node */
    $current_node = $this->entityTypeManager->getStorage('node')->load($node_id);
    if (isset($current_node)) {
      $parent_node = $this->entityTypeManager->getStorage('node')->load($current_node->get('field_napo_film')->getString());
      if (isset($parent_node)) {
        return $parent_node->id();
      }
    }

    return NULL;
  }

}
