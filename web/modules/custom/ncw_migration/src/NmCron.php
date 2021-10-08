<?php

namespace Drupal\ncw_migration;

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
class NmCron implements ContainerInjectionInterface{

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
      'highlight',
      'new',
      'wiki_page',
      'wiki_page_bg',
      'wiki_page_ca',
      'wiki_page_cs',
      'wiki_page_da',
      'wiki_page_de',
      'wiki_page_el',
      'wiki_page_es',
      'wiki_page_et',
      'wiki_page_fi',
      'wiki_page_fr',
      'wiki_page_hr',
      'wiki_page_hu',
      'wiki_page_is',
      'wiki_page_it',
      'wiki_page_lt',
      'wiki_page_lv',
      'wiki_page_mt',
      'wiki_page_nl',
      'wiki_page_no',
      'wiki_page_pl',
      'wiki_page_pt',
      'wiki_page_ro',
      'wiki_page_ru',
      'wiki_page_sk',
      'wiki_page_sl',
      'wiki_page_sv',
      'wiki_page_tr',
    ];

    // Start every migration.
    foreach ($migrations as $migration_id) {
      /** @var \Drupal\migrate\Plugin\Migration  $migration */
      $migration = $this->migrationManager->createInstance($migration_id);
      $migration->getIdMap()->prepareUpdate();
      $executable = new MigrateExecutable($migration, new MigrateMessage());
      $executable->import();

      // Check the nodes that thay have been deleted from source.
      $dels = $migration->getIdMap()->getRowsNeedingUpdate(1000);
      foreach ($dels as $key => $del) {
        $del = (array) $del;
        // Remove it from migration table.
        $migration->getIdMap()->deleteDestination(['nid' => $del['destid1']]);
        // Remove the node.
        $node = $this->entityTypeManager->getStorage('node')->load($del['destid1']);
        if (isset($node)) {
          $node->delete();
        }
      }
    }
  }

}
