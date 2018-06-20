<?php

namespace Drupal\geofield\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\NumericFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to filter Geofields by proximity.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("geofield_proximity")
 *
 * TODO: Use 'geofield_proximity' element in exposed filter.
 * TODO: Allow user to specify the point of origin on the exposed form.
 */
class GeofieldProximity extends NumericFilter {

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
  public function operators() {
    $operators = [
      '<' => [
        'title' => t('Is less than'),
        'method' => 'opSimple',
        'short' => t('<'),
        'values' => 1,
      ],
      '<=' => [
        'title' => t('Is less than or equal to'),
        'method' => 'opSimple',
        'short' => t('<='),
        'values' => 1,
      ],
      '=' => [
        'title' => t('Is equal to'),
        'method' => 'opSimple',
        'short' => t('='),
        'values' => 1,
      ],
      '!=' => [
        'title' => t('Is not equal to'),
        'method' => 'opSimple',
        'short' => t('!='),
        'values' => 1,
      ],
      '>=' => [
        'title' => t('Is greater than or equal to'),
        'method' => 'opSimple',
        'short' => t('>='),
        'values' => 1,
      ],
      '>' => [
        'title' => t('Is greater than'),
        'method' => 'opSimple',
        'short' => t('>'),
        'values' => 1,
      ],
      'between' => [
        'title' => t('Is between'),
        'method' => 'opBetween',
        'short' => t('between'),
        'values' => 2,
      ],
      'not between' => [
        'title' => t('Is not between'),
        'method' => 'opBetween',
        'short' => t('not between'),
        'values' => 2,
      ],
    ];

    return $operators;
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

    $info = $this->operators();

    $haversine_options = $source_plugin->getHaversineOptions();
    $haversine_options['destination_latitude'] = $this->tableAlias . '.' . $lat_alias;
    $haversine_options['destination_longitude'] = $this->tableAlias . '.' . $lon_alias;

    $this->{$info[$this->operator]['method']}($haversine_options);
  }

  /**
   * {@inheritdoc}
   */
  protected function opBetween($options) {
    $this->query->addWhereExpression($this->options['group'], geofield_haversine($options) . ' ' . strtoupper($this->operator) . ' ' . $this->value['min'] . ' AND ' . $this->value['max']);
  }

  /**
   * {@inheritdoc}
   */
  protected function opSimple($options) {
    $this->query->addWhereExpression($this->options['group'], geofield_haversine($options) . ' ' . $this->operator . ' ' . $this->value['value']);
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
      '#title' => t('Source of Origin Point'),
      '#description' => t('How do you want to enter your origin point?'),
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
    parent::validateOptionsForm($form, $form_state);
    /** @var \Drupal\geofield\Plugin\GeofieldProximityInterface $instance */
    $instance = $this->proximityManager->createInstance($form_state->getValue('options')['source']);
    $instance->setViewHandler($this);
    $instance->validateOptionsForm($form['source_configuration'], $form_state, ['source_configuration']);
  }

}
