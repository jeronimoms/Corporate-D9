<?php

namespace Drupal\content_cart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\media\Entity\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Twig\Error\RuntimeError;

/**
 * General class for node like controller.
 */
class CartController extends ControllerBase implements ContainerInjectionInterface
{

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
  public function __construct(RequestStack $request_stack, PrivateTempStoreFactory $privateTempStoreFactory)
  {
    $this->request = $request_stack->getCurrentRequest();
    $this->privateTempStoreFactory = $privateTempStoreFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('request_stack'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function downloadnode(Node $node)
  {
    $mid = $node->get('field_video')->target_id;
    $media = Media::load($mid);
    $filename = $media->get('name')->value;

    $uri_prefix = 'public://videos/napo_video/';
    $uri = $uri_prefix . $filename;
    $headers = [
      'Content-Type' => 'video/mp4',
      'Content-Description' => 'Video Download',
      'Content-Disposition' => 'attachment; filename=' . $filename
    ];

    return new BinaryFileResponse($uri, 200, $headers, true );

  }

  /**
   * {@inheritdoc}
   */
  public function addcart(Node $node)
  {
    $type = $node->getType();

    if ($type == 'napo_video') {
      /** @var PrivateTempStore $store */
      $store = $this->privateTempStoreFactory->get('content_cart.downloads');
      $ids = $store->get('video_downloads');

      if (empty($ids)) {
        $ids = [];
      }
      foreach ($ids as $id) {
        if ($id == $node->id()) {
          $contain = true;
        } else {
          $contain = false;
        }

      }
      if ($contain == false) {
        $ids[] = $node->id();
      }
      $store->set('video_downloads', $ids);
      $this->viewList($ids);

      return TRUE;

    }

  }

  /**
   * {@inheritdoc}
   */
  public function truncatecart()
  {
    /** @var PrivateTempStore $store */
    $store = $this->privateTempStoreFactory->get('content_cart.downloads');
    $store->delete('video_downloads');

  }
  public function deleteone(Node $node)
  {
//    /** @var PrivateTempStore $store */
//    $store = $this->privateTempStoreFactory->get('content_cart.downloads');
//    $store->delete('video_downloads');
    // TODO Delete ONE

  }

  public function viewList($ids) {

    //Prepare the link options to add later to the URL object.
    $link_options = [
      'attributes' => [
        'target' => '_blank',
        'class' => [
          'button'
        ],
      ],
    ];

    $rows = [];


    /**
     * @var \Drupal\media\Entity\Media $tempnode
     *
     * This foreach is to prepare the rows of the table.
     */
    foreach ($ids as $id) {

      $mid = $id->get('field_video')->target_id;;
      $media = Media::load($mid);
      $node = Node::load($id);

      $filename = $media->get('name')->value;
//      $uri_prefix = 'public://videos/napo_video/';
//      $uri = $uri_prefix . $filename;
//      $headers = [
//        'Content-Type' => 'video/mp4',
//        'Content-Description' => 'Video Download',
//        'Content-Disposition' => 'attachment; filename=' . $filename
//      ];
//      $butom_download = new BinaryFileResponse($uri, 200, $headers, true );

//      //Create the 2 URL using the routes.
//      $tempnode_download_url = $butom_download;
//      $tempnode_delete_url = Url::fromRoute('eco_tempnodes.delete', $id);
//
//      //Prepare the URL with the options for the creation of the link
//      $tempnode_download_url->setOptions($link_options);
//      $tempnode_delete_url->setOptions($link_options);
//
//      //Create the 2 links
//      $tempnode_download_link = Link::fromTextAndUrl($this->t('Download'), $tempnode_download_url);
//      $tempnode_delete_link = Link::fromTextAndUrl($this->t('Delete'), $tempnode_delete_url);

      //Prepare the row of the asset.
      $rows[] = [
        'data' => [
          // Cells.
          $filename,
//          $this->dateFormatter->format($tempnode->getCreatedTime(), 'short'),
//          $tempnode->label(),
//          $this->dateFormatter->format($tempnode->getCreatedTime(), 'short'),
//          $tempnode_download_link,
//          $tempnode_delete_link
        ],
      ];
    }
    //End Foreach

    //The headers of the table
    $header = [
      $this->t('File'),
//      $this->t('Year'),
//      $this->t('Type'),
//      $this->t('Created'),
//      $this->t('Download'),
//      $this->t('Delete'),
    ];

    $build = [
      '#theme' => 'cart_block'
    ];

    $table_array = [];
    $table_array['tempnode_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No videos available.'),
    ];

    //Added the pager.
      $table_array['pager'] = [
      '#type' => 'pager',
    ];

    $build['#table'] = $table_array;

    return $build;

  }
}
