<?php

namespace Drupal\napo_content_cart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\napo_content_cart\NccCartTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
class NccController extends ControllerBase implements ContainerInjectionInterface {

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
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request_stack, PrivateTempStoreFactory $privateTempStoreFactory, FormBuilder $form_builder, EntityTypeManagerInterface $entity_type_manager) {
    $this->request = $request_stack->getCurrentRequest();
    $this->privateTempStoreFactory = $privateTempStoreFactory;
    $this->formBuilder = $form_builder;
    $this->entityTypeManager = $entity_type_manager;
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
  public function add(Node $node) {
    /** @var PrivateTempStore $store */
    $store = $this->privateTempStoreFactory->get('napo_content_cart.downloads');
    $ids = $store->get('video_downloads');

    if (isset($ids)) {
      if (!array_key_exists($node->id(), $ids)) {
        // If the element is not already exists in the cart, add it.
        $ids[$node->id()] = $node;
      }
      else {
        // If the element already exists, return a warning.
        return [
          '#theme' => 'ncc_element_modal',
          '#message' => new TranslatableMarkup('Item already added'),
          '#button' => $this->getDefaultButton(),
        ];
      }
    }
    else {
      $ids[$node->id()] = $node;
    }

    // Save the new elements.
    $store->set('video_downloads', $ids);

    // Generate the modal content.
    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['ncc-dialog'],
      ],
      'content' => [
        '#theme' => 'ncc_element_modal',
        '#elements' => [],
        '#message' => new TranslatableMarkup('@count item added to the Download centre', ['@count' => count($ids)]),
        '#button' => $this->getDefaultButton(),
        '#attached' => [
          'library' => ['core/drupal.dialog.ajax'],
        ],
      ],
    ];

    // Include the elements.
    foreach ($ids as $id => $ids_node) {
      // Generate the current node media build.
      $media = $this->entityTypeManager->getStorage('media')->load($ids_node->get('field_image')->getString());
      $media_build = $this->entityTypeManager->getViewBuilder('media')->view($media);

      // Include the arrays.
      $build['content']['#elements'][] = [
        'title' => $ids_node->getTitle(),
        'image' => $media_build,
      ];
    }

    $response = new AjaxResponse();

    // Update the link counter.
    $response->addCommand(new HtmlCommand('.header-download-centre', $this->t('Download Centre(@count)', ['@count' => (count($ids))])));

    // Remplace the element button as added with remove classes.
    $response->addCommand(new ReplaceCommand('.napo-cart-add-' . $node->id(), $this->removeElement($node)));

    // Open the dialog.
    $response->addCommand(new OpenDialogCommand('#add-cart', '', $build, ['width' => 400]));

    return $response;
  }

  /**
   * Delete a element of cart.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node object to add.
   * @param integer $centre
   *   If the deletion is from the download centre.
   *
   * @return array|\Drupal\Core\Ajax\AjaxResponse
   */
  public function delete(Node $node, $centre = 0) {
    $store = $this->privateTempStoreFactory->get('napo_content_cart.downloads');
    $ids = $store->get('video_downloads');

    if (isset($ids)) {
      if (array_key_exists($node->id(), $ids)) {
        // If the elements exists, remove it.
        unset($ids[$node->id()]);
      }
    }

    // Save the new elements.
    $store->set('video_downloads', $ids);

    // Generate the modal content.
    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['ncc-dialog'],
      ],
    ];

    $build['content'] = [
      '#theme' => 'ncc_element_modal',
      '#title' => $node->getTitle(),
      '#message' => new TranslatableMarkup('1 item removed to the Download centre'),
      '#button' => $this->getDefaultButton(),
      '#attached' => [
        'library' => ['core/drupal.dialog.ajax'],
      ]
    ];


    $response = new AjaxResponse();

    // Update the link counter.
    $response->addCommand(new HtmlCommand('.header-download-centre', $this->t('Download Centre(@count)', ['@count' => (count($ids))])));

    if ($centre == 1) {
      // Remove the element from table.
      $response->addCommand(new RemoveCommand('.ncc-element-id-' . $node->id()));
      if (count($ids) == 0) {
        // If the cart is empty, remove the complete table + buttons.
        $response->addCommand(new RemoveCommand('.ncc-table'));
        $response->addCommand(new RemoveCommand('.ncc-submit'));
        $response->addCommand(new ReplaceCommand('.ncc-message', $this->getDefaultMessage()));
      }
    }
    else {
      // Remplace the element button as removed with add classes.
      $response->addCommand(new ReplaceCommand('.napo-cart-delete-' . $node->id(), $this->addElement($node)));
    }

    // Open the modal.
    $response->addCommand(new OpenDialogCommand('#remove-cart', '', $build, ['width' => 400]));

    return $response;
  }

  /**
   * Show the cart content.
   *
   * @param string $file
   *   The element to download.
   *
   * @return array|\Symfony\Component\HttpFoundation\BinaryFileResponse
   */
  public function getList($file = '') {
    $store = $this->privateTempStoreFactory->get('napo_content_cart.downloads');
    $ids = $store->get('video_downloads');

    if ($file) {
      // If the file is passed to download, generate the headers.
      $headers = [
        'Content-Type' => 'application/zip',
        'Content-Disposition' => 'attachment;filename="' . $file . '.zip"',
      ];

      // Generate the download.
      $binary = new BinaryFileResponse('public://download_centre/' . $file . '.zip', 200, $headers, TRUE);

      // Remove the zip after download.
      $binary->deleteFileAfterSend(TRUE);

      return $binary;
    }
    else {
      if (!$ids) {
        return [
          '#markup' => $this->getDefaultMessage(),
        ];
      }
    }

    return $this->formBuilder->getForm(NccDownloadCentreForm::class, $ids);
  }

  /**
   * Get the default message.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function getDefaultMessage() {
    return new TranslatableMarkup(
      '<div class="download-centre-empty">To download complete Napo films or selected scenes to your PC go to the Napo website (' .
      '<a href="/">www.napofilm.com</a>) and go to the film or the scene you want to download. You can either click on the \'Download\' button '.
      '<span class="glyphicon napo-film-video-download-form-title inline-icon"></span>, or add your video to the download centre, by clicking on the \'Add to download centre\' button ' .
      '<span class="glyphicon content-cart-add-to-cart-btn inline-icon content_cart_check_submit-processed" aria-hidden="true"></span></div>'
    );
  }

  /**
   * Get the default button.
   *
   * @return array
   */
  public function getDefaultButton() {
    return [
      '#type' => 'link',
      '#title' => $this->t('Access to Download centre'),
      '#url' => Url::fromRoute('content_cart.list',
        [],
        [
          'attributes' => [
            'class' => ['btn'],
          ],
        ],
      ),
    ];
  }

}
