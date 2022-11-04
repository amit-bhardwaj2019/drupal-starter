<?php

namespace Drupal\pluggable_entity_view_builder;

/**
 * Helper method for adding contextual links to Block content.
 *
 * See https://www.drupal.org/project/drupal/issues/2666578#comment-12680906
 */
trait BlockContentContextualLinksTrait {

  /**
   * Show block content with Contextual links.
   *
   * @param array $build
   *   The original render array, which has the "contextual_links" info.
   * @param array $element
   *   The render array we'd like to output, and we'd like to append teh
   *   contextual links to it.
   *
   * @return array
   *   Render array.
   */
  public function viewWithContextualLinks(array $build, array $element) {
    return [
      '#theme' => 'block_content_contextual_links_wrap',
      '#content' => [
        'block' => $element,
        '#contextual_links' => $build['#contextual_links'],
      ],
    ];
  }

}
