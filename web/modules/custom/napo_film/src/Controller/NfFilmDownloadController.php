<?php

namespace Drupal\napo_film\Controller;

use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\napo_content_cart\NccCartTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\napo_content_cart\Form\NccDownloadCentreForm;

/**
 * General class for node like controller.
 */
class NfFilmDownloadController extends ControllerBase implements ContainerInjectionInterface {

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
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FileSystem $file_system) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function filmDownload(Node $node) {
    if ($node->hasField('field_video')) {
      $default = $node->getTranslation('en');
      $video_id = $node->get('field_video')->getValue();
      if (empty($video_id)) {
        $video_id = $default->get('field_video')->getString();
      }
      if (is_array($video_id) && count($video_id) > 0 && $_GET["number"] != null) {
        $numbervideo = $_GET["number"];
        $video_id = $video_id[$numbervideo]['target_id'];
      }
      $media = $this->entityTypeManager->getStorage('media')->load($video_id);
      $media_file_id = $media->get('field_media_video_file')->getValue()['0']['target_id'];
      $file = $this->entityTypeManager->getStorage('file')->load($media_file_id);


      $headers = [
        'Content-Type' => 'text/' . $file->getMimeType(),
        'Content-Description' => 'File Download',
        'Content-Disposition' => 'attachment; filename=' . $file->getFilename(),
      ];

      $uri = $this->fileSystem->realpath($file->getFileUri());

      return new BinaryFileResponse($uri, 200, $headers, true);
    }

    return [];
  }


}
