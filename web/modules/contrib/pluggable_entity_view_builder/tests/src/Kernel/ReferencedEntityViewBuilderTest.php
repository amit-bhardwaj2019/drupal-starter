<?php

declare(strict_types = 1);

namespace Drupal\Tests\pluggable_entity_view_builder\Kernel;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\NodeInterface;
use Drupal\Tests\EntityViewTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests to check entity view builder plugins on referenced entities.
 *
 * @group pluggable_entity_view_builder
 */
class ReferencedEntityViewBuilderTest extends EntityKernelTestBase {

  use EntityViewTrait {
    buildEntityView as drupalBuildEntityView;
  }
  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'datetime',
    'filter',
    'node',
    'pluggable_entity_view_builder',
    'pluggable_entity_view_builder_test',
    'system',
    'user',
  ];

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Test entity 1.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $testEntity1;

  /**
   * Test entity 2.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $testEntity2;

  /**
   * Test entity 3.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $testEntity3;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Enable Node override, and clear cache.
    $this->container->get('config.factory')
      ->getEditable('pluggable_entity_view_builder.settings')
      ->set('enabled_entity_types', ['node'])
      ->save();

    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('node');
    $this->installEntitySchema('date_format');
    $this->installConfig('filter');
    $this->installConfig('node');
    $this->installConfig('user');

    // Allow anonymous to access published content.
    $role = Role::load(RoleInterface::ANONYMOUS_ID);
    $role->grantPermission('access content');
    $role->save();

    DateFormat::create([
      'id' => 'fallback',
      'label' => 'Fallback',
      'pattern' => 'Y-m-d',
    ])->save();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->renderer = $this->container->get('renderer');

    $this->createContentType([
      'type' => 'article',
      'name' => 'Article',
      'display_submitted' => FALSE,
    ]);

    // Create some test entities.
    $author = $this->createUser();

    $this->testEntity1 = $this->createNode([
      'type' => 'article',
      'name' => 'Entity 1: Published',
      'status' => NodeInterface::PUBLISHED,
      'uid' => $author->id(),
    ]);

    $this->testEntity2 = $this->createNode([
      'type' => 'article',
      'name' => 'Entity 2: Unpublished',
      'status' => NodeInterface::NOT_PUBLISHED,
      'uid' => $author->id(),
    ]);

    $this->testEntity3 = $this->createNode([
      'type' => 'article',
      'name' => 'Entity 3: Published',
      'field_reference' => [
        $this->testEntity1->id(),
        $this->testEntity2->id(),
      ],
      'status' => NodeInterface::PUBLISHED,
      'uid' => $author->id(),
    ]);
  }

  /**
   * Test entity view builder plugin doesn't display unpublished references.
   */
  public function testReferencedEntityViewBuilder(): void {
    // Build entity 3 which references entities 1 and 2.
    $build = $this->drupalBuildEntityView($this->testEntity3);
    $output = (string) $this->renderer->renderRoot($build);
    $this->setRawContent($output);
    $this->assertRaw('<div class="entity-test-referenced-entities">');
    // Unpublished entity should not be output.
    $this->assertNoRaw($this->testEntity2->label());
    // Published referenced entity should be output.
    $this->assertRaw($this->testEntity1->label());
  }

}
