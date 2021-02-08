<?php

namespace Drupal\custom_migrate\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * This process plugin can be used for path/alias fields.
 *
 * Https://www.drupal.org/node/2350135#comment-9476629
 * https://drupal.stackexchange.com/questions/238393/migrate-duplicate-entries-in-url-alias-after-update-migrations)
 *
 * @MigrateProcessPlugin(
 *   id = "empty_if_url_alias_exists",
 * )
 */
class EmptyIfUrlAliasExists extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform(
        $value,
        MigrateExecutableInterface $migrate_executable,
        Row $row,
        $destination_property
    ) {
    // Retrieves a \Drupal\Core\Database\Connection which is a PDO instance.
    $db = Database::getConnection();

    $sth = $db->select('path_alias', 'u')
      ->fields('u', ['id']);
    $and = $sth->andConditionGroup()
      ->condition('u.path', '/node/' . $row->getIdMap()['destid1'])
      ->condition('u.alias', $value);
    $sth->condition($and);
    $data = $sth->execute();
    $results = $data->fetch(\PDO::FETCH_NUM);

    // When no path_alias record found, return the url, so it can be added.
    if ($results === FALSE || count($results) === 0) {
      return $value;
    }
    // When an path record is already present, return ''.
    return "";
  }

}
