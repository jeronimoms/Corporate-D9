<?php

namespace Drupal\napo_msds_activities\Controller;

use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
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
use function Psy\bin;

/**
 * General class for node like controller.
 */
class NmaDownloadController extends ControllerBase implements ContainerInjectionInterface {

  use NccCartTrait;

  /**
   * The Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The private tem store object.
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $privateTempStoreFactory;

  /**
   * The form builder manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $formBuilder;

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
  public function __construct(RequestStack $request_stack, PrivateTempStoreFactory $privateTempStoreFactory, FormBuilder $form_builder, EntityTypeManagerInterface $entity_type_manager, FileSystem $file_system) {
    $this->request = $request_stack->getCurrentRequest();
    $this->privateTempStoreFactory = $privateTempStoreFactory;
    $this->formBuilder = $form_builder;
    $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('tempstore.private'),
      $container->get('form_builder'),
      $container->get('entity_type.manager'),
      $container->get('file_system')
    );
  }

  /**
   * Add a new element to the cart.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node object to add.
   *
   * @return array|\Drupal\Core\Ajax\AjaxResponse
   */
  public function downloadFile($file) {
    $headers = [
      'Content-Type' => 'text/' . $file->getMimeType(),
      'Content-Description' => 'File Download',
      'Content-Disposition' => 'attachment; filename=' . $file->getFilename(),
    ];

    $uri = $this->fileSystem->realpath($file->getFileUri());

    return new BinaryFileResponse($uri, 200, $headers, true);
  }

  public function downloadZip($name) {
    // If the file is passed to download, generate the headers.
    $headers = [
      'Content-Type' => 'application/zip',
      'Content-Disposition' => 'attachment;filename="' . $name . '.zip"',
    ];

    // Generate the download.
    $binary = new BinaryFileResponse('public://msds_download/' . $name . '.zip', 200, $headers, TRUE);

    // Remove the zip after download.
    $binary->deleteFileAfterSend(TRUE);

    return $binary;
  }

  public function downloadGuidance(Node $node) {
    $this->prepareFolder('msds_download');

    // Generate the path.
    $destination = $this->fileSystem->realpath('public://msds_download/');

    if ($node->getType() == 'msds_activities') {
      // Generate the name of new zip.
      $name = 'guidance_' . $this->getNextItem('msds_download');
      $new_name = $name . '.zip';

      // Create the new file.
      $zip = new \ZipArchive;
      $zip->open($destination . '/' . $new_name, \ZipArchive::CREATE);

      $medias = $this->getGuidanceFiles($node);

      foreach ($medias as $media_id) {
        /** @var \Drupal\media\Entity\Media $media */
        $media = $this->entityTypeManager->getStorage('media')->load($media_id);
        $video_fid = $media->get('field_media_document')->getValue()['0']['target_id'];
        $file = $this->entityTypeManager->getStorage('file')->load($video_fid);
        $file_uri = $this->fileSystem->realpath($file->getFileUri());
        if ($file_uri) {
          $zip->addFile($file_uri, $file->getFilename());
        }
      }

      $zip->close();

      return $this->downloadZip($name);
    }
  }

  public function downloadAll(Node $node) {
    $this->prepareFolder('msds_download_all');

    // Generate the path.
    $destination = $this->fileSystem->realpath('public://msds_download_all/');

    if ($node->getType() == 'msds_activities') {
      // Generate the name of new zip.
      $name = 'guidance_' . $this->getNextItem('msds_download_all');
      $new_name = $name . '.zip';

      // Create the new file.
      $zip = new \ZipArchive;
      $zip->open($destination . '/' . $new_name, \ZipArchive::CREATE);

      $files = $this->getGuidanceFiles($node);
      $files[] = $this->getVideoFile($node);
      $files[] = $this->getActivityFile($node);

      foreach ($files as $item) {
        $file = $this->entityTypeManager->getStorage('file')->load($item);
        $file_uri = $this->fileSystem->realpath($file->getFileUri());
        if ($file_uri) {
          $zip->addFile($file_uri, $file->getFilename());
        }
      }

      $zip->close();

      return $this->downloadZip($name);
    }
  }

  public function getVideoFile(Node $node) {
    $video_ref = $node->get('field_msds_video')->getString();
    $video_ref = $this->entityTypeManager->getStorage('node')->load($video_ref);
    $media = $this->entityTypeManager->getStorage('media')->load($video_ref->get('field_video')->getString());
    return $media->get('field_media_video_file')->getValue()['0']['target_id'];
  }

  public function getActivityFile(Node $node) {
    $activity_ref = $node->get('field_activity')->getString();
    /** @var \Drupal\media\Entity\Media $activity_media */
    $activity_media = $this->entityTypeManager->getStorage('media')->load($activity_ref);
    if ($activity_media->bundle() == 'document') {
      $media = $activity_media->get('field_media_document')->getValue()['0']['target_id'];
    }
    else {
      $media = $activity_media->get('field_media_image')->getValue()['0']['target_id'];
    }

    return $media;
  }

  public function getGuidanceFiles(Node $node) {
    $medias = [];
    $node_ref = $this->entityTypeManager->getStorage('node')->load(408);
    $medias[] = $node_ref->get('field_guidance_file')->getString();
    $medias[] = $node_ref->get('field_glossary')->getString();
    $medias[] = $node_ref->get('field_list_of_activities')->getString();

    return $medias;
  }

  public function getAllFiles(Node $node) {
    $medias = $this->getGuidanceFiles($node);

  }

  /**
   * Prepare the folder of zips.
   */
  public function prepareFolder($name) {
    $directory = $this->fileSystem->realpath('public://' . $name);
    if (!is_dir($directory)) {
      mkdir($directory, 0777);
    }
  }

  /**
   * Get the next zip id.
   *
   * @return string
   *   The id of new zip.
   */
  public function getNextItem($name) {
    $directory = $this->fileSystem->realpath('public://' . $name);
    return count(scandir($directory)) + 1;
  }

}