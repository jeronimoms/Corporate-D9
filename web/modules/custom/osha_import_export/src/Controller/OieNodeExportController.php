<?php

namespace Drupal\osha_import_export\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\Element\Date;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\field\Entity\FieldConfig;
use Drupal\file_entity\Entity\FileEntity;

class OieNodeExportController extends ControllerBase implements ContainerInjectionInterface{

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  public function export(Node $node) {
    if (!isset($node) || empty($node)) {
      return new JsonResponse([]);
    }

    // Get each translation.
    $langs = [];
    foreach ($node->getTranslationLanguages() as $lang => $value) {
      $langs[$lang] = $node->getTranslation($lang);
    }

    $data = [
      'vid' => $node->get('vid')->getString(),
      'uid' => $node->get('uid')->getString(),
      'title' => $node->get('title')->getString(),
      'status' => $node->get('status')->getString(),
      'promote' => $node->get('promote')->getString(),
      'sticky' => $node->get('sticky')->getString(),
      'nid' => $node->get('nid')->getString(),
      'type' => $node->get('type')->getString(),
      'created' => $node->get('created')->getString(),
      'changed' => $node->get('changed')->getString(),
      'translate' => (int)$node->isTranslatable(),
      'uuid' => $node->get('uuid')->getString(),
      'revision_timestamp' => $node->get('revision_timestamp')->getString(),
      'revision_uid' => $node->get('revision_uid')->getString(),
    ];

    foreach ($node->getFieldDefinitions() as $field_name => $definition) {
      if (!$definition instanceof FieldConfig) {
        continue;
      }

      if (!$definition->isTranslatable()) {
        $data[$field_name][$node->language()->getId()]['value'] = $this->getFieldValue($node, $definition, $field_name);
      }
      else {
        /**
         * @var \Drupal\node\Entity\Node $node_lang
         */
        foreach ($langs as $lang => $node_lang) {
          $trans_value = $this->getFieldValue($node_lang, $definition, $field_name);
          //$data[$field_name][$lang]['value'] = $trans_value;
          if (isset($trans_value) && !empty($trans_value)) {
            $data[$field_name][$lang]['value'] = $trans_value;
          }
        }
      }
    }

    foreach ($langs as $lang => $node_lang) {
      $data['title_field'][$lang] = [
        'value' => $node_lang->getTitle(),
        'format' => "",
        'safe_value' => $node_lang->getTitle(),
      ];

      $data['translations']['original'] = 'en';
      $data['translations']['data'][$lang] = [
        'entity_type' => "node",
        'entity_id' => $node_lang->id(),
        'language' => $lang,
        'source' => "en",
        'status' => $node_lang->isPublished(),
        'translate' => 0,
        'created' => $node_lang->getRevisionCreationTime(),
        'changed' => $node_lang->getChangedTime(),
      ];
    }

    return new JsonResponse($data);
  }

  public function getFieldValue($node, FieldConfig $definition, $field_name) {
    // Ref entity process.
    if ($definition->getType() == 'entity_reference') {
      $values = $node->get($field_name)->getValue();
      if (!empty($values)) {
        $settings = $definition->getSettings();
        $referers = [];
        foreach ($values as $value) {
          if ($settings['target_type'] == 'media') {
            $referers[] = $this->getMediaValues($value['target_id'], $settings);
          }

          if ($settings['target_type'] == 'taxonomy_term') {
            $referers[] = $this->getTaxonomyValues($value['target_id'], $settings);
          }
        }

        return $referers;


        /*kint($values);
        kint($definition);
        kint($definition->getSettings());
        kint($referers);*/
      }
    }

    // Datetime process.
    if ($definition->getType() == 'datetime') {
      $value = $node->get($field_name)->getString();
      if (!empty($value)) {
        $value = strtotime($value);
        $value = \date('Y-m-d h:i:s', $value);
        return [
          'value' => $value,
          "timezone" => "Europe\/Madrid",
          "timezone_db" => "Europe\/Madrid",
          "date_type" => "datetime",
        ];
      }
    }

    return $node->get($field_name)->getString();
  }

