<?php

namespace Drupal\napo_content_cart\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\napo_content_cart\NccCartTrait;
use Drupal\Tests\search\Kernel\SearchMatchTest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileSystem;

class NccDownloadCentreForm extends FormBase {

  use NccCartTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Drupal\Core\File\FileSystem definition.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PrivateTempStoreFactory $privateTempStoreFactory, FileSystem $file_system) {
    $this->entityTypeManager = $entity_type_manager;
    $this->tempStoreFactory = $privateTempStoreFactory;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('tempstore.private'),
      $container->get('file_system')
    );
  }

  public function getFormId() {
    return "ncc_download_centre_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state, $elements = []) {
    if (empty($elements)) {
      return [];
    }

    $form['message'] = [
      '#markup' => new TranslatableMarkup('<p class="ncc-message">From this download centre, you can either download all the videos you have selected in one operation, or you can select from the list, and click on "download selected" to download only the chosen ones.
          You can, as well, remove selected videos from the download centre list, by clicking on the "X" button, and select the format of the video, by clicking on the "arrow" button and selecting the format.</p>')
    ];

    // Create the headers.
    $header = [
      'element' => $this->t('Select All'),
    ];


    // Load the videos by user.
    foreach ($elements as $id => $element) {
      /** @var \Drupal\media\Entity\Media  $media */
      $media = $this->entityTypeManager->getStorage('media')->load($element->get('field_image')->getString());
      $media_build = $this->entityTypeManager->getViewBuilder('media')->view($media);

      /** @var \Drupal\media\Entity\Media  $video */
      $video = $this->entityTypeManager->getStorage('media')->load($element->get('field_video')->getString());

      $file = [];
      if ($video) {
        $video_fid = $video->get('field_media_video_file')->getString();

        /** @var \Drupal\file\Entity\File $file */
        $file = $this->entityTypeManager->getStorage('file')->load($video_fid);
        $file_mime = $file->getMimeType();
        $file_mime = str_replace('video/', '', $file_mime);
        $file = [
          'filemime' => $file_mime,
          'uri' => file_create_url($file->getFileUri()),
        ];
      }

      $offers_options[$id] = [
        'element' => [
          'data' => [
            '#theme' => 'ncc_element_download',
            '#image' => $media_build,
            '#title' => $element->getTitle(),
            '#url' => Url::fromRoute('entity.node.canonical', ['node' => $id])->toString(),
            '#description' => $element->get('body')->getString(),
            '#file' => $file,
            '#remove' => $this->removeElement($element, 1)
          ],
        ],
        '#attributes' => ['class' => ['ncc-element-id-' . $id]]
      ];
    }

    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $offers_options,
      '#empty' => $this->t('There are not elements to download!'),
      '#attributes' => [
        'class' => ['ncc-table'],
      ]
    ];


    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Download selected (0)'),
      '#attributes' => ['class' => ['ncc-submit-by-items']]
    ];

    $form['actions']['download_all'] = [
      '#type' => 'submit',
      '#value' => $this->t('Download All'),
      '#attributes' => ['class' => ['ncc-submit-all']],
      '#submit' => [[$this, 'submitAllForm']],
    ];

    $form['actions']['#weight'] = -1;

    $form['#attached']['library'][] = 'napo_content_cart/napo_content_cart.form';

    return $form;
  }

  public function submitAllForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('table');
    $this->downloadZip($values);
  }


  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('table');
    // Include the videos checked in the form.
    foreach ($values as $id => $value) {
      if ($value == 0) {
        unset($values[$id]);
      }
    }

    $this->downloadZip($values);
  }

  public function downloadZip($values) {
    // Prepare the folder where the zip will be created.
    $this->prepareFolder();

    // Generate the name of new zip.
    $new_name = 'napo_download_' . $this->getNextItem() . '.zip';

    // Generate the path.
    $destination = $this->fileSystem->realpath('public://download_centre/');

    // Create the new file.
    $zip = new \ZipArchive;
    $zip->open($destination . '/' . $new_name, \ZipArchive::CREATE);

    $files = [];

    // Include the videos checked in the form.
    foreach ($values as $id => $value) {
      $node = $this->entityTypeManager->getStorage('node')->load($id);
      $media = $this->entityTypeManager->getStorage('media')->load($node->get('field_video')->getString());
      $video_fid = $media->get('field_media_video_file')->getValue()['0']['target_id'];
      $file = $this->entityTypeManager->getStorage('file')->load($video_fid);
      $file_uri = $this->fileSystem->realpath($file->getFileUri());
      if ($file_uri) {
        $zip->addFile($file_uri, $file->getFilename());
      }
    }


    // Close the file.
    $zip->close();

    // Download the file.
    header('Content-disposition: attachment; filename=' . $new_name);
    header('Content-type: application/zip');
    readfile($destination . '/' . $new_name);
  }

  public function prepareFolder() {
    $directory = $this->fileSystem->realpath('public://download_centre');
    if (!is_dir($directory)) {
      mkdir($directory, 0777);
    }
  }

  public function getNextItem() {
    $directory = $this->fileSystem->realpath('public://download_centre');
    return count(scandir($directory)) + 1;
  }
}
