<?php

namespace Drupal\geofield\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Geofield Proximity item annotation object.
 *
 * @see \Drupal\geofield\Plugin\GeofieldProximityManager
 * @see plugin_api
 *
 * @Annotation
 */
class GeofieldProximity extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
