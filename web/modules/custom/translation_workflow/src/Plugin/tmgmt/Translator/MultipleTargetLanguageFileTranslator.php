<?php

namespace Drupal\translation_workflow\Plugin\tmgmt\Translator;

use Drupal\Core\File\FileSystemInterface;
use Drupal\tmgmt\JobInterface;
use Drupal\tmgmt_file\Plugin\tmgmt\Translator\FileTranslator;

/**
 * File translator.
 *
 * @TranslatorPlugin(
 *   id = "multiple_target_language_file",
 *   label = @Translation("Multiple target Language file exchange"),
 *   description = @Translation("Provider to export and import files using multiple target languages."),
 *   ui = "Drupal\tmgmt_file\FileTranslatorUi"
 * )
 */
class MultipleTargetLanguageFileTranslator extends FileTranslator {

  /**
   * {@inheritdoc}
   */
  public function requestTranslation(JobInterface $job) {
    $name = 'translation_job_id_' . $job->id() . '_request';
    $export = \Drupal::service('plugin.manager.tmgmt_file.format')->createInstance($job->getSetting('export_format'), $job->getSetting('format_configuration'));

    $path = $job->getSetting('scheme') . '://tmgmt_file/' . $name . '.' . $job->getSetting('export_format');
    $dirname = dirname($path);
    if (\Drupal::service('file_system')->prepareDirectory($dirname, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
      $file = file_save_data($export->export($job), $path, FileSystemInterface::EXISTS_REPLACE);
      \Drupal::service('file.usage')->add($file, 'tmgmt_file', 'tmgmt_job', $job->id());
      $job->submitted('Exported file can be downloaded <a href="@link" download>here</a>.', ['@link' => file_create_url($path)]);
    }
    else {
      $job->rejected('Failed to create writable directory @dirname, check file system permissions.', ['@dirname' => $dirname]);
    }
  }

}
