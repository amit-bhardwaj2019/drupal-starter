<?php

namespace Drupal\pluggable_entity_view_builder\Exception;

/**
 * Thrown when the plugin does not have the view mode specific build.
 *
 * @package Drupal\pluggable_entity_view_builder\Exception
 *
 * @see \Drupal\pluggable_entity_view_builder\EntityViewBuilderTrait
 * @see \Drupal\pluggable_entity_view_builder\EntityViewBuilderPluginAbstract
 */
class ViewModeNotFoundException extends \UnexpectedValueException {
}
