<?php

namespace Drupal\oira_workflow;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\oira_workflow\OwHelper;
use Drupal\node\Entity\Node;

class OwCron implements ContainerInjectionInterface {

  /**
   * The Ow helper object.
   *
   * @var \Drupal\oira_workflow\OwHelper
   */
  protected $helper;

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(OwHelper $helper, ConfigFactoryInterface $config_factory) {
    $this->helper = $helper;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('oira_workflow.helper'),
      $container->get('config.factory')
    );
  }

  /**
   * Implements hook_cron().
   */
  public function cron() {
    // Get default Ow config.
    $ow_config = $this->configFactory->getEditable('oira_workflow.config');
    $ow_minutes = $ow_config->get('moderation_minutes');

    // Get complete workflow entity.
    $workflow = $this->helper->getWorkFlowList('oira');
    $workflow_settings = $workflow->get('type_settings');

    // Define current date.
    $timestamp = new \DateTime();
    $timestamp = $timestamp->getTimestamp();

    // Check if any user has the Oira supervisor role.
    $advisors = $this->helper->getUsersByRol('oira_supervisor');

    // Define the next state.
    $workflow_next_state = 'needs_review';

    foreach ($workflow_settings['entity_types'] as $entity_type => $types) {
      foreach ($types as $type) {
        // Get the entities by type and moderation state.
        $entities = $this->helper->getEntities([
          'entity_type' => $entity_type,
          'type' => $type,
          'moderation_state' => 'final_draft'
        ]);

        foreach ($entities as $id => $entity) {
          if (!$entity instanceof \Drupal\node\Entity\Node) {
            continue;
          }

          // The creatyion time of entity.
          $created_time = $entity->get('created')->getString();

          // Get the difference of minutes betwen entity creation and current date.
          $created_time_difference = (($timestamp - $created_time) / 60);

          if ($created_time_difference >= $ow_minutes) {
            // If no user has the role, change the next state.
            if (!$advisors) {
              $workflow_next_state = 'to_be_approved';
            }
            // Save the entity with the next state.
            $entity->set('moderation_state', $workflow_next_state);
            $entity->save();
          }
        }
      }
    }
  }
}
