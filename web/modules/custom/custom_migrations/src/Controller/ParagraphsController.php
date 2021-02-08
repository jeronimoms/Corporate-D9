<?php

namespace Drupal\custom_migrate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

/**
 * @file
 * Contains Drupal\custom_migrate\Controller\ParagraphsController.
 */
/**
 * This file is used to migrate translations of a specific content type with paragraphs(Gallery)
 */
class ParagraphsController extends ControllerBase {

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a ParagraphController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   A database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Procesa los usuarios que no se han sincronicado bien con wordpress y vuelve a intentarlo si va lo modifica bien en wordpress lo borra de la tabla de errores.
   */
  public function processParagraphsTranslation() {
    // Inicialice.
    $numMod = 0;
    $numErr = 0;
    $languageDefault = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $languages = \Drupal::languageManager()->getLanguages();

    // MIGRACION DOUBLE SLIDER ITEM.
    $dataMigrate = $this->getDataMigrate('paragraph_double_slider_item', TRUE);
    $dataOrigin = $dataMigrate[$languageDefault];
    unset($dataMigrate[$languageDefault]);

    if (is_array($dataMigrate) && count($dataMigrate) > 0) {
      $tables = ['paragraph__field_doubleslider_body', 'paragraph__field_doubleslider_image', 'paragraph__field_doubleslider_title',
        'paragraph_revision__field_doubleslider_body', 'paragraph_revision__field_doubleslider_image', 'paragraph_revision__field_doubleslider_title',
      ];

      // Cambiar todas las traducciones.
      foreach ($dataMigrate as $lang => $data) {
        foreach ($data as $id => $val) {
          try {
            // Comprobar si la traduccion existe en el idioma original (si no se elimina)
            if (array_key_exists($id, $dataOrigin)) {
              // Echo "Existe<br>";
              // UPDATES - TRANSLATIONS.
              foreach ($tables as $table) {
                if ($exist = $this->selectItem($table, $val)) {
                  $res = $this->updateItem($table, ['entity_id' => $val[0]], $dataOrigin[$id]);
                }
              }
              // Update paragraph_item_field_data.
              $val[] = $languageDefault;
              $resData = $this->selectItem('paragraphs_item_field_data', $dataOrigin[$id], ['id', 'revision_id']);
              $dataUpdate = [
                $resData['id'],
                $resData['revision_id'],
                $resData['parent_id'],
                $resData['parent_type'],
                $resData['parent_field_name'],
                0,
                $languageDefault,
              ];
              // Echo "paso 1<br>";.
              $this->updateItem('paragraphs_item_field_data',
                ['id' => $val[0]],
                $dataUpdate,
                ['id',
                  'revision_id',
                  'parent_id',
                  'parent_type',
                  'parent_field_name',
                  'default_langcode',
                  'content_translation_source',
                ]
              );
              // Echo "paso 2<br>";.
              $this->updateItem('paragraphs_item_revision_field_data',
               ['id' => $val[0]],
                $dataUpdate,
                 ['id',
                   'revision_id',
                   'parent_id',
                   'parent_type',
                   'parent_field_name',
                   'default_langcode',
                   'content_translation_source',
                 ]
              );
              // Cambiar en `paragraphs_item_field_data`  + `paragraphs_item_revision_field_data` --> `revision_translation_affected` --> 'null' cuando  langcode = 'es'
              // echo "paso 3<br>";.
              $this->updateItem('paragraphs_item_field_data', ['id' => $val[0], 'default_langcode' => $languageDefault], [$lang, 'null'], ['revision_translation_affected']);
              // Echo "paso 4<br>";.
              $this->updateItem('paragraphs_item_revision_field_data', ['id' => $val[0], 'default_langcode' => $languageDefault], [$lang, 'null'], ['revision_translation_affected']);
            }
            else {
              // Echo "No Existe<br>";
              // echo "Este paragraph se elimina porque no esta en el idioma original: ".$id."<br>";
              // Delete translation (no existe en el idioma original)
              foreach ($tables as $table) {
                $this->deleteItem($table, $val);
              }
              // Delete paragraph_item_field_data.
              $this->deleteItem('paragraphs_item_field_data', $val, ['id', 'revision_id']);
              $this->deleteItem('paragraphs_item_revision_field_data', $val, ['id', 'revision_id']);
            }

            // Delete field_doubleslider_item.
            $this->deleteItem('paragraph__field_doubleslider_item', $val, ['field_doubleslider_item_target_id', 'field_doubleslider_item_target_revision_id']);
            $this->deleteItem('paragraph_revision__field_doubleslider_item', $val, ['field_doubleslider_item_target_id', 'field_doubleslider_item_target_revision_id']);
            // Delete paragraph item.
            $this->deleteItem('paragraphs_item', $val, ['id', 'revision_id']);
            $this->deleteItem('paragraphs_item_revision', $val, ['id', 'revision_id']);
            // Delete migrate_map
            // $this->deleteItem('migrate_map_paragraph_double_slider_item', $val, array('destid1', 'destid2'));.
            $numMod++;
          }
          catch (\Exception $e) {
            echo $e->getMessage() . "<br>";
            $numErr++;
          }

        }
      }

      // Si hay algun original que no tenga su traducción y el nodo galeria tiene traduccion --> la creamos.
      foreach ($dataOrigin as $key => $data) {
        foreach ($dataMigrate as $lang => $data) {
          if (!array_key_exists($key, $data)) {
            echo "Este paragraph no esta traducido: " . $key . "<br>";
            // ************* FALTA **************
          }
        }
      }
    }

    // MIGRACION DOUBLE SLIDER.
    $dataMigrate = $this->getDataMigrate('paragraph_double_slider', TRUE);
    $dataOrigin = $dataMigrate[$languageDefault];
    unset($dataMigrate[$languageDefault]);

    if (is_array($dataMigrate) && count($dataMigrate) > 0) {
      // Cambiar todas las traducciones.
      foreach ($dataMigrate as $lang => $data) {
        foreach ($data as $id => $val) {
          // Comprobar si la traduccion existe en el idioma original (si no se elimina)
          if (array_key_exists($id, $dataOrigin)) {
            // Echo "Existe<br>";
            // Update paragraph_item_field_data.
            $this->updateItem('paragraphs_item_field_data', ['id' => $val[0]], $dataOrigin[$id], ['id', 'revision_id']);
            $this->updateItem('paragraphs_item_revision_field_data', ['id' => $val[0]], $dataOrigin[$id], ['id', 'revision_id']);

          }
          else {
            // Echo "No Existe<br>";
            // Delete paragraph_item_field_data.
            $this->deleteItem('paragraphs_item_field_data', $val, ['id', 'revision_id']);
            $this->deleteItem('paragraphs_item_revision_field_data', $val, ['id', 'revision_id']);
          }

          // Delete paragraph item.
          $this->deleteItem('paragraphs_item', $val, ['id', 'revision_id']);
          $this->deleteItem('paragraphs_item_revision', $val, ['id', 'revision_id']);
          // Delete migrate_map
          // $this->deleteItem('migrate_map_paragraph_double_slider', $val, array('destid1', 'destid2'));.
        }
      }
    }

    echo t($numMod . " Items sincronizados correctamente<br>");
    echo t($numErr . " Items con error al sincronizar");
    die("");
  }

