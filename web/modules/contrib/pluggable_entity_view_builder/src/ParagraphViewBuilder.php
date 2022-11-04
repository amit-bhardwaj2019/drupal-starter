<?php

namespace Drupal\pluggable_entity_view_builder;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\paragraphs\ParagraphViewBuilder as ContribParagraphViewBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Overrides the contrib Paragraphs module's view builder class.
 */
class ParagraphViewBuilder extends ContribParagraphViewBuilder {

  use EntityViewBuilderTrait;

  /**
   * The entity view builder service.
   *
   * @var \Drupal\pluggable_entity_view_builder\EntityViewBuilder\EntityViewBuilderPluginManager
   */
  protected $entityViewBuilderPluginManager;

  /**
   * {@inheritDoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $builder = parent::createInstance($container, $entity_type);
    $builder->entityViewBuilderPluginManager = $container->get('plugin.manager.pluggable_entity_view_builder.entity_view_builder');

    return $builder;
  }

  /**
   * {@inheritDoc}
   */
  public function buildMultiple(array $build_list) {
    return $this->doBuildMultiple($build_list);
  }

}
