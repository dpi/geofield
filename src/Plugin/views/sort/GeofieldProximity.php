<?php

namespace Drupal\geofield\Plugin\views\sort;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\sort\SortPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to sort Geofields by proximity.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsSort("geofield_proximity")
 */
class GeofieldProximity extends SortPluginBase {

  /**
   * The geofield proximity manager.
   *
   * @var \Drupal\geofield\Plugin\GeofieldProximityManager
   */
  protected $proximityManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $proximity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->proximityManager = $proximity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static (
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.geofield_proximity')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['units'] = ['default' => 'GEOFIELD_KILOMETERS'];

    // Data sources and info needed.
    $options['source'] = ['default' => 'geofield_manual_origin'];
    $options['source_configuration'] = ['default' => []];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $lat_alias = $this->realField . '_lat';
    $lon_alias = $this->realField . '_lon';

    /** @var \Drupal\geofield\Plugin\GeofieldProximityInterface $source_plugin */
    $source_plugin = $this->proximityManager->createInstance($this->options['source'], $this->options['source_configuration']);
    $source_plugin->setViewHandler($this);
    $source_plugin->setUnits($this->options['units']);

    try {
      $haversine_options = $source_plugin->getHaversineOptions();
      $haversine_options['destination_latitude'] = $this->tableAlias . '.' . $lat_alias;
      $haversine_options['destination_longitude'] = $this->tableAlias . '.' . $lon_alias;

      $this->query->addOrderBy(NULL, geofield_haversine($haversine_options), $this->options['order'], $this->tableAlias . '_' . $this->field);
    }
    catch (\Exception $e) {
      watchdog_exception('geofield', $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['units'] = [
      '#type' => 'select',
      '#title' => t('Unit of Measure'),
      '#description' => '',
      '#options' => geofield_radius_options(),
      '#default_value' => $this->options['units'],
    ];

    $form['source'] = [
      '#type' => 'select',
      '#title' => $this->t('Source of Origin Point'),
      '#description' => $this->t('How do you want to enter your origin point?'),
      '#options' => [],
      '#default_value' => $this->options['source'],
      '#ajax' => [
        'url' => views_ui_build_form_url($form_state),
      ],
      '#submit' => [[$this, 'submitTemporaryForm']],
      '#executes_submit_callback' => TRUE,
    ];

    foreach ($this->proximityManager->getDefinitions() as $plugin_id => $definition) {
      $form['source']['#options'][$plugin_id] = $definition['admin_label'];
    }

    $form['source_configuration'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $source_plugin_id = isset($form_state->getUserInput()['options']['source']) ? $form_state->getUserInput()['options']['source'] : $this->options['source'];
    $source_plugin_configuration = isset($form_state->getUserInput()['options']['source_configuration']) ? $form_state->getUserInput()['options']['source_configuration'] : $this->options['source_configuration'];
    /** @var \Drupal\geofield\Plugin\GeofieldProximityInterface $instance */
    $source_plugin = $this->proximityManager->createInstance($source_plugin_id, $source_plugin_configuration);
    $source_plugin->setViewHandler($this);
    $source_plugin->buildOptionsForm($form['source_configuration'], $form_state, ['source_configuration']);
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    /** @var \Drupal\geofield\Plugin\GeofieldProximityInterface $instance */
    $instance = $this->proximityManager->createInstance($form_state->getValue('options')['source']);
    $instance->setViewHandler($this);
    $instance->validateOptionsForm($form['source_configuration'], $form_state, ['source_configuration']);
  }

}
