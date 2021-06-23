<?php

namespace Drupal\oira_masquerade\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\masquerade\Masquerade;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Oira Masquerade' block.
 *
 * @Block(
 *   id = "oira_masquerade",
 *   admin_label = @Translation("Oira Masquerade"),
 *   category = @Translation("Forms"),
 * )
 */
class OmBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use DependencySerializationTrait;

  /**
   * The masquerade service.
   *
   * @var \Drupal\masquerade\Masquerade
   */
  protected $masquerade;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Masquerade $masquerade) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->masquerade = $masquerade;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('masquerade')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous() && !$this->masquerade->isMasquerading()) {
      // Do not allow masquerade as anonymous user, use private browsing.
      return AccessResult::forbidden();
    }

    if ($this->masquerade->isMasquerading()) {
      // Allow if the user is masquerade.
      return AccessResult::allowed();
    }

    // Display block for all users that has any of masquerade permissions.
    return AccessResult::allowedIfHasPermissions($account, $this->masquerade->getPermissions());
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['session.is_masquerading']);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($this->masquerade->isMasquerading()) {

      // If the user is already masquerading, then show the button to come back.
      $link_url = Url::fromRoute('oira_masquerade.back');

      // Generate the build to show in the block.
      $build = [
        '#type' => 'markup',
        '#markup' => Link::fromTextAndUrl($this->t('Switch back to your user'), $link_url)->toString(),
        '#attached' => ['library' => ['core/drupal.dialog.ajax']],
      ];

    }
    else {

      // If the user is not masqueraded, then show the button to masquerading.
      $link_url = Url::fromRoute('oira_masquerade.modal');

      //Provide the options to open as modal.
      $link_url->setOptions([
        'attributes' => [
          'class' => ['use-ajax', 'btn', 'btn-default'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode(['width' => 500]),
        ]
      ]);

      // Generate the build to show in the block.
      $build = [
        '#type' => 'markup',
        '#markup' => Link::fromTextAndUrl($this->t('Act as partner'), $link_url)->toString(),
        '#attached' => ['library' => ['core/drupal.dialog.ajax']],
      ];

    }
    return $build;
  }

}
