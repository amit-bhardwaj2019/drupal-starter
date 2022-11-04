<?php

namespace Drupal\pluggable_entity_view_builder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\pluggable_entity_view_builder\EntityViewBuilder\EntityViewBuilderPluginInterface;
use Drupal\pluggable_entity_view_builder\Exception\ViewModeNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * An abstract class for Entity View Builders classes.
 */
abstract class EntityViewBuilderPluginAbstract extends PluginBase implements EntityViewBuilderPluginInterface {

  use BuildFieldTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Abstract constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $build, EntityInterface $entity, string $view_mode): array {
    $bundle = $entity->bundle();
    $view_mode = $build['#view_mode'];

    // If the view mode is 'default' then default to 'full' view mode.
    if ($view_mode == 'default') {
      $view_mode = 'full';
    }

    // We should get a method name such as `buildFull`, and `buildTeaser`.
    $method = 'build' . mb_convert_case($view_mode, MB_CASE_TITLE);
    $method = str_replace(['_', '-', ' '], '', $method);

    if (!is_callable([$this, $method])) {
      throw new ViewModeNotFoundException(sprintf('The view builder method %s for entity %s, bundle %s and view mode %s not found',
          $method,
          $entity->getEntityTypeId(),
          $bundle,
          $view_mode
      ));
    }

    return $this->$method($build, $entity);
  }

}
