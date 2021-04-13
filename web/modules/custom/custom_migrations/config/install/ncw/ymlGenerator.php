<?php

$languages = [
  'es',
];

foreach ($languages as $lang) {
  // Edit.
  $data = [
    'id' => "wiki_page_$lang",
    'label' => "Node: Wiki Page $lang",
    'migration_group' => "ncw_migration",
    'migration_tags' => [
      'json example'
    ],
    'destination' => [
      'default_bundle' => "wiki_page",
      'plugin' => "entity:node",
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
      'keys' => [
        "wiki_page",
        "wiki_page_default"
      ],
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
          'selector' => "item/title",
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
      ],
    ],
    'process' => [
      'langcode' => "constants/lang",
      'title' => "title",
      'field_summary/0/value' => "field_summary/$lang/0/value",
      'field_summary/0/format' => "field_summary/$lang/0/format",
      'body/0/value' => "body/$lang/0/value",
      'body/0/format' => "body/$lang/0/format",
      'field_wiki_page_url' => [
        'plugin' => "field_link",
        'source' => "field_wiki_page_url/$lang",
      ],
    ],
  ];
  /////////////////////////////////////////////
  yaml_emit_file("migrate_plus.migration.wiki_page_$lang.yml", $data, YAML_ANY_ENCODING, YAML_LN_BREAK);
}
