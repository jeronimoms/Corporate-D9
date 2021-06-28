<?php

namespace Drupal\napo_msds_activities\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;


/**
 * Provides a 'NMA Download' Block.
 *
 * @Block(
 *   id = "nma_download_block",
 *   admin_label = @Translation("Nma Download Block"),
 * )
 */
class NmaDownloadBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The Request object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\File\FileSystem definition.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * Drupal\Core\Language\LanguageManagerInterface definition.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, FileSystem $file_system, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $file_system;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('file_system'),
      $container->get('language_manager')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->routeMatch->getParameter('node');
    if (!$node || $node->getType() !== 'msds_activities') {
      return [];
    }

    // Process video link.
    $video = [];
    $video_ref = $node->get('field_msds_video')->getString();
    if ($video_ref) {
      /** @var \Drupal\node\Entity\Node $video_ref */
      $video_ref = $this->entityTypeManager->getStorage('node')->load($video_ref);
      if ($video_ref) {
        $video_media = $this->entityTypeManager->getStorage('media')->load($video_ref->get('field_video')->getString());
        $video['fid'] = $video_media->get('field_media_video_file')->getValue()['0']['target_id'];
        $file = $this->entityTypeManager->getStorage('file')->load($video['fid']);;
        $video['size'] = round($file->getSize() / 1024 / 1024,2);
      }
    }

    // Process activity link.
    $activity = [];
    $activity_ref = $node->get('field_activity')->getString();
    if ($activity_ref) {
      /** @var \Drupal\media\Entity\Media $activity_media */
      $activity_media = $this->entityTypeManager->getStorage('media')->load($activity_ref);
      if ($activity_media->bundle() == 'document') {
        $activity['fid'] = $activity_media->get('field_media_document')->getValue()['0']['target_id'];
      }
      else {
        $activity['fid'] = $activity_media->get('field_media_image')->getValue()['0']['target_id'];
      }

      $file = $this->entityTypeManager->getStorage('file')->load($activity['fid']);;
      $activity['size'] = round($file->getSize() / 1024,2);
    }

    return [
      '#theme' => 'nma__download',
      '#node' => $node,
      '#lang' => $this->languageManager->getCurrentLanguage()->getId(),
      '#video' => $video,
      '#activity' => $activity,
    ];
  }

}
