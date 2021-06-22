<?php

namespace Drupal\napo_content_cart\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\napo_content_cart\NccCartTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\RedirectCommand;

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
      $container->get('entity_type.manager'),
      $container->get('tempstore.private'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "ncc_download_centre_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $elements = []) {
    if (empty($elements)) {
      return [];
    }

    // Set default message.
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
        $video_fid = $video->get('field_media_video_file')->getValue();

        /** @var \Drupal\file\Entity\File $file */
        $file = $this->entityTypeManager->getStorage('file')->load($video_fid[0]['target_id']);
        if (!$file) {
          continue;
        }
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
      '#empty' => $this->t('There are not videos to download!'),
      '#attributes' => [
        'class' => ['ncc-table'],
      ]
    ];


    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Download selected (0)'),
      '#attributes' => ['class' => ['ncc-submit', 'ncc-submit-by-items', 'use-ajax-submit']]
    ];

    $form['actions']['download_all'] = [
      '#type' => 'submit',
      '#value' => $this->t('Download All'),
      '#attributes' => ['class' => ['ncc-submit', 'ncc-submit-all', 'use-ajax-submit']],
      '#submit' => [[$this, 'submitAllForm']],
    ];

    $form['actions']['#weight'] = -1;

    $form['#attached']['library'][] = 'napo_content_cart/napo_content_cart.form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitAllForm(array &$form, FormStateInterface $form_state) {
    $form_state->setResponse($this->sendToDownload());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('table');
    // Include the videos checked in the form.
    foreach ($values as $id => $value) {
      if ($value == 0) {
        unset($values[$id]);
      }
    }

    $form_state->setResponse($this->sendToDownload($values));
  }

  /**
   * {@inheritdoc}
   */
  public function sendToDownload($values = []) {
    $store = $this->tempStoreFactory->get('napo_content_cart.downloads');
    $store_ids = $store->get('video_downloads');

    if (empty($values)) {
      $values = $store_ids;
    }

    // Creat the default response.
    $response = new AjaxResponse();

    foreach ($values as $id => $node) {
      if (is_string($node)) {
        $node = $this->entityTypeManager->getStorage('node')->load($id);
      }
      // Remove each item from the table.
      $response->addCommand(new RemoveCommand('.ncc-element-id-' . $node->id()));
      unset($store_ids[$node->id()]);
    }

    // Clear temp store.
    $store->set('video_downloads', $store_ids);

    // Clear the table if the temp is empty.
    if (count($store_ids) == 0) {
      $response->addCommand(new RemoveCommand('.ncc-table'));
      $response->addCommand(new RemoveCommand('.ncc-submit'));
    }

    // Add redirect to download the file.
    $response->addCommand(new RedirectCommand(Url::fromRoute('content_cart.list', ['file' => $this->prepareZip($values)])->toString()));

    return $response;
  }

  /**
   * Prepare the zip to download.
   *
   * @param array $values
   *   The array of values.
   *
   * @return string
   *   The name of new zip.
   */
  public function prepareZip(array $values) {
    // Prepare the folder where the zip will be created.
    $this->prepareFolder();

    // Generate the name of new zip.
    $name = 'napo_download_' . $this->getNextItem();
    $new_name = $name . '.zip';

    // Generate the path.
    $destination = $this->fileSystem->realpath('public://download_centre/');

    // Create the new file.
    $zip = new \ZipArchive;
    $zip->open($destination . '/' . $new_name, \ZipArchive::CREATE);

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

    return $name;
  }

  /**
   * Prepare the folder of zips.
   */
  public function prepareFolder() {
    $directory = $this->fileSystem->realpath('public://download_centre');
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
  public function getNextItem() {
    $directory = $this->fileSystem->realpath('public://download_centre');
    return count(scandir($directory)) + 1;
  }

}
