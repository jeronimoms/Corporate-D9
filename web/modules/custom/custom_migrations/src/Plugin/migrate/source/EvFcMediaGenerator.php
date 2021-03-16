<?php

namespace Drupal\custom_migrations\Plugin\migrate\source;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Fiel collection to media from database.
 *
 * @todo Support field type collection to media.
 *
 * @MigrateSource(
 *   id = "d7_fc_media",
 *   source_module = "file"
 * )
 */
class EvFcMediaGenerator extends FieldableEntity{

  /**
   * @var array
   */
  protected $sourceFields = [];


  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
    foreach ($this->configuration['field_names'] as $name) {
      $this->sourceFields[$name] = $name;
    }
    // Do not joint source tables.
    $this->configuration['ignore_map'] = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'target_id' => [
        'type' => 'integer',
      ],
    ];
  }

  public function count($refresh = FALSE) {
    return $this->initializeIterator()->count();
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'target_id' => $this->t('The file entity ID.'),
      'file_id' => $this->t('The file entity ID.'),
      'file_path' => $this->t('The file path.'),
      'file_name' => $this->t('The file name.'),
      'file_alt' => $this->t('The file arl.'),
      'file_title' => $this->t('The file title.'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select($this->configuration['entity_type'], 'fci')
      ->fields('fci',
      [
        'item_id',
        'revision_id',
      ]);
    $query->condition('fci.field_name', $this->configuration['bundle']);
    $query->condition('fci.archived', '0');
    $query->leftJoin('field_data_' . $this->configuration['bundle'], 'fd', 'fd.' . $this->configuration['bundle'] . '_value = fci.item_id');
    $query->addField('fd', 'entity_id');
    $query->addField('fd', 'revision_id ');
    $query->leftJoin('field_data_' . $this->configuration['fc_file_name'], 'fdfcif', 'fci.item_id = fdfcif.entity_id');
    $query->where('fdfcif.' . $this->configuration['fc_file_name'] . '_fid IS NOT NULL');
    $query->addField('fdfcif', $this->configuration['fc_file_name'] . '_fid');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    $query_files = $this->select('file_managed', 'f')
      ->fields('f')
      ->condition('uri', 'temporary://%', 'NOT LIKE')
      ->orderBy('f.timestamp');

    $all_files = $query_files->execute()->fetchAllAssoc('fid');

    $files_found = [];

    $parent_iterator = parent::initializeIterator();

    foreach ($this->sourceFields as $name => $source_field) {
      foreach ($parent_iterator as $result) {
        // Support remote file urls.
        $file_url = $all_files[$result['field_col_item_files_fid']]['uri'];
        if (!empty($this->configuration['d7_file_url'])) {
          $file_url = str_replace('public://', '', $file_url);
          $file_url = str_replace('private://', '', $file_url);
          $file_path = UrlHelper::encodePath($file_url);
          $file_url = $this->configuration['d7_file_url'] . $file_path;
        }

        $files_found[] = [
          'target_id' => $result['item_id'],
          'item_id' => $result['item_id'],
          'revision_id' => $result['revision_id'],
          'nid' => $result['entity_id'],
          $this->configuration['fc_file_name'] . '_fid' => $result[$this->configuration['fc_file_name'] . '_fid'],
          'file_name' => $all_files[$result[$this->configuration['fc_file_name'] . '_fid']]['filename'],
          'file_path' => $file_url,
        ];
      }
    }

    return new \ArrayIterator($files_found);
  }

}
