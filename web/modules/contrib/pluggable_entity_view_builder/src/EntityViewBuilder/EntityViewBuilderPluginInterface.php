<?php

namespace Drupal\pluggable_entity_view_builder\EntityViewBuilder;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * An interface for Entity View Builder Plugins.
 */
interface EntityViewBuilderPluginInterface extends ContainerFactoryPluginInterface, PluginInspectionInterface {

  /**
   * Build a render array.
   *
   * @param array $build
   *   The existing render array.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to show.
   * @param string $view_mode
   *   The view mode.
   *
   * @return array
   *   The render array.
   */
  public function build(array $build, EntityInterface $entity, string $view_mode): array;

}
