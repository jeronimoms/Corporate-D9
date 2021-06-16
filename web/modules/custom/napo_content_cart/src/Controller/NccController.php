<?php

namespace Drupal\napo_content_cart\Controller;

use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\media\Entity\Media;
use Drupal\napo_content_cart\NccCartTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Twig\Error\RuntimeError;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\CloseDialogCommand;

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
   * @var PrivateTempStoreFactory
   */
  private $privateTempStoreFactory;
  private $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request_stack, PrivateTempStoreFactory $privateTempStoreFactory) {
    $this->request = $request_stack->getCurrentRequest();
    $this->privateTempStoreFactory = $privateTempStoreFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function addcart(Node $node) {
    /** @var PrivateTempStore $store */
    $store = $this->getContentCartStore();
    $ids = $store->get('video_downloads');

    if (isset($ids)) {
      if (!array_key_exists($node->id(), $ids)) {
        $ids[$node->id()] = $node;
      }
      else {
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

    $store->set('video_downloads', $ids);

    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['my_top_message'],
      ],
    ];

    $build['content'] = [
      '#theme' => 'ncc_element_modal',
      '#title' => $node->getTitle(),
      '#message' => new TranslatableMarkup('1 item added to the Download centre'),
      '#button' => $this->getDefaultButton(),
      '#attached' => [
        'library' => ['core/drupal.dialog.ajax'],
      ]
    ];



    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('.header-download-centre', $this->t('Download Centre(@count)', ['@count' => (count($ids))])));
    $response->addCommand(new ReplaceCommand('.napo-cart-add-' . $node->id(), $this->removeElement($node)));
    $response->addCommand(new OpenDialogCommand('#add-cart', '', $build, ['width' => 400]));

    return $response;

  }

  public function deleteone(Node $node, $centre = 0) {
    /** @var PrivateTempStore $store */
    $store = $this->getContentCartStore();
    $ids = $store->get('video_downloads');

    if (isset($ids)) {
      if (array_key_exists($node->id(), $ids)) {
        unset($ids[$node->id()]);
      }
    }

    $store->set('video_downloads', $ids);

    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['my_top_message'],
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
    $response->addCommand(new HtmlCommand('.header-download-centre', $this->t('Download Centre(@count)', ['@count' => (count($ids))])));
    if ($centre == 1) {
      $response->addCommand(new RemoveCommand('.ncc-element-id-' . $node->id()));
      if (count($ids) == 0) {
        $response->addCommand(new RemoveCommand('.ncc-table'));
        $response->addCommand(new RemoveCommand('.ncc-submit-all'));
        $response->addCommand(new ReplaceCommand('.ncc-message', $this->getDefaultMessage()));
      }
    }
    else {
      $response->addCommand(new ReplaceCommand('.napo-cart-delete-' . $node->id(), $this->addElement($node)));
    }
    $response->addCommand(new OpenDialogCommand('#remove-cart', '', $build, ['width' => 400]));

    return $response;
  }

  public function viewList() {
    /** @var PrivateTempStore $store */
    $store = $this->getContentCartStore();
    $ids = $store->get('video_downloads');
    if (!$ids) {
      return [
        '#markup' => $this->getDefaultMessage(),
      ];
    }

    $form = \Drupal::formBuilder()->getForm(\Drupal\napo_content_cart\Form\NccDownloadCentreForm::class, $ids);


    return $form;

  }

  public function getContentCartStore() {
    return $this->privateTempStoreFactory->get('napo_content_cart.downloads');
  }

  public function getDefaultMessage() {
    return new TranslatableMarkup(
      'To download complete Napo films or selected scenes to your PC go to the Napo website (' .
      '<a href="/">www.napofilm.com</a>) and go to the film or the scene you want to download. You can either click on the \'Download\' button '.
      '<span class="glyphicon napo-film-video-download-form-title inline-icon"></span>, or add your video to the download centre, by clicking on the \'Add to download centre\' button ' .
      '<span class="glyphicon content-cart-add-to-cart-btn inline-icon content_cart_check_submit-processed" aria-hidden="true"></span>'
    );
  }

  public function getDefaultButton() {
    return [
      '#type' => 'link',
      '#title' => $this->t('Access to Download centre'),
      '#url' => Url::fromRoute('content_cart.viewList',
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
