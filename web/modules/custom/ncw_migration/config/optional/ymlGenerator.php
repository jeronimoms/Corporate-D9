<?php

$languages = [
  'bg',
  'ca',
  'cs',
  'da',
  'de',
  'el',
  'es',
  'et',
  'fi',
  'fr',
  'hr',
  'hu',
  'is',
  'it',
  'lt',
  'lv',
  'mt',
  'nl',
  'no',
  'pl',
  'pt',
  'ro',
  'ru',
  'sk',
  'sl',
  'sv',
  'tr',
];

foreach ($languages as $lang) {
  // Edit.
  $data = [
    'id' => "wiki_page_$lang",
    'label' => "Node: Wiki Page $lang",
    'migration_group' => "ncw_migration",
    'migration_tags' => [
      "wiki_page",
      "wiki_page_$lang"
    ],
    'destination' => [
      'default_bundle' => "wiki_page",
      'plugin' => "entity:node",
      'translations' => true,
    ],
    'migration_dependencies' => [],
    'dependencies' => [
      'enforced' => [
        'module' => [
          "ncw_migration"
        ],
      ],
    ],
    'source' => [
      'plugin' => "url",
      'data_fetcher_plugin' => "http",
      'data_parser_plugin' => "nm_json",
      'include_raw_data' => "true",
      'urls' => "https://osha.europa.eu/export/oira_wiki",
      'ids' => [
        'nid' => [
          'type' => "integer",
        ],
      ],
      'item_selector' => "items/",
      'constants' => [
        'lang' => $lang,
      ],
      'fields' => [
        0 => [
          'name' => "nid",
          'label' => "Nid",
          'selector' => "item/nid",
        ],
        1 => [
          'name' => "title",
          'label' => "Title",
          'selector' => "item/title_field/$lang",
        ],
        2 => [
          'name' => "field_summary",
          'label' => "Field Summary",
          'selector' => "item/field_summary",
        ],
        3 => [
          'name' => "body",
          'label' => "Body",
          'selector' => "item/body",
        ],
        4 => [
          'name' => "field_wiki_page_url",
          'label' => "Filed Wiki Page Url",
          'selector' => "item/field_wiki_page_url",
        ],
        5 => [
          'name' => "field_wiki_categories",
          'label' => "Field field_wiki_categories",
          'selector' => "item/field_wiki_categories",
        ],
        6 => [
          'name' => "field_updated",
          'label' => "Field Updated",
          'selector' => "item/field_updated",
        ],
        7 => [
          'name' => "status",
          'label' => "Status",
          'selector' => "item/translations/data/$lang/status",
        ],
        8 => [
          'name' => "created",
          'label' => "Created Wiki Page Url",
          'selector' => "item/translations/data/$lang/created",
        ],
        9 => [
          'name' => "changed",
          'label' => "Changed",
          'selector' => "item/translations/data/$lang/changed",
        ],
      ],
    ],
    'process' => [
      'nid' => [
        'plugin' => "migration_lookup",
        'migration' => "wiki_page",
        'source' => 'nid',
        'no_stub' => true,
      ],
      'langcode' => [
        'plugin' => "static_map",
        'bypass' => true,
        'source' => "constants/lang",
        'map' => [
          'pt' => "pt-pt",
          'no' => "nn",
        ],
      ],
      'content_translation_source' => "en",
      'title' => "title",
      'field_summary/0/value' => "field_summary/$lang/0/value",
      'field_summary/0/format' => "field_summary/$lang/0/format",
      'body/0/value' => "body/$lang/0/value",
      'body/0/format' => "body/$lang/0/format",
      'field_wiki_page_url' => [
        'plugin' => "field_link",
        'source' => "field_wiki_page_url/$lang",
      ],
      'field_wiki_categories' => [
        'plugin' => "nm_taxonomy_term",
        'source' => [
          "field_wiki_categories/name",
          "field_wiki_categories/vocabulary_machine_name"
        ],
      ],
      'field_updated' => [
        'plugin' => "sub_process",
        'source' => "field_updated/und",
        'process' => [
          'value' => [
            'plugin' => "format_date",
            'from_format' => "Y-m-d H:i:s",
            'to_format' => "Y-m-d\TH:i:s",
            'source' => "value",
          ],
        ],
      ],
      'field_migration_source' => [
        'plugin' => "default_value",
        'default_value' => "ncw",
      ],
    ],
  ];
  /////////////////////////////////////////////
  yaml_emit_file("migrate_plus.migration.wiki_page_$lang.yml", $data, YAML_ANY_ENCODING, YAML_LN_BREAK);
}
