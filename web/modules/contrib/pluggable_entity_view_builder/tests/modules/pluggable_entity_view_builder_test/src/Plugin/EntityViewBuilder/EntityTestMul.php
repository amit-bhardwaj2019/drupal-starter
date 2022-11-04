<?php

namespace Drupal\pluggable_entity_view_builder_test\Plugin\EntityViewBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Markup;
use Drupal\pluggable_entity_view_builder\BuildFieldTrait;
use Drupal\pluggable_entity_view_builder\EntityViewBuilderPluginAbstract;

/**
 * The "Entity Test Multilingual" plugin.
 *
 * @EntityViewBuilder(
 *   id = "entity_test_mul.entity_test_mul",
 *   label = @Translation("Entity Test - Multilingual"),
 *   description = "Entity view builder for Entity Test Multilingual."
 * )
 */
class EntityTestMul extends EntityViewBuilderPluginAbstract {

  use BuildFieldTrait;

  /**
   * Build the entity in 'full' view mode.
   *
   * @param array $build
   *   The build array.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildFull(array $build, EntityInterface $entity) {
    // Add the name to the output.
    $build[] = [
      '#type' => 'markup',
      '#markup' => Markup::create($entity->label()),
    ];
    // Output the referenced entities if any.
    if (!empty($entity->field_reference) && !$entity->field_reference->isEmpty()) {

      // Wrap element with a class, so it's easier to assert element in tests.
      // Normally we would use Twig for the wrapping class, however since this
      // is a test, we go for a simpler solution using the render API.
      $element = ['#type' => 'container'];
      $element['element'] = $this->buildReferencedEntities($entity->field_reference, 'full', $entity->language()->getId());
      $element['#attributes']['class'][] = 'entity-test-referenced-entities';

      $build[] = $element;

    }
    return $build;
  }

}
