<?php

namespace Drupal\translation_workflow\Entity\ViewsData;

use Drupal\views\EntityViewsData;

/**
 *
 */
class MultipleTargetLanguageJobViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['tmgmt_job_multiple_target']['progress'] = [
      'title' => 'Progress',
      'help' => 'Displays the progress of a job.',
      'real field' => 'tjid',
      'field' => [
        'id' => 'tmgmt_progress',
      ],
    ];
    $data['tmgmt_job_multiple_target']['word_count'] = [
      'title' => 'Word count',
      'help' => 'Displays the word count of a job.',
      'real field' => 'tjid',
      'field' => [
        'id' => 'tmgmt_wordcount',
      ],
      'sort' => [],
    ];
    $data['tmgmt_job_multiple_target']['tags_count'] = [
      'title' => 'Tags count',
      'help' => 'Displays the HTML tags count of a job.',
      'real field' => 'tjid',
      'field' => [
        'id' => 'tmgmt_tagscount',
      ],
    ];
    $data['tmgmt_job_multiple_target']['label'] = [
      'title' => 'Label',
      'help' => 'Displays a label of the job item.',
      'real field' => 'tjid',
      'field' => [
        'id' => 'tmgmt_entity_label',
      ],
      'sort' => [],
    ];
    $data['tmgmt_job_multiple_target']['translator']['field']['id'] = 'tmgmt_translator';
    $data['tmgmt_job_multiple_target']['translator']['field']['options callback'] = 'tmgmt_translator_labels';
    $data['tmgmt_job_multiple_target']['translator']['filter']['id'] = 'in_operator';
    $data['tmgmt_job_multiple_target']['translator']['filter']['options callback'] = 'tmgmt_translator_labels';

    $data['tmgmt_job_multiple_target']['job_type'] = [
      'title' => 'Job Type (Custom)',
      'help' => 'Displays the job type filter.',
      'field' => [
        'id' => 'tmgmt_job_type',
      ],
      'filter' => [
        'id' => 'tmgmt_job_type_filter',
      ],
    ];

    $data['tmgmt_job_multiple_target']['state'] = [
      'title' => 'States',
      'help' => 'Displays the state of the job.',
      'field' => [
        'id' => 'tmgmt_job_state',
      ],
      'filter' => [
        'id' => 'tmgmt_job_state_filter',
      ],
    ];
    $data['tmgmt_job_multiple_target']['footer'] = [
      'title' => t('Job Overview legend'),
      'help' => t('Add job state legends'),
      'area' => [
        'id' => 'tmgmt_job_legend',
      ],
    ];
    return $data;
  }

}