  public function getTaxonomyValues($id, array $settings) {
    /** @var \Drupal\taxonomy\Entity\Term $term */
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($id);
    $term_data = [
      'tid' => $term->id(),
      'vid' => $term->get('vid')->getString(),
      'name' => $term->getName(),
      'description' => $term->getDescription(),
      'format' => $term->getFormat(),
      'weight' => $term->getWeight(),
      'changed' => $term->getRevisionCreationTime(),
      'uuid' => $term->uuid(),
      'language' => $term->language()->getId(),
      'vocabulary_machine_name' => $term->get('vid')->getString(),
      'description_field' => [
        'en' => [
          'value' => $term->getDescription(),
          'format' => $term->getFormat(),
        ],
      ],
    ];

    foreach ($term->getFieldDefinitions() as $field_name => $definition) {
      if (!$definition instanceof FieldConfig) {
        continue;
      }

      if (!$definition->isTranslatable()) {
        $term_value = $term->get($field_name)->getString();

        if ($field_name == 'field_excluded_from') {
          $values = str_replace(' ', '', explode(',', $term_value));
          foreach ($values as $value) {
            $term_data[$field_name][$term->language()->getId()][]['value'] = $value;
          }
          continue;
        }

        if ($field_name == 'field_tags_code') {
          $term_data[$field_name][$term->language()->getId()]['und'] = [
            'value' => $term_value,
            'safe_value' => $term_value,
          ];
          continue;
        }

        $term_data[$field_name][$term->language()->getId()]['value'] = $term_value;
      }
    }

    $term_data['translations'] = [
      'original' => "en",
      'data' => [],
    ];

    if ($term->isTranslatable()) {
      foreach ($term->getTranslationLanguages() as $lang => $value) {
        $term_lang = $term->getTranslation($lang);
        $term_data['name_field'][$lang] = [
          'value' => $term_lang->getName(),
          'format' => "",
          'safe_value' => $term_lang->getName(),
        ];

        $term_data['translations']['data'][$lang] = [
          'entity_type' => "taxonomy_term",
          'entity_id' => $term_lang->id(),
          'language' => $lang,
          'source' => "en",
          'status' => $term->isPublished(),
          'translate' => 0,
          'created' => $term_lang->getRevisionCreationTime(),
          'changed' => $term_lang->getChangedTime(),
        ];
      }
    }

    $term_data['name_original'] = $term->getName();


    return $term_data;
  }

  public function getMediaValues($id, array $settings) {
    /** @var \Drupal\media\Entity\Media $ref */
    $media = $this->entityTypeManager->getStorage('media')->load($id);

    $field_ref = NULL;
    if ($media->bundle() == 'image_and_link') {
      $field_ref = 'field_media_image_1';
    }
    else {
      $field_ref = 'field_media_image';
    }

    $media_file_values = $media->get($field_ref)->getValue()[0];

    /** @var \Drupal\file_entity\Entity\FileEntity $file */
    $file = $this->entityTypeManager->getStorage('file')->load($media_file_values['target_id']);

    $res = [];
    if (strpos($media->bundle(), 'image') > -1) {
      $res = $this->getNormalicedMediaImageValues($file);
      $res['type'] = $media->bundle();
      $res['field_file_image_alt_text'] = $media_file_values['alt'];
      $res['field_file_image_title_text'] = $media_file_values['title'];
      $res['field_file_description'] = $media->get('field_description')->getValue();
      return $res;
    }

    return $res;
  }

  public function getNormalicedMediaImageValues(FileEntity $file) {
    return [
      'fid' => $file->id(),
      'filename' => $file->getFilename(),
      'uri' => $file->getFileUri(),
      'filesize' => $file->getSize(),
      'status' => $file->get('status')->getString(),
      'timestamp' => $file->getCreatedTime(),
      'uuid' => $file->uuid(),
    ];
  }

  public function getRdfMapping() {
    return [
      'rdftype' => [
        "sioc:Item",
        "foaf:Document",
      ],
      'title' => [
        'predicates' => ["dc:title"],
      ],
      'created' => [
        'predicates' => ["dc:date", "dc:created"],
        'datatype' => "xsd:dateTime",
        "callback" => "date_iso8601",
      ],
    ];
  }

}
