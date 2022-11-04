<?php

namespace Drupal\pluggable_entity_view_builder_paragraphs_example\Plugin\EntityViewBuilder;

use Drupal\paragraphs\ParagraphInterface;
use Drupal\pluggable_entity_view_builder\EntityViewBuilderPluginAbstract;
use Drupal\pluggable_entity_view_builder_example\ProcessedTextBuilderTrait;
use Drupal\pluggable_entity_view_builder_example\TagBuilderTrait;

/**
 * The "Card" paragraph plugin.
 *
 * @EntityViewBuilder(
 *   id = "paragraph.card",
 *   label = @Translation("Paragraph - Card"),
 *   description = "Paragraph view builder for 'Card' bundle."
 * )
 */
class ParagraphCard extends EntityViewBuilderPluginAbstract {

  use ProcessedTextBuilderTrait;
  use TagBuilderTrait;

  /**
   * Build full view mode.
   *
   * @param array $build
   *   The existing build.
   * @param \Drupal\paragraphs\ParagraphInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildFull(array $build, ParagraphInterface $entity): array {
    $element = [];
    $element['#theme'] = 'pluggable_entity_view_builder_example_card';
    $element['#title'] = $this->getTextFieldValue($entity, 'field_title');
    $element['#body'] = $this->buildProcessedText($entity, 'field_body');

    // Image will be add in Twig as Css image background.
    $image_info = $this->getImageAndAlt($entity);
    if ($image_info) {
      $element['#image'] = $image_info['url'];
      $element['#image_alt'] = $image_info['alt'];
    }
    else {
      // Fallback to default image.
      $element['#image'] = 'https://picsum.photos/200';
      $element['#image_alt'] = $this->t('Fallback image to Lorem picsum');
    }

    $url_info = $this->getLinkFieldValue($entity, 'field_link');
    // Fallback to a dummy URL.
    $element['#url'] = $url_info ? $url_info['url'] : '#';

    // Tags.
    $element['#tags'] = $this->buildContentTags($entity);

    $build[] = $element;

    return $build;
  }

  /**
   * Build the content tags section.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $entity
   *   The entity.
   * @param string $field_name
   *   Optional; The term reference field name. Defaults to "field_tags".
   *
   * @return array
   *   Render array.
   */
  protected function buildContentTags(ParagraphInterface $entity, string $field_name = 'field_tags') : array {
    $tags = $this->buildTags($entity, $field_name);
    if (!$tags) {
      return [];
    }

    return [
      '#theme' => 'pluggable_entity_view_builder_paragraphs_example_content__tags',
      '#tags' => $tags,
    ];
  }

  /**
   * Build a list of tags.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $entity
   *   The entity.
   * @param string $field_name
   *   Optional; The term reference field name. Defaults to "field_tags".
   *
   * @return array
   *   Render array.
   */
  protected function buildTags(ParagraphInterface $entity, string $field_name = 'field_tags') : array {
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
