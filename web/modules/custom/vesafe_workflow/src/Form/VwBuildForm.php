<?php

namespace Drupal\vesafe_workflow\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\vesafe_workflow\VwHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * General class for Vw build form.
 */
class VwBuildForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database manager.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The Vesafe helper service.
   *
   * @var \Drupal\vesafe_workflow\VwHelper
   */
  protected $helper;

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $database, VwHelper $vasefe_helper, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
    $this->helper = $vasefe_helper;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('vesafe_workflow.helper'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'build_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $table = NULL) {
    $form['rebuild'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Rebuild lists that already exist'),
      '#description' => $this->t('If checked, the process will rebuild all existing lists and the data will lost.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Build lists'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $vesafe_config = $this->configFactory->getEditable('vesafe_workflow.general');
    $lists = $vesafe_config->get('lists');
    if (!isset($lists)) {
      $tables = $this->database->schema()->findTables('vesafe_%');
      foreach ($tables as $table) {
        $this->database->schema()->dropTable($table);
      }
    }
    else {
      foreach ($vesafe_config->get('lists')['list'] as $list) {
        $name = strtolower($list['name']);

        // Delete the table first if the rebunild option is checked.
        if (boolval($form_state->getValue('rebuild'))) {
          $this->database->schema()->dropTable('vesafe_workflow_' . $name);
        }

        // Create the table.
        if (empty($this->database->schema()->tableExists('vesafe_workflow_' . $name))) {
          $this->database->schema()->createTable('vesafe_workflow_' . $name, [
            'fields' => [
              'id' => [
                'type' => 'serial',
                'not null' => TRUE,
              ],
              'node_id' => [
                'type' => 'int',
                'not null' => TRUE,
                'default' => 0,
              ],
              'user_id' => [
                'type' => 'int',
                'not null' => TRUE,
                'default' => 0,
              ],
              'status' => [
                'type' => 'varchar',
                'length' => 255,
                'not null' => TRUE,
                'default' => '',
              ],
              'weight' => [
                'type' => 'int',
                'not null' => TRUE,
                'default' => 0,
              ],
            ],
            'primary key' => ['id'],
          ]);
        }
      }
    }
  }

}
