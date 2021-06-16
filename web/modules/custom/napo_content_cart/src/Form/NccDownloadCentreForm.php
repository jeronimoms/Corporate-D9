<?php

namespace Drupal\napo_content_cart\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\napo_content_cart\NccCartTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\webform\Entity\WebformSubmission;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PrivateTempStoreFactory $privateTempStoreFactory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->tempStoreFactory = $privateTempStoreFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('tempstore.private')
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


    // Load the offers by user.
    /**
     * @var  $id
     * @var \Drupal\node\Entity\Node $element
     */
    foreach ($elements as $id => $element) {
      /** @var \Drupal\media\Entity\Media  $media */
      $media = $this->entityTypeManager->getStorage('media')->load($element->get('field_image')->getString());
      $media_build = $build = \Drupal::entityTypeManager()->getViewBuilder('media')->view($media);

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
        ];

        ksm($this->removeElement($element));
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
      '#value' => $this->t('Enviar'),
      '#attributes' => ['class' => ['ncc-submit-all']]
    ];

    ksm($form);


    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get default data.
    $data = $form_state->getValues()['table'];

    // Generate the offer links.
    $ofertas = "";
    $company_id = NULL;
    foreach ($data as $item) {
      if ($item > 0) {
        $offer = $this->entityTypeManager->getStorage('node')->load($item);
        $company_id = $offer->get('field_on_name_of')->getString();
        $ofertas .= "<li><a href='" . Url::fromRoute('entity.node.canonical', ['node' => $offer->id()])->toString() . "'>" . $offer->getTitle() . "</a></li>\r\r";
      }
    }

    $company = $this->entityTypeManager->getStorage('node')->load($company_id);

    // Create the body of message.
    $body = "
    Estimado " . $form_state->getValue('developer_name') . ",\r\r

    La empresa " . $company->getTitle() . " ha revisado su currículum y desea enviarle las siguientes ofertas de empleo que podrían interesarle:\r\r
    <ul>" . $ofertas . "</ul>\r\r

    Si desea ver mas ofertas acuda a https://auladrupal.com/empleo\r\r

    Atentamente,\r\r
    El equipo de AaulaDrupal.
    ";

    /** @var \Drupal\webform\WebformSubmissionInterface $webform_submission */
    $submission = WebformSubmission::create([
      'webform_id' => 'contacta_con_desarrollador',
      'uri' => '/desarrolladores/david-biescas',
      'data' => [
        'asunto' => $this->t('Una empresa quiere contactar contigo!'),
        'consulta_oferta' => \Drupal\Core\Mail\MailFormatHelper::htmlToText($body),
        'correo_electronico_submit' => $form_state->getValue('developer_email'),
      ],
    ]);

    // Create the new submission.
    $submission->save();

    \Drupal::messenger()->addMessage($this->t('Hemos enviado las ofertas seleccionadas al desarrollador'));

    $response = new RedirectResponse(Url::fromRoute('entity.node.canonical', ['node' => $form_state->getValue('node_id')])->toString());
    $response->send();
  }
}
