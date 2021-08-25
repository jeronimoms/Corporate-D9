<?php

namespace Drupal\translation_workflow\Entity\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 *
 */
class MultipleTargetLanguageJobListBuilder extends EntityListBuilder {

  /**
   *
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $operations['manage'] = [
      'url' => $entity->toUrl()->setOption('query', ['destination' => Url::fromRoute('<current>')->getInternalPath()]),
      'title' => t('Manage'),
      'weight' => -10,
    ];
    return $operations;
  }

}