  /**
   * Update table paragraph.
   *
   * @param string $table
   *   Table.
   * @param array $conditions
   *   Conditions.
   * @param array $data
   *   Data.
   * @param array $fields
   *   Array where we save paragraph entity id and revision id.
   *
   * @return string
   *   Results
   */
  protected function updateItem($table, $conditions, $data, $fields = ['entity_id', 'revision_id']) {
    // Fields update.
    $array_fields = [];
    foreach ($fields as $k => $field) {
      $array_fields[$field] = $data[$k];
    }

    $sql = $this->database->update($table)
      ->fields($array_fields);

    // Conditions.
    foreach ($conditions as $field => $value) {
      $sql->condition($field, $value, '=');
    }

    $res = $sql->execute();

    return $res;
  }

  /**
   * Delete item paragraph.
   *
   * @param string $table
   *   Sql Table.
   * @param array $val
   *   Value.
   * @param array $fields
   *   Array where we save paragraph entity id and revision id.
   *
   * @return object
   *   SQL Result
   */
  protected function deleteItem($table, $val, $fields = ['entity_id', 'revision_id']) {
    $sql = $this->database->delete($table)
      ->condition($fields[0], $val[0], '=')
      ->condition($fields[1], $val[1], '=');

    $res = $sql->execute();

    return $res;
  }

  /**
   * Exist item.
   *
   * @param string $table
   *   Table of database.
   * @param string $val
   *   Value.
   * @param array $fields
   *   Array where we save paragraph entity id and revision id.
   */
  protected function selectItem($table, $val, $fields = ['entity_id', 'revision_id']) {
    $sql = $this->database->select($table, 'n')
      ->fields('n');
    foreach ($fields as $k => $field) {
      $sql->condition($field, $val[$k], '=');
    }

    $res = $sql->execute()->fetchAssoc();
    if (is_array($res) && count($res) > 0) {
    }
    else {
      $res = FALSE;
    }

    return $res;
  }

  /**
   * Function to insert item.
   *
   * @param string $table
   *   SQL table.
   * @param string $val
   *   Value.
   *
   * @return object
   *   SQL Results
   */
  protected function insertItem($table, $val) {
    $sql = $this->database->insert($table);
    $res = $sql->execute();

    return $res;
  }

  /**
   * Find Caption by nid in D7.
   *
   * @param object $migrate
   *   Parameter migrate.
   * @param bool $translatable
   *   Flag to determine if is translatable.
   *
   * @return array
   *   Data migrated
   */
  protected function getDataMigrate($migrate, $translatable = FALSE) {
    $data = [];
    $query = $this->database->select('migrate_map_' . $migrate, 'n');
    $query->fields('n');
    $result = $query->execute()->fetchAll();
    foreach ($result as $r) {
      if ($translatable) {
        $data[$r->sourceid3][$r->sourceid2] = [$r->destid1, $r->destid2];
      }
      else {
        $data[$r->sourceid3] = [$r->destid1, $r->destid2];
      }
    }

    return $data;
  }

}
