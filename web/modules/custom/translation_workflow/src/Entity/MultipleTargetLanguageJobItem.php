<?php

namespace Drupal\translation_workflow\Entity;

use Drupal\tmgmt\Entity\JobItem;

/**
 *
 */
class MultipleTargetLanguageJobItem extends JobItem {

  /**
   * {@inheritdoc}
   */
  public function getJob() {
    return MultipleTargetLanguageJob::load($this->getJobId());
  }

}
