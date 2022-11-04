<?php

namespace Drupal\pluggable_entity_view_builder;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\pluggable_entity_view_builder\Exception\ViewModeNotFoundException;

/**
 * Helper method for dispatching the `build` function.
 *
 * We are hooking and short circuiting
 * \Drupal\Core\Entity\EntityViewBuilder::buildMultiple
 * as we want to have full control over the output, as well as not trigger any
 * other hooks. That is, our code will be the only one responsible for building
 * the render array, and if we'd need another module's work - we'd call it
 * manually.
 *
 * @property \Drupal\pluggable_entity_view_builder\EntityViewBuilder\EntityViewBuilderPluginManager $entityViewBuilderPluginManager
 */
trait EntityViewBuilderTrait {

  /**
   * Call View Builder plugin for each entity in a list.
   *
   * This is called from methods overriding
   * \Drupal\Core\Entity\EntityViewBuilder::buildMultiple.
   *
   * @return array
   *   Render array.
   */
  public function doBuildMultiple(array $build_list) {
    // Indicate if an entity view builder plugin was found. If it has not, then
    // we should return the default.
    $custom_build = FALSE;

    $new_build_list = [];
    foreach ($build_list as $key => $build) {
      // Comments for example pass other keys such as `#sorted`, and `pager`, so
      // keep them intact.
      if (!is_array($build)) {
        // Not an array value.
        $new_build_list[$key] = $build;
        continue;
      }

      $entity_type_key = "#{$this->entityTypeId}";
      if (!array_key_exists($entity_type_key, $build)) {
        // No entity to build.
        $new_build_list[$key] = $build;
        continue;
      }
      $entity = $build[$entity_type_key];
      if (!$entity instanceof EntityInterface) {
        // No entity to build.
        $new_build_list[$key] = $build;
        continue;
      }
      // Adds contextual links to entities.
      $this->addContextualLinks($build, $entity);

      $value = $this->doBuild($build);
      $new_build_list[$key] = $value['render'];

      if ($value['plugin_used']) {
        $custom_build = TRUE;
      }
    }

    if (!$custom_build) {
      // We don't have a plugin defined, so return the default.
      return parent::buildMultiple($build_list);
    }

    return $new_build_list;
  }

  /**
   * Call View Builder plugin, if exists, to get the correct render array.
   *
   * This is a dispatcher method, that decides - according to the entity type,
   * and bundle to which plugin to call, in order to get a render array.
   *
   * @param array $build
   *   The current render array.
   *
   * @return array
   *   Array with the following keys:
   *   - 'render`: The render array.
   *   - 'plugin_used': TRUE if an Entity view builder plugin was used to create
   *   the render array, otherwise FALSE.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function doBuild(array $build) {
    $no_change = [
      'render' => $build,
      'plugin_used' => FALSE,
    ];
    // We can't rely on the presence of $build['#entity_type'], as it doesn't
    // always exist (e.g. when rendering a User). In those cases will iterate
    // over the build array, and try to extract the Entity.
    if (!empty($build['#entity_type'])) {
      $entity_type = $build['#entity_type'];

      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $build['#' . $entity_type];
    }
    else {
      foreach ($build as $row) {
        if ($row instanceof EntityInterface) {
          /** @var \Drupal\Core\Entity\EntityInterface $entity */
          $entity = $row;
          break;
        }
      }
    }

    if (empty($entity)) {
      // If we don't have an entity, it means we were passed a different key.
      // For example it could be the `pager` for comments.
      return $no_change;
    }

    $bundle = $entity->bundle();

    // Check if we have a plugin to take over the bundle of this entity.
    $plugin_id = $entity->getEntityTypeId() . '.' . $bundle;

    try {
      // Check if the plugin exists.
      $this->entityViewBuilderPluginManager->getDefinition($plugin_id);
    }
    catch (PluginNotFoundException $e) {
      // We don't have a plugin.
      return $no_change;
    }

    /** @var \Drupal\pluggable_entity_view_builder\EntityViewBuilder\EntityViewBuilderPluginInterface $plugin */
    $plugin = $this->entityViewBuilderPluginManager->createInstance($plugin_id);
    $view_mode = $build['#view_mode'];
    try {
      $rendered = $plugin->build($build, $entity, $view_mode);
    }
    catch (ViewModeNotFoundException $e) {
      // Plugin doesn't have the view mode defined.
      return $no_change;
    }

    return [
      'render' => $rendered,
      'plugin_used' => TRUE,
    ];
  }

}
