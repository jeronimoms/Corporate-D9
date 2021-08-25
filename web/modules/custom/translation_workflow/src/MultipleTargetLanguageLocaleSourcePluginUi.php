<?php

namespace Drupal\translation_workflow;

use Drupal\tmgmt_locale\LocaleSourcePluginUi;

/**
 *
 */
class MultipleTargetLanguageLocaleSourcePluginUi extends LocaleSourcePluginUi {
  use MultipleTargetLanguageOverviewFormSubmitTrait;
}
