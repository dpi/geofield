<?php

namespace Drupal\geofield\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\HandlerBase;

/**
 * Defines an interface for Geofield Proximity plugins.
 */
interface GeofieldProximityInterface extends PluginInspectionInterface {

  /**
   * Builds the options form for the geofield proximity plugin.
   *
   * @param array $form
   *   The form element to build.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $options_parents
   *   The values parents.
   */
  public function buildOptionsForm(array &$form, FormStateInterface $form_state, array $options_parents);

  /**
   * Validates the options form for the geofield proximity plugin.
   *
   * @param array $form
   *   The form element to build.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $options_parents
   *   The values parents.
   */
  public function validateOptionsForm(array &$form, FormStateInterface $form_state, array $options_parents);

  /**
   * Set the units to perform the calculation in.
   *
   * @param string $units
   *   The name of the units constant to be used or string representation of it.
   */
  public function setUnits($units);

  /**
   * Get the current units.
   *
   * @return string
   *   The name of the units constant to be used or string representation of it.
   */
  public function getUnits();

  /**
   * Sets view handler which uses this proximity plugin.
   *
   * @param \Drupal\views\Plugin\views\HandlerBase $view_handler
   *   The view handler which uses this proximity plugin.
   */
  public function setViewHandler(HandlerBase $view_handler);

  /**
   * Get the calculated proximity.
   *
   * @param float $lat
   *   The current point latitude.
   * @param float $lon
   *   The current point longitude.
   *
   * @return float
   *   The calculated proximity.
   */
  public function getProximity($lat, $lon);

  /**
   * Gets the haversine options.
   *
   * @return array
   *   The haversine options.
   */
  public function getHaversineOptions();

}
