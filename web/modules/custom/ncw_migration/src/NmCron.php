<?php

namespace Drupal\ncw_migration;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;

/**
 * General class for Cron hooks.
 */
class NmCron {

  use StringTranslationTrait;

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
      $migration = \Drupal::service('plugin.manager.migration')->createInstance($migration_id);
      $migration->getIdMap()->prepareUpdate();
      $executable = new MigrateExecutable($migration, new MigrateMessage());
      $executable->import();
    }
  }

}
