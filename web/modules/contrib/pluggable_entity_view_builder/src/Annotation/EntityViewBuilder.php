<?php

namespace Drupal\pluggable_entity_view_builder\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an EntityViewBuilder annotation object.
 *
 * @Annotation
 */
class EntityViewBuilder extends Plugin {
  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The plugin description.
   *
   * @var string
   */
  public $description;

}
