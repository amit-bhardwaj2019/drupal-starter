<?php

namespace Drupal\pluggable_entity_view_builder\EntityViewBuilder;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin manager for Entity view builder.
 */
class EntityViewBuilderPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/EntityViewBuilder',
      $namespaces,
      $module_handler,
      'Drupal\pluggable_entity_view_builder\EntityViewBuilder\EntityViewBuilderPluginInterface',
      'Drupal\pluggable_entity_view_builder\Annotation\EntityViewBuilder'
    );
    $this->alterInfo('pluggable_entity_view_builder_info');
    $this->setCacheBackend($cache_backend, 'pluggable_entity_view_builder_plugins');
  }

}
