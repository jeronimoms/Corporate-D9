<?php

namespace Drupal\napo_msds_activities\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
   * Drupal\Core\Language\LanguageManagerInterface definition.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
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

    if (!$node) {
      return [];
    }

    $video = [];
    $activity = [];
    $guidance = [];
    $lesson = [];
    $resources = [];

    if ($node->getType() == 'msds_activities') {
      // MSDS Video
      $video_ref = $node->get('field_msds_video')->getString();
      if ($video_ref) {
        $video_ref = $this->entityTypeManager->getStorage('node')->load($video_ref);
        if ($video_ref) {
          $ref = $video_ref->get('field_video')->getValue();
          if (is_array($ref) && count($ref) > 0) {
            $ref = $ref[0]['target_id'];
          }
          $video = $this->normalizeMedia($ref);
        }
      }

      // MSDS Activity.
      $activity_ref = $node->get('field_activity')->getString();
      if ($activity_ref) {
        $activity = $this->normalizeMedia($activity_ref);
      }

      // MSDS Guidance.
      $guidance = $this->t('Facilitator guidance');
    }

    if ($node->getType() == 'lesson') {
      // LESSON Video
      $video_ref = $node->get('field_lesson_video')->getString();
      if ($video_ref) {
        $video_ref = $this->entityTypeManager->getStorage('node')->load($video_ref);
        if ($video_ref) {
          $video = $this->normalizeMedia($video_ref->get('field_video')->getString());
        }
      }

      // LESSON Lesson.
      $lesson_ref = $node->get('field_file')->getString();
      if ($lesson_ref) {
        $lesson = $this->normalizeMedia($lesson_ref);
      }

      // LESSON Guidance.
      $guidance = $this->t('Teacher guidance');

      // LESSON Resources.
      $resources = $this->t('Related Resources');

    }

    return [
      '#theme' => 'nma__download',
      '#node' => $node,
      '#lang' => $this->languageManager->getCurrentLanguage()->getId(),
      '#video' => $video,
      '#activity' => $activity,
      '#lesson' => $lesson,
      '#guidance' => $guidance,
      '#resources' => $resources,
    ];
  }

  /**
   * Normalize the object for twig.
   *
   * @param $media_id
   *   The node object to add.
   *
   * @return array
   */
  public function normalizeMedia($media_id) {
    if (!empty($media_id)) {
      $media_file_id = $this->getMediaFileId($media_id);
      if ($media_file_id) {
        $file = $this->entityTypeManager->getStorage('file')->load($media_file_id);
        return [
          'fid' => $file->get('fid')->getString(),
          'size' => round($file->getSize() / 1024,2),
        ];
      }
    }
    return [];
  }

  /**
   * Get the media id's dependly the bundle.
   *
   * @param $media_id
   *   The node object to add.
   *
   * @return string|array
   */
  public function getMediaFileId($media_id) {
    $media = $this->entityTypeManager->getStorage('media')->load($media_id);
    if ($media) {
      if ($media->bundle() == 'document') {
        return $media->get('field_media_document')->getValue()['0']['target_id'];
      }
      if ($media->bundle() == 'image') {
        return $media->get('field_media_image')->getValue()['0']['target_id'];
      }
      if ($media->bundle() == 'video') {
        return $media->get('field_media_video_file')->getValue()['0']['target_id'];
      }
    }

    return [];
  }

}
