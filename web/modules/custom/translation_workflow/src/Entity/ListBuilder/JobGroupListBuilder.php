<?php

namespace Drupal\translation_workflow\Entity\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

class JobGroupListBuilder extends EntityListBuilder {
  public function buildHeader() {
    return [
      'label' => $this->t('Label'),
    ] + parent::buildHeader();
  }

  public function buildRow(EntityInterface $entity) {
    return [
      'label' => $entity->label(),
      ] + parent::buildRow($entity);
  }

}
