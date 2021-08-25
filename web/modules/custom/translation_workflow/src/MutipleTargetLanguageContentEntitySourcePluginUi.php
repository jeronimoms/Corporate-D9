<?php

namespace Drupal\translation_workflow;

use Drupal\tmgmt_content\ContentEntitySourcePluginUi;

/**
 *
 */
class MutipleTargetLanguageContentEntitySourcePluginUi extends ContentEntitySourcePluginUi {
  use MultipleTargetLanguageOverviewFormSubmitTrait;
}
