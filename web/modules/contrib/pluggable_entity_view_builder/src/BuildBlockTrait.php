<?php

namespace Drupal\pluggable_entity_view_builder;

/**
 * Embed Block and block content helpers.
 *
 * @property \Drupal\Core\Block\BlockManager $blockManager
 * @property \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
 * @property \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
 * @property \Drupal\Core\Session\AccountInterface $currentUser
 * @property \Drupal\Core\Language\LanguageManagerInterface $languageManager
 *
 * To use this trait it is assumed above services are present. You may use the
 * following `create` method in your PEVB plugin, in order to have them.
 *
 * @code
 * public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
 *   $build = parent::create($container, $configuration, $plugin_id, $plugin_definition);
 *   $build->blockManager = $container->get('plugin.manager.block');
 *   $build->entityRepository = $container->get('entity.repository');
 *   $build->languageManager = $container->get('language_manager');
 *
 *   return $build;
 * }
 * @endcode
 */
trait BuildBlockTrait {

  /**
   * Build a plugin block.
   *
   * @param string $block_id
   *   The block ID.
   * @param array $config
   *   Configuration array for the block. Default is empty.
   *
   * @return array
   *   The render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function buildBlock(string $block_id, array $config = []) : array {
    $plugin_block = $this->blockManager->createInstance($block_id, $config);
    // Some blocks might implement access check.
    $access_result = $plugin_block->access($this->currentUser);
    if (is_object($access_result) && $access_result->isForbidden() || is_bool($access_result) && !$access_result) {
      // No access.
      return [];
    }

    return $plugin_block->build();
  }

  /**
   * Build a content block with an optional title, by its uuid.
   *
   * @param string $uuid
   *   The block uuid.
   * @param array $title
   *   Optional; Render array with the title. By passing a render array we
   *   allow passing already formatted titles. This can be used for example in
   *   conjunction with some `$this->buildTitle($entity)` that would return
   *   some render array.
   * @param string|null $langcode
   *   Optional; For which language the entity should be rendered, defaults to
   *   the current content language.
   *
   * @return array
   *   The render array.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function buildContentBlock(string $uuid, array $title = [], string $langcode = NULL) : array {
    $block = $this->entityRepository->loadEntityByUuid('block_content', $uuid);
    if (!$block) {
      // Block content doesn't exist.
      return [];
    }

    $build = $this->entityTypeManager->getViewBuilder('block_content')->view($block, 'full', $langcode);

    $element = [
      '#theme' => 'block',
      '#configuration' => [
        'label' => $title ?: [],
        'label_display' => !empty($title),
        'provider' => 'block_content',
      ],
      '#base_plugin_id' => 'block_content',
      '#plugin_id' => 'block_content:' . $block->uuid(),
      '#derivative_plugin_id' => $block->uuid(),
      '#id' => $block->id(),
      'content' => $build,
    ];

    return [
      '#theme' => 'block_content_contextual_links_wrap',
      '#content' => [
        'block' => $element,
        '#contextual_links' => [
          'block_content' => [
            'route_parameters' => ['block_content' => $block->id()],
          ],
        ],
      ],
    ];
  }

}
