<?php

namespace Drupal\pluggable_entity_view_builder_example\Plugin\EntityViewBuilder;

use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\pluggable_entity_view_builder\EntityViewBuilderPluginAbstract;
use Drupal\pluggable_entity_view_builder_example\ElementContainerTrait;
use Drupal\pluggable_entity_view_builder_example\ProcessedTextBuilderTrait;
use Drupal\pluggable_entity_view_builder_example\TagBuilderTrait;

/**
 * The "Node Article" plugin.
 *
 * @EntityViewBuilder(
 *   id = "node.article",
 *   label = @Translation("Node - Article"),
 *   description = "Node view builder for Article bundle."
 * )
 */
class NodeArticle extends EntityViewBuilderPluginAbstract {

  use ElementContainerTrait;
  use ProcessedTextBuilderTrait;
  use TagBuilderTrait;

  /**
   * Build full view mode.
   *
   * @param array $build
   *   The existing build.
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildFull(array $build, NodeInterface $entity): array {
    // Header.
    $build[] = $this->buildHeroHeader($entity);

    // Tags.
    $build[] = $this->buildContentTags($entity);

    // Body.
    $build[] = $this->buildProcessedText($entity);

    // If Paragraphs example module is enabled, show the paragraphs.
    if ($entity->hasField('field_paragraphs') && !$entity->field_paragraphs->isEmpty()) {
      $build[] = [
        '#theme' => 'pluggable_entity_view_builder_example_cards',
        '#items' => $this->buildReferencedEntities($entity->field_paragraphs, 'full'),
      ];
    }

    // Comments.
    $build[] = $this->buildComment($entity);

    // Load Tailwind CSS framework, so our example are styled.
    $build['#attached']['library'][] = 'pluggable_entity_view_builder_example/tailwind';

    return $build;
  }

  /**
   * Default build in "Teaser" view mode.
   *
   * Show nodes as "cards".
   *
   * @param array $build
   *   The existing build.
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildTeaser(array $build, NodeInterface $entity): array {
    $element = [];

    $element['#theme'] = 'pluggable_entity_view_builder_example_card';

    // User may create a preview, so it won't have an ID or URL yet.
    $element['#url'] = !$entity->isNew() ? $entity->toUrl() : Url::fromRoute('<front>');
    $element['#title'] = $entity->label();
    $element['#body'] = $this->buildProcessedText($entity, 'body', TRUE);
    $element['#tags'] = $this->buildTags($entity);

    // Image as css image background.
    $image_info = $this->getImageAndAlt($entity, 'field_image');
    if ($image_info) {
      $element['#image'] = $image_info['url'];
      $element['#image_alt'] = $image_info['alt'];
    }

    $build[] = $element;

    // Load Tailwind CSS framework, so our example are styled nicer.
    $build['#attached']['library'][] = 'pluggable_entity_view_builder_example/tailwind';

    return $build;
  }

  /**
   * Get common elements for the view modes.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  protected function getElementBase(NodeInterface $entity): array {
    $element = [];

    // User may create a preview, so it won't have an ID or URL yet.
    $element['#nid'] = !$entity->isNew() ? $entity->id() : 0;
    $element['#url'] = !$entity->isNew() ? $entity->toUrl() : Url::fromRoute('<front>');
    $element['#title'] = $entity->label();

    return $element;
  }

  /**
   * Build the Hero Header section, with Title, and Background Image.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   * @param string $image_field_name
   *   Optional; The field name. Defaults to "field_image".
   *
   * @return array
   *   Render array.
   */
  protected function buildHeroHeader(NodeInterface $entity, $image_field_name = 'field_image'): array {
    $image_info = $this->getImageAndAlt($entity, $image_field_name);

    $element = [
      '#theme' => 'pluggable_entity_view_builder_example_hero_header',
      '#title' => $entity->label(),
      '#background_image' => !empty($image_info['url']) ? $image_info['url'] : '',
    ];

    return $this->wrapElementWithContainer($element);
  }

  /**
   * Build the content tags section.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   * @param string $field_name
   *   Optional; The term reference field name. Defaults to "field_tags".
   *
   * @return array
   *   Render array.
   */
  protected function buildContentTags(NodeInterface $entity, string $field_name = 'field_tags'): array {
    $tags = $this->buildTags($entity, $field_name);
    if (!$tags) {
      return [];
    }

    return [
      '#theme' => 'pluggable_entity_view_builder_example_tags',
      '#tags' => $tags,
    ];
  }

  /**
   * Build a list of tags.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   * @param string $field_name
   *   Optional; The term reference field name. Defaults to "field_tags".
   *
   * @return array
   *   Render array.
   */
  protected function buildTags(NodeInterface $entity, string $field_name = 'field_tags'): array {
    if (empty($entity->{$field_name}) || $entity->{$field_name}->isEmpty()) {
      // No terms referenced.
      return [];
    }

    $tags = [];
    foreach ($entity->{$field_name}->referencedEntities() as $term) {
      $tags[] = $this->buildTag($term);
    }

    return $tags;
  }

}
