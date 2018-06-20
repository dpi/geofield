<?php

namespace Drupal\geofield\Plugin\GeofieldProximity;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geofield\Plugin\GeofieldProximityBase;

/**
 * Defines 'Geofield manual origin' plugin.
 *
 * @package Drupal\geofield\Plugin
 *
 * @GeofieldProximity(
 *   id = "geofield_manual_origin",
 *   label = @Translation("Geofield manual origin"),
 *   admin_label = @Translation("Geofield manual origin"),
 * )
 */
class GeofieldProximityDefault extends GeofieldProximityBase {

  /**
   * The origin point to measure proximity from.
   *
   * @var array
   */
  protected $origin;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->origin['lat'] = isset($configuration['origin_latitude']) ? $configuration['origin_latitude'] : NULL;
    $this->origin['lon'] = isset($configuration['origin_longitude']) ? $configuration['origin_longitude'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(array &$form, FormStateInterface $form_state, array $options_parents) {

    $form['origin_latitude'] = [
      '#type' => 'textfield',
      '#title' => t('Origin latitude'),
      '#default_value' => isset($this->configuration['origin_latitude']) ? $this->configuration['origin_latitude'] : '',
    ];

    $form['origin_longitude'] = [
      '#type' => 'textfield',
      '#title' => t('Origin longitude'),
      '#default_value' => isset($this->configuration['origin_longitude']) ? $this->configuration['origin_longitude'] : '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(array &$form, FormStateInterface $form_state, array $options_parents) {
    $values = $form_state->getValues();
    $values = NestedArray::getValue($values, array_merge(['options'], $options_parents));

    if (strlen($values['origin_latitude']) > 0 && !preg_match('/^\-?\d+(?:\.\d+)?$/', $values['origin_latitude'])) {
      $form_state->setError($form['origin_latitude'], t('Invalid latitude value: @value', ['@value' => $values['origin_latitude']]));
    }
    if (strlen($values['origin_longitude']) > 0 && !preg_match('/^\-?\d+(?:\.\d+)?$/', $values['origin_longitude'])) {
      $form_state->setError($form['origin_longitude'], t('Invalid longitude value: @value', ['@value' => $values['origin_longitude']]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getOrigin() {
    return $this->origin;
  }

}
