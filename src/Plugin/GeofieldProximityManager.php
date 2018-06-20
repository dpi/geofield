<?php

namespace Drupal\geofield\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Geofield Proximity plugin manager.
 */
class GeofieldProximityManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/GeofieldProximity', $namespaces, $module_handler, 'Drupal\geofield\Plugin\GeofieldProximityInterface', 'Drupal\geofield\Annotation\GeofieldProximity');

    $this->alterInfo('geofield_geofield_proximity_info');
    $this->setCacheBackend($cache_backend, 'geofield_geofield_proximity_plugins');
  }

}
