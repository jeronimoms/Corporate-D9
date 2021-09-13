<?php

namespace Drupal\oshwiki_import;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\migrate_drupal\MigrationPluginManager;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * General class for Cron hooks.
 */
class OwiCron implements ContainerInjectionInterface{

  use StringTranslationTrait;

  /**
   * The migration plugin manager.
   *
   * @var \Drupal\migrate_drupal\MigrationPluginManager
   */
  protected $migrationManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Implements hook_cron().
   */
  public function __construct(MigrationPluginManager $migration_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->migrationManager = $migration_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.migration'),
      $container->get('entity_type.manager')
    );
  }


  /**
   * Implements hook_cron().
   */
  public function cron() {
    $migrations = [
      'import_oshwiki_data',
    ];

    // Start every migration.
    foreach ($migrations as $migration_id) {
      /** @var \Drupal\migrate\Plugin\Migration  $migration */
      $migration = $this->migrationManager->createInstance($migration_id);
      $migration->getIdMap()->prepareUpdate();
      $executable = new MigrateExecutable($migration, new MigrateMessage());
      $executable->import();
    }

    // Check the nodes that thay have been deleted from source.
    $dels = $migration->getIdMap()->getRowsNeedingUpdate(1000);
    foreach ($dels as $key => $del) {
      $del = (array) $del;
      // Remove it from migration table.
      $migration->getIdMap()->deleteDestination(['nid' => $del['destid1']]);
      // Remove the node.
      $node = $this->entityTypeManager->getStorage('node')->load($del['destid1']);
      $node->delete();
    }
  }

}
